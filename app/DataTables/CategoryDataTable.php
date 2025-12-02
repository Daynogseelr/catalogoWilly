<?php
// filepath: app/DataTables/CategoryDataTable.php
namespace App\DataTables;

use App\Models\Category;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CategoryDataTable extends DataTable
{

     public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'categories.category-action')
            ->setRowId('id');
    }

    public function query(Category $model): QueryBuilder
    {
        return $model->newQuery();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('categories-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('lBfrtip')
            ->lengthMenu([[20, 50, 100, -1], [20, 50, 100, "Todos"]]) // <-- Agrega esta línea
            ->pageLength(20) // <-- Muestra 20 por defecto
            ->orderBy(0)
            ->selectStyleOs()
            ->responsive(true)
            ->buttons([
                Button::make('excel'),
                Button::make('print'),
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
            Column::make('name')->title('Nombre')->addClass('all')->responsivePriority(1),
            Column::make('description')->title('Descripción')->addClass('min-tablet')->responsivePriority(2),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center')
                ->searchable(false)
                ->orderable(false)
                ->title('Acciones')
                ->addClass('all')
                ->responsivePriority(0),
        ];
    }

    protected function filename(): string
    {
        return 'Categories_' . date('YmdHis');
    }
}