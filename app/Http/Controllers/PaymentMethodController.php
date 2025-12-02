<?php

namespace App\Http\Controllers;

use App\DataTables\PaymentMethodDataTable;
use App\Models\PaymentMethod;
use App\Models\Currency;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(PaymentMethodDataTable $dataTable)
    {
        $primaryCurrency = Currency::where('is_principal', 1)->first();
        if (!$primaryCurrency) {
            // Redirige a la gestión de monedas
            return redirect()->route('currencies.index')
                ->with('warning', 'Debe registrar una moneda principal antes de continuar.');
        }
        $currencies = Currency::where('status', 1)->get();
        return $dataTable->render('payment_methods.index', compact('currencies'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'id_currency' => 'required|exists:currencies,id',
            'bank' => 'nullable|string|max:255',
            'reference' => 'required|integer',
            'data' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ]);
        $method = PaymentMethod::create($request->all());
        return response()->json(['message' => 'Método de pago registrado', 'method' => $method]);
    }
    public function edit(PaymentMethod $payment_method)
    {
        return response()->json($payment_method);
    }
    public function update(Request $request, PaymentMethod $payment_method)
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'id_currency' => 'required|exists:currencies,id',
            'bank' => 'nullable|string|max:255',
            'reference' => 'required|integer',
            'data' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ]);
        $payment_method->update($request->all());
        return response()->json(['message' => 'Método de pago actualizado']);
    }
    public function toggleStatus(PaymentMethod $payment_method)
    {
        $payment_method->status = !$payment_method->status;
        $payment_method->save();
        return response()->json(['message' => 'Estado actualizado']);
    }
}