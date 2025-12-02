
@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'stock'])
@section('content')
    <div class="container-fluid ">
        <div class="row ">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row">
                                    <div class="col-3 col-sm-3">
                                        <h4>{{__('Stock')}}</h4>
                                    </div>
                                    <div class="col-9 col-sm-9">
                                        <h5>{{$product->code}} {{$product->name}}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="card-body" >
                            <div class="tabla table-responsive" style="font-size: 13px;"> 
                                <table class="table table-striped" id="ajax-crud-datatableStock" style="font-size: 13px; width: 98% !important;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{__('Date')}}</th>
                                            <th class="text-center">{{__('Usuario')}}</th>
                                            <th class="text-center">{{__('Description')}}</th>
                                            <th class="text-center">{{__('Entrada')}}</th>
                                            <th class="text-center">{{__('Salida')}}</th>
                                            <th class="text-center">{{__('Existencia')}}</th>
                                            <th class="text-center">{{__('Detal')}}</th> <!-- nueva columna -->
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
    @include('footer')
@endsection  
@section('scripts')
    <script type="text/javascript">
         $(document).ready( function () {
            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }); 
            $('#ajax-crud-datatableStock').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('ajax-crud-datatableStock', ['id_product' => $product->id]) }}",
                columns: [
                    {
                        data: 'created_at', // Use the raw created_at data
                        name: 'created_at',
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                // Format for display and filtering
                                if (data) {
                                    const date = new Date(data);
                                    const day = String(date.getDate()).padStart(2, '0');
                                    const month = String(date.getMonth() + 1).padStart(2, '0');
                                    const year = date.getFullYear();
                                    const hours = String(date.getHours()).padStart(2, '0');
                                    const minutes = String(date.getMinutes()).padStart(2, '0');
                                    const seconds = String(date.getSeconds()).padStart(2, '0');
                                    return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
                                }
                                return '';
                            }
                            // For sorting and other types, return the raw data
                            return data;
                        }
                    },
                    { data: 'name', name: 'name' },
                    { data: 'description', name: 'description' },
                    { data: 'addition', name: 'addition' },
                    { data: 'subtraction', name: 'subtraction' },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'detal', name: 'detal' }, // <-- nueva columna en JS
                ],
                drawCallback: function(settings) {
                    centerTableContent()
                },
                order: [[0, 'desc']], // Changed to 'desc' as per your controller's default
                lengthMenu: [
                    [20, 30, 40, 50, -1],
                    ['20', '30', '40', '50', 'Todos']
                ],
                "oLanguage": {
                    "sProcessing": "{{__('Processing')}}...",
                    "sLengthMenu": "{{__('Show')}} <select>" +
                        '<option value="20" selected>20</option>' +
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
                $('#ajax-crud-datatableStock tbody tr td:nth-child(1)').addClass('text-center');
                $('#ajax-crud-datatableStock tbody tr td:nth-child(4)').addClass('text-end').css('padding-right', '50px');
                $('#ajax-crud-datatableStock tbody tr td:nth-child(5)').addClass('text-end').css('padding-right', '50px');
                $('#ajax-crud-datatableStock tbody tr td:nth-child(6)').addClass('text-end').css('padding-right', '50px');
                $('#ajax-crud-datatableStock tbody tr td:nth-child(7)').addClass('text-end').css('padding-right', '50px'); // estilo para existencia detal
            }
        });       
    </script>
@endsection

