@extends('app', ['page' => __('Catalogo'), 'pageSlug' => 'catalog'])
@section('content')
    <div class="container py-4 catalog-section">
        <style>
            /* Solo detalles visuales mínimos, todo el layout con Bootstrap */
            .catalog-card {
                border-radius: 14px;
                box-shadow: 0 2px 12px rgba(0,0,0,.07);
                border: 1px solid #e9ecef;
                background: #fff;
                transition: box-shadow .18s, transform .13s;
            }
            .catalog-card:hover {
                transform: translateY(-4px) scale(1.01);
                box-shadow: 0 8px 32px rgba(0,0,0,.13);
            }
            .catalog-img {
                object-fit: cover;
                background: #f8f9fa;
                border-radius: 12px;
            }
            .product-stock { font-weight: 700; color: #198754; }
            .catalog-price { font-size: 1.18rem; color: #0d6efd; font-weight: 700; }
            .product-price { font-size: 1.45rem; font-weight: 800; color: #0d6efd; }
            .floating-btn { position: fixed; right: 20px; bottom: 20px; z-index: 1050; width:56px; height:56px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; box-shadow:0 6px 18px rgba(0,0,0,.18); }
            .floating-btn.whatsapp { background: #25D366; }
            .floating-btn.cart { background: #0d6efd; right: 90px; }
            .badge-cart { background:#fff; color:#0d6efd; border-radius:50%; padding:3px 7px; font-weight:700; position: absolute; top: -6px; right: -6px; font-size:12px; }
            .filter-bar { border-radius: 10px; background: rgba(255,255,255,0.85); }
        </style>
        <div class="card p-3 mb-4 glass filter-bar">
            <div class="row align-items-center mb-3 g-2">
                <div class="col-12 col-md-3 filter-left">
                    <select id="filter-category" class="form-select">
                        <option value="">Todas las categorías</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 filter-left">
                    @php
                        $principal = $currencies->where('is_principal', 1)->first();
                    @endphp
                    <select id="filter-currency" class="form-select">
                        @foreach ($currencies as $cur)
                            <option value="{{ $cur->id }}" data-rate="{{ $cur->rate }}" data-abbr="{{ $cur->abbreviation }}" {{ $principal && $principal->id == $cur->id ? 'selected' : '' }}>
                                {{ $cur->name }} ({{ $cur->abbreviation }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 filter-right">
                    <div class="input-group">
                        <input type="text" id="filter-search" class="form-control" placeholder="Buscar producto...">
                        <button id="btn-download-pdf" class="btn btn-outline-secondary" type="button" title="Descargar catálogo en PDF">
                            <i class="fa fa-file-pdf-o" aria-hidden="true"></i> PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @php
            $requires_password = auth()->guest() && in_array($priceType, ['price2','price3']) && !session("catalog_unlocked.{$priceType}");
        @endphp
        @php
            // slug usado para generar rutas limpias (minusculas, espacios -> guiones)
            $sucursalSlug = isset($sucursal) ? strtolower(trim(str_replace(' ', '-', $sucursal->name))) : null;
        @endphp
        <div class="row g-3" id="catalog-products" style="{{ $requires_password ? 'display:none;' : '' }}">
            @foreach ($items as $p)
                @php
                    $imgCandidate = $p->image ?? '';
                    $imgUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($imgCandidate) && $imgCandidate
                        ? asset('storage/' . $imgCandidate)
                        : asset('storage/products/product.jpg');
                @endphp
                <div class="col-12 col-sm-6 col-md-3 mb-3 product-card" data-category="{{ $p->category_id }}" data-name="{{ strtolower($p->name) }}" data-id="{{ $p->id }}" data-link="{{ url('/') . '?producto=' . $p->id }}" data-price="{{ $p->price }}" data-price2="{{ $p->basePrice }}">
                    <div class="card catalog-card h-100">
                        <!-- Desktop: imagen arriba, info abajo. Móvil: imagen izq, info der -->
                        <div class="d-block d-md-none">
                            <div class="row g-0 align-items-center">
                                <div class="col-4 text-center">
                                    <img src="{{ $imgUrl }}" onerror="this.onerror=null;this.src='{{ asset('storage/products/product.jpg') }}'" class="catalog-img img-fluid" style="width: 70px; height: 70px;">
                                </div>
                                <div class="col-8 ps-2">
                                    <h6 class="mb-1 fw-bold text-truncate" title="{{ $p->name }}">
                                        <a href="{{ url('/') . '?producto=' . $p->id }}" class="product-link text-decoration-none text-dark">{{ $p->name }}</a>
                                    </h6>
                                    <div class="small text-muted mb-1">{{ optional($p)->category?->name ?? '' }}</div>
                                    <div class="mb-1">Existencia: <span class="product-stock" data-stock="{{ floor($p->stock) }}">{{ number_format(floor($p->stock), 0) }}</span></div>
                                    <div class="catalog-price mb-1">
                                        <span class="product-price">{{ number_format($p->price ?? ($p->basePrice ?? 0), 2) }} {{ isset($principal) && $principal ? $principal->abbreviation : '' }}</span>
                                    </div>
                                    <button class="btn btn-success btn-add-cart btn-sm w-100 mt-1" data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->price }}" data-img="{{ $imgUrl }}">
                                        <i class="fa fa-cart-plus"></i> Agregar al carrito
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="d-none d-md-flex flex-column h-100">
                                <div class="text-center pt-3 px-3">
                                    <img src="{{ $imgUrl }}" onerror="this.onerror=null;this.src='{{ asset('storage/products/product.jpg') }}'" class="catalog-img img-fluid" style="height: 120px; width: 100%; max-width: 120px;">
                                </div>
                            <div class="card-body d-flex flex-column align-items-stretch p-2">
                                <h6 class="mb-1 fw-bold text-truncate" title="{{ $p->name }}">
                                    <a href="{{ url('/') . '?producto=' . $p->id }}" class="product-link text-decoration-none text-dark">{{ $p->name }}</a>
                                </h6>
                                <div class="small text-muted mb-1">{{ optional($p)->category?->name ?? '' }}</div>
                                <div class="mb-1">Existencia: <span class="product-stock" data-stock="{{ floor($p->stock) }}">{{ number_format(floor($p->stock), 0) }}</span></div>
                                <div class="catalog-price mb-2">
                                    <span class="product-price">{{ number_format($p->price ?? ($p->basePrice ?? 0), 2) }} {{ isset($principal) && $principal ? $principal->abbreviation : '' }}</span>
                                </div>
                                <button class="btn btn-success btn-add-cart w-100 mt-auto" data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->price }}" data-img="{{ $imgUrl }}">
                                    <i class="fa fa-cart-plus"></i> Agregar al carrito
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <!-- Modal de información de producto -->
    <div id="product-info-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modal-product-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row align-items-center">
                        <div class="col-md-5 text-center">
                            <img id="modal-product-img" src="" class="img-fluid modal-product-img">
                        </div>
                        <div class="col-md-7">
                            <p class="mb-2" id="modal-product-category"></p>
                            <p class="mb-2" id="modal-product-stock"></p>
                            <p class="mb-2 fw-bold fs-3 text-dark" id="modal-product-price"></p>
                            <div id="modal-product-description" class="mb-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carrito flotante -->
    <button id="btn-cart" class="floating-btn cart d-flex align-items-center" title="Ver carrito">
        <!-- Inline SVG fallback + Font Awesome icon (if loaded) -->
        <i class="fa-solid fa-cart-shopping fa-cart-shopping2" aria-hidden="true"></i>
        <span id="cart-count" class="badge-cart">0</span>
    </button>
    <div id="cart-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Carrito de compras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="cart-items"></div>
                </div>
                <div class="modal-footer flex-column flex-sm-row">
                    <button id="btn-whatsapp-order" class="btn btn-success mb-2 mb-sm-0 me-sm-2 w-100 w-sm-auto">
                        <i class="fab fa-whatsapp"></i> Pedir productos por WhatsApp
                    </button>
                    <button class="btn btn-secondary w-100 w-sm-auto" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de checkout: pedir nombre, nacionalidad y CI -->
    <div id="checkout-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Cédula / RIF</label>
                        <div class="d-flex gap-2 align-items-center">
                            <div style="min-width:92px;">
                                <select id="order-nationality" class="form-select">
                                    <option value="V">V</option>
                                    <option value="E">E</option>
                                    <option value="J">J</option>
                                </select>
                            </div>
                            <div class="flex-grow-1">
                                <input id="order-ci" class="form-control" type="text" placeholder="Ingrese CI o RIF">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nombre</label>
                        <input id="order-name" class="form-control" type="text" placeholder="Nombre" onkeyup="mayus(this);">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button id="btn-confirm-order" type="button" class="btn btn-primary">Confirmar y enviar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Botón flotante de WhatsApp -->
    @php
        $companyPhone = $company->phone ?? '+584168835169';
    @endphp
    <a href="https://wa.me/{{ $companyPhone }}" target="_blank" id="btn-whatsapp-float" class="floating-btn whatsapp d-flex align-items-center justify-content-center" title="Contactar por WhatsApp">
          <i class="fab fa-whatsapp"></i>
        <span class="visually-hidden">WhatsApp</span>
    </a>

    {{-- Modal de contraseña para precios restringidos (invitados) --}}
    @if(isset($requires_password) && $requires_password)
        <div class="modal fade" id="catalog-password-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Acceso a precios restringidos</h5>
                    </div>
                    <div class="modal-body">
                        <p>Introduce la contraseña para ver los precios seleccionados.</p>
                        <div class="mb-3">
                            <input type="password" id="catalog-password-input" class="form-control" placeholder="Contraseña">
                        </div>
                        <div id="catalog-password-error" class="text-danger small" style="display:none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="catalog-password-cancel">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="catalog-password-submit">Enviar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('scripts')
    <script type="text/javascript">
    const products = @json(collect($items)->keyBy('id'));
        // Normalizar stock: forzar entero hacia abajo para cada producto (evita decimales en la UI)
        Object.keys(products).forEach(k => {
            let p = products[k];
            if (p && typeof p.stock !== 'undefined') {
                p.stock = Math.floor(parseFloat(p.stock) || 0);
            }
        });
    const currencies = @json(collect($currencies)->keyBy('id'));
    const priceType = '{{ $priceAlias ?? $priceType }}';
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');

        function updateProductPrices() {
            const selectedCurrencyId = $('#filter-currency').val();
            const selectedCurrency = currencies[selectedCurrencyId];
            $('.product-card').each(function() {
                const $price = $(this).find('.product-price');
                const id = $(this).data('id');
                const p = products[id];
                // Use the computed price (already includes sucursal percent) and convert by currency rate
                const basePrice = parseFloat(p.price ?? p.basePrice ?? 0);
                const finalPrice = basePrice * (selectedCurrency?.rate ?? 1);
                $price.text(finalPrice.toFixed(2) + ' ' + (selectedCurrency?.abbreviation ?? ''));
            });
        }

        function renderCart() {
            let html = '';
            let selectedCurrencyId = $('#filter-currency').val();
            let selectedCurrency = currencies[selectedCurrencyId];
            let total = 0;
            if (cart.length === 0) {
                html = '<p class="text-center text-muted">No hay productos en el carrito.</p>';
            } else {
                html =
                    '<div class="table-responsive"><table class="table table-bordered align-middle"><thead><tr><th></th><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th><th></th></tr></thead><tbody>';
                cart.forEach((item, idx) => {
                    let product = products[item.id];
                    let price = parseFloat(product.price ?? product.basePrice ?? 0) * (selectedCurrency?.rate ?? 1);
                    // forzar que el subtotal use cantidades enteras
                    let qtyInt = parseInt(item.qty, 10) || 0;
                    let subtotal = price * qtyInt;
                    total += subtotal;
                    // asegurar que el atributo max sea entero (redondear hacia abajo)
                    let maxAttr = product.stock ? `max="${Math.floor(product.stock)}"` : '';
                    html += `<tr>
                <td><img src="${item.img}" style="width:50px;height:40px;object-fit:cover;"></td>
                <td class="text-truncate" style="max-width:120px;">${item.name}</td>
                <td>
                    <input type="number" min="1" step="1" ${maxAttr} value="${qtyInt}" class="form-control form-control-sm cart-qty" data-idx="${idx}" style="width:70px;">
                </td>
                <td>${price.toFixed(2)} ${selectedCurrency?.abbreviation ?? ''}</td>
                <td>${subtotal.toFixed(2)} ${selectedCurrency?.abbreviation ?? ''}</td>
                <td><button class="btn btn-danger btn-sm btn-remove-cart" data-idx="${idx}"><i class="fa fa-trash"></i></button></td>
            </tr>`;
                });
                html +=
                    `<tfoot><tr><th colspan="4" class="text-end">Total</th><th colspan="2">${total.toFixed(2)} ${selectedCurrency?.abbreviation ?? ''}</th></tr></tfoot>`;
                html += '</tbody></table></div>';
            }
            $('#cart-items').html(html);
            $('#cart-count').text(cart.length);
            localStorage.setItem('cart', JSON.stringify(cart));
        }

        function filterProducts() {
            let cat = $('#filter-category').val();
            let search = $('#filter-search').val().toLowerCase();
            $('.product-card').each(function() {
                let show = true;
                if (cat && $(this).data('category') != cat) show = false;
                if (search && !$(this).data('name').includes(search)) show = false;
                $(this).toggle(show);
            });
        }
        // --- MODAL PRODUCTO POR LINK ---
        function openProductModalById(id) {
            let product = products[id];
            if (!product) return;
            let currencyId = $('#filter-currency').val();
            let currency = currencies[currencyId];
            let base = parseFloat(product.price ?? product.basePrice ?? 0);
            let price = (base * (currency?.rate ?? 1)).toFixed(2) + ' ' + (currency?.abbreviation ?? '');
            $('#modal-product-title').text(product.name);
            $('#modal-product-img').attr('src', product.image ? '/storage/' + product.image :
                'https://via.placeholder.com/300x180?text=Sin+Imagen');
            $('#modal-product-category').html('<strong>Categoría:</strong> ' + (product.category ? product.category.name :
                ''));
            $('#modal-product-stock').html('<strong>Existencia:</strong> ' + (product.stock ?? 0));
            $('#modal-product-price').text(price);
            $('#modal-product-description').html(product.description ? product.description :
                '<span class="text-muted">Sin descripción</span>');
            const modal = new bootstrap.Modal(document.getElementById('product-info-modal'));
            modal.show();
        }
        // Detectar si hay ?producto= en la URL al cargar
        const urlParams = new URLSearchParams(window.location.search);
        const prodId = urlParams.get('producto');
        if (prodId && products[prodId]) {
            setTimeout(() => openProductModalById(prodId), 500);
        }
        document.addEventListener('DOMContentLoaded', function() {
            updateProductPrices();
            filterProducts();

            // Construir URL para descargar catálogo en PDF usando filtros actuales
            function buildPdfUrl() {
                let base = '{{ url('sucursal') }}';
                let scName = '{{ $sucursalSlug ?? ($sucursal->id ?? '') }}';
                scName = String(scName || '').toLowerCase().trim().replace(/\s+/g, '-');
                let pt = encodeURIComponent(priceType || '');
                let url = base + '/' + scName + '/catalog-pdf' + (pt ? '/' + pt : '');
                let params = new URLSearchParams();
                let cat = $('#filter-category').val();
                let q = $('#filter-search').val();
                let cur = $('#filter-currency').val();
                if (cat) params.set('category', cat);
                if (q) params.set('q', q);
                if (cur) params.set('currency', cur);
                let qs = params.toString();
                if (qs) url += '?' + qs;
                return url;
            }

            $('#btn-download-pdf').on('click', function() {
                let url = buildPdfUrl();
                window.open(url, '_blank');
            });
            renderCart();
            // Deshabilitar botón agregar si no hay stock y asegurar máximo en los botones
            $('.btn-add-cart').each(function() {
                let id = $(this).data('id');
                let st = products[id]?.stock ?? 0;
                if (parseFloat(st) <= 0) {
                    $(this).prop('disabled', true).removeClass('btn-success').addClass('btn-secondary')
                        .text('Agotado');
                }
            });
            $('#filter-currency').on('change', function() {
                updateProductPrices();
                renderCart();
            });
            $('#filter-category, #filter-search').on('input change', filterProducts);
            // Sucursal y tipo de precio (solo si el servidor renderizó los selects - se muestran solo para usuarios autenticados)
            if ($('#sucursal-select').length) {
                // build URL and redirect to route /sucursal/{id}/{priceType?} preserving filters as query params
                function buildCatalogUrl(sucursalId, priceType) {
                    let base = '{{ url('sucursal') }}';
                    // Normalizar nombre: pasar a minúsculas y reemplazar espacios por guiones
                    // (coincide con la normalización que usa el controlador: LOWER(REPLACE(name,' ','-')) )
                    let scName = String(sucursalId || '').toLowerCase().trim().replace(/\s+/g, '-');
                    let url = base + '/' + scName + (priceType ? '/' + encodeURIComponent(priceType) : '');
                    let params = new URLSearchParams();
                    let cat = $('#filter-category').val();
                    // NOTE: removemos el parámetro "currency" para no agregar ?currency al final
                    let q = $('#filter-search').val();
                    if (cat) params.set('category', cat);
                    if (q) params.set('q', q);
                    let qs = params.toString();
                    if (qs) url += '?' + qs;
                    return url;
                }

                function changeSucursalOrPrice() {
                    let sc = $('#sucursal-select').val();
                    let pt = $('#price-type-select').val();
                    if (!sc) return;
                    window.location.href = buildCatalogUrl(sc, pt);
                }

                $('#sucursal-select').on('change', changeSucursalOrPrice);
                $('#price-type-select').on('change', changeSucursalOrPrice);
            }
            // Mostrar modal de contraseña si es necesario (invitados y price2/price3)
            let needPassword = @json($requires_password ?? false);
            if (needPassword) {
                const modalEl = document.getElementById('catalog-password-modal');
                const passwordModal = new bootstrap.Modal(modalEl, {backdrop: 'static', keyboard: false});
                passwordModal.show();

                $('#catalog-password-cancel').on('click', function() {
                    passwordModal.hide();
                });

                $('#catalog-password-submit').on('click', function() {
                    let pw = $('#catalog-password-input').val();
                    $('#catalog-password-error').hide().text('');
                    if (!pw) {
                        $('#catalog-password-error').text('Ingrese la contraseña').show();
                        return;
                    }
                    $.ajax({
                        url: @json(route('catalog.unlock', $sucursalSlug ?? $sucursal->id ?? null)),
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            password: pw,
                            price_type: priceType
                        },
                        success: function(res) {
                            if (res.success) {
                                // mostrar catálogo y cerrar modal
                                $('#catalog-products').show();
                                passwordModal.hide();
                                Swal.fire({icon: 'success', title: 'Acceso concedido', timer: 1200, showConfirmButton: false});
                            } else {
                                $('#catalog-password-error').text(res.message || 'Contraseña incorrecta').show();
                            }
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message || 'Error al verificar la contraseña';
                            $('#catalog-password-error').text(msg).show();
                        }
                    });
                });
            }
            $('.btn-add-cart').on('click', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let img = $(this).data('img');
                let idx = cart.findIndex(i => i.id == id);
                let available = parseFloat(products[id]?.stock ?? 0);
                const addStep = 1; // al hacer clic en el botón, agregar 1 unidad
                if (idx >= 0) {
                    // verificar que no supere stock; trabajar con enteros y redondear hacia abajo
                    let currentQty = parseInt(cart[idx].qty, 10) || 0;
                    let newQty = currentQty + addStep;
                    let availableInt = Math.floor(available);
                    if (newQty > availableInt) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stock insuficiente',
                            text: `No puedes agregar más de ${availableInt} unidad(es) de "${name}".`,
                        });
                        return;
                    }
                    cart[idx].qty = newQty;
                    Swal.fire({
                        icon: 'success',
                        title: 'Cantidad actualizada',
                        text: `"${name}" ahora tiene cantidad ${cart[idx].qty} en el carrito.`,
                        timer: 1200,
                        showConfirmButton: false
                    });
                } else {
                    if (available <= 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Agotado',
                            text: `El producto "${name}" no tiene existencia disponible.`,
                        });
                        return;
                    }
                    cart.push({
                        id,
                        name,
                        qty: 1,
                        img
                    });
                    Swal.fire({
                        icon: 'success',
                        title: 'Producto agregado',
                        text: `"${name}" se agregó al carrito.`,
                        timer: 1200,
                        showConfirmButton: false
                    });
                }
                renderCart();
            });
            $('#btn-cart').on('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('cart-modal'));
                modal.show();
            });

            // Al salir de campo CI: buscar cliente y autocompletar nombre si existe
            $('#order-ci').on('blur', function() {
                let ciVal = $(this).val().trim();
                if (!ciVal) return;
                $.ajax({
                    url: '{{ route("catalog.clientLookup", $sucursalSlug ?? $sucursal->id ?? null) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ci: ciVal
                    },
                    success: function(res) {
                        if (res.found && res.client) {
                            $('#order-name').val(res.client.name || '');
                            if (res.client.nationality) $('#order-nationality').val(res.client.nationality);
                        }
                    }
                });
            });
            $(document).on('click', '.btn-remove-cart', function() {
                let idx = $(this).data('idx');
                cart.splice(idx, 1);
                renderCart();
            });

            // Validar también cuando el usuario sale del input (blur)
            $(document).on('blur', '.cart-qty', function() {
                let idx = $(this).data('idx');
                // forzar entero y redondear hacia abajo
                let qty = parseInt($(this).val(), 10);
                if (isNaN(qty) || qty < 1) qty = 1;
                let productId = cart[idx]?.id;
                let available = Math.floor(parseFloat(products[productId]?.stock ?? 0));
                if (qty > available) {
                    qty = available;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock insuficiente',
                        text: `No puedes elegir más de ${available} unidad(es).`,
                    });
                }
                // asignar entero
                if (typeof idx !== 'undefined' && cart[idx]) {
                    cart[idx].qty = qty;
                }
                renderCart();
            });
            // Abrir modal de checkout para pedir datos antes de enviar por WhatsApp
            $('#btn-whatsapp-order').on('click', function() {
                if (cart.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Carrito vacío',
                        text: 'Agrega productos al carrito antes de pedir.'
                    });
                    return;
                }
                const modal = new bootstrap.Modal(document.getElementById('checkout-modal'));
                modal.show();
            });

            // Confirmar pedido: crear bill y bill_details, luego abrir WhatsApp
            $('#btn-confirm-order').on('click', function() {
                let name = $('#order-name').val().trim();
                let nationality = $('#order-nationality').val();
                let ci = $('#order-ci').val().trim();
                if (!name || !nationality || !ci) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Datos incompletos',
                        text: 'Por favor llena nombre, nacionalidad y CI.'
                    });
                    return;
                }

                // Preparar items para enviar al backend (incluir price_type según la URL)
                let items = cart.map(i => ({
                    id: i.id,
                    qty: i.qty,
                    price_type: priceType || 'price'
                }));

                $.ajax({
                    url: '{{ route("catalog.order", $sucursalSlug ?? $sucursal->id ?? null) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        name: name,
                        nationality: nationality,
                        ci: ci,
                        items: items,
                        selected_currency_id: $('#filter-currency').val()
                    },
                    success: function(res) {
                        if (res.success) {
                            // Armar mensaje de WhatsApp con totales en moneda seleccionada y en la moneda principal
                            let totals = res.totals || {};
                            let selectedTotal = totals.selected ? Number(totals.selected).toFixed(2) + ' ' + (totals.selected_abbr || '') : (res.bill.total || 0).toFixed(2);
                            let principalTotal = totals.principal ? Number(totals.principal).toFixed(2) + ' ' + (totals.principal_abbr || '') : '';
                            let msg = `Hola, mi pedido (codigo: ${res.bill.code}):%0A`;
                            cart.forEach(item => {
                                msg += `- ${item.name} x${item.qty}%0A`;
                            });
                            msg += `%0ATotal (${totals.selected_abbr || ''}): ${selectedTotal}`;
                            if (principalTotal) msg += `%0ATotal (${totals.principal_abbr || ''}): ${principalTotal}`;
                            let phone = (res.company_phone || '{{ $companyPhone }}').replace(/\D/g,'');
                            let url = "https://wa.me/" + phone + "?text=" + msg;
                            window.open(url, '_blank');

                            // Limpiar carrito y cerrar modales
                            cart = [];
                            renderCart();
                            const checkoutModalEl = document.getElementById('checkout-modal');
                            const checkoutModal = bootstrap.Modal.getInstance(checkoutModalEl);
                            if (checkoutModal) checkoutModal.hide();
                            const cartModalEl = document.getElementById('cart-modal');
                            const cartModal = bootstrap.Modal.getInstance(cartModalEl);
                            if (cartModal) cartModal.hide();

                            Swal.fire({
                                icon: 'success',
                                title: 'Pedido creado',
                                text: 'Tu pedido fue registrado y se abrirá WhatsApp.'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message || 'No se pudo crear el pedido.'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Error al crear el pedido.'
                        });
                    }
                });
            });
            // Al hacer click en la card, abrir modal y actualizar URL
            $(document).on('click', '.product-card', function(e) {
                if ($(e.target).closest('.btn-add-cart').length) return;
                let id = $(this).data('id');
                openProductModalById(id);
                history.replaceState(null, '', '?producto=' + id);
            });
            // Al hacer click en el nombre (enlace), evitar navegación y abrir modal
            $(document).on('click', '.product-link', function(e) {
                e.preventDefault();
                let id = $(this).closest('.product-card').data('id');
                openProductModalById(id);
                history.replaceState(null, '', '?producto=' + id);
            });
            // Al cerrar el modal, quitar el parámetro de la URL
            $('#product-info-modal').on('hidden.bs.modal', function() {
                history.replaceState(null, '', window.location.pathname);
            });
        });
    </script>
@endsection
