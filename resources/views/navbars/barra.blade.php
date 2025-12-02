<style>
    .navbar-main {
        background: #fff !important;
        box-shadow: 0 2px 8px rgba(33, 150, 243, 0.07);
        border-radius: 1.5rem;
        position: fixed;
        top: 0;
        left: 250px;
        width: calc(100% - 250px);
        z-index: 1000;
        padding: 0.8rem 1.5rem;
        min-height: 50px;
        display: flex;
        align-items: center;
    }

   /* Ajustes para que el select de sucursal quede inline y luzca más profesional */
.breadcrumb {
    display: flex;
    gap: 0.6rem;
    align-items: center;
    flex-wrap: nowrap; /* evita que el select baje de línea */
    font-size: 0.9rem;
}

.breadcrumb .sucursal-item {
    display: flex;
    align-items: center;
}

/* Estilo del select */
    .sucursal-select {
        min-width: 120px;
        max-width: 260px;
        width: 100%;
        height: 36px;
        padding: 0.35rem 1.1rem 0.35rem 0.6rem;
        font-size: 0.85rem;
        border-radius: 0.5rem;
        border: 1px solid #e6eef8;
        background: #fff url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="%23666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>') no-repeat calc(100% - 10px) center;
        background-size: 12px;
        box-shadow: 0 2px 6px rgba(33,150,243,0.04);
        color: #0f1724;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        transition: min-width 0.2s, max-width 0.2s, width 0.2s;
    }

/* tamaño pequeño estilo bootstrap */
.sucursal-select.form-select-sm {
    height: 34px;
    padding-top: 0.28rem;
    padding-bottom: 0.28rem;
}

