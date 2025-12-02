<!-- filepath: c:\xampp\htdocs\digicatalogo\resources\views\pdf\payment.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Recibo de pago</title>
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
        .size { font-size: 14px; }
    </style>
</head>
<body>
    <table width="100%">
        <tr><td colspan="4"></td></tr>
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
        <tr><td colspan="4"></td></tr>
        <tr>
            <td colspan="4" class="center"><b>RECIBO DE PAGO</b></td>
        </tr>
        <tr>
            <td colspan="2"><b>FECHA:</b> {{$bill->date}}</td>
            <td colspan="2" class="end"><b>HORA:</b> {{$bill->time}}</td>
        </tr>
        <tr>
            <td colspan="4" class="puntos">....................................................................................................</td>
        </tr>
        <tr>
            <td colspan="4">
                Hemos emitido el presente documento bajo el concepto de Recibo de pago por 
                {{$bill->payment_type}} ({{$bill->currency_abbr}})
                bajo el Nº de factura: {{$bill->codeBill}}
            </td>
        </tr>
        <tr><td colspan="4"></td></tr>
        <tr>
            <td colspan="4" class="puntos">....................................................................................................</td>
        </tr>
        <tr>
            <td colspan="2" class="size"><b>TOTAL</b></td>
            <td colspan="2" class="end size">
                <b>{{ $total }} ({{$bill->currency_abbr}})</b>
            </td>
        </tr>
        <tr>
            <td colspan="2"><b>REF:</b></td>
            <td colspan="2" class="end">
                <b>{{ $bill->amount }} ({{ $principal_currency }})</b>
            </td>
        </tr>
        <tr><td colspan="4"></td></tr>
    </table>
</body>
</html>