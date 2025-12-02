@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'bill'])
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-12 card-header-info">
                                <h4>{{ __('Bills') }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive" style="font-size: 13px;">
                            {!! $dataTable->table(
                                ['class' => 'table table-striped', 'style' => 'font-size: 13px; width: 98% !important;'],
                                true,
                            ) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de pagos -->
    <div class="modal fade" id="payment-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Payment record') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 form-outline">
                            <div class="tabla table-responsive">
                                <br>
                                <table id="payment" class="table table-striped">
                                    <thead>
                                        <tr style="width:100%; text-align: center; font-size: 12px !important;">
                                            <th style="text-align: center;">{{ __('Nº') }}</th>
                                            <th style="text-align: center;">{{ __('Metodo de Pago') }}</th>
                                            <th style="text-align: center;">{{ __('Reference') }}</th>
                                            <th style="text-align: center;">{{ __('Amount') }}</th>
                                            <th style="text-align: center;">{{ __('Monto Moneda') }}</th>
                                            <th style="text-align: center;">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="paymentBill" style="font-size: 12px !important;">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" style="text-align:right;">{{ __('Total Paid') }}</th>
                                            <th colspan="3" style="text-align:left;" id="totalPaid"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- Modal Repayment -->
    <div class="modal fade" id="repayment-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Repayment') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_bill" id="id_bill">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 text-center">
                            <div class="form-check text-center repaymentType"></div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <select class="form-select" name="method_repayment" id="method_repayment"></select>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <h6 class="mt-3 mb-1 text-primary">{{ __('Productos ya devueltos') }}</h6>
                            <table class="table table-striped" id="table-devueltos">
                                <thead>
                                    <tr>
                                        <th>{{ __('Code') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Quantity') }}</th>
                                        <th id="priceReturnedTh">{{ __('Price') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="returnedProducts"></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" style="text-align:right;">{{ __('Total') }}</th>
                                        <th colspan="2" style="text-align:left;" id="totalDevuelto"></th>
                                    </tr>
                                </tfoot>
                            </table>
                            <h6 class="mt-4 mb-1 text-primary">{{ __('Productos pendientes de devolución') }}</h6>
                            <table class="table table-striped" id="table-pendientes">
                                <thead>
                                    <tr>
                                        <th>{{ __('Code') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Quantity') }}</th>
                                        <th id="priceTh">{{ __('Price') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="produrepayment"></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" style="text-align:right;">{{ __('Total') }}</th>
                                        <th colspan="3" style="text-align:left;" id="totalPendiente"></th>
                                    </tr>
                                </tfoot>
                            </table>
                            <h6 class="mt-4 mb-1 text-primary">{{ __('Productos Seleccionados para Nueva Devolución') }}
                            </h6>
                            <table class="table table-striped" id="table-nueva">
                                <thead>
                                    <tr>
                                        <th>{{ __('Code') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Quantity') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="selectedProducts"></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" style="text-align:right;">{{ __('Total') }}</th>
                                        <th colspan="3" style="text-align:left;" id="totalNueva"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="col-sm-offset-2 col-sm-12 text-center repaymentAll"><br />
                            <button type="button" class="btn btn-success"
                                id="sendRepayment">{{ __('Enviar Devolución') }}</button>
                            <button type="button" class="btn btn-danger"
                                id="repaymentAllBtn">{{ __('Devolución Completa') }}</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
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
        });

        function mostrarBillPayment(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('mostrarBillPayment') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    $('#payment-modal').modal('show');
                    $('.paymentBill').html('');
                    let totalPaid = 0;
                    let principalAbbr = '';
                    $.each(res.bill_payments, function(index, elemento) {
                        // N°
                        let num = index + 1;
                        // Metodo de pago + abreviatura moneda
                        let metodo = elemento.type;
                        let abbr = elemento.currency_abbreviation ? elemento.currency_abbreviation : '';
                        let metodoMoneda = metodo + ' (' + abbr + ')';
                        // Referencia
                        let referencia = elemento.reference ? elemento.reference : '';
                        // Monto
                        let amount = elemento.amount;
                        // Monto moneda (amount * rate)
                        let montoMoneda = (parseFloat(elemento.amount) * parseFloat(elemento.rate))
                            .toFixed(2);
                        // Suma total pagado
                        totalPaid += parseFloat(elemento.amount);
                        // Abreviatura principal (solo la primera vez)
                        if (elemento.currency_type === 'PRINCIPAL' && !principalAbbr) {
                            principalAbbr = abbr;
                        }
                        // Acción
                        let pdfUrl = '/pdfPayment/' + elemento.id;
                        $('.paymentBill').append(
                            '<tr>' +
                            '<td style="text-align: center;">' + num + '</td>' +
                            '<td style="text-align: center;">' + metodoMoneda + '</td>' +
                            '<td style="text-align: center;">' + referencia + '</td>' +
                            '<td style="text-align: right;">' + amount + '</td>' +
                            '<td style="text-align: right;">' + montoMoneda + '</td>' +
                            '<td style="text-align: center;">' +
                            '<a style="margin: -6px !important; padding: 5px; color:white;" href="' +
                            pdfUrl + '" target="_blank" class="btn btn-info">' +
                            '<i class="fa-regular fa-eye"></i>' +
                            '</a>' +
                            '</td>' +
                            '</tr>'
                        );
                    });
                    // Total pagado abajo (con abreviatura principal)
                    $('#totalPaid').html(totalPaid.toFixed(2) + (principalAbbr ? ' (' + principalAbbr + ')' :
                        ''));
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }
        // Variables globales
        let returnedProducts = [];
        let selectedProducts = [];
        let pendientes = [];
        let resGlobal = null; // Para updateAllTables
        let monedas = [];
        // Mostrar el modal de devolución
        function modalRepayment(id) {
            returnedProducts = [];
            selectedProducts = [];
            pendientes = [];
            monedas = [];
            resGlobal = null;
            $.ajax({
                type: "POST",
                url: "{{ url('modalRepayment') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    resGlobal = res;
                    $('#repayment-modal').modal('show');
                    $('#id_bill').val(id);
                    // Monedas únicas
                    if (res.bill.abbr_bill) monedas.push({
                        abbr: res.bill.abbr_bill,
                        rate: res.bill.rate_bill,
                        id: res.bill.id_currency_bill,
                        rate_official: res.bill.rate_official,
                        abbr_official: res.bill.abbr_official,
                        abbr_principal: res.bill.abbr_principal
                    });
                    if (res.bill.abbr_official) monedas.push({
                        abbr: res.bill.abbr_official,
                        rate: res.bill.rate_official,
                        id: res.bill.id_currency_official,
                        rate_official: res.bill.rate_official,
                        abbr_official: res.bill.abbr_official,
                        abbr_principal: res.bill.abbr_principal
                    });
                    if (res.bill.abbr_principal) monedas.push({
                        abbr: res.bill.abbr_principal,
                        rate: 1,
                        id: res.bill.id_currency_principal,
                        rate_official: res.bill.rate_official,
                        abbr_official: res.bill.abbr_official,
                        abbr_principal: res.bill.abbr_principal
                    });
                    monedas = monedas.filter((v, i, a) => a.findIndex(t => (t.abbr === v.abbr)) === i);

                    // Select de monedas
                    let selectHtml = '';
                    monedas.forEach(function(m) {
                        selectHtml += `<option value="${m.id}" 
                    data-rate="${m.rate}" 
                    data-abbr_repayment="${m.abbr}" 
                    data-rate_official="${m.rate_official}" 
                    data-abbr_official="${m.abbr_official}" 
                    data-abbr_principal="${m.abbr_principal}">
                    ${m.abbr}
                </option>`;
                    });
                    $('#method_repayment').html(selectHtml);
                    let rate = monedas[0].rate;
                    let abbr = monedas[0].abbr;
                    // 1. Productos ya devueltos
                    $('.returnedProducts').html('');
                    let totalDevuelto = 0;
                    res.returned_products.forEach(function(prod) {
                        let price = prod.price * rate;
                        totalDevuelto += price * prod.quantity;
                        $('.returnedProducts').append(
                            `<tr>
                        <td>${prod.code}</td>
                        <td>${prod.name}</td>
                        <td>${prod.quantity}</td>
                        <td>${price.toFixed(2)}</td>
                    </tr>`
                        );
                    });
                    $('#totalDevuelto').html(totalDevuelto.toFixed(2) + ' ' + abbr);
                    // 2. Productos pendientes de devolución
                    pendientes = [];
                    $('.produrepayment').html('');
                    let totalPendiente = 0;
                    res.products.forEach(function(prod) {
                        // prod.quantity ya viene RESTADA por el servidor (no volver a restar aquí)
                        let cantidadPendiente = prod.quantity;
                        if (cantidadPendiente > 0) {
                            pendientes.push({
                                id: prod.id,
                                code: prod.code,
                                name: prod.name,
                                quantity: cantidadPendiente,
                                price: prod.price
                            });
                            let price = prod.price * rate;
                            totalPendiente += price * cantidadPendiente;
                            $('.produrepayment').append(
                                `<tr>
                <td>${prod.code}</td>
                <td>${prod.name}</td>
                <td>${cantidadPendiente}</td>
                <td>${price.toFixed(2)}</td>
                <td>
                    <input type="number" min="1" max="${cantidadPendiente}" value="1" class="form-control form-control-sm input-return-qty" id="returnQty_${prod.id}" style="width:70px;display:inline-block;">
                    <button class="btn btn-warning btn-sm" onclick="addSelectedProduct('${prod.id}', '${prod.code}', '${prod.name}', $('#returnQty_${prod.id}').val(), '${prod.price}')"><i class="fa-solid fa-reply"></i></button>
                </td>
            </tr>`
                            );
                        }
                    });
                    $('#totalPendiente').html(totalPendiente.toFixed(2) + ' ' + abbr);
                    // 3. Productos seleccionados para nueva devolución
                    selectedProducts = [];
                    renderSelectedProducts();
                    // Cambio de moneda
                    $('#method_repayment').off('change').on('change', function() {
                        let rate = $('#method_repayment option:selected').data('rate');
                        let abbr = $('#method_repayment option:selected').data('abbr_repayment');
                        updateAllTables(rate, abbr);
                    });
                    // Tipo de devolución
                    if (res.bill.payment > 0) {
                        $('.repaymentType').html(
                            '<label class="form-check-label" for="repayment">' +
                            '{{ __('No aplicar nota de cobro') }}' +
                            '</label>' +
                            '<input class="form-check-input" type="checkbox" value="" id="repayment">'
                        );
                    } else {
                        $('.repaymentType').html(
                            '<label class="form-check-label" for="repayment">' +
                            '{{ __('The money will be returned to the customer') }}' +
                            '</label>' +
                            '<input class="form-check-input" type="checkbox" value="" id="repayment">'
                        );
                    }
                }
            });
        }

        // Agregar producto a la devolución (con validación y aviso)
        function addSelectedProduct(id, code, name, quantity, price) {
            quantity = parseInt(quantity);
            if (isNaN(quantity) || quantity < 1) return;
            let pendiente = pendientes.find(p => p.id == id);
            if (!pendiente || quantity > pendiente.quantity) {
                Swal.fire({
                    title: "{{ __('Cantidad incorrecta') }}",
                    text: "{{ __('No puede devolver más de lo disponible') }}",
                    confirmButtonText: "{{ __('Okay') }}",
                    icon: "warning"
                });
                return;
            }
            let exists = selectedProducts.find(p => p.id == id);
            let rate = $('#method_repayment option:selected').data('rate');
            let abbr = $('#method_repayment option:selected').data('abbr_repayment');
            if (!exists) {
                selectedProducts.push({
                    id,
                    code,
                    name,
                    quantity,
                    price,
                    rate,
                    abbr
                });
            } else {
                if (exists.quantity + quantity > (pendiente.quantity + exists.quantity)) {
                    Swal.fire({
                        title: "{{ __('Cantidad incorrecta') }}",
                        text: "{{ __('No puede devolver más de lo disponible') }}",
                        confirmButtonText: "{{ __('Okay') }}",
                        icon: "warning"
                    });
                    return;
                }
                exists.quantity += quantity;
            }
            pendiente.quantity -= quantity;
            renderSelectedProducts(rate, abbr);
            $(`#returnQty_${id}`).attr('max', pendiente.quantity).val(1);
            $(`#returnQty_${id}`).closest('tr').find('td').eq(2).text(pendiente.quantity);
            if (pendiente.quantity <= 0) {
                $(`#returnQty_${id}`).prop('disabled', true);
            }
        }

        // Renderiza la tabla de seleccionados (usa el rate y abbr actuales)
        function renderSelectedProducts(rate = null, abbr = null) {
            if (rate === null) rate = $('#method_repayment option:selected').data('rate');
            if (abbr === null) abbr = $('#method_repayment option:selected').data('abbr_repayment');
            $('.selectedProducts').html('');
            let totalNueva = 0;
            selectedProducts.forEach(function(prod) {
                let price = prod.price * rate;
                totalNueva += price * prod.quantity;
                $('.selectedProducts').append(
                    `<tr>
                <td>${prod.code}</td>
                <td>${prod.name}</td>
                <td>${prod.quantity}</td>
                <td>${price.toFixed(2)} ${abbr}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="removeSelectedProduct('${prod.id}')">{{ __('Remove') }}</button>
                </td>
            </tr>`
                );
            });
            $('#totalNueva').html(totalNueva.toFixed(2) + ' ' + abbr);
        }

        // Quitar producto de la devolución
        function removeSelectedProduct(id) {
            let prod = selectedProducts.find(p => p.id == id);
            if (!prod) return;
            let pendiente = pendientes.find(p => p.id == id);
            if (pendiente) {
                pendiente.quantity += prod.quantity;
                $(`#returnQty_${id}`).attr('max', pendiente.quantity).prop('disabled', false);
                $(`#returnQty_${id}`).closest('tr').find('td').eq(2).text(pendiente.quantity);
            }
            selectedProducts = selectedProducts.filter(p => p.id != id);
            renderSelectedProducts();
        }

        // Actualiza precios y totales al cambiar moneda (solo pendientes y devueltos)
        function updateAllTables(rate, abbr) {
            // Pendientes
            let totalPendiente = 0;
            $('.produrepayment tr').each(function() {
                let id = $(this).find('input').attr('id').replace('returnQty_', '');
                let pendiente = pendientes.find(p => p.id == id);
                if (pendiente) {
                    let price = pendiente.price * rate;
                    $(this).find('td').eq(3).text(price.toFixed(2));
                    totalPendiente += price * pendiente.quantity;
                }
            });
            $('#totalPendiente').html(totalPendiente.toFixed(2) + ' ' + abbr);

            // Devueltos
            let totalDevuelto = 0;
            if (resGlobal && resGlobal.returned_products) {
                $('.returnedProducts tr').each(function(index) {
                    let prod = resGlobal.returned_products[index];
                    if (prod) {
                        let price = prod.price * rate;
                        $(this).find('td').eq(3).text(price.toFixed(2));
                        totalDevuelto += price * prod.quantity;
                    }
                });
            }
            $('#totalDevuelto').html(totalDevuelto.toFixed(2) + ' ' + abbr);

            // Seleccionados: actualizar precios y total según la moneda seleccionada
            renderSelectedProducts(rate, abbr);
        }

        // Reinicia variables y cantidades al cerrar el modal
        $('#repayment-modal').on('hidden.bs.modal', function() {
            returnedProducts = [];
            selectedProducts = [];
            pendientes = [];
            $('.returnedProducts').html('');
            $('.selectedProducts').html('');
            $('.produrepayment tr').each(function() {
                let prodQtyCell = $(this).find('td').eq(2);
                if (prodQtyCell.data('original')) {
                    prodQtyCell.text(prodQtyCell.data('original'));
                    prodQtyCell.data('actual', prodQtyCell.data('original'));
                }
                let input = $(this).find('input[type="number"]');
                input.val(1).prop('disabled', false);
                input.attr('max', prodQtyCell.data('original') || input.attr('max'));
            });
        });

        // Enviar devolución parcial
        $('#sendRepayment').click(function() {
            var id_bill = $('#id_bill').val();
            var id_currency = $('#method_repayment').val();
            var rate = $('#method_repayment option:selected').data('rate');
            var rate_official = $('#method_repayment option:selected').data('rate_official') || '';
            var abbr_repayment = $('#method_repayment option:selected').data('abbr_repayment');
            var abbr_official = $('#method_repayment option:selected').data('abbr_official') || '';
            var abbr_principal = $('#method_repayment option:selected').data('abbr_principal') || '';
            const repayment = document.getElementById('repayment');
            var michek = repayment && repayment.checked ? 1 : 0;

            // Enviar productos seleccionados
            $.ajax({
                type: "POST",
                url: "{{ url('saveRepayment') }}",
                data: {
                    id_bill: id_bill,
                    michek: michek,
                    returnedProducts: selectedProducts,
                    id_currency: id_currency,
                    rate: rate,
                    rate_official: rate_official,
                    abbr_repayment: abbr_repayment,
                    abbr_official: abbr_official,
                    abbr_principal: abbr_principal,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(res) {
                    $('#repayment-modal').modal('hide');
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Repayment saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#bills-table').DataTable().ajax.reload();
                    if (res.id) {
                        const pdfLink = "{{ route('pdfNoteCredit', ':id') }}".replace(':id', res.id);
                        window.open(pdfLink, '_blank');
                    }
                },
                error: function(error) {
                    Swal.fire('Error', 'No se pudo guardar la devolución.', 'error');
                    console.log(error);
                }
            });
        });

        // Enviar devolución completa
        $('#repaymentAllBtn').click(function() {
            saveRepaymentAll();
        });

        function saveRepaymentAll() {
            var id_bill = $('#id_bill').val();
            var id_currency = $('#method_repayment').val();
            var rate = $('#method_repayment option:selected').data('rate');
            var rate_official = $('#method_repayment option:selected').data('rate_official') || '';
            var abbr_repayment = $('#method_repayment option:selected').data('abbr_repayment');
            var abbr_official = $('#method_repayment option:selected').data('abbr_official') || '';
            var abbr_principal = $('#method_repayment option:selected').data('abbr_principal') || '';
            const repayment = document.getElementById('repayment');
            var michek = repayment && repayment.checked ? 1 : 0;
            $.ajax({
                type: "POST",
                url: "{{ url('saveRepaymentAll') }}",
                data: {
                    id_bill: id_bill,
                    michek: michek,
                    id_currency: id_currency,
                    rate: rate,
                    rate_official: rate_official,
                    abbr_repayment: abbr_repayment,
                    abbr_official: abbr_official,
                    abbr_principal: abbr_principal,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(res) {
                    // Actualizar tabla
                    $('#bills-table').DataTable().ajax.reload();
                    $('#repayment-modal').modal('hide');
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Log saved successfully') }}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    modalRepayment(res.id_bill);
                    const pdfLink = "{{ route('pdfNoteCredit', ':id') }}".replace(':id', res.id);
                    window.open(pdfLink, '_blank');
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                    }
                }
            });
        }
    </script>
@endsection
