<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;
use App\DataTables\SucursalDataTable;

class SucursalController extends Controller
{
    // Lista con Yajra DataTable
    public function index(SucursalDataTable $dataTable)
    {
        return $dataTable->render('sucursals.sucursal');
    }

    // Store: acepta petición normal o AJAX, devuelve JSON si es AJAX
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'rif' => 'required|string|max:100|unique:sucursals,rif',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_zone' => 'nullable|string|max:50',
            'direction' => 'nullable|string|max:500',
            'percent' => 'nullable|numeric',
            'status' => 'nullable|integer',
        ]);

        $sucursal = Sucursal::create($data);

        if ($request->ajax()) {
            return response()->json(['res' => 'ok', 'sucursal' => $sucursal]);
        }

        return redirect()->route('sucursals.sucursal')->with('success', 'Sucursal creada.');
    }

    // Show: si es AJAX devuelve JSON (para rellenar modal), sino la vista show
    public function show(Sucursal $sucursal)
    {
        if (request()->ajax()) {
            return response()->json($sucursal);
        }
        return view('sucursals.sucursal', compact('sucursal'));
    }

    // Edit: fallback view (modal usa show para obtener datos)
    public function edit(Sucursal $sucursal)
    {
        return view('sucursals.sucursal', compact('sucursal'));
    }

    // Update: acepta AJAX o petición normal. También se usa para toggle de status.
    public function update(Request $request, Sucursal $sucursal)
    {
        // Si solo viene status para toggle, validamos mínimo
        if ($request->has('status') && $request->only('status') && count($request->all()) === 1) {
            $status = (int) $request->input('status', $sucursal->status);
            $sucursal->status = $status;
            $sucursal->save();
            if ($request->ajax()) {
                return response()->json(['res' => 'ok', 'sucursal' => $sucursal]);
            }
            return redirect()->route('sucursals.sucursal')->with('success', 'Estado actualizado.');
        }

        // Validación completa para actualización de datos
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'rif' => 'required|string|max:100|unique:sucursals,rif,' . $sucursal->id,
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_zone' => 'nullable|string|max:50',
            'direction' => 'nullable|string|max:500',
            'percent' => 'nullable|numeric',
            'status' => 'nullable|integer',
        ]);
        $sucursal->update($data);
        if ($request->ajax()) {
            return response()->json(['res' => 'ok', 'sucursal' => $sucursal]);
        }
        return redirect()->route('sucursals.sucursal')->with('success', 'Sucursal actualizada.');
    }
    public function setSucursal(Request $request)
    {
        $request->validate(['sucursal_id' => 'nullable|exists:sucursals,id']);

        // si null, se elimina la selección
        if (empty($request->sucursal_id)) {
            session()->forget('selected_sucursal');
        } else {
            session(['selected_sucursal' => (int)$request->sucursal_id]);
        }

        return response()->json(['success' => true]);
    }

}