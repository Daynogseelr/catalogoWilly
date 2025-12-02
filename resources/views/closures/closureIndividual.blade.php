@extends('app', ['page' => __('Cierres Individuales'), 'pageSlug' => 'individual-closure'])

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12 mb-lg-0 mb-4">
            <div class="card z-index-2 h-100">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <div class="row">
                        <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                            <div class="row">
                                <div class="col-5">
                                    <h4>{{__('Cierres Individuales')}}</h4>
                                </div>
                                <div class="col-5">
                                    @if(in_array(auth()->user()->type, ['SUPERVISOR', 'ADMINISTRATIVO', 'EMPRESA', 'ADMINISTRADOR']))
                                        <div class="form-group mb-0">
                                            <select class="form-select" name="user_id" id="user-select-field"
                                                    data-placeholder="{{ __('Seleccionar usuario') }}">
                                                <option value="TODOS">{{ __('TODOS') }}</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}"
                                                            @if (isset($selected_user_id) && $selected_user_id == $user->id) selected @endif>
                                                        {{ $user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-2 text-end">
                                    <a class="btn btn-danger2" onClick="addIndividualClosure()" href="javascript:void(0)">
                                        <i class="fa-solid fa-circle-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                </div>
                <div class="card-body p-3">
                    <div class="card-body">
                        {!! $dataTable->table(['class' => 'table table-striped', 'style' => 'font-size:13px;width:98%!important;'], true) !!}
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
        $(function() {
            // Select2 para el select de usuario
            $('#user-select-field').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: $(this).data('placeholder'),
                dropdownCssClass: "color",
                selectionCssClass: "form-select",
                language: "es"
            });

            // Espera a que la tabla esté inicializada
            if (window.LaravelDataTables && window.LaravelDataTables['individual-closure-table']) {
                window.LaravelDataTables['individual-closure-table'].on('preXhr.dt', function (e, settings, data) {
                    data.user_id = $('#user-select-field').val();
                });

                $('#user-select-field').on('change', function() {
                    window.LaravelDataTables['individual-closure-table'].ajax.reload();
                });
            }
        });

        function addIndividualClosure() {
            Swal.fire({
                title: "{{__('¿Estás seguro de hacer el cierre individual?')}}",
                text: "{{__('¡Se generará el cierre individual!')}}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "{{__('Sí, ¡generar cierre!')}}",
                cancelButtonText: "{{__('Cancelar')}}"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('storeIndividualClosure') }}",
                        data: {
                            user_id: $('#user-select-field').val(),
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: (data) => {
                            if (data != 'mal') {
                                window.LaravelDataTables['individual-closure-table'].ajax.reload();
                                Swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: "{{__('Cierre guardado exitosamente')}}",
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                const pdfLink = "{{ route('pdfClosure', ':id') }}".replace(':id', data);
                                window.open(pdfLink, '_blank');
                            } else {
                                Swal.fire({
                                    position: "top-end",
                                    icon: "info",
                                    title: "{{__('No hay cambios para nuevo cierre')}}",
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        },
                        error: function(error) {
                            Swal.fire({
                                position: "top-end",
                                icon: "error",
                                title: "{{__('Hubo un error al generar el cierre')}}",
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection