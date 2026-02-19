@extends('ocadmin::layouts.app')

@section('title', $lang->heading_title)

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">{{ $lang->heading_title }}</h3>
            </div>
            <div class="col-sm-6">
                @include('ocadmin::layouts.partials.breadcrumb')
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="mb-3 text-end">
            <button type="submit" form="form-profile" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                <i class="bi bi-floppy"></i>
            </button>
            <a href="{{ route('lang.ocadmin.dashboard') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-circle"></i> {{ $lang->text_edit }}
            </div>
            <div class="card-body">
                <form action="{{ route('lang.ocadmin.account.profile.update') }}" method="post" id="form-profile" data-oc-toggle="ajax">
                    @csrf
                    @method('PUT')

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

                    <div class="row mb-3 required">
                        <label for="input-email" class="col-sm-2 col-form-label">{{ $lang->column_email }}</label>
                        <div class="col-sm-10">
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="{{ $lang->placeholder_email }}" id="input-email" class="form-control" maxlength="255">
                            <div id="error-email" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <label for="input-current_password" class="col-sm-2 col-form-label">{{ $lang->column_current_password }}</label>
                        <div class="col-sm-10">
                            <input type="password" name="current_password" value="" placeholder="{{ $lang->placeholder_current_password }}" id="input-current_password" class="form-control">
                            <div id="error-current_password" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-password" class="col-sm-2 col-form-label">{{ $lang->column_new_password }}</label>
                        <div class="col-sm-10">
                            <input type="password" name="password" value="" placeholder="{{ $lang->placeholder_new_password }}" id="input-password" class="form-control">
                            <div id="error-password" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-password_confirmation" class="col-sm-2 col-form-label">{{ $lang->column_new_password_confirm }}</label>
                        <div class="col-sm-10">
                            <input type="password" name="password_confirmation" value="" placeholder="{{ $lang->placeholder_new_password_confirm }}" id="input-password_confirmation" class="form-control">
                            <div id="error-password_confirmation" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-10 offset-sm-2">
                            <div class="form-text">{{ $lang->help_password }}</div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
