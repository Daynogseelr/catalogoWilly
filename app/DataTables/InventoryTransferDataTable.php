<?php

namespace App\DataTables;

use App\Models\InventoryTransfer;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;

class InventoryTransferDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('from_name', function ($row) {
                return $row->fromSucursal?->name ?? '-';
            })
            ->addColumn('to_name', function ($row) {
                return $row->toSucursal?->name ?? '-';
            })
            ->addColumn('status_label', function ($row) {
                $map = [
                    0 => ['Pendiente', 'warning'],
                    1 => ['Aprobado', 'primary'],
                    2 => ['Cancelado', 'danger'],
                    3 => ['Lista', 'success'],
                ];
                $info = $map[$row->status] ?? ['Desconocido', 'secondary'];
                return "<span class='badge bg-{$info[1]}'>{$info[0]}</span>";
            })
            ->addColumn('action', function ($row) {
                $open = "<button class='btn btn-primary open-transfer' data-id='".$row->id."' title='Abrir transferencia'> <i class='fa-regular fa-pen-to-square'></i></button> ";
                $pdf = "<a href='" . route('inventory-transfers.pdf', $row->id) . "' target='_blank' class='btn btn-info' title='PDF'> <i class='fa-regular fa-eye'></i></a>";
                return $open.$pdf;
            })
            ->editColumn('created_at', function ($row) {
                // Formato a estilo español: día/mes/año hora:minutos
                if ($row->created_at instanceof \Carbon\Carbon) {
                    return $row->created_at->format('d/m/Y H:i');
                }

                // fallback si viene como string
                try {
                    return (new \Carbon\Carbon($row->created_at))->format('d/m/Y H:i');
                } catch (\Throwable $e) {
                    return $row->created_at;
                }
            })
            ->rawColumns(['action','status_label']);
    }

    public function query(InventoryTransfer $model)
    {
        return $model->newQuery()->with(['fromSucursal','toSucursal'])->select('inventory_transfers.*');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('inventory-transfers-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('lBfrtip')
            ->orderBy(0)
            ->pageLength(25)
            ->lengthMenu([[10,25,50,100,-1],[10,25,50,100,'Todos']])
            ->responsive(true)
            ->language(['url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json']);
    }

    protected function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('code')->title('Código'),
            Column::computed('from_name')->title('Origen'),
            Column::computed('to_name')->title('Destino'),
            Column::computed('status_label')->title('Status')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('created_at')->title('Fecha'),
            Column::computed('action')->title('Acciones')->orderable(false)->searchable(false)->exportable(false)->printable(false)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'InventoryTransfers_' . date('YmdHis');
    }
}
