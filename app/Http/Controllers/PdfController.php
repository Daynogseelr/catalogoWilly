<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\User;
use App\Models\Bill;
use App\Models\Bill_detail;
use App\Models\Bill_payment;
use App\Models\Closure;
use App\Models\CompanyInfo;
use App\Models\Currency;
use App\Models\Repayment;
use App\Models\Shopping;
use App\Models\Stock;
use App\Models\InventoryAdjustment;
use App\Models\Sucursal;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\Response; // Importa Response
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Etiqueta\Etiqueta;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer;
use Endroid\QrCode\Writer\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Dompdf\Dompdf;
use Dompdf\Options;

use TCPDF;

use Illuminate\Support\Facades\DB;

class PdfController extends Controller
{
    public function pdf($id)
    {
        //$pdf = PDF::loadHTML('<h1>Hola mi cachorrito PRO</h1>');
        DB::statement("SET SQL_MODE=''");
        $bill = DB::table('bills')
            ->join('clients as clients', 'clients.id', '=', 'bills.id_client')
            ->join('users as sellers', 'sellers.id', '=', 'bills.id_seller')
            ->select(
                DB::raw('DATE(bills.created_at) as date'),
                DB::raw('TIME(bills.created_at) as time'),
                'bills.id_sucursal as id_sucursal',
                'bills.id as id',
                'bills.code as code',
                'bills.type as type',
                'bills.id_currency_principal',
                'bills.id_currency_official',
                'bills.total_amount',
                'bills.discount',
                'bills.net_amount',
                'bills.rate_bill',
                'bills.rate_official',
                'bills.abbr_bill',
                'bills.abbr_official',
                'bills.abbr_principal',
                'clients.name as clientName',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'clients.phone as phone',
                'clients.direction as direction',
                'sellers.name as sellerName',
            )
            ->where('bills.id', $id)
            ->first();
       // Trae los detalles con el campo iva
        $bill_details = Bill_detail::select(
            'name',
            'id',
            'price',
            'priceU',
            'net_amount',
            'quantity',
            'iva'
        )
        ->where('id_bill', $id)
        ->get();
        $bill_details_count = count($bill_details);
        $heightProduct = $bill_details_count * 44;
        $height = 330 + $heightProduct;

        // Calcula BI, IVA y Exento
        $bi = 0;
        $iva = 0;
        $exento = 0;
        foreach ($bill_details as $detail) {
            $monto = $detail->net_amount * $bill->rate_official;
            if ($detail->iva == 1) {
                $bi += $monto / 1.16;
                $iva += $monto - ($monto / 1.16);
            } else {
                $exento += $monto;
            }
        }
        // Usar la sucursal asociada a la factura; si no existe, fallback a la primera sucursal
        $company = null;
        if (!empty($bill->id_sucursal)) {
            $company = Sucursal::find($bill->id_sucursal);
        }
        if (!$company) {
            $company = Sucursal::first();
        }
        // Traer los pagos asociados a la factura (si los hay)
        // JOIN con `payment_methods` y `currencies` para obtener la abreviatura
        // de la moneda del método de pago y evitar acceso a relaciones inexistentes
        $payments = DB::table('bill_payments')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'bill_payments.id_payment_method')
            ->leftJoin('currencies', 'currencies.id', '=', 'payment_methods.id_currency')
            ->select(
                'bill_payments.*',
                'payment_methods.type as payment_type',
                'payment_methods.id as payment_method_id',
                'payment_methods.id_currency as payment_method_currency_id',
                'currencies.abbreviation as payment_currency_abbr'
            )
            ->where('bill_payments.id_bill', $id)
            ->get();

