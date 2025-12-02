<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre Global Detallado</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; width: 700px; margin: -50px 0px -50px -40px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; font-size: 8pt; }
        th { background-color: #f2f2f2; }
        .footer { text-align: right; font-style: italic; }
        @page { size: letter; margin: 1in; }
        .total-row { font-weight: bold; border-top: 1px solid #000; }
    </style>
</head>
<body>
    <div class="header">
        <h2>CIERRE GLOBAL DETALLADO</h2>
        <p><b>Fecha:</b> {{ $closure->date }} <b>Hora:</b> {{ $closure->time }}</p>
        <hr>
    </div>

    <h3 style="text-align: center;">FACTURACIÓN</h3>
    @forelse($billsGrouped as $abbr => $byType)
        @foreach($byType as $type => $group)
            <table>
                <thead>
                    <tr>
                        <th colspan="6" style="text-align: center;">{{ $type }} ({{ $abbr }})</th>
                    </tr>
                    <tr>
                        <th>Código</th>
                        <th>Empleado</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Descuento</th>
                        <th>Total Neto</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalNet = 0; @endphp
                    @foreach($group as $bill)
                        <tr>
                            <td>{{ $bill->code }}</td>
                            <td>{{ $bill->seller_name }} </td>
                            <td>{{ $bill->client_name }} </td>
                            <td style="text-align: right;">{{ number_format($bill->total_amount * $bill->rate_bill,2) }}</td>
                            <td style="text-align: right;">{{ number_format($bill->discount * $bill->rate_bill,2) }}</td>
                            <td style="text-align: right;">{{ number_format($bill->net_amount * $bill->rate_bill,2) }}</td>
                        </tr>
                        @php 
                            $totalNet += $bill->net_amount  * $bill->rate_bill; 
                        @endphp
                    @endforeach
                    <tr class="total-row">
                        <td colspan="5" style="text-align: right;"><b>TOTAL</b></td>
                        <td style="text-align: right;"><b>{{ number_format($totalNet,2) }}</b></td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @empty
        <p>No hay facturación en este cierre.</p>
    @endforelse

    <h3 style="text-align: center;">PAGOS (CONTADO)</h3>
    @forelse($bill_paymentContadoGrouped as $abbr => $byType)
        @foreach($byType as $type => $group)
            <table>
                <thead>
                    <tr>
                        <th colspan="5" style="text-align: center;">{{ $type }} ({{ $abbr }})</th>
                    </tr>
                    <tr>
                        <th>Factura</th>
                        <th>Empleado</th>
                        <th>Cliente</th>
                        <th>Referencia</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalPay = 0; @endphp
                    @foreach($group as $pay)
                        <tr>
                            <td>{{ $pay->bill_code }}</td>
                            <td>{{ $pay->seller_name }}</td>
                            <td>{{ $pay->client_name }}</td>
                            <td>{{ $pay->reference }}</td>
                            <td style="text-align: right;">{{ number_format($pay->amount * $pay->rate,2) }}</td>
                        </tr>
                        @php 
                            $totalPay += $pay->amount * $pay->rate; 
                        @endphp
                    @endforeach
                    <tr class="total-row">
                        <td colspan="4" style="text-align: right;"><b>TOTAL</b></td>
                        <td style="text-align: right;"><b>{{ number_format($totalPay,2) }}</b></td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @empty
        <p>No hay pagos contado en este cierre.</p>
    @endforelse

    <h3 style="text-align: center;">COBRANZAS (CREDITO)</h3>
    @forelse($bill_paymentCreditoGrouped as $abbr => $byType)
        @foreach($byType as $type => $group)
            <table>
                <thead>
                    <tr>
                        <th colspan="5" style="text-align: center;">{{ $type }} ({{ $abbr }})</th>
                    </tr>
                    <tr>
                        <th>Factura</th>
                        <th>Empleado</th>
                        <th>Cliente</th>
                        <th>Referencia</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalCol = 0; @endphp
                    @foreach($group as $col)
                        <tr>
                            <td>{{ $col->bill_code }}</td>
                            <td>{{ $col->seller_name }}</td>
                            <td>{{ $col->client_name }}</td>
                            <td>{{ $col->reference }}</td>
                            <td style="text-align: right;">{{ number_format($col->amount * $col->rate,2) }}</td>
                        </tr>
                        @php 
                            $totalCol += $col->amount * $col->rate; 
                        @endphp
                    @endforeach
                    <tr class="total-row">
                        <td colspan="4" style="text-align: right;"><b>TOTAL</b></td>
                        <td style="text-align: right;"><b>{{ number_format($totalCol,2) }}</b></td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @empty
        <p>No hay cobranzas crédito en este cierre.</p>
    @endforelse

    <h3 style="text-align: center;">DEVOLUCIONES</h3>
    @forelse($repaymentsGrouped as $abbr => $byStatus)
        @foreach($byStatus as $status => $group)
            @php $statusText = $status == 0 ? 'NOTA DE CRÉDITO' : 'DEVOLUCIÓN'; @endphp
            <table>
                <thead>
                    <tr>
                        <th colspan="5" style="text-align: center;">{{ $statusText }} ({{ $abbr }})</th>
                    </tr>
                    <tr>
                        <th>Factura</th>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalRep = 0; @endphp
                    @foreach($group as $rep)
                        <tr>
                            <td>{{ $rep->bill_code }}</td>
                            <td>{{ $rep->code }}</td>
                            <td>{{ $rep->product_code }}-{{ $rep->product_name }}</td>
                            <td style="text-align: center;">{{ $rep->quantity }}</td>
                            <td style="text-align: right;">{{ number_format($rep->amount * $rep->rate,2) }}</td>
                        </tr>
                        @php 
                            $totalRep += $rep->amount * $rep->rate; 
                        @endphp
                    @endforeach
                    <tr class="total-row">
                        <td colspan="4" style="text-align: right;"><b>TOTAL</b></td>
                        <td style="text-align: right;"><b>{{ number_format($totalRep,2) }}</b></td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @empty
        <p>No hay devoluciones en este cierre.</p>
    @endforelse

    <h3 style="text-align: center;">CAJA CHICA</h3>
    <table>
        <tr>
            <td colspan="4" style="text-align: right;"><b>Total Caja Chica:</b></td>
            <td style="text-align: right;"><b>{{ number_format($smallBox,2) }}</b></td>
        </tr>
    </table>

    <div class="footer">
        <p>Fecha de Generación: {{ date('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>