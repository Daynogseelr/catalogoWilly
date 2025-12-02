@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'credit'])
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-6 col-sm-6 col-md-3">
                                        <h4>{{ __('Cuentas por Cobrar') }}</h4>
                                    </div>
                                    <div class="col-6 col-sm-6 col-md-3 totales-container">
                                        <div class="totales-content">
                                            <h5>
                                                <span class="total-label">Totales:</span>
                                                <span id="totalDolares" class="total-amount">0.00</span>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="col-8 col-sm-10 col-md-4 text-end">
                                        <select class="form-select" name="client" id="single-select-field"
                                            data-placeholder="{{ __('Select client') }}">
                                            <option value="TODOS">TODOS</option>
                                            @foreach ($bills as $bill)
                                                <option value="{{ $bill->client->id }}"
                                                    @if (isset($id_shopper) && $id_shopper == $bill->client->id) selected @endif>
                                                    {{ $bill->client->name }} {{ $bill->client->last_name }}
                                                    {{ $bill->client->nationality }}-{{ $bill->client->ci }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4 col-sm-2 col-md-2 text-end">
                                        <select class="form-select" id="currency-select">
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}" data-rate="{{ $currency->rate }}"
                                                    data-abbr="{{ $currency->abbreviation }}">
                                                    {{ $currency->name }} ({{ $currency->abbreviation }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="card-body">
                            <div class="tabla table-responsive" style="font-size: 13px;">
                                <table class="table table-striped" id="ajax-crud-datatableCredit"
                                    style="font-size: 13px; width: 98% !important;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{ __('Code') }}</th>
                                            <th class="text-center">{{ __('Date') }}</th>
                                            <th class="text-center">{{ __('Seller') }}</th>
                                            <th class="text-center">{{ __('Client') }}</th>
                                            <th class="text-center">{{ __('Identification Document') }}</th>
                                            <th class="text-center">{{ __('Days') }}</th>
                                            <th class="text-center">{{ __('Forma') }}</th>
                                            <th class="text-center total-col">{{ __('total') }}</th>
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
                    <input type="hidden" id="id_bill">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Método de Pago</label>
                            <select class="form-select" id="pago_metodo">
                                <option value="">Seleccione...</option>
                                @foreach ($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}" data-moneda="{{ $pm->currency->abbreviation }}"
                                        data-currency="{{ $pm->id_currency }}" data-reference="{{ $pm->reference }}">
                                        {{ $pm->type }} ({{ $pm->currency->abbreviation }})
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
                    <button type="button" class="btn btn-success" id="btnProcesarPagos"
                        onClick="storePaymentCredit()">Registrar
                        Pagos</button>
                </div>
                <div class="p-3">
                    <h6 >Pagos Realizados</h6>
                    <table class="table table-bordered table-sm" id="tablaPagosRealizados">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th class="end">Monto</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('footer')
@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script type="text/javascript">
        var currencyPrincipal = @json($currencyPrincipal);
        var currencies = @json($currencies);
        let table;
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            initializeDataTable();
            $('#single-select-field').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                dropdownCssClass: "color",
                selectionCssClass: "form-select",
                language: "es"
            });
        });
            function initializeDataTable(clientId = null) {
                if ($.fn.DataTable.isDataTable('#ajax-crud-datatableCredit')) {
                    $('#ajax-crud-datatableCredit').DataTable().destroy();
                }
                table = $('#ajax-crud-datatableCredit').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('ajax-crud-datatableCredit') }}',
                        data: {
                            client: clientId
                        } // Enviar el ID del cliente
                    },
                    columns: [
                { data: 'code', name: 'code' },
                { data: 'created_at', name: 'created_at' },
                { data: 'sellerName', name: 'sellerName' },
                { data: 'clientName', name: 'clientName' },
                { data: 'ci', render: function(data, type, row) {
                    return `${row.nationality}-${row.ci}`;
                }},
                { data: 'creditDays', name: 'creditDays', className: 'text-end' },
                { data: 'forma', render: function(data, type, row) {
                    return `${row.name} (${row.abbr})`;
                }},
                { 
                    data: 'payment',
                    name: 'payment',
                    className: 'credit-total',
                    render: function(data, type, row) {
                        // Guardamos el monto base y el rate original en data attributes para el JS
                        return `<span class="credit-amount" data-amount="${row.payment}" data-rate="1">${parseFloat(row.payment).toFixed(2)}</span>`;
                    }
                },
                { data: 'action', name: 'action', orderable: false }
            ],
                    drawCallback: function(settings) {
                        centerTableContent();
                         actualizarTotalesTabla();
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
            }
            // Inicializar la tabla por primera vez (con todos los registros)
          
            function centerTableContent() {
                $('#ajax-crud-datatableCredit tbody tr td:nth-child(1)').addClass('text-center');
                $('#ajax-crud-datatableCredit tbody tr td:nth-child(2)').addClass('text-center');
                $('#ajax-crud-datatableCredit tbody tr td:nth-child(5)').addClass('text-center');
                $('#ajax-crud-datatableCredit tbody tr td:nth-child(6)').addClass('text-end').css('padding-right',
                    '50px');
                $('#ajax-crud-datatableCredit tbody tr td:nth-child(7)').addClass('text-end').css('padding-right',
                    '50px');
                $('#ajax-crud-datatableCredit tbody tr td:nth-child(8)').addClass('text-end').css('padding-right',
                    '50px');
            }
            
            // Actualizar totales al cambiar moneda
            $('#currency-select').on('change', function() {
                actualizarTotalesTabla();
            });

            // Actualizar tabla y totales al cambiar cliente
            $('#single-select-field').on('change', function() {
                var clientId = $(this).val();
                initializeDataTable(clientId);
                setTimeout(actualizarTotalesTabla, 500); // Espera a que se recargue la tabla
            });

            // Al cargar la página, actualiza totales
            setTimeout(actualizarTotalesTabla, 500);

            function actualizarTotalesTabla() {
                let rate = parseFloat($('#currency-select option:selected').data('rate'));
                let abbr = $('#currency-select option:selected').data('abbr');
                let total = 0;

                $('#ajax-crud-datatableCredit tbody tr').each(function() {
                    let $span = $(this).find('.credit-amount');
                    let baseAmount = parseFloat($span.data('amount'));
                    let baseRate = parseFloat($span.data('rate')) || 1;
                    let converted = (baseAmount / baseRate) * rate;
                    $span.text(converted.toFixed(2) + ' ' + abbr);
                    total += converted;
                });

                $('#totalDolares').text(total.toFixed(2) + ' ' + abbr);
            }


        let pagos = [];
        let totalOrden = 0;
        // Abrir modal de facturación
        function credit(id) {
            $('#id_bill').val(id);
            $.ajax({
                type: "POST",
                url: "{{ url('credit') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    pagos = [];
                    totalOrden = parseFloat((res.amount));
                    $('#totalRestante').text(totalOrden.toFixed(2) + ' ' + currencyPrincipal.abbreviation);
                    $('#tablaPagos tbody').empty();
                    $('#btnAgregarPago').prop('disabled', false); // <-- Habilita el botón Agregar
                    $('#vueltoInfo').html('');
                    $('#pago_metodo').val('');
                    $('#pago_monto').val('');
                    $('#pago_referencia').val('').addClass('d-none');
                    $('#facturarModal').modal('show');
                    $('#dtNoteCredit').html('');
                    $('#tablaPagosRealizados tbody').empty();
                    if (res.payments && res.payments.length > 0) {
                        let pagosHtml = '';
                        res.payments.forEach(function(p) {
                            pagosHtml += `<tr>
                                <td>${p.created_at ? moment(p.created_at).format('DD/MM/YYYY') : ''}</td>
                                <td>${p.code_repayment ? 'Nota Crédito' : (p.payment_type || '') + ' ' + (p.currency_abbr || '') + (p.payment_bank ? ' - ' + p.payment_bank : '')}</td>
                                <td>${p.reference ? p.reference : (p.code_repayment ? p.code_repayment : '-')}</td>
                                <td class="end">${parseFloat(p.amount).toFixed(2)}</td>
                            </tr>`;
                        });
                        pagosHtml += `<tr class="total-row">
                            <td colspan="3" class="end"><b>Total Pagado:</b></td>
                            <td class="end"><b>${parseFloat(res.totalPagado).toFixed(2)} ${res.bill.abbr_principal}</b></td>
                        </tr>`;
                        $('#tablaPagosRealizados tbody').html(pagosHtml);
                    } else {
                        $('#tablaPagosRealizados tbody').html('<tr><td colspan="4" class="text-center">No hay pagos registrados</td></tr>');
                    }
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
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }

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
        function storePaymentCredit() {
            event.preventDefault();
            var id_bill = $('#id_bill').val();
            $.ajax({
                type: "POST",
                url: "{{ url('storePaymentCredit') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    pagos: pagos,
                    id_bill: id_bill
                },
                dataType: 'json',
                success: function(id) {
                    const modalEl = document.getElementById('facturarModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                    var id_client = $('#single-select-field').val();
                    if (id_client) {   
                        initializeDataTable(id_client);
                    } else {
                        initializeDataTable();
                    }
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
       
    </script>
@endsection
