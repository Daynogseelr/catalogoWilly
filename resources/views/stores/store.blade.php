@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'store'])
@section('content')
    <style>
        .margen {
            margin-top: 40px !important;
        }
        @media (max-width: 767.98px) {
            .margen {
                margin-top: 80px !important;
            }
        }
        @media (max-width: 575.98px) {
            .margen {
                margin-top: 90px !important;
            }
        }
    </style>
    <div class="container-fluid margen " style="">
        <div class="row justify-content-center productBuscador">
        </div>
    </div>
    <!-- boostrap mostrar product modal -->
    <div class="modal fade" id="product-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{ __('Product Detail') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Imagen del producto -->
                        <div class="col-xxl-5 col-xl-5 col-lg-5 col-md-5 col-sm-5 col-6 d-flex align-items-center justify-content-center"
                            id="divcarousel">
                            <!-- Aquí se inserta la imagen por JS -->
                        </div>
                        <div class="col-xxl-7 col-xl-7 col-lg-7 col-md-7 col-sm-7 col-6">
                            <!-- Nombre del producto -->
                            <h5 id="divname" class="mb-2"></h5>
                            <!-- Precios -->
                            <div id="divprice" class="mb-2"></div>
                            <!-- Existencia -->
                            <div class="mb-2">
                                <label class="form-label" for="existencia">{{ __('Existencia') }}</label>
                                <input name="existencia" type="text" class="form-control" id="existencia" placeholder="1"
                                    title="Es obligatorio una cantidad" min="1" required autocomplete="off" disabled>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    @include('footer')
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            var category = $('#category').val();
            var scope = $('#buscador').val();
            var sortBy = $('#sort-by-stock').val();
            var id_currencyStore = $('#id_currencyStore').val();
            $.ajax({
                type: "POST",
                url: "{{ route('indexStoreAjax') }}",
                data: {
                    page: $(this).attr('href').split('page=')[1],
                    category: category,
                    scope: scope,
                    sort_by: sortBy,
                    id_currencyStore: id_currencyStore
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    $('.productBuscador').html(res);
                    window.scrollTo(0, 0);
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        });
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(document).on('mouseenter', '.card-integral, .card-fraccionado', function() {
                let id = $(this).data('id');
                let type = $(this).data('type');
                showProductInfo(id, type, this);
            });
            $(document).on('mouseleave', '.card-integral, .card-fraccionado', function() {
                hideProductInfo(this);
            });
            $('#category').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                height: '20px',
                dropdownCssClass: "cate",
                selectionCssClass: "catego",
                language: "es"
            });
        });
        function filtro() {
            var tecla = event.key;
            if (['.', ',', 'e', '-', '+'].includes(tecla)) {
                event.preventDefault()
            }
        }
        function refreshProductList() {
            var category = $('#category').val();
            var scope = $('#buscador').val();
            var sortBy = $('#sort-by-stock').val();
            var id_currencyStore = $('#id_currencyStore').val(); // <-- Añade esto
            $.ajax({
                type: "POST",
                url: "{{ route('indexStoreAjax') }}",
                data: {
                    category: category,
                    scope: scope,
                    sort_by: sortBy,
                    id_currencyStore: id_currencyStore // <-- Añade esto
                },
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    $('.productBuscador').html(res);
                },
                error: function(error) {
                    if (error) {
                        console.error("Error en la solicitud AJAX:", error.responseJSON || error);
                    }
                }
            });
        }
        // Opcional: Ejecutar al cargar la página para mostrar los productos iniciales con filtros por defecto
        document.addEventListener('DOMContentLoaded', function() {
            refreshProductList();
        });
        function downloadCatalog() {
            var category = $('#category').val();
            var scope = $('#buscador').val();
            var sortBy = $('#sort-by-stock').val();
            var url = "{{ url('pdfCatalog') }}" +
                "&category=" + category +
                "&scope=" + scope +
                "&sort_by=" + sortBy +
                window.open(url, '_blank');
        }
        document.addEventListener('keydown', function(event) {
            if (event.keyCode === 121 && !event.ctrlKey && !event.altKey && !event.shiftKey) {
                // Prevenir el comportamiento por defecto de la tecla F10 (ej. abrir menú de depuración en algunos navegadores)
                event.preventDefault();
                window.location.href = "{{ route('indexBilling') }}";
            }
        });
        function mostrarProduct(id) {
            // Obtiene la tasa y la abreviatura de la moneda seleccionada
            let $selected = $('#id_currencyStore option:selected');
            let tasa = parseFloat($selected.data('tasa')) || 1;
            let abbr = $selected.data('abbr') || '';
            $.ajax({
                type: "POST",
                url: "/mostrarProductBilling",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    // Imagen
                    $('#divcarousel').html(
                        `<img src="${res.image_url}" alt="Imagen" style="max-width:180px;max-height:180px;border-radius:8px;margin-bottom:10px;">`
                    );
                    // Nombre
                    $('#divname').html(`<h5>${res.name}</h5>`);
                    // Existencia
                    $('#existencia').val(res.existencia);
                    // Precios con tasa y abreviatura
                    let preciosHtml = '<ul style="list-style:none;padding-left:0;">';
                    if (res.prices.detal) preciosHtml +=
                        `<li>Precio Detal: <b>${(parseFloat(res.prices.detal) * tasa).toFixed(2)} ${abbr}</b></li>`;
                    if (res.prices.price1) preciosHtml +=
                        `<li>Precio 1: <b>${(parseFloat(res.prices.price1) * tasa).toFixed(2)} ${abbr}</b></li>`;
                    if (res.prices.price2) preciosHtml +=
                        `<li>Precio 2: <b>${(parseFloat(res.prices.price2) * tasa).toFixed(2)} ${abbr}</b></li>`;
                    if (res.prices.price3) preciosHtml +=
                        `<li>Precio 3: <b>${(parseFloat(res.prices.price3) * tasa).toFixed(2)} ${abbr}</b></li>`;
                    preciosHtml += '</ul>';
                    $('#divprice').html(preciosHtml);
                    // Limpia descripción si no la necesitas
                    $('#divdescription').html('');
                    // Muestra el modal
                    $('#product-modal').modal('show');
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo cargar el producto.', 'error');
                }
            });
        }
    </script>
@endsection
