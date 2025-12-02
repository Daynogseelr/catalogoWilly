<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre</title>
    <style>
        @page { size: 80mm auto; margin: 10px; margin-left: 20px; }
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px; text-align: left; }
        .titulo { text-align: center; font-weight: bold; }
        .center { text-align: center; }
        .nota { font-size: 8px; }
        .end { text-align: right; }
        .puntos { padding: 0px; }
        .size { font-size: 12px; }
    </style>
</head>
<body>
    <table width="100%">
        <tr><td colspan="4"></td></tr>
        <tr>
            <td colspan="4"><b>Vendedor:</b> {{ $closure->sellerName }} </td>
        </tr>
        <tr><td colspan="4"></td></tr>
        <tr>
            <td colspan="4" class="center"><b>CIERRE {{ $closure->type }}</b></td>
        </tr>
        <tr>
            <td colspan="2"><b>FECHA:</b> {{ $closure->date }}</td>
            <td colspan="2" class="end"><b>HORA:</b> {{ $closure->time }}</td>
        </tr>
        <tr><td colspan="4" class="puntos">....................................................................................................</td></tr>
        <tr><td colspan="4" class="center"><b>FACTURACIÓN</b></td></tr>
        <tr><td colspan="4" class="puntos">....................................................................................................</td></tr>
        @foreach($bills->groupBy('abbr') as $abbr => $group)
            <tr><td colspan="4" class="center"><b>({{ $group->first()->currency_name }})</b></td></tr>
            @foreach($group->groupBy('type') as $type => $typeGroup)
                <tr>
                    <td colspan="2"><b>{{ $type }}</b></td>
                    <td colspan="2" class="end">{{ number_format($typeGroup->sum('total'), 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="size"><b>TOTAL {{ $abbr }}:</b></td>
                <td colspan="2" class="end size"><b>{{ number_format($group->sum('total'), 2) }}</b></td>
            </tr>
        @endforeach
        <tr><td colspan="4"></td></tr>
        <tr><td colspan="4" class="puntos">....................................................................................................</td></tr>
        <tr><td colspan="4" class="center"><b>PAGOS (CONTADO)</b></td></tr>
        <tr><td colspan="4" class="puntos">....................................................................................................</td></tr>
        @foreach($payments->groupBy('abbr') as $abbr => $group)
            <tr><td colspan="4" class="center"><b>({{ $group->first()->currency_name }})</b></td></tr>
            @foreach($group->groupBy('payment_type') as $type => $typeGroup)
                <tr>
                    <td colspan="2"><b>{{ $type }}</b></td>
                    <td colspan="2" class="end">{{ number_format($typeGroup->sum('total'), 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="size"><b>TOTAL {{ $abbr }}:</b></td>
                <td colspan="2" class="end size"><b>{{ number_format($group->sum('total'), 2) }}</b></td>
            </tr>
        @endforeach
        <tr><td colspan="4"></td></tr>
        <tr><td colspan="4" class="puntos">....................................................................................................</td></tr>
        <tr><td colspan="4" class="center"><b>COBRANZAS (CREDITO)</b></td></tr>
        <tr><td colspan="4" class="puntos">....................................................................................................</td></tr>
        @foreach($collections->groupBy('abbr') as $abbr => $group)
            <tr><td colspan="4" class="center"><b>({{ $group->first()->currency_name }})</b></td></tr>
            @foreach($group->groupBy('payment_type') as $type => $typeGroup)
                <tr>
                    <td colspan="2"><b>{{ $type }}</b></td>
                    <td colspan="2" class="end">{{ number_format($typeGroup->sum('total'), 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="size"><b>TOTAL {{ $abbr }}:</b></td>
                <td colspan="2" class="end size"><b>{{ number_format($group->sum('total'), 2) }}</b></td>
            </tr>
        @endforeach
        <tr><td colspan="4"></td></tr>
        <tr><td colspan="4" class="puntos">....................................................................................................</td></tr>
        <tr><td colspan="4" class="center"><b>DEVOLUCIONES</b></td></tr>
        <tr><td colspan="4" class="puntos">....................................................................................................</td></tr>
        @foreach($repayments->groupBy('abbr') as $abbr => $group)
            <tr><td colspan="4" class="center"><b>({{ $group->first()->currency_name }})</b></td></tr>
            @foreach($group->groupBy('status') as $status => $statusGroup)
                <tr>
                    <td colspan="2"><b>
                        @if ($status == 0)
                            NOTA DE CREDITO
                        @else
                            DEVOLUCIÓN
                        @endif
                    </b></td>
                    <td colspan="2" class="end">{{ number_format($statusGroup->sum('total'), 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="size"><b>TOTAL {{ $abbr }}:</b></td>
                <td colspan="2" class="end size"><b>{{ number_format($group->sum('total'), 2) }}</b></td>
            </tr>
        @endforeach
        <tr><td colspan="4"></td></tr>
        <tr><td colspan="4" class="puntos">....................................................................................................</td></tr>
        {{-- Totales por IVA (productos) --}}
        <tr>
            <td colspan="2">PRODUCTOS EXCENTO BS:</td>
            <td colspan="2" class="end"><b>{{ number_format($exento_total ?? 0, 2) }}</b></td>
        </tr>
        <tr>
            <td colspan="2">PRODUCTOS BASE 16% BS:</td>
            <td colspan="2" class="end"><b>{{ number_format($base16_total ?? 0, 2) }}</b></td>
        </tr>
        <tr>
            <td colspan="2">IMPUESTO 16%:</td>
            <td colspan="2" class="end"><b>{{ number_format($iva_total ?? 0, 2) }}</b></td>
        </tr>
        <tr><td colspan="4"></td></tr>
        @foreach($smallBox as $sb)
            <tr>
                <td colspan="2"><b>CAJA CHICA ({{ $sb->abbr }}):</b></td>
                <td colspan="2" class="end"><b>{{ number_format($sb->total, 2) }}</b></td>
            </tr>
        @endforeach
    </table>
</body>
</html>