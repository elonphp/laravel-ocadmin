@extends('ocadmin::layouts.auth')

@section('title', '登入')

@section('content')
<div id="content">
  <div class="container-fluid">
    <br/><br/>
    <div class="row justify-content-sm-center">
      <div class="col-sm-4 col-md-6">
        <div class="card">
          <div class="card-header"><i class="fas fa-lock"></i> 系統登入</div>
          <div class="card-body">
              <form id="form-login" action="{{ route('lang.ocadmin.login') }}" method="post">
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
                  <label for="input-account" class="form-label">帳號</label>
                  <div class="input-group">
                    <div class="input-group-text"><i class="fas fa-user"></i></div>
                    <input type="text" name="account" value="{{ old('account') }}" placeholder="帳號 / Email" id="input-account" class="form-control" required/>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="input-password" class="form-label">密碼</label>
                  <div class="input-group mb-2">
                    <div class="input-group-text"><i class="fas fa-lock"></i></div>
                    <input type="password" name="password" value="" placeholder="密碼" id="input-password" class="form-control" required/>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="form-check">
                    <input type="checkbox" name="remember" id="input-remember" class="form-check-input" value="1">
                    <label for="input-remember" class="form-check-label">記住我</label>
                  </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> 登入</button>
                </div>
              </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
