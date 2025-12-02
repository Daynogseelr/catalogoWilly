@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'shopping'])
@section('content')
    <style>
        .product td input {
            width: 100%;
            text-align: right;
            margin: 0 auto;
            display: block;
        }
    </style>
    <div class="container-fluid py-1">
        <div class="row mt-1">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info mb-2" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-7 col-sm-8">
                                        <h4>{{ __('Add Shopping') }}</h4>
                                    </div>
                                    <div class="col-5 col-sm-4 text-end">
                                        <a class="btn btn-danger2" onClick="add()" href="javascript:void(0)">
                                            Agregar nuevo producto <i class="fa-solid fa-circle-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <form id="shoppingForm" name="shoppingForm" class="form-horizontal" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-2 col-sm-2 form-outline">
                                    <input name="codeBill" type="text" class="form-control" id="codeBill"
                                        placeholder="{{ __('Code') }} {{ __('Bill') }}"
                                        title="Es obligatorio un codigo" minlength="1" maxlength="100" required
                                        onkeyup="mayus(this);" autocomplete="off">
                                    <label class="form-label" for="form2Example17">{{ __('Code') }}
                                        {{ __('Bill') }}</label>
                                    <span id="codeBillError" class="text-danger error-messages"></span>
                                </div>
                                <div class="col-md-2 col-sm-2 form-outline">
                                    <input name="date" type="date" class="form-control" id="date" required
                                        onkeyup="mayus(this);" autocomplete="off">
                                    <label class="form-label" for="form2Example17">{{ __('Date') }}</label>
                                    <span id="dateError" class="text-danger error-messages"></span>
                                </div>
                                <div class="col-md-6 col-sm-6 form-outline">
                                    <input name="name" type="text" class="form-control" id="name"
                                        placeholder="{{ __('Name') }} del Proveedor" title="Es obligatorio un nombre"
                                        minlength="2" maxlength="200" required onkeyup="mayus(this);" autocomplete="off">
                                    <label class="form-label" for="form2Example17">{{ __('Name') }} del
                                        Proveedor</label>
                                    <span id="nameError" class="text-danger error-messages"></span>
                                </div>
                                <div class="col-md-2 col-sm-2 form-outline">
                                    <input name="total" type="text" class="form-control" id="total"
                                        placeholder="{{ __('Total') }} {{ __('Bill') }}"
                                        title="Es obligatorio un precio" minlength="1" maxlength="50" required
                                        onkeypress='return validaMonto(event)' autocomplete="off">
                                    <label class="form-label" for="form2Example17">{{ __('Total') }}
                                        {{ __('Bill') }}</label>
                                    <span id="totalError" class="text-danger error-messages"></span>
                                </div>
                                <div class="col-md-12 col-sm-12 form-outline">
                                    <select class="js-example-basic-multiple js-example-basic-multiple-products"
                                        name="id_product[]" multiple="multiple" data-placeholder="Seleccione un producto">
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->code }}
                                                {{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="form-label" for="form2Example17">{{ __('Product') }}</label>
                                    <span id="id_productError" class="text-danger error-messages"></span>
                                </div>
                                <div class="col-md-12 col-sm-12 form-outline">
                                    <div class="tabla table-responsive">
                                        <br>
                                        <table id="payment" class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th colspan="9" style="text-align: center;">{{ __('Products') }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th>{{ __('Nº') }}</th>
                                                    <th>{{ __('Code') }}</th>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Quantity') }}</th>
                                                    <th>{{ __('Cost') }}</th>
                                                    <th>
                                                        <select class="form-select form-select-sm" id="currency_table_id">
                                                            @foreach ($currencies as $currency)
                                                                <option
                                                                    {{ $currency->is_principal == 1 ? 'selected' : '' }}
                                                                    value="{{ $currency->id }}">
                                                                    {{ $currency->abbreviation }}</option>
                                                            @endforeach
                                                        </select>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="product" style="font-size: 12px !important;">
                                                <tr class="no-products-message">
                                                    <td style="text-align: center;" colspan="9">No hay productos
                                                        seleccionados.</td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr style="text-align: center;">
                                                    <td colspan="4" style="text-align: right;">
                                                        <strong>Totales:</strong>
                                                    </td>
                                                    <td id="totalCost">0.00</td>
                                                    <td id="totalPrice2">0.00</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-sm-offset-2 col-sm-12 text-center btn-solution">
                                    <button type="submit" class="btn btn-primary"
                                        id="btn-send">{{ __('Send') }}</button>
                                </div>
                        </form>
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
                    <h5 class="modal-title" id="modal-title">{{ __('Add Product') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="productForm" name="productForm" class="form-horizontal" method="POST"
                        enctype="multipart/form-data">
                        <div class="row">
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
                            <div class="col-md-3 col-sm-3">
                                <input type="text" class="form-control" id="stock_min" name="stock_min"
                                    value="0" onkeypress='return validaNumericos(event)' required>
                                <label class="form-label" for="form2Example17">{{ __('Stock minimo') }}<span
                                        class="text-danger">*</span></label>
                                <span id="stock_minError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-9 col-sm-9 form-outline">
                                <input name="url" type="file" class="form-control" id="url" multiple
                                    title="Es obligatorio una Imagen">
                                <label class="form-label" for="form2Example17">{{ __('Image') }}</label>
                                <span id="urlError" class="text-danger error-messages"></span>
                            </div>
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
                                                title="Es obligatorio un descuento" minlength="1" 
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
                                                title="Es obligatorio un descuento" minlength="1"
                                                maxlength="10" required onkeypress='return validaMonto(event)'>
                                            <label class="form-label" for="form2Example17">{{ __('Utility') }} % Precio 1
                                                <span class="text-danger">*</span></label>
                                            <span id="utilityError" class="text-danger error-messages"></span>
                                        </div>
                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <input name="price" type="text" class="form-control price-field"
                                                id="price" placeholder="{{ __('Price') }} 1"
                                                title="Es obligatorio un precio" minlength="1" maxlength="50" required
                                                onkeypress='return validaMonto(event)' autocomplete="off">
                                            <label class="form-label" for="form2Example17">{{ __('Price') }} 1 <span
                                                    class="text-danger">*</span></label>
                                            <span id="priceError" class="text-danger error-messages"></span>
                                        </div>

                                        <div class="col-md-4 col-sm-4 form-outline">
                                            <input name="price2_1" type="text" class="form-control price2-field"
                                                id="price2_1" placeholder="{{ __('Price 1 en moneda ') }}"
                                                title="Es obligatorio un precio" minlength="1" maxlength="80" required
                                                autocomplete="off">
                                            <label class="form-label" for="price2_1">{{ __('Precio total en moneda') }}
                                                <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-4 col-sm-4">
                                            <input name="utility2" type="text" class="form-control utility-field"
                                                id="utility2" placeholder="{{ __('Utility') }} Precio 2"
                                                title="Es obligatorio un descuento" minlength="1" 
                                                maxlength="10"  onkeypress='return validaMonto(event)'>
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
                                                title="Es obligatorio un descuento" minlength="1" value="0"
                                                maxlength="10"  onkeypress='return validaMonto(event)'>
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
                                    minlength="2" maxlength="100" onkeyup="mayus(this);" autocomplete="off">
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
    <script type="text/javascript">
        $(document).ready(function() {
            // Deshabilita todos los campos al abrir el modal
            function disableAllFields() {
                document.querySelectorAll('.utility-field').forEach(f => {
                    f.value = '';
                    f.disabled = true;
                });
                document.querySelectorAll('.price-field').forEach(f => {
                    f.value = '';
                    f.disabled = true;
                });
                document.querySelectorAll('.price2-field').forEach(f => {
                    f.value = '';
                    f.disabled = true;
                });
            }

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

            // Al abrir el modal, deshabilita todos los campos
            $('#product-modal').on('shown.bs.modal', function() {
                disableAllFields();
                $('#units').val(1);
            });

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
            // Variables de tasas de cambio
            const currencies = @json($currencies);
            let tasaCambio = 1;
            const products = @json($products);
            let counter = 1;
            // Seriales temporales por producto
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
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
                        precio2Input.val(isFinite(precio) ? (precio * tasaCambio).toFixed(2) : '');
                    } else {
                        // Si no hay costo o utilidad vacía, limpiamos
                        precioInput.val('');
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
                    } else if (precio > 0 && costo === 0) {
                        // Si costo es cero, utilidad es indefinida; dejamos campo utilidad vacío o 0 según preferencia
                        utilidadInput.val('');
                        precio2Input.val((precio * tasaCambio).toFixed(2));
                    } else {
                        utilidadInput.val('');
                        precio2Input.val('');
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
                        }
                    }
                });
            }

            // Inicializa la función al abrir el modal
            $('#product-modal').on('shown.bs.modal', function() {
                actualizarCamposModalProducto();
            });
            // Unifica la moneda seleccionada en toda la vista
            function updateCurrencyAll(currencyId) {
                currencies.forEach(currency => {
                    if (currency.id == currencyId) {
                        tasaCambio = currency.rate;
                    }
                });
                // Actualiza precios en la tabla
                $('.product tr').each(function() {
                    let priceInput = $(this).find('.price');
                    let price2Input = $(this).find('.price2');
                    let price = parseFloat(priceInput.val()) || 0;
                    price2Input.val((price * tasaCambio).toFixed(2));
                });
                // Actualiza precio en el modal si está   
                let priceModal = parseFloat($('#price').val()) || 0;
                $('#price2').val((priceModal * tasaCambio).toFixed(2));
                recalculateTotals();
            }
            // Initialize Select2
            $('.js-example-basic-multiple-products').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                closeOnSelect: false,
                selectionCssClass: "form-select",
                language: "es"
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

            // Product selection change event
            // Cuando cambie la moneda principal, actualiza todo
            $('#currency_id').on('change', function() {
                updateCurrencyAll($(this).val());
            });
            // Actualiza precios según moneda seleccionada en la tabla
            $('#currency_table_id').on('change', function() {
                let currencyId = $(this).val();
                currencies.forEach(currency => {
                    if (currency.id == currencyId) {
                        tasaCambio = currency.rate;
                    }
                });
                $('.product tr').each(function() {
                    let costInput = $(this).find('.cost');
                    let price2Input = $(this).find('.price2');
                    let cost = parseFloat(costInput.val()) || 0;
                    price2Input.val((cost * tasaCambio).toFixed(2));
                });
                recalculateTotals();
            });

            // Actualiza el precio2 en el modal según la moneda seleccionada
            $('#price').on('input', function() {
                let price = parseFloat($(this).val()) || 0;
                $('#price2').val((price * tasaCambio).toFixed(2));
            });

            // Actualiza el precio2 en la tabla principal según la moneda seleccionada
            $('.product').on('input', '.price', function() {
                let price = parseFloat($(this).val()) || 0;
                let price2Input = $(this).closest('tr').find('.price_bs');
                price2Input.val((price * tasaCambio).toFixed(2));
                recalculateTotals();
            });


            $(document).on('change', '.js-example-basic-multiple-products', function(event) {
                $.ajax({
                    type: "POST",
                    url: "{{ url('addProductShopping') }}",
                    data: {
                        id_product: $('.js-example-basic-multiple').val(),
                    },
                    dataType: 'json',
                    success: function(res) {
                        let tableBody = $('.product');
                        let selectedProducts = res.products || [];
                        let existingProducts = {};
                        tableBody.find('tr').each(function() {
                            let productId = $(this).find('input').data('id');
                            if (productId) {
                                existingProducts[productId] = $(this);
                            }
                        });
                        let counter = 1;
                        tableBody.find('tr.no-products-message').remove();
                        selectedProducts.forEach(product => {
                            let partes = product.name.match(/.{1,80}/g);
                            let name = partes.join("<br>");
                            if (existingProducts[product.id]) {
                                let row = existingProducts[product.id];
                                row.find('td:eq(0)').text(counter++);
                                // ...actualiza otras celdas si lo necesitas
                            } else {
                                let cantidad = 0;
                                let cantidadInput = $(
                                    `.product tr[data-id="${product.id}"]`).find(
                                    '.quantity');
                                if (cantidadInput.length) {
                                    cantidad = parseInt(cantidadInput.val()) || 0;
                                }
                                let row = `
                                    <tr data-id="${product.id}">
                                        <td style="text-align: center;">${counter++}</td>
                                        <td style="text-align: center;">${product.code}</td>
                                        <td>${name}</td>
                                        <td class="center"><input type="text" name="quantity[]" value="0" class="form-control quantity" data-id="${product.id}" onkeypress='return validaNumericos(event)' style="width: 60px;"></td>
                                        <td class="center"><input type="text" name="cost[]" value="${product.cost}" class="form-control cost" data-id="${product.id}" onkeypress='return validaMonto(event)' style="width: 90px;"></td>
                                        <td class="center"><input type="text" name="price2[]" value="${(product.cost * tasaCambio).toFixed(2)}" class="form-control price2" data-id="${product.id}" onkeypress='return validaMonto(event)' style="width: 120px;"></td>
                                    </tr>
                                `;
                                tableBody.append(row);                              
                            }
                        });
                        tableBody.find('tr').each(function() {
                            let productId = $(this).find('input').data('id');
                            if (productId && !selectedProducts.find(p => p.id ==
                                    productId)) {
                                $(this).remove();
                            }
                        });
                        recalculateTotals();
                    },
                    error: handleError
                });
            });

            // Evento input para cost, utility, price, price2 en la tabla
            $('.product').on('input', '.cost, .utility, .price, .price2', function() {
                const row = $(this).closest('tr');
                const costInput = row.find('.cost');
                const precio2Input = row.find('.price2');
                let costo = parseFloat(costInput.val()) || 0;
                let precio2 = parseFloat(precio2Input.val()) || 0;
                if ($(this).hasClass('cost') || $(this).hasClass('utility')) {
                    precio2 = costo * tasaCambio;
                    precio2Input.val(precio2.toFixed(2));
                } else if ($(this).hasClass('price2')) {
                    costo = precio2 / tasaCambio;
                    costInput.val(costo.toFixed(2));
                }
                recalculateTotals();
            });
            // Evento para abrir el modal de seriales al cambiar cantidad
            $('.product').on('change', '.quantity', function() {
                const row = $(this).closest('tr');
                const productId = $(this).data('id');
                const quantity = parseInt($(this).val()) || 0;     
                recalculateTotals(); // <-- Agrega esta línea
            });


            function recalculateTotals() {
                let totalQuantity = 0;
                let totalCost = 0;
                let totalPrice2 = 0;
                let total = 0;
                $('.product tr').each(function() {
                    let rowQuantity = parseFloat($(this).find('.quantity').val()) || 0;
                    let rowCost = parseFloat($(this).find('.cost').val()) || 0;
                    let rowPrice2 = parseFloat($(this).find('.price2').val()) || 0;
                    total += (rowCost * rowQuantity);
                    totalCost += (rowCost * rowQuantity);
                    totalPrice2 += (rowPrice2 * rowQuantity);
                });
                const totalId = $('#total');
                totalId.val(total.toFixed(2));
                updateTotals(totalCost, totalPrice2);

            }

            function updateTotals(totalCost,totalPrice2) {
                $('#totalCost').text(totalCost.toFixed(2));
                $('#totalPrice2').text(totalPrice2.toFixed(2));
            }


            function handleError(error) {
                if (error) {
                    console.error("AJAX Error:", error); // More descriptive error message
                    if (error.responseJSON && error.responseJSON.errors) {
                        console.log("Validation Errors:", error.responseJSON.errors);
                    }
                }
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
                        const newProductData = data;
                        products.push(newProductData);
                        $("#product-modal").modal('hide');
                        let newOption = new Option(
                            newProductData.code + " " + newProductData.name,
                            newProductData.id,
                            true,
                            true
                        );
                        $('.js-example-basic-multiple-products').append(newOption).trigger(
                            'change');
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
            $('#shoppingForm').submit(function(event) {
                event.preventDefault();
                // Seriales por producto (ya guardados en serialesTemp)
                // Obtener datos del formulario
                const id_inventory = $('#id_inventory').val();
                const codeBill = $('#codeBill').val();
                const date = $('#date').val();
                const nameProvider = $('#name').val();
                const totalBill = $('#total').val();
                const selectedProducts = $('.js-example-basic-multiple-products').val();
                // Obtener datos de la tabla de productos
                const productsTableData = [];
                $('.product tr').each(function() {
                    const productId = $(this).find('.quantity').data('id');
                    const quantity = $(this).find('.quantity').val();
                    const cost = $(this).find('.cost').val();
                    const price2 = $(this).find('.price2').val();
                    if (productId) {
                        productsTableData.push({
                            id: productId,
                            quantity: quantity,
                            cost: cost,
                            price2: price2
                        });
                    }
                });

                // Crear objeto con todos los datos
                const datas = {
                    id_inventory: id_inventory,
                    codeBill: codeBill,
                    date: date,
                    nameProvider: nameProvider,
                    totalBill: totalBill,
                    selectedProducts: selectedProducts, // Productos del select2
                    productsTableData: productsTableData, // Productos de la tabla
                    currency_id: $('#currency_table_id').val(),
                };
                // Enviar solicitud AJAX
                $.ajax({
                    url: "{{ route('storeShopping') }}", // Usar la ruta con nombre
                    type: 'POST',
                    data: datas,
                    dataType: 'json', // Esperar respuesta en formato JSON
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content') // Incluir token CSRF
                    },
                    success: function(response) {
                        // Manejar la respuesta del servidor (ej: mostrar mensaje de éxito)
                        console.log(response);
                        Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "{{ __('Log saved successfully') }}",
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#shoppingForm')[0].reset(); // Limpiar el formulario
                        $('.js-example-basic-multiple-products').val(null).trigger(
                            'change'); // Limpiar select2
                        $('.product').empty(); // Limpiar la tabla
                        $('.product').append(
                            '<tr class="no-products-message"><td style="text-align: center;" colspan="8">No hay productos seleccionados.</td></tr>'
                        );
                        updateTotals(0, 0, 0, 0);
                        const pdfLink = "{{ route('pdfShopping', ':id') }}".replace(':id',
                            response.id);
                        // Create a temporary anchor element for the click even
                        window.open(pdfLink, '_blank');
                    },
                    error: function(error) {
                        // Manejar errores de la solicitud
                        console.error(error);
                        alert('Error al registrar la compra. Por favor, inténtelo de nuevo.');
                        if (error.responseJSON && error.responseJSON.errors) {
                            // Mostrar errores de validación en el formulario
                            $.each(error.responseJSON.errors, function(key, value) {
                                $('#' + key + 'Error').text(value[
                                    0
                                ]); // Mostrar el primer mensaje de error para cada campo.
                                $('#' + key).addClass(
                                    'is-invalid'); // Agregar clase de error al input
                            });
                        }
                    }
                });
            });

            function deleteSerial(indexId) {
                $('#tr' + indexId).html('');
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
                        var oTable = $('#ajax-crud-datatable').dataTable();
                        oTable.fnDraw(false);
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
        });

        function add() {
            $.ajax({
                type: "POST",
                url: "{{ url('codeProduct') }}",
                success: function(res) {
                    $('#selectProduct').hide();
                    $('#productForm').trigger("reset");
                    $('.error-messages').html('');
                    $('#product-modal').modal('show');
                    $('#id').val('');
                    $('#code').val(res);
                }
            });
        }

        function addCategory() {
            $('#categoryForm').trigger("reset");
            $('.error-messages').html('');
            $('#category-modal').modal('show');
            $('#id_category').val('');
        }
    </script>
@endsection
