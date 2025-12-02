
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'category'])
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
                                        <h4>{{__('Categories')}}</h4>
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
                        {{-- Yajra DataTable --}}
                        {!! $dataTable->table(['class' => 'table table-striped  table-bordered w-100', 'style' => 'font-size: 13px;'], true) !!}
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
    <!-- boostrap category model -->
    <div class="modal fade" id="category-modal" aria-hidden="true" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Add Category')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="categoryForm" name="categoryForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="name" type="text" class="form-control" id="name"  placeholder="{{__('Name')}}" title="Es obligatorio un nombre" minlength="2" maxlength="30" required onkeyup="mayus(this);"  autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('Name')}}</label>
                                <span id="nameError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <input name="description" type="text" class="form-control" id="description"  placeholder="{{__('Description')}}" title="Es obligatorio una descripcion" minlength="2" maxlength="100" onkeyup="mayus(this);" autocomplete="off">
                                <label class="form-label" for="form2Example17">{{__('Description')}}</label>
                                <span id="descriptionError" class="text-danger error-messages"></span>
                            </div>
                        </div>  
                        <div class="col-sm-offset-2 col-sm-12 text-center"><br/>
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
        $(document).ready( function () {
            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }); 
        });
        function add(){
            $('#categoryForm').trigger("reset");
            $('#modal-title').html("{{__('Add Category')}}");
            $('.error-messages').html('');
            $('#category-modal').modal('show');
            $('#id').val('');
        }       
        function editFunc(id){
            $.ajax({
                type:"POST",
                url: "{{ url('editCategory') }}",
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    $('#modal-title').html("{{__('Edit Category')}}");
                    $('.error-messages').html('');
                    $('#category-modal').modal('show');
                    $('#id').val(res.id);
                    $('#name').val(res.name);
                    $('#description').val(res.description);
                    $('#categories-table').DataTable().ajax.reload();
                }
            });
        }  
        function deleteFuncAp(id){
            var id = id;
            Swal.fire({
                title: "{{__('You are sure?')}}",
                text: "{{__('your record will be deleted')}}!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                cancelButtonText: "{{__('Cancel')}}",
                confirmButtonText: "{{__('Yes, delete')}}!"
                }).then((result) => {
                if (result.isConfirmed) {
                    deleteFunc(id);
                }
            });
        }
        function deleteFunc(id){
            var id = id;
            // ajax
            $.ajax({
                type:"POST",
                url: "{{ url('deleteCategory') }}",
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    $('#categories-table').DataTable().ajax.reload();
                    Swal.fire({
                        title: "{{__('Removed')}}!",
                        text: "{{__('Your registration was deleted')}}.",
                        icon: "success"
                    });
                }
            });
        }
        $('#categoryForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            $.ajax({
                type:'POST',
                url: "{{ url('storeCategory')}}",
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#category-modal").modal('hide');
                    $('#categories-table').DataTable().ajax.reload();
                    $("#btn-save").html('Enviar');
                    $("#btn-save"). attr("disabled", false);
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
                        $('#descriptionError').html(error.responseJSON.errors.description);
                    } 
                }    
            });
        });
        function micheckbox(id){
            console.log('entro');
            //Verifico el estado del checkbox, si esta seleccionado sera igual a 1 de lo contrario sera igual a 0
            var id = id; 
            $.ajax({
                type: "GET",
                dataType: "json",
                //url: '/StatusNoticia',
                url: "{{ url('statusCategory') }}",
                data: {'id': id},
                success: function(data){
                    console.log(data.status);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "{{__('Modified status')}}",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('.cambia'+id+'').html('');
                    if (data.status == '1') {
                        $('.cambia'+id+'').append('<i style="margin: -5px !important; padding: 0px !important;"  class="fa-solid fa-toggle-on text-success fs-4"></i>');
                    } else {
                        $('.cambia'+id+'').append('<i style="margin: -5px !important; padding: 0px !important;"  class="fa-solid fa-toggle-off text-danger fs-4"></i>');
                    }
                }
            });
        }
    </script>
@endsection

