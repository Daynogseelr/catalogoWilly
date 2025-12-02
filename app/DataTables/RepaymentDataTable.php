<?php

namespace App\DataTables;

use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;

class RepaymentDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('action', function ($row) {
                return view('bills.repayment-action', [
                    'code' => $row->code,
                    'amount' => $row->amount,
                    'status' => $row->status,
                ])->render();
            })
            // Filtros personalizados para los alias
            ->filterColumn('client', function($query, $keyword) {
                $query->whereRaw("clients.name LIKE ?", ["%".strtolower($keyword)."%"]);
            })
            ->filterColumn('ci', function($query, $keyword) {
                $query->whereRaw("LOWER(CONCAT(clients.nationality, '-', clients.ci)) LIKE ?", ["%".strtolower($keyword)."%"]);
            })
            ->filterColumn('codeBill', function($query, $keyword) {
                $query->whereRaw("bills.code LIKE ?", ["%".$keyword."%"]);
            })
            ->editColumn('client', function($row) {
                return $row->client;
            })
            ->editColumn('ci', function($row) {
                return $row->ci;
            })
            ->editColumn('status', function($row) {
                return $row->status == 0 ? 'Pendiente' : 'Devolución';
            })
            ->rawColumns(['action']);
    }
    public function query()
    {
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        return DB::table('repayments')
            ->when($sucursalId, function($q) use ($sucursalId) {
                $q->where('repayments.id_sucursal', $sucursalId);
            })
            ->join('clients', 'clients.id', '=', 'repayments.id_client')
            ->leftJoin('bills', 'bills.id', '=', 'repayments.id_bill')
            ->select(
                DB::raw('SUM(repayments.amount) as amount'),
                'repayments.code as code',
                'repayments.created_at',
                'clients.name as client',
                DB::raw("CONCAT(clients.nationality, '-', clients.ci) as ci"),
                'repayments.status',
                'bills.code as codeBill'
            )
            ->groupBy(
                'repayments.code',
                'repayments.created_at',
                'repayments.status',
                'clients.name',
                'clients.nationality',
                'clients.ci',
                'bills.code'
            );
    }
    public function html()
    {
        return $this->builder()
            ->setTableId('repayment-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('lBfrtip')
            ->lengthMenu([[20, 50, 100, -1], [20, 50, 100, "Todos"]]) // <-- Agrega esta línea
            ->pageLength(20) // <-- Muestra 20 por defecto
            ->orderBy(0)
            ->responsive(true)
            ->language([
                'url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            ])
            ->buttons([
                ['extend' => 'copy', 'exportOptions' => ['columns' => ':not(.not-export)']],
                ['extend' => 'excel', 'exportOptions' => ['columns' => ':not(.not-export)']],
                ['extend' => 'csv', 'exportOptions' => ['columns' => ':not(.not-export)']],
                ['extend' => 'pdf', 'exportOptions' => ['columns' => ':not(.not-export)']],
                ['extend' => 'print', 'exportOptions' => ['columns' => ':not(.not-export)']],
            ])
            ->initComplete($this->getInitCompleteScript());
    }
    protected function getInitCompleteScript(): string
    {
        return <<<JS
            function () {
                var api = this.api();
                var thead = $(api.table().header());
                var filterRow = $('<tr>').addClass('filters').appendTo(thead);
                api.columns().every(function (index) {
                    var newTh = $('<th>').appendTo(filterRow);
                    if (index === api.columns().count() - 1) {
                        return;
                    }
                    var column = this;
                    var title = thead.find('th').eq(index).text();
                    var input = $('<input type="text" class="form-control form-control-sm" placeholder="Buscar ' + title + '" />');
                    input.appendTo(newTh);
                    input.on('click', function (e) { e.stopPropagation(); });
                    input.on('keyup change clear', function () {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                });
                function updateFilterVisibility() {
                    var mainThs = thead.find('tr').first().find('th');
                    filterRow.find('th').each(function (i) {
                        if (mainThs.eq(i).css('display') === 'none') {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    });
                }
                updateFilterVisibility();
                api.on('responsive-resize responsive-display draw', function () {
                    updateFilterVisibility();
                });
                setTimeout(updateFilterVisibility, 200);
            }
        JS;
    }
    protected function getColumns()
    {
        return [
            Column::make('code')->title('Código')->addClass('all'),
            Column::make('codeBill')->title('Factura')->addClass('all'),
            Column::make('created_at')->title('Fecha')->addClass('all'),
            Column::make('client')->title('Cliente')->addClass('all'),
            Column::make('ci')->title('Documento')->addClass('all'),
            Column::make('amount')->title('Total')->addClass('all'),
            Column::make('status')->title('Estado')->addClass('all'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center not-export')
                ->searchable(false)
                ->orderable(false)
                ->title('Acciones')
                ->addClass('all'),
        ];
    }
    protected function filename(): string
    {
        return 'Repayments_' . date('YmdHis');
    }
}