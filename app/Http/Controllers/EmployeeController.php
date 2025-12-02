<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeDataTable;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function indexEmployee(EmployeeDataTable $dataTable){
        if ( auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'ADMINISTRATIVO' || auth()->user()->type == 'EMPRESA') {
            $sucursals = Sucursal::orderBy('name')->get();
            return $dataTable->render('users.employee', compact('sucursals'));
        }
        return redirect()->route('indexStore');
    }

    public function storeEmployee(Request $request){
        $employeeId = $request->id;

        $commonRules = [
            'name' => 'required|min:2|max:20|string',
            'nationality' => 'required',
            'ci' => 'required|numeric|min:1000000|max:999999999',
            'percent' => 'required|numeric|min:0|max:100',
            'user' => 'required|min:3|max:100',
            'sucursals' => 'nullable|array',
            'sucursals.*' => 'exists:sucursals,id',
        ];

        if (empty($employeeId)) {
            $rules = array_merge($commonRules, [
                'ci' => $commonRules['ci'] . '|unique:users,ci',
                'user' => $commonRules['user'] . '|unique:users,user',
                'password' => 'required|min:4|max:20',
            ]);
        } else {
            $user = User::find($employeeId);
            if (! $user) abort(404);
            $rules = array_merge($commonRules, [
                'ci' => 'required|numeric|min:1000000|max:999999999|unique:users,ci,'.$user->id,
                'user' => 'required|min:3|max:100|unique:users,user,'.$user->id,
                'password' => 'nullable|min:4|max:20',
            ]);
        }

        $request->validate($rules);

        $selectedPrices = $request->input('price', []);
        $detal  = in_array('detal', $selectedPrices) ? 1 : 0;
        $price  = in_array('price', $selectedPrices) ? 1 : 0;
        $price2 = in_array('price2', $selectedPrices) ? 1 : 0;
        $price3 = in_array('price3', $selectedPrices) ? 1 : 0;

        $dataToSave = [
            'name' => $request->name,
            'nationality' => $request->nationality,
            'ci' => $request->ci,
            'phone' => $request->phone,
            'user' => $request->user,
            'status' => '1',
            'type' => $request->type,
            'direction' => $request->direction,
            'percent' => $request->percent,
            'smallBox' => $request->smallBox,
            'detal' => $detal,
            'price' => $price,
            'price2' => $price2,
            'price3' => $price3,
        ];

        if ($request->filled('password') && $request->password !== 'PASSWORD') {
            $dataToSave['password'] = Hash::make($request->password);
        }

        $employee = User::updateOrCreate(['id' => $employeeId], $dataToSave);

        // sincronizar sucursales seleccionadas
        $sucursals = $request->input('sucursals', []);
        $employee->sucursals()->sync($sucursals);

        return response()->json($employee);
    }

    public function editEmployee(Request $request)
    {
        $employee = User::where('id', $request->id)->first();
        if (!$employee) {
            return response()->json(['error' => 'Empleado no encontrado.'], 404);
        }

        $sucursalIds = $employee->sucursals()->pluck('sucursals.id')->toArray();

        return response()->json([
            'res' => $employee,
            'sucursals' => $sucursalIds,
        ]);
    }

    public function statusEmployee(Request $request){
        $employee = User::find($request->id);
        if ($employee->status == '1') {
            $employee->update(['status' => '0']);
        } else {
            $employee->update(['status' => '1']);
        }
        return Response()->json($employee);
    }
}