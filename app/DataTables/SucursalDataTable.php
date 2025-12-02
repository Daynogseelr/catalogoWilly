<?php

namespace App\DataTables;

use App\Models\Sucursal;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;

class SucursalDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('percent_formatted', function ($row) {
                return $row->percent !== null ? number_format($row->percent, 2) . '%' : '-';
            })
            ->addColumn('status_label', function ($row) {
                return $row->status == 1 ? 'Activo' : 'Inactivo';
            })
            ->addColumn('action', function ($row) {
                // botón editar abre modal (no link a otra página)
                $edit = '<button type="button" data-id="' . $row->id . '" class="btn btn-sm btn-primary me-1 btn-edit-sucursal" title="Editar"><i class="fa fa-edit"></i></button>';
                return $edit;
            })
            ->rawColumns(['action']);
    }

    public function query(Sucursal $model)
    {
        return $model->newQuery()->select('id','name','rif','state','city','postal_zone','direction','percent','status');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('sucursal-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('lBfrtip')
            ->orderBy(0)
            ->pageLength(20)
            ->lengthMenu([[20,50,100,-1],[20,50,100,'Todos']])
            ->buttons([])
            ->responsive(true)
            ->language(['url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'])
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
        if (index === api.columns().count() - 1) { return; }
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
            if (mainThs.eq(i).css('display') === 'none') { $(this).hide(); } else { $(this).show(); }
        });
    }
    updateFilterVisibility();
    api.on('responsive-resize responsive-display draw', function () { updateFilterVisibility(); });
    setTimeout(updateFilterVisibility, 200);
}
JS;
    }

    protected function getColumns(): array
    {
        return [
            Column::make('name')->title('Nombre'),
            Column::make('rif')->title('RIF'),
            Column::computed('percent_formatted')->title('%')->addClass('text-end'),
            Column::computed('status_label')->title('Estado')->addClass('text-center'),
            Column::computed('action')->title('Acciones')->orderable(false)->searchable(false)->exportable(false)->printable(false)->addClass('text-center not-export'),
        ];
    }

    protected function filename(): string
    {
        return 'Sucursals_' . date('YmdHis');
    }
}