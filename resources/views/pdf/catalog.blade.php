<!DOCTYPE html>
<html>
<head>
    <title>Catálogo de Productos</title>
    <style>
    @page {
        margin: 30px;
    }
    .product-grid {
        /* Eliminamos display: grid y sus propiedades */
        /* display: grid; */
        /* grid-template-columns: repeat(3, 1fr); */
        /* gap: 10px; */

        /* Usamos text-align para centrar los elementos inline-block dentro del grid */
        text-align: center; /* Esto centrará los productos si la suma de sus anchos no llena la línea */
        width: 100%; /* Ocupa todo el ancho disponible */
        margin-bottom: 10px; /* Espacio entre cada "fila" de productos */
    }
    .product-row {
        /* Este div agrupará 3 productos para formar una fila */
        width: 100%;
        text-align: center; /* Asegura que los productos dentro de la fila se centren */
        margin-bottom: 5px; /* Pequeño espacio entre filas */
        overflow: hidden; /* Limpia los floats si decides usarlos, aunque con inline-block no es tan crítico */
    }
    .product {
        width: 210px; /* Ancho de tu card */
        display: inline-block; /* Sigue usando inline-block */
        vertical-align: top; /* Asegura que se alineen en la parte superior */
        margin: 5px; /* Margen entre productos. Ajusta según sea necesario */
        text-align: center;
        height: 290px; /* Altura de tu card */
        border: 1px solid #ddd;
        padding: 5px;
        /* Asegúrate de que el ancho total de 3 productos + márgenes no exceda el ancho de la página */
        /* 3 * 210px (ancho) + 3 * (5px * 2) (margen horizontal) = 630px + 30px = 660px */
        /* Una página A4 tiene un ancho de ~794px. Si los márgenes de página son 30px a cada lado, te quedan 734px. */
        /* 660px cabe en 734px, pero puede ser ajustado si es muy apretado. */
    }
    .product img {
        width: 150px;
        height: 150px;
        display: block;
        margin: 10px auto;
    }
    .product h6 {
        font-size: 12px;
        height: 60px;
        margin: 0;
        padding: 0;
        overflow: hidden; /* Importante para el texto que desborda */
    }
    .product p {
        font-size: 13px;
        margin: 0;
        padding: 0;
    }
    .product p b {
        font-size: 15px;
    }
    .h1{
        margin: auto;
        text-align: center;
    }
    .page-break {
        page-break-after: always;
    }
</style>
</head>
<body>
    <div class="h1">
        <h1>Catálogo de Productos</h1>
    </div>
    @php
        $productsCollection = $products->toArray(); // Asegúrate de que $products sea la variable correcta que contiene todos los productos
        // Divide en grupos de 9 productos para cada página del PDF (3 filas de 3 productos)
        $pages = array_chunk($productsCollection, 9);
    @endphp

    @foreach($pages as $page_chunk)
        <div class="product-grid">
            @php
                // Divide cada página en filas de 3 productos
                $rows = array_chunk($page_chunk, 3);
            @endphp
            @foreach($rows as $row_chunk)
                <div class="product-row">
                    @foreach($row_chunk as $pro)
                        <div class="product">
                            <img src="{{ asset('storage/' . $pro['url1']) }}"
                                onerror="this.src='{{ asset('storage/products/product.png') }}'"
                                >
                            <h6>{{ $pro['name'] }}</h6>
                            @php
                                $priceBs = $pro['price'] * ($dolar->priceBs ?? 1);
                                $price = $priceBs / ($dolar->price ?? 1);
                            @endphp
                            <p>Precio <b>$ {{ number_format($price, 2) }}</b></p>
                            <p>Precio en Bs <b>{{ number_format($priceBs, 2) }}</b></p>
                            <p>Cod. <b>{{ $pro['code'] }}</b></p>
                            {{--<p>Stock: <b>{{ $pro['stock'] ?? 0 }}</b></p>--}}
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        {{-- Forzar salto de página después de cada grupo de 9 productos (una "página" del PDF) --}}
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>