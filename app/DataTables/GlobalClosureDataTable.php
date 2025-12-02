<?php

namespace App\DataTables;

use App\Models\Closure;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class GlobalClosureDataTable extends DataTable
{
    public function dataTable($query)
{
    return datatables()
        ->of($query)
        ->addColumn('action', 'closures.closure-action')
        ->addColumn('formatted_created_at', function ($closure) {
            return \Carbon\Carbon::parse($closure->created_at)->format('d/m/Y H:i:s');
        })
        // Filtro personalizado para el alias seller
        ->filterColumn('seller', function($query, $keyword) {
            $query->whereRaw("sellers.name", ["%".strtolower($keyword)."%"]);
        })
        ->rawColumns(['action']);
}

public function query()
{
    $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;

    return DB::table('closures')
        ->when($sucursalId, function($q) use ($sucursalId) {
            $q->where('closures.id_sucursal', $sucursalId);
        })
        ->leftJoin('users as sellers', 'closures.id_seller', '=', 'sellers.id')
        ->select(
            'closures.id',
            DB::raw('FORMAT(closures.bill_amount, 2) as bill_amount'),
            DB::raw('FORMAT(closures.payment_amount, 2) as payment_amount'),
            DB::raw('FORMAT(closures.repayment_amount, 2) as repayment_amount'),
            DB::raw('FORMAT(closures.small_box_amount, 2) as small_box_amount'),
            'closures.created_at',
            DB::raw("sellers.name as seller")
        )
        ->where('closures.type', 'GLOBAL')
        ->orderBy('closures.created_at', 'desc');
}

    public function html()
    {
        return $this->builder()
            ->setTableId('global-closure-table')
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
        Column::make('formatted_created_at')->title('Fecha')->addClass('all'),
        Column::make('seller')->title('Usuario')->addClass('all'),
        Column::make('bill_amount')->title('Total')->addClass('all'),
        Column::make('payment_amount')->title('Pagos')->addClass('all'),
        Column::make('repayment_amount')->title('Devoluciones')->addClass('all'),
        Column::make('small_box_amount')->title('Caja Chica')->addClass('all'),
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
        return 'GlobalClosures_' . date('YmdHis');
    }
}