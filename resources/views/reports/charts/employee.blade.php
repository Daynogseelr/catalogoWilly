@extends('app', ['page' => __('Rendimiento de Ventas'), 'pageSlug' => 'employeeChart'])

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-12 mb-lg-0 mb-4">
                <div class="card shadow-lg border-0 rounded-4 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent border-bottom-0">
                        <div class="row">
                            <div class="col-sm-12 card-header-info">
                                <div class="row">
                                    <div class="col-12">
                                        <h4 class="card-title text-dark font-weight-bold mb-0">{{__('Sales Performance Report Graphics')}}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 align-items-end mb-4">
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label for="seller_filter" class="form-label text-muted text-sm">{{__('Seller')}}:</label>
                                    <select name="seller_filter" class="form-select rounded-3" id="seller_filter">
                                        <option value="TODOS">{{__('All Sellers')}}</option>
                                        @foreach($sellers as $seller)
                                            <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label for="metric_type" class="form-label text-muted text-sm">{{__('Metric Type')}}:</label>
                                    <select name="metric_type" class="form-select rounded-3" id="metric_type">
                                        <option value="total_amount">{{__('Total Amount')}}</option>
                                        <option value="quantity">{{__('Number of Bills')}}</option>
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
                                {{-- Modificamos el estilo del contenedor del gráfico --}}
                                <div id="grafi" class="chart-container rounded-3" style="min-height: 600px; padding: 0; margin: 0;">
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

            function populateMonthSelect() {
                const months = [
                    { value: '0', text: '{{__('Select Month')}}' },
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
            function updateChart() {
                const sellerFilter = $('#seller_filter').val();
                const metricType = $('#metric_type').val();
                const desde = $('#desde').val();
                const hasta = $('#hasta').val();
                let month = $('#desdeM').val();

                if (desde !== hasta) {
                    $('#desdeM').prop('disabled', true);
                    month = '0';
                    $('#desdeM').val('0');
                } else {
                    $('#desdeM').prop('disabled', false);
                }

                $.ajax({
                    url: '{{ route('reports.sales_performance.chart') }}',
                    type: 'GET',
                    data: {
                        seller_filter: sellerFilter,
                        metric_type: metricType,
                        desde: desde,
                        hasta: hasta,
                        month: month
                    },
                    async: true,
                    success: function (response) {
                        const chartCategories = response.categories.length > 0 ? response.categories : ['{{__('No Data')}}'];
                        const chartData = response.data.length > 0 ? response.data : [0];

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
                                text: response.chartTitle,
                                align: 'center',
                                style: {
                                    fontSize: '18px',
                                    fontWeight: 'bold',
                                    color: '#344767'
                                }
                            },
                            xAxis: {
                                categories: chartCategories,
                                labels: {
                                    skew3d: false,
                                    style: {
                                        fontSize: '12px',
                                        color: '#67748e'
                                    },
                                    // Opcional: Rotar etiquetas si se superponen mucho
                                    // rotation: -45,
                                    // align: 'right'
                                },
                                title: {
                                    text: response.xAxisLabel,
                                    style: {
                                        fontSize: '14px',
                                        fontWeight: 'bold',
                                        color: '#344767'
                                    }
                                }
                            },
                            yAxis: {
                                allowDecimals: response.metricType === 'total_amount',
                                min: 0,
                                title: {
                                    text: response.yAxisTitle,
                                    skew3d: false,
                                    // Ya ajustado en la petición anterior para separar el título de los montos
                                    margin: 100, // Manteniendo este valor alto para la separación
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
                                        if (response.metricType === 'total_amount') {
                                            return '$' + Highcharts.numberFormat(this.value, 2);
                                        }
                                        return this.value;
                                    }
                                }
                            },
                            tooltip: {
                                headerFormat: '<span style="font-size:11px">{point.key}</span><br>',
                                pointFormat: '<span style="color:{point.color}">\u25CF</span> {series.name}: <b>{point.y:' + (response.metricType === 'total_amount' ? '.2f' : '.0f') + '}</b><br/>'
                            },
                            plotOptions: {
                                column: {
                                    stacking: 'normal',
                                    depth: 40,
                                    dataLabels: {
                                        enabled: true,
                                        format: '{point.y:' + (response.metricType === 'total_amount' ? '.2f' : '.0f') + '}',
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
                                name: response.yAxisTitle,
                                data: chartData,
                                color: response.metricType === 'quantity' ? '#6200EE' : '#03DAC6'
                            }]
                        });
                    },
                    error: function (error) {
                        console.error("Error fetching sales performance chart data:", error);
                        $('#grafi').html('<p class="text-danger text-center mt-5">{{__('Error loading sales performance chart data. Please try again.')}}</p>');
                    }
                });
            }

            // --- Event Listeners for Selects ---
            $('#seller_filter').on('change', updateChart);
            $('#metric_type').on('change', updateChart);
            $('#desde').on('change', updateChart);
            $('#hasta').on('change', updateChart);
            $('#desdeM').on('change', updateChart);

            // Initial chart load when the page loads
            updateChart();
        });
    </script>
@endsection