<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\OrderDataTable;
use App\Models\Bill;
use App\Models\Bill_detail;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(OrderDataTable $dataTable)
    {
        return $dataTable->render('orders.index');
    }

    /**
     * AJAX endpoint for DataTable with optional debug information.
     * Append ?debug=1 to the DataTable ajax URL to receive SQL, bindings,
     * a row count and a small sample alongside the normal DataTables JSON.
     */
    public function ajax(OrderDataTable $dataTable)
    {
        return $dataTable->ajax();
    }
      /**
     * Cambia el status de una factura/pedido (por ejemplo: 0=PENDIENTE,1=APROBADO,2=RECHAZADO)
     */
    public function changeStatusOrder(Request $request)
    {
        $id = $request->id;
        $status = intval($request->status);
        $bill = Bill::find($id);
        if (!$bill) {
            return response()->json(['res' => 'error', 'message' => 'Bill not found'], 404);
        }
        $bill->status = $status;
        $bill->save();
        return response()->json(['res' => 'bien', 'id' => $bill->id, 'status' => $bill->status]);
    }
     public function mostrarOrder(Request $request)
    {
        // If an id is provided, return that bill and its details. Otherwise keep legacy behaviour
      
        $bill = Bill::with('bill_details')->find($request->id);
        if (!$bill) {
            return Response()->json(['success' => 'error']);
        }
        $bill_details = $bill->bill_details->map(function ($detail) {
        $product = Product::find($detail->id_product);
            return [
                'id' => $detail->id,
                'id_product' => $detail->id_product,
                'code' => $detail->code,
                'name' => $detail->name,
                'price' => $detail->price,
                'quantity' => $detail->quantity,
                'discount_percent' => $detail->discount_percent,
                'product_name' => $product->name ?? 'N/A',
                'price_type' => $detail->price_type,
            ];
        });

        return Response()->json([
            'bill' => $bill,
            'bill_details' => $bill_details,
            'success' => $bill_details->isEmpty() ? 'error' : 'bien'
        ]);
    }

    /**
     * Update quantity of a bill detail (used from orders modal).
     * Also recalculates and returns the updated bill totals.
     */
    public function updateQuantityOrder(Request $request)
    {
        $detail = Bill_detail::find($request->id);
        if (!$detail) {
            return response()->json(['res' => 'error', 'message' => 'Detail not found'], 404);
        }

        $quantity = intval($request->quantity);
        $total_amount = $detail->price * $quantity;
        $discount = 0;
        if ($detail->discount_percent != 0) {
            $discount = $total_amount * ($detail->discount_percent / 100);
            $net_amount = $total_amount - $discount;
        } else {
            $net_amount = $total_amount;
        }

        $detail->quantity = $quantity;
        $detail->total_amount = $total_amount;
        $detail->discount = $discount;
        $detail->net_amount = $net_amount;
        $detail->save();

        // Recalc parent bill totals
        $billTotals = Bill_detail::selectRaw('SUM(total_amount) as total_amount,SUM(discount) as discount,SUM(net_amount) as net_amount')
            ->where('id_bill', $detail->id_bill)
            ->first();

        $bill = Bill::find($detail->id_bill);
        if ($bill) {
            $bill->total_amount = $billTotals->total_amount ?? 0;
            $bill->discount = $billTotals->discount ?? 0;
            $bill->net_amount = $billTotals->net_amount ?? 0;
            $bill->save();
        }

        return response()->json([
            'res' => 'bien',
            'detail' => $detail,
            'bill_totals' => [
                'total_amount' => $bill->total_amount ?? 0,
                'discount' => $bill->discount ?? 0,
                'net_amount' => $bill->net_amount ?? 0,
            ],
        ]);
    }

    /**
     * Delete a bill detail from an order and recalculate totals.
     */
    public function deleteDetailOrder(Request $request)
    {
        $detail = Bill_detail::find($request->id);
        if (!$detail) {
            return response()->json(['res' => 'error', 'message' => 'Detail not found'], 404);
        }
        $billId = $detail->id_bill;
        $detail->delete();

        // Recalc parent bill totals
        $billTotals = Bill_detail::selectRaw('SUM(total_amount) as total_amount,SUM(discount) as discount,SUM(net_amount) as net_amount')
            ->where('id_bill', $billId)
            ->first();

        $bill = Bill::find($billId);
        if ($bill) {
            $bill->total_amount = $billTotals->total_amount ?? 0;
            $bill->discount = $billTotals->discount ?? 0;
            $bill->net_amount = $billTotals->net_amount ?? 0;
            $bill->save();
        }

        return response()->json([
            'res' => 'bien',
            'bill_totals' => [
                'total_amount' => $bill->total_amount ?? 0,
                'discount' => $bill->discount ?? 0,
                'net_amount' => $bill->net_amount ?? 0,
            ],
        ]);
    }


}
