@extends('ocadmin::layouts.app')

@section('title', $user->exists ? '編輯帳號' : '新增帳號')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-account" data-bs-toggle="tooltip" title="儲存" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ route('lang.ocadmin.account.account.index') }}" data-bs-toggle="tooltip" title="返回" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $user->exists ? '編輯帳號' : '新增帳號' }}</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('lang.ocadmin.dashboard') }}">首頁</a></li>
                <li class="breadcrumb-item"><a href="#">帳號管理</a></li>
                <li class="breadcrumb-item"><a href="{{ route('lang.ocadmin.account.account.index') }}">帳號</a></li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-pencil"></i> {{ $user->exists ? '編輯帳號' : '新增帳號' }}</div>
            <div class="card-body">
                <form id="form-account" action="{{ $user->exists ? route('lang.ocadmin.account.account.update', $user) : route('lang.ocadmin.account.account.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($user->exists)
                    @method('PUT')
                    @endif

                    <div class="row mb-3 required" id="input-username">
                        <label for="input-username-field" class="col-sm-2 col-form-label">帳號</label>
                        <div class="col-sm-10">
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" placeholder="請輸入帳號" id="input-username-field" class="form-control">
                            <div id="error-username" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-email">
                        <label for="input-email-field" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="請輸入 Email" id="input-email-field" class="form-control">
                            <div id="error-email" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-mobile">
                        <label for="input-mobile-field" class="col-sm-2 col-form-label">手機</label>
                        <div class="col-sm-10">
                            <input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}" placeholder="請輸入手機號碼" id="input-mobile-field" class="form-control">
                            <div id="error-mobile" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 {{ $user->exists ? '' : 'required' }}" id="input-password">
                        <label for="input-password-field" class="col-sm-2 col-form-label">密碼</label>
                        <div class="col-sm-10">
                            <input type="password" name="password" value="" placeholder="{{ $user->exists ? '留空則不變更密碼' : '請輸入密碼' }}" id="input-password-field" class="form-control" autocomplete="new-password">
                            <div id="error-password" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 {{ $user->exists ? '' : 'required' }}" id="input-password-confirmation">
                        <label for="input-password-confirmation-field" class="col-sm-2 col-form-label">確認密碼</label>
                        <div class="col-sm-10">
                            <input type="password" name="password_confirmation" value="" placeholder="{{ $user->exists ? '留空則不變更密碼' : '請再次輸入密碼' }}" id="input-password-confirmation-field" class="form-control" autocomplete="new-password">
                            <div id="error-password_confirmation" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-name">
                        <label for="input-name-field" class="col-sm-2 col-form-label">姓名</label>
                        <div class="col-sm-10">
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="請輸入姓名" id="input-name-field" class="form-control">
                            <div id="error-name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-display-name">
                        <label for="input-display-name-field" class="col-sm-2 col-form-label">顯示名稱</label>
                        <div class="col-sm-10">
                            <input type="text" name="display_name" value="{{ old('display_name', $user->display_name) }}" placeholder="請輸入顯示名稱" id="input-display-name-field" class="form-control">
                            <div id="error-display_name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-is-active">
                        <label class="col-sm-2 col-form-label">狀態</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch form-switch-lg">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" id="input-is-active-field" class="form-check-input" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                            </div>
                            <div id="error-is_active" class="invalid-feedback"></div>
                        </div>
                    </div>

                    @if($user->exists)
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">最後登入</label>
                        <div class="col-sm-10">
                            <p class="form-control-plaintext">
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->format('Y-m-d H:i:s') }}
                                    @if($user->last_login_ip)
                                        ({{ $user->last_login_ip }})
                                    @endif
                                @else
                                    尚未登入
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif

                    @if($user->exists)
                    <input type="hidden" name="user_id" value="{{ $user->id }}" id="input-user-id">
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
