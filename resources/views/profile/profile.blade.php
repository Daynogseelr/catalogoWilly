@extends('app', ['page' => __('Ventas'), 'pageSlug' => 'profile'])
@section('content')
<div class="container-fluid profile-modern">
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow profile-card">
                <div class="card-header bg-gradient-primary text-white d-flex align-items-center">
                    <i class="fa fa-user-edit fa-2x me-2"></i>
                    <h5 class="mb-0">{{ __('Edit Profile') }}</h5>
                </div>
                <form method="post" action="{{ route('updateProfile') }}" autocomplete="off">
                    <div class="card-body">
                        <div class="row g-3">
                            @csrf
                            @method('post')
                            <div class="col-md-12">
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="fw-bold">{{ __('Name') }}</label>
                                    <input type="text" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" placeholder="{{ __('Name') }}" value="{{ old('name', auth()->user()->name) }}" minlength="2" maxlength="150" required onkeypress="return onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                    @include('alerts.feedback', ['field' => 'name'])
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('ci') ? ' has-danger' : '' }}">
                                    <label class="fw-bold">
                                        @if (auth()->user()->type == 'EMPRESA')
                                            {{__('Identification card')}} (*):
                                        @else
                                            {{__('Identification Document')}} (*):
                                        @endif
                                    </label>
                                    <div class="row">
                                        <div class="col-4" style="padding-right:0">
                                            <select class="form-select required" name="nationality">
                                                <option {{ auth()->user()->nationality == "V" ? 'selected' : ''}} value="V">V</option>
                                                <option {{ auth()->user()->nationality == "E" ? 'selected' : ''}} value="E">E</option>
                                                <option {{ auth()->user()->nationality == "J" ? 'selected' : ''}} value="J">J</option>
                                            </select>
                                        </div>
                                        <div class="col-8" style="padding-left:0">
                                            <input name="ci" type="text" class="form-control{{ $errors->has('ci') ? ' is-invalid' : '' }}" value="{{ old('ci', auth()->user()->ci) }}" id="ci" placeholder="Documento de identidad" minlength="7" maxlength="9" required onkeypress='return validaNumericos(event)' onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                        </div>
                                    </div>
                                    @include('alerts.feedback', ['field' => 'ci'])
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('phone') ? ' has-danger' : '' }}">
                                    <label class="fw-bold">{{__('Phone')}} (*):</label>
                                    <input name="phone" type="text" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" value="{{ old('phone', auth()->user()->phone) }}" id="phone" placeholder="{{__('Phone')}}" minlength="11" maxlength="11" required onkeypress='return validaNumericos(event)' onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                    @include('alerts.feedback', ['field' => 'phone'])
                                </div>
                            </div>
                            @if (auth()->user()->type == 'EMPRESA')
                                <div class="col-md-4">
                                    <div class="form-group{{ $errors->has('state') ? ' has-danger' : '' }}">
                                        <label class="fw-bold">{{__('State')}} (*):</label>
                                        <input name="state" type="text" class="form-control{{ $errors->has('state') ? ' is-invalid' : '' }}" value="{{ old('state', auth()->user()->state) }}" id="state" placeholder="{{__('State')}}" minlength="3" maxlength="200" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                        @include('alerts.feedback', ['field' => 'state'])
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group{{ $errors->has('city') ? ' has-danger' : '' }}">
                                        <label class="fw-bold">{{__('City')}} (*):</label>
                                        <input name="city" type="text" class="form-control{{ $errors->has('city') ? ' is-invalid' : '' }}" value="{{ old('city', auth()->user()->city) }}" id="city" placeholder="{{__('City')}}" minlength="3" maxlength="200" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                        @include('alerts.feedback', ['field' => 'city'])
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group{{ $errors->has('postal_zone') ? ' has-danger' : '' }}">
                                        <label class="fw-bold">{{__('Postal zone')}} (*):</label>
                                        <input name="postal_zone" type="text" class="form-control{{ $errors->has('postal_zone') ? ' is-invalid' : '' }}" value="{{ old('postal_zone', auth()->user()->postal_zone) }}" id="postal_zone" placeholder="{{__('Postal_zone')}}" minlength="3" maxlength="200" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                        @include('alerts.feedback', ['field' => 'postal_zone'])
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-12">
                                <div class="form-group{{ $errors->has('direction') ? ' has-danger' : '' }}">
                                    <label class="fw-bold">{{__('Direction')}} (*):</label>
                                    <input name="direction" type="text" class="form-control{{ $errors->has('direction') ? ' is-invalid' : '' }}" value="{{ old('direction', auth()->user()->direction) }}" id="direction" placeholder="{{__('Direction')}}" minlength="3" maxlength="200" required onkeyup="mayus(this);" onpaste="return false" autocomplete="off">
                                    @include('alerts.feedback', ['field' => 'direction'])
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group{{ $errors->has('email') ? ' has-danger' : '' }}">
                                    <label class="fw-bold">{{ __('Usuario') }}</label>
                                    <input type="text" name="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="{{ __('Email') }}" value="{{ old('email', auth()->user()->email) }}" minlength="5" maxlength="200" required onpaste="return false" autocomplete="off" onkeyup="mayus(this);">
                                    @include('alerts.feedback', ['field' => 'email'])
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-gradient w-100 mt-3">{{ __('Save') }}</button>
                    </div>
                </form>
                <div class="card-footer bg-light">
                    @if (auth()->user()->type == 'EMPRESA')
                        <form method="post" action="{{ route('updateProfileFile') }}" autocomplete="off" enctype="multipart/form-data">
                            @csrf
                            @method('post')
                            <div class="form-outline mb-3">
                                <label class="form-label fw-bold" for="form2Example17">{{__('Company image')}}</label>
                                <input name="logo" type="file" accept="image/jpg,image/jpeg,image/png" class="form-control" id="logo">
                                @include('alerts.feedback', ['field' => 'logo'])
                                @if ($errors->has('logo'))
                                    {{$errors->first('logo')}}
                                @endif
                            </div>
                            <button type="submit" class="btn btn-gradient w-100">{{ __('Save Picture') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow profile-card">
                <div class="card-header bg-gradient-info text-white d-flex align-items-center">
                    <i class="fa fa-lock fa-2x me-2"></i>
                    <h5 class="mb-0">{{ __('Password') }}</h5>
                </div>
                <form method="post" action="{{ route('passwordProfile') }}" autocomplete="off">
                    <div class="card-body">
                        @csrf
                        @method('post')
                        @include('alerts.success', ['key' => 'password_status'])
                        <div class="form-group{{ $errors->has('old_password') ? ' has-danger' : '' }}">
                            <label class="fw-bold">{{ __('Enter password') }}</label>
                            <input type="password" name="old_password" class="form-control{{ $errors->has('old_password') ? ' is-invalid' : '' }}" placeholder="{{ __('Enter password') }}" value="{{ old('old_password')}}" required>
                            @include('alerts.feedback', ['field' => 'old_password'])
                        </div>
                        <div class="form-group{{ $errors->has('password') ? ' has-danger' : '' }}">
                            <label class="fw-bold">{{ __('New Password') }}</label>
                            <input type="password" name="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{ __('New Password') }}" value="{{ old('password')}}" required>
                            @include('alerts.feedback', ['field' => 'password'])
                        </div>
                        <div class="form-group">
                            <label class="fw-bold">{{ __('Confirm new password') }}</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="{{ __('Confirm new password') }}" value="{{ old('password_confirmation')}}" required>
                        </div>
                        <button type="submit" class="btn btn-gradient w-100 mt-3">{{ __('Change Password') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('footer')
@endsection

@section('scripts')
@if (session($key ?? 'status'))
    <script>
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: "{{__('Log saved successfully')}}",
            showConfirmButton: false,
            timer: 1500
        })
    </script>
@endif  
@if (session($key ?? 'yes_password_status'))
    <script>
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: "{{__('Log saved successfully')}}",
            showConfirmButton: false,
            timer: 1500
        })
        password_status
    </script>
