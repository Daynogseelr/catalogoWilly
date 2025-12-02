<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajuste de Inventario</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
            width: 660px;
            margin: -30px 0px -30px -20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header p {
            margin: 5px 0;
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
            font-size: 8pt;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: right;
            font-style: italic;
        }
        .resumen {
            margin-top: 10px;
            margin-bottom: 10px;
            font-size: 10pt;
        }
        @page {
            size: letter;
            margin: 1in;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Ajuste de Inventario</h2>
    </div>
    <div>
        <span><b>Generado por:</b> {{ $user->name }}</span>
        <span style="float: right;"><b>Nº: {{ $ajuste_id }}</b></span>
        <div style="clear: both;"></div>
    </div>
    <br>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Stock Anterior</th>
                <th>Nuevo Stock</th>
                <th>Diferencia</th>
                <th>Precio</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stock as $item)
                <tr>
                    <td>{{ $item['product_code'] ?? 'N/A' }}</td>
                    <td>{{ $item['product_name'] ?? 'N/A' }}</td>
                    <td style="text-align: right;">{{ isset($item['quantity'], $item['diferencia']) ? $item['quantity'] - $item['diferencia'] : 'N/A' }}</td>
                    <td style="text-align: right;">{{ $item['quantity'] ?? 'N/A' }}</td>
                    <td style="text-align: right;">{{ $item['diferencia'] ?? 'N/A' }}</td>
                    <td style="text-align: right;">{{ number_format($item['product_price'] ?? 0, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($item['monto'] ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="resumen">
        <b>Monto Perdido:</b> {{ number_format($amount_lost, 2) }}<br>
        <b>Monto Ganado:</b> {{ number_format($amount_profit, 2) }}<br>
        <b>Total Ajuste:</b> {{ number_format($total, 2) }}
    </div>

    <div class="footer">
        <p>Fecha de Generación: {{ date('Y-m-d H:i:s') }}</p>
    </div>

</body>
</html>