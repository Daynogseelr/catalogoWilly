{{-- filepath: c:\xampp\htdocs\digicatalogo\resources\views\login\login.blade.php --}}
@extends('app', ['class' => 'login-page', 'page' => __(''), 'contentClass' => 'login-page', 'pageSlug' => 'login'])
@section('content')
<style>
    body {
        background: linear-gradient(135deg, #e3f2fd 0%, #f5f6fa 100%);
        min-height: 100vh;
    }
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-card {
        background: #fff;
        border-radius: 1.5rem;
        box-shadow: 0 8px 32px 0 rgba(33,150,243,0.10);
        padding: 2.5rem 2rem 2rem 2rem;
        max-width: 400px;
        width: 100%;
        margin: auto;
        transition: box-shadow 0.3s;
        position: relative;
    }
    .login-card:hover {
        box-shadow: 0 16px 48px 0 rgba(33,150,243,0.18);
    }
    .logo-img {
        display: block;
        margin: 0 auto;
        max-width: 120px;
        filter: drop-shadow(0 2px 8px rgba(33,150,243,0.10));
    }
    .logo-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 2rem;
        color: #2196f3;
        letter-spacing: 2px;
        text-align: center;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    .form-control {
        background: #f4f8fb;
        border: 1px solid #bdbdbd;
        border-radius: 0.75rem;
        font-size: 1rem;
        padding-right: 2.5rem;
        color: #333;
        box-shadow: none;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        border-color: #2196f3;
        background: #fff;
        box-shadow: 0 0 0 0.2rem rgba(33,150,243,0.10);
    }
    .btn-login {
        background: linear-gradient(90deg, #2196f3 0%, #00bcd4 100%);
        color: #fff;
        border-radius: 0.75rem;
        font-weight: 600;
        box-shadow: 0 4px 16px 0 rgba(33,150,243,0.10);
        transition: background 0.2s;
        font-size: 1.1rem;
        border: none;
    }
    .btn-login:hover {
        background: linear-gradient(90deg, #1565c0 0%, #0097a7 100%);
        color: #fff;
    }
    .show-password {
        position: absolute;
        right: 1.2rem;
        top: 30%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #2196f3;
        font-size: 1.3rem;
        z-index: 2;
    }
    .forgot-link {
        color: #2196f3;
        text-decoration: underline;
        font-size: 0.95rem;
        display: block;
        text-align: right;
        margin-top: -10px;
        margin-bottom: 18px;
    }
    .login-card .card-body {
        background: transparent;
        border-radius: 1.5rem;
        padding: 0;
    }
    h5 {
        color:#00bcd4 !important;
        font-weight: 500;
    }
    @media (max-width: 500px) {
        .login-card {
            padding: 1.5rem 0.5rem;
        }
        .logo-img {
            max-width: 90px;
        }
    }
</style>
<div class="login-container">
    <div class="login-card">
        <img src="{{ asset('assets/img/teles5.png') }}" alt="TelematicsTech Logo" class="logo-img">
        <div class="card-body">
            <h5 class="logo-title mb-2">{{ __('TELEMATICSTECH') }}</h5>
            <div class="text-center mb-3" style="color:  #1565c0; font-size:1.1rem; font-weight:500;">
                SMART SYSTEM
            </div>
            <form method="post" action="{{ route('logeo') }}" autocomplete="off">
                @csrf
                @include('alerts.success')
                @include('alerts.feedback', ['field' => 'user2'])
                <div class="form-outline mb-4 position-relative {{ $errors->has('user') ? ' has-danger' : '' }}">
                    <input name="user" type="text"
                        class="form-control {{ $errors->has('user') ? ' is-invalid' : '' }}"
                        value="{{ old('user') }}" id="user" placeholder="Usuario"
                        title="Es obligatorio un Usuario" minlength="5" maxlength="100"
                        onpaste="return false" autocomplete="off" onkeyup="mayus(this);" required>
                    <label class="form-label" for="user">{{ __('Usuario') }}</label>
                    @include('alerts.feedback', ['field' => 'user'])
                </div>
                <div class="form-outline mb-2 position-relative {{ $errors->has('password') ? ' has-danger' : '' }}">
                    <input name="password" type="password"
                        class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}"
                        value="{{ old('password') }}" id="password" placeholder="Contraseña"
                        title="Es obligatorio una contraseña" minlength="4" maxlength="20"
                        required>
                    <label class="form-label" for="password">{{ __('Contraseña') }}</label>
                    <span class="show-password" onclick="togglePassword()" title="Mostrar/Ocultar contraseña">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </span>
                    @include('alerts.feedback', ['field' => 'password'])
                </div>
                <div class="pt-1 mb-2 text-center">
                    <button class="btn btn-login btn-lg w-100" type="submit">{{ __('Iniciar') }}</button>
                </div>
            </form>
        </div>
        @include('footer')
    </div>
</div>
@endsection
@section('scripts')
<!-- Bootstrap Icons CDN para el icono del ojo -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        }
    }
</script>
@endsection