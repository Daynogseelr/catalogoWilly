@extends('app', ['page' => __('Pedidos'), 'pageSlug' => 'orders'])
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <h6 class="text-capitalize">Pedidos</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            {!! $dataTable->table(['class' => 'table table-hover table-bordered w-100']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('footer')
@endsection
@section('scripts')
    {!! $dataTable->scripts() !!}
    <script>
        (function(){
            function formatCurrency(v){ return parseFloat(v||0).toFixed(2); }
            $(document).on('click', '.btn-view-order', function(e){
                e.preventDefault();
                var id = $(this).data('id');
                openOrderModal(id, true);
            });
            function openOrderModal(id, showModal = true){
                // Build (or reuse) modal container
                var modalId = 'modal-order-detail';
                var modal = $('#' + modalId);
                if (!modal.length) {
                    modal = $("<div class='modal fade' id='"+modalId+"' tabindex='-1' aria-hidden='true'>\
                        <div class='modal-dialog modal-lg'>\
                        <div class='modal-content'>\
                        <div class='modal-header'>\
                            <h5 class='modal-title'>Detalles del pedido <span class='order-code'></span></h5>\
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Cerrar'></button>\
                        </div>\
                        <div class='modal-body'>\
                            <div class='table-responsive'>\
                                <table class='table table-striped' id='order-details-table'>\
                                    <thead><tr><th>Codigo</th><th>Nombre</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th><th>Acción</th></tr></thead>\
                                    <tbody></tbody>\
                                    <tfoot><tr><th colspan=4 class='text-end'>Total</th><th class='order-total'>0.00</th><th></th></tr></tfoot>\
                                </table>\
                            </div>\
                        </div>\
                        <div class='modal-footer order-modal-footer'>\
                        </div>\
                        </div></div></div>");
                    $('body').append(modal);
                }
                // show loading
                modal.find('tbody').html('<tr><td colspan="6" class="text-center">Cargando...</td></tr>');
                modal.find('.order-total').text('0.00');
                modal.find('.order-code').text(id);
                // fetch details
                $.ajax({
                    url: '{{ url("mostrarOrder") }}',
                    type: 'POST',
                    data: { id: id },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    dataType: 'json'
                }).done(function(res){
                    var bill = res.bill || {};
                    var details = res.bill_details || [];
                    var tbody = modal.find('tbody').empty();
                    var total = 0;
                    details.forEach(function(d){
                        var subtotal = (parseFloat(d.price)||0) * (parseFloat(d.quantity)||0);
                        total += subtotal;
                        var tr = $('<tr>');
                        tr.append($('<td>').text(d.code||''));
                        tr.append($('<td>').text(d.name||''));
                        var qty = $('<input>').attr({type:'number', min:0, step:1}).addClass('form-control form-control-sm qty-input').val(d.quantity).data('detail-id', d.id);
                        tr.append($('<td>').append(qty));
                        tr.append($('<td>').text(formatCurrency(d.price)));
                        tr.append($('<td>').text(formatCurrency(subtotal)));
                        var delBtn = $('<button>').addClass('btn btn-sm btn-danger delete-detail').text('Eliminar').data('detail-id', d.id);
                        tr.append($('<td>').append(delBtn));
                        tbody.append(tr);
                    });
                    modal.find('.order-total').text(formatCurrency(total));
                    // footer buttons
                    var footer = modal.find('.order-modal-footer').empty();
                    if (bill.status === 0 || bill.status === '0') {
                        footer.append($('<button>').addClass('btn btn-success btn-approve').text('Aprobar').data('id', id));
                        footer.append($('<button>').addClass('btn btn-danger btn-reject ms-2').text('Rechazar').data('id', id));
                    } else {
                        footer.append($('<span>').addClass('badge bg-secondary').text(bill.status == 1 ? 'APROBADO' : 'RECHAZADO'));
                    }
                    // readonly or editable
                    if (bill.status === 0 || bill.status === '0') {
                        modal.find('input.qty-input').prop('disabled', false);
                        modal.find('button.delete-detail').prop('disabled', false);
                    } else {
                        modal.find('input.qty-input').prop('disabled', true);
                        modal.find('button.delete-detail').prop('disabled', true);
                    }
                    // handlers
                    modal.find('input.qty-input').off('change').on('change', function(){
                        var detailId = $(this).data('detail-id');
                        var qty = parseInt($(this).val(),10)||0;
                        $.ajax({ url: '{{ url("updateQuantityOrder") }}', type: 'POST', data: { id: detailId, quantity: qty }, headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } })
                        .done(function(){ openOrderModal(id, false); });
                    });
                    modal.find('button.delete-detail').off('click').on('click', function(){
                        var detailId = $(this).data('detail-id');
                        if (!confirm('Eliminar detalle?')) return;
                        $.ajax({ url: '{{ url("deleteDetailOrder") }}', type: 'POST', data: { id: detailId }, headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } })
                        .done(function(){ openOrderModal(id, false); if ($.fn.dataTable.isDataTable('#orders-table')) { $('#orders-table').DataTable().ajax.reload(null,false); } });
                    });
                    modal.find('.btn-approve').off('click').on('click', function(){
                        if (!confirm('Aprobar pedido?')) return;
                        $.ajax({ url: '{{ url("changeStatusOrder") }}', type: 'POST', data: { id: id, status: 1 }, headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } })
                        .done(function(){ openOrderModal(id, false); if ($.fn.dataTable.isDataTable('#orders-table')) { $('#orders-table').DataTable().ajax.reload(null,false); } });
                    });
                    modal.find('.btn-reject').off('click').on('click', function(){
                        if (!confirm('Rechazar pedido?')) return;
                        $.ajax({ url: '{{ url("changeStatusOrder") }}', type: 'POST', data: { id: id, status: 2 }, headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } })
                        .done(function(){ openOrderModal(id, false); if ($.fn.dataTable.isDataTable('#orders-table')) { $('#orders-table').DataTable().ajax.reload(null,false); } });
                    });
                    // show modal if requested (when refreshing we avoid re-showing to prevent stacked backdrops)
                    if (showModal) {
                        var bsModal = new bootstrap.Modal(modal[0]);
                        bsModal.show();
                    }
                }).fail(function(){
                    modal.find('tbody').html('<tr><td colspan="6" class="text-center text-danger">Error cargando detalles</td></tr>');
                    var bsModal = new bootstrap.Modal(modal[0]);
                    bsModal.show();
                });
            }

            // When the modal is fully hidden, reload the orders DataTable to reflect changes
            $(document).on('hidden.bs.modal', '#modal-order-detail', function () {
                if ($.fn.dataTable && $.fn.dataTable.isDataTable('#orders-table')) {
                    $('#orders-table').DataTable().ajax.reload(null, false);
                }
            });
        })();
    </script>
@endsection
