<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Bill; // Assuming Employee model for company ID
use App\Models\Bill_payment;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Shopping;
use Carbon\Carbon; // For date handling

class ChartController extends Controller
{
   public function chart(Request $request)
    {
        // allow filtering by sucursal (id or name)
        $sucursalParam = $request->query('sucursal');
        if (! $sucursalParam) {
            $sucursalParam = session('selected_sucursal');
        }
        $sucursalId = null;
        if ($sucursalParam) {
            if (is_numeric($sucursalParam)) {
                $sucursalId = intval($sucursalParam);
            } else {
                $s = DB::table('sucursals')
                    ->where('name', $sucursalParam)
                    ->orWhere(DB::raw("LOWER(REPLACE(name,' ', '-'))"), strtolower(str_replace(' ', '-', $sucursalParam)))
                    ->first();
                if ($s) $sucursalId = $s->id;
            }
        }

        $query = DB::table('bill_details')
            ->join('products', 'products.id', '=', 'bill_details.id_product')
            ->join('bills', 'bills.id', '=', 'bill_details.id_bill')
            ->when($sucursalId, function($q) use ($sucursalId){
                return $q->where('bills.id_sucursal', $sucursalId);
            })
            ->select(
                DB::raw('SUM(bill_details.quantity) as total'),
                DB::raw('SUBSTRING(products.name, 1, 40) as name'), // Limit product name to 40 characters
                'products.code as code'
            )
            ->groupBy('products.id', 'products.name', 'products.code') // Group by id, original name, and code
            ->orderBy('total', 'desc')
            ->limit(10);

        $products = $query->get();

        return response()->json($products, 200);
    }

    // Method for Most Notable Clients
    public function chart2(Request $request)
    {
        $sucursalParam = $request->query('sucursal');
        if (! $sucursalParam) {
            $sucursalParam = session('selected_sucursal');
        }
        $sucursalId = null;
        if ($sucursalParam) {
            if (is_numeric($sucursalParam)) {
                $sucursalId = intval($sucursalParam);
            } else {
                $s = DB::table('sucursals')
                    ->where('name', $sucursalParam)
                    ->orWhere(DB::raw("LOWER(REPLACE(name,' ', '-'))"), strtolower(str_replace(' ', '-', $sucursalParam)))
                    ->first();
                if ($s) $sucursalId = $s->id;
            }
        }

        $query = DB::table('bills')
            ->join('clients', 'clients.id', '=', 'bills.id_client')
            ->when($sucursalId, function($q) use ($sucursalId){
                return $q->where('bills.id_sucursal', $sucursalId);
            })
            ->select(
                DB::raw('SUM(bills.net_amount) as total'), // Sum net_amount for client purchases
                'clients.name as name'
            )
            ->where('bills.status', 1) // Assuming status 1 means completed bills, adjust if needed
            ->where('bills.type','!=' ,'PRESUPUESTO') // Assuming you only count sales, adjust or remove if services/other types are included
            ->groupBy('clients.id', 'clients.name')
            ->orderBy('total', 'desc')
            ->limit(10);

        $clients = $query->get();

        return response()->json($clients, 200);
    }
    public function monthlySummary(Request $request)
    {

  
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subMonths(9)->startOfMonth(); // Últimos 10 meses (incluyendo el actual)

        $months = [];
        $currentMonth = $startDate->copy();
        while ($currentMonth->lte($endDate)) {
            $months[] = $currentMonth->format('M Y'); // Ej. "Ene 2023"
            $currentMonth->addMonth();
        }

        // Fetch Bill data (net_amount)
        $sucursalParam = $request->query('sucursal');
        if (! $sucursalParam) {
            $sucursalParam = session('selected_sucursal');
        }
        $sucursalId = null;
        if ($sucursalParam) {
            if (is_numeric($sucursalParam)) {
                $sucursalId = intval($sucursalParam);
            } else {
                $s = DB::table('sucursals')
                    ->where('name', $sucursalParam)
                    ->orWhere(DB::raw("LOWER(REPLACE(name,' ', '-'))"), strtolower(str_replace(' ', '-', $sucursalParam)))
                    ->first();
                if ($s) $sucursalId = $s->id;
            }
        }

        $billsData = Bill::whereBetween('created_at', [$startDate, $endDate->endOfMonth()])
            ->when($sucursalId, function($q) use ($sucursalId){
                return $q->where('id_sucursal', $sucursalId);
            })
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%b %Y") as month'),
                DB::raw('SUM(net_amount) as total_net_amount')
            )
            ->groupBy('month')
            ->orderBy('month') // Order by month string to ensure correct chronological order
            ->get()
            ->keyBy('month');

        // Fetch Bill Payments data (amount)
        // For bill payments, join bills to allow filtering by sucursal
        $billPaymentsData = DB::table('bill_payments')
            ->join('bills','bills.id','=','bill_payments.id_bill')
            ->when($sucursalId, function($q) use ($sucursalId){
                return $q->where('bills.id_sucursal', $sucursalId);
            })
            ->whereBetween('bill_payments.created_at', [$startDate, $endDate->endOfMonth()])
            ->select(
                DB::raw('DATE_FORMAT(bill_payments.created_at, "%b %Y") as month'),
                DB::raw('SUM(bill_payments.amount) as total_amount_paid')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');


        // Fetch Shoppings data (total)
        $shoppingsData = Shopping::when($sucursalId, function($q) use ($sucursalId){
                return $q->where('id_sucursal', $sucursalId);
            })->whereBetween('created_at', [$startDate, $endDate->endOfMonth()])
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%b %Y") as month'),
                DB::raw('SUM(total) as total_shopping_amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');


        // Initialize data series with 0 for all months
        $netAmounts = array_fill_keys($months, 0);
        $amountsPaid = array_fill_keys($months, 0);
        $shoppingTotals = array_fill_keys($months, 0);

        // Populate data
        foreach ($months as $monthKey) {
            if ($billsData->has($monthKey)) {
                $netAmounts[$monthKey] = (float) $billsData[$monthKey]->total_net_amount;
            }
            if ($billPaymentsData->has($monthKey)) {
                $amountsPaid[$monthKey] = (float) $billPaymentsData[$monthKey]->total_amount_paid;
            }
             if ($shoppingsData->has($monthKey)) {
                $shoppingTotals[$monthKey] = (float) $shoppingsData[$monthKey]->total_shopping_amount;
            }
        }


        $response = [
            'chartTitle' => __('Monthly Sales Overview'),
            'xAxisLabel' => __('Month'),
            'yAxisTitle' => __('Amount'),
            'categories' => array_values($months),
            'series' => [
                [
                    'name' => __('Net Billed Amount'),
                    'data' => array_values($netAmounts),
                    'color' => '#03DAC6' // Color for Net Billed Amount
                ],
                [
                    'name' => __('Payments Received'),
                    'data' => array_values($amountsPaid),
                    'color' => '#6200EE' // Color for Payments Received
                ],
                [
                    'name' => __('Purchases Cost'),
                    'data' => array_values($shoppingTotals),
                    'color' => '#F44336' // Color for Purchases
                ]
            ]
        ];

        return response()->json($response);
    }
 
        
}