@extends('ocadmin::layouts.app')

@section('title', $user->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-member" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.member.member.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $user->exists ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ $user->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <form action="{{ $user->exists ? route('lang.ocadmin.member.member.update', $user) : route('lang.ocadmin.member.member.store') }}" method="post" id="form-member" data-oc-toggle="ajax">
                    @csrf
                    @if($user->exists)
                    @method('PUT')
                    @endif

                    <div class="row mb-3">
                        <label for="input-first_name" class="col-sm-2 col-form-label">{{ $lang->column_first_name }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" placeholder="{{ $lang->placeholder_first_name }}" id="input-first_name" class="form-control" maxlength="100">
                            <div id="error-first_name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-last_name" class="col-sm-2 col-form-label">{{ $lang->column_last_name }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" placeholder="{{ $lang->placeholder_last_name }}" id="input-last_name" class="form-control" maxlength="100">
                            <div id="error-last_name" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-username" class="col-sm-2 col-form-label">{{ $lang->column_username }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" placeholder="{{ $lang->placeholder_username }}" id="input-username" class="form-control" maxlength="100">
                            <div id="error-username" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required">
                        <label for="input-email" class="col-sm-2 col-form-label">{{ $lang->column_email }}</label>
                        <div class="col-sm-10">
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="{{ $lang->placeholder_email }}" id="input-email" class="form-control" maxlength="255">
                            <div id="error-email" class="invalid-feedback"></div>
                        </div>
                    </div>

                    @php
                        $hasBackend = $user->exists && $user->hasBackendRole();
                    @endphp

                    @if($hasBackend)
                    <div class="alert alert-warning">
                        <i class="fa-solid fa-triangle-exclamation"></i> {{ $lang->warning_backend_password }}
                    </div>
                    @endif

                    <div class="row mb-3 {{ $user->exists ? '' : 'required' }}">
                        <label for="input-password" class="col-sm-2 col-form-label">{{ $lang->column_password }}</label>
                        <div class="col-sm-10">
                            <input type="password" name="password" value="" placeholder="{{ $lang->placeholder_password }}" id="input-password" class="form-control" @if($hasBackend) disabled @endif>
                            <div id="error-password" class="invalid-feedback"></div>
                            @if($user->exists && !$hasBackend)
                            <div class="form-text">{{ $lang->help_password_edit }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3 {{ $user->exists ? '' : 'required' }}">
                        <label for="input-password_confirmation" class="col-sm-2 col-form-label">{{ $lang->column_password_confirm }}</label>
                        <div class="col-sm-10">
                            <input type="password" name="password_confirmation" value="" placeholder="{{ $lang->placeholder_password_confirm }}" id="input-password_confirmation" class="form-control" @if($hasBackend) disabled @endif>
                            <div id="error-password_confirmation" class="invalid-feedback"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
