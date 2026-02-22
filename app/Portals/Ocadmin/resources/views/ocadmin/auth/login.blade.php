@extends('ocadmin::layouts.auth')

@section('title', 'Login')

@section('content')
<div class="login-box">
    <div class="card">
        <div class="card-header text-center">
            <img src="{{ versioned_asset('assets/ocadmin/image/logo.png') }}" alt="Ocadmin" title="Ocadmin">
        </div>
        <div class="card-body">
            <form action="{{ route('lang.ocadmin.login') }}" method="POST">
                @csrf

                @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control" placeholder="Email" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                    <label for="remember" class="form-check-label">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-sign-in"></i> Login
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
