@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'memberships'])
@section('content')
    <div class="container-fluid py-1">
        <div class="row mt-4">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-10 col-sm-11">
                                        <h4>{{__('Memberships')}}</h4>
                                    </div>
                                    <div class="col-2 col-sm-1">
                                        @if (auth()->user()->type == 'EMPRESA')
                                            <a class="btn btn-primary" onClick="add()" href="javascript:void(0)">
                                                <i class="fa-solid fa-circle-plus"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="card-body">
                            <div class="tabla table-responsive" style="font-size: 13px;">
                                <table class="table table-striped" id="ajax-crud-datatable"
                                    style="font-size: 13px; width: 98% !important;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{__('Company')}}</th>
                                            <th class="text-center">{{__('Date')}}</th>
                                            <th class="text-center">{{__('Amount')}}</th>
                                            <th class="text-center">{{__('Status')}}</th>
                                            <th class="text-center">{{__('Action')}}</th>
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
    <!-- boostrap payment model -->
    <div class="modal fade" id="payment-modal" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Payment record')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="paymentForm" name="paymentForm" class="form-horizontal" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label for="type">{{__('Payment type')}}:</label>
                                    <select class="form-select" name="type" id="type">
                                        <option value="">{{__('Select a payment type')}}</option>
                                        <option value="TRANSFERENCIA">{{__('Wire transfer')}}</option>
                                        <option value="PAGO MOVIL">{{__('Mobile Payment')}}</option>
                                        <option value="OTRO">{{__('Other')}}</option>
                                    </select>
                                </div>
                                <span id="typeError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <div class="form-group">
                                    <label for="bank">{{__('Bank to which the operation was carried out')}}:</label>
                                    <select class="form-select" name="bank" id="bank">
                                        <option value="">{{__('Select a bank')}}</option>
                                        <option value="BANCO NACIONAL DE CREDITO">Banco Nacional de Credito</option>
                                        <option value="BANCO DE VENEZUELA">Banco de Venezuela</option>
                                        <option value="BANCARIBE">Bancaribe</option>
                                        <option value="BANCO PROVINCIAL">Banco Provincial</option>
                                        <option value="BANO MUNDIAL">Banco Mundial</option>
                                        <option value="BANCO MERCANTIL">Banco Mercantil</option>
                                        <option value="BANCO EXTERIOR">Banco Exterior</option>
                                        <option value="OTRO">{{__('Other')}}</option>
                                    </select>
                                </div>
                                <span id="bankError" class="text-danger error-messages"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <div class="form-group">
                                    <label class="form-label" for="form2Example17">{{__('Reference')}}</label>
                                    <input name="reference" type="text" class="form-control" id="reference" placeholder="{{__('Reference')}}"
                                    title="Es obligatorio un nombre" minlength="1" maxlength="30" required>
                                    <span id="referenceError" class="text-danger error-messages"></span>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <div class="form-group">
                                    <label class="form-label" for="form2Example17">{{__('Amount')}}</label>
                                    <input name="amount" type="text" class="form-control" id="amount"  placeholder="{{__('Amount')}}" title="Es obligatorio un precio" minlength="1" maxlength="10" required onkeypress='return validaMonto(event)' onkeyup="mayus(this);"  autocomplete="off"> 
                                    <span id="amountError" class="text-danger error-messages"></span>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 form-outline">
                                <div class="form-group">
                                    <label class="form-label" for="form2Example17">{{__('Payment Capture')}}</label>
                                    <input name="capture" type="file" class="form-control" id="capture"  accept="image/*" title="Es obligatorio una Imagen" >
                                    <span id="captureError" class="text-danger error-messages"></span>
                                </div>
                            </div>
                            <div class="col-sm-offset-2 col-sm-12 text-center"><br/>
                                <button type="submit" class="btn btn-primary" id="btn-save">{{__('Process')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    <!-- boostrap verpayment model -->
    <div class="modal fade" id="vermembership-modal" aria-hidden="true">
        <div class="modal-dialog "> 
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">{{__('Payment record')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <label for="vertype">{{__('Payment type')}}:</label>
                                <input name="vertype" type="text" class="form-control" id="vertype" disabled>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <div class="form-group">
                                <label for="verbank">{{__('Bank to which the operation was carried out')}}:</label>
                                <input name="verbank" type="text" class="form-control" id="verbank" disabled>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <div class="form-group">
                                <label class="form-label" for="verreference">{{__('Reference')}}</label>
                                <input name="verreference" type="text" class="form-control" id="verreference" disabled>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <div class="form-group">
                                <label class="form-label" for="veramount">{{__('Amount')}}</label>
                                <input name="veramount" type="text" class="form-control" id="veramount" disabled>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 form-outline">
                            <div class="form-group">
                                <label class="form-label" for="vercapture">{{__('Payment Capture')}}</label>
                                <div  id="vercapture" >
                                </div>
                            </div>
                        </div>
                        @if (auth()->user()->type == 'ADMINISTRADOR')
                            <div class="col-sm-offset-2 col-sm-12 text-center" id="btnMembership"><br />
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- end bootstrap model -->
    @include('footer')
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#ajax-crud-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('ajax-crud-datatableMembership') }}",
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    { data: 'status', render: function(data, type, row){
                        if (data == 'PENDIENTE') {
                            return '<td class="text-primary"><span class="text-primary">{{__('PENDING')}}</span></td>';
                        } else if (data == 'VERIFICADO') {
                            return '<td class="text-success"><span class="text-success">{{__('VERIFIED')}}</span></td>';
                        } else if (data == 'RECHAZADO') {
                            return '<td class="text-danger"><span class="text-danger">{{__('REFUSED')}}</span></td>';
                        } 
                    }},
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ],
                drawCallback: function(settings) {
                    centerTableContent()
                },
                order: [
                    [1, 'desc']
                ],
                "oLanguage": {
                    "sProcessing": "{{__('Processing')}}...",
                    "sLengthMenu": "{{__('Show')}} <select>" +
                        '<option value="10">10</option>' +
                        '<option value="20">20</option>' +
                        '<option value="30">30</option>' +
                        '<option value="40">40</option>' +
                        '<option value="50">50</option>' +
                        "<option value='-1'>{{__('All')}}</option>" +
                        "</select> {{__('Registers')}}",
                    "sZeroRecords": "{{__('No results found')}}",
                    "sEmptyTable": "{{__('No data available in this table')}}",
                    "sInfo": "{{__('Showing of')}} (_START_ {{__('to the')}} _END_) {{__('of a total of')}} _TOTAL_ {{__('Registers')}}",
                    "sInfoEmpty": "{{__('Showing 0 to 0 of a total of 0 records')}}",
                    "sInfoFiltered": "({{__('of')}} _MAX_ {{__('existents')}})",
                    "sInfoPostFix": "",
                    "sSearch": "{{__('Search')}}:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "{{__('Please wait - loading')}}...",
                    "oPaginate": {
                        "sFirst": "{{__('First')}}",
                        "sLast": "{{__('Last')}}",
                        "sNext": "{{__('Next')}}",
                        "sPrevious": "{{__('Previous')}}"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                }
            });
            function centerTableContent() {
                $('#ajax-crud-datatable tbody tr td:nth-child(1)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(2)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(3)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(4)').addClass('text-center');
                $('#ajax-crud-datatable tbody tr td:nth-child(5)').addClass('text-center ');
            }
        });
        function add() {
            $('#paymentForm').trigger("reset");
            $('.error-messages').html('');
            $('#payment-modal').modal('show');
        }
        $('#paymentForm').submit(function(e) {
            e.preventDefault();
            $('.error-messages').html('');
            var formData = new FormData(this);
            $.ajax({
                type:'POST',
                url: "{{ url('playmentMembership')}}",
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                success: (data) => {
                    $("#payment-modal").modal('hide');
                    var oTable = $('#ajax-crud-datatable').dataTable();
                    oTable.fnDraw(false);
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "Registro guardado exitosamente",
                        showConfirmButton: false,
                        timer: 1500
                    }); 
                },
                error: function(error) {
                    if (error) {
                        console.log(error.responseJSON.errors);
                        console.log(error);
                        $('#typeError').html(error.responseJSON.errors.type);
                        $('#bankError').html(error.responseJSON.errors.bank);
                        $('#referenceError').html(error.responseJSON.errors.reference);
                        $('#amountError').html(error.responseJSON.errors.amount);
                        $('#captureError').html(error.responseJSON.errors.capture);
                    } 
                }    
            });
        });
        function verMembership(id) {
            $.ajax({
                type: "POST",
                url: "{{ url('verMembership') }}",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    console.log(res)
                    $('#vermembership-modal').modal('show');
                    $('#btnMembership').html('');
                    $('#vertype').val(res.type);
                    $('#verbank').val(res.bank);
                    $('#verreference').val(res.reference);
                    $('#veramount').val(res.amount);
                    $('#vercapture').html(
                        '<img  src="/payments/'+res.capture+'"'+
                        'class="card-product-img-top mx-auto"'+
                        'style="height: 150px; width: 150px;display: block;  margin-top: 10px;"'+
                        'alt="capture">'
                    );
                    if (res.status == 'PENDIENTE') {
                        var statusV = "'VERIFICADO'";
                        var statusR = "'RECHAZADO'";
                        $('#btnMembership').html(
                            '<a class="btn btn-primary" style="margin-right: 20px !important;" onClick="statusMembership(' +
                            id + ','+statusV+')" >{{__('Verify')}}</a>' +
                            '<a class="btn btn-danger" style="margin-right: 20px !important;" onClick="statusMembership(' +
                            id + ','+statusR+')" >{{__('Decline')}}</a>' 
                        );
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
        function statusMembership(id, status) {
            Swal.fire({
                title: "{{__('Are you sure you want to change the status?')}}",
                text: "{{__('The status will be')}} " + {{__('status')}} ,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "{{__('Yeah!')}}",
                cancelButtonText: "{{__('Cancel')}}"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "{{ url('statusMembership') }}",
                        data: {
                            'id': id,
                            'status': status
                        },
                        dataType: 'json',
                        success: function(res) {
                            console.log(res)
                            $('#vermembership-modal').modal('hide');
                            var oTable = $('#ajax-crud-datatable').dataTable();
                            oTable.fnDraw(false);
                            Swal.fire({
                                title: "{{__('Status change')}}!",
                                text: "{{__('The status has been updated')}}",
                                confirmButtonText: "{{__('Okay')}}",
                                icon: "success"
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection