<?php
namespace App\DataTables;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Currency;
use Illuminate\Http\Request;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;

class ProductDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        $request = request();
        $currencyId = $request->get('currency_id');
        $stockFilter = $request->get('stock_filter', 'all');

        // sucursal seleccionada en sesión (null si no hay)
        $sucursalId = session('selected_sucursal');

        // Obtener tasa de cambio
        $currency = Currency::find($currencyId);

        return (new EloquentDataTable($query))
            ->addColumn('price', function ($row) {
                // Mostrar el precio base tal como está (sin aplicar porcentaje de sucursal)
                return is_numeric($row->cost) ? number_format($row->cost, 2) : null;
            })
            ->addColumn('price_currency', function ($row) use ($currency) {
                // Mostrar el precio base convertido por la tasa de moneda (sin porcentaje de sucursal)
                $rate = $currency?->rate ?? 1;
                $base = is_numeric($row->cost) ? floatval($row->cost) : 0;
                return number_format($base * $rate, 2);
            })
            ->addColumn('stock', function ($row) use ($sucursalId) {
                $q = Stock::where('id_product', $row->id);
                if (! empty($sucursalId)) {
                    $q->where('id_sucursal', $sucursalId);
                }
                $quantity = $q->orderBy('id', 'desc')->value('quantity');
                return $quantity ?? 0;
            })
            ->addColumn('stock_detal', function ($row) use ($sucursalId) {
                $q = Stock::where('id_product', $row->id);
                if (! empty($sucursalId)) {
                    $q->where('id_sucursal', $sucursalId);
                }
                $quantity = $q->orderBy('id', 'desc')->value('quantity') ?? 0;
                $units = isset($row->units) ? (int) $row->units : 1;
                return (int) round($quantity * $units);
            })
            ->addColumn('image', function ($row) {
                $img = '';
                $url = $row->{'url'};
                if ($url) {
                    $img = '<img src="'.asset('storage/'.$url).'" style="width:40px;height:40px;border-radius:6px;margin-right:4px;" />';
                }
                return $img;
            })
            ->addColumn('action', 'products.product-action')
            ->filter(function ($query) use ($stockFilter, $sucursalId) {
                // construir condición extra para la sucursal en las subconsultas
                $sucursalConstraint = '';
                if (! empty($sucursalId)) {
                    // proteger con intval
                    $sucursalConstraint = ' AND s2.id_sucursal = ' . intval($sucursalId);
                }

                if ($stockFilter == 'min') {
                    $query->whereIn('id', function($sub) use ($sucursalId, $sucursalConstraint) {
                        $sub->select('id_product')
                            ->from('stocks')
                            ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
                            ->whereRaw('
                                quantity <= (
                                    SELECT stock_min FROM products WHERE products.id = stocks.id_product
                                )
                            ')
                            ->whereRaw('id = (
                                SELECT MAX(id) FROM stocks s2 WHERE s2.id_product = stocks.id_product' . $sucursalConstraint . '
                            )');
                    });
                } elseif ($stockFilter == 'max') {
                    $query->whereIn('id', function($sub) use ($sucursalId, $sucursalConstraint) {
                        $sub->select('id_product')
                            ->from('stocks')
                            ->when($sucursalId, fn($q) => $q->where('id_sucursal', $sucursalId))
                            ->whereRaw('
                                quantity > (
                                    SELECT stock_min FROM products WHERE products.id = stocks.id_product
                                )
                            ')
                            ->whereRaw('id = (
                                SELECT MAX(id) FROM stocks s2 WHERE s2.id_product = stocks.id_product' . $sucursalConstraint . '
                            )');
                    });
                }
            })
            ->rawColumns(['image', 'action']);
    }

    public function query(Product $model)
    {
        return $model->newQuery();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('products-table')
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
    public function getColumns(): array
    {
        return [
            Column::make('code')->title('Código')->addClass('text-center'),
            Column::make('code2')->title('Código Barra')->addClass('text-center'),
            Column::make('name')->title('Nombre'),
            Column::make('price')->title('Costo')->addClass('text-center'),
            Column::computed('price_currency')->title('Costo Moneda')->addClass('text-center'),
            Column::computed('stock')->title('Stock')->addClass('text-center'),
            Column::computed('stock_detal')->title('Stock Detal')->addClass('text-center'),
            Column::computed('image')->title('Imágen')->addClass('text-center not-export')->orderable(false)->searchable(false)->exportable(false)->printable(false), 
            Column::computed('action')->title('Acciones')->addClass('text-center not-export')->orderable(false)->searchable(false)->exportable(false)->printable(false), 
        ];
    }

    protected function filename(): string
    {
        return 'Products_' . date('YmdHis');
    }
}