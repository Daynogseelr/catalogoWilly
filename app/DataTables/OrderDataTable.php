<?php

namespace App\DataTables;

use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Column;

class OrderDataTable extends DataTable
{
    public function dataTable($query)
    {
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                return view('orders.order-action', [
                    'id' => $row->id,
                ])->render();
            })
            ->editColumn('status', function ($row) {
                if ($row->status === 0 || $row->status === '0') return 'PENDIENTE';
                if ($row->status === 1 || $row->status === '1') return 'APROBADO';
                if ($row->status === 2 || $row->status === '2') return 'RECHAZADO';
                return $row->status;
            })
            ->rawColumns(['action']);
    }

    public function query()
    {
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;
        return DB::table('bills')
            ->join('clients as clients', 'clients.id', '=', 'bills.id_client')
            ->select(
                'bills.id',
                'bills.code',
                'bills.created_at',
                'clients.name as client',
                DB::raw("CONCAT(clients.nationality, '-', clients.ci) as ci"),
                'bills.type',
                'bills.net_amount',
                'bills.status',
                'bills.payment'
            )
            ->where('bills.type', 'PEDIDO')
            ->when($sucursalId, function ($q) use ($sucursalId) {
                 $q->where('bills.id_sucursal', $sucursalId);
            });
    }

    public function ajax(): \Illuminate\Http\JsonResponse
    {
        return $this->dataTable($this->query())->toJson();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('orders-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('ajax-crud-datatableOrder'))
            ->orderBy(1, 'desc')
            ->responsive(true)
            ->dom('lBfrtip')
            ->lengthMenu([[20, 50, 100, -1], [20, 50, 100, "Todos"]])
            ->pageLength(20)
            ->language(['url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'])
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

    public function getColumns(): array
    {
        return [
            Column::make('code')->title(__('Code'))->addClass('text-center'),
            Column::make('created_at')->title(__('Date'))->addClass('text-center'),
            Column::make('client')->title(__('Client'))->addClass('text-center'),
            Column::make('ci')->title(__('Identification Document'))->addClass('text-center'),
            Column::make('net_amount')->title(__('Total'))->addClass('text-center'),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->addClass('text-center not-export')->orderable(false)->searchable(false)->exportable(false)->printable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Orders_' . date('YmdHis');
    }
}
