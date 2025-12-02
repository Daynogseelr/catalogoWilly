@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'dashboard'])

@section('content')
<div class="container-fluid dashboard-modern">
    {{-- ROW FOR TOTALS --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-total shadow-lg border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-total bg-gradient-primary text-white me-3">
                        <i class="ni ni-money-coins"></i>
                    </div>
                    <div>
                        <span class="text-uppercase fw-bold text-muted small">Total {{__('Billing')}}</span>
                        <h3 class="fw-bold text-primary mb-0">${{ number_format($totalBilling, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-total shadow-lg border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-total bg-gradient-success text-white me-3">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                    <div>
                        <span class="text-uppercase fw-bold text-muted small">Total {{__('Shopping')}}</span>
                        <h3 class="fw-bold text-success mb-0">${{ number_format($totalPurchases, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-total shadow-lg border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-total bg-gradient-info text-white me-3">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div>
                        <span class="text-uppercase fw-bold text-muted small">Total {{__('Stock')}}</span>
                        <h3 class="fw-bold text-info mb-0">$ {{ number_format($totalStock, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ROW FOR KEY METRICS --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <a href="{{ route('indexBilling') }}" class="card card-metric shadow-sm border-0 text-decoration-none">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-metric bg-gradient-primary text-white me-3">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <span class="text-uppercase fw-bold text-muted small">{{__('Bills')}}</span>
                        <h4 class="fw-bold mb-0">{{$countBill}}</h4>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('indexCategory') }}" class="card card-metric shadow-sm border-0 text-decoration-none">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-metric bg-gradient-info text-white me-3">
                        <i class="fa-solid fa-list-ul"></i>
                    </div>
                    <div>
                        <span class="text-uppercase fw-bold text-muted small">{{__('Categories')}}</span>
                        <h4 class="fw-bold mb-0">{{$countCategory}}</h4>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('indexProduct') }}" class="card card-metric shadow-sm border-0 text-decoration-none">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-metric bg-gradient-warning text-white me-3">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <div>
                        <span class="text-uppercase fw-bold text-muted small">{{__('Products')}}</span>
                        <h4 class="fw-bold mb-0">{{$countProduct}}</h4>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('indexClient') }}" class="card card-metric shadow-sm border-0 text-decoration-none">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-metric bg-gradient-success text-white me-3">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <span class="text-uppercase fw-bold text-muted small">{{__('Clients')}}</span>
                        <h4 class="fw-bold mb-0">{{$countClient}}</h4>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- ROW FOR CHARTS --}}
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card card-chart shadow border-0">
                <div class="card-header bg-transparent pb-0 pt-3">
                    <h5 class="mb-0 fw-bold text-dark">{{__('Last 10 Months Sales Summary')}}</h5>
                </div>
                <div class="card-body pt-0" id="ultimosMeses">
                    <div class="chart-area" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-chart shadow border-0">
                <div class="card-header bg-transparent pb-0 pt-3">
                    <h5 class="mb-0 fw-bold text-dark">{{__('Most Sold Products')}}</h5>
                </div>
                <div class="card-body pt-0" id="productsmasvendidos">
                    <div class="chart-area" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-chart shadow border-0">
                <div class="card-header bg-transparent pb-0 pt-3">
                    <h5 class="mb-0 fw-bold text-dark">{{__('Most Notable Clients')}}</h5>
                </div>
                <div class="card-body pt-0" id="clientsmasdestacados">
                    <div class="chart-area" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('footer')
@endsection

@section('scripts')

    {{-- Animate.css for entrance animations (if you want to use it) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>


    <script type="text/javascript">
        $(document).ready( function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // --- New Highcharts script for Last 10 Months Sales Summary ---
            function getSelectedSucursal() {
                return $('#sucursalSelector').val();
            }

            if (document.getElementById("ultimosMeses")) {
                $.ajax({
                    url: '{{ route('dashboard.monthly_summary') }}',
                    type: 'GET',
                    data: { sucursal: getSelectedSucursal() },
                    async: true,
                    success: function (response) {
                        console.log("Monthly Summary Data:", response); // For debugging
                        Highcharts.chart('ultimosMeses', {
                            chart: {
                                type: 'line',
                                // Adjusted margins for a cleaner look and better space usage
                                marginLeft: 60,
                                marginRight: 20,
                                marginTop: 30,
                                marginBottom: 70,
                                style: {
                                    fontFamily: 'Inter, sans-serif'
                                }
                            },
                            title: {
                                text: null // Title moved to card header
                            },
                            subtitle: {
                                text: '{{__('Monthly Overview of Sales, Payments, Services & Purchases')}}',
                                align: 'center',
                                style: {
                                    fontSize: '14px',
                                    color: '#67748e'
                                }
                            },
                            xAxis: {
                                categories: response.categories, // Month names
                                labels: {
                                    style: {
                                        fontSize: '12px',
                                        color: '#67748e'
                                    }
                                },
                                title: {
                                    text: '{{__('Month')}}',
                                    style: {
                                        fontSize: '14px',
                                        fontWeight: 'bold',
                                        color: '#344767'
                                    }
                                },
                                lineColor: '#ddd',
                                tickColor: '#ddd'
                            },
                            yAxis: {
                                title: {
                                    text: '{{__('Amount')}} ($)',
                                    margin: 20, // Keep some margin for separation
                                    style: {
                                        fontSize: '14px',
                                        fontWeight: 'bold',
                                        color: '#344767'
                                    }
                                },
                                labels: {
                                    formatter: function () {
                                        return '$' + Highcharts.numberFormat(this.value, 0, '.', ','); // Format as currency
                                    },
                                    style: {
                                        color: '#67748e'
                                    }
                                },
                                gridLineColor: '#eee'
                            },
                            tooltip: {
                                headerFormat: '<span style="font-size:11px">{point.key}</span><br>',
                                pointFormat: '<span style="color:{series.color}">\u25CF</span> {series.name}: <b>${point.y:,.2f}</b><br/>',
                                shared: true,
                                useHTML: true,
                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                borderColor: '#ccc',
                                borderWidth: 1
                            },
                            plotOptions: {
                                series: {
                                    marker: {
                                        enabled: true, // Show points on lines
                                        radius: 4,
                                        symbol: 'circle' // Use circles for points
                                    },
                                    lineWidth: 2, // Thicker lines
                                    shadow: true,
                                    pointStart: 0, // Ensure points start from the first category
                                    animation: {
                                        duration: 1500 // Smooth animation on load
                                    }
                                }
                            },
                            series: response.series, // Expecting an array of series from the backend
                            legend: {
                                align: 'center',
                                verticalAlign: 'bottom',
                                layout: 'horizontal',
                                itemStyle: {
                                    fontSize: '11px',
                                    fontWeight: 'normal',
                                    color: '#333'
                                }
                            },
                            credits: {
                                enabled: false // Remove Highcharts.com watermark
                            }
                        });
                    },
                    error: function (error) {
                        console.error("Error loading monthly summary chart:", error);
                        $('#ultimosMeses .chart-area').html('<div class="alert alert-danger text-center">{{__('Error loading monthly summary data. Please try again.')}}</div>');
                    }
                });
            }

            // Highcharts script for Most Sold Products (existing code, added margin and better error handling)
            if (document.getElementById("productsmasvendidos")) {
                const action = "sales2";
                $.ajax({
                    url: '{{ route('chart') }}',
                    type: 'GET',
                    data: { action, sucursal: getSelectedSucursal() },
                    async: true,
                    success: function (response) {
                        console.log("Products Data:", response);
                        Highcharts.chart('productsmasvendidos', {
                            chart: {
                                type: 'column',
                                marginLeft: 60, // Consistent margin
                                marginRight: 20, // Consistent margin
                                marginTop: 30,
                                marginBottom: 70,
                                options3d: {
                                    enabled: true,
                                    alpha: 10,
                                    beta: 15,
                                    viewDistance: 25,
                                    depth: 40
                                },
                                style: {
                                    fontFamily: 'Inter, sans-serif' // Consistent font for charts
                                }
                            },
                            title: {
                                text: null, // Title moved to card header
                            },
                            subtitle: {
                                text: '{{__('Top 10 Products')}}', // Add a subtitle for context
                                align: 'center',
                                style: {
                                    fontSize: '14px',
                                    color: '#67748e'
                                }
                            },
                            xAxis: {
                                categories: (function(){
                                    var data = [];
                                    $.each(response, function(index, product){
                                        data.push(product.name + ' (' + product.code + ')');
                                    })
                                    return data;
                                })(),
                                labels: {
                                    rotation: -45,
                                    style: {
                                        fontSize: '11px',
                                        color: '#67748e' // Darker label color
                                    }
                                },
                                lineColor: '#ddd',
                                tickColor: '#ddd'
                            },
                            yAxis: {
                                allowDecimals: false,
                                min: 0,
                                title: {
                                    text: "{{__('Quantity Sold')}}",
                                    skew3d: false,
                                    margin: 20, // Consistent margin
                                    style: {
                                        fontSize: '14px',
                                        fontWeight: 'bold',
                                        color: '#344767'
                                    }
                                },
                                labels: {
                                    style: {
                                        color: '#67748e' // Grayer labels
                                    }
                                },
                                gridLineColor: '#eee'
                            },
                            tooltip: {
                                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                    '<td style="padding:0"><b>{point.y}</b></td></tr>',
                                footerFormat: '</table>',
                                shared: true,
                                useHTML: true,
                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                borderColor: '#ccc',
                                borderWidth: 1
                            },
                            plotOptions: {
                                column: {
                                    stacking: 'normal',
                                    depth: 40,
                                    borderRadius: 3,
                                    dataLabels: {
                                        enabled: true,
                                        format: '{point.y}',
                                        style: {
                                            fontWeight: 'bold',
                                            textOutline: 'none',
                                            color: 'Contrast'
                                        }
                                    },
                                    pointPadding: 0.1,
                                    groupPadding: 0.2
                                }
                            },
                            series:  [{
                                name: "{{__('Quantity')}}",
                                data: (function(){
                                    var data = [];
                                    $.each(response, function(index, product){
                                        data.push(parseFloat(product.total));
                                    })
                                    return data;
                                })(),
                                color: '#fbc687'
                            }],
                            legend: {
                                enabled: false
                            },
                            credits: {
                                enabled: false
                            }
                        });
                    },
                    error: function (error) {
                        console.log("Error loading products chart:", error);
                        $('#productsmasvendidos .chart-area').html('<div class="alert alert-danger text-center">{{__('Error loading products data. Please try again.')}}</div>');
                    }
                });
            }

            // Highcharts script for Most Notable Clients (existing code, added margin and better error handling)
            if (document.getElementById("clientsmasdestacados")) {
                const action = "sales2";
                $.ajax({
                    url: '{{ route('chart2') }}',
                    type: 'GET',
                    data: { action, sucursal: getSelectedSucursal() },
                    async: true,
                    success: function (response) {
                        console.log("Clients Data:", response);
                        Highcharts.chart('clientsmasdestacados', {
                            chart: {
                                type: 'column',
                                marginLeft: 60, // Consistent margin
                                marginRight: 20, // Consistent margin
                                marginTop: 30,
                                marginBottom: 70,
                                options3d: {
                                    enabled: true,
                                    alpha: 10,
                                    beta: 15,
                                    viewDistance: 25,
                                    depth: 40
                                },
                                style: {
                                    fontFamily: 'Inter, sans-serif' // Consistent font for charts
                                }
                            },
                            title: {
                                text: null, // Title moved to card header
                            },
                            subtitle: {
                                text: '{{__('Top 10 Clients')}}', // Add a subtitle for context
                                align: 'center',
                                style: {
                                    fontSize: '14px',
                                    color: '#67748e'
                                }
                            },
                            xAxis: {
                                categories: (function(){
                                    var data = [];
                                    $.each(response, function(index, client){
                                        data.push(client.name);
                                    })
                                    return data;
                                })(),
                                labels: {
                                    rotation: -45,
                                    style: {
                                        fontSize: '11px',
                                        color: '#67748e'
                                    }
                                },
                                lineColor: '#ddd',
                                tickColor: '#ddd'
                            },
                            yAxis: {
                                allowDecimals: true,
                                min: 0,
                                title: {
                                    text: "{{__('Total Amount')}} ($)",
                                    skew3d: false,
                                    margin: 20, // Consistent margin
                                    style: {
                                        fontSize: '14px',
                                        fontWeight: 'bold',
                                        color: '#344767'
                                    }
                                },
                                labels: {
                                    formatter: function() {
                                        return '$' + Highcharts.numberFormat(this.value, 0, '.', ','); // Format Y-axis labels as currency
                                    },
                                    style: {
                                        color: '#67748e'
                                    }
                                },
                                gridLineColor: '#eee'
                            },
                            tooltip: {
                                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                    '<td style="padding:0"><b>${point.y:,.2f}</b></td></tr>',
                                footerFormat: '</table>',
                                shared: true,
                                useHTML: true,
                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                borderColor: '#ccc',
                                borderWidth: 1
                            },
                            plotOptions: {
                                column: {
                                    stacking: 'normal',
                                    depth: 40,
                                    borderRadius: 3,
                                    dataLabels: {
                                        enabled: true,
                                        format: '${point.y:,.0f}', // Format data labels as currency (no decimals if full dollar)
                                        style: {
                                            fontWeight: 'bold',
                                            textOutline: 'none',
                                            color: 'Contrast'
                                        }
                                    },
                                    pointPadding: 0.1,
                                    groupPadding: 0.2
                                }
                            },
                            series:  [{
                                name: "{{__('Amount')}}",
                                data: (function(){
                                    var data = [];
                                    $.each(response, function(index, client){
                                        data.push(parseFloat(client.total));
                                    })
                                    return data;
                                })(),
                                color: '#82d616'
                            }],
                            legend: {
                                enabled: false
                            },
                            credits: {
                                enabled: false
                            }
                        });
                    },
                    error: function (error) {
                        console.log("Error loading clients chart:", error);
                        $('#clientsmasdestacados .chart-area').html('<div class="alert alert-danger text-center">{{__('Error loading clients data. Please try again.')}}</div>');
                    }
                });
            }

            // when selector changes, save selection in session via existing endpoint then reload
            $('#sucursalSelector').on('change', function(){
                const selected = $(this).val();
                fetch('/set-sucursal', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({ sucursal_id: selected })
                }).then(function(res){
                    if (res.ok) location.reload(); else location.reload();
                }).catch(function(){ location.reload(); });
            });
        });
    </script>
@endsection

<style>
.dashboard-modern {
    background: linear-gradient(135deg, #f8fafc 0%, #e9ecef 100%);
    min-height: 100vh;
    padding-bottom: 40px;
}
.card-total {
    background: linear-gradient(120deg, #fff 60%, #f1f3f6 100%);
    border-radius: 1rem;
    transition: box-shadow .2s;
}
.card-total:hover {
    box-shadow: 0 8px 24px rgba(44,62,80,.12);
}
.icon-total {
    width: 60px;
    height: 60px;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    box-shadow: 0 2px 8px rgba(44,62,80,.08);
}
.card-metric {
    background: linear-gradient(120deg, #fff 60%, #f1f3f6 100%);
    border-radius: 1rem;
    transition: box-shadow .2s, transform .2s;
    cursor: pointer;
}
.card-metric:hover {
    box-shadow: 0 8px 24px rgba(44,62,80,.12);
    transform: translateY(-4px) scale(1.03);
}
.icon-metric {
    width: 48px;
    height: 48px;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 2px 8px rgba(44,62,80,.08);
}
.card-chart {
    border-radius: 1rem;
    background: #fff;
    box-shadow: 0 4px 16px rgba(44,62,80,.08);
    margin-bottom: 0;
}
.card-header {
    border-bottom: none;
}
.chart-area {
    min-height: 280px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.text-primary { color: #cb0c9f !important; }
.text-success { color: #82d616 !important; }
.text-info { color: #17c1e8 !important; }
.text-dark { color: #344767 !important; }
.text-muted { color: #6c757d !important; }
.fw-bold { font-weight: bold !important; }
.bg-gradient-primary { background: linear-gradient(135deg, #cb0c9f 0%, #6a82fb 100%) !important; }
.bg-gradient-success { background: linear-gradient(135deg, #82d616 0%, #28a745 100%) !important; }
.bg-gradient-info { background: linear-gradient(135deg, #17c1e8 0%, #007bff 100%) !important; }
.bg-gradient-warning { background: linear-gradient(135deg, #fbc687 0%, #f7b731 100%) !important; }
</style>