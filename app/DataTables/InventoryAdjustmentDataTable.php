<?php

namespace App\DataTables;

use App\Models\InventoryAdjustment;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Column;

class InventoryAdjustmentDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('action', function ($row) {
                return '<a href="'.route('pdfStock', $row->id).'" class="btn btn-info btn-sm" target="_blank"><i class="fa-regular fa-eye"></i></a>';
            })
            ->editColumn('user', function($row) {
                return $row->user_name;
            })
            ->rawColumns(['action']);
    }

    public function query()
    {
        $sucursalId = session('selected_sucursal') ? intval(session('selected_sucursal')) : null;

        return InventoryAdjustment::query()
            ->when($sucursalId, function($q) use ($sucursalId) {
                $q->where('inventory_adjustments.id_sucursal', $sucursalId);
            })
            ->join('users', 'users.id', '=', 'inventory_adjustments.id_user')
            ->select(
                'inventory_adjustments.id',
                'inventory_adjustments.description',
                'inventory_adjustments.amount_lost',
                'inventory_adjustments.amount_profit',
                'inventory_adjustments.amount',
                'inventory_adjustments.created_at',
                'users.name as user_name'
            );
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('inventory-adjustment-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('lBfrtip')
            ->lengthMenu([[20, 50, 100, -1], [20, 50, 100, "Todos"]]) // <-- Agrega esta línea
            ->pageLength(20) // <-- Muestra 20 por defecto
            ->orderBy(0)
            ->responsive(true)
            ->language([
                'url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('id')->title('ID')->addClass('all'),
            Column::make('description')->title('Descripción')->addClass('all'),
            Column::make('amount_lost')->title('Monto Perdido')->addClass('all'),
            Column::make('amount_profit')->title('Monto Ganado')->addClass('all'),
            Column::make('amount')->title('Monto Total')->addClass('all'),
            Column::make('created_at')->title('Fecha')->addClass('all'),
            Column::make('user')->title('Usuario')->addClass('all'),
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
        return 'InventoryAdjustments_' . date('YmdHis');
    }
}