<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // For date handling

class ReportController extends Controller // Adjust Controller name as per your project
{
    /**
     * Resolve selected sucursal id from session.
     * session('selected_sucursal') can be an id or a name/slug.
     * Returns integer id or null.
     */
    private function resolveSelectedSucursalId()
    {
        $sel = session('selected_sucursal');
        if (! $sel) return null;
        if (is_numeric($sel)) return intval($sel);
        $s = DB::table('sucursals')
            ->where('name', $sel)
            ->orWhere(DB::raw("LOWER(REPLACE(name,' ','-'))"), strtolower(str_replace(' ', '-', $sel)))
            ->first();
        return $s ? intval($s->id) : null;
    }
    public function productIndex()
    {
        // Fetch categories for the current company
        $categories = Category::get();
        return view('reports.charts.product', compact('categories'));
    }

    public function productReport(Request $request)
    {
        DB::statement("SET SQL_MODE=''");

        $form = $request->input('form', 'quantity'); // Default to quantity
        $categoryId = $request->input('category');
        $desdeYear = $request->input('desde');
        $hastaYear = $request->input('hasta');
        $month = $request->input('month');

        // Start with a base query
        $query = DB::table('bill_details')
            ->join('bills', 'bills.id', '=', 'bill_details.id_bill')
            ->join('products', 'products.id', '=', 'bill_details.id_product')
            ->where('bills.status', 1)
            ->where('bills.type', 'FACTURA');

        // Filtrar por sucursal seleccionada si aplica
        $selectedSucursalId = $this->resolveSelectedSucursalId();
        if ($selectedSucursalId) {
            $query->where('bills.id_sucursal', $selectedSucursalId);
        }

        // Apply Year Filtering
        if ($desdeYear && $hastaYear) {
            $query->whereYear('bills.created_at', '>=', $desdeYear)
                ->whereYear('bills.created_at', '<=', $hastaYear);
        } elseif ($desdeYear) { // If only "From Year" is selected, filter for that specific year
            $query->whereYear('bills.created_at', $desdeYear);
        }

        // Apply Month Filtering (only if 'From Year' and 'To Year' are the same)
        if ($desdeYear && $hastaYear && $desdeYear === $hastaYear && $month && $month !== '0') {
            $query->whereMonth('bills.created_at', $month);
        }

        // Apply Category Filtering
        if ($categoryId && $categoryId !== 'todos') {
            $query->join('add_categories', 'add_categories.id_product', '=', 'products.id')
                ->where('add_categories.id_category', $categoryId);
        }

        $chartTitle = '';
        $yAxisTitle = '';
        $aggregatedColumn = '';

        // Determine aggregation based on 'form'
        if ($form === 'quantity') {
            $query->select(
                'products.name',
                DB::raw('SUM(bill_details.quantity) as aggregated_value')
            )
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('aggregated_value')
                ->limit(10); // Top 10 most sold products by quantity
            $chartTitle = __('Most Sold Products by Quantity');
            $yAxisTitle = __('Quantity of Products');
            $aggregatedColumn = 'quantity';
        } else { // form === 'total'
            $query->select(
                'products.name',
                DB::raw('SUM(bill_details.net_amount) as aggregated_value') // Assuming net_amountBs is what you want for total profit/revenue
            )
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('aggregated_value')
                ->limit(10); // Top 10 most profitable products by total amount
            $chartTitle = __('Most Profitable Products by Total Amount');
            $yAxisTitle = __('Total Amount ($)');
            $aggregatedColumn = 'total_amount';
        }

        $results = $query->get();

        // Prepare data for Highcharts
        $categories = $results->pluck('name')->toArray();
        $data = $results->pluck('aggregated_value')->map(function ($value) {
            return (float) $value; // Ensure values are floats for Highcharts
        })->toArray();

        return response()->json([
            'categories' => $categories, // X-axis labels
            'data' => $data,             // Series data
            'chartTitle' => $chartTitle,
            'yAxisTitle' => $yAxisTitle,
            'form' => $form // Pass back the form type for client-side adjustments
        ], 200);
    }
    public function billIndex()
    {
        // Check if the authenticated user has the required roles to view this report.
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'ADMINISTRATIVO') {
            return view('reports.charts.bill'); // Return the bill report view.
        } else {
            return redirect()->route('dashboard'); // Redirect to dashboard or another appropriate route if not authorized.
        }
    }

    /**
     * Fetches bill data for charting based on applied filters.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function billReport(Request $request)
    {
        DB::statement("SET SQL_MODE=''");
        $form = $request->input('form', 'quantity');
        $billType = $request->input('type');
        $desdeYear = $request->input('desde');
        $hastaYear = $request->input('hasta');
        $month = $request->input('month');

        $query = DB::table('bills')
            ->where('status', 1);

        // Filtrar por sucursal seleccionada si aplica
        $selectedSucursalId = $this->resolveSelectedSucursalId();
        if ($selectedSucursalId) {
            $query->where('id_sucursal', $selectedSucursalId);
        }

        if ($billType && $billType !== 'todos') {
            $query->where('type', $billType);
        }

        // Apply Year Filtering (prefix created_at to avoid ambiguity when joined)
        if ($desdeYear && $hastaYear) {
            $query->whereYear('bill_payments.created_at', '>=', $desdeYear)
                ->whereYear('bill_payments.created_at', '<=', $hastaYear);
        } elseif ($desdeYear) {
            $query->whereYear('bill_payments.created_at', $desdeYear);
        }

        // Apply Month Filtering (always apply if specified, regardless of daily grouping)
        if ($month && $month !== '0') {
            $query->whereMonth('bill_payments.created_at', $month);
        }

        $chartTitle = '';
        $yAxisTitle = '';
        $xAxisLabel = '';
        $aggregatedColumn = '';

        if ($form === 'quantity') {
            $chartTitle = __('Number of Bills Issued');
            $yAxisTitle = __('Number of Bills');
            $aggregatedColumn = DB::raw('COUNT(id) as aggregated_value');
        } else { // form === 'total'
            $chartTitle = __('Total Amount of Bills');
            $yAxisTitle = __('Total Amount ($)');
            $aggregatedColumn = DB::raw('SUM(net_amount) as aggregated_value'); // Assumed 'net_amount' is in $
        }

        $categories = [];
        $data = [];

        // Determine grouping dynamically
        if ($desdeYear && $hastaYear && $desdeYear === $hastaYear && $month && $month !== '0') {
            // Group by day for a specific month within a single year
            $query->select(
                DB::raw('DAY(bill_payments.created_at) as period'),
                $aggregatedColumn
            )
                ->groupBy('period')
                ->orderBy('period');
            $xAxisLabel = __('Days of the Month');
            $chartTitle .= ' ' . __('in') . ' ' . Carbon::create(null, $month, 1)->monthName . ' ' . $desdeYear; // Enhance title

            $groupedResults = $query->get()->keyBy('period');
            $daysInMonth = Carbon::createFromDate($desdeYear, $month, 1)->daysInMonth;

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $categories[] = (string)$i;
                $data[] = isset($groupedResults[$i]) ? (float) $groupedResults[$i]->aggregated_value : 0;
            }
        } elseif ($desdeYear && $hastaYear && $desdeYear === $hastaYear) {
            // Group by month if filtering within a single year (but no specific month selected)
            $query->select(
                DB::raw('MONTH(bill_payments.created_at) as period'),
                $aggregatedColumn
            )
                ->groupBy('period')
                ->orderBy('period');
            $xAxisLabel = __('Months');
            $chartTitle .= ' ' . __('in') . ' ' . $desdeYear; // Enhance title
            $groupedResults = $query->get()->keyBy('period');

            $monthNames = [
                1 => __('January'),
                2 => __('February'),
                3 => __('March'),
                4 => __('April'),
                5 => __('May'),
                6 => __('June'),
                7 => __('July'),
                8 => __('August'),
                9 => __('September'),
                10 => __('October'),
                11 => __('November'),
                12 => __('December')
            ];

            for ($i = 1; $i <= 12; $i++) {
                $categories[] = $monthNames[$i];
                $data[] = isset($groupedResults[$i]) ? (float) $groupedResults[$i]->aggregated_value : 0;
            }
        } else {
            // Group by year if filtering across multiple years or no year/month specified
            $query->select(
                DB::raw('YEAR(bill_payments.created_at) as period'),
                $aggregatedColumn
            )
                ->groupBy('period')
                ->orderBy('period');
            $xAxisLabel = __('Years');
            $groupedResults = $query->get()->keyBy('period');

            $minYear = $groupedResults->keys()->min() ?? (new Carbon())->subYears(9)->year;
            $maxYear = $groupedResults->keys()->max() ?? (new Carbon())->year;

            if ($desdeYear) $minYear = min($minYear, (int)$desdeYear);
            if ($hastaYear) $maxYear = max($maxYear, (int)$hastaYear);

            for ($year = $minYear; $year <= $maxYear; $year++) {
                $categories[] = (string)$year;
                $data[] = isset($groupedResults[$year]) ? (float) $groupedResults[$year]->aggregated_value : 0;
            }
        }

        return response()->json([
            'categories' => $categories,
            'data' => $data,
            'chartTitle' => $chartTitle,
            'yAxisTitle' => $yAxisTitle,
            'xAxisLabel' => $xAxisLabel,
            'form' => $form
        ], 200);
    }

    public function paymentIndex()
    {
        // Fetch distinct payment method types for the current company
        $paymentMethods = PaymentMethod::select('type')
            ->distinct()
            ->get();

        return view('reports.charts.payment', compact('paymentMethods'));
    }
    public function paymentReport(Request $request)
    {
        DB::statement("SET SQL_MODE=''");


        $form = $request->input('form', 'quantity');
        $paymentMethodType = $request->input('type');
        $desdeYear = $request->input('desde');
        $hastaYear = $request->input('hasta');
        $month = $request->input('month');

        $query = DB::table('bill_payments');

        // Filtrar por sucursal seleccionada si aplica (join con bills si es necesario)
        $selectedSucursalId = $this->resolveSelectedSucursalId();
        if ($selectedSucursalId) {
            $query->join('bills as b2', 'b2.id', '=', 'bill_payments.id_bill')
                ->where('b2.id_sucursal', $selectedSucursalId);
        }

        // Apply Payment Method Type Filtering (qualify columns to avoid ambiguity)
        if ($paymentMethodType && $paymentMethodType !== 'todos') {
            $query->where('bill_payments.type', $paymentMethodType);
        }

        // Apply Year Filtering (qualify created_at to avoid ambiguous column error when joined)
        if ($desdeYear && $hastaYear) {
            $query->whereYear('bill_payments.created_at', '>=', $desdeYear)
                ->whereYear('bill_payments.created_at', '<=', $hastaYear);
        } elseif ($desdeYear) {
            $query->whereYear('bill_payments.created_at', $desdeYear);
        }

        // Apply Month Filtering (always apply if specified, regardless of daily grouping)
        if ($month && $month !== '0') {
            $query->whereMonth('bill_payments.created_at', $month);
        }

        $chartTitle = '';
        $yAxisTitle = '';
        $xAxisLabel = '';
        $aggregatedColumn = '';

        if ($form === 'quantity') {
            $chartTitle = __('Number of Payments');
            $yAxisTitle = __('Number of Payments');
            // Qualify id to avoid ambiguity
            $aggregatedColumn = DB::raw('COUNT(bill_payments.id) as aggregated_value');
        } else { // form === 'total'
            $chartTitle = __('Total Amount of Payments');
            $yAxisTitle = __('Total Amount ($)'); // Explicitly using $ for consistency with 'amount' column
            // Sum 'amount' column (USD) from bill_payments table.
            // Qualify amount and use COALESCE to handle potential NULL values, treating them as 0.
            $aggregatedColumn = DB::raw('SUM(COALESCE(bill_payments.amount, 0)) as aggregated_value');
        }

        $categories = [];
        $data = [];

        // Determine grouping dynamically
        if ($desdeYear && $hastaYear && $desdeYear === $hastaYear && $month && $month !== '0') {
            // Group by day for a specific month within a single year
            $query->select(
                DB::raw('DAY(bill_payments.created_at) as period'),
                $aggregatedColumn
            )
                ->groupBy('period')
                ->orderBy('period');
            $xAxisLabel = __('Days of the Month');
            $chartTitle .= ' ' . __('in') . ' ' . Carbon::create(null, $month, 1)->monthName . ' ' . $desdeYear;

            $groupedResults = $query->get()->keyBy('period');
            $daysInMonth = Carbon::createFromDate($desdeYear, $month, 1)->daysInMonth;

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $categories[] = (string)$i;
                $data[] = isset($groupedResults[$i]) ? (float) $groupedResults[$i]->aggregated_value : 0;
            }
        } elseif ($desdeYear && $hastaYear && $desdeYear === $hastaYear) {
            // Group by month if filtering within a single year (but no specific month selected)
            $query->select(
                DB::raw('MONTH(bill_payments.created_at) as period'),
                $aggregatedColumn
            )
                ->groupBy('period')
                ->orderBy('period');
            $xAxisLabel = __('Months');
            $chartTitle .= ' ' . __('in') . ' ' . $desdeYear;
            $groupedResults = $query->get()->keyBy('period');

            $monthNames = [
                1 => __('January'),
                2 => __('February'),
                3 => __('March'),
                4 => __('April'),
                5 => __('May'),
                6 => __('June'),
                7 => __('July'),
                8 => __('August'),
                9 => __('September'),
                10 => __('October'),
                11 => __('November'),
                12 => __('December')
            ];

            for ($i = 1; $i <= 12; $i++) {
                $categories[] = $monthNames[$i];
                $data[] = isset($groupedResults[$i]) ? (float) $groupedResults[$i]->aggregated_value : 0;
            }
        } else {
            // Group by year if filtering across multiple years or no year/month specified
            $query->select(
                DB::raw('YEAR(bill_payments.created_at) as period'),
                $aggregatedColumn
            )
                ->groupBy('period')
                ->orderBy('period');
            $xAxisLabel = __('Years');
            $groupedResults = $query->get()->keyBy('period');

            $minYear = $groupedResults->keys()->min() ?? (new Carbon())->subYears(9)->year;
            $maxYear = $groupedResults->keys()->max() ?? (new Carbon())->year;

            if ($desdeYear) $minYear = min($minYear, (int)$desdeYear);
            if ($hastaYear) $maxYear = max($maxYear, (int)$hastaYear);

            $categories = [];
            $data = [];
            for ($year = $minYear; $year <= $maxYear; $year++) {
                $categories[] = (string)$year;
                $data[] = isset($groupedResults[$year]) ? (float) $groupedResults[$year]->aggregated_value : 0;
            }
        }

        return response()->json([
            'categories' => $categories,
            'data' => $data,
            'chartTitle' => $chartTitle,
            'yAxisTitle' => $yAxisTitle,
            'xAxisLabel' => $xAxisLabel,
            'form' => $form
        ], 200);
    }
    public function creditIndex()
    {
        // Obtener solo los clientes que tienen facturas con saldo pendiente (payment > 0)
        $clientsWithDebt = User::whereHas('bills', function ($query) {
            $query->where('payment', '>', 0) // Solo facturas con saldo pendiente
                ->whereNotIn('type', ['PRESUPUESTO', 'ESPERA', 'FACTURA']); // Excluir tipos que no sean de cobro
        })
            ->get();

        return view('reports.charts.credit', compact('clientsWithDebt'));
    }
    public function creditReport(Request $request)
    {
        DB::statement("SET SQL_MODE=''"); // Necesario para algunas versiones de MySQL con GROUP BY

        $clientFilter = $request->input('client_filter');
        $desdeYear = $request->input('desde');
        $hastaYear = $request->input('hasta');
        $month = $request->input('month');

        $query = Bill::where('payment', '>', 0) // Solo facturas con saldo pendiente
            ->whereNotIn('type', ['PRESUPUESTO', 'ESPERA', 'FACTURA']); // Excluir tipos que no sean de cobro

        // Filtrar por sucursal seleccionada si aplica
        $selectedSucursalId = $this->resolveSelectedSucursalId();
        if ($selectedSucursalId) {
            $query->where('id_sucursal', $selectedSucursalId);
        }

        // Apply client filter
        if ($clientFilter && $clientFilter !== 'TODOS') {
            $query->where('id_client', $clientFilter);
        }

        // Apply Year Filtering
        if ($desdeYear && $hastaYear) {
            $query->whereYear('created_at', '>=', $desdeYear)
                ->whereYear('created_at', '<=', $hastaYear);
        } elseif ($desdeYear) {
            $query->whereYear('created_at', $desdeYear);
        }

        // Apply Month Filtering (always apply if specified, regardless of daily grouping)
        if ($month && $month !== '0') {
            $query->whereMonth('created_at', $month);
        }

        $chartTitle = __('Outstanding Balance Report');
        $yAxisTitle = __('Outstanding Balance') . ' ($)';
        $xAxisLabel = '';
        $aggregatedColumn = DB::raw('SUM(payment) as aggregated_value'); // Suma el campo 'payment'

        $categories = [];
        $data = [];

        // Determine grouping dynamically
        if ($desdeYear && $hastaYear && $desdeYear === $hastaYear && $month && $month !== '0') {
            // Group by day for a specific month within a single year
            $query->select(
                DB::raw('DAY(created_at) as period'),
                $aggregatedColumn
            )
                ->groupBy('period')
                ->orderBy('period');
            $xAxisLabel = __('Days of the Month');
            $chartTitle .= ' ' . __('in') . ' ' . Carbon::create(null, $month, 1)->monthName . ' ' . $desdeYear; // Enhance title

            $groupedResults = $query->get()->keyBy('period');
            $daysInMonth = Carbon::createFromDate($desdeYear, $month, 1)->daysInMonth;

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $categories[] = (string)$i;
                $data[] = isset($groupedResults[$i]) ? (float) $groupedResults[$i]->aggregated_value : 0;
            }
        } elseif ($desdeYear && $hastaYear && $desdeYear === $hastaYear) {
            // Group by month if filtering within a single year (but no specific month selected)
            $query->select(
                DB::raw('MONTH(created_at) as period'),
                $aggregatedColumn
            )
                ->groupBy('period')
                ->orderBy('period');
            $xAxisLabel = __('Months');
            $chartTitle .= ' ' . __('in') . ' ' . $desdeYear; // Enhance title
            $groupedResults = $query->get()->keyBy('period');

            $monthNames = [
                1 => __('January'),
                2 => __('February'),
                3 => __('March'),
                4 => __('April'),
                5 => __('May'),
                6 => __('June'),
                7 => __('July'),
                8 => __('August'),
                9 => __('September'),
                10 => __('October'),
                11 => __('November'),
                12 => __('December')
            ];

            for ($i = 1; $i <= 12; $i++) {
                $categories[] = $monthNames[$i];
                $data[] = isset($groupedResults[$i]) ? (float) $groupedResults[$i]->aggregated_value : 0;
            }
        } else {
            // Group by year if filtering across multiple years or no year/month specified
            $query->select(
                DB::raw('YEAR(created_at) as period'),
                $aggregatedColumn
            )
                ->groupBy('period')
                ->orderBy('period');
            $xAxisLabel = __('Years');
            $groupedResults = $query->get()->keyBy('period');

            // Determine min/max year for categories based on results or default range
            $minYear = $groupedResults->keys()->min();
            $maxYear = $groupedResults->keys()->max();

            // If no data, provide a default range (e.g., last 10 years)
            if (empty($minYear) || empty($maxYear)) {
                $currentYear = (new Carbon())->year;
                $minYear = $currentYear - 9;
                $maxYear = $currentYear;
            }

            // Adjust min/max year based on user input for consistent range
            if ($desdeYear) $minYear = min($minYear, (int)$desdeYear);
            if ($hastaYear) $maxYear = max($maxYear, (int)$hastaYear);


            for ($year = $minYear; $year <= $maxYear; $year++) {
                $categories[] = (string)$year;
                $data[] = isset($groupedResults[$year]) ? (float) $groupedResults[$year]->aggregated_value : 0;
            }
        }

        return response()->json([
            'categories' => $categories,
            'data' => $data,
            'chartTitle' => $chartTitle,
            'yAxisTitle' => $yAxisTitle,
            'xAxisLabel' => $xAxisLabel
        ], 200);
    }
    public function employeeIndex()
    {

        // Obtener todos los vendedores que han generado alguna factura para la compañía
        // Asumo que 'VENDEDOR' es un tipo en la tabla 'users' o se identifica por 'id_seller' en 'bills'
        // Mejor filtrar por users que son vendedores y están asociados a facturas de la compañía
        $sellers = User::where('type', '!=', 'ADMINISTRADOR')->get();
        return view('reports.charts.employee', compact('sellers'));
    }
    public function employeeReport(Request $request)
    {
        DB::statement("SET SQL_MODE=''"); // Necesario para algunas versiones de MySQL con GROUP BY
        $sellerFilter = $request->input('seller_filter');
        $metricType = $request->input('metric_type', 'total_amount'); // 'total_amount' o 'quantity'
        $desdeYear = $request->input('desde');
        $hastaYear = $request->input('hasta');
        $month = $request->input('month');
        $query = Bill::whereNotIn('type', ['PRESUPUESTO', 'ESPERA']) // Solo facturas completadas/reales
            ->whereNotNull('id_seller'); // Solo facturas asignadas a un vendedor

        // Filtrar por sucursal seleccionada si aplica
        $selectedSucursalId = $this->resolveSelectedSucursalId();
        if ($selectedSucursalId) {
            $query->where('id_sucursal', $selectedSucursalId);
        }
        // Apply seller filter
        if ($sellerFilter && $sellerFilter !== 'TODOS') {
            $query->where('id_seller', $sellerFilter);
        }
        // Apply Year Filtering
        if ($desdeYear && $hastaYear) {
            $query->whereYear('created_at', '>=', $desdeYear)
                ->whereYear('created_at', '<=', $hastaYear);
        } elseif ($desdeYear) {
            $query->whereYear('created_at', $desdeYear);
        }
        // Apply Month Filtering
        if ($month && $month !== '0') {
            $query->whereMonth('created_at', $month);
        }
        $chartTitle = '';
        $yAxisTitle = '';
        $xAxisLabel = '';
        $aggregatedColumn = '';
        if ($metricType === 'quantity') {
            $chartTitle = __('Number of Bills per Seller');
            $yAxisTitle = __('Number of Bills');
            $aggregatedColumn = DB::raw('COUNT(id) as aggregated_value');
        } else { // metricType === 'total_amount'
            $chartTitle = __('Total Sales Amount per Seller');
            $yAxisTitle = __('Total Sales Amount') . ' ($)';
            $aggregatedColumn = DB::raw('SUM(net_amount) as aggregated_value'); // Suma el monto neto de la factura
        }
        $categories = [];
        $data = [];
        // Determine grouping dynamically based on date filters or by seller if no date grouping
        if ($sellerFilter && $sellerFilter !== 'TODOS') {
            // If a specific seller is selected, group by time period
            if ($desdeYear && $hastaYear && $desdeYear === $hastaYear && $month && $month !== '0') {
                // Group by day for a specific month within a single year
                $query->select(
                    DB::raw('DAY(created_at) as period'),
                    $aggregatedColumn
                )
                    ->groupBy('period')
                    ->orderBy('period');
                $xAxisLabel = __('Days of the Month');
                $chartTitle .= ' ' . __('for') . ' ' . User::find($sellerFilter)->name . ' ' . __('in') . ' ' . Carbon::create(null, $month, 1)->monthName . ' ' . $desdeYear;
                $groupedResults = $query->get()->keyBy('period');
                $daysInMonth = Carbon::createFromDate($desdeYear, $month, 1)->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $categories[] = (string)$i;
                    $data[] = isset($groupedResults[$i]) ? (float) $groupedResults[$i]->aggregated_value : 0;
                }
            } elseif ($desdeYear && $hastaYear && $desdeYear === $hastaYear) {
                // Group by month if filtering within a single year (but no specific month selected)
                $query->select(
                    DB::raw('MONTH(created_at) as period'),
                    $aggregatedColumn
                )
                    ->groupBy('period')
                    ->orderBy('period');
                $xAxisLabel = __('Months');
                $chartTitle .= ' ' . __('for') . ' ' . User::find($sellerFilter)->name . ' ' . __('in') . ' ' . $desdeYear;
                $groupedResults = $query->get()->keyBy('period');
                $monthNames = [
                    1 => __('January'),
                    2 => __('February'),
                    3 => __('March'),
                    4 => __('April'),
                    5 => __('May'),
                    6 => __('June'),
                    7 => __('July'),
                    8 => __('August'),
                    9 => __('September'),
                    10 => __('October'),
                    11 => __('November'),
                    12 => __('December')
                ];
                for ($i = 1; $i <= 12; $i++) {
                    $categories[] = $monthNames[$i];
                    $data[] = isset($groupedResults[$i]) ? (float) $groupedResults[$i]->aggregated_value : 0;
                }
            } else {
                // Group by year if filtering across multiple years for a specific seller
                $query->select(
                    DB::raw('YEAR(created_at) as period'),
                    $aggregatedColumn
                )
                    ->groupBy('period')
                    ->orderBy('period');
                $xAxisLabel = __('Years');
                $chartTitle .= ' ' . __('for') . ' ' . User::find($sellerFilter)->name;
                $groupedResults = $query->get()->keyBy('period');
                $minYear = $groupedResults->keys()->min();
                $maxYear = $groupedResults->keys()->max();
                if (empty($minYear) || empty($maxYear)) {
                    $currentYear = (new Carbon())->year;
                    $minYear = $currentYear - 9;
                    $maxYear = $currentYear;
                }
                if ($desdeYear) $minYear = min($minYear, (int)$desdeYear);
                if ($hastaYear) $maxYear = max($maxYear, (int)$hastaYear);
                for ($year = $minYear; $year <= $maxYear; $year++) {
                    $categories[] = (string)$year;
                    $data[] = isset($groupedResults[$year]) ? (float) $groupedResults[$year]->aggregated_value : 0;
                }
            }
        } else {
            // No specific seller selected, group by seller for the selected time period
            $query->select(
                'id_seller',
                $aggregatedColumn
            )
                ->groupBy('id_seller');
            // Optionally apply date filters to this grouping
            if ($desdeYear && $hastaYear) {
                $query->whereYear('created_at', '>=', $desdeYear)
                    ->whereYear('created_at', '<=', $hastaYear);
            } elseif ($desdeYear) {
                $query->whereYear('created_at', $desdeYear);
            }
            if ($month && $month !== '0') {
                $query->whereMonth('created_at', $month);
            }
            $query->orderBy('aggregated_value', 'desc'); // Order by performance
            $xAxisLabel = __('Sellers');
            $chartTitle = __('Sales Performance by Seller');
            $groupedResults = $query->get();
            foreach ($groupedResults as $result) {
                $sellerName = User::find($result->id_seller)->name ?? __('Unknown Seller');
                $categories[] = $sellerName;
                $data[] = (float) $result->aggregated_value;
            }
        }
        return response()->json([
            'categories' => $categories,
            'data' => $data,
            'chartTitle' => $chartTitle,
            'yAxisTitle' => $yAxisTitle,
            'xAxisLabel' => $xAxisLabel,
            'metricType' => $metricType
        ], 200);
    }
}
