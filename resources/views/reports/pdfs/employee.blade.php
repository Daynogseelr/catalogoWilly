@extends('app', ['page' => __('Rendimiento de Ventas (Tabla)'), 'pageSlug' => 'employeePDF'])

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-10 col-sm-11"> {{-- Adjusted column size --}}
                                <h4 class="card-title">{{__('Reportes PDF de Empleados')}}</h4>
                            </div>
                            <div class="col-2 col-sm-1 d-flex justify-content-end"> {{-- Adjusted column size and alignment --}}
                                <a style="padding: 10px 10px; color:white;" id="generatePdfBtn" target="_blank" data-toggle="tooltip" data-original-title="Generate PDF" class="btn btn-info">
                                    <i class="fa-regular fa-file-pdf"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="seller_filter">{{__('Seller')}}:</label>
                                    <select class="form-control" id="seller_filter">
                                        <option value="TODOS">{{__('All Sellers')}}</option>
                                        @foreach($sellers as $seller)
                                            <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_date_filter">{{__('From Date')}}:</label>
                                    <input type="date" class="form-control" id="start_date_filter">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_date_filter">{{__('To Date')}}:</label>
                                    <input type="date" class="form-control" id="end_date_filter">
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="tabla table-responsive" style="font-size: 12px;">
                                <table class="table table-striped" id="ajax-crud-datatableSalesPerformance" style="font-size: 13px; width: 98% !important; vertical-align:middle;">
                                    <thead>
                                        <tr>
                                            <th>{{__('Bill ID')}}</th>
                                            <th>{{__('Code')}}</th>
                                            <th>{{__('Date')}}</th>
                                            <th>{{__('Client')}}</th>
                                            <th>{{__('Seller')}}</th>
                                            <th>{{__('Status')}}</th>
                                            <th>{{__('Net Amount')}} ($)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="6" style="text-align:right">{{__('Grand Total')}}:</th>
                                            <th id="grand_total_display">0.00</th>
                                        </tr>
                                    </tfoot>
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
            let dataTable = $('#ajax-crud-datatableSalesPerformance').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('reports.sales_performance.ajax') }}",
                    data: function (d) {
                        d.start_date_filter = $('#start_date_filter').val();
                        d.end_date_filter = $('#end_date_filter').val();
                        d.seller_filter = $('#seller_filter').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'b.id' },
                    { data: 'code', name: 'b.code' },
                    { data: 'formatted_date', name: 'b.created_at' },
                    { data: 'client_name', name: 'c.name' },
                    { data: 'seller_name', name: 's.name' },
                    { data: 'status', name: 'b.status' },
                    { data: 'net_amount_formatted', name: 'b.net_amount', orderable: false, searchable: false }, // Columna 6 (visible)
                    { data: 'net_amount_raw', name: 'b.net_amount', visible: false, searchable: false } // Columna 7 (oculta, para cálculos)
                ],
                "footerCallback": function ( row, data, start, end, display ) {
                    let api = this.api();
                    // Usar la columna oculta (índice 7) para el cálculo
                    let total = api.column(7, {page:'current'}).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b); // Ahora 'a' y 'b' deberían ser números
                    }, 0);
                    $('#grand_total_display').html(total.toFixed(2));
                },
                lengthMenu: [
                    [20, 30, 40, 50, -1],
                    ['20', '30', '40', '50', 'Todos']
                ],
                pageLength: 20,
                language: {
                    "sProcessing": "{{__('Processing')}}...",
                    "sLengthMenu": "{{__('Show')}} _MENU_ {{__('Registers')}}",
                    "sZeroRecords": "{{__('No results found')}}",
                    "sEmptyTable": "{{__('No data available in this table')}}",
                    "sInfo": "{{__('Showing of')}} _START_ {{__('to the')}} _END_ {{__('of a total of')}} _TOTAL_ {{__('Registers')}}",
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
                        "sSortAscending": ": {{__('Activate to sort the column in ascending order')}}",
                        "sSortDescending": ": {{__('Activate to sort the column in descending order')}}"
                    }
                }
            });

            // Auto-apply filters when any filter input changes
            $('#start_date_filter, #end_date_filter, #seller_filter').on('change input', function() {
                dataTable.draw();
            });

            // Generate PDF using current filters and DataTable state
            $('#generatePdfBtn').click(function(event) {
                event.preventDefault();
                let startDate = $('#start_date_filter').val();
                let endDate = $('#end_date_filter').val();
                let sellerId = $('#seller_filter').val();

                // Get current DataTable search, order column, and direction
                let search = dataTable.search();
                let order = dataTable.order();
                let orderColumnIndex = order.length > 0 ? order[0][0] : null;
                let orderDir = order.length > 0 ? order[0][1] : null;

                let url = "{{ route('reports.sales_performance.pdf') }}" +
                    "?start_date=" + encodeURIComponent(startDate) +
                    "&end_date=" + encodeURIComponent(endDate) +
                    "&seller=" + encodeURIComponent(sellerId) +
                    "&search=" + encodeURIComponent(search) +
                    "&order_column=" + encodeURIComponent(orderColumnIndex) +
                    "&order_dir=" + encodeURIComponent(orderDir);

                window.open(url, '_blank');
            });
        });
    </script>
@endsection