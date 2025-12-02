<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Transfer {{ $transfer->code }}</title>
    <style>
        @page { size: 80mm auto; margin:8px; font-weight:bold }
        body { font-family: "Courier New", monospace; font-size:11px }
        .center { text-align:center }
        .right { text-align:right }
        .bold { font-size: 13px !important}
        table { width:100% }
        th,td { padding:2px 0 }
    </style>
</head>
<body>
    <div class="center bold">TRANSFERENCIA</div>
    <div class="center">{{ $transfer->code }}</div>
    <hr>
    <div><strong>Origen:</strong> {{ optional($transfer->fromSucursal)->name }}</div>
    <div><strong>Destino:</strong> {{ optional($transfer->toSucursal)->name }}</div>
    <div><strong>Fecha:</strong> {{ $transfer->created_at }}</div>
    <hr>
    <table>
        <thead>
            <tr><th>Producto</th><th class="right">Cantidad</th></tr>
        </thead>
        <tbody>
            @foreach($transfer->items as $it)
                <tr>
                    <td>{{ optional($it->product)->code }} {{ optional($it->product)->name }}</td>
                    <td class="right">{{ number_format($it->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <div class="center">Firma: ______________________</div>
    <div class="center small">Gracias</div>
</body>
</html>