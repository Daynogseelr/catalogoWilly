<style>
    .card-product {
        border-radius: 1.5rem;
        box-shadow: 0 8px 32px rgba(33, 149, 243, 0);
        transition: box-shadow 0.2s, transform 0.2s;
        background: white;
        border: none;
        height: 330px;
        min-width: 220px;
        margin-bottom: 18px;
        overflow: hidden;
        padding: 18px 14px 14px 14px;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .card-product:hover {
        box-shadow: 0 16px 48px rgba(33,150,243,0.18);
        transform: translateY(-4px) scale(1.03);
        background: linear-gradient(135deg, #e3f2fd17 0%rgba(187, 222, 251, 0.253)fb 100%);
    }
    .card-product-logo {
        width: 48px;
        height: 48px;
        margin-bottom: 8px;
        margin-top: 2px;
        filter: drop-shadow(0 2px 8px rgba(33,150,243,0.10));
        display: block;
    }
    .card-product-img-top {
        border-radius: 1rem;
        object-fit: cover;
        background: #fff;
        margin-bottom: 10px;
        max-width: 100%;
        height: 120px;
        width: 120px;
        box-shadow: 0 2px 8px rgba(33,150,243,0.10);
        border: 2px solid #2196f3;
        display: block;
    }
    .card-title {
        font-weight: 700;
        font-size: 0.7rem;
        text-align: center;
        margin-bottom: 8px;
        letter-spacing: 1px;
        min-height: 50px;
        font-family: 'Montserrat', sans-serif;
    }
    .card-body p {
        font-size: 0.8rem;
        margin-bottom: 4px;
        word-break: break-word;
        font-weight: 500;
    }
    .card-body span, .card-body b {
        color: #005255 !important;
        font-weight: 700;
    }
    .card-product a {
        text-decoration: none !important;
        color: inherit !important;
    }
    .card-product a:focus, .card-product a:active {
        outline: none !important;
        box-shadow: none !important;
    }
     @media (max-width: 575.98px) {
        .card-product {
            flex-direction: row !important;
            align-items: flex-start !important;
            height: auto;
            min-width: 100%;
            padding: 14px 10px 14px 10px;
            background: white;
            box-shadow: 0 4px 16px rgba(33,150,243,0.10);
        }
        .card-product-img-top {
            margin: 0 !important;
        }
        .card-body {
            margin-top: 0 !important;
            padding: 0 0 0 10px !important;
        }
        .card-title {
            font-size: 0.7rem !important;
            text-align: left !important;
            margin-bottom:0px;
        }
    }
     .product-info-popup {
            box-shadow: 0 4px 16px rgba(33,150,243,0.10);
            pointer-events: none;
            top: 10px;
            font-size: 0.7rem;
        }
        .card-integral .product-info-popup { background: #e3f2fd; }
        .card-fraccionado .product-info-popup { background: #fffde7; }
        .badge {
            font-size: 0.7rem !important;
        }
</style>
@foreach($products as $pro)
    <div class="col-xxl-2 col-xl-3 col-lg-3 col-md-4 col-sm-6 col-12">
        <div class="card card-product">
            <a class="a" onClick="mostrarProduct({{ $pro->id }})" href="javascript:void(0)">
                <div class="d-sm-none d-flex align-items-center w-100" style="flex-direction: row;">
                    <img src="{{ asset('storage/' . $pro->url) }}"
                        onerror="this.src='{{ asset('storage/products/product.jpg') }}'"
                        class="card-product-img-top"
                        alt="{{ $pro->name }}"
                        style="margin:0;">
                    <div class="card-body ms-2" style="flex:1;">
                        <h6 class="card-title">
                            {{ $pro->name }} 
                        </h6>
                        {{-- Precios normales --}}
                        @if ($currencySelected->id == $currencyPrincipal->id) 
                            <p>
                                Precio en <span><b>{{ $currencyPrincipal->abbreviation }} {{ number_format($pro->price , 2) }}</b></span>
                            </p>
                            @if ($currencySelected->id != $currencyOfficial->id)
                                <p>
                                    Precio en <span><b>{{ $currencyOfficial->abbreviation }} {{ number_format($pro->price * $currencyOfficial->rate, 2) }}</b></span>
                                </p>
                            @endif
                        @else 
                            @php
                                $priceSelected = $pro->price * $currencySelected->rate;
                                $pricePrincipal = $pro->price;
                            @endphp
                            <p>
                                Precio en <span><b>{{ $currencyPrincipal->abbreviation }} {{ number_format($pricePrincipal , 2) }}</b></span>
                            </p>
                            <p>
                                Precio en <span><b>{{ $currencySelected->abbreviation }} {{ number_format($priceSelected , 2) }}</b></span>
                            </p>
                        @endif
                        <p>Cod. <span><b>{{ $pro->code }}</b></span></p>
                    </div>
                </div>
                <div class="d-none d-sm-block">
                    <img src="{{ asset('storage/' . $pro->url) }}"
                        onerror="this.src='{{ asset('storage/products/product.jpg') }}'"
                        class="card-product-img-top mx-auto"
                        alt="{{ $pro->name }}">
                    <div class="card-body" style="margin-top: -10px;">
                        <h6 class="card-title">
                            {{ $pro->name }}
                            
                        </h6>
                        @if ($currencySelected->id == $currencyPrincipal->id) 
                            <p>
                                Precio en <span><b>{{ $currencyPrincipal->abbreviation }} {{ number_format($pro->price , 2) }}</b></span>
                            </p>
                            @if ($currencySelected->id != $currencyOfficial->id)
                                <p>
                                    Precio en <span><b>{{ $currencyOfficial->abbreviation }} {{ number_format($pro->price * $currencyOfficial->rate, 2) }}</b></span>
                                </p>
                            @endif
                        @else 
                            @php
                                $priceSelected = $pro->price * $currencySelected->rate;
                                $pricePrincipal = $pro->price;
                            @endphp
                            <p>
                                Precio en <span><b>{{ $currencyPrincipal->abbreviation }} {{ number_format($pricePrincipal , 2) }}</b></span>
                            </p>
                            <p>
                                Precio en <span><b>{{ $currencySelected->abbreviation }} {{ number_format($priceSelected , 2) }}</b></span>
                            </p>
                        @endif
                        <p>Cod. <span><b>{{ $pro->code }}</b></span></p>
                    </div>
                </div>
            </a>
            {{-- Info extra para INTEGRAL y FRACCIONADO --}}
            <div class="product-info-popup" style="display:none; position:absolute; z-index:1000;"></div>
        </div>
    </div>
@endforeach
{!! $products->render(); !!}