<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nota de credito</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 10px;
            margin-left: 20px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            
        }

        th, td {
            
            padding: 2px;
            text-align: left;
        }

        .titulo {
            text-align: center;
            font-weight: bold;
        }
        .center {
            text-align: center;
        }
        .nota {
            font-size: 8px;
        }
        .end{
            text-align: right;
        }
        .puntos{
            padding: 0px;
        }
        .size{
            font-size: 14px;
        }
    </style>
</head>
<body>
    <table width="100%">
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
        <tr>
            <td colspan="4"><b>Vendedor:</b> {{$bill->sellerName}} </td>
        </tr>
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="4" class="center"><b>NOTA DE CREDITO</b></td>
        </tr>
        <tr>
            <td colspan="2"><b>Nro:</b></td>
            <td colspan="2" class="end">{{$bill->codeRepayment}}</td>
        </tr>
        <tr>
            <td colspan="2"><b>FECHA:</b> {{$bill->date}}</td>
            <td colspan="2" class="end"><b>HORA:</b> {{$bill->time}}</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">....................................................................................................</td>
        </tr> 
        <tr>
            <td colspan="4">Hemos emitido el presente documento bajo el concepto de Nota de credito por devolucion de mercancia bajo el Nº de factura: {{$bill->codeBill}}</td>
        </tr>
        <tr>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">....................................................................................................</td>
        </tr>
        <tr>
            <td >BI G16%</td>
            {{--<td >{{$bi}}</td>--}}
            <td >{{number_format($bill->total * $bill->rate_official,2)}}</td>
            <td class="end">I.V.A.G16%</td>
            {{--<td class="end">{{$iva}}</td>--}}
            <td class="end">Exento</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">....................................................................................................</td>
        </tr>
        <tr>
            <td colspan="2" class="size"><b>TOTAL</b></td>
            <td colspan="2" class="end size"><b>{{number_format($bill->total * $bill->rate_official,2)}} {{$bill->abbr_official}}</b></td>
        </tr>
        <tr>
            <td colspan="2"><b>REF:</b></td>
            <td colspan="2" class="end"><b>{{$bill->total}} {{$bill->abbr_principal}}</b></td>
        </tr>
        <tr>
            <td colspan="4"></td>
        </tr>
    </table>
</body>
</html>