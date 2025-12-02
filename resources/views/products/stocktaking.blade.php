{{-- filepath: resources/views/products/stocktaking.blade.php --}}
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'inventary'])
@section('content')
    <div class="container-fluid ">
        <div class="row ">
            <div class="col-lg-12 mb-lg-0 mb-1">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-12 col-sm-5 col-md-5">
                                        <h4>{{ __('Ajuste de Inventario') }}</h4>
                                    </div>
                                    <div class="col-8 col-sm-5 col-md-5">
                                        <input name="descriptionStock" type="text" class="form-control"
                                            id="descriptionStock" placeholder="{{ __('Description') }}"
                                            title="Es obligatorio" minlength="2" maxlength="200" required
                                            onkeyup="mayus(this);" autocomplete="off">
                                    </div>
                                    <div class="col-2 col-sm-1 col-md-1">
                                        <button id="procesar" class="btn btn-success"><i
                                                class="fa-solid fa-cog fa-spin"></i></button>
                                    </div>
                                    <div class="col-2 col-sm-1 col-md-1 text-end">
                                        @if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'ADMINISTRATIVO')
                                            <button class="btn btn-danger" onclick="confirmReset()">
                                                <i class="fa-solid fa-eraser"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-1">
                        <div class="card-body">
                            <div class="tabla table-responsive" style="font-size: 13px;">
                                <table class="table table-striped" id="ajax-crud-datatable"
                                    style="font-size: 13px; width: 98% !important; vertical-align:middle;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{ __('Code') }}</th>
                                            <th class="text-center">{{ __('Name') }}</th>
                                            <th class="text-center">{{ __('Stock') }}</th>
                                            <th class="text-center">{{ __('Nuevo Stock') }}</th>
                                            <th class="text-center">{{ __('Diferencia') }}</th>
                                            <th class="text-center">{{ __('Precio') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="row mt-2">
                                <div class="col-4"><b>{{ __('Cantidad Perdida') }}:</b> <span id="amountLost">0.00</span>
                                </div>
                                <div class="col-4"><b>{{ __('Cantidad Ganada') }}:</b> <span id="amountProfit">0.00</span>
                                </div>
                                <div class="col-4"><b>{{ __('Total Ajuste') }}:</b> <span id="totalAmount">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('footer')
@endsection
@section('scripts')
    <script type="text/javascript">
        let table;
        let preciosProductos = {};
        let cambiosInventario = {};

        function calcularTotales() {
            let perdida = 0,
                ganancia = 0;
            for (let productId in cambiosInventario) {
                let dif = parseFloat(cambiosInventario[productId]);
                let precio = parseFloat(preciosProductos[productId] || 0);
                if (dif < 0) perdida += Math.abs(dif) * precio;
                if (dif > 0) ganancia += Math.abs(dif) * precio;
            }
            $('#amountLost').text(perdida.toFixed(2));
            $('#amountProfit').text(ganancia.toFixed(2));
            $('#totalAmount').text((ganancia - perdida).toFixed(2));
        }

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

                table = $('#ajax-crud-datatable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: "{{ url('ajax-crud-datatableStocktaking') }}",
                    },
                    columns: [{
                            data: 'code',
                            name: 'code'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'stock',
                            name: 'stock'
                        },
                        {
                            data: 'acción',
                            name: 'acción',
                            orderable: false,
                            render: function(data, type, row) {
                                return `<input name="nuevo_stock" type="text" value="${row.stock}" class="custom-input nuevo-stock" style="width: 60px;" data-stock-original="${row.stock}" data-product-id="${row.id}" autocomplete="off">`;
                            }
                        },
                        {
                            data: 'diferencia',
                            name: 'diferencia',
                            orderable: false,
                            className: 'text-center',
                            render: function(data, type, row) {
                                return 0;
                            }
                        },
                        {
                            data: 'price',
                            name: 'price',
                            orderable: false,
                            className: 'text-center'
                        }
                    ],
                    drawCallback: function(settings) {
                        centerTableContent();
                    },
                    order: [
                        [0, 'desc']
                    ],
                    lengthMenu: [
                        [20, 30, 40, 50, -1],
                        ['20', '30', '40', '50', 'Todos']
                    ],
                    oLanguage: {
                        "sProcessing": "{{ __('Processing') }}...",
                        "sLengthMenu": "{{ __('Show') }} <select>" +
                            '<option value="20" selected>20</option>' +
                            '<option value="30">30</option>' +
                            '<option value="40">40</option>' +
                            '<option value="50">50</option>' +
                            "<option value='-1'>{{ __('All') }}</option>" +
                            "</select> {{ __('Registers') }}",
                        "sZeroRecords": "{{ __('No results found') }}",
                        "sEmptyTable": "{{ __('No data available in this table') }}",
                        "sInfo": "{{ __('Showing of') }} (_START_ {{ __('to the') }} _END_) {{ __('of a total of') }} _TOTAL_ {{ __('Registers') }}",
                        "sInfoEmpty": "{{ __('Showing 0 to 0 of a total of 0 records') }}",
                        "sInfoFiltered": "({{ __('of') }} _MAX_ {{ __('existents') }})",
                        "sInfoPostFix": "",
                        "sSearch": "{{ __('Search') }}:",
                        "sUrl": "",
                        "sInfoThousands": ",",
                        "sLoadingRecords": "{{ __('Please wait - loading') }}...",
                        "oPaginate": {
                            "sFirst": "{{ __('First') }}",
                            "sLast": "{{ __('Last') }}",
                            "sNext": "{{ __('Next') }}",
                            "sPrevious": "{{ __('Previous') }}"
                        },
                        "oAria": {
                            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                        }
                    }
                });
                $('#ajax-crud-datatable').on('input', '.nuevo-stock', function() {
                    let productId = $(this).data('product-id');
                    let nuevoStock = parseFloat($(this).val());
                    let stockOriginal = parseFloat($(this).data('stock-original'));
                    let diferencia = nuevoStock - stockOriginal;
                    // Guarda la diferencia en el objeto global
                    cambiosInventario[productId] = diferencia;
                    // Actualiza la celda de diferencia
                    let row = table.rows(function(idx, data, node) {
                        return data.id == productId ? true : false;
                    }).nodes().to$();
                    $(row).find('td:eq(4)').text(diferencia.toFixed(2));

                    calcularTotales();
                });
                table.on('xhr', function(e, settings, json) {
                    if (json && json.data) {
                        json.data.forEach(function(row) {
                            preciosProductos[row.id] = parseFloat(row.price);
                        });
                    }
                });
            $('#procesar').click(function() {
                let datosParaEnviar = [];
                for (let productId in cambiosInventario) {
                    let nuevoStock = parseFloat($(`input[data-product-id="${productId}"]`).val());
                    let stockOriginal = parseFloat($(`input[data-product-id="${productId}"]`).data('stock-original'));
                    let diferencia = nuevoStock - stockOriginal;
                    datosParaEnviar.push({
                        id_producto: productId,
                        stock_original: stockOriginal,
                        nuevo_stock: nuevoStock,
                        diferencia: diferencia
                    });
                }
                if (datosParaEnviar.length > 0) {
                    var descriptionStock = $('#descriptionStock').val();
                    if (descriptionStock.trim() === '') {
                        $('#descriptionStock').addClass('is-invalid');
                    } else {
                        $.ajax({
                            url: "{{ route('storeStocktaking') }}",
                            type: 'POST',
                            data: {
                                datos: datosParaEnviar,
                                descriptionStock: descriptionStock,
                                amountLost: $('#amountLost').text(),
                                amountProfit: $('#amountProfit').text(),
                                totalAmount: $('#totalAmount').text(),
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(id_inventory_ajustment) {
                                Swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: "{{ __('Log saved successfully') }}",
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $('#descriptionStock').removeClass('is-invalid');
                                cambiosInventario = {};
                                table.ajax.reload();
                                table.draw();
                                // Limpiar campos y totales
                                $('#amountLost').text('0.00');
                                $('#amountProfit').text('0.00');
                                $('#totalAmount').text('0.00');
                                $('#descriptionStock').val('');
                                const pdfLink =
                                    "{{ route('pdfStock', ':id_inventory_ajustment') }}"
                                    .replace(':id_inventory_ajustment', id_inventory_ajustment);
                                window.open(pdfLink, '_blank');
                            },
                            error: function(error) {
                                alert('Error al ajustar el inventario.');
                                console.error(error);
                            }
                        });
                    }
                } else {
                    alert('No se han realizado cambios en el inventario.');
                }
            });

            window.confirmReset = function() {
                Swal.fire({
                    title: "¿Estás seguro?",
                    text: "Estás a punto de resetear el inventario. ¡Esta acción no se puede deshacer!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sí, ¡resetear!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('stocktakingReset') }}",
                            type: 'POST',
                            data: {
                                descriptionStock: 'RESETEO DE INVENTARIO',
                                amountLost: $('#amountLost').text(),
                                amountProfit: $('#amountProfit').text(),
                                totalAmount: $('#totalAmount').text(),
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function() {
                                Swal.fire("¡Inventario reseteado!", "", "success");
                                if (table) table.ajax.reload();
                                $('#amountLost').text('0.00');
                                $('#amountProfit').text('0.00');
                                $('#totalAmount').text('0.00');
                            },
                            error: function(error) {
                                alert('Error al resetear el inventario.');
                                console.error(error);
                            }
                        });
                    }
                });
            }

            function centerTableContent() {
                $('#ajax-crud-datatable tbody tr td:nth-child(1)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(3)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(4)').addClass('text-center');
            }
        });
    </script>
@endsection