/* Si el ancho es pequeño, permitir wrapping y poner el select en bloque */
@media (max-width: 991px) {
    .breadcrumb { flex-wrap: wrap; gap: 0.4rem; }
    .sucursal-select {
        min-width: 70px !important;
        max-width: 120px !important;
        width: 100% !important;
        font-size: 0.78rem !important;
        padding-left: 0.4rem !important;
        padding-right: 0.4rem !important;
    }
}
    .btn-barra,
    .btn-danger2 {
        font-size: 1.08rem !important;
        padding: 6px 18px !important;
        border-radius: 0.75rem !important;
        margin-left: 10px !important;
        background: linear-gradient(90deg, #2196f3 0%, #00bcd4 100%) !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(33, 150, 243, 0.10);
        border: none !important;
        font-weight: 600 !important;
        transition: background 0.2s, box-shadow 0.2s;
    }

    .btn-barra:hover,
    .btn-danger2:hover{
        background: linear-gradient(90deg, #1565c0 0%, #0097a7 100%) !important;
        color: #fff !important;
        box-shadow: 0 4px 16px rgba(33, 150, 243, 0.18);
    }

    .navbar-nav {
        flex-direction: row !important;
        align-items: center;
        gap: 0.7rem;
    }

    .navbar-nav .nav-item {
        margin: 0 !important;
    }

    .sidebar-toggle-btn {
        display: none;
    }

    @media (max-width: 991px) {
        .navbar-nav {
            margin-left: auto !important;
            flex-direction: row !important;
            justify-content: flex-end !important;
            width: auto !important;
        }

        .navbar-main {
            left: 0;
            width: 100%;
            border-radius: 1rem;
            padding: 0.5rem 0.5rem;
            min-height: 56px;
            flex-direction: column;
            align-items: stretch;
        }

        .container-fluid {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.5rem !important;
        }

        .sidebar-toggle-btn {
            display: inline-block !important;
        }
        .btn-sm {
            padding: 0.3rem 0.4rem !important;
        }
        i {
            font-size: 0.9rem !important;
        }
        .breadcrumb-item{
            font-size: 0.7rem !important;
        }
    }
</style>
<nav class="navbar navbar-main navbar-expand-lg shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-0 px-0 d-flex flex-wrap align-items-center justify-content-between">
        <div class="d-flex flex-wrap align-items-center w-100" style="gap: 1rem;">
            <nav aria-label="breadcrumb" class="me-2 flex-grow-1">
                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-0 px-0">
                    <li class="breadcrumb-item">{{ $pageTitles[$pageSlug] ?? 'Pagina' }}</li>
                    <li class="breadcrumb-item breadcrumb-user">{{ auth()->user()->type }}</li>
                    <li class="breadcrumb-item breadcrumb-user">{{ auth()->user()->name }}</li>
                    @if ($pageSlug != 'catalog')
                        @auth
                            <li class="breadcrumb-item sucursal-item">
                                <select id="sucursalSelect" class="form-select form-select-sm sucursal-select" onchange="changeSucursal()">
                                    @foreach($sucursals as $sucursal)
                                        <option value="{{ $sucursal->id }}" {{ session('selected_sucursal') == $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </li>
                        @endauth
                    @else
                        @auth
                            @php
                                // convertir priceType interno (price2/price3) a alias usado en UI
                                $priceAlias = $priceType;
                                if ($priceAlias === 'price2') $priceAlias = 'mayorista';
                                if ($priceAlias === 'price3') $priceAlias = 'mayorista2';
                            @endphp
                            <li class="breadcrumb-item sucursal-item">
                                <select id="sucursal-select" class="form-select form-select-sm sucursal-select">
                                    @foreach($sucursals as $sc)
                                        @php $val = $sc->name; @endphp
                                        <option value="{{ $val }}" {{ isset($sucursal) && ($sucursal->name ?? '') == $sc->name ? 'selected' : '' }}>{{ $sc->name }}</option>
                                    @endforeach
                                </select>
                            </li>
                            <li class="breadcrumb-item sucursal-item">
                                <select id="price-type-select" class="form-select form-select-sm sucursal-select">
                                    <option value="detal" {{ (isset($priceAlias) && $priceAlias == 'detal') ? 'selected' : '' }}>Precio detal</option>
                                    <option value="price" {{ (isset($priceAlias) && $priceAlias == 'price') ? 'selected' : '' }}>Precio 1</option>
                                    <option value="mayorista" {{ (isset($priceAlias) && $priceAlias == 'mayorista') ? 'selected' : '' }}>Mayorista</option>
                                    <option value="mayorista2" {{ (isset($priceAlias) && $priceAlias == 'mayorista2') ? 'selected' : '' }}>Mayorista 2</option>
                                </select>
                           </li>
                        @endauth          
                    @endif
                </ol>
            </nav>

            <ul class="navbar-nav justify-content-end align-items-center flex-wrap ms-auto" style="flex-wrap:nowrap;">
                @if ($pageSlug == 'store')
                    <li class="nav-item">
                        <a onclick="downloadCatalog()" class="btn btn-danger2 btn-sm" title="Descargar catálogo">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </li>
                @endif
                <li class="nav-item">
                    <button class="sidebar-toggle-btn btn btn-danger2 d-block d-lg-none btn-sm" id="sidebarToggle"
                        title="Menú">
                        <i class="fa fa-bars"></i>
                    </button>
                </li>
                <li class="nav-item">
                    <a class="btn btn-danger2 btn-sm" href="{{ route('logout') }}" title="Salir">
                        <i class="fa fa-power-off"></i>
                    </a>
                </li>
            </ul>
            @if ($pageSlug == 'store')
                <div class="row g-2 w-100 align-items-center buscadorCategorias">
                    <div class="col-6 col-sm-6 col-md-2">
                        <select id="sort-by-stock" class="form-select" onchange="refreshProductList()">
                            <option value="date">Por fecha</option>
                            <option value="asc">Menor a Mayor Existencia</option>
                            <option value="desc">Mayor a Menor Existencia</option>
                            <option value="available">Existencia Mayor a 0</option>
                            <option value="unavailable">Producto sin Existencia</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-6 col-md-2">
                        <select onChange="refreshProductList()" class="form-select catego cate" name="id_category"
                            id="category" data-placeholder="{{ __('Categories') }}">
                            <option></option>
                            <option value="TODAS">{{ __('ALL') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-9 col-sm-9 col-md-6">
                        <input id="buscador" type="text" class="form-control buscar"
                            placeholder="{{ __('Buscar...') }}" onKeyup="refreshProductList()">
                    </div>
                    <div class="col-3 col-sm-3 col-md-2">
                    <select id="id_currencyStore" name="id_currencyStore" class="form-select"  onchange="refreshProductList()">
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency->id }}"
                                data-tasa="{{ $currency->rate }}"
                                data-abbr="{{ $currency->abbreviation }}"
                                {{ $currency->is_official == 1 ? 'selected' : '' }}>
                                {{ $currency->abbreviation }}
                            </option>
                        @endforeach
                    </select>
                    </div>
                </div>
            @endif
        </div>

    </div>
</nav>
<script>
    // Mostrar/ocultar sidebar en móvil
    document.querySelectorAll('#sidebarToggle').forEach(function(btn) {
        btn.onclick = function() {
            var sidebar = document.getElementById('sidebarMenu');
            sidebar.classList.toggle('show');
        };
    });
    // Opcional: ocultar sidebar al hacer click fuera en móvil
    document.addEventListener('click', function(e) {
        var sidebar = document.getElementById('sidebarMenu');
        var toggleBtns = document.querySelectorAll('#sidebarToggle');
        if (window.innerWidth <= 991) {
            if (!sidebar.contains(e.target) && !Array.from(toggleBtns).some(btn => btn.contains(e.target))) {
                sidebar.classList.remove('show');
            }
        }
    });
    function changeSucursal() {
    const selectedSucursal = document.getElementById('sucursalSelect').value;
    fetch('/set-sucursal', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ sucursal_id: selectedSucursal })
    }).then(response => {
        if (response.ok) {
            location.reload();
        }
    });
}
</script>
