@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'productPDF'])

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="row">
                            <div class="col-sm-12 card-header-info" style="width: 98% !important;">
                                <div class="row d-flex align-items-center"> {{-- Added d-flex and align-items-center for better alignment --}}
                                    <div class="col-8 col-sm-4"> {{-- Adjusted column size --}}
                                        <h4>{{__('Reportes PDF de Productos')}}</h4>
                                    </div>
                                    <div class="col-4 col-sm-3 totales-container"> {{-- Adjusted column size --}}
                                        <div class="totales-content" style="margin-top: -10px !important;">
                                            <h5>
                                                <span class="total-label">{{__('Total')}}:</span>
                                                <span id="totalDolares" class="total-amount">0.00</span> $
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="col-10 col-sm-4"> {{-- Adjusted column size --}}
                                        <select class="form-select" name="stock_filter" id="stock_filter">
                                            <option value="TODOS">{{__('Todos los Productos')}}</option>
                                            <option value="MAYORACERO">{{__('Stock Mayor a 0')}}</option>
                                            <option value="SINSTOCK">{{__('Producto sin Stock')}}</option>
                                        </select>
                                    </div>
                                    <div class="col-2 col-sm-1 d-flex justify-content-end"> {{-- Adjusted column size and alignment --}}
                                        <a style="padding: 10px 10px; color:white;" id="generatePdfBtn" target="_blank" data-toggle="tooltip" data-original-title="Generate PDF" class="btn btn-info">
                                            <i class="fa-regular fa-file-pdf"></i> {{-- Changed icon to pdf --}}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="card-body" >
                            <div class="tabla table-responsive" style="font-size: 13px;">
                                <table class="table table-striped" id="ajax-crud-datatableProductPDF" style="font-size: 13px; width: 98% !important; vertical-align:middle;"> {{-- Changed width to 100% --}}
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{__('Date')}}</th>
                                            <th class="text-center">{{__('Code')}}</th>
                                            <th class="text-center">{{__('Name')}}</th>
                                            <th class="text-center">{{__('Price')}} ($)</th>
                                            <th class="text-center">{{__('Quantity')}}</th>
                                            <th class="text-center">{{__('Total')}} ($)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data will be loaded here by DataTables --}}
                                    </tbody>
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

            let dataTable = $('#ajax-crud-datatableProductPDF').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('ajax-crud-datatableProductPDF') }}",
                    data: function (d) {
                        d.stock_filter = $('#stock_filter').val(); // Send selected stock filter
                    }
                },
                columns: [
                    { data: 'formatted_created_at', name: 'p.created_at' }, // **CORRECCIÓN AQUÍ: Usar 'p.created_at'**
                    { data: 'code', name: 'p.code' }, // Also changed to 'p.code' for consistency
                    { data: 'name', name: 'p.name' },   // Also changed to 'p.name' for consistency
                    { data: 'price', name: 'p.price' }, // Also changed to 'p.price' for consistency
                    { data: 'quantity', name: 's.quantity' }, // This should be 's.quantity' as it refers to the actual column in the joined table
                    { data: 'total', name: 'total', orderable: false }
                ],
                drawCallback: function(settings) {
                    centerTableContent();
                    calculateTotal();
                },
                order: [[0, 'desc']], // Default order by Date (first column) descending
                columnDefs: [
                    { targets: [0, 3, 4], searchable: false }, // Disable search for Date, Price, Quantity, Total
                    { targets: [1, 2], searchable: true} // Enable search for Code, Name, Inventory Name
                ],
                lengthMenu: [
                    [20, 30, 40, 50, -1],
                    ['20', '30', '40', '50', 'Todos']
                ],
                pageLength: 20, // Default page length
                language: { // Using 'language' instead of 'oLanguage' for DataTables 1.10+
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

            // Reload table on filter change
            $('#stock_filter').change(function () {
                dataTable.ajax.reload();
            });

            // Handle PDF generation button click
            $('#generatePdfBtn').click(function(event) {
                event.preventDefault(); // Prevent default link behavior

                let selectedStockFilter = $('#stock_filter').val();

                // Get current search value from DataTables
                let currentSearch = dataTable.search();

                // Get current ordering from DataTables
                let order = dataTable.order();
                let orderColumnIndex = order.length > 0 ? order[0][0] : null;
                let orderDirection = order.length > 0 ? order[0][1] : null;

                // Build the URL for PDF with all filters and current DataTable state
                let pdfUrl = "{{ route('pdfProduct') }}" +
                             "?type=" + selectedStockFilter +
                             "&search=" + encodeURIComponent(currentSearch) + // Encode search query
                             "&order_column=" + orderColumnIndex +
                             "&order_dir=" + orderDirection;

                window.open(pdfUrl, '_blank'); // Open PDF in new tab
            });

            function calculateTotal() {
                let total = 0;
                dataTable.rows({page: 'current'}).every(function (rowIdx, tableLoop, rowLoop) {
                    let data = this.data();
                    total += parseFloat(data.total); // Parse total as float and add to sum
                });
                $('#totalDolares').text(total.toFixed(2)); // Display total with 2 decimal places
            }

            function centerTableContent() {
                // Ensure the DataTables re-initializes or re-applies classes on draw
                // This is often better handled with CSS for DataTables columns
                $('#ajax-crud-datatableProductPDF tbody tr td:nth-child(1)').addClass('text-center'); // Date
                $('#ajax-crud-datatableProductPDF tbody tr td:nth-child(2)').addClass('text-center'); // Code
                $('#ajax-crud-datatableProductPDF tbody tr td:nth-child(3)').addClass('text-start');  // Name (left align generally better for text)
                $('#ajax-crud-datatableProductPDF tbody tr td:nth-child(4)').addClass('text-end').css('padding-right', '10px'); // Price
                $('#ajax-crud-datatableProductPDF tbody tr td:nth-child(5)').addClass('text-center'); // Quantity
                $('#ajax-crud-datatableProductPDF tbody tr td:nth-child(6)').addClass('text-end').css('padding-right', '10px'); // Total
            }
        });
    </script>
@endsection