<!DOCTYPE html>
<html>
<head>
    <title>{{ __('Product Report') }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .filters {
            margin-bottom: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .filters p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #e6f7ff; /* Light blue background */
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('Reporte de Productos') }}</h1>
    </div>

    <div class="filters">
        <p><strong>{{ __('Fecha de Reporte') }}:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        <p><strong>{{ __('Filtro por Stock') }}:</strong> {{ $stockFilterName ?? __('Todos los Productos') }}</p>
        @if (!empty($searchQuery))
            <p><strong>{{ __('Filtro por Busqueda') }}:</strong> "{{ $searchQuery }}"</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">{{__('Code')}}</th>
                <th>{{__('Name')}}</th>
                <th class="text-right">{{__('Price')}} ($)</th>
                <th class="text-center">{{__('Quantity')}}</th>
                <th class="text-right">{{__('Total')}} ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td class="text-center">{{ $product->code }}</td>
                    <td>{{ $product->name }}</td>
                    <td class="text-right">{{ number_format($product->price, 2, '.', ',') }}</td>
                     <td class="text-right">{{ $product->current_stock_quantity ?? 0 }}</td> {{-- Display current_stock_quantity --}}
                    <td class="text-right">{{ number_format($product->individual_total ?? 0, 2, '.', '') }}</td> {{-- Display the calculated individual total --}}
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">{{ __('No se encontraron productos que coincidan con tus criterios.') }}</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>{{ __('TOTAL') }}:</strong></td>
                <td class="text-right"><strong>{{ number_format($total, 2, '.', ',') }} $</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p></p>
    </div>
</body>
</html>