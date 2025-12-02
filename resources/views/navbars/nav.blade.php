<style>
    .sidebar {
        min-height: 100vh;
        background: white;
        box-shadow: 0 4px 24px rgba(33,150,243,0.07);
        border-radius: 1.5rem 0 0 1.5rem;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1040;
        width: 250px;
        transition: left 0.3s;
    }
    .sidebar.collapsed { left: -260px; }
    .sidebar .logo-nav img {
        width: 100%;
        height: 80px;
        display: block;
        margin: 0rem auto;
        filter: drop-shadow(0 2px 8px rgba(33,150,243,0.10));
    }
    .sidebar .nav-link {
        color: #5f6061 !important;
        font-weight: 600;
        border-radius: 0.75rem;
        margin-bottom: 6px;
        transition: background 0.2s, color 0.2s;
        padding: 6px 12px;
        display: flex;
        align-items: center;
        font-size: 0.9rem;
    }
    .sidebar .nav-link.active,
    .sidebar .nav-link:hover {
        background: linear-gradient(90deg, #00bcd4 0%, #21f3e9 100%) !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(33,150,243,0.10);
    }
    .sidebar .icon {
        color: #00bcd4 !important;
        font-size: 1rem !important;
        margin-right: 12px;
        transition: color 0.2s;
    }
    .sidebar .nav-link.active .icon,
    .sidebar .nav-link:hover .icon {
        color: #fff !important;
    }
    @media (max-width: 991px) {
        .sidebar { left: -260px; }
        .sidebar.show { left: 0; }
        .sidebar .logo-nav img { width: 100%; }
    }
    .sidebar-toggle-btn { z-index: 1050; }
    @media (max-width: 991px) {
        .sidebar-toggle-btn { display: block; }
    }
</style>

<nav class="sidebar d-flex flex-column" id="sidebarMenu">
    <div class="logo-nav text-center">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('assets/img/teles2.png') }}" alt="Logo">
        </a>
    </div>
    <ul class="nav flex-column mb-auto">
        @php
            $userType = auth()->user()->type ?? null;
            $firstSucursalName = \App\Models\Sucursal::first()?->name ?? 'default';
            // Normalizar para usar en la URL pública: minusculas y espacios -> guiones
            $firstSucursalSlug = strtolower(trim(str_replace(' ', '-', $firstSucursalName)));
        @endphp
        @if($userType == 'ADMINISTRADOR' || $userType == 'ADMINISTRATIVO')
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'store' || $pageSlug == 'indexStore' || $pageSlug == 'catalog_public') active @endif" data-bs-toggle="collapse" href="#submenuCatalog" role="button" aria-expanded="false" aria-controls="submenuCatalog">
                    <i class="fa-solid fa-store icon"></i>
                    <span>{{ __('Catalogo') }}</span>
                </a>
                <ul class="collapse nav flex-column ms-3" id="submenuCatalog" data-bs-parent="#sidebarMenu">
                    <li class="nav-item">
                        <a href="{{ route('indexStore') }}" class="nav-link @if ($pageSlug == 'store' || $pageSlug == 'indexStore') active-sub-nav @endif">
                            <span>{{ __('Catálogo interno') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('catalog.index', $firstSucursalSlug) }}" class="nav-link @if ($pageSlug == 'catalog_public') active-sub-nav @endif">
                            <span>{{ __('Catálogo público') }}</span>
                        </a>
                    </li>
                </ul>
            </li>
        @elseif($userType == 'EMPLEADO' || $userType == 'SUPERVISOR')
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'store' || $pageSlug == 'indexStore') active @endif" href="{{ route('indexStore') }}">
                    <i class="fa-solid fa-store icon"></i>
                    <span>{{ __('Catalogo') }}</span>
                </a>
            </li>
        @else
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'store' || $pageSlug == 'indexStore') active @endif" href="{{ route('indexStore') }}">
                    <i class="fa-solid fa-store icon"></i>
                    <span>{{ __('Catalogo') }}</span>
                </a>
            </li>
        @endif
        @if (   
            auth()->user()->type == 'ADMINISTRADOR' ||
            auth()->user()->type == 'ADMINISTRATIVO' 
            )
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'order') active @endif" href="{{ route('indexOrder') }}">
                    <i class="fa-solid fa-cart-shopping icon"></i>
                    <span>{{ __('Pedidos') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'company_info') active @endif" href="{{ route('company_info.show') }}">
                    <i class="fa-solid fa-info-circle icon"></i>
                    <span>{{ __('Información') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'sucursal') active @endif" href="{{ route('sucursals.index') }}">
                    <i class="fa-solid fa-location-dot icon"></i>
                    <span>{{ __('Sucursales') }}</span>
                </a>
            </li>
        @endif
        @if (   
            auth()->user()->type == 'ADMINISTRADOR' ||
            auth()->user()->type == 'ADMINISTRATIVO' ||
            auth()->user()->type == 'SUPERVISOR'
            )
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'inventory-transfers') active @endif" href="{{ route('inventory-transfers.index') }}">
                    <i class="fa-solid fa-right-left icon"></i>
                    <span>{{ __('Transferencias') }}</span>
                </a>
            </li>
        @endif
        @if (   
            auth()->user()->type == 'ADMINISTRADOR' ||
            auth()->user()->type == 'ADMINISTRATIVO' 
            )
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'currency') active-nav @endif"
                    href="{{ route('currencies.index') }}">
                    <i class="fas fa-money-bill-wave icon"></i>
                    <span>{{ __('Currency') }}</span>
                </a>
            </li>
        @endif
        @if (
            auth()->user()->type == 'ADMINISTRADOR' ||
            auth()->user()->type == 'ADMINISTRATIVO'
            )
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'category') active @endif"
                    href="{{ route('indexCategory') }}">
                    <i class="fa-solid fa-cash-register icon"></i>
                    <span>{{ __('Categories') }}</span>
                </a>
            </li>
        @endif
        @if (
                auth()->user()->type == 'ADMINISTRADOR' ||
                auth()->user()->type == 'ADMINISTRATIVO' ||
                auth()->user()->type == 'SUPERVISOR' 
            )
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'product' || $pageSlug == 'stocktaking' || $pageSlug == 'shopping' || $pageSlug == 'label') active-nav @endif" data-bs-toggle="collapse"
                    href="#submenuproduct">
                    <i class="fa-solid fa-box icon"></i>
                    <span>{{ __('Products') }}</span>
                </a>
                <ul class="collapse nav flex-column ms-3 submenu-list" id="submenuproduct"
                    data-bs-parent="#sidebarMenu">
                    @if (
                            auth()->user()->type == 'ADMINISTRADOR' ||
                            auth()->user()->type == 'ADMINISTRATIVO' 
                        )
                        <li class="nav-item">
                            <a href="{{ route('indexProduct') }}"
                                class="nav-link @if ($pageSlug == 'product') active-sub-nav @endif">
                                <span>{{ __('Articles') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('shopping.index') }}"
                                class="nav-link @if ($pageSlug == 'shopping') active-sub-nav @endif">
                                <span>{{ __('Shopping') }}</span>
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('indexLabel') }}"
                            class="nav-link @if ($pageSlug == 'label') active-sub-nav @endif">
                            <span>{{ __('Labels') }}</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        @if (
                auth()->user()->type == 'ADMINISTRADOR' || 
                auth()->user()->type == 'ADMINISTRATIVO'
            )
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'userCompany' || $pageSlug == 'userEmployee' || $pageSlug == 'userClient') active @endif" data-bs-toggle="collapse"
                    href="#submenuUsersCompany" role="button" aria-expanded="false"
                    aria-controls="submenuUsersCompany">
                    <i class="fa-solid fa-users-gear icon"></i>
                    <span>{{ __('Users') }}</span>
                </a>
                <ul class="collapse nav flex-column ms-3" id="submenuUsersCompany" data-bs-parent="#sidebarMenu">
                    <li class="nav-item">
                        <a href="{{ route('indexEmployee') }}"
                            class="nav-link @if ($pageSlug == 'userEmployee') active-sub-nav @endif">
                            <span>{{ __('Employees') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('indexClient') }}"
                            class="nav-link @if ($pageSlug == 'userClient') active-sub-nav @endif">
                            <span>{{ __('Clients') }}</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link @if ($pageSlug == 'billing') active @endif"
                href="{{ route('indexBilling') }}">
                <i class="fa-solid fa-cash-register icon"></i>
                <span>{{ __('Billing') }}</span>
            </a>
        </li>
        @if (
                auth()->user()->type == 'ADMINISTRADOR' || 
                auth()->user()->type == 'ADMINISTRATIVO' ||
                auth()->user()->type == 'SUPERVISOR'
            )
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'bill' || $pageSlug == 'credit' || $pageSlug == 'repayment' || $pageSlug == 'stocktaking') active @endif" data-bs-toggle="collapse"
                    href="#submenudocuments" role="button" aria-expanded="false" aria-controls="submenudocuments">
                    <i class="fa-solid fa-folder-open icon"></i>
                    <span>{{ __('Documents') }}</span>
                </a>
                <ul class="collapse nav flex-column ms-3" id="submenudocuments" data-bs-parent="#sidebarMenu">
                    <li class="nav-item">
                        <a href="{{ route('indexBill') }}"
                            class="nav-link @if ($pageSlug == 'bill') active-sub-nav @endif">
                            <span>{{ __('Bills') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('indexCredit') }}"
                            class="nav-link @if ($pageSlug == 'credit') active-sub-nav @endif">
                            <span>{{ __('Credits') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('indexRepayment') }}"
                            class="nav-link @if ($pageSlug == 'repayment') active-sub-nav @endif">
                            <span>{{ __('Repayments') }}</span>
                        </a>
                    </li>
                    @if (
                            auth()->user()->type == 'ADMINISTRADOR' ||
                            auth()->user()->type == 'ADMINISTRATIVO' 
                        )
                        <li class="nav-item">
                            <a href="{{ route('indexInventoryAdjustment') }}"
                                class="nav-link @if ($pageSlug == 'stocktaking') active-sub-nav @endif">
                                <span>{{ __('Ajuste de Inventario') }}</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif
        @if (
                auth()->user()->type == 'ADMINISTRADOR' ||
                auth()->user()->type == 'ADMINISTRATIVO' ||
                auth()->user()->type == 'SUPERVISOR' ||
                auth()->user()->type == 'EMPLEADO' 
            )
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'closure' || $pageSlug == 'closureIndividual') active @endif" data-bs-toggle="collapse"
                    href="#submenuclosure" role="button" aria-expanded="false" aria-controls="submenuclosure">
                    <i class="fa-solid fa-file-invoice icon"></i>
                    <span>{{ __('Closures') }}</span>
                </a>
                <ul class="collapse nav flex-column ms-3" id="submenuclosure" data-bs-parent="#sidebarMenu">
                    @if (
                            auth()->user()->type == 'ADMINISTRADOR' ||
                            auth()->user()->type == 'ADMINISTRATIVO' ||
                            auth()->user()->type == 'SUPERVISOR'
                        )
                        <li class="nav-item">
                            <a href="{{ route('closures.index') }}"
                                class="nav-link @if ($pageSlug == 'closure') active-sub-nav @endif">
                                <span>{{ __('Global') }}</span>
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('individualClosures.index') }}"
                            class="nav-link @if ($pageSlug == 'closureIndividual') active-sub-nav @endif">
                            <span>{{ __('Individual') }}</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        @if (
                auth()->user()->type == 'ADMINISTRADOR' ||
                auth()->user()->type == 'ADMINISTRATIVO'
            )
            <li class="nav-item">
                <a class="nav-link @if ($pageSlug == 'paymentMethod') active @endif"
                    href="{{ route('payment-methods.index') }}">
                    <i class="fa-regular fa-credit-card icon"></i>
                    <span>{{ __('Payment Methods') }}</span>
                </a>
            </li>
        @endif
        @if (
                auth()->user()->type == 'ADMINISTRADOR' ||
                auth()->user()->type == 'ADMINISTRATIVO'
            )
            <li class="nav-item">
                <a class="nav-link @if (
                    $pageSlug == 'report' ||
                        $pageSlug == 'serviceCategory' ||
                        $pageSlug == 'productIndex' ||
                        $pageSlug == 'indexProductPDF') active @endif" data-bs-toggle="collapse"
                    href="#submenuReportsMain" role="button" aria-expanded="false"
                    aria-controls="submenuReportsMain">
                    <i class="fa-solid fa-chart-line icon"></i>
                    <span>{{ __('Reports') }}</span>
                </a>
                <ul class="collapse nav flex-column ms-3" id="submenuReportsMain" data-bs-parent="#sidebarMenu">
                    <li class="nav-item">
                        <a class="nav-link @if ($pageSlug == 'indexProductPDF') active-sub-nav @endif"
                            data-bs-toggle="collapse" href="#submenuPdfReports" role="button" aria-expanded="false"
                            aria-controls="submenuPdfReports">
                            <i class="fa-solid fa-file-pdf icon"></i>
                            <span>{{ __('Pdf') }}</span>
                        </a>
                        <ul class="collapse nav flex-column ms-3" id="submenuPdfReports"
                            data-bs-parent="#submenuReportsMain">
                            <li class="nav-item">
                                <a href="{{ route('indexProductPDF') }}"
                                    class="nav-link @if ($pageSlug == 'indexProductPDF') active-sub-nav @endif">
                                    <span>{{ __('Products') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('indexBillPDF') }}"
                                    class="nav-link @if ($pageSlug == 'indexBillPDF') active-sub-nav @endif">
                                    <span>{{ __('Bills') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('indexProfitPDF') }}"
                                    class="nav-link @if ($pageSlug == 'indexProfitPDF') active-sub-nav @endif">
                                    <span>{{ __('Ganancias') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('indexCreditPDF') }}"
                                    class="nav-link @if ($pageSlug == 'indexCreditPDF') active-sub-nav @endif">
                                    <span>{{ __('Cuentas por Cobrar') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('indexEmployeePDF') }}"
                                    class="nav-link @if ($pageSlug == 'indexEmployeePDF') active-sub-nav @endif">
                                    <span>{{ __('Employees') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if ($pageSlug == 'productIndex') active-sub-nav @endif"
                            data-bs-toggle="collapse" href="#submenuChartReports" role="button"
                            aria-expanded="false" aria-controls="submenuChartReports">
                            <i class="fa-solid fa-chart-pie icon"></i>
                            <span>{{ __('Graphics') }}</span>
                        </a>
                        <ul class="collapse nav flex-column ms-3" id="submenuChartReports"
                            data-bs-parent="#submenuReportsMain">
                            <li class="nav-item">
                                <a href="{{ route('productIndex') }}"
                                    class="nav-link @if ($pageSlug == 'productIndex') active-sub-nav @endif">
                                    <span>{{ __('Products') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('billIndex') }}"
                                    class="nav-link @if ($pageSlug == 'billIndex') active-sub-nav @endif">
                                    <span>{{ __('Bills') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('paymentIndex') }}"
                                    class="nav-link @if ($pageSlug == 'paymentIndex') active-sub-nav @endif">
                                    <span>{{ __('Ganancias') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('creditIndex') }}"
                                    class="nav-link @if ($pageSlug == 'creditIndex') active-sub-nav @endif">
                                    <span>{{ __('Cuentas por Cobrar') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('employeeIndex') }}"
                                    class="nav-link @if ($pageSlug == 'employeeIndex') active-sub-nav @endif">
                                    <span>{{ __('Employees') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link @if ($pageSlug == 'profile') active @endif" href="{{ route('indexProfile') }}">
                <i class="fa-regular fa-user-circle icon"></i>
                <span>{{ __('Profile') }}</span>
            </a>
        </li>
    </ul>
</nav>
    

    @if ($pageSlug == 'billing')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidenav = document.getElementById('sidebarMenu');
        let isSidenavVisible = true;
        const body = document.body;

        function setFullWidth(full) {
            if (full) {
                body.classList.add('billing-fullwidth');
            } else {
                body.classList.remove('billing-fullwidth');
            }
        }

        document.addEventListener('mousemove', function(event) {
            if (event.clientX < 5 && !isSidenavVisible) {
                sidenav.style.left = '0';
                isSidenavVisible = true;
                setFullWidth(false);
            }
            else if (event.clientX > sidenav.offsetWidth + 10 && isSidenavVisible) {
                sidenav.style.left = `-${sidenav.offsetWidth}px`;
                isSidenavVisible = false;
                setFullWidth(true);
            }
        });

        document.addEventListener('click', function(event) {
            if (isSidenavVisible && !sidenav.contains(event.target) && event.clientX > sidenav.offsetWidth) {
                sidenav.style.left = `-${sidenav.offsetWidth}px`;
                isSidenavVisible = false;
                setFullWidth(true);
            }
        });

        // Al cargar la página, oculta el menú y expande el contenido
        sidenav.style.left = `-${sidenav.offsetWidth}px`;
        isSidenavVisible = false;
        setFullWidth(true);
    });
</script>
@endif

