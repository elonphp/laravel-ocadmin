@extends('ocadmin::layouts.app')

@section('title', $user->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-user" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.user.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
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
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a href="#tab-data" data-bs-toggle="tab" class="nav-link active">{{ $lang->tab_data }}</a></li>
                    <li class="nav-item"><a href="#tab-role" data-bs-toggle="tab" class="nav-link">{{ $lang->tab_role }}</a></li>
                </ul>
                <form action="{{ $user->exists ? route('lang.ocadmin.system.user.update', $user) : route('lang.ocadmin.system.user.store') }}" method="post" id="form-user" data-oc-toggle="ajax">
                    @csrf
                    @if($user->exists)
                    @method('PUT')
                    @endif

                    <div class="tab-content">
                        {{-- 基本資料 --}}
                        <div id="tab-data" class="tab-pane active">
                            <div class="row mb-3" id="input-first-name">
                                <label for="input-first-name-field" class="col-sm-2 col-form-label">{{ $lang->column_first_name }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" placeholder="{{ $lang->placeholder_first_name }}" id="input-first-name-field" class="form-control" maxlength="100">
                                    <div id="error-first-name" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3" id="input-last-name">
                                <label for="input-last-name-field" class="col-sm-2 col-form-label">{{ $lang->column_last_name }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" placeholder="{{ $lang->placeholder_last_name }}" id="input-last-name-field" class="form-control" maxlength="100">
                                    <div id="error-last-name" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3 required" id="input-username">
                                <label for="input-username-field" class="col-sm-2 col-form-label">{{ $lang->column_username }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="username" value="{{ old('username', $user->username) }}" placeholder="{{ $lang->placeholder_username }}" id="input-username-field" class="form-control" maxlength="100">
                                    <div id="error-username" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3 required" id="input-email">
                                <label for="input-email-field" class="col-sm-2 col-form-label">{{ $lang->column_email }}</label>
                                <div class="col-sm-10">
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="{{ $lang->placeholder_email }}" id="input-email-field" class="form-control" maxlength="255">
                                    <div id="error-email" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3 {{ $user->exists ? '' : 'required' }}" id="input-password">
                                <label for="input-password-field" class="col-sm-2 col-form-label">{{ $lang->column_password }}</label>
                                <div class="col-sm-10">
                                    <input type="password" name="password" value="" placeholder="{{ $lang->placeholder_password }}" id="input-password-field" class="form-control">
                                    <div id="error-password" class="invalid-feedback"></div>
                                    @if($user->exists)
                                    <div class="form-text">{{ $lang->help_password_edit }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3 {{ $user->exists ? '' : 'required' }}" id="input-password-confirmation">
                                <label for="input-password-confirmation-field" class="col-sm-2 col-form-label">{{ $lang->column_password_confirm }}</label>
                                <div class="col-sm-10">
                                    <input type="password" name="password_confirmation" value="" placeholder="{{ $lang->placeholder_password_confirm }}" id="input-password-confirmation-field" class="form-control">
                                    <div id="error-password-confirmation" class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        {{-- 角色指派 --}}
                        <div id="tab-role" class="tab-pane">
                            <div class="row">
                                @forelse($roles as $role)
                                <div class="col-lg-4 col-md-6 mb-2">
                                    <div class="form-check">
                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}" class="form-check-input" @checked(in_array($role->id, $userRoles))>
                                        <label for="role-{{ $role->id }}" class="form-check-label">
                                            {{ $role->display_name }}
                                            <small class="text-muted d-block"><code>{{ $role->name }}</code></small>
                                        </label>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center text-muted py-4">{{ $lang->text_no_data }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
