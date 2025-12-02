@extends('app', ['page' => __('Transfer Detail'), 'pageSlug' => 'inventory-transfers'])
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Transferencia {{ $transfer->code }}</h4>
        </div>
        <div class="card-body">
            <p><strong>Origen:</strong> {{ optional($transfer->fromSucursal)->name }}</p>
            <p><strong>Destino:</strong> {{ optional($transfer->toSucursal)->name }}</p>
            <p><strong>Notas:</strong> {{ $transfer->notes }}</p>
            <table class="table">
                <thead><tr><th>Producto</th><th>Cantidad</th></tr></thead>
                <tbody>
                    @foreach($transfer->items as $it)
                        <tr>
                            <td>{{ optional($it->product)->code }} - {{ optional($it->product)->name }}</td>
                            <td>{{ $it->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