        $pdf = PDF::setPaper([0, 0, 210, $height])->loadView('pdf.bill', [
            'bill' => $bill,
            'bi' => round($bi, 2),
            'iva' => round($iva, 2),
            'exento' => round($exento, 2),
            'bill_details' => $bill_details,
            'company' => $company,
            'payments' => $payments,
        ]);
        return $pdf->stream();
    }
    
    public function pdfLast()
    {
        $userId = auth()->id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }
        // buscar la última factura creada por el usuario logueado
        $lastBill = DB::table('bills')
            ->where('id_seller', $userId)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->select('id')
            ->first();
        if (!$lastBill) {
            abort(404, 'No hay facturas para este usuario.');
        }
        
        return $this->pdf($lastBill->id);
    }
    public function pdfNoteCredit($code)
    {
        //$pdf = PDF::loadHTML('<h1>Hola mi cachorrito PRO</h1>');
        DB::statement("SET SQL_MODE=''");
        $bill = DB::table('bills')
            ->join('clients as clients', 'clients.id', '=', 'bills.id_client')
            ->join('users as sellers', 'sellers.id', '=', 'bills.id_seller')
            ->join('repayments as repayments', 'repayments.id_bill', '=', 'bills.id')
            ->select(
                DB::raw('FORMAT(SUM(repayments.amount), 2) as total'),
                DB::raw('DATE(repayments.created_at) as date'),
                DB::raw('TIME(repayments.created_at) as time'),
                'bills.id as id',
                'bills.code as codeBill',
                'bills.type as type',
                'repayments.code as codeRepayment',
                'repayments.rate_official as rate_official',
                'repayments.abbr_official as abbr_official',
                'repayments.abbr_principal as abbr_principal',
                'clients.name as clientName',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'clients.phone as phone',
                'clients.direction as direction',
                'sellers.name as sellerName',
            )
            ->where('repayments.code', $code)
            ->groupBy('repayments.code')
            ->first();
        $total =  floatval(str_replace(',', '', $bill->total *  $bill->rate_official));
        $bi = $total / 1.16;
        $iva = $total - $bi;
        $pdf = PDF::setPaper([0, 0, 226.77, 300])->loadView('pdf.noteCredit', [
            'bill' => $bill,
            'bi' =>   round($bi, 2),
            'iva' =>   round($iva, 2),
        ]);
        return $pdf->stream();
    }
    public function pdfClosure($id)
    {
        // Traer el cierre
        $closure = DB::table('closures')
            ->join('users', 'closures.id_seller', '=', 'users.id')
            ->select(
                'closures.*',
                'users.name as sellerName',
                DB::raw('DATE(closures.created_at) as date'),
                DB::raw('TIME(closures.created_at) as time')
            )
            ->where('closures.id', $id)
            ->first();
        if ($closure->type == 'GLOBAL') {
            // 1) FACTURACIÓN (agrupado por moneda y tipo)
            $bills = DB::table('bills')
                ->join('currencies', 'bills.id_currency_bill', '=', 'currencies.id')
                ->where('bills.id_closure', $id)
                ->select(
                    'bills.type',
                    'bills.id_currency_bill',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.rate_bill',
                    DB::raw('SUM(bills.net_amount * bills.rate_bill) as total'),
                    DB::raw('SUM(bills.net_amount) as total_base')
                )
                ->groupBy('bills.type', 'bills.id_currency_bill', 'currencies.abbreviation', 'currencies.name', 'bills.rate_bill')
                ->get();

            $totalBill = $bills->sum('total');

            // 2) PAGOS (collection CONTADO, agrupado por moneda y tipo de pago)
            $payments = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->where('bill_payments.id_closure', $id)
                ->where('bill_payments.collection', 'CONTADO')
                ->select(
                    'payment_methods.type as payment_type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bill_payments.rate',
                    DB::raw('SUM(bill_payments.amount * bill_payments.rate) as total'),
                    DB::raw('SUM(bill_payments.amount) as total_base')
                )
                ->groupBy('payment_methods.type', 'currencies.abbreviation', 'currencies.name', 'bill_payments.rate')
                ->get();

            $totalPayments = $payments->sum('total');

            // 3) COBRANZAS (collection CREDITO, agrupado por moneda y tipo de pago)
            $collections = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->where('bill_payments.id_closure', $id)
                ->where('bill_payments.collection', 'CREDITO')
                ->select(
                    'payment_methods.type as payment_type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bill_payments.rate',
                    DB::raw('SUM(bill_payments.amount * bill_payments.rate) as total'),
                    DB::raw('SUM(bill_payments.amount) as total_base')
                )
                ->groupBy('payment_methods.type', 'currencies.abbreviation', 'currencies.name', 'bill_payments.rate')
                ->get();

            $totalCollections = $collections->sum('total');

            // 4) DEVOLUCIONES (agrupado por moneda y status)
            $repayments = DB::table('repayments')
                ->join('currencies', 'repayments.id_currency', '=', 'currencies.id')
                ->where('repayments.id_closure', $id)
                ->select(
                    'repayments.status',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'repayments.rate',
                    DB::raw('SUM(repayments.amount * repayments.rate) as total'),
                    DB::raw('SUM(repayments.amount) as total_base')
                )
                ->groupBy('repayments.status', 'currencies.abbreviation', 'currencies.name', 'repayments.rate')
                ->get();

            $totalRepayments = $repayments->sum('total');

            // 5) Caja chica
             $smallBox = DB::table('small_boxes')
                ->join('currencies', 'small_boxes.id_currency', '=', 'currencies.id')
                ->where('id_closure', $id)
                ->select(
                    'currencies.abbreviation as abbr',
                    DB::raw('SUM(small_boxes.cash) as total')
                )
                ->groupBy('currencies.abbreviation')
                ->get();
        } else {
            // 1) FACTURACIÓN (agrupado por moneda y tipo)
            $bills = DB::table('bills')
                ->join('currencies', 'bills.id_currency_bill', '=', 'currencies.id')
                ->where('bills.id_closureI', $id)
                ->select(
                    'bills.type',
                    'bills.id_currency_bill',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.rate_bill',
                    DB::raw('SUM(bills.net_amount * bills.rate_bill) as total'),
                    DB::raw('SUM(bills.net_amount) as total_base')
                )
                ->groupBy('bills.type', 'bills.id_currency_bill', 'currencies.abbreviation', 'currencies.name', 'bills.rate_bill')
                ->get();

            $totalBill = $bills->sum('total');

            // 2) PAGOS (collection CONTADO, agrupado por moneda y tipo de pago)
            $payments = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->where('bill_payments.id_closureI', $id)
                ->where('bill_payments.collection', 'CONTADO')
                ->select(
                    'payment_methods.type as payment_type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bill_payments.rate',
                    DB::raw('SUM(bill_payments.amount * bill_payments.rate) as total'),
                    DB::raw('SUM(bill_payments.amount) as total_base')
                )
                ->groupBy('payment_methods.type', 'currencies.abbreviation', 'currencies.name', 'bill_payments.rate')
                ->get();

            $totalPayments = $payments->sum('total');

            // 3) COBRANZAS (collection CREDITO, agrupado por moneda y tipo de pago)
            $collections = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->where('bill_payments.id_closureI', $id)
                ->where('bill_payments.collection', 'CREDITO')
                ->select(
                    'payment_methods.type as payment_type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bill_payments.rate',
                    DB::raw('SUM(bill_payments.amount * bill_payments.rate) as total'),
                    DB::raw('SUM(bill_payments.amount) as total_base')
                )
                ->groupBy('payment_methods.type', 'currencies.abbreviation', 'currencies.name', 'bill_payments.rate')
                ->get();

            $totalCollections = $collections->sum('total');

            // 4) DEVOLUCIONES (agrupado por moneda y status)
            $repayments = DB::table('repayments')
                ->join('currencies', 'repayments.id_currency', '=', 'currencies.id')
                ->where('repayments.id_closureI', $id)
                ->select(
                    'repayments.status',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'repayments.rate',
                    DB::raw('SUM(repayments.amount * repayments.rate) as total'),
                    DB::raw('SUM(repayments.amount) as total_base')
                )
                ->groupBy('repayments.status', 'currencies.abbreviation', 'currencies.name', 'repayments.rate')
                ->get();

            $totalRepayments = $repayments->sum('total');

            // 5) Caja chica
             $smallBox = DB::table('small_boxes')
                ->join('currencies', 'small_boxes.id_currency', '=', 'currencies.id')
                ->where('id_closureIndividual', $id)
                ->select(
                    'currencies.abbreviation as abbr',
                    DB::raw('SUM(small_boxes.cash) as total')
                )
                ->groupBy('currencies.abbreviation')
                ->get();
        }
           // Totales por tipo de IVA para los bill_details del cierre (convertidos a moneda local usando rate_bill)
        $closureField = ($closure->type == 'GLOBAL') ? 'id_closure' : 'id_closureI';
        $detailsTotals = DB::table('bill_details')
            ->join('bills', 'bill_details.id_bill', '=', 'bills.id')
            ->where('bills.' . $closureField, $id)
            ->select(
                DB::raw('SUM(CASE WHEN bill_details.iva = 0 THEN bill_details.net_amount * bills.rate_bill ELSE 0 END) as exento'),
                DB::raw('SUM(CASE WHEN bill_details.iva = 1 THEN bill_details.net_amount * bills.rate_bill ELSE 0 END) as base16')
            )
            ->first();

        $exento_total = floatval($detailsTotals->exento ?? 0);
        $base16_total = floatval($detailsTotals->base16 ?? 0);
        $iva_total = round($base16_total * 0.16, 2);

        return PDF::loadView('pdf.closure', [
            'closure' => $closure,
            'bills' => $bills,
            'totalBill' => $totalBill,
            'payments' => $payments,
            'totalPayments' => $totalPayments,
            'collections' => $collections,
            'totalCollections' => $totalCollections,
            'repayments' => $repayments,
            'totalRepayments' => $totalRepayments,
            'smallBox' => $smallBox,
            'exento_total' => $exento_total ?? 0,
            'base16_total' => $base16_total ?? 0,
            'iva_total' => $iva_total ?? 0,
        ])->setPaper([0, 0, 226.77, 800])->stream('cierre.pdf');
    }

    public function pdfClosureDetail($id)
    {
        // Traer el cierre
        $closure = DB::table('closures')
            ->join('users', 'closures.id_seller', '=', 'users.id')
            ->select(
                'closures.*',
                'users.name as sellerName',
                DB::raw('DATE(closures.created_at) as date'),
                DB::raw('TIME(closures.created_at) as time')
            )
            ->where('closures.id', $id)
            ->first();
        if ($closure->type == 'GLOBAL') {
            // 1) FACTURACIÓN (detalles agrupados por moneda y tipo)
            $bills = DB::table('bills')
                ->join('currencies', 'bills.id_currency_bill', '=', 'currencies.id')
                ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
                ->join('clients as clients', 'bills.id_client', '=', 'clients.id')
                ->where('bills.id_closure', $id)
                ->select(
                    'bills.id',
                    'bills.code',
                    'bills.type',
                    'bills.id_currency_bill',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.rate_bill',
                    'bills.total_amount',
                    'bills.discount',
                    'bills.net_amount',
                    'sellers.name as seller_name',
                    'clients.name as client_name',
                )
                ->orderBy('bills.id_currency_bill')
                ->orderBy('bills.type')
                ->get();

            $billsGrouped = $bills->groupBy(['abbr', 'type']);

            // 2) PAGOS (CONTADO, detalles agrupados por moneda y tipo)
            $bill_paymentContado = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->join('bills', 'bill_payments.id_bill', '=', 'bills.id')
                ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
                ->join('clients as clients', 'bills.id_client', '=', 'clients.id')
                ->where('bill_payments.id_closure', $id)
                ->where('bill_payments.collection', 'CONTADO')
                ->select(
                    'bill_payments.id',
                    'bill_payments.amount',
                    'bill_payments.reference',
                    'bill_payments.rate',
                    'payment_methods.type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.code as bill_code',
                    'sellers.name as seller_name',
                    'clients.name as client_name',
                )
                ->orderBy('currencies.id')
                ->orderBy('payment_methods.type')
                ->get();

            $paymentsGrouped = $bill_paymentContado->groupBy(['abbr', 'type']);

            // 3) COBRANZAS (CREDITO, detalles agrupados por moneda y tipo)
            $bill_paymentCredito = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->join('bills', 'bill_payments.id_bill', '=', 'bills.id')
                ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
                ->join('clients as clients', 'bills.id_client', '=', 'clients.id')
                ->where('bill_payments.id_closure', $id)
                ->where('bill_payments.collection', 'CREDITO')
                ->select(
                    'bill_payments.id',
                    'bill_payments.amount',
                    'bill_payments.reference',
                    'bill_payments.rate',
                    'payment_methods.type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.code as bill_code',
                    'sellers.name as seller_name',
                    'clients.name as client_name',
                )
                ->orderBy('currencies.id')
                ->orderBy('payment_methods.type')
                ->get();

            $collectionsGrouped = $bill_paymentCredito->groupBy(['abbr', 'type']);

            // 4) DEVOLUCIONES (detalles agrupados por moneda y status)
            $repayments = DB::table('repayments')
                ->join('currencies', 'repayments.id_currency', '=', 'currencies.id')
                ->join('bills', 'repayments.id_bill', '=', 'bills.id')
                ->join('products', 'repayments.id_product', '=', 'products.id')
                ->where('repayments.id_closure', $id)
                ->select(
                    'repayments.id',
                    'repayments.amount',
                    'repayments.rate',
                    'repayments.status',
                    'repayments.quantity',
                    'repayments.code',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.code as bill_code',
                    'products.code as product_code',
                    'products.name as product_name'
                )
                ->orderBy('currencies.id')
                ->orderBy('repayments.status')
                ->get();

            $repaymentsGrouped = $repayments->groupBy(['abbr', 'status']);

            // 5) Caja chica
            $smallBox = DB::table('small_boxes')
                ->join('users as empleados', 'small_boxes.id_employee', '=', 'empleados.id')
                ->join('currencies', 'small_boxes.id_currency', '=', 'currencies.id')
                ->where('id_closure', $id)
                ->select(
                    'empleados.id',
                    'empleados.name as employee_name',
                    'currencies.abbreviation as abbr',
                    DB::raw('SUM(small_boxes.cash) as total')
                )
                ->groupBy('empleados.id', 'empleados.name', 'currencies.abbreviation')
                ->get();
        } else {
            // 1) FACTURACIÓN (detalles agrupados por moneda y tipo)
            $bills = DB::table('bills')
                ->join('currencies', 'bills.id_currency_bill', '=', 'currencies.id')
                ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
                ->join('clients as clients', 'bills.id_client', '=', 'clients.id')
                ->where('bills.id_closureI', $id)
                ->select(
                    'bills.id',
                    'bills.code',
                    'bills.type',
                    'bills.id_currency_bill',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.rate_bill',
                    'bills.total_amount',
                    'bills.discount',
                    'bills.net_amount',
                    'sellers.name as seller_name',
                    'clients.name as client_name',
                )
                ->orderBy('bills.id_currency_bill')
                ->orderBy('bills.type')
                ->get();

            // Agrupación para la vista
            $billsGrouped = $bills->groupBy(['abbr', 'type']);

            // 2) PAGOS (CONTADO, detalles agrupados por moneda y tipo)
            $bill_paymentContado = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->join('bills', 'bill_payments.id_bill', '=', 'bills.id')
                ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
                ->join('clients as clients', 'bills.id_client', '=', 'clients.id')
                ->where('bill_payments.id_closureI', $id)
                ->where('bill_payments.collection', 'CONTADO')
                ->select(
                    'bill_payments.id',
                    'bill_payments.amount',
                    'bill_payments.reference',
                    'bill_payments.rate',
                    'payment_methods.type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.code as bill_code',
                    'sellers.name as seller_name',
                    'clients.name as client_name',
                )
                ->orderBy('currencies.id')
                ->orderBy('payment_methods.type')
                ->get();

            $paymentsGrouped = $bill_paymentContado->groupBy(['abbr', 'type']);

            // 3) COBRANZAS (CREDITO, detalles agrupados por moneda y tipo)
            $bill_paymentCredito = DB::table('bill_payments')
                ->join('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
                ->join('currencies', 'payment_methods.id_currency', '=', 'currencies.id')
                ->join('bills', 'bill_payments.id_bill', '=', 'bills.id')
                ->join('users as sellers', 'bills.id_seller', '=', 'sellers.id')
                ->join('clients as clients', 'bills.id_client', '=', 'clients.id')
                ->where('bill_payments.id_closureI', $id)
                ->where('bill_payments.collection', 'CREDITO')
                ->select(
                    'bill_payments.id',
                    'bill_payments.amount',
                    'bill_payments.reference',
                    'bill_payments.rate',
                    'payment_methods.type',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.code as bill_code',
                    'sellers.name as seller_name',
                    'clients.name as client_name',
                )
                ->orderBy('currencies.id')
                ->orderBy('payment_methods.type')
                ->get();

            $collectionsGrouped = $bill_paymentCredito->groupBy(['abbr', 'type']);

            // 4) DEVOLUCIONES (detalles agrupados por moneda y status)
            $repayments = DB::table('repayments')
                ->join('currencies', 'repayments.id_currency', '=', 'currencies.id')
                ->join('bills', 'repayments.id_bill', '=', 'bills.id')
                ->join('products', 'repayments.id_product', '=', 'products.id')
                ->where('repayments.id_closureI', $id)
                ->select(
                    'repayments.id',
                    'repayments.amount',
                    'repayments.rate',
                    'repayments.status',
                    'repayments.quantity',
                    'repayments.code',
                    'currencies.abbreviation as abbr',
                    'currencies.name as currency_name',
                    'bills.code as bill_code',
                    'products.code as product_code',
                    'products.name as product_name'
                )
                ->orderBy('currencies.id')
                ->orderBy('repayments.status')
                ->get();

            $repaymentsGrouped = $repayments->groupBy(['abbr', 'status']);

            // 5) Caja chica
            $smallBox = DB::table('small_boxes')
                ->join('users as empleados', 'small_boxes.id_employee', '=', 'empleados.id')
                ->join('currencies', 'small_boxes.id_currency', '=', 'currencies.id')
                ->where('id_closureIndividual', $id)
                ->select(
                    'empleados.name as employee_name',
                    'currencies.abbreviation as abbr',
                    DB::raw('SUM(small_boxes.cash) as total')
                )
                ->groupBy('empleados.id', 'empleados.name','currencies.abbreviation')
                ->get();
        }

        return PDF::loadView('pdf.closureDetail', [
            'closure' => $closure,
            'billsGrouped' => $billsGrouped,
            'bill_paymentContadoGrouped' => $paymentsGrouped,
            'bill_paymentCreditoGrouped' => $collectionsGrouped,
            'repaymentsGrouped' => $repaymentsGrouped,
            'smallBox' => $smallBox,
        ])->setPaper('letter', 'portrait')->stream('cierre_detallado.pdf');
    }

    public function pdfStock($id_inventory_adjustment)
    {
        try {
            DB::statement("SET SQL_MODE=''");
            // Consulta principal para obtener los stocks
            $stock = Stock::select('stocks.*', 'products.name as product_name', 'products.code as product_code', 'products.price as product_price')
                ->join('products', 'stocks.id_product', '=', 'products.id')
                ->where('stocks.id_inventory_adjustment', $id_inventory_adjustment)
                ->get();

            if ($stock->isEmpty()) {
                return response()->json(['message' => 'No se encontraron registros de stock para este ajuste.'], 404);
            }

            // Obtener datos del usuario
            $user = User::find($stock->first()->id_user);
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado.'], 404);
            }

            // Calcular diferencias y montos
            $amount_lost = 0;
            $amount_profit = 0;
            // Obtener la sucursal del ajuste para comparar stocks dentro de la misma sucursal
            $adjustment = InventoryAdjustment::find($id_inventory_adjustment);
            $sucursalId = $adjustment->id_sucursal ?? null;

            $stockConDiferencia = $stock->map(function ($item) use (&$amount_lost, &$amount_profit, $sucursalId) {
                // Stock anterior dentro de la misma sucursal
                $stockAnterior = Stock::where('id_product', $item->id_product)
                    ->when($sucursalId, function($q) use ($sucursalId) {
                        $q->where('id_sucursal', $sucursalId);
                    })
                    ->where('id', '<', $item->id)
                    ->orderBy('id', 'desc')
                    ->first();
                $diferencia = $item->quantity;
                if ($stockAnterior) {
                    $diferencia = $item->quantity - $stockAnterior->quantity;
                }
                $monto = abs($diferencia) * $item->product_price;
                if ($diferencia < 0) $amount_lost += $monto;
                if ($diferencia > 0) $amount_profit += $monto;
                return [
                    'id' => $item->id,
                    'product_code' => $item->product_code,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'diferencia' => $diferencia,
                    'product_price' => $item->product_price,
                    'monto' => $monto,
                ];
            });

            $total = $amount_profit - $amount_lost;

            $pdf = PDF::loadView('pdf.stock', [
                'user' => $user,
                'stock' => $stockConDiferencia,
                'amount_lost' => $amount_lost,
                'amount_profit' => $amount_profit,
                'total' => $total,
                'ajuste_id' => $id_inventory_adjustment
            ]);
            $pdf->setPaper('letter', 'portrait');
            return $pdf->stream('ajuste_inventario.pdf');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener datos del stock: ' . $e->getMessage()], 500);
        }
    }
    public function pdfLabel($id, $quantity)
    {
        try {
            DB::statement("SET SQL_MODE=''");
            $product = Product::find($id);

            $generator = new BarcodeGeneratorPNG();
            $barcodePNG = base64_encode($generator->getBarcode($product->code, $generator::TYPE_CODE_128, 2, 30));

            // Lectura de parámetros opcionales: format, price_type y sucursal
            $format = request()->query('format', 'compact');
            $priceType = request()->query('price_type', 'price');
            $sucursal = request()->query('sucursal', null);

            // Obtener precio a mostrar según tipo seleccionado
            $price = null;
            if ($priceType === 'detal') {
                $price = $product->price_detal;
            } elseif ($priceType === 'price2') {
                $price = $product->price2;
            } elseif ($priceType === 'price3') {
                $price = $product->price3;
            } else {
                $price = $product->price;
            }

            // Ajustar precio según porcentaje de la sucursal seleccionada en sesión/consulta
            $percent = 0;
            if ($sucursal) {
                $percent = DB::table('sucursals')->where('id', $sucursal)->value('percent') ?? 0;
            }
            if ($price !== null) {
                $price = floatval($price) * (1 + floatval($percent) / 100.0);
            }

            // Si es detal, mostrar el nombre_detal si existe
            $displayName = $product->name;
            if ($priceType === 'detal' && !empty($product->name_detal)) {
                $displayName = $product->name_detal;
            }

            // Cantidad mostrada en etiqueta: si es detal, multiplicar por units
            if ($priceType === 'detal' && is_numeric($product->units)) {
                $displayQuantity = 1;
            } else {
                $displayQuantity = $product->units;
            }

            // Ajustar tamaño de papel según formato
            if ($format === 'big') {
                // Usar 80mm de ancho -> 80mm = 80/25.4 in * 72 pt/in = 226.77 pt
                $width80mm = 226.77;
                $height = 250; // altura suficiente para la etiqueta grande
                $pdf = PDF::setPaper([0, 0, $width80mm, $height], 'portrait')->loadView('pdf.label', [
                    'product' => $product,
                    'quantity' => $quantity,
                    'displayQuantity' => $displayQuantity,
                    'barcodePNG' => $barcodePNG,
                    'format' => $format,
                    'price' => $price,
                    'displayName' => $displayName,
                    'priceType' => $priceType,
                    'sucursal' => $sucursal,
                ]);
            } else {
                $height = ($quantity * 70);
                $pdf = PDF::setPaper([0, 0, 145.73, 52.52])->loadView('pdf.label', [
                    'product' => $product,
                    'quantity' => $quantity,
                    'displayQuantity' => $displayQuantity,
                    'barcodePNG' => $barcodePNG,
                    'format' => $format,
                    'price' => $price,
                    'displayName' => $displayName,
                    'priceType' => $priceType,
                    'sucursal' => $sucursal,
                ]);
            }
            return $pdf->stream();
        } catch (\Exception $e) {
            // Manejo de errores (importante para depuración)
            return response()->json(['message' => 'Error al generar etiqueta: ' . $e->getMessage()], 500);
        }
    }
    public function pdfLabelAll($code)
    {
        try {
            DB::statement("SET SQL_MODE=''");
            $format = request()->query('format', 'compact');
            $priceType = request()->query('price_type', 'price');
            $sucursal = request()->query('sucursal', null);
            // Consulta principal para obtener los stocks (filtrar por sucursal si se indicó)
            $stockQuery = Stock::select('stocks.*', 'products.name as product_name', 'products.code as product_code')
                ->join('products', 'stocks.id_product', '=', 'products.id')
                ->where('stocks.id_shopping', $code);
            if ($sucursal) {
                $stockQuery->where('stocks.id_sucursal', $sucursal);
            }
            $stock = $stockQuery->get();
            if ($stock->isEmpty()) {
                return response()->json(['message' => 'No se encontraron registros de stock para este código y compañía.'], 404);
            }
            // Obtener porcentaje de sucursal una sola vez
            $percent = 0;
            if ($sucursal) {
                $percent = DB::table('sucursals')->where('id', $sucursal)->value('percent') ?? 0;
            }

            $labels = $stock->map(function ($item) use ($priceType, $percent) {
                // Subconsulta para obtener el stock anterior
                $stockAnterior = Stock::where('id_product', $item->id_product)
                    ->where('id', '<', $item->id) // Stock con ID menor (anterior)
                    ->orderBy('id', 'desc') // Ordena descendente para obtener el más reciente
                    ->first();
                $diferencia = $item->quantity;
                if ($stockAnterior) {
                    $diferencia = $item->quantity - $stockAnterior->quantity;
                }
                if ($diferencia > 0) {
                    $generator = new BarcodeGeneratorPNG();
                    $barcodePNG = base64_encode($generator->getBarcode($item->product_code, $generator::TYPE_CODE_128, 2, 30));

                    // Obtener información del producto para aplicar price_type y name_detal
                    $product = \App\Models\Product::where('code', $item->product_code)->first();
                    $displayName = $item->product_name;
                    $displayQuantity = $diferencia;
                    if ($product) {
                        if ($priceType === 'detal' && !empty($product->name_detal)) {
                            $displayName = $product->name_detal;
                            if (is_numeric($product->units)) {
                                $displayQuantity = $diferencia * intval($product->units);
                            }
                        }
                        // Excluir si no existe precio según priceType
                        if ($priceType === 'detal' && (empty($product->price_detal) || floatval($product->price_detal) <= 0)) return null;
                        if ($priceType === 'price' && (empty($product->price) || floatval($product->price) <= 0)) return null;
                        if ($priceType === 'price2' && (empty($product->price2) || floatval($product->price2) <= 0)) return null;
                        if ($priceType === 'price3' && (empty($product->price3) || floatval($product->price3) <= 0)) return null;
                            // determinar precio a mostrar y ajustarlo por porcentaje de sucursal
                            $price = null;
                            if ($priceType === 'detal') $price = $product->price_detal;
                            elseif ($priceType === 'price2') $price = $product->price2;
                            elseif ($priceType === 'price3') $price = $product->price3;
                            else $price = $product->price;
                            if ($price !== null) {
                                $price = floatval($price) * (1 + floatval($percent) / 100.0);
                            }
                    }

                        return [
                            'code' => $item->product_code,
                            'name' => $displayName,
                            'quantity' => $displayQuantity,
                            'barcodePNG' => $barcodePNG,
                            'price' => $price ?? null,
                        ];
                }
            });
            // Filtrar nulls (productos sin precio según filtro) y reindexar
            $labels = collect($labels)->filter()->values();
            if ($labels->isEmpty()) {
                return response()->json(['message' => 'No hay etiquetas para generar con los filtros indicados.'], 404);
            }

            // Calcular tamaño de papel según formato
            $count = $labels->count();
            if ($format === 'big') {
                // Cada etiqueta grande aprox 120 de alto; ancho fijo 80mm
                $width80mm = 226.77;
                $height = max(120, $count * 120);
                $pdf = PDF::setPaper([0, 0, $width80mm, $height], 'portrait')->loadView('pdf.labelAll', [
                    'labels' => $labels,
                    'format' => $format,
                    'priceType' => $priceType,
                    'sucursal' => $sucursal,
                ]);
            } else {
                // Formato compacto: cada etiqueta aprox 52.52 de alto
                $height = max(52.52, $count * 52.52);
                $pdf = PDF::setPaper([0, 0, 145.73, $height])->loadView('pdf.labelAll', [
                    'labels' => $labels,
                    'format' => $format,
                    'priceType' => $priceType,
                    'sucursal' => $sucursal,
                ]);
            }
            return $pdf->stream();
        } catch (\Exception $e) {
            // Manejo de errores (importante para depuración)
            return response()->json(['message' => 'Error al generar etiqueta: ' . $e->getMessage()], 500);
        }
    }
    public function pdfProduct(Request $request)
    {
        if (auth()->user()->type == 'ADMINISTRADOR') {
            $id_compan = auth()->id();
        } else if (auth()->user()->type == 'EMPRESA') {
            $id_compan = auth()->id();
        } else if (auth()->user()->type == 'EMPLEADO' || auth()->user()->type == 'SUPERVISOR' || auth()->user()->type == 'ADMINISTRATIVO') {
            $id_company = Employee::select('id_company')->where('id_employee', auth()->id())->first();
            $id_compan =  $id_company->id_company;
        }
        $type = $request->input('type');
        $products = Product::from('products')
            ->joinSub(function ($query) {
                $query->from('stocks')
                    ->select('stocks.id_product', DB::raw('MAX(stocks.created_at) as ultimo_stock'))
                    ->groupBy('stocks.id_product');
            }, 'ultimo_stock', function ($join) {
                $join->on('products.id', '=', 'ultimo_stock.id_product');
            })
            ->leftJoin('stocks', function ($join) {
                $join->on('products.id', '=', 'stocks.id_product')
                    ->on('stocks.created_at', '=', 'ultimo_stock.ultimo_stock');
            })
            ->where('products.id_company', $id_compan);

        if ($type === 'TODOS') {
            $products->where('stocks.quantity', '>', 0);
        } elseif ($type === 'SINSTOCK') {
            $products->where('stocks.quantity', '=', 0);
        } elseif ($type === 'NEGATIVOS') {
            $products->where('stocks.quantity', '<', 0);
        }

        $products = $products->select('products.id', 'products.name', 'products.price', 'stocks.created_at', 'stocks.quantity', 'products.code')
            ->orderBy('products.created_at', 'desc')
            ->get(); // Get the results as a Collection

        $total = 0;
        foreach ($products as $product) {
            $total += ($product->quantity ?? 0) * $product->price;
        }

        $pdf = PDF::loadView('pdf.product', compact('products', 'total', 'type')); // Load your PDF view
        return $pdf->stream('products.pdf'); // Stream the PDF to the browser
    }
    public function pdfShopping($id)
    {
        try {
            DB::statement("SET SQL_MODE=''");
            // Consulta principal para obtener los stocks
            $stock = Stock::select('stocks.*', 'products.name as product_name', 'products.code as product_code') // Selecciona los campos necesarios
                ->join('products', 'stocks.id_product', '=', 'products.id') // Realiza el JOIN
                ->where('stocks.id_shopping', $id)
                ->get();
            if ($stock->isEmpty()) {
                return response()->json(['message' => 'No se encontraron registros de stock para este código y compañía.'], 404);
            }
            // Obtener datos de la compañía (una sola vez)
            // Obtener datos del usuario (asumiendo que todos los stocks tienen el mismo usuario, se toma el primero)
            $shopping = Shopping::find($id);
            $user = User::find($shopping->id_user);
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado.'], 404);
            }

            // Calcular la diferencia con el stock anterior
            $pdf = PDF::loadView('pdf.shopping', [
                'code' => $id,
                'user' => $user,
                'stock' => $stock,
                'shopping' => $shopping,
            ]);
            $pdf->setPaper('letter', 'portrait'); // Set paper size to letter and portrait orientation
            return $pdf->stream('campras.pdf');
        } catch (\Exception $e) {
            // Manejo de errores (importante para depuración)
            return response()->json(['message' => 'Error al obtener datos del stock: ' . $e->getMessage()], 500);
        }
    }
    public function pdfPayment($id)
    {
        DB::statement("SET SQL_MODE=''");
        $bill = DB::table('bills')
            ->join('clients as clients', 'clients.id', '=', 'bills.id_client')
            ->join('bill_payments as payments', 'payments.id_bill', '=', 'bills.id')
            ->join('payment_methods as pm', 'pm.id', '=', 'payments.id_payment_method')
            ->join('currencies as curr', 'curr.id', '=', 'pm.id_currency')
            ->select(
                DB::raw('DATE(payments.created_at) as date'),
                DB::raw('TIME(payments.created_at) as time'),
                'bills.id as id',
                'bills.code as codeBill',
                'payments.amount as amount',
                'payments.rate as rate',
                'curr.abbreviation as currency_abbr',
                'pm.type as payment_type',
                'clients.name as clientName',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'clients.phone as phone',
                'clients.direction as direction'
            )
            ->where('payments.id', $id)
            ->first();

        // Abreviatura de moneda principal
        $principal_currency = DB::table('currencies')
            ->where('is_principal', 1)
            ->value('abbreviation');

        $total = floatval($bill->amount) * floatval($bill->rate);
        $bi = $total / 1.16;
        $iva = $total - $bi;

        $pdf = PDF::setPaper([0, 0, 226.77, 300])->loadView('pdf.payment', [
            'bill' => $bill,
            'bi' => round($bi, 2),
            'iva' => round($iva, 2),
            'total' => round($total, 2),
            'principal_currency' => $principal_currency,
        ]);
        return $pdf->stream();
    }
    public function pdfCatalog(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(900);
        DB::statement("SET SQL_MODE=''");

        $productsQuery = Product::select('products.price as price', 'products.id as id', 'products.code as code', 'products.id_company as id_company', 'url1', 'name', 'status')
            ->where('status', '!=', '0')
            ->where('products.id_company', $request->id_company);

        // ... [tu lógica de filtrado por category, scope, id_inventory, etc., es la misma] ...

        if ($request->category != '' && $request->category != 'TODAS') {
            $productsQuery->join('add_categories', 'add_categories.id_product', '=', 'products.id')
                ->where('add_categories.id_category', $request->category);
        }

        if ($request->scope != '') {
            $productsQuery->where('products.name', 'like', "%$request->scope%");
        }

        $selectedInventoryId = $request->input('id_inventory');
        if ($selectedInventoryId) {
            $productsQuery->whereHas('stocks', function ($query) use ($selectedInventoryId) {
                $query->where('id_inventory', $selectedInventoryId);
            });
        }

        if ($request->has('sort_by') && in_array($request->sort_by, ['asc', 'desc', 'available', 'unavailable'])) {
            $productsQuery = $this->sortProductsByStock($productsQuery, $request->sort_by, $selectedInventoryId);
        } else {
            $productsQuery->orderByDesc('products.updated_at');
        }

        // Obtener TODOS los productos filtrados y ordenados, no paginados
        $products = $productsQuery->get(); // <-- Cambiado de paginate a get()

        // Asignar el stock de inventario a cada producto (igual que en indexStoreAjax)
        foreach ($products as $product) {
            $latestStock = DB::table('stocks')
                ->where('id_product', $product->id)
                ->when($selectedInventoryId, function ($query) use ($selectedInventoryId) {
                    return $query->where('id_inventory', $selectedInventoryId);
                })
                ->latest()
                ->first();
            $product->stock = $latestStock ? $latestStock->quantity : 0;
        }

        // Cargar la vista con TODOS los productos en un solo PDF
        $pdf = PDF::loadView('pdf.catalog', compact('products', 'dolar')); // <-- products (no productsPage)
        return $pdf->stream('catalogo.pdf'); // O download('catalogo.pdf') si quieres forzar la descarga
    }

    // Asegúrate de que esta función sea la misma que la que tienes en indexStoreAjax
    private function sortProductsByStock($products, $sortBy, $selectedInventoryId = null)
    {
        $stockSubquery = '(SELECT quantity FROM stocks
                        WHERE id_product = products.id
                        ' . ($selectedInventoryId ? 'AND id_inventory = ' . (int)$selectedInventoryId : '') . '
                        ORDER BY created_at DESC LIMIT 1)';

        $products->addSelect(DB::raw($stockSubquery . ' as stock_value'));

        switch ($sortBy) {
            case 'asc':
                $products->orderByRaw("COALESCE(" . $stockSubquery . ", 0) ASC");
                break;
            case 'desc':
                $products->orderByRaw("COALESCE(" . $stockSubquery . ", 0) DESC");
                break;
            case 'available':
                $products->whereRaw($stockSubquery . ' > 0');
                break;
            case 'unavailable':
                $products->whereRaw($stockSubquery . ' < 1');
                break;
        }

        return $products;
    }
    public function pdfCreditPayment($id)
    {
        // Trae la factura
        $bill = DB::table('bills')
            ->join('clients as clients', 'clients.id', '=', 'bills.id_client')
            ->join('users as sellers', 'sellers.id', '=', 'bills.id_seller')
            ->select(
                DB::raw('DATE(bills.created_at) as date'),
                DB::raw('TIME(bills.created_at) as time'),
                'bills.id as id',
                'bills.code as code',
                'bills.type as type',
                'bills.total_amount',
                'bills.discount',
                'bills.net_amount',
                'bills.abbr_bill',
                'bills.abbr_official',
                'bills.abbr_principal',
                'clients.name as clientName',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'clients.phone as phone',
                'clients.direction as direction',
                'sellers.name as sellerName',
            )
            ->where('bills.id', $id)
            ->first();

        // Trae los pagos realizados
        $payments = DB::table('bill_payments')
            ->leftJoin('payment_methods', 'bill_payments.id_payment_method', '=', 'payment_methods.id')
            ->leftJoin('currencies', 'currencies.id', '=', 'payment_methods.id_currency')
            ->select(
                'bill_payments.amount',
                'bill_payments.created_at',
                'bill_payments.reference',
                'bill_payments.code_repayment',
                'payment_methods.type as payment_type',
                'payment_methods.bank as payment_bank',
                'currencies.abbreviation as currency_abbr',
            )
            ->where('bill_payments.id_bill', $id)
            ->orderBy('bill_payments.created_at')
            ->get();

        $totalPagado = $payments->sum('amount');
        $restante = $bill->net_amount - $totalPagado;

        return Pdf::setPaper([0, 0, 226.77, 600])
            ->loadView('pdf.credit-payment', [
                'bill' => $bill,
                'payments' => $payments,
                'totalPagado' => $totalPagado,
                'restante' => $restante
            ])->stream('detalle_pagos_credito.pdf');
    }
    public function pdfRepaymentDetail($code)
    {
        // Trae la cabecera del repayment
        $repayment = DB::table('repayments')
            ->join('clients', 'clients.id', '=', 'repayments.id_client')
            ->join('users as sellers', 'sellers.id', '=', 'repayments.id_seller')
            ->join('bills', 'bills.id', '=', 'repayments.id_bill')
            ->select(
                'repayments.code as code',
                'repayments.created_at as created_at',
                'repayments.status as status',
                'bills.code as codeBill',
                'clients.name as clientName',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'clients.phone as phone',
                'clients.direction as direction',
                'sellers.name as sellerName',
            )
            ->where('repayments.code', $code)
            ->first();

        // Trae los productos devueltos en ese repayment
        $products = DB::table('repayments')
            ->join('products', 'products.id', '=', 'repayments.id_product')
            ->select(
                'products.name as product_name',
                'repayments.quantity',
                'repayments.amount'
            )
            ->where('repayments.code', $code)
            ->get();

        $total = $products->sum(function ($item) {
            return $item->amount * $item->quantity;
        });

        return Pdf::setPaper([0, 0, 226.77, 400])
            ->loadView('pdf.repayment-detail', [
                'repayment' => $repayment,
                'products' => $products,
                'total' => $total
            ])->stream('detalle_reembolso.pdf');
    }
}
