<?php

namespace App\Http\Controllers;

use App\DataTables\CurrencyDataTable;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller
{

    public function index(CurrencyDataTable $currencyDataTable)
    {
        return $currencyDataTable->render('currencies.index', [
            'currencyDataTable' => $currencyDataTable
        ]);
    }
    /**
     * Almacena una nueva moneda.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeCurrency(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:10|unique:currencies,abbreviation',
            'is_principal' => 'required|boolean',
            'is_official' => 'required|boolean',
            'rate' => 'required',
        ];
        $request->validate($rules);
        $rate = $request->rate;
        // Si esta moneda es de facturación, poner las demás en 0
        if ($request->is_principal == 1) {
            Currency::where('is_principal', 1)->update(['is_principal' => 0]);
            $rate = 1; // La tasa de la moneda principal siempre es 1
        }
        // Si esta moneda es de facturación, poner las demás en 0
        if ($request->is_official == 1) {
            Currency::where('is_official', 1)->update(['is_official' => 0]);
        }
        Currency::create([
            'name' => strtoupper($request->name),
            'abbreviation' => strtoupper($request->abbreviation),
            'status' => 1,
            'rate' => $rate,
            'is_principal' => $request->is_principal,
            'is_official' => $request->is_official,
        ]);
        return response()->json(['message' => 'Moneda creada exitosamente.']);
    }

    public function updateCurrency(Request $request, Currency $currency)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'abbreviation' => ['required', 'string', 'max:10', Rule::unique('currencies')->ignore($currency->id)],
            'is_principal' => 'required|boolean',
            'is_official' => 'required|boolean',
            'rate' => 'required',
        ];
        $request->validate($rules);
        $rate = $request->rate;
        // Si esta moneda es de facturación, poner las demás en 0
        if ($request->is_principal == 1) {
            Currency::where('id', '!=', $currency->id)->update(['is_principal' => 0]);
            $rate = 1;
        }
        if ($request->is_official == 1) {
            Currency::where('id', '!=', $currency->id)->update(['is_official' => 0]);;
        }
        $currency->name = strtoupper($request->name);
        $currency->abbreviation = strtoupper($request->abbreviation);
        $currency->status = $request->status ?? 1;
        $currency->rate = $rate;
        $currency->is_principal = $request->is_principal;
        $currency->is_official = $request->is_official;
        $currency->save();
        return response()->json(['message' => 'Moneda actualizada exitosamente.']);
    }
    /**
     * Muestra los datos de una moneda para edición.
     *
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\JsonResponse
     */
    public function editCurrency(Currency $currency)
    {
        return response()->json($currency);
    }
}
