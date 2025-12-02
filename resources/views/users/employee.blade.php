@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'userEmployee'])
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
                                        <h4>{{ __('Employees') }}</h4>
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
    <!-- boostrap company model -->
    <div class="modal fade" id="company-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{ __('Add Employee') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="companyForm" name="companyForm" class="form-horizontal"
                        method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <div class="col-md-8 col-sm-8 form-outline">
                                <input name="name" type="text" class="form-control" id="name"
                                    placeholder="{{ __('Name') }}" title="Es obligatorio un Nombre" minlength="2"
                                    maxlength="20" required onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Name') }}</label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-4 col-sm-4 form-outline">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-4 col-sm-4" style="padding-right:0">
                                            <select class="form-select required" name="nationality">
                                                <option value="V">V</option>
                                                <option value="E">E</option>
                                            </select>
                                        </div>
                                        <div class="col-8 col-sm-8" style="padding-left:0">
                                            <input name="ci" type="text" class="form-control" id="ci"
                                                placeholder="{{ __('Identification Document') }}"
                                                title="Es obligatorio una Cédula" minlength="7" maxlength="10" required
                                                onkeypress='return validaNumericos(event)' onkeyup="mayus(this);"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <label class="form-label"
                                        for="form2Example17">{{ __('Identification Document') }}</label>
                                    <span id="ciError" class="text-danger error-messages"></span>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4 form-outline mb-2">
                                <input name="phone" type="text" class="form-control" id="phone"
                                    placeholder="{{ __('Phone') }}" title="Es obligatorio un Teléfono" onkeypress='return validaNumericos(event)' autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Phone') }}</label>
                                <span id="phoneError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-8 col-sm-8 form-outline mb-2">
                                <input name="direction" type="text" class="form-control" id="direction"
                                    placeholder="{{ __('Direction') }}" title="Es obligatorio un Dirección"
                                    onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{ __('Direction') }}</label>
                                <span id="directionError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-6 form-outline mb-2">
                                <select name="sucursals[]" id="sucursals"
                                    class="form-control js-example-basic-multiple-sucursals" multiple="multiple"
                                    style="width:100%;">
                                    @isset($sucursals)
                                        @foreach ($sucursals as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->rif ?? '' }})
                                            </option>
                                        @endforeach
                                    @endisset
                                </select>
                                <label class="form-label">{{ __('Sucursales') }}</label>
                                <span id="sucursalsError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-6 col-sm-6 form-outline">
                                <select class="js-example-basic-multiple js-example-basic-multiple-price form-select"
                                    data-placeholder="Seleccione los precios" name="price[]" multiple="multiple">
                                    <option value="detal">Precio Detal</option>
                                    <option value="price">Precio 1</option>
                                    <option value="price2">Precio 2</option>
                                    <option value="price3">Precio 3</option>
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Precios') }} con que
                                    trabajara</label>
                                <span id="priceError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-4 col-sm-12 form-outline mb-2">
                                <select class="form-select" name="type" id="type">
                                    <option value="EMPLEADO">{{ __('EMPLEADO') }}</option>
                                    <option value="SUPERVISOR">{{ __('SUPERVISOR') }}</option>
                                    <option value="ADMINISTRATIVO">{{ __('ADMINISTRATIVO') }}</option>
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Type') }}</label>
                                <span id="phoneError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-4 col-sm-12 form-outline mb-2">
                                <input name="percent" type="text" class="form-control" id="percent"
                                    placeholder="{{ __('Porcentaje Maximo de Descuento en Ventas') }}"
                                    title="Es obligatorio un descuento" minlength="1" maxlength="10" required
                                    onkeypress='return validaMonto(event)'>
                                <label class="form-label"
                                    for="form2Example17">{{ __('% Maximo de Descuento en Ventas') }}</label>
                                <span id="percentError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-4 col-sm-12 form-outline mb-2">
                                <select class="form-select" name="smallBox" id="smallBox">
                                    <option value="0">{{ __('NO') }}</option>
                                    <option value="1">{{ __('SI') }}</option>
                                </select>
                                <label class="form-label" for="form2Example17">{{ __('Usa Caja Chica') }}</label>
                                <span id="phoneError" class="text-danger error-messages"></span>
                            </div>

                            <section class="mb-3">
                                <div class="row justify-content-center">
                                    <div class="col-12 col-md-8 col-lg-6">
                                        <div class="card shadow-sm border-primary mx-auto">
                                            <div class="card-header bg-primary text-white py-2 px-3 text-center">
                                                <i class="fa fa-user-lock me-2"></i> {{ __('Datos de acceso') }}
                                            </div>
                                            <div class="card-body py-3">
                                                <div class="mb-3">
                                                    <input name="user" type="text"
                                                        class="form-control form-control-sm" id="user"
                                                        placeholder="Usuario" title="Es obligatorio un Usuario"
                                                        minlength="3" maxlength="100" required autocomplete="off"
                                                        onkeyup="mayus(this);">
                                                    <label class="form-label" for="user">{{ __('Usuario') }}</label>
                                                    <span id="userError" class="text-danger error-messages"></span>
                                                </div>
                                                <div class="mb-2">
                                                    <input name="password" type="password"
                                                        class="form-control form-control-sm" id="password"
                                                        placeholder="{{ __('Password') }}"
                                                        title="Es obligatorio una contraseña" minlength="4"
                                                        maxlength="20" required>
                                                    <label class="form-label" for="password">{{ __('Password') }}</label>
                                                    <span id="passwordError" class="text-danger error-messages"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
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
    {!! $dataTable->scripts() !!}
    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#single-select-field').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                dropdownCssClass: "color",
                selectionCssClass: "form-select",
                dropdownParent: $('#company-modal .modal-body'),
                language: "es"
            });
            $('.js-example-basic-multiple-price').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                closeOnSelect: false,
                selectionCssClass: "form-select",
                dropdownParent: $('#company-modal .modal-body'),
                language: "es"
            });
            $('.js-example-basic-multiple-sucursals').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: 'Seleccione sucursales',
                closeOnSelect: false,
                selectionCssClass: "form-select",
                dropdownParent: $('#company-modal .modal-body'),
                language: "es"
            });
        });

        function add() {
            $('#companyForm').trigger("reset");
            $('#modal-title').html("{{ __('Add Employee') }}");
            $('.error-messages').html('');
            $('#company-modal').modal('show');
            $('#id').val('');
            $("#single-select-field").val('');
            $('.js-example-basic-multiple-sucursals').val('').trigger('change');
            $('.js-example-basic-multiple-price').val('').trigger('change');
        }

        function editFunc(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('editEmployee') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    $('#modal-title').html("{{ __('Edit Employee') }}");
                    $('.error-messages').html('');
                    $('#company-modal').modal('show');
                    $('#id').val(res.res.id);
                    $('#name').val(res.res.name);
                    $('#ci').val(res.res.ci);
                    $('#type').val(res.res.type);
                    $('#phone').val(res.res.phone);
                    $('#percent').val(res.res.percent);
                    $('#smallBox').val(res.res.smallBox);
                    $('#direction').val(res.res.direction);
                    $('#user').val(res.res.user);
                    $('#password').val('PASSWORD');
                    // --- Aquí armamos el array de precios seleccionados ---
                    var selectedPrices = [];
                    if (res.res.detal == 1) selectedPrices.push('detal');
                    if (res.res.price == 1) selectedPrices.push('price');
                    if (res.res.price2 == 1) selectedPrices.push('price2');
                    if (res.res.price3 == 1) selectedPrices.push('price3');
                    $('.js-example-basic-multiple-price').val(selectedPrices).trigger('change');
                    if (res.sucursals) {
                        $('.js-example-basic-multiple-sucursals').val(res.sucursals).trigger('change');
                    } else {
                        $('.js-example-basic-multiple-sucursals').val([]).trigger('change');
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
        $('#companyForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: "{{ url('storeEmployee') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: (data) => {
                    console.log(data);
                    $("#company-modal").modal('hide');
                    $('#employee-table').DataTable().ajax.reload();
                    $("#btn-save").html('Enviar');
                    $("#btn-save").attr("disabled", false);
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
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#nameError').html(error.responseJSON.errors.name);
                        $('#ciError').html(error.responseJSON.errors.ci);
                        $('#phoneError').html(error.responseJSON.errors.phone);
                        $('#percentError').html(error.responseJSON.errors.percent);
                        $('#directionError').html(error.responseJSON.errors.direction);
                        $('#userError').html(error.responseJSON.errors.user);
                        $('#passwordError').html(error.responseJSON.errors.password);

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
                url: "{{ url('statusEmployee') }}",
                data: {
                    'id': id
                },
                success: function(data) {
                    console.log(data.status);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{ __('Modified status') }}",
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
