<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Catálogo - {{ $sucursal->name ?? '' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size:12px; color:#222; }
        .header { text-align: center; margin-bottom: 12px; }
        .company { font-weight:700; font-size:16px; }
        .sucursal { font-size:12px; color:#666; }
        .grid { width:100%; border-collapse: collapse; }
        .item { border-bottom:1px solid #eee; padding:8px 0; }
        .row { display:flex; align-items:center; padding:8px 0; }
        .thumb { width:90px; height:70px; margin-right:12px; }
        .thumb img{ width:90px; height:70px; object-fit:cover; }
        .name { font-weight:700; font-size:11px; }
        .meta { color:#666; font-size:11px; }
        .price { color:#0d6efd; font-weight:800; font-size:13px; }
        .footer { margin-top:18px; font-size:10px; color:#666; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">{{ $company->name ?? 'Mi Empresa' }}</div>
        <div class="sucursal">Catálogo - {{ $sucursal->name ?? '' }} - {{ $currency->abbreviation ?? '' }}</div>
    </div>

    <div class="catalog-list">
        @foreach($items as $it)
            @php
                // $it->image may be an absolute filesystem path or a data URI; use it directly
                $imgSrc = $it->image;
            @endphp
            <div class="row item">
                <div class="thumb">
                    <img src="{{ $imgSrc }}" alt="{{ $it->name }}" width="90" height="70">
                </div>
                <div class="info">
                    <div class="name">{{ $it->name }}</div>
                    <div class="meta">{{ $it->category }} • Existencia: {{ intval($it->stock) }}</div>
                    <div class="price">{{ number_format($it->price, 2) }} {{ $currency->abbreviation ?? '' }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="footer">Generado: {{ date('d/m/Y H:i') }} - {{ $company->name ?? '' }}</div>
</body>
</html>