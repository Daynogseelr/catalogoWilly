@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'userClient'])
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-10 col-sm-11">
                                        <h4>{{__('Clients')}}</h4>
                                    </div>
                                    <div class="col-2 col-sm-1">
                                        <a class="btn btn-danger2" onClick="add()" href="javascript:void(0)">
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
    <!-- boostrap Client model -->
    <div class="modal fade" id="company-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Add Client')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="companyForm" name="companyForm" class="form-horizontal"
                        method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="name" type="text" class="form-control" id="name"
                                    placeholder="{{__('Name')}}" title="Es obligatorio un nombre" minlength="2" maxlength="20"
                                    required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('Name')}}</label>
                                <span id="nameError" class="text-danger error-messages"></span>
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
                                                placeholder="{{__('Identification Document')}}" title="Es obligatorio una cedula" minlength="7"
                                                maxlength="10" required onkeypress='return validaNumericos(event)'
                                                onkeyup="mayus(this);" autocomplete="off">
                                        </div>
                                    </div>
                                    <label class="form-label" for="form2Example17">{{__('Identification Document')}}</label>
                                    <span id="ciError" class="text-danger error-messages"></span>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 form-outline mb-2">
                                <input name="phone" type="text" class="form-control" id="phone"
                                    placeholder="{{__('Phone')}}" title="Es obligatorio un telefono" minlength="9"
                                    maxlength="15" onkeypress='return validaNumericos(event)'
                                    autocomplete="off">
                                <label class="form-label" for="form2Example17"> {{__('Phone')}}</label>
                                <span id="phoneError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline mb-2">
                                <input name="direction" type="text" class="form-control" id="direction"
                                    placeholder="{{__('Direction')}}" title="Es obligatorio un direccion" minlength="3"
                                    maxlength="250" onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17"> {{__('Direction')}}</label>
                                <span id="directionError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline mb-2">
                                <input name="email" type="text" class="form-control" id="email"
                                    placeholder="{{__('Email')}}" title="Es obligatorio un correo" minlength="5"
                                    maxlength="200" autocomplete="off" onkeyup="mayus(this);">
                                <label class="form-label" for="form2Example17">{{__('Email')}}</label>
                                <span id="emailError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline mb-2">
                                <input name="discount" type="text" class="form-control" id="discount"
                                    placeholder="Descuento" title="Es obligatorio un descuento" minlength="1"
                                    maxlength="10" required onkeypress='return validaMonto(event)'>
                                <label class="form-label" for="form2Example17">{{__('Discount')}} %</label>
                                <span id="discountError" class="text-danger error-messages"></span>
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-12 text-center"><br />
                            <button type="submit" class="btn btn-primary" id="btn-save">{{__('Send')}}</button>
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
        });
        function add() {
            $('#companyForm').trigger("reset");
            $('#modal-title').html("{{__('Add Client')}}");
            $('.error-messages').html('');
            $('#company-modal').modal('show');
            $('#id').val('');
        }
        function editFunc(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('editClient') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    $('#modal-title').html("{{__('Edit Client')}}");
                    $('.error-messages').html('');
                    $('#company-modal').modal('show');
                    $('#id').val(res.id);
                    $('#name').val(res.name);
                    $('#last_name').val(res.last_name);
                    $('#ci').val(res.ci);
                    $('#phone').val(res.phone);
                    $('#discount').val(res.discount);
                    $('#direction').val(res.direction);
                    $('#email').val(res.email);
                }
            });
        }
        $('#companyForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: "{{ url('storeClient') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#company-modal").modal('hide');
                    $('#client-table').DataTable().ajax.reload();
                    $("#btn-save").html('Enviar');
                    $("#btn-save").attr("disabled", false);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{__('Log saved successfully')}}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#nameError').html(error.responseJSON.errors.name);
                        $('#last_nameError').html(error.responseJSON.errors.last_name);
                        $('#ciError').html(error.responseJSON.errors.ci);
                        $('#phoneError').html(error.responseJSON.errors.phone);
                        $('#directionError').html(error.responseJSON.errors.direction);
                        $('#discountError').html(error.responseJSON.errors.discount);
                        $('#emailError').html(error.responseJSON.errors.email);
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
                url: "{{ url('statusClient') }}",
                data: {
                    'id': id
                },
                success: function(data) {
                    console.log(data.status);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{__('Modified status')}}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('.cambia' + id + '').html('');
                    if (data.status == '1') {
                        $('.cambia' + id + '').append(
                        '<i class="fa-solid fa-toggle-on text-success fs-4"></i>');
                    } else {
                        $('.cambia' + id + '').append(
                        '<i class="fa-solid fa-toggle-off text-danger fs-4"></i>');
                    }
                }
            });
        }
    </script>
@endsection
