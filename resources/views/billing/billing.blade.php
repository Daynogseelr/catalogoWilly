@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'billing'])
@section('content')
    <div class="container-fluid ms-1">
        <div class="row ">
            <div class="col-12 mb-4" id="hiddeProduct"> {{-- d-none to hide by default --}}
                <div class="card shadow-lg border-0 rounded-4 animate__animated animate__fadeIn">
                    <div class="card-header rounded-top-4 py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-info fw-bold">Productos</h4>
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" onClick="hiddeProduct()">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-items-center mb-0"
                                id="ajax-crud-datatableBilling">
                                <thead>
                                    <tr>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder">
                                            {{ __('Code') }}</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder ps-2">
                                            {{ __('Name') }}</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder text-end ps-2">
                                            {{ __('Stock') }}</th>
                                        <th class="text-center" style="width: 150px !important;">
                                            <select class="form-select" id="currencySelectProduct">
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}"
                                                        {{ $currency->is_official == 1 ? 'selected' : '' }}
                                                        data-tasa="{{ $currency->rate }}"
                                                        data-abbr="{{ $currency->abbreviation }}">
                                                        {{ $currency->name }} ({{ $currency->abbreviation }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </th>
                                        <th class="text-center text-secondary">{{ __('Action') }}</th>
                                        {{-- Action --}}
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mb-1" id="hiddeBill">
            <div class="card shadow-lg border-0 rounded-4 animate__animated animate__fadeIn">
                <div class="card-header rounded-top-4 py-1 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 ">{{ __('Bill') }}</h4>
                    <button class="btn btn-light align-items-center mt-2" onClick="billWait();">
                        <i class="fa-regular fa-eye "></i> {{ __(' En Espera') }}
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-2 align-items-end">
                        {{-- Client Select --}}
                        <div class="col-md-7 col-sm-6 col-12">
                            <label for="single-select-field"
                                class="form-label text-muted text-sm">{{ __('Select Client') }}</label>
                            <select class="form-select form-control-lg rounded-3" name="client" id="single-select-field"
                                data-placeholder="{{ __('Select client') }}">
                                <option></option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @if (isset($id_shopper) && $id_shopper == $client->id) selected @endif>
                                        {{ $client->name }} {{ $client->last_name ? $client->last_name : '' }}
                                        {{ $client->nationality }}-{{ $client->ci }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-3 col-6">
                            <label for="cod"
                                class="form-label text-muted text-sm">{{ __('Code / Search (*)') }}</label>
                            <input name="cod" type="text" class="form-control rounded-3 " id="cod"
                                placeholder="{{ __('Code / Search use (*)') }}"
                                title="{{ __('Enter code or use * for search') }}" minlength="1" maxlength="20" required
                                onkeyup="mayus(this);" autocomplete="off">
                        </div>
                        {{-- Document Number Input (with form retained and robust alignment) --}}
                        <div class="col-md-2 col-sm-3 col-6">
                            <label for="document" class="form-label form-label2 text-muted text-sm">{{ __('Document No') }}</label>
                            <div class="d-flex align-items-end">
                                <form class="form mb-0" action="{{ route('budget') }}" method="POST" id="myForm">
                                    @csrf
                                    <input name="code" type="text" class="form-control rounded-3" id="document"
                                            placeholder="{{ __('Document No') }}" title="{{ __('Enter document number') }}"
                                            minlength="1" maxlength="20" required onkeyup="mayus(this);" autocomplete="off">
                                    <button class="btn btn-primary ms-2 d-none" type="submit" id="btnDocument">{{ __('Buscar') }}</button>
                                </form>
                                <a href="{{ route('pdf.last') }}" target="_blank" class="btn btn-outline-secondary ms-2 d-flex align-items-center justify-content-center"
                                    id="btnPrintLastBill" title="{{ __('Imprimir último comprobante') }}" style="height: calc(2.375rem + 2px); padding: .375rem .75rem;">
                                    <i class="fa-solid fa-file-lines"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    {{-- Price & Billing Options Section --}}
                    <div class="row g-3 mb-3 align-items-center">
                        <div class="col-md-3 col-sm-4 col-11">
                            <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-start"
                                style="min-height: 58px;">
                                <p class="text-sm text-dark font-weight-bold mb-0 me-2">{{ __('Tasa') }}:</p>
                                <h3 class="text-primary2 font-weight-bolder mb-0">{{ $currencyOfficial->rate }}</h3>
                            </div>
                        </div>
                        <div class="col-md-7 col-sm-4 col-7">
                        </div>
                        <div class="col-md-2 col-sm-4 col-6">
                            <label for="currencySelect"
                                class="form-label text-muted text-sm">{{ __('Pago en Moneda') }}</label>
                            <select class="form-select form-control-lg rounded-3" name="currencySelect" id="currencySelect">
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}" data-tasa="{{ $currency->rate }}"
                                        {{ $currency->is_official == 1 ? 'selected' : '' }}>
                                        {{ $currency->name }} ({{ $currency->abbreviation }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- Product List Table Section --}}
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-white pb-0 pt-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ __('Productos Añadidos') }}</h5>
                            <span class="btnAll text-end">
                                {{-- Your button or content for btnAll goes here --}}
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive tabla dt">
                            </div>
                        </div>
                        <div class="row justify-content-center"> {{-- Added row and justify-content-center --}}
                            <div class="col-12 col-md-8 col-lg-6"> {{-- Added responsive column classes --}}
                                <div class="card shadow-lg border-0 rounded-4 mb-2">
                                    <div class="card-body p-2">
                                        <div class="table-responsive">
                                            <table id="total"
                                                class="table table-striped table-bordered table-hover align-middle total">
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" style="text-align: center;">
                            <div class="button"></div>
                        </div>
                        <a id="ahidden" href="" target="_blank" hidden></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 mb-lg-0 mb-4" id="hiddeBillWait">
            <div class="card z-index-2 h-100">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <div class="row">
                        <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                            <div class="row">
                                <div class="col-11 col-sm-11">
                                    <h4>{{ __('Facturas en Espera') }}</h4>
                                </div>
                                <div class="col-1 col-sm-1 text-end">
                                    <a style="color:black; padding: 4px 5px 0px 5px; height: 25px; text-decoration: none;"
                                        href="javascript:void(0)" onClick="hiddeBillWite()">X</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="card-body">
                        <div class="tabla table-responsive" style="font-size: 13px;">
                            <table class="table table-striped" id="ajax-crud-datatableBillWait"
                                style="font-size: 13px; width: 98% !important;">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{ __('Date') }}</th>
                                        <th class="text-center">{{ __('Seller') }}</th>
                                        <th class="text-center">{{ __('Client') }}</th>
                                        <th class="text-center">{{ __('Identification Document') }}</th>
                                        <th class="text-center" style="width: 150px !important;">
                                            <select class="form-select" id="currencySelectWait">
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}"
                                                        {{ $currency->is_official == 1 ? 'selected' : '' }}
                                                        data-tasa="{{ $currency->rate }}">
                                                        {{ $currency->name }} ({{ $currency->abbreviation }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </th>
                                        <th class="text-center">{{ __('Action') }}</th>
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
    <!-- boostrap client model -->
    <div class="modal fade" id="client-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{ __('Add Client') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="form" action="{{ route('storeShopper') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="name" type="text" class="form-control" id="name"
                                    value="{{ old('name') }}" placeholder="{{ __('Name') }}"
                                    title="Es obligatorio un nombre" minlength="2" maxlength="250" required
                                    onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Name') }}</label>
                                @error('name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-4 col-sm-4" style="padding-right:0">
                                            <select class="form-select required" name="nationality">
                                                <option value="V">V</option>
                                                <option value="E">E</option>
                                                <option value="J">J</option>
                                                <option value="G">G</option>
                                            </select>
                                        </div>
                                        <div class="col-8 col-sm-8" style="padding-left:0">
                                            <input name="ci" type="text" class="form-control" id="ci"
                                                value="{{ old('ci') }}"
                                                placeholder="{{ __('Identification Document') }}"
                                                title="Es obligatorio una cedula" minlength="7" maxlength="10" required
                                                onkeypress='return validaNumericos(event)' onkeyup="mayus(this);"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <label class="form-label"
                                        for="form2Example17">{{ __('Identification Document') }}</label>
                                    @error('ci')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline mb-2">
                                <input name="phone" type="text" class="form-control" id="phone"
                                    value="{{ old('phone') }}" placeholder="{{ __('Phone') }}"
                                    title="Es obligatorio un telefono" maxlength="11"
                                    onkeypress='return validaNumericos(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17"> {{ __('Phone') }}</label>
                                @error('phone')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline mb-2">
                                <input name="direction" type="text" class="form-control" id="direction"
                                    value="{{ old('direction') }}" placeholder="{{ __('Direction') }}"
                                    title="Es obligatorio un direccion" maxlength="200" onkeyup="mayus(this);"
                                    autocomplete="off">
                                <label class="form-label" for="form2Example17"> {{ __('Direction') }}</label>
                                @error('direction')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline mb-2">
                                <input name="email" type="text" class="form-control" id="email"
                                    value="{{ old('email') }}" placeholder="{{ __('Email') }}"
                                    title="Es obligatorio un correo" maxlength="50" autocomplete="off"
                                    onkeyup="mayus(this);">
                                <label class="form-label" for="form2Example17">{{ __('Email') }}</label>
                                @error('email')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
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
                                <input name="existencia" type="text" class="form-control" id="existencia"
                                    placeholder="1" title="Es obligatorio una cantidad" min="1" required
                                    autocomplete="off" disabled>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    {{-- Modal para facturar --}}
    <div class="modal fade" id="facturarModal" tabindex="-1" aria-labelledby="facturarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <input type="hidden" id="facturar_order_id">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="facturarModalLabel">Registrar Pagos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Método de Pago</label>
                            <select class="form-select" id="pago_metodo">
                                <option value="">Seleccione...</option>
                                @foreach ($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}" data-moneda="{{ $pm->currency->abbreviation }}"
                                        data-currency="{{ $pm->id_currency }}" data-reference="{{ $pm->reference }}">
                                        {{ $pm->type }} ({{ $pm->currency->abbreviation }}) {{ $pm->bank ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Monto</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="pago_monto">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Referencia</label>
                            <input type="text" class="form-control d-none" id="pago_referencia">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" id="btnAgregarPago">Agregar</button>
                        </div>
                    </div>
                    <div class="row ">
                        <div class="col-md-12 col-sm-12 form-outline">
                            <div class="tabla table-responsive">
                                <table id="dtNoteCredit" class="table table-striped dtNoteCredit">
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h6>Pagos Registrados</h6>
                        <table class="table table-bordered table-sm" id="tablaPagos">
                            <thead>
                                <tr>
                                    <th>Método</th>
                                    <th>Moneda</th>
                                    <th>Monto</th>
                                    <th>Referencia</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <div class="text-end">
                            <strong>Total Restante: <span id="totalRestante"></span></strong>
                            <span id="vueltoInfo"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnProcesarPagos" onClick="storeBill()"
                        disabled>Registrar
                        Pagos</button>
                </div>
            </div>
        </div>
    </div>
    <!-- boostrap mostrar dias creditos modal -->
    <div class="modal fade" id="creditDays-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{ __('Registrar Dias de Credito') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 form-outline mb-2">
                            <input name="creditDays" type="text" class="form-control" id="creditDays"
                                placeholder="{{ __('Credit Days') }}" title="Es obligatorio los dias de creditos"
                                maxlength="11" onkeypress='return validaNumericos(event)' autocomplete="off" required>
                            <label class="form-label" for="form2Example17"> {{ __('Credit Days') }}</label>
                        </div>
                        <div class="col-sm-offset-2 col-sm-12 text-center"><br />
                            <button type="buttom" class="btn btn-primary"
                                onClick="storeCredit()">{{ __('Send') }}</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- Modal Selección de Precio -->
    <div class="modal fade" id="selectPriceModal" tabindex="-1" aria-labelledby="selectPriceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="selectPriceModalLabel">Selecciona el precio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="d-grid gap-2"></div>
                    <div class="mt-3 text-muted" style="font-size:0.9em;">
                        También puedes presionar la tecla <b>1</b>, <b>2</b>, <b>3</b> o <b>4</b> para seleccionar.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    @if ($openSmallBoxModal)
        <div class="modal fade" id="smallBoxInitialModal" tabindex="-1" aria-labelledby="smallBoxInitialModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="smallBoxInitialModalLabel">Abrir Caja Chica</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="smallBoxForm" action="{{ route('smallbox.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3 row">
                                <div class="col-7">
                                    <label for="currencySelectSmallBox" class="form-label">Moneda</label>
                                    <select class="form-select" id="currencySelectSmallBox">
                                        <option value="">Seleccione...</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}"
                                                data-abbr="{{ $currency->abbreviation }}">
                                                {{ $currency->name }} ({{ $currency->abbreviation }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-5">
                                    <label for="cashDolar" class="form-label">Cantidad de Efectivo</label>
                                    <input type="text" step="0.01" min="0" class="form-control"
                                        onkeypress='return validaMonto(event)' id="cashDolar" autocomplete="off">
                                </div>
                            </div>
                            <div class="mb-2">
                                <button type="button" class="btn btn-info w-100" id="btnAddSmallBox">Agregar</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="smallBoxTable">
                                    <thead>
                                        <tr>
                                            <th>Moneda</th>
                                            <th>Monto</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Las filas se agregan dinámicamente --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" id="btnSaveSmallBox" disabled>Guardar Caja
                                Chica</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalEl = document.getElementById('smallBoxInitialModal');
                if (modalEl) {
                    var myModal = new bootstrap.Modal(modalEl, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    myModal.show();
                }
            });
        </script>
    @endif
    @include('footer')
    <!-- Supervisor Authorization Modal -->
    <div class="modal fade" id="supervisorAuthModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Autorización Supervisor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label for="supervisor_password">Clave de Autorización</label>
                        <input type="password" id="supervisor_password" class="form-control" />
                        <div id="supervisorAuthError" class="text-danger small mt-1"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="supervisorAuthSubmit" class="btn btn-primary">Validar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @if (isset($id_shopper))
        @if ($id_shopper == 0)
            <script>
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "No se encontro ningun documento!",
                });
            </script>
        @endif
    @endif
    @if (session('success'))
        <script>
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Cliente Registrado',
                showConfirmButton: false,
                timer: 1500
            })
        </script>
    @endif
    @if (session('success2'))
        <script>
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Caja chica registrada',
                showConfirmButton: false,
                timer: 1500
            })
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Cliente no registrado',
                showConfirmButton: false,
                timer: 1500
            })
        </script>
    @endif
    {{-- Display validation errors --}}
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <script>
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: {{ $error }},
                    showConfirmButton: false,
                    timer: 1500
                })
            </script>
        @endforeach
    @endif
    <script type="text/javascript">
        let smallBoxData = [];
        var productDataTable;
        var currencies = @json($currencies);
        var currencyOfficial = @json($currencyOfficial);
        var currencyPrincipal = @json($currencyPrincipal);
        let billWaitTableInitialized = false;
        let billProductTableInitialized = false;
        var tasaCambio = $('#currencySelect option:selected').data('tasa');
        var tasaCambioWait = $('#currencySelectWait option:selected').data('tasa');
        var tasaCambioProduct = $('#currencySelectProduct option:selected').data('tasa');
        $(document).ready(function() {
            $('#btnAddSmallBox').on('click', function() {
                let id = $('#currencySelectSmallBox').val();
                let abbr = $('#currencySelectSmallBox option:selected').data('abbr');
                let cash = $('#cashDolar').val();
                if (!id || !cash || parseFloat(cash) <= 0) {
                    Swal.fire('Error', 'Seleccione moneda y monto válido.', 'error');
                    return;
                }
                if (smallBoxData.find(item => item.id == id)) {
                    Swal.fire('Error', 'Ya agregó esta moneda.', 'error');
                    return;
                }
                smallBoxData.push({
                    id,
                    abbr,
                    cash
                });
                updateSmallBoxTable();
                updateCurrencySelect();
            });
            $(document).on('click', '.btn-remove-currency', function() {
                let idx = $(this).data('idx');
                smallBoxData.splice(idx, 1);
                updateSmallBoxTable();
                updateCurrencySelect();
            });
            $('#smallBoxForm').on('submit', function(e) {
                e.preventDefault();
                if (smallBoxData.length === 0) {
                    Swal.fire('Error', 'Agregue al menos una moneda.', 'error');
                    return;
                }
                // Agrega los datos como inputs ocultos
                $(this).find('input[name^="small_boxes"]').remove();
                smallBoxData.forEach((item, idx) => {
                    $(this).append(
                        `<input type="hidden" name="small_boxes[${idx}][id_currency]" value="${item.id}">`
                    );
                    $(this).append(
                        `<input type="hidden" name="small_boxes[${idx}][cash]" value="${item.cash}">`
                    );
                });
                this.submit();
            });
            document.getElementById("hiddeProduct").style.display = "none";
            document.getElementById("hiddeBillWait").style.display = "none";
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#currencySelectProduct').on('change', function() {
                tasaCambioProduct = $(this).find(':selected').data('tasa');
                productDataTable.ajax.reload();
            });
            $('#currencySelect').on('change', function() {
                tasaCambio = $(this).find(':selected').data('tasa');
                mostrarBill();
            });
            // Al cambiar el inventario, recarga la tabla con la moneda seleccionada
            $('.dataTables_wrapper').css({
                'height': '800px', // Altura fija del contenedor
                'overflow-y': 'auto' // Habilita el scroll vertical si el contenido excede la altura
            });
            $('#currencySelectWait').on('change', function() {
                tasaCambioWait = $(this).find(':selected').data('tasa');
                $('#ajax-crud-datatableBillWait').DataTable().ajax.reload();
            });
            $('#single-select-field').select2({
                theme: "bootstrap-5",
                width: function() {
                    return $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ?
                        '100%' : 'style';
                },
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                dropdownCssClass: "color",
                selectionCssClass: "form-select",
                language: "es",
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') {
                        return data;
                    }
                    if (data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                        var modifiedData = $.extend({}, data, true);
                        return modifiedData;
                    }
                    return null;
                }
            }).on('select2:select', function(e) { // Manejar el evento select2:select
                // Se ejecuta cuando se selecciona un elemento, ya sea con el mouse o con Enter.
                console.log("Elemento seleccionado:", e.params.data);
            }).on('select2:opening', function() { // Usar select2:opening para evitar problemas de enfoque
                // Eliminar el manejador de eventos anterior para evitar duplicados
                $('.select2-search__field').off('keydown');
            }).on('select2:open', function(e) {
                let $select = $(this);
                $('.select2-search__field').on('keydown', function(event) {
                    if (event.key === 'Enter') {
                        let searchTerm = $(this).val();
                        if (searchTerm.trim() !== "") {
                            let $match = $select.find('option').filter(function() {
                                return $(this).text().toUpperCase() === searchTerm
                                    .toUpperCase();
                            });
                            if ($match.length > 0) {
                                // SE ENCONTRÓ UNA COINCIDENCIA
                                console.log("Se encontró una coincidencia:", $match.val());
                                $select.val($match.val()).trigger(
                                    'change'); // Seleccionar la opción coincidente
                                $select.select2('close'); // Cerrar el dropdown
                                event
                                    .preventDefault(); // Evitar el comportamiento predeterminado del Enter
                                event.stopPropagation();
                                return false;
                            } else {
                                // NO SE ENCONTRÓ COINCIDENCIA
                                console.log("No se encontró coincidencia");
                                $('#ci').val(searchTerm);
                                $('#client-modal').modal('show');
                                $('#client-modal').on('shown.bs.modal', function(e) {
                                    $('#name').focus();
                                });
                                event.preventDefault();
                                event.stopPropagation();
                                return false;
                            }
                        }
                    }
                });
            });
        });

        function updateSmallBoxTable() {
            let tbody = '';
            smallBoxData.forEach((item, idx) => {
                tbody += `<tr>
                    <td>${item.abbr}</td>
                    <td>${parseFloat(item.cash).toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm btn-remove-currency" data-idx="${idx}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            $('#smallBoxTable tbody').html(tbody);
            $('#btnSaveSmallBox').prop('disabled', smallBoxData.length === 0);
        }

        function updateCurrencySelect() {
            // Oculta las monedas ya seleccionadas
            $('#currencySelectSmallBox option').each(function() {
                let val = $(this).val();
                if (val && smallBoxData.find(item => item.id == val)) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });
            $('#currencySelectSmallBox').val('');
            $('#cashDolar').val('');
        }

        function add() {
            $('#clientForm').trigger("reset");
            $('#modal-title').html("{{ __('Add Client') }}");
            $('.error-messages').html('');
            $('#client-modal').modal('show');
            $('#id').val('');
        }

        function mostrarProduct(id) {
            // Obtiene la tasa y la abreviatura de la moneda seleccionada
            let $selected = $('#currencySelectProduct option:selected');
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

        function mostrarBill() {
            $.ajax({
                type: "POST",
                url: "{{ url('mostrarBill') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(res) {
                    $('.dt').html('');
                    $('.dt').html(
                        '<table  id="supplie" class="table table-hover align-items-center mb-0">' +
                        '<thead>' +
                        '<tr style="width:100%; text-align: center; font-size: 12px !important;" >' +
                        '<th class="text-uppercase text-secondary text-xxs font-weight-bolder">{{ __('Code') }}</th>' +
                        '<th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-2">{{ __('Name') }}</th>' +
                        '<th class="text-uppercase text-secondary text-xxs font-weight-bolder text-end ps-2">{{ __('Price') }} (U)</th>' +
                        '<th class="text-uppercase text-secondary text-xxs font-weight-bolder text-end ps-2">{{ __('Quantity') }}</th>' +
                        '<th class="text-uppercase text-secondary text-xxs font-weight-bolder text-end ps-2">% {{ __('Discount') }}</th>' +
                        '<th class="text-uppercase text-secondary text-xxs font-weight-bolder text-end ps-2">{{ __('Discount') }}</th>' +
                        '<th class="text-uppercase text-secondary text-xxs font-weight-bolder text-end ps-2">{{ __('Price') }}</th>' +
                        '<th class="text-secondary opacity-7"></th>' +
                        '</tr>' +
                        '</thead>' +
                        '<tbody class="produ" style="font-size: 12px !important;">' +
                        '</tbody>' +
                        '</table>'
                    );
                    if (res.success == 'bien') {
                        $.each(res.bill_details, function(index, elemento) {
                            var priceColumnU = 0;
                            priceColumnU = elemento.price * tasaCambio;
                            const priceColumn = priceColumnU * elemento.quantity;
                            let partes = elemento.name.match(/.{1,65}/g);
                            var name = partes.join("<br>");
                            const priceDiscount = priceColumn * (elemento.discount_percent / 100);
                            const price = priceColumn - priceDiscount;
                            $('.produ').append(
                                '<tr>' +
                                '<td style="text-align: center;">' + elemento.code + '</td>' +
                                '<td>' + name + '</td>' +
                                '<td style="text-align: right; padding-right: 30px !important;">' +
                                priceColumnU.toFixed(2) + '</td>' +
                                '<td style="text-align: end; vertical-align: middle;">' +
                                '<div class="d-flex justify-content-end align-items-end" style="min-width:120px;">' +
                                '<input id="quantity' + elemento.id +
                                '" type="text" value ="' + elemento.quantity +
                                '" min="1" step="1" data-id="' + elemento.id +
                                '" data-id_product="' + elemento.id_product +
                                '" data-price_type="' + elemento.price_type +
                                '" class="form-control form-control-sm text-center quantity-input quantity" ' +
                                'style="width: 70px; border-radius: 0.5rem; font-size: 1rem; font-weight: 500; margin:0 8px;" ' +
                                'onkeypress="return validaMonto(event)">' +
                                '</div>' +
                                '</td>' +
                                '<td style="text-align: end; vertical-align: middle;">' +
                                '<div class="d-flex justify-content-end align-items-end" style="min-width:120px;">' +
                                '<input id="discount' + elemento.id +
                                '" type="text" value ="' + elemento.discount_percent +
                                '" min="1" step="1" data-id="' + elemento.id +
                                '" class="form-control form-control-sm text-center discount-input discount" ' +
                                'style="width: 70px; border-radius: 0.5rem; font-size: 1rem; font-weight: 500; margin:0 8px;" ' +
                                'onkeypress="return validaMonto(event)">' +
                                '</div>' +
                                '</td>' +
                                '<td style="text-align: right; padding-right: 30px !important;">' +
                                priceDiscount.toFixed(2) + '</td>' +
                                '<td style="text-align: right; padding-right: 30px !important;">' +
                                price.toFixed(2) + '</td>' +
                                '<td style="text-align: center;">' +
                                '<a style="padding: 4px; margin-bottom: -4px !important; font-size: 11px !important;" onClick="deleteBillDetail(' +
                                elemento.id +
                                ')" data-toggle="tooltip" class="delete btn btn-danger">' +
                                '<i class="fa-solid fa-trash-can"></i>' +
                                '</a>' +
                                '</td>' +
                                '</tr>'
                            );
                        });
                        $('.btnAll').html('');
                        $('.btnAll').append(
                            '<a style=" padding: 7px; font-size: 20px !important;" onClick="deleteBill()" data-toggle="tooltip" class="delete btn btn-danger">' +
                            '<i class="fa-solid fa-trash-can"></i>' +
                            '</a>'
                        );
                        $('.button').html('');
                        $('.button').append(
                            '<div class="row">' +
                            '<div class="col-3 text-center">' +
                            '<a id="btnProcessBudget" style=" padding: 5px; font-size: 15px !important;" onClick="authorizeAndStoreBudget()" data-toggle="tooltip" class="btn btn-info btn-lg rounded-pill">' +
                            'Procesar {{ __('Budget') }}' +
                            '</a>' +
                            '<div class="small text-muted mt-1">Código: -1</div>' +
                            '</div>' +
                            '<div class="col-3 text-center">' +
                            '<a id="btnProcessPayment" style=" padding: 5px; font-size: 15px !important;" onClick="facturar()" data-toggle="tooltip" class="btn btn-primary btn-lg rounded-pill">' +
                            '{{ __('Procesar Pago') }}' +
                            '</a>' +
                            '<div class="small text-muted mt-1">Código: -2</div>' +
                            '</div>' +
                            '<div class="col-3 text-center">' +
                            '<a id="btnProcessCredit" style=" padding: 5px; font-size: 15px !important;" onClick="authorizeAndStoreCredit()" data-toggle="tooltip" class="btn btn-success btn-lg rounded-pill">' +
                            'Factura a {{ __('Credit') }}' +
                            '</a>' +
                            '<div class="small text-muted mt-1">Código: -3</div>' +
                            '</div>' +
                            '<div class="col-3 text-center">' +
                            '<a id="btnProcessWait" style=" padding: 5px; font-size: 15px !important;" onClick="storeBillWait()" data-toggle="tooltip" class="btn btn-warning btn-lg rounded-pill">' +
                            '{{ __('Bill Wait') }}' +
                            '</a>' +
                            '<div class="small text-muted mt-1">Código: -4</div>' +
                            '</div>' +
                            '</div>'
                        );
                    } else {
                        $('.button').html('');
                        $('.btnAll').html('<p class="ocultar-en-movil">Catalogo (F10)</p>');
                        $('.produ').append(
                            '<tr>' +
                            '<td colspan="8" class="text-center text-muted py-4">{{ __('Aún no se han añadido elementos.') }}</td>' +
                            '</tr>'
                        );
                    }
                    summaryBill()
                    $('#cod').focus();
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }
        $(document).on('change', '.mode-fraction', function() {
            let billDetailId = $(this).data('id');
            let mode = $(this).val(); // "COMPLETO" o "FRACCION"
            $.ajax({
                type: "POST",
                url: "{{ url('updateFractionMode') }}",
                data: {
                    id: billDetailId,
                    mode: mode
                },
                dataType: 'json',
                success: function(res) {
                    console.log(res);
                    mostrarBill(); // Refresca la tabla
                },
                error: function(error) {
                    Swal.fire('Error', 'No se pudo actualizar el modo de venta.', 'error');
                }
            });
        });

        function deleteBillDetail(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('deleteBillDetail') }}",
                data: {
                    id: id,
                },
                dataType: 'json',
                success: function(res) {
                    console.log(res);
                    mostrarBill();
                }
            });
        }

        function summaryBill() {
            // Inicializa los acumuladores
            let total_amount = 0;
            let total_discount = 0;
            let total_price = 0;
            // Recorre cada fila de la tabla de productos añadidos
            $('#supplie tbody.produ tr').each(function() {
                // Obtiene los valores de las columnas (ajusta el índice si cambias el orden)
                let priceU = parseFloat($(this).find('td').eq(2).text()) || 0;
                let quantity = parseFloat($(this).find('input.quantity').val()) || 0;
                let discount = parseFloat($(this).find('td').eq(5).text()) || 0;
                let price = parseFloat($(this).find('td').eq(6).text()) || 0;
                total_amount += priceU * quantity;
                total_discount += discount;
                total_price += price;
            });
            // Moneda seleccionada y tasas
            var selectedCurrencyId = $('#currencySelect').val();
            // --- Generar select de monedas para referencia ---
            let refSelectHtml = '';
            let otherCurrencies = currencies.filter(function(c) {
                return c.id != selectedCurrencyId;
            });
            if (otherCurrencies.length > 0) {
                refSelectHtml =
                    '<select id="refCurrencySelect" class="form-select form-select-sm d-inline-block" style="width:auto;display:inline-block;">';
                otherCurrencies.forEach(function(currency) {
                    refSelectHtml += '<option value="' + currency.id + '" data-tasa="' +
                        currency.rate + '"  data-abbr="' +
                        currency.abbreviation + '">' + currency.abbreviation + '</option>';
                });
                refSelectHtml += '</select>';
            }
            // --- Calcular referencia en la moneda seleccionada del select ---
            let refValue = '';
            if (otherCurrencies.length > 0) {
                // Por defecto, usa la primera moneda del select
                let refTasa = otherCurrencies[0].rate;
                let refAbbr = otherCurrencies[0].abbreviation;
                refValue = (total_price / tasaCambio * refTasa).toFixed(2) + ' ' + refAbbr;
            } else {
                // Solo hay una moneda, muestra el mismo monto
                refValue = total_price.toFixed(2);
            }
            // Renderiza la tabla de totales
            $('.total').html('');
            $('.total').append(
                '<thead>' +
                '<tr>' +
                '<th scope="col" class="text-center">{{ __('Total') }}</th>' +
                '<th scope="col" class="text-center">{{ __('Discount') }}</th>' +
                '<th scope="col" class="text-center">{{ __('Total to pay') }}</th>' +
                '</tr>' +
                '</thead>' +
                '<tbody>' +
                '<tr class="fw-bold ">' +
                '<td class="text-end pe-4 fs-9">' + total_amount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + '</td>' +
                '<td class="text-end pe-4 fs-9">' + total_discount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + '</td>' +
                '<td class="fs-4 text-end pe-4 border border-2 border-dark text-primary2">' +
                total_price.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) +
                '</td>' +
                '</tr>' +
                '<tr class="fw-bold fs-6">' +
                '<td class="text-end pe-4"></td>' +
                '<td class="text-end pe-4">REF:' + (refSelectHtml ? refSelectHtml : '') + '</td>' +
                '<td class="text-end pe-4 text-secondary" id="refValueCell">' + refValue + '</td>' +
                '</tr>' +
                '</tbody>'
            );
            // --- Evento para actualizar la referencia al cambiar el select ---
            $('#refCurrencySelect').on('change', function() {
                let refTasa = $(this).find(':selected').data('tasa');
                let refAbbr = $(this).find(':selected').data('abbr');
                let refMonto = (total_price / tasaCambio * refTasa).toFixed(2);
                $('#refValueCell').text(refMonto + ' ' + refAbbr);
            });
        }
        let ignoreChangeEvent = false;
        $(document).on('change', 'select[name=client]', function(event) {
            if (ignoreChangeEvent) { // Comprueba la bandera
                ignoreChangeEvent = false; // Resetea la bandera inmediatamente
                return; // Sale de la función sin ejecutar el resto del código
            }
            var id_client = $('#single-select-field').val();
            if (id_client == '' || id_client == null) {
                Swal.fire({
                    title: "{{ __('Select client') }}",
                    text: "{{ __('Empty client') }}",
                    confirmButtonText: "{{ __('Okay') }}",
                    icon: "question"
                });
            } else {
                $.ajax({
                    type: "POST",
                    url: "{{ url('changeClient') }}",
                    data: {
                        id_client: id_client,
                    },
                    dataType: 'json',
                    success: function(res) {
                        console.log('res2');
                        if (res != null) {
                            console.log('res');
                            console.log(res);
                            mostrarBill();
                        }
                    },
                    error: function(error) {
                        if (error) {
                            console.log(error.responseJSON.errors);
                            console.log(error);
                        }
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "{{ url('changeClientVerify') }}",
                    data: {
                        id_client: id_client,
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res == 0) {
                            ignoreChangeEvent = true; // Activa la bandera ANTES de limpiar el select
                            $('#single-select-field').val('').trigger(
                                'change'); // Limpia la selección y DISPARA el evento change
                            ignoreChangeEvent =
                                false; // Desactiva la bandera despues de limpiar el select
                            Swal.fire("Cliente esta desactivado para la compra!");
                        }
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "{{ url('changeNoteCredit') }}",
                    data: {
                        id_client: id_client,
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res == 'credit') {
                            Swal.fire("Cliente tiene Nota de Credito a su favor!");
                        }
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "{{ url('changeClientCredit') }}",
                    data: {
                        id_client: id_client,
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.res == 'credit') {
                            Swal.fire("Cliente tiene credito vencido un total de $: " + res.billSUM);
                        }
                    }
                });
            }
        });

        function deleteBill() {
            $.ajax({
                type: "POST",
                url: "{{ url('deleteBill') }}",
                dataType: 'json',
                success: function(res) {
                    console.log(res);
                    mostrarBill();
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }

        function updateDiscount(id) {
            var discount = $('#discount' + id).val();
            $.ajax({
                type: "POST",
                url: "{{ url('updateDiscount') }}",
                data: {
                    id: id,
                    discount: discount,
                },
                dataType: 'json',
                success: function(res) {
                    console.log(res);
                    mostrarBill();
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }

        function verificaDiscount(id) {
            console.log('4');
            var discount = $('#discount' + id).val();
            if (discount < 0) {
                discount = 0;
                $('#discount' + id).val(0);
            }
            if (discount > 100) {
                discount = 100;
                $('#discount' + id).val(100);
            }
            $.ajax({
                type: "POST",
                url: "{{ url('verificaDiscount') }}",
                data: {
                    id: id,
                    discount: discount,
                },
                dataType: 'json',
                success: function(res) {
                    if (res.res == 'bien') {
                        $('#discount' + res.id).val(res.discount);
                        updateDiscount(res.id);
                    } else {
                        console.log('5');
                        Swal.fire({
                            title: 'Autorizar',
                            html: '<input type="password" id="passwordInput" class="form-control password-input">', // Agregamos una clase para seleccionar el input fácilmente
                            showCancelButton: true,
                            confirmButtonText: 'Autorizar',
                            showLoaderOnConfirm: true,
                            didOpen: () => { // Usamos didOpen para acceder al input después de que se muestra el modal
                                const passwordInput = document.getElementById('passwordInput');
                                passwordInput
                                    .focus(); // Opcional: enfocar el input al abrir el modal
                                passwordInput.addEventListener('keyup', (event) => {
                                    if (event.key === 'Enter') {
                                        Swal
                                            .clickConfirm(); // Simula un clic en el botón de confirmar
                                    }
                                });
                            },
                            preConfirm: () => {
                                const enterPassword = document.getElementById('passwordInput')
                                    .value;
                                if (enterPassword) { // Verifica que no esté vacío
                                    return authorizeDiscount(enterPassword, res.id, discount);
                                } else {
                                    Swal.showValidationMessage(
                                        'Por favor, ingresa la contraseña.'
                                    ); // Muestra un mensaje de validación si está vacío
                                    return false; // Evita que se cierre el modal
                                }
                            },
                            willClose: (dismissReason) => {
                                // Code to execute when the modal is about to close (optional)
                                if (dismissReason === Swal.DismissReason.cancel) {
                                    console.log('User canceled the authorization.');
                                    // Execute code specific to cancellation (optional)
                                    mostrarBill();
                                } else if (dismissReason === Swal.DismissReason.backdrop || Swal
                                    .DismissReason.esc) {
                                    console.log('User closed the modal (backdrop or ESC).');
                                    // Execute code specific to closing without confirmation (optional)
                                    mostrarBill();
                                }
                            }
                        });
                    }
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }

        function storeBudget() {
            $.ajax({
                type: "POST",
                url: "{{ url('storeBudget') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id_currency: $('#currencySelect').val(),
                },
                dataType: 'json',
                success: function(id) {
                    $('.button').html('');
                    $('.btnAll').html('');
                    $('.dt').html('');
                    mostrarBill();
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Log saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    const pdfLink = "{{ route('pdf', ':id') }}".replace(':id', id);
                    // Intentar imprimir (QZ Tray) o fallback (abrir + print)
                    printBill(pdfLink);
                    ignoreChangeEvent = true; // Activa la bandera ANTES de limpiar el select
                    $('#currencySelect').val(window.currencies.find(c => c.is_official == 1).id).trigger(
                        'change');
                    $('#single-select-field').val('').trigger(
                        'change'); // Limpia la selección y DISPARA el evento change
                    ignoreChangeEvent = false; // Desactiva la bandera despues de limpiar el select
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }

        function storeCredit() {
            var creditDays = $('#creditDays').val();
            console.log(creditDays);
            $.ajax({
                type: "POST",
                url: "{{ url('storeCredit') }}",
                data: {
                    id_currency: $('#currencySelect').val(),
                    creditDays: creditDays,
                },
                dataType: 'json',
                success: function(id) {
                    $('.button').html('');
                    $('.btnAll').html('');
                    $('.dt').html('');
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Log saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    // Cerrar el modal de "credit days" al guardar
                    $('#creditDays-modal').modal('hide');

                    const pdfLink = "{{ route('pdf', ':id') }}".replace(':id', id);
                    printBill(pdfLink);
                    ignoreChangeEvent = true; // Activa la bandera ANTES de limpiar el select
                    $('#currencySelect').val(window.currencies.find(c => c.is_official == 1).id).trigger(
                        'change');
                    $('#single-select-field').val('').trigger(
                        'change'); // Limpia la selección y DISPARA el evento change
                    ignoreChangeEvent = false; // Desactiva la bandera despues de limpiar el select
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }

        function verifyStock(product, price_type) {
            var id_client = $('#single-select-field').val();
            if (id_client == '' || id_client == null) {
                Swal.fire({
                    title: "{{ __('Select client') }}",
                    text: "{{ __('Empty client') }}",
                    confirmButtonText: "{{ __('Okay') }}",
                    icon: "question"
                });
            } else {
                // Para agilizar, llamamos directamente a addBill. El controlador valida stock.
                addBill(product, price_type);
                document.getElementById("hiddeProduct").style.display = "none";
                document.getElementById("hiddeBill").style.display = "block";
            }
        }

        function addBill(product, price_type) {
            var id_client = $('#single-select-field').val();
            var data = {
                id_client: id_client,
                price_type: price_type
            };

            // Si es numérico, lo tomamos como id_product, si no, como code
            if ($.isNumeric(product)) {
                data.id_product = product;
            } else {
                data.code = product;
            }

            $.ajax({
                type: "POST",
                url: "{{ url('addBill') }}",
                data: data,
                dataType: 'json',
                success: function(res) {
                    // Si el controlador devolvió el detalle, hacemos upsert local para evitar otra llamada
                    if (res && res.res === 'bien' && res.bill_detail) {
                        var elemento = res.bill_detail;
                        // Si la tabla está vacía (mensaje "Aún no se han añadido elementos."), recargamos completamente
                        var tbody = $('.produ');
                        var emptyMsg = tbody.find('td:contains("Aún no se han añadido elementos.")');
                        if (tbody.children().length === 0 || emptyMsg.length > 0) {
                            // Para mantener consistencia en botones y estructura, recargar con mostrarBill en el primer elemento
                            mostrarBill();
                            return;
                        }

                        // Cálculos iguales a los de mostrarBill
                        var priceColumnU = elemento.price * tasaCambio;
                        const priceColumn = priceColumnU * elemento.quantity;
                        const priceDiscount = priceColumn * (elemento.discount_percent / 100);
                        const price = priceColumn - priceDiscount;

                        // Si ya existe la fila, actualizamos
                        var existing = $('#quantity' + elemento.id);
                        if (existing.length) {
                            var $tr = existing.closest('tr');
                            existing.val(elemento.quantity);
                            $tr.find('td').eq(2).text(priceColumnU.toFixed(2));
                            $tr.find('td').eq(5).text(priceDiscount.toFixed(2));
                            $tr.find('td').eq(6).text(price.toFixed(2));
                        } else {
                            // Construir fila y anexarla
                            let partes = elemento.name ? elemento.name.match(/.{1,65}/g) : null;
                            var name = partes ? partes.join("<br>") : (elemento.name || '');
                            var row = '<tr>' +
                                '<td style="text-align: center;">' + (elemento.code || '') + '</td>' +
                                '<td>' + name + '</td>' +
                                '<td style="text-align: right; padding-right: 30px !important;">' + priceColumnU.toFixed(2) + '</td>' +
                                '<td style="text-align: end; vertical-align: middle;">' +
                                '<div class="d-flex justify-content-end align-items-end" style="min-width:120px;">' +
                                '<input id="quantity' + elemento.id + '" type="text" value ="' + elemento.quantity + '" min="1" step="1" data-id="' + elemento.id + '" data-id_product="' + elemento.id_product + '" data-price_type="' + elemento.price_type + '" class="form-control form-control-sm text-center quantity-input quantity" ' +
                                'style="width: 70px; border-radius: 0.5rem; font-size: 1rem; font-weight: 500; margin:0 8px;" ' +
                                'onkeypress="return validaMonto(event)">' +
                                '</div>' +
                                '</td>' +
                                '<td style="text-align: end; vertical-align: middle;">' +
                                '<div class="d-flex justify-content-end align-items-end" style="min-width:120px;">' +
                                '<input id="discount' + elemento.id + '" type="text" value ="' + elemento.discount_percent + '" min="1" step="1" data-id="' + elemento.id + '" class="form-control form-control-sm text-center discount-input discount" ' +
                                'style="width: 70px; border-radius: 0.5rem; font-size: 1rem; font-weight: 500; margin:0 8px;" ' +
                                'onkeypress="return validaMonto(event)">' +
                                '</div>' +
                                '</td>' +
                                '<td style="text-align: right; padding-right: 30px !important;">' + priceDiscount.toFixed(2) + '</td>' +
                                '<td style="text-align: right; padding-right: 30px !important;">' + price.toFixed(2) + '</td>' +
                                '<td style="text-align: center;">' +
                                '<a style="padding: 4px; margin-bottom: -4px !important; font-size: 11px !important;" onClick="deleteBillDetail(' + elemento.id + ')" data-toggle="tooltip" class="delete btn btn-danger">' +
                                '<i class="fa-solid fa-trash-can"></i>' +
                                '</a>' +
                                '</td>' +
                                '</tr>';
                            $('.produ').append(row);
                        }
                        // Actualizar totales en cliente
                        summaryBill();
                        $('#cod').focus();
                    } else if (res && res.res === 'mal') {
                        Swal.fire({
                            title: "{{ __('Producto sin stock') }}",
                            text: "{{ __('producto sin stock') }}",
                            confirmButtonText: "{{ __('Okay') }}",
                            showConfirmButton: false,
                            icon: "question"
                        });
                    } else {
                        // Fallback: si la respuesta no incluye el detalle, recargar la vista completa
                        mostrarBill();
                    }
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#nameError').html(error.responseJSON.errors.name);
                    }
                }
            });
        }
        let enterKeyPressed = false; // Control para evitar ejecuciones múltiples rápidas
        // --- Listener global para keydown ---
        window.addEventListener('keydown', function(event) {
            if (event.keyCode === 13 && !enterKeyPressed) {
                const focusedElement = document.activeElement;
                // Si ya hay un modal de SweetAlert2 abierto, no hagas nada con este listener global
                if (document.body.classList.contains('swal2-shown')) {
                    return;
                }
                enterKeyPressed = true;
                if (focusedElement.id === 'cod') {
                    handleCodInput(focusedElement);
                    event.preventDefault();
                } else if (focusedElement.classList.contains('discount')) {
                    handleDiscountInput(focusedElement);
                    event.preventDefault(); // Evita el comportamiento por defecto (ej. envío de formulario)
                } else if (focusedElement.classList.contains('quantity')) {
                    focusedElement.dataset.triggeredByEnter = 'true';
                    handleQuantityInput(focusedElement);
                    event.preventDefault(); // Detiene el comportamiento predeterminado del Enter
                    focusedElement
                        .blur(); // Simula el desenfoque para disparar el blur después de que la lógica de Enter se haya ejecutado
                } else if (focusedElement.id === 'document') {
                    const btnDocument = document.getElementById('btnDocument');
                    btnDocument.click();
                    event.preventDefault();
                }
                // Resetea la bandera después de un breve tiempo
                setTimeout(() => {
                    enterKeyPressed = false;
                }, 200);
            }
        });

        function handleDiscountInput(input) {
            // Aquí puedes asegurar que el foco se mueva al siguiente campo si es necesario,
            // o simplemente que la lógica de verificación se dispare.
            const id = input.dataset.id;
            verificaDiscount(id);
            // IMPORTANTE: Después de que Enter dispara verificaDiscount,
            // queremos evitar que el 'blur' posterior también lo haga.
            // Una opción es añadir una clase temporal o una bandera en el elemento.
            input.dataset.triggeredByEnter = 'true'; // Establece una bandera en el propio elemento
            input.blur(); // Simula el desenfoque para que el usuario pueda seguir
        }
        $(document).on('blur', '.discount', function() {
            // Si la función ya fue disparada por 'Enter', resetea la bandera y sal
            if (this.dataset.triggeredByEnter === 'true') {
                delete this.dataset.triggeredByEnter; // Limpia la bandera
                return; // Evita que verificaDiscount se llame de nuevo
            }
            // Si no fue disparado por Enter, ejecuta verificaDiscount
            const id = this.dataset.id;
            verificaDiscount(id);
        });

        function handleCodInput(input) {
            var id_client = $('#single-select-field').val();
            if (id_client == '' || id_client == null) {
                Swal.fire({
                    title: "{{ __('Select client') }}",
                    text: "{{ __('Empty client') }}",
                    confirmButtonText: "{{ __('Okay') }}",
                    showConfirmButton: false,
                    icon: "question"
                });
            } else {
                var code = input.value;
                // Detectar códigos especiales negativos para activar botones
                if (code && code.startsWith('-')) {
                    var n = parseInt(code, 10);
                    // Limpiar el campo inmediatamente
                    $('#cod').val('');
                    switch (n) {
                        case -1:
                            // Procesar Budget
                            authorizeAndStoreBudget();
                            break;
                        case -2:
                            // Procesar Pago
                            facturar();
                            break;
                        case -3:
                            // Factura a Crédito
                            authorizeAndStoreCredit();
                            break;
                        case -4:
                            // Bill Wait
                            storeBillWait();
                            break;
                        default:
                            // Si no es uno de los negativos esperados, ignorar y seguir con el flujo normal
                            break;
                    }
                    return; // no continuar con búsqueda de producto
                }

                if (code.startsWith('*')) {
                    var actualSearchTerm = code.substring(1);
                    // Marcar que estamos en modo búsqueda por teclado para activar navegación
                    window.billingSearchActive = true;
                    billProduct();
                    productDataTable.search(actualSearchTerm).draw();
                    // Limpiamos el campo pero mantenemos la bandera activa para permitir navegación
                    $('#cod').val('');
                } else {
                    openSelectPriceModal('code', code);
                    $('#cod').val('');
                }
            }
        }
        $(document).on('blur', '.quantity', function() {
            // Si la función ya fue disparada por 'Enter', resetea la bandera y sal
            if (this.dataset.triggeredByEnter === 'true') {
                delete this.dataset.triggeredByEnter; // Limpia la bandera
                return; // Evita que handleQuantityInput se llame de nuevo
            }
            // Si no fue disparado por Enter, ejecuta handleQuantityInput
            handleQuantityInput(this);
        });

        function handleQuantityInput(input) {
            var quantity = input.value;
            var id_product = input.dataset.id_product;
            var id = input.dataset.id;
            var price_type = input.dataset.price_type;
            if (quantity < 0.01) {
                quantity = 0.01;
                $('#quantity' + id).val(0.01);
            }
            $.ajax({
                type: "POST",
                url: "{{ url('verifyStockQuantity') }}",
                data: {
                    id: id,
                    id_product: id_product,
                    quantity: quantity,
                    price_type: price_type // <-- Nuevo
                },
                dataType: 'json',
                success: function(res) {
                    console.log(res);
                    if (res.res == 'bien') {
                        updateQuantity(res.id, res.quantity);
                    } else {
                        updateQuantity(res.id, res.quantityMax);
                        Swal.fire({
                            title: "{{ __('No cuentas con esa cantidad en Inventario') }}",
                            text: "{{ __('producto sin stock') }}",
                            confirmButtonText: "{{ __('Okay') }}",
                            showConfirmButton: false,
                            icon: "question"
                        });
                    }
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }

        function updateQuantity(id, quantity) {
            $.ajax({
                type: "POST",
                url: "{{ url('updateQuantity') }}",
                data: {
                    id: id,
                    quantity: quantity,
                },
                dataType: 'json',
                success: function(res) {
                    mostrarBill();
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }

        function authorizeDiscount(password, id, discount) {
            console.log(discount);
            $.ajax({
                type: "POST",
                url: "{{ url('authorizeDiscount') }}",
                data: {
                    password: password
                },
                dataType: 'json',
                success: function(res) {
                    console.log(res);
                    if (res.res == 'bien') {
                        $('#discount' + id).val(discount);
                        updateDiscount(id);
                    } else {
                        $('#discount' + id).val(res.discount);
                        updateDiscount(id);
                        Swal.fire({
                            title: "{{ __('No Autrorizado') }}",
                            text: "{{ __('Codigo Incorrecto') }}",
                            confirmButtonText: "{{ __('Okay') }}",
                            showConfirmButton: false,
                            icon: "question"
                        });
                    }
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }

        function modalDayCredit() {
            $("#creditDays").val('');
            $("#creditDays-modal").modal('show');
        }

        function storeBillWait() {
            $.ajax({
                type: "POST",
                url: "{{ url('storeBillWait') }}",
                dataType: 'json',
                success: function(id) {
                    $('#paymentForm').trigger("reset");
                    $('#dtmodal').html('');
                    $('#dtmodalvuelto').html('');
                    $('.button').html('');
                    $('.btnAll').html('');
                    $('.dt').html('');
                    $('.total').html('');
                    mostrarBill();
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Log saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    ignoreChangeEvent = true; // Activa la bandera ANTES de limpiar el select
                    $('#single-select-field').val('').trigger(
                        'change'); // Limpia la selección y DISPARA el evento change
                    ignoreChangeEvent = false; // Desactiva la bandera despues de limpiar el select
                },
                error: function(error) {
                    if (error) {
                        console.log(error);
                        console.log(error.responseJSON.errors);
                    }
                }
            });
        }

        document.addEventListener('keydown', function(event) {
            if (event.keyCode === 121 && !event.ctrlKey && !event.altKey && !event.shiftKey) {
                // Prevenir el comportamiento por defecto de la tecla F10 (ej. abrir menú de depuración en algunos navegadores)
                event.preventDefault();
                window.location.href = "{{ route('indexStore') }}";
            }
        });

        function hiddeProduct() {
            document.getElementById("hiddeProduct").style.display = "none";
            document.getElementById("hiddeBill").style.display = "block";
            if ($.fn.DataTable.isDataTable('#ajax-crud-datatableBill')) {
                $('#ajax-crud-datatableBill').DataTable().destroy();
                $('#ajax-crud-datatableBill').empty();
                billProductTableInitialized = false;
            }
        }

        function hiddeBillWite() {
            document.getElementById("hiddeBillWait").style.display = "none";
            document.getElementById("hiddeBill").style.display = "block";
            if ($.fn.DataTable.isDataTable('#ajax-crud-datatableBillWait')) {
                $('#ajax-crud-datatableBillWait').DataTable().destroy();
                $('#ajax-crud-datatableBillWait').empty();
                billWaitTableInitialized = false;
            }
        }

        function billWait() {
            document.getElementById("hiddeBillWait").style.display = "block";
            document.getElementById("hiddeBill").style.display = "none";
            if (!billWaitTableInitialized) {
                $('#ajax-crud-datatableBillWait').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('ajax-crud-datatableBillWait') }}",
                    columns: [{
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'sellerName',
                            render: function(data, type, row) {
                                return `${row.sellerName} ${row.sellerLast_name ? row.sellerLast_name : ''}`;
                            }
                        },
                        {
                            data: 'clientName',
                            render: function(data, type, row) {
                                return `${row.clientName} ${row.clientLast_name ? row.clientLast_name : ''}`;
                            }
                        },
                        {
                            data: 'ci',
                            render: function(data, type, row) {
                                return `${row.nationality}-${row.ci}`;
                            }
                        },
                        {
                            data: 'total',
                            name: 'total',
                            render: function(data, type, row) {
                                return (parseFloat(data) * tasaCambioWait).toFixed(2);
                            },
                            orderable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false
                        },
                    ],
                    drawCallback: function(settings) {
                        centerTableContentWait();
                    },
                    order: [
                        [1, 'desc']
                    ],
                    lengthMenu: [ // Define las opciones del menú de "Mostrar"
                        [20, 30, 40, 50, -1], // Valores reales
                        ['20', '30', '40', '50', 'Todos'] // Texto a mostrar
                    ],
                    "oLanguage": {
                        "sProcessing": "{{ __('Processing') }}...",
                        "sLengthMenu": "{{ __('Show') }} <select>" +
                            '<option value="20" selected>20</option>' +
                            '<option value="20">20</option>' +
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
                billWaitTableInitialized = true;
            } else {
                $('#ajax-crud-datatableBillWait').DataTable().ajax.reload();
            }
        }

        function centerTableContentWait() {
            $('#ajax-crud-datatableBillWait tbody tr td:nth-child(1)').addClass('text-center');
            $('#ajax-crud-datatableBillWait tbody tr td:nth-child(4)').addClass('text-center');
            $('#ajax-crud-datatableBillWait tbody tr td:nth-child(5)').addClass('text-end').css('padding-right', '50px');
            $('#ajax-crud-datatableBillWait tbody tr td:nth-child(6)').addClass('text-end').css('padding-right', '50px');
        }

        // --- Billing keyboard helpers (single shared scope) ---
        let billingRowIndex = -1;

        function getBillingRows() {
            // Preferir los nodos devueltos por DataTables cuando la instancia existe.
            try {
                if (typeof productDataTable !== 'undefined' && productDataTable && productDataTable.rows) {
                    // usar filas actualmente visibles en la página actual y aplicando el filtro de búsqueda
                    const nodes = productDataTable.rows({ search: 'applied', page: 'current' }).nodes();
                    return $(nodes).filter(function() { return $(this).is(':visible'); });
                }
            } catch (e) {
                // caerá al selector genérico si algo falla
            }
            return $('#ajax-crud-datatableBilling tbody tr:visible');
        }

        function clearBillingSelection() {
            getBillingRows().removeClass('keyboard-selected');
            billingRowIndex = -1;
        }

        function highlightBillingRow(index) {
            const rows = getBillingRows();
            if (!rows || rows.length === 0) return;
            if (index < 0) index = 0;
            if (index >= rows.length) index = rows.length - 1;
            billingRowIndex = index;
            rows.removeClass('keyboard-selected');
            const row = rows.eq(billingRowIndex);
            row.addClass('keyboard-selected');
            // Ensure visibility inside the scroll container
            const container = $('#ajax-crud-datatableBilling').parent();
            if (container && row.length) {
                const rowTop = row.position().top;
                const rowBottom = rowTop + row.outerHeight();
                const containerHeight = container.height();
                if (rowTop < 0 || rowBottom > containerHeight) {
                    const newScroll = container.scrollTop() + (rowTop - 20);
                    container.animate({ scrollTop: newScroll }, 120);
                }
            }
        }

        // Initialize keyboard handlers once
        function setupBillingKeyboard() {
            if (window._billingKeyboardInitialized) return;
            // Flechas y Enter (namespaced). Usamos document-level para no depender del foco exacto en #cod
            $(document).on('keydown.billingNav', function(e) {
                const active = document.activeElement;
                const codVal = (active && active.id === 'cod') ? $(active).val() : $('#cod').val() || '';
                const navActive = (codVal && codVal.indexOf('*') !== -1) || (window.billingSearchActive === true);
                if (!navActive) {
                    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') clearBillingSelection();
                    return;
                }
                const rows = getBillingRows();
                if (!rows || rows.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (billingRowIndex < rows.length - 1) highlightBillingRow(billingRowIndex + 1);
                    else highlightBillingRow(0);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (billingRowIndex > 0) highlightBillingRow(billingRowIndex - 1);
                    else highlightBillingRow(rows.length - 1);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (billingRowIndex >= 0) {
                        const row = rows.eq(billingRowIndex);
                        let rowData = null;
                        try {
                            if (typeof productDataTable !== 'undefined' && productDataTable && productDataTable.row) {
                                rowData = productDataTable.row(row).data();
                            }
                        } catch (err) { rowData = null; }
                        if (rowData && rowData.id) {
                            openSelectPriceModal('id', rowData.id);
                        } else {
                            row.trigger('dblclick');
                        }
                        clearBillingSelection();
                        // Limpia el campo #cod si el foco está en él
                        try { if (document.activeElement && document.activeElement.id === 'cod') { $(document.activeElement).val(''); } else { $('#cod').val(''); } } catch(e){}
                        window.billingSearchActive = false;
                    }
                }
            });

            // click en fila (namespaced)
            $(document).on('click.billingNav', '#ajax-crud-datatableBilling tbody tr', function() {
                $('#ajax-crud-datatableBilling tbody tr').removeClass('keyboard-selected');
                $(this).addClass('keyboard-selected');
                billingRowIndex = getBillingRows().index($(this));
            });

            window._billingKeyboardInitialized = true;
        }

        function billProduct() {
            document.getElementById("hiddeProduct").style.display = "block";
            document.getElementById("hiddeBill").style.display = "none";
            if (!billProductTableInitialized) {
                productDataTable = $('#ajax-crud-datatableBilling').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ url('ajax-crud-datatableBilling') }}",
                    },
                    columns: [{
                            data: 'code',
                            name: 'code'
                        },
                        {
                            data: 'name',
                            render: function(data, type, row) {
                                let partes = data.match(/.{1,65}/g)
                                return partes.join("<br>");
                            }
                        },
                        {
                            data: 'stock',
                            name: 'stock_orderable', // Usaremos este nombre en el backend para la ordenación
                            orderable: true, // <--- CAMBIO IMPORTANTE: Permitir ordenación en el frontend
                            searchable: false,
                            className: 'text-center' // Añadido className para centrar
                        },
                        {
                            data: null,
                            name: 'price',
                            orderable: false,
                            searchable: false,
                            className: 'text-end',
                            render: function(data, type, row) {
                                return (parseFloat(row.price) * tasaCambioProduct).toFixed(2);
                            }
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false
                        },
                    ],
                    "columnDefs": [{
                        "targets": [3], // Índice de la columna (empezando desde 0)
                        "searchable": false
                    }],
                    drawCallback: function(settings) {
                        centerTableContent()
                    },
                    order: [
                        [0, 'desc']
                    ],
                    "lengthMenu": [
                        [50, 100, 500],
                        [50, 100, 500]
                    ],
                    "oLanguage": {
                        "sProcessing": "{{ __('Processing') }}...",
                        "sLengthMenu": "{{ __('Show') }} <select>" +
                            '<option value="50">50</option>' +
                            '<option value="100">100</option>' +
                            '<option value="500">500</option>' +
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
                    },
                    fnRowCallback: function(nRow, aData, rowIndex, settings) {
                        $(nRow).dblclick(function() {
                            // Get the product ID from the data object (assuming 'id' is the key)
                            // Call the addBill function with the product ID
                            const productId = aData.id;
                            openSelectPriceModal('id', productId);
                        });
                        return nRow;
                    }
                });

                // Usar la configuración compartida de teclado
                setupBillingKeyboard();

                // Cuando la tabla termine de dibujarse, si estamos en búsqueda activa seleccionar la primera fila
                productDataTable.on('draw', function() {
                    if (window.billingSearchActive === true) {
                        const rows = getBillingRows();
                        if (rows && rows.length > 0) {
                            highlightBillingRow(0);
                            try { $('#cod').focus(); } catch(e){}
                        }
                    }
                });

                billProductTableInitialized = true;
            } else {
                // Recargar usando la instancia ya creada
                if (typeof productDataTable !== 'undefined' && productDataTable) {
                    // en la próxima draw, seleccionar primera si está activa la búsqueda
                    productDataTable.one('draw', function() {
                        if (window.billingSearchActive === true) {
                            const rows = getBillingRows();
                            if (rows && rows.length > 0) {
                                highlightBillingRow(0);
                                try { $('#cod').focus(); } catch(e){}
                            }
                        }
                    });
                    productDataTable.ajax.reload();
                } else {
                    $('#ajax-crud-datatableBill').DataTable().ajax.reload();
                }
            }
        }

        function centerTableContent() {
            $('#ajax-crud-datatableBilling tbody tr td:nth-child(1)').addClass('text-center');
            $('#ajax-crud-datatableBilling tbody tr td:nth-child(3)').addClass('text-end').css('padding-right',
                '40px');
            $('#ajax-crud-datatableBilling tbody tr td:nth-child(4)').addClass('text-center');
        }
        mostrarBill();
        let pagos = [];
        let totalOrden = 0;
        // Abrir modal de facturación
        function facturar() {
            var id_client = $('#single-select-field').val();
            $.ajax({
                type: "POST",
                url: "{{ url('facturar') }}",
                data: {
                    id_client: id_client
                },
                dataType: 'json',
                success: function(res) {
                    pagos = [];
                    $('#tablaPagos tbody').empty();
                    var totalProduct = parseFloat(res.amountProduct) ?? 0;
                    var totalService = parseFloat(res.amountService) ?? 0;
                    totalOrden = totalProduct + totalService;
                    $('#totalRestante').text(totalOrden.toFixed(2) + ' ' + currencyPrincipal.abbreviation);
                    $('#btnProcesarPagos').prop('disabled', true);
                    $('#btnAgregarPago').prop('disabled', false); // <-- Habilita el botón Agregar
                    $('#vueltoInfo').html('');
                    $('#pago_metodo').val('');
                    $('#pago_monto').val('');
                    $('#pago_referencia').val('').addClass('d-none');
                    $('#facturarModal').modal('show');
                    $('#dtNoteCredit').html('');
                    if (res.res == 'credit') {
                        $('.dtNoteCredit').html(
                            '<table  id="noteCredit" class="table table-striped">' +
                            '<thead>' +
                            '<tr style="width:100%; text-align: center; font-size: 12px !important;" >' +
                            '<th colspan="4" style=" text-align: center;">{{ __('Notas de creditos') }}</th>' +
                            '</tr>' +
                            '<tr style="width:100%; text-align: center; font-size: 12px !important;" >' +
                            '<th style=" text-align: center;">{{ __('Code') }}</th>' +
                            '<th style=" text-align: center;">{{ __('Amount') }}</th>' +
                            '<th style=" text-align: center;" >{{ __('Action') }}</th>' +
                            '</tr>' +
                            '</thead>' +
                            '<tbody class="noteCredit" style="font-size: 12px !important;">' +
                            '</tbody>' +
                            '</table>'
                        );
                        $.each(res.repayments, function(index, elemento) {
                            $('.noteCredit').append(
                                '<tr>' +
                                '<td style=" text-align: center;">' + elemento.code + '</td>' +
                                '<td style=" text-align: center;">' + elemento.amount + '</td>' +
                                '<td style=" text-align: center;" class="btnNoteCredit">' +
                                '<a style="padding: 5px; margin-bottom: -0.1px !important; margin-top: -4px !important; font-size: 12px !important;" onClick="agregarNotaCredito(\'' +
                                elemento.code + '\', ' + elemento.amount +
                                ')" data-toggle="tooltip" class="btn btn-primary ">' +
                                '<i class="fa-solid fa-share"></i>' +
                                '</a>' +
                                '</td>' +
                                '</tr>'
                            );
                        });
                    }
                    // El enfoque se gestiona con los handlers de Bootstrap (shown/hidden)
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }

        // Mantener el foco en #pago_metodo mientras el modal de facturación esté abierto
        (function() {
            var $modal = $('#facturarModal');
            $modal.on('shown.bs.modal', function() {
                // Un pequeño intento inmediato
                try {
                    $('#pago_metodo').focus();
                } catch (e) {}
                // Refuerzo: re-enfocar periódicamente si algo externo roba el foco
                var focusInterval = setInterval(function() {
                    if (!$modal.hasClass('show')) { // ya se cerró
                        clearInterval(focusInterval);
                        return;
                    }
                    var active = document.activeElement;
                    if (!$.contains($modal[0], active)) {
                        try {
                            $('#pago_metodo').focus();
                        } catch (e) {}
                    }
                }, 250);
                $modal.data('focusInterval', focusInterval);
            });
            $modal.on('hidden.bs.modal', function() {
                var interval = $modal.data('focusInterval');
                if (interval) {
                    clearInterval(interval);
                    $modal.removeData('focusInterval');
                }
            });
        })();

        function agregarNotaCredito(code, amount) {
            // Evita agregar la misma nota de crédito dos veces (sin alerta)
            if (pagos.find(p => p.metodoId === 'nota_credito_' + code)) {
                return;
            }
            let restante = calcularRestante();
            let montoUsado = parseFloat(amount);
            let vuelto = 0;

            // Si la nota de crédito es mayor al restante, solo usa lo necesario y el resto es vuelto
            if (montoUsado > restante) {
                vuelto = montoUsado - restante;
                montoUsado = restante;
            }

            pagos.push({
                metodoId: 'nota_credito_' + code,
                metodoText: 'Nota de Crédito',
                moneda: currencyPrincipal.abbreviation,
                idCurrency: currencyPrincipal.id,
                monto: montoUsado,
                referencia: code,
                montoPrincipal: montoUsado,
                rate: 1
            });
            renderPagos();

            // Elimina la fila de la nota de crédito de la tabla visual
            $(`.noteCredit tr`).filter(function() {
                return $(this).find('td').eq(0).text() == code;
            }).remove();

            // Resetea método de pago y monto
            $('#pago_metodo').val('');
            $('#pago_monto').val('');
            $('#pago_referencia').val('').addClass('d-none').removeAttr('required');

            // Muestra el vuelto si corresponde
            if (vuelto > 0) {
                $('#vueltoInfo').html(
                    `<span class="badge bg-warning text-dark ms-2">Vuelto: ${vuelto.toFixed(2)} ${currencyPrincipal.abbreviation}</span>`
                );
            } else {
                $('#vueltoInfo').html('');
            }

            // Actualiza el total restante y controla los botones
            $('#btnAgregarPago').prop('disabled', calcularRestante() <= 0.01);
            $('#btnProcesarPagos').prop('disabled', calcularRestante() > 0.01);
        }
        // Al cambiar el método de pago, sugerir el monto según el total restante
        $('#pago_metodo').on('change', function() {
            let ref = $('#pago_metodo option:selected').data('reference');
            if (ref == 1) {
                $('#pago_referencia').removeClass('d-none').attr('required', true);
            } else {
                $('#pago_referencia').addClass('d-none').val('').removeAttr('required');
            }
            let idCurrency = $('#pago_metodo option:selected').data('currency');
            let restante = calcularRestante();
            var rate = 1;
            currencies.forEach(c => {
                if (c.id == idCurrency) {
                    rate = c.rate;
                }
            });
            if (idCurrency && idCurrency != currencyPrincipal.id) {
                $('#pago_monto').val((restante * rate).toFixed(2));
            } else {
                $('#pago_monto').val(restante.toFixed(2));
            }
        });
        // Al cambiar el monto, mostrar vuelto si aplica
        $('#pago_monto').on('input', function() {
            mostrarVuelto();
        });
        // Al agregar pago, si el monto supera el restante, ajustar el monto principal y mostrar vuelto
        $('#btnAgregarPago').on('click', function() {
            let metodoId = $('#pago_metodo').val();
            let metodoText = $('#pago_metodo option:selected').text();
            let moneda = $('#pago_metodo option:selected').data('moneda');
            let idCurrency = $('#pago_metodo option:selected').data('currency');
            let monto = parseFloat($('#pago_monto').val());
            let referencia = $('#pago_referencia').val();
            let refRequired = $('#pago_metodo option:selected').data('reference');
            let restante = calcularRestante();
            let vuelto = 0;
            let vueltoTexto = '';
            if (!metodoId || !monto || monto <= 0 || (refRequired == 1 && !referencia)) {
                Swal.fire('Error', 'Complete todos los campos requeridos.', 'error');
                return;
            }
            let montoPrincipal = monto;
            if (idCurrency != currencyPrincipal.id) {
                let key = currencyPrincipal.id + '_' + idCurrency;
                var rate = 1;
                currencies.forEach(c => {
                    if (c.id == idCurrency) {
                        rate = c.rate;
                    }
                });
                let maxMonto = restante * rate;
                if (monto > maxMonto) {
                    vuelto = monto - maxMonto;
                    monto = maxMonto;
                    montoPrincipal = restante;
                    $('#pago_monto').val(maxMonto.toFixed(2));
                } else {
                    montoPrincipal = monto / rate;
                }
                if (vuelto > 0) {
                    vueltoTexto =
                        `<span class="badge bg-warning text-dark ms-2">Vuelto: ${vuelto.toFixed(2)} ${moneda}</span>`;
                }
            } else {
                if (monto > restante) {
                    vuelto = monto - restante;
                    monto = restante;
                    montoPrincipal = restante;
                    $('#pago_monto').val(restante.toFixed(2));
                }
                if (vuelto > 0) {
                    vueltoTexto =
                        `<span class="badge bg-warning text-dark ms-2">Vuelto: ${vuelto.toFixed(2)} ${currencyPrincipal.abbreviation}</span>`;
                }
            }
            var rate = 1;
            if (idCurrency != currencyPrincipal.id) {
                let key = currencyPrincipal.id + '_' + idCurrency;
                currencies.forEach(c => {
                    if (c.id == idCurrency) {
                        rate = c.rate;
                    }
                });
                // montoPrincipal = monto / rate; // ya lo haces
            }
            pagos.push({
                metodoId,
                metodoText,
                moneda,
                idCurrency,
                monto, // Monto en moneda seleccionada
                referencia,
                montoPrincipal, // Monto en principal (para el cálculo)
                rate // <--- agrega la tasa usada
            });
            renderPagos();
            // Limpiar campos
            $('#pago_monto').val('');
            $('#pago_referencia').val('').addClass('d-none').removeAttr('required');
            $('#pago_metodo').val('');
            $('#pago_monto').closest('.col-md-3').find('.alert').remove();
            $('#vueltoInfo').html(vueltoTexto);
        });
        // Renderizar tabla de pagos y controlar botones
        function renderPagos() {
            let tbody = '';
            let totalPagado = 0;
            pagos.forEach(function(p, idx) {
                totalPagado += parseFloat(p.montoPrincipal);
                tbody += `<tr>
                        <td>${p.metodoText}</td>
                        <td>${p.moneda}</td>
                        <td>${parseFloat(p.monto).toFixed(2)}</td>
                        <td>${p.referencia || ''}</td>
                        <td><button type="button" class="btn btn-danger btn-sm btn-remove-pago" data-idx="${idx}"><i class="fas fa-trash"></i></button></td>
                    </tr>`;
            });
            $('#tablaPagos tbody').html(tbody);
            let restante = totalOrden - totalPagado;
            if (restante < 0) restante = 0;
            $('#totalRestante').text(restante.toFixed(2) + ' ' + currencyPrincipal.abbreviation);
            $('#vueltoInfo').html('');
            // Deshabilita el botón de agregar si el restante es 0 o menor
            $('#btnAgregarPago').prop('disabled', restante <= 0.01);
            // Habilita el botón de procesar solo si el total está cubierto
            $('#btnProcesarPagos').prop('disabled', restante > 0.01);
            // Si el botón de procesar está habilitado, enfocarlo para que Enter lo dispare
            if (!$('#btnProcesarPagos').prop('disabled')) {
                try {
                    $('#btnProcesarPagos').focus();
                } catch (e) {
                    console.log('No se pudo enfocar #btnProcesarPagos', e);
                }
            } else {
                // Mantener foco en el select de método de pago si aún faltan pagos
                try {
                    $('#pago_metodo').focus();
                } catch (e) {}
            }
            // Mostrar vuelto si existe
            mostrarVuelto();
        }
        // Eliminar pago
        $(document).on('click', '.btn-remove-pago', function() {
            let idx = $(this).data('idx');
            pagos.splice(idx, 1);
            renderPagos();
        });
        // Procesar pagos
        function storeBill() {
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "{{ url('storeBill') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id_currency: $('#currencySelect').val(),
                    pagos: pagos
                },
                dataType: 'json',
                success: function(id) {
                    const modalEl = document.getElementById('facturarModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                    $('.button').html('');
                    $('.btnAll').html('');
                    $('.dt').html('');
                    $('.total').html('');
                    mostrarBill();
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Log saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    const pdfLink = "{{ route('pdf', ':id') }}".replace(':id', id);
                    printBill(pdfLink);
                    // Restablecer Select2
                    ignoreChangeEvent = true; // Activa la bandera ANTES de limpiar el select
                    $('#currencySelect').val(window.currencies.find(c => c.is_official == 1).id).trigger(
                        'change');
                    $('#single-select-field').val('').trigger(
                        'change'); // Limpia la selección y DISPARA el evento change
                    ignoreChangeEvent = false; // Desactiva la bandera despues de limpiar el select
                },
                error: function(error) {
                    if (error) {
                        console.log(error);
                        console.log(error.responseJSON.errors);
                    }
                }
            });
        }

        // Calcular el total restante en moneda principal
        function calcularRestante() {
            let totalPagado = 0;
            pagos.forEach(function(p) {
                totalPagado += parseFloat(p.montoPrincipal); // SIEMPRE EN PRINCIPAL
            });
            let restante = totalOrden - totalPagado;
            return restante > 0 ? restante : 0;
        }
        // Mostrar vuelto si el monto ingresado supera el restante
        function mostrarVuelto() {
            let idCurrency = $('#pago_metodo option:selected').data('currency');
            let restante = calcularRestante();
            let monto = parseFloat($('#pago_monto').val()) || 0;
            let vuelto = 0;
            let vueltoTexto = '';
            // No mostrar vuelto si no hay método, no hay monto o el restante es 0
            if (!$('#pago_metodo').val() || !$('#pago_monto').val() || restante <= 0) {
                $('#vueltoInfo').html('');
                return;
            }
            if (idCurrency && idCurrency != currencyPrincipal.id) {
                let key = currencyPrincipal.id + '_' + idCurrency;
                var rate = 1;
                currencies.forEach(c => {
                    if (c.id == idCurrency) {
                        rate = c.rate;
                    }
                });
                let maxMonto = (restante * rate);
                if (monto > maxMonto) {
                    vuelto = monto - maxMonto;
                    vueltoTexto =
                        `<span class="badge bg-warning text-dark ms-2">Vuelto: ${vuelto.toFixed(2)} ${$('#pago_metodo option:selected').data('moneda')}</span>`;
                }
            } else {
                if (monto > restante) {
                    vuelto = monto - restante;
                    vueltoTexto =
                        `<span class="badge bg-warning text-dark ms-2">Vuelto: ${vuelto.toFixed(2)} ${currencyPrincipal.abbreviation}</span>`;
                }
            }
            // Elimina cualquier alerta debajo del input de monto
            $('#pago_monto').closest('.col-md-3').find('.alert').remove();
            // Solo muestra el vuelto al lado del total restante
            $('#vueltoInfo').html(vueltoTexto);
        }
        let selectedProductId = null;

        function openSelectPriceModal(type, product) {
            console.log('Producto seleccionado:', product, 'Tipo:', type);
            selectedProductId = product;
            let data = {};
            if (type === 'id') {
                data.id = product;
            } else if (type === 'code') {
                data.code = product;
            }
            console.log('Data enviada:', data);
            $.ajax({
                type: "POST",
                url: "/getProductPrices",
                data: data,
                dataType: 'json',
                success: function(product) {
                    let prices = [];
                    if (product.prices.detal && parseFloat(product.prices.detal) > 0) prices.push({
                        type: 'detal',
                        label: '0 - Precio Detal'
                    });
                    if (product.prices.price && parseFloat(product.prices.price) > 0) prices.push({
                        type: 'price',
                        label: '1 - Precio 1'
                    });
                    if (product.prices.price2 && parseFloat(product.prices.price2) > 0) prices.push({
                        type: 'price2',
                        label: '2 - Precio 2'
                    });
                    if (product.prices.price3 && parseFloat(product.prices.price3) > 0) prices.push({
                        type: 'price3',
                        label: '3 - Precio 3'
                    });
                    if (prices.length === 1) {
                        verifyStock(product.id, prices[0].type);
                    } else if (prices.length > 1) {
                        let btns = '';
                        prices.forEach((p) => {
                            btns +=
                                `<button class="btn btn-outline-primary mb-2" data-price="${p.type}" style="width:100%">${p.label}</button>`;
                        });
                        $('#selectPriceModal .modal-body .d-grid').html(btns);
                        $('#selectPriceModal').modal('show');
                        $('#selectPriceModal .modal-body .d-grid button').off('click').on('click', function() {
                            let selectedPrice = $(this).data('price');
                            $('#selectPriceModal').modal('hide');
                            verifyStock(product.id, selectedPrice);
                        });
                        $(document).off('keydown.selectPrice').on('keydown.selectPrice', function(e) {
                            if (['0', '1', '2', '3'].includes(e.key)) {
                                let idx = parseInt(e.key);
                                if (idx >= 0 && idx < prices.length) {
                                    $('#selectPriceModal .modal-body .d-grid button').eq(idx).click();
                                }
                            }
                        });
                        $('#selectPriceModal').on('hidden.bs.modal', function() {
                            $(document).off('keydown.selectPrice');
                        });
                    } else {
                        Swal.fire('Sin precios disponibles', 'Este producto no tiene precios configurados.',
                            'warning');
                    }
                },
                error: function(xhr) {
                    let msg = 'Error desconocido';
                    if (xhr.status === 404) {
                        msg = 'Producto no encontrado o código incorrecto.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                }
            });
        }
        /* -------------------- Impresión simplificada: abrir PDF -------------------- */
        // Eliminada integración con QZ Tray. Ahora sólo abrimos el PDF en nueva pestaña
        async function printBill(pdfUrl) {
            try {
                // Descargamos el PDF (misma-origin) y lo servimos como blob para incrustarlo
                const resp = await fetch(pdfUrl, {
                    credentials: 'same-origin'
                });
                if (!resp.ok) {
                    // si falla la descarga, abrimos directamente la URL
                    window.open(pdfUrl, '_blank');
                    return;
                }
                const blob = await resp.blob();
                const blobUrl = URL.createObjectURL(blob);
                // Abrir ventana nueva (debe ser llamada desde un evento de usuario para evitar bloqueos)
                const printWindow = window.open('', '_blank');
                if (!printWindow) {
                    // popup bloqueado: abrir directamente la URL original
                    window.location.href = pdfUrl;
                    return;
                }
                const html = `<!doctype html><html><head><title>Imprimir</title>
                    <meta charset="utf-8" />
                    <style>html,body{height:100%;margin:0}iframe{border:0;width:100%;height:100%}</style>
                    </head><body>
                    <iframe id="pdfFrame" src="${blobUrl}"></iframe>
                    <script>
                        (function(){
                            var iframe = document.getElementById('pdfFrame');
                            var printed = false;
                            function doPrint(){
                                try{
                                    iframe.contentWindow.focus();
                                    iframe.contentWindow.print();
                                }catch(e){
                                    try{ window.print(); }catch(e2){}
                                }
                            }

                            // Al cargar el iframe, iniciamos la impresión inmediatamente
                            iframe.onload = function(){
                                try{ doPrint(); }catch(e){}

                                // Añadir listener: esperar que el usuario presione Enter (o NumpadEnter) para cerrar la ventana
                                function shouldCloseByKey(ev){
                                    try{
                                        var k = ev.key || '';
                                        var code = ev.code || '';
                                        var keyCode = ev.keyCode || ev.which || 0;
                                        if (k === 'Enter' || code === 'Enter' || code === 'NumpadEnter' || keyCode === 13) return true;
                                    }catch(e){}
                                    return false;
                                }

                                function onUserEnter(ev){
                                    try{
                                        if (shouldCloseByKey(ev)){
                                            removeAllKeyListeners();
                                            try{ window.close(); }catch(e){}
                                        }
                                    }catch(e){}
                                }

                                // Crear función para remover listeners
                                function removeAllKeyListeners(){
                                    try{ window.removeEventListener('keydown', onUserEnter); }catch(e){}
                                    try{ document.removeEventListener('keydown', onUserEnter); }catch(e){}
                                    try{ if (iframe && iframe.contentWindow) iframe.contentWindow.removeEventListener('keydown', onUserEnter); }catch(e){}
                                    try{ if (invisibleInput) { invisibleInput.removeEventListener('keydown', onUserEnter); invisibleInput.parentNode && invisibleInput.parentNode.removeChild(invisibleInput); } }catch(e){}
                                }

                                // Añadimos listeners en varios ámbitos
                                window.addEventListener('keydown', onUserEnter);
                                document.addEventListener('keydown', onUserEnter);
                                try{ if (iframe && iframe.contentWindow) iframe.contentWindow.addEventListener('keydown', onUserEnter); }catch(e){}

                                // Añadir un input oculto y enfocarlo para capturar la tecla Enter incluso si el visor PDF no reenvía eventos
                                var invisibleInput = document.createElement('input');
                                invisibleInput.type = 'text';
                                invisibleInput.style.position = 'fixed';
                                invisibleInput.style.left = '-9999px';
                                invisibleInput.style.top = '0px';
                                invisibleInput.setAttribute('aria-hidden', 'true');
                                invisibleInput.tabIndex = 0;
                                document.body.appendChild(invisibleInput);
                                invisibleInput.addEventListener('keydown', onUserEnter);
                                // Intentar enfocar el input después de breve tiempo
                                setTimeout(function(){ try{ invisibleInput.focus(); }catch(e){} }, 100);
                            };

                            // Marcar impresión completada cuando el navegador notifique onafterprint
                            if (typeof window.onafterprint !== 'undefined'){
                                window.onafterprint = function(){
                                    printed = true;
                                    try{ setTimeout(function(){ try{ window.close(); }catch(e){} }, 500); }catch(e){}
                                };
                            }

                            // Fallback: cerrar máximo tras 10s
                            setTimeout(function(){ if (!printed) try{ window.close(); }catch(e){} }, 5000);

                        })();
                    <\/script>
                    </body></html>`;

                printWindow.document.open();
                printWindow.document.write(html);
                printWindow.document.close();
            } catch (err) {
                console.error('Error al abrir PDF para impresión automática:', err);
                try {
                    window.open(pdfUrl, '_blank');
                } catch (e) {
                    window.location.href = pdfUrl;
                }
            }
        }


        // Wrapper functions that ask supervisor auth before proceeding
        function authorizeAndStoreBudget() {
            $('#supervisor_password').val('');
            $('#supervisorAuthError').text('');
            $('#supervisorAuthModal').data('action', 'budget').modal('show');
        }

        function authorizeAndStoreCredit() {
            $('#supervisor_password').val('');
            $('#supervisorAuthError').text('');
            $('#supervisorAuthModal').data('action', 'credit').modal('show');
        }

        $('#supervisorAuthSubmit').on('click', function() {
            var password = $('#supervisor_password').val();
            var action = $('#supervisorAuthModal').data('action');
            if (!password || password.length < 3) {
                $('#supervisorAuthError').text('Ingrese la clave de supervisor');
                return;
            }
            $.ajax({
                url: '{{ route('billing.validateSupervisor') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    password: password
                },
                success: function(res) {
                    if (res.ok) {
                        $('#supervisorAuthModal').modal('hide');
                        if (action === 'budget') {
                            storeBudget();
                        } else if (action === 'credit') {
                            modalDayCredit();
                        }
                    } else {
                        $('#supervisorAuthError').text('Clave inválida');
                    }
                },
                error: function(xhr) {
                    $('#supervisorAuthError').text('Clave inválida o error en el servidor');
                }
            });
        });
    </script>
@endsection
<style>
    /* Estilo para la fila seleccionada por teclado */
    #ajax-crud-datatableBilling tbody tr.keyboard-selected {
        background-color: #389bec !important;
    }

    /* Este CSS es crucial para la consistencia */
    #ajax-crud-datatableBilling {
        width: 99% !important;
        /* Asegura que la tabla ocupe el 100% del contenedor */
    }

    /* Opcional: Eliminar cualquier margen inferior por defecto de los labels si es el problema */
    .form-label2 {
        margin-bottom: 0.5rem !important;
        /* Este es el default de Bootstrap, si está más alto, ajústalo. O a 0 si quieres que estén pegados al input */
    }

    /* General Card Styling */
    .card {
        border-radius: 0.75rem;
        /* Consistent rounded corners */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        /* Soft, subtle shadow */
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    /* Card Header Styles (for main sections) */
    .card-header.bg-gradient-dark,
    .card-header.bg-gradient-primary,
    .card-header.bg-gradient-info {
        border-bottom: none;
        /* Remove default border */
        padding: 1rem 1.5rem;
        /* Consistent padding */
        border-radius: 0.75rem 0.75rem 0 0 !important;
        /* Rounded top corners only */
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h4,
    .card-header h5 {
        font-weight: 600;
    }

    /* Form Controls (Inputs & Selects) */
    .form-control,
    .form-select {
        border-radius: 0.5rem;
        /* Rounded input fields */
        border: 1px solid #dee2e6;
        /* Subtle border */
        padding: 0.75rem 1rem;
        /* Comfortable padding */
        font-size: 0.9rem;
        /* Consistent font size */
        transition: all 0.2s ease-in-out;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #cb0c9f;
        /* Highlight with primary color on focus */
        box-shadow: 0 0 0 0.2rem rgba(203, 12, 159, 0.25);
        /* Subtle glowing effect */
    }

    /* Labels for form controls */
    .form-label {
        font-weight: 500;
        color: #6c757d;
        /* Muted gray for labels */
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
    }

    /* Price Display Box */
    .p-3.bg-light {
        background-color: #f8f9fa !important;
        /* Light background for the price box */
        border: 1px solid #e9ecef;
        /* Soft border */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .p-3.bg-light p {
        font-size: 1rem;
        font-weight: 600;
    }

    .p-3.bg-light h3 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #35b3e5;
        /* Primary color for the dollar amount */
    }

    /* Table Styling */
    .table {
        margin-bottom: 0;
        /* Remove default margin from table */
    }

    .table thead th {
        font-size: 0.7rem;
        /* Smaller font for table headers */
        font-weight: 700;
        color: #8898aa !important;
        /* Muted color for headers */
        border-bottom: 2px solid #e9ecef;
        /* Stronger bottom border */
        padding: 0.75rem 1rem;
        text-transform: uppercase;
        /* Ensure all caps */
        letter-spacing: 0.05em;
    }

    .table tbody tr {
        transition: background-color 0.15s ease-in-out;
    }

    .table tbody tr:hover {
        background-color: #f0f2f5;
        /* Light hover effect for rows */
    }

    .table tbody td {
        padding: 0.5rem 0.7rem;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
        /* Subtle top border for rows */
        font-size: 0.875rem;
    }

    /* Specific styling for totals tables */
    .totals-table th,
    .totals-table td {
        border-top: none;
        /* Remove borders within totals table */
        padding: 0.5rem 0;
        /* Compact padding */
    }

    /* Buttons */
    .btn {
        border-radius: 0.5rem;
        /* Consistent rounded buttons */
        padding: 0.75rem 1.5rem;
        /* Generous padding */
        font-weight: 600;
        transition: all 0.2s ease-in-out;
    }

    .btn-outline-light {
        border-color: rgba(255, 255, 255, 0.5);
        color: #fff;
    }

    .btn-outline-light:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-color: #fff;
    }

    .btn-outline-secondary {
        border-color: #6c757d;
        color: #6c757d;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: #fff;
    }

    /* Chosen/Select2 specific styles */
    .chosen-container-single .chosen-single {
        border-radius: 0.5rem !important;
        height: calc(2.8rem + 2px) !important;
        /* Adjust height to match form-control-lg */
        line-height: calc(2.8rem + 2px) !important;
        border: 1px solid #dee2e6;
        background: #fff;
        box-shadow: none;
    }

    .chosen-container-single .chosen-single span {
        margin-right: 26px;
        /* Adjust if needed */
    }

    .chosen-container-single .chosen-single div b {
        background-position: 0 5px !important;
        /* Adjust arrow position */
    }

    .chosen-container-active.chosen-with-drop .chosen-single {
        border-color: #cb0c9f !important;
        box-shadow: 0 0 0 0.2rem rgba(203, 12, 159, 0.25) !important;
    }

    .chosen-container-single .chosen-drop {
        border-radius: 0.5rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .chosen-results li.highlighted {
        background-color: #cb0c9f !important;
        background-image: none !important;
        color: #fff !important;
    }

    /* Ensure Font Awesome icons for buttons are aligned */
    .btn i {
        vertical-align: middle;
    }
</style>
