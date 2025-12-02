@extends('app', ['page' => __('Reporte de Ganancias'), 'pageSlug' => 'paymentChart'])

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            {{-- CAMBIA col-lg-10 A col-lg-12 --}}
            <div class="col-lg-12 col-md-12 mb-lg-0 mb-4">
                <div class="card shadow-lg border-0 rounded-4 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent border-bottom-0">
                        <div class="row">
                            <div class="col-sm-12 card-header-info">
                                <div class="row">
                                    <div class="col-12">
                                        <h4 class="card-title text-dark font-weight-bold mb-0">{{__('Earnings Report Graphics')}}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 align-items-end mb-4">
                            <div class="col-md-2 col-sm-4">
                                <div class="form-group">
                                    <label for="form" class="form-label text-muted text-sm">{{__('Form')}}:</label>
                                    <select name="form" class="form-select rounded-3" id="form">
                                        <option selected value="quantity">{{__('Quantity of Payments')}}</option>
                                        <option value="total">{{__('Total Amount')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-4">
                                <div class="form-group">
                                    <label for="type" class="form-label text-muted text-sm">{{__('Payment Method')}}:</label>
                                    <select name="type" class="form-select rounded-3" id="type">
                                        <option value="todos">{{__('All Methods')}}</option>
                                        @foreach($paymentMethods as $method)
                                            <option value="{{ $method->type }}">{{ $method->type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4">
                                <div class="form-group">
                                    <label for="desde" class="form-label text-muted text-sm">{{__('From Year')}}:</label>
                                    <select name="desde" class="form-select rounded-3" id="desde">
                                        {{-- Options will be populated by JS --}}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4">
                                <div class="form-group">
                                    <label for="hasta" class="form-label text-muted text-sm">{{__('To Year')}}:</label>
                                    <select name="hasta" class="form-select rounded-3" id="hasta">
                                        {{-- Options will be populated by JS --}}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4">
                                <div class="form-group">
                                    <label for="desdeM" class="form-label text-muted text-sm">{{__('Month')}}:</label>
                                    <select name="desdeM" class="form-select rounded-3" id="desdeM" disabled>
                                        {{-- Options will be populated by JS --}}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div id="grafi" class="chart-container rounded-3 p-3"  style="min-height: 600px; padding: 0; margin: 0;">
                                    {{-- Highcharts will render here --}}
                                </div>
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
        $(document).ready(function () {
            // Set up AJAX headers with CSRF token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // --- Dropdown Population Functions ---
            // Populates the "From Year" and "To Year" select elements
            function populateYearSelects() {
                const currentYear = new Date().getFullYear();
                const startYear = currentYear - 9; // Last 10 years including current year

                let yearsOptions = '';
                for (let year = currentYear; year >= startYear; year--) {
                    yearsOptions += `<option value="${year}">${year}</option>`;
                }
                $('#desde').html(yearsOptions);
                $('#hasta').html(yearsOptions);
            }

            // Populates the "Month" select element
            function populateMonthSelect() {
                const months = [
                    { value: '0', text: '{{__('Select Month')}}' }, // Option to not filter by month
                    { value: '1', text: '{{__('January')}}' },
                    { value: '2', text: '{{__('February')}}' },
                    { value: '3', text: '{{__('March')}}' },
                    { value: '4', text: '{{__('April')}}' },
                    { value: '5', text: '{{__('May')}}' },
                    { value: '6', text: '{{__('June')}}' },
                    { value: '7', text: '{{__('July')}}' },
                    { value: '8', text: '{{__('August')}}' },
                    { value: '9', text: '{{__('September')}}' },
                    { value: '10', text: '{{__('October')}}' },
                    { value: '11', text: '{{__('November')}}' },
                    { value: '12', text: '{{__('December')}}' }
                ];
                let monthOptions = '';
                months.forEach(month => {
                    monthOptions += `<option value="${month.value}">${month.text}</option>`;
                });
                $('#desdeM').html(monthOptions);
            }

            // Initialize dropdowns on document ready
            populateYearSelects();
            populateMonthSelect();

            // --- Chart Update Function ---
            // Fetches data from the server and updates the Highcharts chart
            function updateChart() {
                const form = $('#form').val();
                const type = $('#type').val(); // Payment method type
                const desde = $('#desde').val();
                const hasta = $('#hasta').val();
                let month = $('#desdeM').val();

                // Logic to enable/disable the month select:
                // Only enable if "From Year" and "To Year" are the same.
                if (desde !== hasta) {
                    $('#desdeM').prop('disabled', true);
                    month = '0'; // Reset month filter if years are different
                    $('#desdeM').val('0'); // Set dropdown back to "Select Month"
                } else {
                    $('#desdeM').prop('disabled', false);
                }

                $.ajax({
                    url: '{{ route('reporte.payment') }}', // New route for payment reports
                    type: 'GET',
                    data: {
                        form: form,
                        type: type, // Pass the payment method type filter
                        desde: desde,
                        hasta: hasta,
                        month: month
                    },
                    async: true,
                    success: function (response) {
                        const chartCategories = response.categories.length > 0 ? response.categories : ['{{__('No Data')}}'];
                        const chartData = response.data.length > 0 ? response.data : [0]; // Default to 0 if no data

                        Highcharts.chart('grafi', {
                            chart: {
                                type: 'column',
                                 marginLeft: -200, // Ajusta según el espacio deseado a la izquierda.
                                marginRight: 10, // Un poco de espacio a la derecha, si no, puedes probar con 0.
                                marginTop: 10,  // Deja espacio para el título principal del gráfico.
                                marginBottom: 50, // Deja espacio para las etiquetas del eje X y la leyenda.
                                options3d: {
                                    enabled: true,
                                    alpha: 15,
                                    beta: 15,
                                    viewDistance: 25,
                                    depth: 40
                                },
                                style: {
                                    fontFamily: 'Inter, sans-serif'
                                }
                            },
                            title: {
                                text: response.chartTitle, // Dynamic title from controller
                                align: 'center',
                                style: {
                                    fontSize: '18px',
                                    fontWeight: 'bold',
                                    color: '#344767'
                                }
                            },
                            xAxis: {
                                categories: chartCategories, // Dynamic categories (Months or Years)
                                labels: {
                                    skew3d: false,
                                    style: {
                                        fontSize: '12px',
                                        color: '#67748e'
                                    }
                                },
                                title: {
                                    text: response.xAxisLabel, // Dynamic X-axis title (Months or Years)
                                    style: {
                                        fontSize: '14px',
                                        fontWeight: 'bold',
                                        color: '#344767'
                                    }
                                }
                            },
                            yAxis: {
                                allowDecimals: response.form === 'total', // Allow decimals for total amount
                                min: 0,
                                title: {
                                    text: response.yAxisTitle, // Dynamic Y-axis title from controller
                                    skew3d: false,
                                      margin: 100,
                                    style: {
                                        fontSize: '14px',
                                        fontWeight: 'bold',
                                        color: '#344767'
                                    }
                                },
                                labels: {
                                    style: {
                                        color: '#67748e'
                                    },
                                    formatter: function () {
                                        if (response.form === 'total') {
                                            return '$' + Highcharts.numberFormat(this.value, 2); // Format as currency
                                        }
                                        return this.value;
                                    }
                                }
                            },
                            tooltip: {
                                headerFormat: '<span style="font-size:11px">{point.key}</span><br>',
                                pointFormat: '<span style="color:{point.color}">\u25CF</span> {series.name}: <b>{point.y:' + (response.form === 'total' ? '.2f' : '.0f') + '}</b><br/>' // Format based on form type
                            },
                            plotOptions: {
                                column: {
                                    stacking: 'normal',
                                    depth: 40,
                                    dataLabels: {
                                        enabled: true,
                                        format: '{point.y:' + (response.form === 'total' ? '.2f' : '.0f') + '}' // Format data labels
                                    }
                                },
                                series: {
                                    pointPadding: 0.1,
                                    groupPadding: 0.1,
                                    borderWidth: 0,
                                    shadow: true,
                                    borderRadius: 5
                                }
                            },
                            series: [{
                                name: response.yAxisTitle, // Series name matches y-axis title
                                data: chartData,
                                color: response.form === 'quantity' ? '#FFC107' : '#17A2B8' // Different colors for quantity vs total
                            }]
                        });
                    },
                    error: function (error) {
                        console.error("Error fetching payment chart data:", error);
                        $('#grafi').html('<p class="text-danger text-center mt-5">{{__('Error loading payment chart data. Please try again.')}}</p>');
                    }
                });
            }

            // --- Event Listeners for Selects ---
            // Trigger chart update whenever a filter selection changes
            $('#form').on('change', updateChart);
            $('#type').on('change', updateChart);
            $('#desde').on('change', updateChart);
            $('#hasta').on('change', updateChart);
            $('#desdeM').on('change', updateChart);

            // Initial chart load when the page loads
            updateChart();
        });
    </script>
@endsection