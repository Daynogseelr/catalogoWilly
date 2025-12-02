@extends('app', ['page' => __('Información'), 'pageSlug' => 'company_info'])

@section('content')
<div class="container py-4">
    <style>
        /* Asegura que el logo tenga un tamaño estándar y no desborde */
        .company-logo {
            width: 140px;
            height: 140px;
            object-fit: cover; /* recorta manteniendo aspecto */
            display: inline-block;
        }

        /* pequeños dispositivos: reducir ligeramente el logo */
        @media (max-width: 576px) {
            .company-logo {
                width: 100px;
                height: 100px;
            }
        }
    </style>
    <div class="row mb-4">
        <div class="col-md-3 text-center">
            <div class="card company-card">
                <div class="card-body d-flex flex-column align-items-center">
                    @if($info && $info->logo)
                        <img src="{{ asset('storage/' . $info->logo) }}" class="company-logo mb-3 rounded-circle border" alt="Logo de la empresa">
                    @else
                        <img src="https://via.placeholder.com/140x140?text=Logo" class="company-logo mb-3 rounded-circle border" alt="Logo placeholder">
                    @endif
                    <div class="mt-2 text-center">
                        <span class="company-badge"><i class="fa-solid fa-phone me-1"></i> {{ $info->phone ?? 'Sin teléfono' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card company-card">
                <div class="card-body">
                    <h2 class="fw-bold mb-2 d-flex align-items-center company-name">
                        <i class="fa-solid fa-building me-2 text-accent"></i>{{ $info->name ?? 'Nombre de la empresa' }}
                    </h2>
                    <p class="mb-1"><i class="fa-solid fa-location-dot me-1 text-secondary"></i> <strong>Dirección:</strong> {{ $info->address ?? '-' }}</p>
                    @if($info && $info->rif)
                        <p class="mb-1"><i class="fa-solid fa-id-card me-1 text-secondary"></i> <strong>RIF:</strong> {{ $info->rif }}</p>
                    @endif
                    <p class="mb-2 lead">{!! nl2br(e($info->description ?? '')) !!}</p>
                    @auth
                        <a href="{{ route('company_info.edit') }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-edit"></i> Editar información
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        @foreach(['photo1','photo2','photo3'] as $p)
            @if($info && $info->$p)
                <div class="col-md-4 mb-3">
                    <img src="{{ asset('storage/' . $info->$p) }}" class="img-fluid company-photo rounded shadow-sm" alt="Foto de la empresa">
                </div>
            @endif
        @endforeach
    </div>
    <div class="mb-4">
        <div class="company-section-title">
            <i class="fa-solid fa-globe"></i> Redes Sociales
        </div>
        <div class="d-flex flex-wrap gap-2">
            @php
                $icons = [
                    'facebook' => 'fab fa-facebook',
                    'instagram' => 'fab fa-instagram',
                    'twitter' => 'fab fa-twitter',
                    'whatsapp' => 'fab fa-whatsapp',
                    'youtube' => 'fab fa-youtube',
                    'tiktok' => 'fab fa-tiktok',
                ];
                $colors = [
                    'facebook' => 'bg-primary text-white',
                    'instagram' => 'bg-danger text-white',
                    'twitter' => 'bg-info text-white',
                    'whatsapp' => 'bg-success text-white',
                    'youtube' => 'bg-danger text-white',
                    'tiktok' => 'bg-dark text-white',
                ];
            @endphp
            @if($info && $info->socials)
                @foreach($info->socials as $red => $url)
                    @if($url)
                        <a href="{{ $url }}" target="_blank" class="btn company-social-btn {{ $colors[$red] ?? 'btn-light border' }} d-flex align-items-center gap-2 shadow-sm">
                            <i class="{{ $icons[$red] ?? 'fab fa-globe' }} fs-5"></i> {{ ucfirst($red) }}
                        </a>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
    <div class="mb-4">
        <div class="company-section-title">
            <i class="fa-solid fa-truck"></i> Métodos de Envío y Traslado
        </div>
        <div class="card company-card">
            <div class="card-body">
                {!! nl2br(e($info->shipping_methods ?? 'No especificado.')) !!}
            </div>
        </div>
    </div>
    <div class="mb-4">
        <div class="company-section-title">
            <i class="fa-solid fa-credit-card"></i> Métodos de Pago
        </div>
        <div class="card company-card">
            <div class="card-body">
                {!! nl2br(e($info->payment_methods ?? 'No especificado.')) !!}
            </div>
        </div>
    </div>
</div>
@endsection