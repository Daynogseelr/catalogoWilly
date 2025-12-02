@extends('app', ['page' => __('Moneda'), 'pageSlug' => 'currency'])
@section('content')
    <style>
        .card-header-info h4 {
            font-weight: 700;
            color: #35b3e5;
            letter-spacing: 1px;
            margin-bottom: 0;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 6px;
            padding: 4px 8px;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 6px;
            padding: 4px 8px;
        }

        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #e0e0e0 !important;
        }

        .dt-buttons .dt-button {
            background: #ececec !important;
            color: #000000 !important;
            border-radius: 6px !important;
            margin-right: 4px !important;
            border: none !important;
            font-weight: 600 !important;
            padding: 6px 14px !important;
        }

        .dt-buttons .dt-button:hover {
            background: #d1d0d0 !important;
            color: #000000 !important;
        }
    </style>
    <div class="container-fluid">
        <div class="row mt-0">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-10 col-sm-11">
                                        <h4>{{ __('Gestión de Monedas') }}</h4>
                                    </div>
                                    <div class="col-2 col-sm-1">
                                        <button type="button" class="btn btn-danger2" data-bs-toggle="modal"
                                            data-bs-target="#currencyModal" data-mode="create">
                                            <i class="fa-solid fa-circle-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive" style="font-size: 13px;">
                            {!! $currencyDataTable->html()->table(
                                    ['class' => 'table table-striped table-bordered w-100', 'id' => 'currency-table', 'style' => 'font-size:13px;'],
                                    true,
                                ) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal Moneda --}}
    <div class="modal fade" id="currencyModal" tabindex="-1" aria-labelledby="currencyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="currencyModalLabel">Crear Moneda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="currencyForm">
                    @csrf
                    <input type="hidden" name="_method" id="currencyFormMethod" value="POST">
                    <input type="hidden" name="currency_id" id="currencyId">
                    <div class="modal-body row">
                        <div class="col-12 mb-3">
                            <label for="currency_name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="currency_name" name="name" required
                                onkeyup="mayus(this);">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="abbreviation" class="form-label">Abreviatura</label>
                            <input type="text" class="form-control" id="abbreviation" name="abbreviation" required
                                onkeyup="mayus(this);">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="rate" class="form-label">tasa</label>
                            <input type="text" class="form-control" id="rate" name="rate" required
                                onkeypress="return validaMonto(event);">
                        </div>
                         <div class="col-6 mb-3">
                            <label for="is_principal" class="form-label">¿Moneda de Principal?</label>
                            <select class="form-select" id="is_principal" name="is_principal" required>
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                            <small class="form-text text-info">Solo una moneda puede estar marcada como principal.</small>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="is_official" class="form-label">¿Moneda de Oficial?</label>
                            <select class="form-select" id="is_official" name="is_official" required>
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                            <small class="form-text text-info">Solo una moneda puede estar marcada como oficial.</small>
                        </div>
                        <div class="mb-3" id="currencyStatusField" style="display: none;">
                            <label for="currency_status" class="form-label">Estado</label>
                            <select class="form-select" id="currency_status" name="status" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="saveCurrencyBtn">Guardar Moneda</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    {!! $currencyDataTable->html()->scripts() !!}
    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var currencyModal = new bootstrap.Modal(document.getElementById('currencyModal'));
            var currencyForm = $('#currencyForm');
            var currencyModalLabel = $('#currencyModalLabel');
            var currencyFormMethod = $('#currencyFormMethod');
            var currencyIdField = $('#currencyId');
            var principalCurrencyWarning = $('#principalCurrencyWarning');
            var currencyStatusField = $('#currencyStatusField');
            var saveCurrencyBtn = $('#saveCurrencyBtn');
            // --- Funciones de Ayuda ---
            function resetCurrencyForm() {
                currencyForm[0].reset();
                currencyForm.attr('action', '');
                currencyIdField.val('');
                currencyModalLabel.text('Crear Moneda');
                saveCurrencyBtn.text('Guardar Moneda');
                currencyFormMethod.val('POST');
                currencyStatusField.hide();
                principalCurrencyWarning.hide();
            }
            // --- Evento abrir modal ---
            $('#currencyModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var mode = button.data('mode');
                resetCurrencyForm();
                if (mode === 'create') {
                    currencyModalLabel.text('Crear Nueva Moneda');
                    currencyForm.attr('action', '{{ route('currencies.store') }}');
                    // Verificar si ya existe una moneda PRINCIPAL
                } else if (mode === 'edit') {
                    var currencyId = button.data('id');
                    currencyModalLabel.text('Editar Moneda');
                    saveCurrencyBtn.text('Actualizar Moneda');
                    currencyFormMethod.val('PUT');
                    currencyStatusField.show();
                    currencyIdField.val(currencyId);
                    currencyForm.attr('action', '{{ route('currencies.update', ':currencyId') }}'.replace(
                        ':currencyId', currencyId));
                    $.get('{{ route('currencies.edit', ':currencyId') }}'.replace(':currencyId',
                        currencyId),
                        function(response) {
                            $('#currency_name').val(response.name ? response.name.toUpperCase() : '');
                            $('#abbreviation').val(response.abbreviation ? response.abbreviation
                                .toUpperCase() : '');
                            $('#currency_status').val(response.status);
                            $('#rate').val(response.rate);
                            $('#is_principal').val(response.is_principal);
                            $('#is_official').val(response.is_official);
                        });
                }
            });

            // --- Envío del formulario de Moneda ---
            currencyForm.on('submit', function(e) {
                e.preventDefault();
                var url = currencyForm.attr('action');
                var formData = currencyForm.serializeArray();
                var requestMethod = currencyFormMethod.val();
                $.each(formData, function(i, field) {
                    if (field.name === 'name' || field.name === 'abbreviation' || field.name ===
                        'type') {
                        field.value = field.value.toUpperCase();
                    }
                });
                var serializedFormData = $.param(formData);
                if (requestMethod === 'PUT' || requestMethod === 'PATCH' || requestMethod === 'DELETE') {
                    serializedFormData += '&_method=' + requestMethod;
                }
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: serializedFormData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            currencyModal.hide();
                            $('#currency-table').DataTable().ajax.reload(null, false);
                        });
                    },
                    error: function(xhr) {
                        var errorMessage = 'Error al guardar la moneda.';
                        var title = 'Error';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = Object.values(xhr.responseJSON.errors).map(err =>
                                '- ' + err.join('<br>')).join('<br>');
                            title = 'Errores de Validación';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: title,
                            html: errorMessage
                        });
                    }
                });
            });
        });
    </script>
    @if (session('warning'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: '¡Atención!',
                text: '{{ session('warning') }}',
                confirmButtonText: 'Aceptar'
            });
        </script>
    @endif
@endsection
