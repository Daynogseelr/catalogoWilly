{{-- filepath: resources/views/pdf/credit-payment.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Pagos</title>
    <style>
        @page { size: 80mm auto; margin: 10px; margin-left: 20px; }
        body { font-family: "Times New Roman", Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px; text-align: left; }
        .titulo { text-align: center; font-weight: bold; }
        .center { text-align: center; }
        .end { text-align: right; }
        .puntos { padding: 0px; }
        .size { font-size: 14px; }
        .detalle { font-size: 9px; }
        .total-row { font-weight: bold; border-top: 1px solid #000; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="4" class="titulo">DETALLE DE PAGOS</td>
        </tr>
        <tr>
            <td colspan="4"><b>Cliente:</b> {{ $bill->clientName }}</td>
        </tr>
        <tr>
            <td colspan="4"><b>CI/RIF:</b> {{ $bill->nationality }}-{{ $bill->ci }}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Dir:</b> {{ $bill->direction }}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Tlf:</b> {{ $bill->phone }}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Factura:</b> {{ $bill->code }}</td>
        </tr>
        <tr>
            <td colspan="2"><b>Fecha:</b> {{ $bill->date }}</td>
            <td colspan="2" class="end"><b>Hora:</b> {{ $bill->time }}</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">................................................................................................</td>
        </tr>
        <tr>
            <td colspan="2"><b>Total Factura:</b></td>
            <td colspan="2" class="end"><b>{{ number_format($bill->net_amount,2) }} {{ $bill->abbr_principal }}</b></td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">................................................................................................</td>
        </tr>
        <tr>
            <td colspan="4" class="center"><b>Pagos Realizados</b></td>
        </tr>
        <tr>
            <th class="detalle">Fecha</th>
            <th class="detalle">Método</th>
            <th class="detalle">Referencia</th>
            <th class="detalle end">Monto</th>
        </tr>
        @foreach($payments as $p)
        <tr>
            <td class="detalle">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</td>
            <td class="detalle">
                @if($p->code_repayment)
                    Nota Crédito
                @else
                    {{ $p->payment_type }} {{ $p->currency_abbr }}{{ $p->payment_bank ? ' - '.$p->payment_bank : '' }}
                @endif
            </td>
            <td class="detalle">{{ $p->reference ?? $p->code_repayment ?? '-' }}</td>
            <td class="detalle end">{{ number_format($p->amount,2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="4" class="puntos">................................................................................................</td>
        </tr>
        <tr class="total-row">
            <td colspan="3" class="end">Total Pagado:</td>
            <td class="end">{{ number_format($totalPagado,2) }} {{ $bill->abbr_principal }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="3" class="end">Restante:</td>
            <td class="end">{{ number_format($restante,2) }} {{ $bill->abbr_principal }}</td>
        </tr>
    </table>
</body>
</html>