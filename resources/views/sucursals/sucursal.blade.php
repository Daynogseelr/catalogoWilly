@extends('app', ['page' => __('Sucursales'), 'pageSlug' => 'sucursals'])

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header pb-0 pt-3 bg-transparent">
                <div class="row">
                    <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                        <div class="row">
                            <div class="col-10 col-sm-11">
                                <h4>{{ __('Sucursales') }}</h4>
                            </div>
                            <div class="col-2 col-sm-1">
                                <a href="#" id="btn-new-sucursal" class="btn btn-danger2 btn-sm">
                                    <i class="fa-solid fa-circle-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
            </div>
            <div class="card-body">
                {!! $dataTable->table(['class' => 'table table-striped table-hover'], true) !!}
            </div>
        </div>
    </div>

    <!-- Modal editar/crear -->
    <div class="modal fade" id="sucursalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="sucursalForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sucursalModalTitle">Sucursal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" id="sucursal_id" name="id" value="">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="name" id="sucursal_name" class="form-control"  onkeyup="mayus(this);" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RIF</label>
                                <input type="text" name="rif" id="sucursal_rif" class="form-control"  onkeyup="mayus(this);" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <input type="text" name="state" id="sucursal_state" class="form-control"  onkeyup="mayus(this);">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="city" id="sucursal_city" class="form-control"  onkeyup="mayus(this);">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Zona Postal</label>
                                <input type="text" name="postal_zone" id="sucursal_postal_zone" class="form-control"  onkeyup="mayus(this);">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="direction" id="sucursal_direction" class="form-control"  onkeyup="mayus(this);">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">% (percent)</label>
                                <input type="number" step="0.01" name="percent" id="sucursal_percent"
                                    class="form-control"  onkeyup="mayus(this);">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado </label>
                                <select name="status" id="sucursal_status" class="form-control">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div id="sucursalFormErrors" class="mt-2 text-danger" style="display:none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="sucursalSaveBtn">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    {!! $dataTable->scripts() !!}
    <script>
        $(function() {
            const table = $('#sucursal-table').DataTable();

            // Abrir modal para crear
            $('#btn-new-sucursal').on('click', function(e) {
                e.preventDefault();
                $('#sucursalForm')[0].reset();
                $('#sucursal_id').val('');
                $('#sucursalModalTitle').text('Nueva Sucursal');
                $('#sucursalFormErrors').hide().text('');
                new bootstrap.Modal(document.getElementById('sucursalModal')).show();
            });

            // Abrir modal para editar (botón desde DataTable)
            $(document).on('click', '.btn-edit-sucursal', function() {
                let id = $(this).data('id');
                $('#sucursalFormErrors').hide().text('');
                $.getJSON('/sucursals/' + id, function(data) {
                    $('#sucursalForm')[0].reset();
                    $('#sucursal_id').val(data.id);
                    $('#sucursal_name').val(data.name);
                    $('#sucursal_rif').val(data.rif);
                    $('#sucursal_state').val(data.state);
                    $('#sucursal_city').val(data.city);
                    $('#sucursal_postal_zone').val(data.postal_zone);
                    $('#sucursal_direction').val(data.direction);
                    $('#sucursal_percent').val(data.percent);
                    $('#sucursal_status').val(data.status);
                    $('#sucursalModalTitle').text('Editar Sucursal');
                    new bootstrap.Modal(document.getElementById('sucursalModal')).show();
                }).fail(function() {
                    alert('Error al cargar sucursal');
                });
            });

            // Guardar (crear o actualizar) via AJAX
            $('#sucursalForm').on('submit', function(e) {
                e.preventDefault();
                let id = $('#sucursal_id').val();
                let url = id ? '/sucursals/' + id : '/sucursals';
                let method = id ? 'PUT' : 'POST';
                let formData = $(this).serialize();
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData + '&_method=' + method + '&_token={{ csrf_token() }}',
                    success: function(res) {
                        $('#sucursalModal').modal && $('#sucursalModal').modal(
                        'hide'); // bootstrap v4 fallback
                        bootstrap.Modal.getInstance(document.getElementById('sucursalModal'))
                            ?.hide();
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        let msg = 'Error';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' | ');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        $('#sucursalFormErrors').show().text(msg);
                    }
                });
            });

        });
    </script>
@endsection
