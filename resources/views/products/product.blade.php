@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'product'])
@section('content')
    <style>
        .dropzone {
            width: 30% !important;
            height: 80px !important;
            border: 2px dashed #ccc;
            text-align: center;
            padding: 5px;
            margin: 5px auto;
        }

        .imagendrop {
            width: 100% !important;
            height: 110px !important;
            text-align: center;
            padding: 5px;
            margin: 5px auto;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12">
                                        <h4>{{ __('Products') }}</h4>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <label for="currencySelect">Moneda:</label>
                                            <select id="currencySelect" class="form-select">
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}" data-tasa="{{ $currency->rate }}"
                                                        {{ $currency->is_principal == 1 ? 'selected' : '' }}>
                                                        {{ $currency->name }} ({{ $currency->abbreviation }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="stockFilter">Filtro de Stock:</label>
                                            <select id="stockFilter" class="form-select">
                                                <option value="all">Todos</option>
                                                <option value="min">Mínimo stock</option>
                                                <option value="max">Máximo stock</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive" style="font-size: 13px;">
                            {!! $dataTable->table(
                                ['class' => 'table table-striped table-bordered w-100', 'style' => 'font-size:13px;'],
                                true,
                            ) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- boostrap product model -->
    <div class="modal fade" id="product-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="row" style="width:90%;">
                        <div class="col-6 col-sm-6 col-md-6 col-lg-8 col-xl-8">
                            <h5 class="modal-title" id="modal-title">{{ __('Add Product') }}</h5>
                        </div>
                        <div class="col-3 col-sm-3 col-md-3 col-lg-2 col-xl-2" id="inventario">

                        </div>
                        <div class="col-3 col-sm-3 col-md-3 col-lg-2 col-xl-2" id="status">

                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="productForm" name="productForm" class="form-horizontal" method="POST"
                        enctype="multipart/form-data">
                        <div class="row">
                            <input type="hidden" name="id" id="id">
                            <div class="col-md-2 col-sm-2 form-outline">
                                <input name="code" type="text" class="form-control" id="code"
                                    placeholder="{{ __('Code') }}" title="Es obligatorio un codigo" minlength="1"
                                    maxlength="50" required onkeypress='return validaMonto(event)' autocomplete="off"
                                    readonly>
                                <label class="form-label" for="form2Example17">{{ __('Code') }}</label>
                                <span id="codeError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-3 col-sm-3 form-outline">
                                <input name="code2" type="text" class="form-control" id="code2"
                                    placeholder="{{ __('Code') }} de Barra" maxlength="50"
                                    onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Code') }} de Barra</label>
                                <span id="code2Error" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-7 col-sm-7 form-outline">
                                <input name="name" type="text" class="form-control" id="name"
                                    placeholder="{{ __('Name') }}" title="Es obligatorio un nombre" minlength="2"
                                    maxlength="200" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Name') }}<span
                                        class="text-danger">*</span></label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-5 col-sm-5 form-outline">
                                <select class="form-select" name="iva" id="iva">
                                    <option value="1">Si</option>
                                    <option value="0">No</option>
                                </select>
                                <label class="form-label" for="modal_currency_id">¿Usa I.V.A.?</label>
                            </div>
                            <div class="col-md-7 col-sm-7 form-outline">
                                <input name="name_detal" type="text" class="form-control" id="name_detal"
                                    placeholder="{{ __('Name') }} Detal" minlength="2" maxlength="200"
                                    onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Name') }} Detal</label>
                                <span id="name_detalError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <input type="text" class="form-control" id="stock_min" name="stock_min"
                                    value="0" onkeypress='return validaNumericos(event)' required>
                                <label class="form-label" for="form2Example17">{{ __('Stock minimo') }}<span
                                        class="text-danger">*</span></label>
                                <span id="stock_minError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <input name="existencia" type="text" class="form-control" id="existencia"
                                    placeholder="{{ __('existencia') }}" minlength="1" maxlength="50"
                                    onkeypress='return validaMonto(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Existencia') }}</label>
                                <span id="ExistenciaError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-8 col-sm-8 form-outline">
                                <input name="url" type="file" class="form-control" id="url" multiple
                                    title="Es obligatorio una Imagen">
                                <label class="form-label" for="form2Example17">{{ __('Image') }}</label>
                                <span id="urlError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-sm-2"></div>
                            <div class="col-md-8 col-sm-8 form-outline">
                                <div class="row" id="divdropzone">
                                    <div class="col-md-4 col-sm-4 col-4" id="divimg" data-url="url">

                                    </div>
                                    <div class="col-md-4 col-sm-4 col-4 text-center">
                                        {{-- Imagen se carga por JS --}}
                                        <button type="button" class="btn btn-danger btn-sm mt-2" id="delete-img"
                                            style="display:none;" onclick="deleteImage()">
                                            Eliminar imagen
                                        </button>
                                    </div>
                                    <div class="dropzone col-md-4 col-sm-4 col-4" data-url="url">
                                        {{ __('drop the image here') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2"></div>

                            <div class="col-md-10 col-sm-10 form-outline">
                                <select class="js-example-basic-multiple js-example-basic-multiple-category"
                                    data-placeholder="Seleccione una categoría" name="id_category[]" multiple="multiple">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Category') }}</label>
                                <span id="id_categoryError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-2 col-sm-2" style="margin-bottom:25px;">
                                <a class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center"
                                    onClick="addCategory()" href="javascript:void(0)">
                                    Agregar Categoria
                                </a>
                            </div>
                            <div class="col-12">
                                <section class="bg-white rounded shadow-sm m-4"
                                    style="border: 1.5px solid #e3e3e3; padding: 1.5rem; box-shadow: 0 2px 12px rgba(0,0,0,0.07); max-width: 100%; overflow-x: auto;">
                                    <div class="row flex-wrap">
                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <input name="cost" type="text" class="form-control" id="cost"
                                                placeholder="{{ __('Cost') }}" title="Es obligatorio un precio"
                                                minlength="1" maxlength="50" required
                                                onkeypress='return validaMonto(event)' autocomplete="off">
                                            <label class="form-label" for="form2Example17">{{ __('Cost') }}<span
                                                    class="text-danger">*</span></label>
                                            <span id="costError" class="text-danger error-messages"></span>
                                        </div>
                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <input name="units" type="text" class="form-control" id="units"
                                                placeholder="{{ __('Unidades') }}" title="Es obligatorio un precio"
                                                minlength="1" maxlength="50" required
                                                onkeypress='return validaMonto(event)' autocomplete="off">
                                            <label class="form-label" for="form2Example17">{{ __('Unidades') }}<span
                                                    class="text-danger">*</span></label>
                                            <span id="unitsError" class="text-danger error-messages"></span>
                                        </div>
                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <select class="form-select" name="modal_currency_id" id="modal_currency_id">
                                                @foreach ($currencies as $currency)
                                                    <option {{ $currency->is_principal == 1 ? 'selected' : '' }}
                                                        value="{{ $currency->id }}">{{ $currency->abbreviation }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <label class="form-label" for="modal_currency_id">Moneda</label>
                                        </div>
                                        <div class="col-md-4 col-sm-4">
                                            <input name="utility_detal" type="text" class="form-control utility-field"
                                                id="utility_detal" placeholder="{{ __('Utility') }} Detal %"
                                                title="Es obligatorio un descuento" minlength="1" value="0"
                                                maxlength="10" onkeypress='return validaMonto(event)'>
                                            <label class="form-label" for="form2Example17">{{ __('Utility') }} % Detal
                                            </label>
                                            <span id="utility_detalError" class="text-danger error-messages"></span>
                                        </div>
                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <input name="price_detal" type="text" class="form-control price-field"
                                                id="price_detal" placeholder="{{ __('Price') }} Detal"
                                                title="Es obligatorio un precio" minlength="1" maxlength="50"
                                                onkeypress='return validaMonto(event)' autocomplete="off">
                                            <label class="form-label" for="form2Example17">{{ __('Price') }}
                                                Detal</label>
                                            <span id="price_detalError" class="text-danger error-messages"></span>
                                            <small class="text-muted ms-2" id="sucursal_price_0"></small>
                                        </div>

                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <input name="price2_detal" type="text" class="form-control price2-field"
                                                id="price2_detal" placeholder="{{ __('Price Detal en moneda') }}"
                                                title="Es obligatorio un precio" minlength="1" maxlength="80"
                                                autocomplete="off">
                                            <label class="form-label"
                                                for="price2_detal">{{ __('Precio total en moneda') }}</label>
                                        </div>
                                        <div class="col-md-4 col-sm-4">
                                            <input name="utility" type="text" class="form-control utility-field"
                                                id="utility" placeholder="{{ __('Utility') }} Precio 1"
                                                title="Es obligatorio una utilidad" required
                                                onkeypress='return validaMonto(event)'>
                                            <label class="form-label" for="form2Example17">{{ __('Utility') }} % Precio 1
                                                <span class="text-danger">*</span></label>
                                            <span id="utilityError" class="text-danger error-messages"></span>
                                        </div>
                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <input name="price" type="text" class="form-control price-field"
                                                id="price" placeholder="{{ __('Price') }} 1"
                                                title="Es obligatorio un precio" required
                                                onkeypress='return validaMonto(event)' autocomplete="off">
                                            <label class="form-label" for="form2Example17">{{ __('Price') }} 1 <span
                                                    class="text-danger">*</span></label>
                                            <span id="priceError" class="text-danger error-messages"></span>
                                            <small class="text-muted ms-2" id="sucursal_price_1"></small>
                                        </div>

                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <input name="price2_1" type="text" class="form-control price2-field"
                                                id="price2_1" placeholder="{{ __('Price 1 en moneda ') }}"
                                                title="Es obligatorio un precio" minlength="1" maxlength="80" required
                                                autocomplete="off">
                                            <label class="form-label" for="price2">{{ __('Precio total en moneda') }}
                                                <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-4 col-sm-4">
                                            <input name="utility2" type="text" class="form-control utility-field"
                                                id="utility2" placeholder="{{ __('Utility') }} Precio 2"
                                                title="Es obligatorio un descuento" minlength="1" maxlength="10"
                                                onkeypress='return validaMonto(event)'>
                                            <label class="form-label" for="form2Example17">{{ __('Utility') }} % Precio
                                                2</label>
                                            <span id="utility2Error" class="text-danger error-messages"></span>
                                        </div>
                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <input name="price2" type="text" class="form-control  price-field"
                                                id="price2" placeholder="{{ __('Price 2') }}"
                                                title="Es obligatorio un precio" minlength="1" maxlength="50"
                                                onkeypress='return validaMonto(event)' autocomplete="off">
                                            <label class="form-label" for="form2Example17">{{ __('Price') }} 2</label>
                                            <span id="price2Error" class="text-danger error-messages"></span>
                                            <small class="text-muted ms-2" id="sucursal_price_2"></small>
                                        </div>

                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <input name="price2_2" type="text" class="form-control price2-field"
                                                id="price2_2" placeholder="{{ __('Precio 2 en moneda') }}"
                                                title="Es obligatorio un precio" minlength="1" maxlength="80"
                                                autocomplete="off">
                                            <label class="form-label" for="price2_2">{{ __('Precio total en moneda') }}
                                            </label>
                                        </div>
                                        <div class="col-md-4 col-sm-4">
                                            <input name="utility3" type="text" class="form-control utility-field"
                                                id="utility3" placeholder="{{ __('Utility') }} 3"
                                                title="Es obligatorio un descuento" minlength="1" maxlength="10"
                                                onkeypress='return validaMonto(event)'>
                                            <label class="form-label" for="form2Example17">{{ __('Utility') }} % Precio 3
                                            </label>
                                            <span id="utility3Error" class="text-danger error-messages"></span>
                                        </div>
                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <input name="price3" type="text" class="form-control  price-field"
                                                id="price3" placeholder="{{ __('Price') }} 3"
                                                title="Es obligatorio un precio" minlength="1" maxlength="50"
                                                onkeypress='return validaMonto(event)' autocomplete="off">
                                            <label class="form-label" for="form2Example17">{{ __('Price') }} 3</label>
                                            <span id="price3Error" class="text-danger error-messages"></span>
                                            <small class="text-muted ms-2" id="sucursal_price_3"></small>
                                        </div>

                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <input name="price2_3" type="text" class="form-control price2-field"
                                                id="price2_3" placeholder="{{ __('Price 3 en moneda') }}"
                                                title="Es obligatorio un precio" minlength="1" maxlength="80"
                                                autocomplete="off">
                                            <label class="form-label" for="price2_3">{{ __('Precio total en moneda') }}
                                            </label>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-12 text-center"><br />
                            <button type="submit" class="btn btn-primary" id="btn-save">{{ __('Send') }}</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    <!-- boostrap product Stock model -->
    <div class="modal fade" id="productStock-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalStock-title">{{ __('Replenish Stock') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="productStockForm" name="productStockForm"
                        class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_product" id="id_product">
                        <input type="hidden" name="status" id="status">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="codeStock" type="text" class="form-control" id="codeStock"
                                    placeholder="{{ __('Code') }}" title="Es obligatorio un codigo" minlength="2"
                                    maxlength="200" required onkeyup="mayus(this);" autocomplete="off" disabled>
                                <label class="form-label" for="form2Example17">{{ __('Code') }}</label>
                                <span id="codeError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="nameStock" type="text" class="form-control" id="nameStock"
                                    placeholder="{{ __('Name') }}" title="Es obligatorio un nombre" minlength="2"
                                    maxlength="200" required onkeyup="mayus(this);" autocomplete="off" disabled>
                                <label class="form-label" for="form2Example17">{{ __('Name') }}</label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <textarea class="form-control" rows="3" id="descriptions" name="descriptions" onkeyup="mayus(this);"
                                    autocomplete="off"></textarea>
                                <label class="form-label" for="form2Example17">{{ __('Description') }}</label>
                                <span id="descriptionsError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="stocks" type="text" class="form-control" id="stocks"
                                    placeholder="{{ __('Stock') }}" title="Es obligatorio un stocks" minlength="1"
                                    maxlength="10" required onkeypress='return validaMonto(event)' onkeyup="mayus(this);"
                                    autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Stock') }}</label>
                                <span id="stockError" class="text-danger error-messages"></span>
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-12 text-center"><br />
                            <button type="submit" class="btn btn-primary" id="btn-save">{{ __('Send') }}</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    <!-- boostrap category model -->
    <div class="modal fade" id="category-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{ __('Add Category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="categoryForm" name="categoryForm" class="form-horizontal"
                        method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_category" id="id_category">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="name" type="text" class="form-control" id="name"
                                    placeholder="{{ __('Name') }}" title="Es obligatorio un nombre" minlength="2"
                                    maxlength="30" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Name') }}</label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="description" type="text" class="form-control" id="description"
                                    placeholder="{{ __('Description') }}" title="Es obligatorio una descripcion"
                                    minlength="2" maxlength="100" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Description') }}</label>
                                <span id="descriptionError" class="text-danger error-messages"></span>
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-12 text-center"><br />
                            <button type="submit" class="btn btn-primary" id="btn-save">{{ __('Send') }}</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    @include('footer')
@endsection
@section('scripts')
    {!! $dataTable->scripts() !!}
    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.js-example-basic-multiple-category').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                closeOnSelect: false,
                selectionCssClass: "form-select",
                dropdownParent: $('#product-modal .modal-body'),
                language: "es"
            });
            $('#products-table').on('preXhr.dt', function(e, settings, data) {
                data.currency_id = $('#currencySelect').val();
                data.stock_filter = $('#stockFilter').val();
            });
            var table = $('#products-table').DataTable();

            function reloadTable() {
                table.ajax.reload();
            }
            $('#currencySelect, #stockFilter').on('change', reloadTable);
            // Sobrescribe la función ajax para enviar los parámetros
            $.fn.dataTable.ext.errMode = 'none'; // Evita errores JS en consola
            // Deshabilita todos los campos al abrir el modal


            // Habilita utilidades si hay costo, y precios si hay utilidad > 0
            function toggleFields() {
                const cost = parseFloat(document.getElementById('cost').value) || 0;
                const utilityFields = document.querySelectorAll('.utility-field');
                const priceFields = document.querySelectorAll('.price-field');
                const price2Fields = document.querySelectorAll('.price2-field');

                if (cost > 0) {
                    utilityFields.forEach((f, idx) => {
                        f.disabled = false;
                        const util = parseFloat(f.value) || 0;
                        if (util > 0) {
                            priceFields[idx].disabled = false;
                            price2Fields[idx].disabled = false;
                        } else {
                            priceFields[idx].value = '';
                            priceFields[idx].disabled = true;
                            price2Fields[idx].value = '';
                            price2Fields[idx].disabled = true;
                        }
                    });
                } else {
                    disableAllFields();
                }
            }

            window.toggleFields = toggleFields;

            // Evita que el usuario ponga menos de 1 manualmente
            $('#units').on('blur', function() {
                if (parseInt($(this).val()) < 1 || !$(this).val()) {
                    $(this).val(1);
                }
            });
            // Eventos para activar/desactivar campos
            document.getElementById('cost').addEventListener('input', toggleFields);
            document.querySelectorAll('.utility-field').forEach(f => {
                f.addEventListener('input', toggleFields);
            });

            function actualizarCamposModalProducto() {
                let currencyId = $('#modal_currency_id').val();
                let tasaCambio = 1;
                currencies.forEach(currency => {
                    if (currency.id == currencyId) tasaCambio = currency.rate;
                });

                // Helper para obtener tasa actual
                const updateTasa = () => {
                    currencyId = $('#modal_currency_id').val();
                    tasaCambio = 1;
                    currencies.forEach(currency => {
                        if (currency.id == currencyId) tasaCambio = currency.rate;
                    });
                };

                // Limpia handlers previos para evitar duplicados
                $('#cost').off('input');
                $('.utility-field').off('input');
                $('.price-field').off('input');
                $('.price2-field').off('input');
                $('#modal_currency_id').off('change');
                $('#units').off('input');

                // 1) Si actualizas el costo -> recalcula precios a partir de utilidades (nuevo método: price = cost * (1 + util/100))
                $('#cost').on('input', function() {
                    let costo = parseFloat($(this).val()) || 0;
                    let unidades = parseInt($('#units').val()) || 1;
                    $('.utility-field').each(function(idx, utilInput) {
                        let utilidad = parseFloat($(utilInput).val()) || 0;
                        let precioInput = $('.price-field').eq(idx);
                        let precio2Input = $('.price2-field').eq(idx);
                        // Si hay utilidad (incluso 0) o hay contenido en el campo, recalcular
                        if ($(utilInput).val() !== '') {
                            let precio = costo * (1 + utilidad / 100); // precio para paquete
                            if (idx === 0 && unidades > 0) { // Detal -> precio por unidad
                                precio = precio / unidades;
                            }
                            precioInput.val(isFinite(precio) ? precio.toFixed(2) : '');
                            // mostrar precio ajustado por sucursal (local moneda)
                            let adj = isFinite(precio) ? (precio * (1 + sucursalPercent / 100)) : null;
                            $('#sucursal_price_' + idx).text(adj !== null ? adj.toFixed(2) : '');
                            precio2Input.val(isFinite(precio) ? (precio * tasaCambio).toFixed(2) :
                                '');
                        }
                    });
                });

                // 1b) Si actualizas la utilidad -> recalcula precio (sin límite)
                $('.utility-field').on('input', function() {
                    let idx = $('.utility-field').index(this);
                    let costo = parseFloat($('#cost').val()) || 0;
                    let utilidad = parseFloat($(this).val()) || 0;
                    let unidades = parseInt($('#units').val()) || 1;
                    let precioInput = $('.price-field').eq(idx);
                    let precio2Input = $('.price2-field').eq(idx);

                    if (costo > 0 && $(this).val() !== '') {
                        let precio = costo * (1 + utilidad / 100); // precio para paquete
                        if (idx === 0 && unidades > 0) { // Detal -> precio por unidad
                            precio = precio / unidades;
                        }
                        precioInput.val(isFinite(precio) ? precio.toFixed(2) : '');
                        // mostrar precio ajustado por sucursal
                        let adj = isFinite(precio) ? (precio * (1 + sucursalPercent / 100)) : null;
                        $('#sucursal_price_' + idx).text(adj !== null ? adj.toFixed(2) : '');
                        precio2Input.val(isFinite(precio) ? (precio * tasaCambio).toFixed(2) : '');
                    } else {
                        // Si no hay costo o utilidad vacía, limpiamos
                        precioInput.val('');
                        $('#sucursal_price_' + idx).text('');
                        precio2Input.val('');
                    }
                });

                // 2) Si cambias el precio -> calcula utilidad usando util = (price_total - cost) / cost * 100
                $('.price-field').on('input', function() {
                    let idx = $('.price-field').index(this);
                    let costo = parseFloat($('#cost').val()) || 0;
                    let unidades = parseInt($('#units').val()) || 1;
                    let precio = parseFloat($(this).val()) || 0;
                    let utilidadInput = $('.utility-field').eq(idx);
                    let precio2Input = $('.price2-field').eq(idx);

                    if (precio > 0 && costo > 0) {
                        let utilidad;
                        if (idx === 0 && unidades > 0) { // Detal: precio es por unidad
                            let totalPrecio = precio * unidades;
                            utilidad = ((totalPrecio - costo) / costo) * 100;
                        } else { // Precio por paquete
                            utilidad = ((precio - costo) / costo) * 100;
                        }
                        utilidadInput.val(isFinite(utilidad) ? utilidad.toFixed(2) : '');
                        precio2Input.val((precio * tasaCambio).toFixed(2));
                        // actualizar precio ajustado por sucursal
                        let adj = isFinite(precio) ? (precio * (1 + sucursalPercent / 100)) : null;
                        $('#sucursal_price_' + idx).text(adj !== null ? adj.toFixed(2) : '');
                    } else if (precio > 0 && costo === 0) {
                        // Si costo es cero, utilidad es indefinida; dejamos campo utilidad vacío o 0 según preferencia
                        utilidadInput.val('');
                        precio2Input.val((precio * tasaCambio).toFixed(2));
                        let adj2 = isFinite(precio) ? (precio * (1 + sucursalPercent / 100)) : null;
                        $('#sucursal_price_' + idx).text(adj2 !== null ? adj2.toFixed(2) : '');
                    } else {
                        utilidadInput.val('');
                        precio2Input.val('');
                        $('#sucursal_price_' + idx).text('');
                    }
                });

                // 3) Si cambias el precio en moneda -> calcula precio local y utilidad
                $('.price2-field').on('input', function() {
                    let idx = $('.price2-field').index(this);
                    let costo = parseFloat($('#cost').val()) || 0;
                    let unidades = parseInt($('#units').val()) || 1;
                    let precio2 = parseFloat($(this).val()) || 0;
                    let precioInput = $('.price-field').eq(idx);
                    let utilidadInput = $('.utility-field').eq(idx);

                    updateTasa();
                    if (tasaCambio !== 0 && precio2 > 0) {
                        let precio = precio2 / tasaCambio;
                        precioInput.val(isFinite(precio) ? precio.toFixed(2) : '');
                        // actualizar display sucursal para este precio calculado
                        let adj = isFinite(precio) ? (precio * (1 + sucursalPercent / 100)) : null;
                        $('#sucursal_price_' + idx).text(adj !== null ? adj.toFixed(2) : '');
                        let utilidad;
                        if (costo > 0) {
                            if (idx === 0 && unidades > 0) {
                                let totalPrecio = precio * unidades;
                                utilidad = ((totalPrecio - costo) / costo) * 100;
                            } else {
                                utilidad = ((precio - costo) / costo) * 100;
                            }
                            utilidadInput.val(isFinite(utilidad) ? utilidad.toFixed(2) : '');
                        } else {
                            utilidadInput.val('');
                        }
                    } else {
                        precioInput.val('');
                        utilidadInput.val('');
                        $('#sucursal_price_' + idx).text('');
                    }
                });

                // Si cambia la moneda, actualiza todos los precios en moneda
                $('#modal_currency_id').on('change', function() {
                    updateTasa();
                    $('.price-field').each(function(idx, precioInput) {
                        let precio = parseFloat($(precioInput).val()) || 0;
                        let precio2Input = $('.price2-field').eq(idx);
                        precio2Input.val(isFinite(precio) ? (precio * tasaCambio).toFixed(2) : '');
                    });
                });

                // Si cambian las unidades, recalcula solo la línea Detal
                $('#units').on('input', function() {
                    let unidades = parseInt($(this).val()) || 1;
                    if (unidades < 1) {
                        unidades = 1;
                        $(this).val(1);
                    }
                    let costo = parseFloat($('#cost').val()) || 0;
                    let utilidadDetal = parseFloat($('.utility-field').eq(0).val()) || 0;
                    let precioInput = $('.price-field').eq(0);
                    let precio2Input = $('.price2-field').eq(0);
                    updateTasa();
                    if (costo > 0 && $('.utility-field').eq(0).val() !== '') {
                        let precioDetal = (costo * (1 + utilidadDetal / 100)) / unidades;
                        precioInput.val(isFinite(precioDetal) ? precioDetal.toFixed(2) : '');
                        let adj = isFinite(precioDetal) ? (precioDetal * (1 + sucursalPercent / 100)) : null;
                        $('#sucursal_price_0').text(adj !== null ? adj.toFixed(2) : '');
                        precio2Input.val(isFinite(precioDetal) ? (precioDetal * tasaCambio).toFixed(2) :
                        '');
                    } else if (precioInput.val() !== '') {
                        // si precio ya existe y cambió unidades, recalculamos utilidad con nueva unidades
                        let precio = parseFloat(precioInput.val()) || 0;
                        if (precio > 0 && costo > 0) {
                            let totalPrecio = precio * unidades;
                            let utilidad = ((totalPrecio - costo) / costo) * 100;
                            $('.utility-field').eq(0).val(isFinite(utilidad) ? utilidad.toFixed(2) : '');
                            precio2Input.val((precio * tasaCambio).toFixed(2));
                            let adj = isFinite(precio) ? (precio * (1 + sucursalPercent / 100)) : null;
                            $('#sucursal_price_0').text(adj !== null ? adj.toFixed(2) : '');
                        }
                    }
                });
            }

            // Inicializa la función al abrir el modal
            $('#product-modal').on('shown.bs.modal', function() {
                actualizarCamposModalProducto();
            });

        });

        const currencies = @json($currencies);
        // Porcentaje de la sucursal seleccionada (para mostrar precio ajustado en el modal)
        const sucursalPercent = parseFloat("{{ $sucursalPercent ?? 0 }}") || 0;
        let tasaCambio = 1;
        // Actualiza el precio2 en el modal según la moneda seleccionada
        function editFunc(id) {
            $('#productForm').trigger("reset");
            $('.error-messages').html('');
            $.ajax({
                type: "POST",
                url: "{{ url('editProduct') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    $('#modal-title').html("{{ __('Editar Producto') }}");

                    let p = res.product;

                    $('#id').val(p.id);
                    $('#code').val(p.code);
                    $('#code2').val(p.code2);
                    $('#name').val(p.name);
                    $('#name_detal').val(p.name_detal);
                    $('#cost').val(p.cost);
                    $('#units').val(p.units);
                    $('#stock_min').val(p.stock_min);
                    $('#iva').val(p.iva);
                    $('#existencia').val(res.quantity ? res.quantity.quantity : 0);

                    // Utilidades y precios
                    $('#utility_detal').val(p.utility_detal);
                    $('#utility').val(p.utility);
                    $('#utility2').val(p.utility2);
                    $('#utility3').val(p.utility3);

                    console.log('Producto cargado:', p);
                    $('#price_detal').val(p.price_detal);
                    $('#price').val(p.price);
                    $('#price2').val(p.price2);
                    $('#price3').val(p.price3);

                    $('#price2_detal').val(p.price_detal ?? '');
                    $('#price2_1').val(p.price);
                    $('#price2_2').val(p.price2 ?? '');
                    $('#price2_3').val(p.price2 ?? '');

                    // Imagen
                    var imageUrl = p.url;
                    if (imageUrl) {
                        var fullImageUrl = '{{ asset('storage') }}' + '/' + imageUrl;
                        $('#divimg').html('<img class="imagendrop" src="' + fullImageUrl +
                            '" onerror="this.src=\'{{ asset('storage/products/product.jpg') }}\'" alt="Product Image">'
                        );
                        $('#delete-img').show();
                    } else {
                        $('#divimg').html(
                            '<img class="imagendrop" src="{{ asset('storage/products/product.jpg') }}">'
                        );
                        $('#delete-img').hide();
                    }

                    // Categorías
                    if (res.categories && res.categories.length > 0) {
                        var selectedIds = res.categories.map(function(item) {
                            return item.id_category;
                        });
                        $('.js-example-basic-multiple-category').val(selectedIds).trigger('change');
                    } else {
                        $('.js-example-basic-multiple-category').val('').trigger('change');
                    }

                    // Estado
                    let productStatus = p.status;
                    let productId = p.id;
                    let inventarioHref = '/indexStock/' + productId;
                    let inventarioLink = '<a style="color:white;" href="' + inventarioHref +
                        '" data-toggle="tooltip" data-original-title="Inventario" class="btn btn-info">Kardex</a>';
                    $('#inventario').html(inventarioLink);
                    $('#status').html(function() {
                        let html = '';
                        if (productStatus == '1') {
                            html = '<a class="btn btn-success cambia' + productId +
                                '" href="javascript:void(0)" onClick="micheckbox(' +
                                productId + ')">Activo</a>';
                        } else {
                            html = '<a class="btn btn-danger cambia' + productId +
                                '" href="javascript:void(0)" onClick="micheckbox(' +
                                productId + ')">Inactivo</a>';
                        }
                        return html;
                    });

                    $('#product-modal').modal('show');
                    $('#divdropzone').show();
                    // actualizar display de precios ajustados por sucursal
                    (function() {
                        let vals = [
                            parseFloat($('#price_detal').val()) || null,
                            parseFloat($('#price').val()) || null,
                            parseFloat($('#price2').val()) || null,
                            parseFloat($('#price3').val()) || null,
                        ];
                        vals.forEach((v, i) => {
                            if (v !== null && !isNaN(v)) {
                                let adj = v * (1 + sucursalPercent / 100);
                                $('#sucursal_price_' + i).text(adj.toFixed(2));
                            } else {
                                $('#sucursal_price_' + i).text('');
                            }
                        });
                    })();
                    toggleFields();
                }
            });
        }

        function deleteImage() {
            var productId = $('#id').val();
            Swal.fire({
                title: "¿Seguro que deseas eliminar la imagen?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('deleteProductImage') }}",
                        type: "POST",
                        data: {
                            id: productId,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: "success",
                                title: "Imagen eliminada",
                                showConfirmButton: false,
                                timer: 1200
                            });
                            // Recarga la imagen y oculta el botón
                            $('#divimg').html(
                                '<img class="imagendrop" src="{{ asset('storage/products/product.jpg') }}">'
                            );
                            $('#delete-img').hide();
                            $('#products-table').DataTable().ajax.reload();
                        },
                        error: function() {
                            Swal.fire("Error", "No se pudo eliminar la imagen", "error");
                        }
                    });
                }
            });
        }

        function editStock(id, stock) {
            $.ajax({
                type: "POST",
                url: "{{ url('editProduct') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    if (stock == 'Reponer') {
                        $('#modalStock-title').html("{{ __('Replenish Stock') }}");
                        $('#status').val('Reponer');
                    } else {
                        $('#modalStock-title').html("{{ __('Subtract Stock') }}");
                        $('#status').val('Restar');
                    }
                    $('.error-messages').html('');
                    $('#productStock-modal').modal('show');
                    $('#id_product').val(res.product.id);
                    $('#codeStock').val(res.product.code);
                    $('#nameStock').val(res.product.name);
                    $('#stocks').val('');
                }
            });
        }
        $('#productStockForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            // Antes de enviar, agrega los valores de is_fraction por producto fraccionado
            // Busca todos los selects de modo fraccion y agrega un input oculto por cada uno
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: "{{ url('storeStock') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#productStock-modal").modal('hide');
                    $('#products-table').DataTable().ajax.reload();
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Log saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#stocksError').html(error.responseJSON.errors.stocks);
                        $('#descriptionsError').html(error.responseJSON.errors
                            .descriptions);
                    }
                }
            });
        });

        function deleteFuncAp(id) {
            var id = id;
            Swal.fire({
                title: "Estas seguro?",
                text: "su registro sera eliminado!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                cancelButtonText: "Cancelar",
                confirmButtonText: "Si, eliminar!"
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteFunc(id);
                }
            });
        }

        function deleteFunc(id) {
            var id = id;
            // ajax
            $.ajax({
                type: "POST",
                url: "{{ url('deleteProduct') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    var oTable = $('#ajax-crud-datatable').dataTable();
                    oTable.fnDraw(false);
                    Swal.fire({
                        title: "Eliminado!",
                        text: "Su registro fue eliminado.",
                        icon: "success"
                    });
                }
            });
        }
        $('#productForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            // Validación adicional: si utilidad detal > 0, nombre detal es requerido
            let utilidadDetal = parseFloat($('input[name="utility_detal"]').val()) || 0;
            let nameDetal = $('input[name="name_detal"]').val().trim();
            if (utilidadDetal > 0 && nameDetal === '') {
                $('#name_detalError').html('El nombre detal es obligatorio si hay utilidad detal.');
                $('input[name="name_detal"]').focus();
                return false;
            }
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: "{{ url('storeProduct') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#product-modal").modal('hide');
                    $('#products-table').DataTable().ajax.reload();
                    $("#btn-save").html('Enviar');
                    $("#btn-save").attr("disabled", false);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Log saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function(error) {
                    if (error && error.responseJSON && error.responseJSON.errors) {
                        $('#codeError').html(error.responseJSON.errors.code);
                        $('#code2Error').html(error.responseJSON.errors.code2);
                        $('#nameError').html(error.responseJSON.errors.name);
                        $('#name_detalError').html(error.responseJSON.errors.name_detal);
                        $('#costError').html(error.responseJSON.errors.cost);
                        $('#utilityDetalError').html(error.responseJSON.errors.utility_detal);
                        $('#priceDetalError').html(error.responseJSON.errors.price_detal);
                        $('#utilityError').html(error.responseJSON.errors.utility);
                        $('#priceError').html(error.responseJSON.errors.price);
                        $('#utility2Error').html(error.responseJSON.errors.utility2);
                        $('#price2Error').html(error.responseJSON.errors.price2);
                        $('#utility3Error').html(error.responseJSON.errors.utility3);
                        $('#price3Error').html(error.responseJSON.errors.price3);
                        $('#stock_minError').html(error.responseJSON.errors.stock_min);
                        $('#ivaError').html(error.responseJSON.errors.iva);
                        $('#unitsError').html(error.responseJSON.errors.units);
                        $('#urlError').html(error.responseJSON.errors.url);
                        $('#modal_currency_idError').html(error.responseJSON.errors.modal_currency_id);
                        $('#id_categoryError').html(error.responseJSON.errors.id_category);
                    }
                }
            });
        });

        function micheckbox(id) {
            console.log('entro');
            //Verifico el estado del checkbox, si esta seleccionado sera igual a 1 de lo contrario sera igual a 0
            var id = id;
            $.ajax({
                type: "GET",
                dataType: "json",
                //url: '/StatusNoticia',
                url: "{{ url('statusProduct') }}",
                data: {
                    'id': id
                },
                success: function(data) {
                    console.log(data.status);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Modified status') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#status').html(function() {
                        let html = '';
                        if (data.status == '1') {
                            html = '<a class="btn btn-success cambia' + id +
                                '" href="javascript:void(0)" onClick="micheckbox(' + id +
                                ')">' +
                                'Activo' +
                                '</a>';
                        } else {
                            html = '<a class="btn btn-danger cambia' + id +
                                '" href="javascript:void(0)" onClick="micheckbox(' + id +
                                ')">' +
                                'Inactivo' +
                                '</a>';
                        }
                        return html;
                    });
                }
            });
        }

        function dropHandler(ev) {
            console.log(ev.target.dataset);
            // Prevent default behavior (Prevent file from being opened)
            ev.preventDefault();
            // Check for dropped items
            if (ev.dataTransfer.items) {
                [...ev.dataTransfer.items].forEach((item, i) => {
                    // Only process files
                    if (item.kind === "file") {
                        const file = item.getAsFile();
                        // Check if the file is an image
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                const base64Image = event.target.result.split(',')[
                                    1]; // Extract base64 data
                                const id = $('#id').val();
                                const formData = new FormData();
                                formData.append('image', base64Image);
                                formData.append('id', id);
                                // Send image data to Laravel controller via AJAX
                                $.ajax({
                                    url: "{{ url('/upload-image') }}", // Replace with your actual route
                                    method: 'POST',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    success: function(response) {
                                        Swal.fire({
                                            position: "top-end",
                                            icon: "success",
                                            title: "{{ __('Updated Image') }}",
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        $('#products-table').DataTable().ajax.reload();
                                        editFunc(response);
                                    },
                                    error: function(error) {
                                        Swal.fire({
                                            position: "top-end",
                                            icon: "error",
                                            title: "((__('Something is wrong')))",
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                    }
                                });
                            };
                            reader.readAsDataURL(file); // Read image as base64
                        } else {
                            Swal.fire({
                                position: "top-end",
                                icon: "error",
                                title: "((__('Something is wrong')))",
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    }
                });
            } else {
                console.error('No files dropped.');
            }
        }
        // Event listener for dropzone
        var dropzones = document.querySelectorAll('[data-url]');
        dropzones.forEach(dropzone => {
            dropzone.addEventListener('drop', dropHandler);
            dropzone.addEventListener('dragover', (ev) => {
                ev.preventDefault();
            });
        });


        function addCategory() {
            $('#categoryForm').trigger("reset");
            $('.error-messages').html('');
            $('#category-modal').modal('show');
            $('#id_category').val('');
        }
        $('#categoryForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: "{{ url('storeCategory') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#category-modal").modal('hide');
                    $("#btn-save").html('Enviar');
                    $("#btn-save").attr("disabled", false);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Log saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    var newOption = new Option(data.name, data.id, false, true);
                    // Obtener el select y agregar la nueva opción
                    var selectCategory = $('.js-example-basic-multiple-category');
                    selectCategory.append(newOption).trigger(
                        'change'); // Append y trigger 'change' para Select2
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#nameError').html(error.responseJSON.errors.name);
                        $('#descriptionError').html(error.responseJSON.errors.description);
                    }
                }
            });
        });
    </script>
@endsection
