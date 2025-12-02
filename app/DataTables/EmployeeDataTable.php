<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class EmployeeDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<User > $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('sucursals', function ($row) {
                if ($row->sucursals->isEmpty()) {
                    return 'SIN SUCURSAL';
                }
                // devuelve nombres de sucursales separados por coma; si quieres etiquetas HTML puedes modificarlas aquí
                return $row->sucursals->pluck('name')->implode(', ');
            })
            ->addColumn('action', 'users.employee-action')
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<User >
     */
    public function query(User  $model): QueryBuilder
    {
        return $model->newQuery()->where('type','!=','ADMINISTRADOR')->with('sucursals');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('employee-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('lBfrtip')
                    ->lengthMenu([[20, 50, 100, -1], [20, 50, 100, "Todos"]]) // <-- Agrega esta línea
                    ->pageLength(20) // <-- Muestra 20 por defecto
                    ->orderBy(0)
                    ->selectStyleSingle()
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
    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('Nombre')->addClass('all')->responsivePriority(1),
            Column::make('ci')->title('Cédula')->addClass('min-tablet')->responsivePriority(2),
            Column::make('phone')->title('Teléfono')->addClass('min-tablet')->responsivePriority(3),
            Column::make('type')->title('Tipo')->addClass('min-tablet')->responsivePriority(4),
             Column::computed('sucursals')->title('Sucursales')->addClass('min-tablet')->responsivePriority(5),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center not-export')
                ->searchable(false)
                ->orderable(false)
                ->title('Acciones')
                ->addClass('all')
                ->responsivePriority(0),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
