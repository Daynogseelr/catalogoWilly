<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\DataTables\RepaymentDataTable;

class RepaymentController extends Controller
{
    public function indexRepayment(RepaymentDataTable $dataTable)
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO' ||  auth()->user()->type == 'EMPLEADO') {
            return $dataTable->render('bills.repayment');
        } else {
            return redirect()->route('indexStore');
        }
    }
    public function ajaxRepayment()
    {
        DB::statement("SET SQL_MODE=''");
        if (request()->ajax()) {
            return datatables()->of(DB::table('repayments')
            ->join('clients', 'clients.id', '=', 'repayments.id_client')
            ->leftJoin('bills', 'bills.id', '=', 'repayments.id_bill')
            ->select(
                DB::raw('SUM(repayments.amount) as amount'),
                'repayments.id as id',
                'repayments.code as code',
                'repayments.created_at as created_at',
                'repayments.status as status',
                'clients.name as clientName',
                'clients.nationality as nationality',
                'clients.ci as ci',
                'bills.code as codeBill'
            )
            ->groupBy('repayments.code')
            ->get())
            ->addColumn('action', 'bills.repayment-action')
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('indexStore');
    }
}
