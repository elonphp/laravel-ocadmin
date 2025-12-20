@extends('ocadmin::layouts.auth')

@section('title', __('ocadmin::auth.login'))

@section('content')
<div id="content">
    <div class="container-fluid">
        <br/><br/>
        <div class="row justify-content-sm-center">
            <div class="col-sm-4 col-md-6">
                <div class="card">
                    <div class="card-header"><i class="fas fa-lock"></i> {{ __('ocadmin::auth.login') }}</div>
                    <div class="card-body">
                        <form id="form-login" action="{{ ocadmin_route('login') }}" method="post">
                            @csrf

                            @if($errors->any())
                            <div class="alert alert-danger alert-dismissible">
                                <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
                            </div>
                            @endif

                            @if(session('error'))
                            <div class="alert alert-danger alert-dismissible">
                                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            </div>
                            @endif

                            <div class="row mb-3">
                                <label for="input-email" class="form-label">{{ __('ocadmin::auth.email') }}</label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-user"></i></div>
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('ocadmin::auth.email') }}" id="input-email" class="form-control" required autofocus/>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="input-password" class="form-label">{{ __('ocadmin::auth.password') }}</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-text"><i class="fas fa-lock"></i></div>
                                    <input type="password" name="password" value="" placeholder="{{ __('ocadmin::auth.password') }}" id="input-password" class="form-control" required/>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="remember" id="input-remember" class="form-check-input" value="1" {{ old('remember') ? 'checked' : '' }}>
                                    <label for="input-remember" class="form-check-label">{{ __('ocadmin::auth.remember_me') }}</label>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> {{ __('ocadmin::auth.login') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Language Switcher -->
                <div class="text-center mt-3">
                    @include('ocadmin::components.locale-switcher-simple')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
