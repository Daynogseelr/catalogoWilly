<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\Bill_payment;
use App\Models\Bill;
use App\Models\Bill_detail;
use App\Models\Currency;
use App\Models\Repayment;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class CreditController extends Controller
{
    public function indexCredit()
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' ||  auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
            $paymentMethods = PaymentMethod::select('*')->where('status', 1)->get();
            $bills = Bill::select('id_client')
                ->when($sucursalId, function($q) use ($sucursalId) {
                    $q->where('id_sucursal', $sucursalId);
                })
                ->where('payment','!=',0)
                ->groupBy('id_client')
                ->get();
            $currencies = Currency::select('id', 'name', 'abbreviation', 'rate')->where('status', 1)->get();
            $currencyPrincipal = Currency::where('is_principal', 1)->first();
            return view('bills.credit', compact('bills', 'paymentMethods', 'currencies', 'currencyPrincipal'));
        } else {
            return redirect()->route('indexStore');
        }
    }
    public function ajaxCredit()
    {
        DB::statement("SET SQL_MODE=''");
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        $clientId = request()->get('client');
        if (request()->ajax()) {
            $clientsCredit = DB::table('clients')
                ->select(
                    'clients.id as id_client',
                    'clients.phone as phone',
                    'clients.name as clientName',  
                    'sellers.name as sellerName', 
                    'clients.nationality',
                    'clients.ci',
                    'bills.created_at',
                    'bills.code',
                    'bills.id as id',
                    'bills.creditDays as creditDays',
                    'bills.payment as payment',
                    'currencies.abbreviation as abbr',
                    'currencies.name as name',
                )
                ->join('bills', 'bills.id_client', '=', 'clients.id')
                ->join('users as sellers', 'sellers.id', '=', 'bills.id_seller')
                ->join('currencies', 'currencies.id', '=', 'bills.id_currency_bill')
                ->where('bills.type', 'CREDITO')
                ->where('bills.payment','!=', 0)
                ->when($sucursalId, function($q) use ($sucursalId) {
                    $q->where('bills.id_sucursal', $sucursalId);
                })
                ->where(function ($query) use ($clientId) {
                    if ($clientId && $clientId !== 'TODOS') {
                        $query->where('clients.id', $clientId);
                    }
                })
                ->get();
            // Para calcular el saldo pendiente en la colección:
            return datatables()->of($clientsCredit)
                ->addColumn('action', 'bills.credit-action')
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->make(true);
    
        }
        return view('indexStore');
    }
    public function storePaymentCredit(Request $request)
    {
        $bill = Bill::find($request->id_bill);
        $amount = 0;
        foreach ($request->pagos as $pago) {
            if (strpos($pago['metodoId'], 'nota_credito_') === 0) {
                // Es una nota de crédito
                $code = str_replace('nota_credito_', '', $pago['metodoId']);
                // Guarda el pago UNA SOLA VEZ con code_repayment
                Bill_payment::create([
                    'id_bill' => $bill->id,
                    'id_seller' => auth()->id(),
                    'id_sucursal' => $bill->id_sucursal,
                    'code_repayment' => $code, // Nuevo campo para el código de la nota de crédito
                    'reference' => $pago['referencia'] ?? null,
                    'amount' => $pago['montoPrincipal'], // Monto en moneda principal
                    'rate' => isset($pago['rate']) ? $pago['rate'] : 1,
                    'collection' => 'CREDITO',
                ]);

                // Actualiza todos los repayments con ese código a status 1
                Repayment::where('code', $code)->where('status', 0)->update(['status' => 1]);
            } else {
                // Pago normal
                Bill_payment::create([
                    'id_bill' => $bill->id,
                    'id_seller' => auth()->id(),
                    'id_sucursal' => $bill->id_sucursal,
                    'id_payment_method' => $pago['metodoId'],
                    'reference' => $pago['referencia'] ?? null,
                    'amount' => $pago['montoPrincipal'],
                    'rate' => isset($pago['rate']) ? $pago['rate'] : 1,
                    'collection' => 'CREDITO',
                ]);
            }
            $amount += $pago['montoPrincipal'];
        }
        $bill->payment = $bill->payment - $amount;
        if ($bill->payment < 0) {
            $bill->payment = 0; // Asegura que el pago no sea negativo
        }
        $bill->save();
        return Response()->json(['id_bill' => $bill->id]);
    }

   public function credit(Request $request)
{
    $bill = Bill::find($request->id);

    // Notas de crédito pendientes
    $repayments = Repayment::select(
        DB::raw('FORMAT(SUM(amount), 2) as amount'),
        'code'
    )
        ->where('id_client', $bill->id_client)
        ->where('status', 0)
        ->groupBy('code')
        ->get();

    // Pagos realizados (igual que en el PDF)
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
            'currencies.abbreviation as currency_abbr'
        )
        ->where('bill_payments.id_bill', $bill->id)
        ->orderBy('bill_payments.created_at')
        ->get();

    $totalPagado = $payments->sum('amount');
    $restante = $bill->net_amount - $totalPagado;

    $res = $repayments->isEmpty() ? 'notCredit' : 'credit';

    return response()->json([
        'repayments' => $repayments,
        'res' => $res,
        'amount' => round($bill->payment, 2),
        'payments' => $payments,
        'totalPagado' => $totalPagado,
        'restante' => $restante,
        'bill' => [
            'abbr_principal' => $bill->abbr_principal ?? '',
        ]
    ]);
}

    public function mostrarCredit(Request $request)
    {
        $bill = Bill::find($request->id);
        $ya = 0;
        if ($bill->payment <= 0) {
            $ya = 1;
        }
        $bill_payments = Bill_payment::select('*')
            ->where('id_bill', '=', $bill->id)
            ->get();
        $bill_paymentsSUM = Bill_payment::selectRaw('SUM(amountBs) as amountBs, SUM(amount) as amount')
            ->where('id_bill', '=', $bill->id)
            ->first();
        return Response()->json([
            'bill_payments' => $bill_payments,
            'bill_paymentsSUM' => $bill_paymentsSUM,
            'ya' => $ya,
        ]);
    }
}