@endif 
@endsection

<style>
.profile-modern {
    background: linear-gradient(135deg, #f8fafc 0%, #e9ecef 100%);
    min-height: 100vh;
    padding-bottom: 40px;
}
.profile-card {
    border-radius: 1rem;
    background: #fff;
    box-shadow: 0 4px 16px rgba(44,62,80,.08);
    margin-bottom: 0;
    overflow: hidden;
}
.card-header {
    border-bottom: none;
    padding: 1.2rem 1.5rem;
    font-size: 1.2rem;
    font-weight: bold;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
}
.btn-gradient {
    background: linear-gradient(135deg, #72cde9 0%, #17c1e8 100%);
    color: #fff;
    font-weight: bold;
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 2px 8px rgba(44,62,80,.08);
    transition: background 0.2s, box-shadow 0.2s;
}
.btn-gradient:hover {
    background: linear-gradient(135deg, #17c1e8 0%, #72dbc1 100%);
    box-shadow: 0 4px 16px rgba(44,62,80,.12);
    color: #fff;
}
.form-label, .fw-bold {
    color: #344767;
}
input[type="text"], input[type="password"], select, .form-control {
    border-radius: 0.5rem !important;
    border: 1px solid #e0e5ec !important;
    font-size: 1rem;
}
input[type="file"] {
    border-radius: 0.5rem !important;
    border: 1px solid #e0e5ec !important;
    font-size: 1rem;
    padding: 0.5rem;
}
.form-group {
    margin-bottom: 1rem;
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #62cfae 0%, #6a82fb 100%) !important;
}
.bg-gradient-info {
    background: linear-gradient(135deg, #17c1e8 0%, #007bff 100%) !important;
}
.text-white {
    color: #fff !important;
}
</style>