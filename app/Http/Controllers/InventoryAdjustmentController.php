<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\DataTables\InventoryAdjustmentDataTable;
use App\Models\InventoryAdjustment;

class InventoryAdjustmentController extends Controller
{
    public function indexInventoryAdjustment(InventoryAdjustmentDataTable $dataTable)
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            return $dataTable->render('products.inventary');
        } else {
            return redirect()->route('indexStore');
        }
    }
    public function indexStocktaking()
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            $products = Product::where('status', 1)->get();
            $categories = Category::where('status', '1')->get();
            return view('products.stocktaking', compact('products', 'categories'));
        } else {
            return redirect()->route('indexStore');
        }
    }

    public function ajaxStocktaking()
    {
        DB::statement("SET SQL_MODE=''");
        if (request()->ajax()) {
            $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : DB::table('sucursals')->value('id');

            $sucursalFilter = $sucursalId ? "AND stocks.id_sucursal = {$sucursalId}" : "";

            $products = DB::table('products')
                ->select(
                    'products.id as id',
                    'products.code as code',
                    'products.name as name',
                    // Subconsulta para obtener el último quantity de stock de cada producto
                    DB::raw('(
                        SELECT quantity FROM stocks 
                        WHERE stocks.id_product = products.id '
                        . $sucursalFilter . '
                        ORDER BY created_at DESC, id DESC 
                        LIMIT 1
                    ) as stock'),
                    'products.price as price'
                );

            return datatables()->of($products)->make(true);
        }
        return redirect()->route('indexStore');
    }
    public function storeStocktaking(Request $request)
    {
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : DB::table('sucursals')->value('id');

        $inventoryAdjustment = InventoryAdjustment::create([
            'id_sucursal' => $sucursalId,
            'id_user' => auth()->id(),
            'description' => $request->descriptionStock,
            'amount_lost' => $request->amountLost,
            'amount_profit' => $request->amountProfit,
            'amount' => $request->totalAmount,
        ]);
        foreach ($request->datos as $dato) {
            $diferenciaAbsoluta = abs($dato['diferencia']);
            Stock::create([
                'id_inventory_adjustment' => $inventoryAdjustment->id,
                'id_product' => $dato['id_producto'],
                'id_user' => auth()->id(),
                'id_sucursal' => $sucursalId,
                'addition' => $dato['diferencia'] > 0 ? $diferenciaAbsoluta : 0,
                'subtraction' => $dato['diferencia'] < 0 ? $diferenciaAbsoluta : 0,
                'quantity' => $dato['nuevo_stock'],
                'description' => $request->descriptionStock,
            ]);
        }
        return response()->json($inventoryAdjustment->id);
    }

    public function stocktakingReset(Request $request)
    {
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : DB::table('sucursals')->value('id');

        $inventoryAdjustment = InventoryAdjustment::create([
            'id_sucursal' => $sucursalId,
            'id_user' => auth()->id(),
            'description' => $request->descriptionStock,
            'amount_lost' => $request->amountLost,
            'amount_profit' => $request->amountProfit,
            'amount' => $request->totalAmount,
        ]);

        // Obtener el último stock por producto filtrando por sucursal
        $subqueryWhere = $sucursalId ? "WHERE id_sucursal = {$sucursalId}" : "";
        $stocks = Stock::select('stocks.*')
            ->join(DB::raw("(SELECT id_product, MAX(created_at) as last_created_at FROM stocks {$subqueryWhere} GROUP BY id_product) as subquery"), function ($join) {
                $join->on('stocks.id_product', '=', 'subquery.id_product')
                    ->on('stocks.created_at', '=', 'subquery.last_created_at');
            })
            ->where('stocks.id_sucursal', $sucursalId)
            ->get();
        foreach ($stocks as $stock) {
            $quantity = $stock->quantity;
            if ($quantity != 0) {
                $diferenciaAbsoluta = abs($quantity);
                Stock::create([
                    'id_inventory_adjustment' => $inventoryAdjustment->id,
                    'id_product' => $stock->id_product,
                    'id_user' => auth()->id(),
                    'id_sucursal' => $sucursalId,
                    'addition' => $quantity < 0 ? $diferenciaAbsoluta : 0,
                    'subtraction' => $quantity > 0 ? $diferenciaAbsoluta : 0,
                    'quantity' => 0,
                    'description' => 'RESETEO DE INVENTARIO',
                ]);
            }
        }
        return response()->json(['success' => true]);
    }
}
