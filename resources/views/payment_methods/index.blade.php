
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'payment'])
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
    .table-bordered th, .table-bordered td {
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
    <div class="container-fluid ">
            <div class="row ">
                <div class="col-lg-12 mb-lg-0 mb-4">
                    <div class="card z-index-2 h-100">
                        <div class="card-header pb-0 pt-3 bg-transparent">
                            <div class="row">
                                <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                    <div class="row">
                                        <div class="col-10 col-sm-11">
                                            <h4>{{__('Métodos de Pago')}}</h4>
                                        </div>
                                        <div class="col-2 col-sm-1">
                                            <a class="btn btn-danger2" id="btnAddPaymentMethod">
                                                <i class="fa-solid fa-circle-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive" style="font-size: 13px;">
                                {{-- DataTable --}}
                                {!! $dataTable->table(['id' => 'payment-methods-table', 'class' => 'table table-striped table-bordered w-100']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Agregar/Editar Método de Pago -->
    <div class="modal fade" id="paymentMethodModal" tabindex="-1" aria-labelledby="paymentMethodModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="paymentMethodForm">
                @csrf
                <input type="hidden" id="payment_method_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentMethodModalLabel">Nuevo Método de Pago</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="type" class="form-label">{{ __('Tipo') }}</label>
                            <select class="form-select" name="type" id="type" required>
                                <option value="">{{ __('Seleccione un tipo de pago') }}</option>
                                <option value="EFECTIVO">{{ __('Efectivo') }}</option>
                                <option value="TARJETA DE DEBITO">{{ __('Tarjeta de débito') }}</option>
                                <option value="BIOPAGO">{{ __('Biopago') }}</option>
                                <option value="TRANSFERENCIA">{{ __('Transferencia bancaria') }}</option>
                                <option value="PAGO MOVIL">{{ __('Pago móvil') }}</option>
                                <option value="BINANCE">{{ __('Binance') }}</option>
                                <option value="PAYPAL">{{ __('PayPal') }}</option>
                                <option value="ZELLE">{{ __('Zelle') }}</option>
                                <option value="RETENCION">{{ __('Retención') }}</option>
                                <option value="OTRO">{{ __('Otro') }}</option>
                            </select>
                            <input type="text" class="form-control mt-2 d-none" id="type_other" name="type_other"
                                placeholder="Especifique otro método de pago" style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase();">
                        </div>
                        <div class="mb-3">
                            <label for="id_currency" class="form-label">Moneda</label>
                            <select class="form-select" id="id_currency" name="id_currency" required>
                                <option value="">Seleccione...</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }}
                                        ({{ $currency->abbreviation }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="bank" class="form-label">Banco</label>
                            <input type="text" class="form-control" id="bank" name="bank"
                                style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                        </div>
                        <div class="mb-3">
                            <label for="reference" class="form-label">Referencia</label>
                            <select class="form-select" name="reference" id="reference" required>
                                <option value="">{{ __('Seleccione') }}</option>
                                <option value="1">SI</option>
                                <option value="0">NO</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="data" class="form-label">Datos</label>
                            <input type="text" class="form-control" id="data" name="data">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    {!! $dataTable->scripts() !!}
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Abrir modal para nuevo método
            $('#btnAddPaymentMethod').on('click', function() {
                $('#paymentMethodForm')[0].reset();
                $('#payment_method_id').val('');
                $('#paymentMethodModalLabel').text('Nuevo Método de Pago');
                $('#type_other').addClass('d-none').val('').removeAttr('required'); // <-- OCULTA Y LIMPIA
                const modal = new bootstrap.Modal(document.getElementById('paymentMethodModal'));
                modal.show();
            });

            // Editar método
            $(document).on('click', '.btn-edit', function() {
                var id = $(this).data('id');
                $.get("{{ url('payment-methods') }}/" + id + "/edit", function(data) {
                    $('#payment_method_id').val(data.id);
                    $('#id_currency').val(data.id_currency);
                    $('#bank').val(data.bank);
                    $('#reference').val(data.reference);
                    $('#data').val(data.data);
                    $('#status').val(data.status);
                    $('#paymentMethodModalLabel').text('Editar Método de Pago');

                    // Verifica si el tipo está en las opciones del select
                    let typeSelect = $('#type');
                    let typeOther = $('#type_other');
                    let found = false;
                    typeSelect.find('option').each(function() {
                        if ($(this).val() === data.type) {
                            found = true;
                            return false;
                        }
                    });

                    if (found) {
                        typeSelect.val(data.type);
                        typeOther.addClass('d-none').val('').removeAttr('required');
                    } else {
                        typeSelect.val('OTRO');
                        typeOther.removeClass('d-none').val(data.type).attr('required', true);
                    }

                    const modal = new bootstrap.Modal(document.getElementById(
                    'paymentMethodModal'));
                    modal.show();
                });
            });

            // Guardar método (nuevo o editar)
            $('#paymentMethodForm').on('submit', function(e) {
                e.preventDefault();
                let typeSelect = $('#type');
                let originalType = typeSelect.val();
                if (originalType === 'OTRO') {
                    // Si el campo está vacío, no envíes el formulario
                    if (!$('#type_other').val().trim()) {
                        $('#type_other').focus();
                        Swal.fire('Error', 'Debe especificar el otro método de pago.', 'error');
                        return;
                    }
                    // Cambia temporalmente el valor del select para el envío
                    typeSelect.append($('<option>', {
                        value: $('#type_other').val(),
                        text: $('#type_other').val(),
                        selected: true
                    }));
                }
                var id = $('#payment_method_id').val();
                var url = id ? "{{ url('payment-methods') }}/" + id : "{{ url('payment-methods') }}";
                var method = id ? 'PUT' : 'POST';
                $.ajax({
                    url: url,
                    method: method,
                    data: $(this).serialize(),
                    success: function(response) {
                        const modalEl = document.getElementById('paymentMethodModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();
                        window.LaravelDataTables["payment-methods-table"].ajax.reload(null,
                            false);
                        Swal.fire('¡Éxito!', response.message, 'success');
                        // Restaurar el select a OTRO si fue cambiado
                        if (originalType === 'OTRO') {
                            typeSelect.val('OTRO');
                            typeSelect.find('option[value="' + $('#type_other').val() + '"]')
                                .remove();
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Error al guardar.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).join('<br>');
                        }
                        Swal.fire('Error', msg, 'error');
                        // Restaurar el select a OTRO si fue cambiado
                        if (originalType === 'OTRO') {
                            typeSelect.val('OTRO');
                            typeSelect.find('option[value="' + $('#type_other').val() + '"]')
                                .remove();
                        }
                    }
                });
            });

            // Cambiar estado
            $(document).on('click', '.btn-toggle-status', function() {
                var id = $(this).data('id');
                $.post("{{ url('payment-methods') }}/" + id + "/toggle-status", {
                    _token: '{{ csrf_token() }}'
                }, function(response) {
                    window.LaravelDataTables["payment-methods-table"].ajax.reload(null, false);
                    Swal.fire('¡Actualizado!', response.message, 'success');
                });
            });

            $('#type').on('change', function() {
                if ($(this).val() === 'OTRO') {
                    $('#type_other').removeClass('d-none').attr('required', true);
                } else {
                    $('#type_other').addClass('d-none').val('').removeAttr('required');
                }
            });
        });
    </script>
@endsection

