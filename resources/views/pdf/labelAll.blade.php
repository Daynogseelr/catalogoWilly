<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Etiqueta</title>
    <style>
         body {
            font-family: sans-serif;
            margin: -35px;
            padding: 0;
            font-size: 9px;
        }

        .label-container {
            width: 4.8cm;
            max-height: 1cm;
            display: flex;
            flex-direction: column;
           /* border: 1px solid red; */
        }
        .product-info {
            height: 0.8cm; /* Altura fija de 1cm */
            overflow: hidden; /* Oculta el texto que se desborda */
            text-overflow: ellipsis; /* Agrega puntos suspensivos (...) si el texto se desborda */
            /* border: 1px solid blue;*/
        }

        .barcode {
            height: 0.2cm; /* Altura fija de 0.5cm */
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            /* border: 1px solid green;*/
        }

        img {
            width: 2cm;/* Ancho máximo de 3cm */
            max-height: 0.5cm; /* Alto máximo de 0.5cm */
            vertical-align: middle;
        }
    </style>
</head>
<body>
    @foreach ($labels as $label)
        @if($label)
            @for ($i = 0; $i < $label['quantity']; $i++)
                @php
                    $nombre = $label['name'];
                    $nombreCorto = mb_substr($nombre, 0, 50);
                    if (mb_strlen($nombre) > 50) $nombreCorto .= '...';
                @endphp
                @if(($format ?? 'compact') === 'big')
                    <div class="big-label">
                        <div class="big-name">{{ strtoupper($nombreCorto) }}</div>
                        <div class="big-sep"></div>
                        <div class="big-units">Und x {{ $label['quantity'] }}</div>
                        <div class="big-price">Ref {{ isset($label['price']) ? number_format($label['price'], 2, ',', '.') : '' }}</div>
                        <div class="big-barcode">
                            <img src="data:image/png;base64,{{ $label['barcodePNG'] }}" alt="barcode">
                        </div>
                        <div class="big-code">COD. {{ $label['code'] }}</div>
                    </div>
                @else
                    <div class="label-container">
                        <div class="product-info">
                            {{ $nombreCorto }}
                        </div>
                        <div class="barcode">
                            <span style="font-size:11px; font-weight:700;">Ref {{ number_format($price ?? 0, 2, ',', '.') }}</span> <img src="data:image/png;base64,{{ $barcodePNG }}">  <b>COD. {{ $product->code }} </b> 
                        </div>
                    </div>
                @endif
            @endfor
        @endif
    @endforeach
</body>
</html>

<style>
    /* Reusar estilos del label individual para formato big */
    .big-label{
        width: 7cm; /* 80mm */
        margin: 0 auto;
        display: block;
        box-sizing: border-box;
        padding: 4px 6px;
        transform: none !important;
        -webkit-transform: none !important;
        writing-mode: horizontal-tb !important;
        page-break-inside: avoid;
    }
    .big-label img{ max-width: 100%; height: auto; display:block; margin:6px auto; }
    .big-name{ font-size: 12px; font-weight:700; margin-top:4px; }
    .big-sep{ border-top:1px solid #000; margin:6px 0; }
    .big-units{ font-size:9px; }
    .big-price{ font-size:28px; font-weight:700; margin:6px 0; }
    .big-code{ font-size:10px;}
</style>