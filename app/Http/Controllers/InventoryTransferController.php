<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryTransfer;
use App\Models\InventoryTransferItem;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\DataTables\InventoryTransferDataTable;

class InventoryTransferController extends Controller
{
    public function index(InventoryTransferDataTable $dataTable)
    {
        $sucursales = DB::table('sucursals')->get();
        $products = Product::select('id','code','code2','name')->get();
        return $dataTable->render('inventory_transfer.index', compact('sucursales','products'));
    }

    public function datatable(Request $request)
    {
        $query = DB::table('inventory_transfers')
            ->leftJoin('sucursals as f', 'inventory_transfers.id_sucursal_from', '=', 'f.id')
            ->leftJoin('sucursals as t', 'inventory_transfers.id_sucursal_to', '=', 't.id')
            ->select('inventory_transfers.*', 'f.name as from_name', 't.name as to_name')
            ->orderBy('inventory_transfers.created_at','desc');

        return datatables()->of($query)
            ->addColumn('action', function($row){
                $open = "<button class='btn btn-sm btn-primary open-transfer' data-id='".$row->id."' title='Abrir transferencia'>Abrir</button> ";
                $pdf = "<a href='".route('inventory-transfers.pdf', $row->id)."' target='_blank' class='btn btn-sm btn-secondary' title='PDF'>PDF</a>";
                return $open.$pdf;
            })
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_sucursal_from' => 'required|integer',
            'id_sucursal_to' => 'required|integer|different:id_sucursal_from',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $code = 'TR-' . date('YmdHis');
            $transfer = InventoryTransfer::create([
                'code' => $code,
                'id_sucursal_from' => $data['id_sucursal_from'],
                'id_sucursal_to' => $data['id_sucursal_to'],
                'status' => 0,
                'created_by' => auth()->id() ?? null,
                'notes' => $request->input('notes'),
            ]);

            foreach ($data['items'] as $item) {
                $quantity = floatval($item['quantity']);
                if ($quantity <= 0) continue;
                InventoryTransferItem::create([
                    'inventory_transfer_id' => $transfer->id,
                    'id_product' => $item['id_product'],
                    'quantity' => $quantity,
                ]);

                // Ajuste de inventario: aquí hay que integrar con la lógica de stocks del proyecto.
                // Por ahora dejaremos la responsabilidad al proceso que sincronice stocks.
            }

            DB::commit();
            return response()->json($transfer->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing transfer (only if pending) with new items/notes.
     */
    public function update(Request $request, $id)
    {
        $transfer = InventoryTransfer::with('items')->findOrFail($id);

        // only allow update when pending
        if ($transfer->status != 0) {
            return response()->json(['message' => 'Solo se pueden editar transferencias en estado pendiente'], 400);
        }

        $user = auth()->user();
        if (!in_array($user->type, ['ADMINISTRADOR','ADMINISTRATIVO'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            // update notes if provided
            if ($request->has('notes')) {
                $transfer->notes = $request->input('notes');
                $transfer->save();
            }

            // remove existing items and recreate (simple approach)
            InventoryTransferItem::where('inventory_transfer_id', $transfer->id)->delete();

            foreach ($data['items'] as $item) {
                $quantity = intval($item['quantity']);
                if ($quantity <= 0) continue;
                InventoryTransferItem::create([
                    'inventory_transfer_id' => $transfer->id,
                    'id_product' => $item['id_product'],
                    'quantity' => $quantity,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Actualizada', 'id' => $transfer->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function pdf($id)
    {
        $transfer = InventoryTransfer::with('items.product','fromSucursal','toSucursal')->findOrFail($id);
        $height = 220 + count($transfer->items) * 30;
        $pdf = PDF::setPaper([0,0,210,$height])->loadView('pdf.transfer', [
            'transfer' => $transfer,
        ]);
        return $pdf->stream('transfer-'.$transfer->code.'.pdf');
    }

    /**
     * Show transfer data as JSON for modal editing/viewing.
     */
    public function show(Request $request, $id)
    {
        $transfer = InventoryTransfer::with('items.product','fromSucursal','toSucursal')->findOrFail($id);
        if ($request->wantsJson() || $request->ajax() || $request->query('ajax')) {
            $data = $transfer->toArray();
            return response()->json($data);
        }
        // Fallback: render a view if needed (not implemented)
        return view('inventory_transfer.show', compact('transfer'));
    }

    /**
     * Approve a pending transfer: adjust stocks and set status=1.
     */
    public function approve(Request $request, $id)
    {
        $user = auth()->user();
        if (!in_array($user->type, ['ADMINISTRADOR','ADMINISTRATIVO'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        DB::beginTransaction();
        try {
            $transfer = InventoryTransfer::with('items')->findOrFail($id);
            if ($transfer->status != 0) {
                return response()->json(['message' => 'Transferencia no está en estado pendiente'], 400);
            }

            // Solo descontar inventario en sucursal de origen
            foreach ($transfer->items as $item) {
                $prodId = $item->id_product;
                $qty = (float) $item->quantity;

                // origin stock
                $lastOrigin = DB::table('stocks')->where('id_product', $prodId)->where('id_sucursal', $transfer->id_sucursal_from)->orderBy('id','desc')->first();
                $originQty = $lastOrigin ? (float) $lastOrigin->quantity : 0;
                if ($originQty < $qty) {
                    DB::rollBack();
                    return response()->json(['message' => 'Stock insuficiente para el producto ID '.$prodId], 400);
                }

                $newOriginQty = $originQty - $qty;
                DB::table('stocks')->insert([
                    'id_sucursal' => $transfer->id_sucursal_from,
                    'id_product' => $prodId,
                    'id_user' => $user->id,
                    'id_inventory_transfer' => $transfer->id,
                    'cost' => null,
                    'addition' => 0,
                    'subtraction' => $qty,
                    'quantity' => $newOriginQty,
                    'description' => 'TRANSFERENCIA '.$transfer->code.' ORIGEN',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Cambiar status a 3 (LISTA)
            $transfer->status = 1;
            $transfer->save();

            DB::commit();
            return response()->json(['message' => 'Transferencia LISTA']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject a pending transfer: set status=2 (cancelado).
     */
    public function reject(Request $request, $id)
    {
        $user = auth()->user();
        if (!in_array($user->type, ['ADMINISTRADOR','ADMINISTRATIVO'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        $transfer = InventoryTransfer::findOrFail($id);
        if ($transfer->status != 0) {
            return response()->json(['message' => 'Transferencia no está en estado pendiente'], 400);
        }
        $transfer->status = 2;
        $transfer->save();
        return response()->json(['message' => 'Rechazada']);
    }

    /**
     * Mark an approved transfer as LISTA: add stock to destination and set status=3.
     */
    public function markAsLista(Request $request, $id)
    {
        $user = auth()->user();
        if (!in_array($user->type, ['ADMINISTRADOR','ADMINISTRATIVO'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        DB::beginTransaction();
        try {
            $transfer = InventoryTransfer::with('items')->findOrFail($id);
            if ($transfer->status != 1) {
                return response()->json(['message' => 'La transferencia debe estar en estado aprobado para marcar como LISTA'], 400);
            }

            // For each item: add stock to destination only
            foreach ($transfer->items as $item) {
                $prodId = $item->id_product;
                $qty = (float) $item->quantity;

                // destination stock: get last quantity
                $lastDest = DB::table('stocks')->where('id_product', $prodId)->where('id_sucursal', $transfer->id_sucursal_to)->orderBy('id','desc')->first();
                $destQty = $lastDest ? (float) $lastDest->quantity : 0;
                $newDestQty = $destQty + $qty;
                DB::table('stocks')->insert([
                    'id_sucursal' => $transfer->id_sucursal_to,
                    'id_product' => $prodId,
                    'id_user' => $user->id,
                    'id_inventory_transfer' => $transfer->id,
                    'cost' => null,
                    'addition' => $qty,
                    'subtraction' => 0,
                    'quantity' => $newDestQty,
                    'description' => 'TRANSFERENCIA '.$transfer->code.' DESTINO',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // set status = 3 (LISTA)
            $transfer->status = 3;
            $transfer->save();

            DB::commit();
            return response()->json(['message' => 'Transferencia marcada como LISTA']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Return current stock quantity for a product in a given sucursal (branch).
     */
    public function productStock(Request $request)
    {
        $id_product = $request->query('id_product');
        $id_sucursal = $request->query('id_sucursal');

        if (!$id_product) {
            return response()->json(['quantity' => 0]);
        }

        $query = DB::table('stocks')->where('id_product', $id_product);
        if ($id_sucursal) {
            $query->where('id_sucursal', $id_sucursal);
        }
        $last = $query->orderBy('id', 'desc')->first();
        $quantity = $last ? (float) $last->quantity : 0;
        return response()->json(['quantity' => $quantity]);
    }
}
