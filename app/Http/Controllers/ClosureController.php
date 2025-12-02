<?php

namespace App\Http\Controllers;

use App\DataTables\IndividualClosureDataTable;
use App\DataTables\GlobalClosureDataTable;
use App\Models\Closure;
use App\Models\Bill;
use App\Models\Repayment;
use App\Models\Bill_payment;
use App\Models\SmallBox;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class ClosureController extends Controller
{
    public function indexClosure(GlobalClosureDataTable $dataTable)
    {
        if (auth()->user()->type == 'ADMINISTRADOR'  || auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            return $dataTable->render('closures.closure');
        } else {
            return redirect()->route('indexStore');
        }
    }
    public function ajaxClosure()
    {
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        if (request()->ajax()) {
            $query = Closure::select('id', DB::raw('FORMAT(bill_amount, 2) as bill_amount'), 'created_at')
                ->where('type', 'GLOBAL')
                ->when($sucursalId, function ($q) use ($sucursalId) {
                    $q->where('id_sucursal', $sucursalId);
                })
                ->orderBy('created_at', 'desc');

            return datatables()->of($query)
                ->addColumn('action', 'closures.closure-action')
                ->addColumn('formatted_created_at', function ($closure) {
                    return $closure->created_at->format('d/m/Y H:i:s'); // Adjust the format as needed
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }
        return view('index');
    }
    public function storeClosure()
    {
        // 1. Generar cierres individuales faltantes (solo usuarios de la sucursal seleccionada)
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        if ($sucursalId) {
            $userIds = DB::table('sucursal_user')->where('id_sucursal', $sucursalId)->pluck('id_user')->toArray();
            $users = User::whereIn('id', $userIds)->select('id')->get();
        } else {
            $users = User::select('id')->get();
        }

        foreach ($users as $user) {
            $bills = Bill::where('id_seller', $user->id)
                ->where('status', 1)
                ->where('id_closureI', null)
                ->when($sucursalId, function ($q) use ($sucursalId) {
                    $q->where('id_sucursal', $sucursalId);
                })->get();

            $payments = Bill_payment::where('id_seller', $user->id)
                ->where('id_closureI', null)
                ->when($sucursalId, function ($q) use ($sucursalId) {
                    $q->where('id_sucursal', $sucursalId);
                })->get();

            $repayments = Repayment::where('id_seller', $user->id)
                ->where('id_closureI', null)
                ->when($sucursalId, function ($q) use ($sucursalId) {
                    $q->where('id_sucursal', $sucursalId);
                })->get();

            $smallBoxes = SmallBox::where('id_employee', $user->id)
                ->where('id_closureIndividual', null)
                ->get();

            if ($bills->isNotEmpty() || $payments->isNotEmpty() || $repayments->isNotEmpty() || $smallBoxes->isNotEmpty()) {
                $closure = Closure::create([
                    'id_seller' => $user->id,
                    'bill_amount' => $bills->sum('net_amount'),
                    'payment_amount' => $payments->sum('amount'),
                    'repayment_amount' => $repayments->sum('amount'),
                    'small_box_amount' => $smallBoxes->sum('cash'),
                    'type' => 'INDIVIDUAL',
                    'id_sucursal' => $sucursalId,
                ]);
                // Actualiza los registros con el id del cierre
                Bill::whereIn('id', $bills->pluck('id'))->update(['id_closureI' => $closure->id]);
                Bill_payment::whereIn('id', $payments->pluck('id'))->update(['id_closureI' => $closure->id]);
                Repayment::whereIn('id', $repayments->pluck('id'))->update(['id_closureI' => $closure->id]);
                SmallBox::whereIn('id', $smallBoxes->pluck('id'))->update(['id_closureIndividual' => $closure->id]);
            }
        }
        // 2. Generar el cierre globa
        $bills = Bill::where('status', 1)
            ->where('id_closure', null)
            ->when($sucursalId, function ($q) use ($sucursalId) {
                $q->where('id_sucursal', $sucursalId);
            })->get();

        $payments = Bill_payment::where('id_closure', null)
            ->when($sucursalId, function ($q) use ($sucursalId) {
                $q->where('id_sucursal', $sucursalId);
            })->get();

        $repayments = Repayment::where('id_closure', null)
            ->when($sucursalId, function ($q) use ($sucursalId) {
                $q->where('id_sucursal', $sucursalId);
            })->get();

        $smallBoxes = SmallBox::where('id_closure', null)
           ->get();
        if ($bills->isEmpty() && $payments->isEmpty() && $repayments->isEmpty() && $smallBoxes->isEmpty()) {
            return response('mal');
        }
        $closure = Closure::create([
            'id_seller' => auth()->id(),
            'bill_amount' => $bills->sum('net_amount'),
            'payment_amount' => $payments->sum('amount'),
            'repayment_amount' => $repayments->sum('amount'),
            'small_box_amount' => $smallBoxes->sum('cash'),
            'type' => 'GLOBAL',
            'id_sucursal' => $sucursalId,
        ]);

        // Actualiza los registros con el id del cierre global
        Bill::whereIn('id', $bills->pluck('id'))->update(['id_closure' => $closure->id]);
        Bill_payment::whereIn('id', $payments->pluck('id'))->update(['id_closure' => $closure->id]);
        Repayment::whereIn('id', $repayments->pluck('id'))->update(['id_closure' => $closure->id]);
        SmallBox::whereIn('id', $smallBoxes->pluck('id'))->update(['id_closure' => $closure->id]);
        return response($closure->id);
    }
    public function indexIndividualClosure(IndividualClosureDataTable $dataTable, Request $request)
    {
        $users = collect();
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        $selected_user_id = $request->input('user_id', auth()->id());
        if (in_array(auth()->user()->type, ['SUPERVISOR', 'ADMINISTRATIVO','ADMINISTRADOR'])) {
            if ($sucursalId) {
                $userIds = DB::table('sucursal_user')->where('id_sucursal', $sucursalId)->pluck('id_user')->toArray();
                $users = User::whereIn('id', $userIds)->where('status', 1)->where('type','!=','ADMINISTRADOR')->get();
            } else {
                $users = User::where('status', 1)->where('type','!=','ADMINISTRADOR')->get();
            }
        } elseif (auth()->user()->type == 'EMPLEADO') {
            $users = collect([auth()->user()]);
            $selected_user_id = auth()->id();
        }
        return $dataTable->render('closures.closureIndividual', compact('users', 'selected_user_id'));
    }

    public function ajaxIndividualClosure(Request $request)
    {
        return app(IndividualClosureDataTable::class)->ajax();
    }

    public function storeIndividualClosure(Request $request)
    {
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        $user_id = $request->input('user_id', auth()->id());
        if (auth()->user()->type == 'EMPLEADO') {
            $user_id = auth()->id();
        }
        // Facturas
        $bills = Bill::where('id_seller', $user_id)
            ->where('status', 1)
            ->where('id_closureI', null)
            ->when($sucursalId, function ($q) use ($sucursalId) {
                $q->where('id_sucursal', $sucursalId);
            })->get();
        // Pagos
        $payments = Bill_payment::where('id_seller', $user_id)
            ->where('id_closureI', null)
            ->when($sucursalId, function ($q) use ($sucursalId) {
                $q->where('id_sucursal', $sucursalId);
            })->get();
        // Devoluciones
        $repayments = Repayment::where('id_seller', $user_id)
            ->where('id_closureI', null)
            ->when($sucursalId, function ($q) use ($sucursalId) {
                $q->where('id_sucursal', $sucursalId);
            })->get();
        // Caja chica
        $smallBoxes = SmallBox::where('id_employee', $user_id)
            ->where('id_closureIndividual', null)
            ->get();
        if ($bills->isEmpty() && $payments->isEmpty() && $repayments->isEmpty() && $smallBoxes->isEmpty()) {
            return response('mal');
        }
        $closure = Closure::create([
            'id_seller' => $user_id,
            'bill_amount' => $bills->sum('net_amount'),
            'payment_amount' => $payments->sum('amount'),
            'repayment_amount' => $repayments->sum('amount'),
            'small_box_amount' => $smallBoxes->sum('cash'),
            'type' => 'INDIVIDUAL',
            'id_sucursal' => $sucursalId,
        ]);
        // Actualiza los registros con el id del cierre
        Bill::whereIn('id', $bills->pluck('id'))->update(['id_closureI' => $closure->id]);
        Bill_payment::whereIn('id', $payments->pluck('id'))->update(['id_closureI' => $closure->id]);
        Repayment::whereIn('id', $repayments->pluck('id'))->update(['id_closureI' => $closure->id]);
        SmallBox::whereIn('id', $smallBoxes->pluck('id'))->update(['id_closureIndividual' => $closure->id]);
        return response($closure->id);
    }
}
