<?php

namespace App\DataTables;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Carbon\Carbon;

class CurrencyDataTable extends DataTable
{

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('updated_at', function ($row) {
                return Carbon::parse($row->updated_at)->format('d/m/Y H:i:s');
            })
            // Columna 'status' eliminada
            ->addColumn('action', 'currencies.currency_action') // Vista para los botones de acción
            ->setRowId('id')
            ->rawColumns(['action']); // 'status' eliminado de rawColumns
    }

    public function query(Currency $model): QueryBuilder
    {
        return $model->newQuery();
    }

    public function html(): HtmlBuilder
{
    return $this->builder()
        ->setTableId('currency-table')
        ->columns($this->getColumns())
        ->minifiedAjax(route('currencies.data')) // <--- ADD THIS EXPLICIT ROUTE
        ->dom('lBfrtip')
        ->lengthMenu([[20, 50, 100, -1], [20, 50, 100, "Todos"]]) // <-- Agrega esta línea
        ->pageLength(20) // <-- Muestra 20 por defecto
        ->orderBy(0)
        ->selectStyleOs()
        ->responsive(true)
        ->buttons([
            Button::make('excel'),
            Button::make('print')
        ])
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

    // Crea los filtros
    api.columns().every(function (index) {
        var newTh = $('<th>').appendTo(filterRow);

        // Si la columna actual es la última (Acciones), no añadir input de búsqueda
        if (index === api.columns().count() - 1) {
            return;
        }

        var column = this;
        var title = thead.find('th').eq(index).text();

        var input = $('<input type="text" class="form-control form-control-sm" placeholder="Buscar ' + title + '" />');
        input.appendTo(newTh);

        input.on('click', function (e) {
            e.stopPropagation();
        });

        input.on('keyup change clear', function () {
            if (column.search() !== this.value) {
                column.search(this.value).draw();
            }
        });
    });
    // Función robusta para ocultar los filtros de columnas ocultas
    function updateFilterVisibility() {
        // DataTables Responsive pone display:none en los <th> de las columnas ocultas
        // Así que simplemente sincronizamos los <th> de filtros con los <th> del thead principal
        var mainThs = thead.find('tr').first().find('th');
        filterRow.find('th').each(function (i) {
            if (mainThs.eq(i).css('display') === 'none') {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    }
    // Llama a la función al cargar la tabla (sin delay)
    updateFilterVisibility();
    // Y también cuando cambie el responsive
    api.on('responsive-resize responsive-display draw', function () {
        updateFilterVisibility();
    });
    // Por si acaso, vuelve a llamar después de un pequeño delay tras inicialización
    setTimeout(updateFilterVisibility, 200);
}
JS;
}

    public function getColumns(): array
{
    return [
        Column::make('name')
            ->title('Nombre')
            ->addClass('all') // Siempre visible
            ->responsivePriority(1),
        Column::make('abbreviation')
            ->title('Abreviatura')
            ->addClass('all') // Siempre visible
            ->responsivePriority(2),
        Column::make('rate')
            ->title('Tasa')
            ->addClass('min-tablet') // Visible en sm en adelante
            ->responsivePriority(3),
        Column::make('updated_at')
            ->title('Actualización')
            ->addClass('min-desktop') // Visible en md en adelante
            ->responsivePriority(5),
        Column::computed('action')
            ->exportable(false)
            ->printable(false)
            ->width(80)
            ->addClass('all text-center') // Siempre visible
            ->searchable(false)
            ->orderable(false)
            ->title('Acciones')
            ->responsivePriority(0),
    ];
}
    protected function filename(): string
    {
        return 'Currencies_' . date('YmdHis');
    }
}