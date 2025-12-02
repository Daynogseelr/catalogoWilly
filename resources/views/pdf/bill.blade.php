<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Factura</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 10px;
            margin-left: 10px;
        }
        body {
            font-family: "Times New Roman", Arial, sans-serif;
            font-size: 11px;
            color: #000000; /* Asegurar negro */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            
        }

        th, td {
            padding: 1px 0px; /* quitar espacio entre líneas */
            line-height: 1; /* compactar líneas */
            text-align: left;
        }

        .titulo {
            text-align: center;
            font-weight: 900;
            font-size: 13px; /* títulos más grandes */
        }
        .center {
            text-align: center;
        }
        .company-info {
            font-weight: 500; /* medium: less heavy than 600 */
            font-size: 12px;
            color: #111111; /* slightly lighter than pure black */
        }
        b { font-weight: 700; color: #000; }
        .end{
            text-align: right;
        }
        .puntos{
            padding: 0px;
            font-size: 10px;
            color: #000;
        }
        .size{
            font-size: 14px;
            font-weight: bold; /* totales más destacados */
        }
  
    </style>
</head>
<body>
    <table width="100%">
        <tr>
            <td colspan="4" class="titulo">SENIAT</td>
        </tr>
        <tr>
            <td colspan="4" class="center">{{$company->rif}}</td>
        </tr>
        <tr>
            <td colspan="4" class="center">{{$company->name}}</td>
        </tr>
        <tr>
            <td colspan="4" class="center">{{$company->direction}}</td>
        </tr>
        <tr>
            <td colspan="4" class="center">{{$company->city}} EDO. {{$company->state}} ZONA POSTAL {{$company->postal_zone}}</td>
        </tr>
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="4"><b>Cliente:</b> {{$bill->clientName}}</td>
        </tr>
        <tr>
            <td colspan="4"><b>CI/RIF:</b> {{$bill->nationality}}-{{$bill->ci}}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Dir:</b> {{$bill->direction}}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Tlf:</b> {{$bill->phone}}</td>
        </tr>
        {{-- Vendedor se mostrará al final para imitar ticket físico --}}
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            @if ($bill->type == 'PRESUPUESTO')
                <td colspan="4" class="titulo"><b>PRESUPUESTO</b></td>
            @else
                <td colspan="4" class="titulo"><b>FACTURA</b></td>
            @endif
        </tr>
        <tr>
            <td colspan="2"><b>Nro:</b></td>
            <td colspan="2" class="end">{{$bill->code}}</td>
        </tr>
        <tr>
            <td colspan="2"><b>FECHA:</b> {{$bill->date}}</td>
            <td colspan="2" class="end"><b>HORA:</b> {{$bill->time}}</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">.........................................................................................</td>
        </tr>
        @foreach ($bill_details as $bill_detail)
        <tr>
            <td colspan="2">{{$bill_detail->quantity}} x {{number_format($bill_detail->priceU * $bill->rate_official,2)}} </td>
            <td colspan="2" class="end">{{$bill->abbr_official}} {{number_format($bill_detail->net_amount * $bill->rate_official,2)}}</td>
        </tr>
        <tr>
            <td colspan="4">{{$bill_detail->name}}@if($bill_detail->iva != 1) (E) @endif</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">.........................................................................................</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="2">Exento (E)</td>
            <td colspan="2" class="end">{{$bill->abbr_official}} {{ number_format($exento, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">BI G16%</td>
            <td colspan="2" class="end">{{$bill->abbr_official}} {{ number_format($bi, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">I.V.A.G16%</td>
            <td colspan="2" class="end">{{$bill->abbr_official}} {{ number_format($iva, 2, ',', '.') }}</td>
        </tr>
        
        <tr>
            <td colspan="4" class="puntos">...........................................................................................</td>
        </tr>
        <tr>
            <td colspan="2">DESCUENTO</td>
            <td colspan="2" class="end">{{$bill->abbr_official}} {{number_format($bill->discount * $bill->rate_official,2)}}</td>
        </tr>
        <tr>
            <td colspan="2" class="size"><b>TOTAL</b></td>
            <td colspan="2" class="end size"><b>{{$bill->abbr_official}} {{ number_format($bill->net_amount * $bill->rate_official, 2, ',', '.') }} </b></td>
        </tr>
        <tr>
            <td colspan="4"></td>
        </tr>
        {{-- Metodo(s) de pago --}}
        @if ($bill->type != 'PRESUPUESTO')
            @if ($bill->type == 'CREDITO')
                <tr>
                    <td colspan="2">CREDITO</td>
                    <td colspan="2" class="end"> {{$bill->abbr_official}} {{number_format($bill->net_amount * $bill->rate_official,2)}}</td>
                </tr>
            @else
                @foreach ($payments as $p)
                    @php
                        // Determinar la abreviatura de moneda del pago:
                        // 1) Si la consulta ya trajo 'abbreviation' o 'payment_currency_abbr' usarla
                        // 2) Intentar la relación payment_method->currency (si existe)
                        // 3) Fallback a la abreviatura oficial de la factura
                        $payAbbr = $p->abbreviation ?? $p->payment_currency_abbr ?? optional(optional($p->payment_method)->currency)->abbreviation ?? $bill->abbr_official;
                    @endphp
                    <tr>
                        <td colspan="2"> {{ strtoupper($p->payment_type ?? $p->collection) }}</td>
                        <td colspan="2" class="end">{{ $payAbbr }} {{ number_format(($p->amount * $p->rate), 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
        @endif
        {{-- Total de items: sumar las cantidades de bill_details y mostrar # ITEMS --}}
        @php
            $totalItems = 0;
            foreach ($bill_details as $d) {
                $totalItems += floatval($d->quantity);
            }
        @endphp
        <tr>
            <td colspan="4"># ITEMS: {{ (intval($totalItems) == $totalItems) ? number_format($totalItems,0,',','.') : number_format($totalItems,2,',','.') }}</td>
        </tr>
        {{-- Mensaje y datos al final (vendedor, cajero, serial) --}}
        <tr>
            <td colspan="4" class="center "><b>!!GRACIAS POR SU COMPRA!!</b></td>
        </tr>
        <tr>
            <td colspan="2">Cajero: </td>
            <td colspan="2" class="end">{{$bill->sellerName}}</td>
        </tr>
        <tr>
            <td colspan="2">MH </td>
            <td colspan="2" class="end">Z1B8000010</td>
        </tr>
    </table>
</body>
</html>