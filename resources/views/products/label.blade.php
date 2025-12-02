@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'label'])
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info mb-2" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-8 col-sm-10">
                                        <h4 class="mb-0 fw-bold" style="letter-spacing:0.2px;">{{ __('Labels') }}</h4>
                                        <small class="text-muted">Selecciona formato y tipo de precio para generar etiquetas</small>
                                    </div>
                                    <div class="col-4 col-sm-2 text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-2 flex-nowrap">
                                            <select id="format" class="form-select form-select-sm sucursal-select" style="flex:0 0 140px; min-width:140px;">
                                                <option value="compact">Formato compacto</option>
                                                <option value="big">Formato grande (ticket)</option>
                                            </select>

                                            <select id="price_type" class="form-select form-select-sm sucursal-select" style="flex:0 0 140px; min-width:140px;">
                                                <option value="price">Precio normal</option>
                                                <option value="detal">Precio detal</option>
                                                <option value="price2">Precio 2</option>
                                                <option value="price3">Precio 3</option>
                                            </select>

                                            <!-- Usar la sucursal seleccionada en sesión en lugar de crear otro select -->
                                            <input type="hidden" id="sucursal" value="{{ session('selected_sucursal') ?? '' }}">

                                            <div class="input-group input-group-sm" style="flex:0 0 120px; min-width:120px;">
                                                <input name="product_code" type="text" class="form-control" id="product_code" placeholder="{{ __('Código producto') }}" title="Código de producto (code o code2)" minlength="1" maxlength="50" autocomplete="off">
                                            </div>

                                            <div class="input-group input-group-sm" style="flex:0 0 180px; min-width:180px;">
                                                <input name="code" type="text" class="form-control" id="code" placeholder="{{ __('Ingrese código Compra') }}" title="Es obligatorio un codigo" minlength="1" maxlength="50" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="card-body">
                            <div class="tabla table-responsive" style="font-size: 13px;">
                                <table class="table table-striped" id="ajax-crud-datatable"
                                    style="font-size: 13px; width: 98% !important; vertical-align:middle;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{__('Code')}}</th>
                                            <th class="text-center">{{__('Name')}}</th>
                                            <th class="text-center">{{__('Price')}}</th>
                                            <th class="text-center">{{__('Stock')}}</th>
                                            <th class="text-center">{{__('Images')}}</th>
                                            <th class="text-center">{{__('Action')}}</th>
                                        </tr>
                                    </thead>
                                </table>
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
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#ajax-crud-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('ajax-crud-datatableLabel') }}",
                    data: function(d){
                        d.sucursal = $('#sucursal').val();
                        d.price_type = $('#price_type').val();
                        d.format = $('#format').val();
                    }
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
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'stock',
                        name: 'stock'
                    },
                    {
                        data: 'url',
                        name: 'url',
                        orderable: false
                    },
                    { 
                        data: null, // Important: Use null if 'acción' is not in your data
                        defaultContent: "", 
                        name: 'acción', 
                        orderable: false,
                        render: function(data, type, row) {
                            return `<div style="text-align: center;"><input name="quantity" type="text" class="custom-input" style="width: 60px;" data-product-id="${row.id}" onkeypress='return validaMonto(event)' autocomplete="off"></div>`;
                        }, 
                        createdCell: function (td, cellData, rowData, row, col) {
                            $(td).css({
                                'vertical-align': 'middle' // Optional: for vertical centering
                            });
                        }
                    },
                ],
                order: [[0, 'desc']],
                columnDefs: [
                        { targets: [2, 3, 4, 5], searchable: false }, // Disable search for price, stock, url and action
                        { targets: [0, 1], searchable: true} // Explicitly enable search for code and name
                    ],
                drawCallback: function(settings) {
                    centerTableContent()
                },
                "oLanguage": {
                    "sProcessing": "{{__('Processing')}}...",
                    "sLengthMenu": "{{__('Show')}} <select>" +
                        '<option value="10">10</option>' +
                        '<option value="20">20</option>' +
                        '<option value="30">30</option>' +
                        '<option value="40">40</option>' +
                        '<option value="50">50</option>' +
                        "<option value='-1'>{{__('All')}}</option>" +
                        "</select> {{__('Registers')}}",
                    "sZeroRecords": "{{__('No results found')}}",
                    "sEmptyTable": "{{__('No data available in this table')}}",
                    "sInfo": "{{__('Showing of')}} (_START_ {{__('to the')}} _END_) {{__('of a total of')}} _TOTAL_ {{__('Registers')}}",
                    "sInfoEmpty": "{{__('Showing 0 to 0 of a total of 0 records')}}",
                    "sInfoFiltered": "({{__('of')}} _MAX_ {{__('existents')}})",
                    "sInfoPostFix": "",
                    "sSearch": "{{__('Search')}}:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "{{__('Please wait - loading')}}...",
                    "oPaginate": {
                        "sFirst": "{{__('First')}}",
                        "sLast": "{{__('Last')}}",
                        "sNext": "{{__('Next')}}",
                        "sPrevious": "{{__('Previous')}}"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                }
            });
            function centerTableContent() {
                // Columns: 1=Code, 2=Name, 3=Price, 4=Stock, 5=Images, 6=Action
                $('#ajax-crud-datatable tbody tr td:nth-child(1)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(3)').addClass('text-end');
                $('#ajax-crud-datatable tbody tr td:nth-child(4)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(5)').addClass('text-center');
            }
            // Recargar tabla al cambiar filtros (sucursal viene de sesión)
            $('#price_type, #format').on('change', function(){
                $('#ajax-crud-datatable').DataTable().ajax.reload();
            });
            $('#ajax-crud-datatable').on('keypress', 'input[name="quantity"]', function(event) {
                if (event.which === 13) { // 13 es el código de la tecla Enter
                    event.preventDefault(); // Evita que el formulario se envíe de forma tradicional
                    var inputValue = $(this).val();
                    var productId = $(this).data('product-id');
                    $.ajax({
                        url: "{{ url('storeLabel') }}",
                        type: 'POST', // O 'PUT' si es una actualización
                        data: {
                            quantity: inputValue,
                            id: productId
                            , format: $('#format').val()
                            , price_type: $('#price_type').val()
                            , sucursal: $('#sucursal').val()
                        },
                        success: function(response) {
                            // Manejar la respuesta del servidor (puede ser un mensaje de éxito, actualización de la tabla, etc.)
                            console.log(response);
                            // Puedes actualizar la tabla o mostrar un mensaje de éxito aquí
                            $('#ajax-crud-datatable').DataTable().ajax.reload(); // Recarga la tabla si es necesario
                            // Limpiar el input o hacer cualquier otra acción necesaria
                            $(this).val(''); // Limpia el input actual
                            const pdfLink = "{{ route('pdfLabel', ['id' => ':id', 'quantity' => ':quantity']) }}";
                            const finalPdfLink = pdfLink
                                .replace(':id', response.id)
                                .replace(':quantity', response.quantity)
                                + '?format=' + encodeURIComponent($('#format').val())
                                + '&price_type=' + encodeURIComponent($('#price_type').val())
                                + '&sucursal=' + encodeURIComponent($('#sucursal').val());
                            // Abrir pestaña temporal para evitar que el JSON de error quede visible
                            var win = window.open('', '_blank');
                            fetch(finalPdfLink).then(function(res) {
                                if (res.ok) {
                                    // Redirige la pestaña al PDF si está bien
                                    win.location = finalPdfLink;
                                } else {
                                    // Leer mensaje JSON y mostrar alerta
                                    res.json().then(function(data) {
                                        try { win.close(); } catch(e) {}
                                        Swal.fire({
                                            title: "{{__('Algo salio mal')}}",
                                            text: data.message || 'Error',
                                            icon: 'error'
                                        });
                                    }).catch(function() {
                                        try { win.close(); } catch(e) {}
                                        Swal.fire({
                                            title: "{{__('Algo salio mal')}}",
                                            text: 'Ha ocurrido un error inesperado.',
                                            icon: 'error'
                                        });
                                    });
                                }
                            }).catch(function() {
                                try { win.close(); } catch(e) {}
                                Swal.fire({
                                    title: "{{__('Algo salio mal')}}",
                                    text: 'Ha ocurrido un error de red.',
                                    icon: 'error'
                                });
                            });
                        },
                        error: function(error) {
                            // Manejar errores de la solicitud AJAX
                            console.error("Error en la solicitud AJAX:", error);
                            // Mostrar mensaje de error al usuario si es necesario
                            alert('Ocurrió un error al guardar el stock.');
                        }
                    });
                }
            });
            // Nuevo: input para generar 1 ticket por código de producto (code o code2)
            $('#product_code').on('keypress', function(event) {
                if (event.which === 13) {
                    event.preventDefault();
                    var code = $(this).val().trim();
                    if (!code) {
                        Swal.fire({ title: "{{__('Ingrese un codigo')}}", text: "{{__('codigo vacio')}}", icon: 'question' });
                        return;
                    }
                    // Buscar producto por code/code2
                    $.ajax({
                        url: "{{ url('findLabelProduct') }}",
                        type: 'POST',
                        data: { code: code },
                        success: function(res) {
                            if (res && res.id) {
                                // Reutilizar storeLabel flow: enviar id y quantity=1
                                $.ajax({
                                    url: "{{ url('storeLabel') }}",
                                    type: 'POST',
                                    data: {
                                        id: res.id,
                                        quantity: 1,
                                        format: $('#format').val(),
                                        price_type: $('#price_type').val(),
                                        sucursal: $('#sucursal').val()
                                    },
                                    success: function(response) {
                                        const pdfLink = "{{ route('pdfLabel', ['id' => ':id', 'quantity' => ':quantity']) }}";
                                        const finalPdfLink = pdfLink.replace(':id', response.id).replace(':quantity', response.quantity) + '?format=' + encodeURIComponent($('#format').val()) + '&price_type=' + encodeURIComponent($('#price_type').val()) + '&sucursal=' + encodeURIComponent($('#sucursal').val());
                                        var win = window.open('', '_blank');
                                        fetch(finalPdfLink).then(function(r) {
                                            if (r.ok) {
                                                win.location = finalPdfLink;
                                            } else {
                                                r.json().then(function(data){ try{win.close();}catch(e){} Swal.fire({ title: "{{__('Algo salio mal')}}", text: data.message || 'Error', icon: 'error' }); });
                                            }
                                        }).catch(function(){ try{win.close();}catch(e){} Swal.fire({ title: "{{__('Algo salio mal')}}", text: 'Ha ocurrido un error de red.', icon: 'error' }); });
                                        // limpiar campo
                                        $('#product_code').val('');
                                    },
                                    error: function(err) { console.error(err); Swal.fire({ title: "{{__('Algo salio mal')}}", text: 'No se pudo generar etiqueta', icon:'error' }); }
                                });
                            }
                        },
                        error: function(err) {
                            console.error(err);
                            Swal.fire({ title: "{{__('No se encontro producto')}}", text: "{{__('codigo incorrecto')}}", icon: 'question' });
                        }
                    });
                }
            });
        });
        document.getElementById('code').addEventListener('keypress', function(event) {
            if (event.keyCode === 13) {
                event.preventDefault(); // Evita que se recargue la página
                var code = this.value; // Obtiene el valor del input
                // Envía el valor a la ruta de Laravel usando AJAX
                if (code == '') {
                    Swal.fire({
                        title: "{{__('Ingrese un codigo')}}",
                        text: "{{__('codigo vacio')}}",
                        icon: "question"
                    });
                } else {
                    $.ajax({
                        url: "{{ url('storeLabelAll') }}",
                        type: 'POST',
                        data: { code: code }, // Incluye el token CSRF de Laravel
                        success: function(response) {
                            // Maneja la respuesta del servidor
                            console.log(response);
                            // Puedes actualizar la página, mostrar un mensaje, etc.
                            const pdfLink = "{{ route('pdfLabelAll', ':code') }}".replace(':code', response.code)
                                + '?format=' + encodeURIComponent($('#format').val())
                                + '&price_type=' + encodeURIComponent($('#price_type').val())
                                + '&sucursal=' + encodeURIComponent($('#sucursal').val());
                            // Abrir pestaña temporal y verificar respuesta del endpoint antes de mostrar
                            var win2 = window.open('', '_blank');
                            fetch(pdfLink).then(function(res) {
                                if (res.ok) {
                                    win2.location = pdfLink;
                                } else {
                                    res.json().then(function(data) {
                                        try { win2.close(); } catch(e) {}
                                        Swal.fire({
                                            title: "{{__('Algo salio mal')}}",
                                            text: data.message || 'Error',
                                            icon: 'error'
                                        });
                                    }).catch(function() {
                                        try { win2.close(); } catch(e) {}
                                        Swal.fire({
                                            title: "{{__('Algo salio mal')}}",
                                            text: 'Ha ocurrido un error inesperado.',
                                            icon: 'error'
                                        });
                                    });
                                }
                            }).catch(function() {
                                try { win2.close(); } catch(e) {}
                                Swal.fire({
                                    title: "{{__('Algo salio mal')}}",
                                    text: 'Ha ocurrido un error de red.',
                                    icon: 'error'
                                });
                            });
                        },
                        error: function(error) {
                            console.error(error);
                            if (error.responseJSON && error.responseJSON.message) { // Verifica si existe error.responseJSON.message
                                Swal.fire({
                                    title: "{{__('Algo salio mal')}}",
                                    text: error.responseJSON.message, // Muestra el mensaje de error del servidor
                                    icon: "error" // Cambia el icono a "error"
                                });
                            } else {
                            Swal.fire({
                                    title: "{{__('Algo salio mal')}}",
                                    text: "Ha ocurrido un error inesperado.", // Muestra un mensaje genérico
                                    icon: "error"
                                });
                            }
                        }
                    }); 
                }
                
            }
        });
    </script>
@endsection
