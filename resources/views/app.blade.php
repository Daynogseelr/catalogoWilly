<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icons/icon-192x192.png') }}">
    <title>Telematicsteh</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/tele4.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/tele4.png') }}">
    <!-- Nucleo Icons -->
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <link href="{{ asset('fontawesome-free-6.5.2-web/css/all.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/fontawesome.js') }}" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link href="{{ asset('assets/css/jquery.dataTables.min.css') }}" rel="stylesheet">
    <!-- select2 -->
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/select2-bootstrap-5-theme.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/select2-bootstrap-5-theme.rtl.min.css') }}" />
    <!-- banderas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />
    <script src="{{ asset('assets/js/hig/highcharts.js') }}"></script>
    <script src="{{ asset('assets/js/hig/highcharts-3d.js') }}"></script>
    <script src="{{ asset('assets/js/hig/exporting.js') }}"></script>
    <script src="{{ asset('assets/js/hig/export-data.js') }}"></script>
    <script src="{{ asset('assets/js/hig/accessibility.js') }}"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />
    @yield('css')
    <style>
        body {
            background: #f5f6fa;
        }

        .content-wrapper {
            margin-left: 250px;
            padding-top: 80px;
            transition: margin-left 0.3s;
        }

        @media (max-width: 991px) {
            .content-wrapper {
                margin-left: 0;
                padding-top: 80px;
            }
        }

        .card-header-info h4 {
            font-weight: 700;
            color: #35b3e5;
            letter-spacing: 1px;
            margin-bottom: 0;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 6px;
            padding: 4px 8px;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 6px;
            padding: 4px 8px;
        }

        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #e0e0e0 !important;
        }

        .dt-buttons .dt-button {
            background: #ececec !important;
            color: #000000 !important;
            border-radius: 6px !important;
            margin-right: 4px !important;
            border: none !important;
            font-weight: 600 !important;
            padding: 6px 14px !important;
        }

        .dt-buttons .dt-button:hover {
            background: #d1d0d0 !important;
            color: #000000 !important;
        }

        body.billing-fullwidth .content-wrapper {
            margin-left: 0 !important;
            width: 100% !important;
            transition: margin-left 0.3s, width 0.3s;
        }

        body.billing-fullwidth .navbar-main {
            left: 0 !important;
            width: 100% !important;
            border-radius: 1rem;
            transition: left 0.3s, width 0.3s;
        }
    </style>
</head>

<body class="g-sidenav-show bg-gray-100 {{ $class ?? '' }}">
    @auth
        @include('navbars.nav')
        @include('navbars.barra')
        <div class="content-wrapper">
            <main class="main-content position-relative border-radius-lg">
                @yield('content')
            </main>
        </div>
    @endauth
    @guest
        <div class="content-fluid">
            <main class="main-content position-relative border-radius-lg">
                @yield('content')
            </main>
        </div>
    @endguest
    <!--   Core JS Files   -->
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script>
        function validaMonto(evt) {
            const input = evt.target.value;
            const decimalIndex = input.indexOf('.');
            // Permitir borrar
            if (evt.keyCode === 8) {
                return true;
            }
            // Si se intenta ingresar un punto y ya existe uno, no permitir
            if (evt.which === 46 && decimalIndex !== -1) {
                return false;
            }
            // Validar si el caracter ingresado es un número o un punto
            if ((evt.which >= 48 && evt.which <= 57) || evt.which === 46) {
                // Si ya hay un punto y se están ingresando números, verificar la cantidad de decimales
                if (decimalIndex !== -1 && evt.which >= 48 && evt.which <= 57) {
                    const decimalCount = input.length - decimalIndex - 1;
                    if (decimalCount >= 2) {
                        // No permitir más de dos decimales
                        const numero = String.fromCharCode(evt.which);
                        if (evt.target.selectionStart <= decimalIndex) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
                return true; // Permitir números y punto
            } else {
                return false; // No permitir otros caracteres
            }
        }

        function mayus(e) {
            e.value = e.value.toUpperCase();
        }

        function validaNumericos(event) {
            if (event.charCode >= 48 && event.charCode <= 57) {
                return true;
            }
            return false;
        }

        function sololetras(evento) {
            key = evento.keyCode || evento.which;
            teclado = String.fromCharCode(key).toLocaleLowerCase();
            letras = " áéíóúabcdefghijklmnñopqrstuvwxyz";
            especiales = "8-32-37-38-39-46-164";

            teclado_especial = false;
            for (var i in especiales) {
                if (key == especiales[i]) {
                    teclado_especial = true;
                    break;
                }
            }
            if (letras.indexOf(teclado) == -1 && !teclado_especial) {
                return false;
            }
        }
    </script>
    @yield('scripts')
    @yield('js')
</body>

</html>
