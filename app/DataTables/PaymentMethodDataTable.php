<?php

namespace App\DataTables;

use App\Models\PaymentMethod;
use App\Models\Currency;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Carbon\Carbon;

class PaymentMethodDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($row) {
                $btn = '<button class="btn btn-sm btn-primary btn-edit" data-id="'.$row->id.'"><i class="fas fa-edit"></i></button> ';
                return $btn;
            })
            ->editColumn('status', function ($row) {
                return $row->status ? 'Activo' : 'Inactivo';
            })
            ->editColumn('id_currency', function ($row) {
                return optional($row->currency)->abbreviation ?? '';
            })
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y H:i');
            })
            ->editColumn('updated_at', function ($row) {
                return Carbon::parse($row->updated_at)->format('d/m/Y H:i');
            })
            ->setRowId('id');
    }

    public function query(PaymentMethod $model)
    {
        return $model->newQuery()->with('currency');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('payment-methods-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('lBfrtip')
            ->lengthMenu([[20, 50, 100, -1], [20, 50, 100, "Todos"]]) // <-- Agrega esta línea
            ->pageLength(20) // <-- Muestra 20 por defecto
            ->orderBy(1)
            ->selectStyleSingle()
            ->responsive(true)
            ->buttons([
                'excel', 'print', 'reload'
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

    protected function getColumns()
    {
        return [
            Column::make('type')->title('Tipo')
                ->addClass('all') // Siempre visible
                ->responsivePriority(1),
            Column::make('id_currency')->title('Moneda')
                ->addClass('all') // Siempre visible
                ->responsivePriority(2),
            Column::make('bank')->title('Banco')
                ->addClass('min-tablet') // Siempre visible
                ->responsivePriority(3),
            Column::make('data')->title('Datos')
                ->addClass('min-desktop') // Siempre visible
                ->responsivePriority(4),
            Column::make('status')->title('Estado')
                ->addClass('min-tablet') // Siempre visible
                ->responsivePriority(5),
            Column::make('created_at')->title('Creado')
                ->addClass('min-desktop') // Siempre visible
                ->responsivePriority(1),
            Column::make('updated_at')->title('Actualizado')
                ->addClass('min-desktop') // Siempre visible
                ->responsivePriority(1),
            Column::computed('action')->title('Acciones')
                ->exportable(false)
                ->printable(false)
                ->width(80)
                ->addClass('text-center')
                ->addClass('all') // Siempre visible
                ->responsivePriority(0),
        ];
    }

    protected function filename(): string
    {
        return 'PaymentMethods_' . date('YmdHis');
    }
}