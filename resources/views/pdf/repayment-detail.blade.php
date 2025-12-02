{{-- filepath: resources/views/pdf/repayment-detail.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Nota de Crédito</title>
    <style>
        @page { size: 80mm auto; margin: 10px; margin-left: 20px; }
        body { font-family: "Times New Roman", Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px; text-align: left; }
        .titulo { text-align: center; font-weight: bold; }
        .center { text-align: center; }
        .end { text-align: right; }
        .puntos { padding: 0px; }
        .detalle { font-size: 9px; }
        .total-row { font-weight: bold; border-top: 1px solid #000; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="4" class="titulo">DETALLE DEVOLUCIÓN</td>
        </tr>
        <tr>
            <td colspan="4"><b>Cliente:</b> {{ $repayment->clientName}}</td>
        </tr>
        <tr>
            <td colspan="4"><b>CI/RIF:</b> {{ $repayment->nationality }}-{{ $repayment->ci }}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Dir:</b> {{ $repayment->direction }}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Tlf:</b> {{ $repayment->phone }}</td>
        </tr>
        <tr>
            <td colspan="2"><b>Factura:</b> {{ $repayment->codeBill }}</td>
            <td colspan="2" class="end"><b>Nota Crédito:</b> {{ $repayment->code }}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Fecha:</b> {{ \Carbon\Carbon::parse($repayment->created_at)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">................................................................................................</td>
        </tr>
        <tr>
            <td colspan="4" class="center"><b>Productos Devueltos</b></td>
        </tr>
        <tr>
            <th class="detalle">Producto</th>
            <th class="detalle center">Cant</th>
            <th class="detalle end" colspan="2">Monto</th>
        </tr>
        @foreach($products as $p)
        <tr>
            <td class="detalle">{{ $p->product_name }}</td>
            <td class="detalle center">{{ $p->quantity }}</td>
            <td class="detalle end" colspan="2">{{ number_format($p->amount * $p->quantity, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="4" class="puntos">................................................................................................</td>
        </tr>
        <tr class="total-row">
            <td colspan="3" class="end">Total:</td>
            <td class="end">{{ number_format($total,2) }}</td>
        </tr>
        <tr>
            <td colspan="4" class="center"><b>Estado: {{ $repayment->status == 0 ? 'Pendiente' : 'Devuelto' }}</b></td>
        </tr>
    </table>
</body>
</html>