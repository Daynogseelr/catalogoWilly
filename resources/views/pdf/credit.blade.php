@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Cuentas por Cobrar</title>
    <style>
        body { font-family: sans-serif; font-size: 8.5px; } /* Slightly smaller for more columns */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: left; } /* Less padding */
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
        <h1>Reporte de Cuentas por Cobrar</h1>
        <p>Generado en: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="report-details">
        <p><strong>Cliente:</strong> {{ $clientFilterName }}</p>
        <p><strong>Estado:</strong> {{ $statusFilterName }}</p>
        <p><strong>Rango de Fechas (Factura):</strong> {{ $dateRangeFilterName }}</p>
        @if(!empty($searchQuery))
        <p><strong>Búsqueda:</strong> "{{ $searchQuery }}"</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Código Factura</th> {{-- Changed header text --}}
                <th>Fecha Factura</th>
                <th>Fecha Vencimiento</th>
                <th>Cliente</th>
                <th>Monto Total ($)</th>
                <th>Monto Pagado ($)</th>
                <th>Saldo Pendiente ($)</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bills as $bill)
                <tr>
                    <td>{{ $bill->code ?? 'N/A' }}</td> {{-- Accessing the 'code' property --}}
                    <td>{{ (new Carbon($bill->created_at))->format('d/m/Y') }}</td>
                    <td>{{ $bill->due_date ? $bill->due_date->format('d/m/Y') : 'N/A' }}</td> {{-- Using accessor --}}
                    <td>{{ $bill->client_name ?? 'N/A' }}</td>
                    <td>{{ number_format($bill->net_amount ?? 0, 2, '.', '') }}</td>
                    <td>{{ number_format($bill->amount_paid ?? 0, 2, '.', '') }}</td> {{-- Using accessor --}}
                    <td>{{ number_format($bill->payment ?? 0, 2, '.', '') }}</td> {{-- Direct payment field --}}
                    <td>{{ $bill->calculated_status }}</td> {{-- Using accessor --}}
                </tr>
            @empty
                <tr>
                    <td colspan="8">No se encontraron facturas pendientes para los filtros seleccionados.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">TOTALES:</td>
                <td>{{ number_format($grandTotalAmount ?? 0, 2, '.', '') }}</td>
                <td>{{ number_format($grandTotalPaid ?? 0, 2, '.', '') }}</td>
                <td>{{ number_format($grandTotalOutstanding ?? 0, 2, '.', '') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>