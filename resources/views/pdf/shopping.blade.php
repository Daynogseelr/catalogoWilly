<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt; /* Tamaño de fuente para todo el documento */
            width: 660px;
            margin: -30px 0px -30px -20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header p {
            margin: 5px 0; /* Espacio entre las líneas del encabezado */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
            font-size: 8pt; /* Tamaño de fuente para la tabla */
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: right;
            font-style: italic;
        }
        @page {
            size: letter; /* Define el tamaño de la página a carta */
            margin: 1in; /* Margen de 1 pulgada en todos los lados */
        }
    </style>
</head>
<body>

    <div class="header">
        {{--<p>{{ $company->nationality }}-{{ $company->ci }}</p>
        <p>{{ $company->name }}</p>
         <p>{{ $company->direction }}</p>
        <p>{{ $company->city }} EDO. {{ $company->state }} ZONA POSTAL {{ $company->postal_zone }}</p>--}}
    </div>
    <div>
        <p style="line-height: 0.6;"><b>Factura:</b> {{ $shopping->codeBill }}</p>
        <p style="line-height: 0.6;"><b>Proveedor:</b> {{ $shopping->name }}</p>
        <p style="line-height: 0.6;"><b>Fecha:</b> {{ $shopping->date }}</p>
        <p style="line-height: 0.6;"><b>Total:</b> {{ $shopping->total }}</p>
    </div>
    <div>
        <span><b>Generado por:</b> {{ $user->name }} {{ $user->last_name }}</span>
        <span style="float: right;"><b>Nº: {{ $code }}</b></span>
        <div style="clear: both;"></div>  
    </div>
    <br>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Costo</th>
                <th>Stock Ingresado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stock as $item)
                <tr>
                    <td>{{ $item['product_code'] ?? 'N/A' }}</td> {{-- Muestra el código del producto --}}
                    <td>{{ $item['product_name'] ?? 'N/A' }}</td> {{-- Muestra el nombre del producto --}}
                    <td style="text-align: right;" >{{ $item['cost']}}</td>
                    <td style="text-align: right;" >{{ $item['addition']}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Fecha de Generación: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>

</body>
</html>