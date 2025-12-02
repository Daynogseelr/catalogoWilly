@extends('app', ['page' => __('Transferencias de Inventario'), 'pageSlug' => 'inventory-transfers'])
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Transferencias</h4>
                    <button class="btn btn-danger2" id="btnNewTransfer"><i class="fa-solid fa-circle-plus"></i></button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {!! $dataTable->table(['class' => 'table table-hover table-bordered w-100']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal crear transferencia -->
    <div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Transferencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="transferForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Origen</label>
                                <select class="form-select" name="id_sucursal_from" id="id_sucursal_from">
                                    <option value="">Seleccione...</option>
                                    @foreach ($sucursales as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Destino</label>
                                <select class="form-select" name="id_sucursal_to" id="id_sucursal_to">
                                    <option value="">Seleccione...</option>
                                    @foreach ($sucursales as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <hr>
                        <h6>Productos</h6>
                        <table class="table table-sm" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Existencia</th>
                                    <th>Cantidad</th>
                                    <th><button type="button" id="addRow" class="btn btn-sm btn-success">+</button></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <div class="mb-2">
                            <label>Notas (opcional)</label>
                            <textarea class="form-control" id="notes" name="notes"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="editing_transfer_id" value="">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-danger" id="rejectTransfer" style="display:none">Rechazar</button>
                    <button class="btn btn-success" id="approveTransfer" style="display:none">Aprobar</button>
                        <button class="btn btn-success" id="markLista" style="display:none">Marcar como LISTA</button>
                    <button class="btn btn-primary" id="saveTransfer">Enviar</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
{!! $dataTable->scripts() !!}
<script>
$(function(){
    // abrir modal en modo crear
    $('#btnNewTransfer').on('click', function(){
        // set creation mode
        $('#editing_transfer_id').val('');
        $('#transferModal .modal-title').text('Nueva Transferencia');
        // enable sucursal selects for creation
        $('#id_sucursal_from').prop('disabled', false);
        $('#id_sucursal_to').prop('disabled', true).empty().append('<option value="">Seleccione...</option>');
        // reset form fields
        $('#itemsTable tbody').empty();
        $('#notes').val('');
        // show appropriate buttons
        $('#addRow').show();
        $('#saveTransfer').show();
        $('#approveTransfer').hide();
        $('#rejectTransfer').hide();
        $('#transferModal').modal('show');
        $('#addRow').prop('disabled', true);
        $('#markLista').hide();
    });

    var sucursales = @json($sucursales);
    var currentUserType = @json(auth()->user()->type ?? '');

    // inicialmente no permitir elegir destino hasta seleccionar origen
    $('#id_sucursal_to').prop('disabled', true);
    // deshabilitar boton agregar hasta tener origen y destino
    $('#addRow').prop('disabled', true);

    var products = @json($products);
    var selectedIds = new Set();

    function buildOptions(excludeSet){
        var opts = '<option value="">Seleccione...</option>';
        products.forEach(function(p){
            if(!excludeSet.has(String(p.id))){
                var code2 = p.code2 ?? '';
                opts += '<option value="'+p.id+'" data-code2="'+code2+'">'+p.code+' - '+p.name+'</option>';
            }
        });
        return opts;
    }

    function initProductSelect($select){
        $select.select2({
            theme: 'bootstrap-5',
            width: $select.data('width') ? $select.data('width') : ($select.hasClass('w-100') ? '100%' : 'style'),
            placeholder: $select.data('placeholder') || 'Buscar producto por código, nombre o code2',
            dropdownCssClass: 'color',
            selectionCssClass: 'form-select',
            dropdownParent: $('#transferModal .modal-body'),
            language: 'es',
            allowClear: false,
            matcher: function(params, data) {
                if ($.trim(params.term) === '') { return data; }
                var term = params.term.toLowerCase();
                var text = (data.text || '').toLowerCase();
                var code2 = ($(data.element).data('code2') || '').toString().toLowerCase();
                if (text.indexOf(term) > -1 || code2.indexOf(term) > -1) { return data; }
                return null;
            }
        });

        $select.on('select2:select', function(e){
            var id = e.params.data.id;
            selectedIds.add(String(id));
            $('select.item-product').not(this).each(function(){
                $(this).find('option[value="'+id+'"]').remove();
                $(this).trigger('change.select2');
            });
            // disable this select so it cannot be changed
            $(this).prop('disabled', true);
            if ($(this).data('select2')) { $(this).trigger('change.select2'); }

            // fetch existencia según sucursal origen seleccionada
            var fromId = $('#id_sucursal_from').val();
            var $existCell = $(this).closest('tr').find('.item-existence');
            $existCell.text('...');
            if (fromId) {
                console.log('Requesting stock for product', id, 'sucursal', fromId);
                $.get('{!! route("inventory-transfers.stock") !!}', { id_product: id, id_sucursal: fromId })
                            .done(function(resp){
                                console.log('Stock response', resp);
                                var q = resp && typeof resp.quantity !== 'undefined' ? parseFloat(resp.quantity) : 0;
                                var display = (Number.isFinite(q) ? (Math.round(q * 100) / 100) : '-');
                                var qInt = Math.floor(q);
                                if (q <= 0) {
                                    $existCell.text('Sin existencia').addClass('text-danger');
                                    // disable quantity input and clear value (no 0 allowed)
                                    var $qtyInput = $existCell.closest('tr').find('.item-qty');
                                    $qtyInput.val('').prop('disabled', true).addClass('is-invalid');
                                    // mark row/select visually
                                    $existCell.closest('tr').addClass('out-of-stock');
                                } else {
                                    $existCell.text(display).removeClass('text-danger');
                                    var $qtyInput = $existCell.closest('tr').find('.item-qty');
                                    $qtyInput.prop('disabled', false).removeClass('is-invalid');
                                    // set min, step and integer max on input and clamp current value
                                    $qtyInput.attr('min', 1).attr('step', 1).attr('max', qInt);
                                    var cur = parseInt($qtyInput.val(), 10);
                                    if (isNaN(cur) || cur < 1) { $qtyInput.val(1); cur = 1; }
                                    if (cur > qInt) { $qtyInput.val(qInt); }
                                    $existCell.closest('tr').removeClass('out-of-stock');
                                }
                            }).fail(function(xhr){
                                console.error('Stock request failed', xhr);
                                $existCell.text('-');
                            });
            } else {
                $existCell.text('-');
            }
        });
    }

    $('#addRow').on('click', function(){
        // prevent adding if there is any row with product select empty
        var emptyExists = false;
        $('#itemsTable tbody tr').each(function(){
            var val = $(this).find('.item-product').val();
            if(!val){ emptyExists = true; return false; }
        });
        if(emptyExists){ alert('Seleccione el producto en la fila existente antes de agregar otra.'); return; }

        var opts = buildOptions(selectedIds);
        var $row = $('<tr>\n<td><select class="form-select form-select-sm item-product w-100" data-placeholder="Buscar producto...">'+opts+'</select></td>\n<td class="item-existence text-end">-</td>\n<td><input type="number" min="1" step="1" class="form-control form-control-sm item-qty text-end" value="1" style="width:90px"></td>\n<td><button type="button" class="btn btn-sm btn-danger removeRow">-</button></td>\n</tr>');
        $('#itemsTable tbody').append($row);
        var $select = $row.find('.item-product');
        initProductSelect($select);
    });

    $(document).on('click', '.removeRow', function(){
        var $row = $(this).closest('tr');
        var $select = $row.find('.item-product');
        var val = $select.val();
        if(val){
            // free the product id
            selectedIds.delete(String(val));
            // re-add option to other selects if not present
            $('select.item-product').each(function(){
                if(!$(this).find('option[value="'+val+'"]').length){
                    var prod = products.find(p => String(p.id) === String(val));
                    if(prod){
                        $(this).append('<option value="'+prod.id+'" data-code2="'+(prod.code2||'')+'">'+prod.code+' - '+prod.name+'</option>');
                    }
                }
                $(this).trigger('change.select2');
            });
        }
        // destroy select2 and remove row
        if($select.data('select2')){ $select.select2('destroy'); }
        $row.remove();
    });

    // validate quantity input against max and min (stock)
    $(document).on('input change', '.item-qty', function(){
        var $this = $(this);
        var maxAttr = $this.attr('max');
        var minAttr = $this.attr('min');
        var max = (typeof maxAttr !== 'undefined') ? parseFloat(maxAttr) : null;
        var min = (typeof minAttr !== 'undefined') ? parseFloat(minAttr) : 1;
        var val = parseFloat($this.val());
        if (isNaN(val)) { return; }
        if (min !== null && !isNaN(min) && val < min) {
            $this.val(min);
            $this.addClass('is-invalid');
            return;
        }
        if (max !== null && !isNaN(max) && val > max) {
            $this.val(max);
            $this.addClass('is-invalid');
        } else {
            $this.removeClass('is-invalid');
        }
    });

    function updateAddButton(){
        var fromId = $('#id_sucursal_from').val();
        var toId = $('#id_sucursal_to').val();
        if(fromId && toId){
            $('#addRow').prop('disabled', false);
        } else {
            $('#addRow').prop('disabled', true);
        }
    }

    // al cambiar la sucursal origen, habilitar y filtrar las opciones de destino
    $('#id_sucursal_from').on('change', function(){
        var fromId = $(this).val();
        var toSelect = $('#id_sucursal_to');
        toSelect.empty();
        toSelect.append('<option value="">Seleccione...</option>');
        if(!fromId){
            toSelect.prop('disabled', true);
            // limpiar filas si origen quitado
            selectedIds.clear();
            $('#itemsTable tbody').empty();
            return;
        }
        // agregar sucursales excepto la seleccionada
        sucursales.forEach(function(s){
            if(String(s.id) !== String(fromId)){
                toSelect.append('<option value="'+s.id+'">'+s.name+'</option>');
            }
        });
        toSelect.prop('disabled', false);
        // al cambiar origen limpiamos filas (existencias dependen del origen)
        selectedIds.clear();
        $('#itemsTable tbody').empty();
        updateAddButton();
    });

    // cuando se abre el modal limpiar selects solo en modo crear
    $('#transferModal').on('show.bs.modal', function(){
        var editingId = $('#editing_transfer_id').val();
        if (editingId) {
            // apertura en modo ver/editar: no limpiar (contenido ya cargado)
            return;
        }
        // modo crear: limpiar campos
        $('#id_sucursal_from').val('');
        $('#id_sucursal_to').empty().append('<option value="">Seleccione...</option>').prop('disabled', true);
        $('#itemsTable tbody').empty();
        $('#notes').val('');
    });

    // al cambiar destino actualizar estado del boton +
    $('#id_sucursal_to').on('change', function(){ updateAddButton(); });

    $('#saveTransfer').on('click', function(){
        var id_from = $('#id_sucursal_from').val();
        var id_to = $('#id_sucursal_to').val();
        var notes = $('#notes').val();
        var editingId = $('#editing_transfer_id').val();

        if(!id_from || !id_to){
            alert('Seleccione sucursal origen y destino');
            return;
        }

        var valid = true;
        var items = [];
        $('#itemsTable tbody tr').each(function(index){
            var rowIndex = index + 1;
            var idp = $(this).find('.item-product').val();
            var qty = $(this).find('.item-qty').val();
            if(!idp){
                alert('Seleccione un producto en la fila ' + rowIndex);
                valid = false;
                return false;
            }
            var qtyFloat = parseFloat(qty);
            if(isNaN(qtyFloat) || qtyFloat < 1){
                alert('Ingrese una cantidad válida (mínimo 1) en la fila ' + rowIndex);
                valid = false;
                return false;
            }
            items.push({id_product:idp, quantity: parseInt(qty, 10)});
        });
        if(!valid) return;
        if(items.length === 0){ alert('Agregue al menos un producto'); return; }

        if (!editingId) {
            // create new
            $.ajax({
                type:'POST',
                url: '{!! route("inventory-transfers.store") !!}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_sucursal_from: id_from,
                    id_sucursal_to: id_to,
                    notes: notes,
                    items: items
                },
                success:function(id){
                    $('#transferModal').modal('hide');
                    // recargar tabla Yajra
                    if (window.LaravelDataTables && window.LaravelDataTables['inventory-transfers-table']) {
                        window.LaravelDataTables['inventory-transfers-table'].ajax.reload();
                    }
                    var pdfUrl = '{!! url("/inventory-transfers") !!}/'+id+'/pdf';
                    window.open(pdfUrl, '_blank');
                },
                error:function(xhr){ alert('Error: '+xhr.responseText); }
            });
        } else {
            // update existing transfer
            $.ajax({
                type:'POST',
                url: '{!! url("/inventory-transfers") !!}/'+editingId,
                data: {
                    _token: '{{ csrf_token() }}',
                    notes: notes,
                    items: items
                },
                success:function(resp){
                    $('#transferModal').modal('hide');
                    if (window.LaravelDataTables && window.LaravelDataTables['inventory-transfers-table']) {
                        window.LaravelDataTables['inventory-transfers-table'].ajax.reload();
                    }
                    alert('Transferencia actualizada');
                },
                error:function(xhr){ alert('Error: '+xhr.responseText); }
            });
        }
    });

    // Open existing transfer in modal for viewing/editing
    $(document).on('click', '.open-transfer', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        console.log('open-transfer clicked, id=', id);
        if(!id) { console.warn('No id on open-transfer'); return; }
        $.get('{!! url("/inventory-transfers") !!}/'+id+'?ajax=1')
            .done(function(data){
            console.log('transfer data received:', data);
            try {
                // server returns JSON when AJAX
                var t = data;
                if (!t || typeof t !== 'object') {
                    console.error('Invalid transfer payload', t);
                    alert('Respuesta inválida del servidor al cargar la transferencia');
                    return;
                }
                if (!t.items) {
                    console.warn('Transfer object has no items:', t);
                    // still continue but warn the user
                }
            $('#editing_transfer_id').val(t.id);
            // set title
            $('#transferModal .modal-title').text('Transferencia '+(t.code || ''));

            // repopulate sucursal selects (to ensure options are present) and disable them
            var fromSelect = $('#id_sucursal_from');
            var toSelect = $('#id_sucursal_to');
            fromSelect.empty().append('<option value="">Seleccione...</option>');
            toSelect.empty().append('<option value="">Seleccione...</option>');
            sucursales.forEach(function(s){
                fromSelect.append('<option value="'+s.id+'">'+s.name+'</option>');
                toSelect.append('<option value="'+s.id+'">'+s.name+'</option>');
            });
            fromSelect.val(t.id_sucursal_from).prop('disabled', true);
            toSelect.val(t.id_sucursal_to).prop('disabled', true);
            $('#addRow').prop('disabled', false);
            // populate items
            $('#itemsTable tbody').empty();
            var selIds = new Set();
            t.items.forEach(function(it){
                selIds.add(String(it.id_product));
                var prod = products.find(p => String(p.id) === String(it.id_product));
                var prodText = prod ? (prod.code+' - '+prod.name) : it.id_product;
                var $row = $('<tr>');
                var $select = $('<select class="form-select form-select-sm item-product w-100">');
                // mark option as selected so select2 shows it
                $select.append('<option selected value="'+it.id_product+'" data-code2="'+(prod?prod.code2:'')+'">'+prodText+'</option>');
                $row.append($('<td>').append($select));
                $row.append($('<td class="item-existence text-end">-</td>'));
                $row.append($('<td>').append('<input type="number" min="1" step="1" class="form-control form-control-sm item-qty text-end" value="'+(Math.round(parseFloat(it.quantity) || 0))+'" style="width:90px">'));
                $row.append($('<td>').append('<button type="button" class="btn btn-sm btn-danger removeRow">-</button>'));
                $('#itemsTable tbody').append($row);
                // init select2 for this select
                initProductSelect($select);
                // disable product select for existing items (cannot change product)
                $select.prop('disabled', true);
                if ($select.data('select2')) { $select.trigger('change.select2'); }
                // capture existence cell for this row to avoid async overwrites
                var $existCell = $row.find('.item-existence');
                // manually set stock existence cell based on origin (use closure variable)
                if (t.id_sucursal_from) {
                    (function($cell, prodId){
                            $.get('{!! route("inventory-transfers.stock") !!}', { id_product: prodId, id_sucursal: t.id_sucursal_from })
                                .done(function(resp){
                                    var q = resp && typeof resp.quantity !== 'undefined' ? parseFloat(resp.quantity) : 0;
                                    var display = (Number.isFinite(q) ? (Math.round(q * 100) / 100) : '-');
                                    var qInt = Math.floor(q);
                                    if (q <= 0) {
                                        $cell.text('Sin existencia').addClass('text-danger');
                                        $cell.closest('tr').find('.item-qty').prop('disabled', true).addClass('is-invalid');
                                        $cell.closest('tr').addClass('out-of-stock');
                                    } else {
                                        $cell.text(display).removeClass('text-danger');
                                        var $qtyInput = $cell.closest('tr').find('.item-qty');
                                        $qtyInput.prop('disabled', false).removeClass('is-invalid');
                                        $qtyInput.attr('min',1).attr('step',1).attr('max',qInt);
                                        var cur = parseInt($qtyInput.val(), 10);
                                        if (isNaN(cur) || cur < 1) { $qtyInput.val(1); cur = 1; }
                                        if (cur > qInt) { $qtyInput.val(qInt); }
                                    }
                                });
                        })($existCell, it.id_product);
                }
            });

            // initialize global selectedIds so new rows exclude already chosen products
            selectedIds = selIds;

            // configure UI depending on status and user type
            var status = t.status; // 0 pendiente, 1 aprobado, 2 cancelado, 3 lista
            // Limpia cualquier indicador previo
            $('#transferModal .modal-title .status-indicator').remove();
            if (status == 0) {
                if (currentUserType === 'SUPERVISOR') {
                    // solo vista
                    $('#addRow').hide();
                    $('#saveTransfer').hide();
                    $('#approveTransfer').hide();
                    $('#rejectTransfer').hide();
                    $('#itemsTable tbody').find('select, input').prop('disabled', true);
                } else if (currentUserType === 'ADMINISTRATIVO' || currentUserType === 'ADMINISTRADOR') {
                    // edición permitida
                    $('#addRow').show();
                    $('#saveTransfer').show();
                    $('#approveTransfer').show();
                    $('#rejectTransfer').show();
                    $('#id_sucursal_from').prop('disabled', true);
                    $('#id_sucursal_to').prop('disabled', true);
                    $('#itemsTable tbody').find('.item-qty').prop('disabled', false);
                    $('#itemsTable tbody').find('.item-product').prop('disabled', true).trigger('change.select2');
                } else {
                    // solo vista
                    $('#addRow').hide();
                    $('#saveTransfer').hide();
                    $('#approveTransfer').hide();
                    $('#rejectTransfer').hide();
                    $('#itemsTable tbody').find('select, input').prop('disabled', true);
                }
                $('#markLista').hide();
            } else if (status == 3) {
                // LISTA: solo vista, mostrar indicador visual
                $('#markLista').hide();
                $('#addRow').hide();
                $('#saveTransfer').hide();
                $('#approveTransfer').hide();
                $('#rejectTransfer').hide();
                $('#itemsTable tbody').find('select, input').prop('disabled', true);
                // Agregar indicador visual en el título
                $('#transferModal .modal-title').append('<span class="status-indicator badge bg-success ms-2">LISTA</span>');
            } else {
                // aprobado (1) o cancelado (2): solo vista
                $('#addRow').hide();
                $('#saveTransfer').hide();
                $('#approveTransfer').hide();
                $('#rejectTransfer').hide();
                // mostrar botón para marcar LISTA sólo si está aprobado y el usuario es administrativo/administrador
                if (status == 1) {
                    $('#markLista').show();
                } else {
                    $('#markLista').hide();
                }
                $('#itemsTable tbody').find('select, input').prop('disabled', true);
            }

                $('#transferModal').modal('show');
            } catch (err) {
                console.error('Error populating transfer modal:', err);
                alert('Error al procesar datos de la transferencia: '+ (err.message || err));
            }
            }).fail(function(xhr){
                console.error('Failed to load transfer', xhr.status, xhr.responseText);
                alert('Error al cargar la transferencia: '+ (xhr.responseText || xhr.status));
            });
    });

    // Approve
    $('#approveTransfer').on('click', function(){
        var id = $('#editing_transfer_id').val();
        if(!id) return;
        if(!confirm('¿Aprobar esta transferencia? Se ajustará el stock.')) return;
        $.post('{!! url("/inventory-transfers") !!}/'+id+'/approve', {_token:'{{ csrf_token() }}'})
            .done(function(){
                $('#transferModal').modal('hide');
                if (window.LaravelDataTables && window.LaravelDataTables['inventory-transfers-table']) {
                    window.LaravelDataTables['inventory-transfers-table'].ajax.reload();
                }
                alert('Transferencia aprobada');
            }).fail(function(xhr){ alert('Error: '+xhr.responseText); });
    });

    // Reject
    $('#rejectTransfer').on('click', function(){
        var id = $('#editing_transfer_id').val();
        if(!id) return;
        if(!confirm('¿Rechazar esta transferencia?')) return;
        $.post('{!! url("/inventory-transfers") !!}/'+id+'/reject', {_token:'{{ csrf_token() }}'})
            .done(function(){
                $('#transferModal').modal('hide');
                if (window.LaravelDataTables && window.LaravelDataTables['inventory-transfers-table']) {
                    window.LaravelDataTables['inventory-transfers-table'].ajax.reload();
                }
                alert('Transferencia rechazada');
            }).fail(function(xhr){ alert('Error: '+xhr.responseText); });
    });

    // Mark as LISTA (finalize: add stock to destination)
    $('#markLista').on('click', function(){
        var id = $('#editing_transfer_id').val();
        if(!id) return;
        if(!confirm('¿Marcar esta transferencia como LISTA? Se añadirá el stock a la sucursal destino.')) return;
        $.post('{!! url("/inventory-transfers") !!}/'+id+'/mark-lista', {_token:'{{ csrf_token() }}'})
            .done(function(){
                $('#transferModal').modal('hide');
                if (window.LaravelDataTables && window.LaravelDataTables['inventory-transfers-table']) {
                    window.LaravelDataTables['inventory-transfers-table'].ajax.reload();
                }
                alert('Transferencia marcada como LISTA');
            }).fail(function(xhr){ alert('Error: '+xhr.responseText); });
    });
});
</script>



@endsection
