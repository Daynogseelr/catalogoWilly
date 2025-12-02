@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Facturas</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total-row td { font-weight: bold; }
        .report-header { text-align: center; margin-bottom: 20px; }
        .report-details { margin-bottom: 20px; }
        .report-details p { margin: 2px 0; }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>Reporte de Facturas</h1>
        <p>Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="report-details">
        <p><strong>Tipo de Factura:</strong> {{ $billTypeFilterName }}</p>
        <p><strong>Rango de Fechas:</strong> {{ $dateRangeFilterName }}</p>
        {{-- Removed <p><strong>Mes:</strong> {{ $monthFilterName }}</p> --}}
        @if(!empty($searchQuery))
        <p><strong>Búsqueda:</strong> "{{ $searchQuery }}"</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Total ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bills as $bill)
                <tr>
                    <td>{{ $bill->code }}</td>
                    <td>{{ (new Carbon($bill->created_at))->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $bill->client_name ?? 'N/A' }}</td>
                    <td>{{ $bill->type }}</td>
                    <td>{{ number_format($bill->net_amount ?? 0, 2, '.', '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No se encontraron facturas para los filtros seleccionados.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">TOTAL GENERAL:</td>
                <td>{{ number_format($grandTotal ?? 0, 2, '.', '') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>