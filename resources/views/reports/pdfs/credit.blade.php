@extends('app', ['page' => __('Cuentas por Cobrar'), 'pageSlug' => 'creditPDF'])

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-10 col-sm-11"> {{-- Adjusted column size --}}
                                <h4 class="card-title">{{__('Accounts Receivable PDF Reports')}}</h4>
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="client_filter">{{__('Client')}}:</label>
                                    <select class="form-control" id="client_filter">
                                        <option value="TODOS">{{__('All Clients')}}</option>
                                        {{-- Populated with clients that actually have debt --}}
                                        @foreach($clientsWithDebt as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status_filter">{{__('Status')}}:</label>
                                    <select class="form-control" id="status_filter">
                                        <option value="TODOS">{{__('All Status')}}</option>
                                        <option value="PENDING">{{__('Pending')}}</option>
                                        <option value="PARTIAL">{{__('Partially Paid')}}</option>
                                        <option value="OVERDUE">{{__('Overdue')}}</option>
                                        <option value="PAID">{{__('Paid')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date_filter">{{__('From Invoice Date')}}:</label>
                                    <input type="date" class="form-control" id="start_date_filter">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date_filter">{{__('To Invoice Date')}}:</label>
                                    <input type="date" class="form-control" id="end_date_filter">
                                </div>
                            </div>
                        </div>
                       
                        <div class="card-body">
                            <div class="tabla table-responsive" style="font-size: 12px;">
                                <table class="table table-striped" id="ajax-crud-datatableAccountsReceivablePDF"  style="font-size: 13px; width: 98% !important; vertical-align:middle;">
                                    <thead>
                                        <tr>
                                            <th>{{__('Code')}}</th> {{-- Changed from ID Invoice to Code --}}
                                            <th>{{__('Invoice Date')}}</th>
                                            <th>{{__('Due Date')}}</th>
                                            <th>{{__('Client')}}</th>
                                            <th>{{__('Total Amount')}} ($)</th>
                                            <th>{{__('Amount Paid')}} ($)</th>
                                            <th>{{__('Outstanding Balance')}} ($)</th>
                                            <th>{{__('Status')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" style="text-align:right">{{__('Total Sums')}}:</th>
                                            <th id="grand_total_amount_display">0.00</th>
                                            <th id="grand_total_paid_display">0.00</th>
                                            <th id="grand_total_outstanding_display">0.00</th>
                                            <th></th>
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
            let dataTable = $('#ajax-crud-datatableAccountsReceivablePDF').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('reports.accounts_receivable.ajax') }}",
                    data: function (d) {
                        d.client_filter = $('#client_filter').val();
                        d.status_filter = $('#status_filter').val();
                        d.start_date_filter = $('#start_date_filter').val();
                        d.end_date_filter = $('#end_date_filter').val();
                    }
                },
                columns: [
                    { data: 'code', name: 'b.code' }, // Changed from ID Invoice to Code
                    { data: 'invoice_date', name: 'b.created_at' },
                    { data: 'due_date_formatted', name: 'b.creditDays' }, // Order by creditDays if possible, or created_at + creditDays
                    { data: 'client_name', name: 'c.name' },
                    { data: 'total_amount_formatted', name: 'b.net_amount' }, // Using net_amount as total
                    { data: 'amount_paid_formatted', name: 'amount_paid', orderable: false, searchable: false }, // Calculated
                    { data: 'outstanding_balance_formatted', name: 'b.payment' }, // Direct from DB, so orderable
                    { data: 'status', name: 'status', orderable: false, searchable: false } // Calculated
                ],
                "footerCallback": function ( row, data, start, end, display ) {
                    let api = this.api();

                    // Sum columns 4, 5, 6
                    let totalAmount = api.column(4, {page:'current'}).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);

                    let totalPaid = api.column(5, {page:'current'}).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);

                    let totalOutstanding = api.column(6, {page:'current'}).data().reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);

                    $('#grand_total_amount_display').html(totalAmount.toFixed(2));
                    $('#grand_total_paid_display').html(totalPaid.toFixed(2));
                    $('#grand_total_outstanding_display').html(totalOutstanding.toFixed(2));
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
            $('#client_filter, #status_filter, #start_date_filter, #end_date_filter').on('change input', function() {
                dataTable.draw();
            });

            // Generate PDF using current filters and DataTable state
            $('#generatePdfBtn').click(function(event) {
                event.preventDefault();
                let client = $('#client_filter').val();
                let status = $('#status_filter').val();
                let startDate = $('#start_date_filter').val();
                let endDate = $('#end_date_filter').val();

                let search = dataTable.search();
                let order = dataTable.order();
                let orderColumnIndex = order.length > 0 ? order[0][0] : null;
                let orderDir = order.length > 0 ? order[0][1] : null;

                let url = "{{ route('reports.accounts_receivable.pdf') }}" +
                    "?client=" + encodeURIComponent(client) +
                    "&status=" + encodeURIComponent(status) +
                    "&start_date=" + encodeURIComponent(startDate) +
                    "&end_date=" + encodeURIComponent(endDate) +
                    "&search=" + encodeURIComponent(search) +
                    "&order_column=" + encodeURIComponent(orderColumnIndex) +
                    "&order_dir=" + encodeURIComponent(orderDir);

                window.open(url, '_blank');
            });
        });
    </script>
@endsection