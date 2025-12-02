@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Ganancias</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; } /* Slightly smaller font for more data */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; } /* Slightly less padding */
        th { background-color: #f2f2f2; }
        .total-row td { font-weight: bold; }
        .report-header { text-align: center; margin-bottom: 15px; }
        .report-header h1 { font-size: 16px; margin-bottom: 5px; }
        .report-details { margin-bottom: 15px; }
        .report-details p { margin: 1px 0; }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>Reporte de Ganancias</h1>
        <p>Generado en: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="report-details">
        <p><strong>Tipo de Método de Pago:</strong> {{ $paymentMethodTypeFilterName }}</p>
        <p><strong>Rango de Fechas:</strong> {{ $dateRangeFilterName }}</p>
        @if(!empty($searchQuery))
        <p><strong>Búsqueda:</strong> "{{ $searchQuery }}"</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Pago</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Método</th>
                <th>Referencia</th>
                <th>Vendedor</th>
                <th>Monto ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($profits as $profit)
                <tr>
                    <td>{{ $profit->id }}</td>
                    <td>{{ (new Carbon($profit->created_at))->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $profit->type ?? 'N/A' }}</td>
                    <td>{{ $profit->method ?? 'N/A' }}</td>
                    <td>{{ $profit->reference ?? 'N/A' }}</td>
                    <td>{{ $profit->seller_name ?? 'N/A' }}</td>
                    <td>{{ number_format($profit->amount ?? 0, 2, '.', '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No se encontraron ganancias para los filtros seleccionados.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="6" style="text-align: right;">TOTAL GENERAL:</td>
                <td>{{ number_format($grandTotal ?? 0, 2, '.', '') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>