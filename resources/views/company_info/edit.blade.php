@extends('app', ['page' => __('Editar Empresa'), 'pageSlug' => 'company_info'])

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4">Editar Información de la Empresa</h2>
    <form method="POST" action="{{ route('company_info.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="row mb-3">
            <div class="col-md-3 text-center">
                <label for="logo" class="form-label fw-bold">Logo</label>
                <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                @if($info && $info->logo)
                    <img src="{{ asset('storage/' . $info->logo) }}" class="img-fluid rounded mt-2" style="max-height:120px;">
                @endif
            </div>
            <div class="col-md-9">
                <label for="name" class="form-label fw-bold">Nombre</label>
                <input type="text" class="form-control mb-2" id="name" name="name" value="{{ old('name', $info->name ?? '') }}" required>
                <label for="address" class="form-label fw-bold">Dirección</label>
                <input type="text" class="form-control mb-2" id="address" name="address" value="{{ old('address', $info->address ?? '') }}" required>
                <label for="rif" class="form-label fw-bold">RIF</label>
                <input type="text" class="form-control mb-2" id="rif" name="rif" value="{{ old('rif', $info->rif ?? '') }}">
                <label for="phone" class="form-label fw-bold">Teléfono (WhatsApp)</label>
                <input type="text" class="form-control mb-2" id="phone" name="phone" value="{{ old('phone', $info->phone ?? '') }}" placeholder="+584XXXXXXXXX">
                <label for="description" class="form-label fw-bold">Descripción</label>
                <textarea class="form-control mb-2" id="description" name="description" rows="3">{{ old('description', $info->description ?? '') }}</textarea>
            </div>
        </div>
        <div class="row mb-3">
            @foreach(['photo1','photo2','photo3'] as $p)
                <div class="col-md-4">
                    <label for="{{ $p }}" class="form-label fw-bold">Foto {{ substr($p,-1) }}</label>
                    <input type="file" class="form-control" id="{{ $p }}" name="{{ $p }}" accept="image/*">
                    @if($info && $info->$p)
                        <img src="{{ asset('storage/' . $info->$p) }}" class="img-fluid rounded mt-2" style="max-height:120px;">
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mb-3">
            <h5 class="fw-bold">Redes Sociales</h5>
            @php
                $redes = ['facebook','instagram','twitter','whatsapp','youtube','tiktok'];
            @endphp
            <div class="row">
                @foreach($redes as $red)
                    <div class="col-md-4 mb-2">
                        <label for="socials[{{ $red }}]" class="form-label">{{ ucfirst($red) }}</label>
                        <input type="url" class="form-control" name="socials[{{ $red }}]" id="socials[{{ $red }}]"
                            value="{{ old('socials.'.$red, $info->socials[$red] ?? '') }}" placeholder="https://{{ $red }}.com/tuempresa">
                    </div>
                @endforeach
            </div>
        </div>
        <div class="mb-3">
            <label for="shipping_methods" class="form-label fw-bold">Métodos de Envío y Traslado</label>
            <textarea class="form-control" id="shipping_methods" name="shipping_methods" rows="12">{{ old('shipping_methods', $info->shipping_methods ?? '') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="payment_methods" class="form-label fw-bold">Métodos de Pago</label>
            <textarea class="form-control" id="payment_methods" name="payment_methods" rows="8">{{ old('payment_methods', $info->payment_methods ?? '') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label fw-bold">Clave Mayorista (solo se muestra en editar)</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Dejar en blanco para mantener la actual">
            <div class="form-text">Si quieres cambiar la clave para acceder al precio mayorista, ingresa una nueva. Dejar vacío no cambiará la clave.</div>
        </div>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="{{ route('company_info.show') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection