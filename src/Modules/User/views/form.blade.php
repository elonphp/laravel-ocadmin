@extends('ocadmin::layouts.app')

@section('title', isset($user->id) ? __('user::user.edit') : __('user::user.create'))

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" id="button-save" data-bs-toggle="tooltip" title="{{ __('ocadmin::common.save') }}" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ ocadmin_route('users.index') }}" data-bs-toggle="tooltip" title="{{ __('ocadmin::common.back') }}" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ isset($user->id) ? __('user::user.edit') : __('user::user.create') }}</h1>
            <ol class="breadcrumb">
                @foreach($breadcrumbs as $breadcrumb)
                    <li class="breadcrumb-item"><a href="{{ $breadcrumb->href }}">{{ $breadcrumb->text }}</a></li>
                @endforeach
                <li class="breadcrumb-item active">{{ isset($user->id) ? __('user::user.edit') : __('user::user.create') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ isset($user->id) ? __('user::user.edit') : __('user::user.create') }}
            </div>
            <div class="card-body">
                <form id="form-user">
                    @csrf
                    @if(isset($user->id))
                        @method('PUT')
                    @endif

                    <div class="row mb-3 required">
                        <label for="input-name" class="col-sm-2 col-form-label">{{ __('user::user.name') }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="name" id="input-name" class="form-control"
                                   value="{{ old('name', $user->name) }}" placeholder="{{ __('user::user.name') }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 required">
                        <label for="input-email" class="col-sm-2 col-form-label">{{ __('user::user.email') }}</label>
                        <div class="col-sm-10">
                            <input type="email" name="email" id="input-email" class="form-control"
                                   value="{{ old('email', $user->email) }}" placeholder="{{ __('user::user.email') }}">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 {{ isset($user->id) ? '' : 'required' }}">
                        <label for="input-password" class="col-sm-2 col-form-label">{{ __('user::user.password') }}</label>
                        <div class="col-sm-10">
                            <input type="password" name="password" id="input-password" class="form-control"
                                   placeholder="{{ isset($user->id) ? __('user::user.password_hint') : __('user::user.password') }}" autocomplete="new-password">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3 {{ isset($user->id) ? '' : 'required' }}">
                        <label for="input-password-confirmation" class="col-sm-2 col-form-label">{{ __('user::user.password_confirmation') }}</label>
                        <div class="col-sm-10">
                            <input type="password" name="password_confirmation" id="input-password-confirmation" class="form-control"
                                   placeholder="{{ isset($user->id) ? __('user::user.password_hint') : __('user::user.password_confirmation') }}" autocomplete="new-password">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#button-save').on('click', function() {
        var $btn = $(this);
        var $form = $('#form-user');

        // Clear previous errors
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');

        // Disable button
        $btn.prop('disabled', true);

        $.ajax({
            url: '{{ isset($user->id) ? ocadmin_route("users.update", $user->id) : ocadmin_route("users.store") }}',
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        var $input = $form.find('[name="' + field + '"]');
                        $input.addClass('is-invalid');
                        $input.siblings('.invalid-feedback').text(messages[0]);
                    });
                    toastr.error('{{ __("ocadmin::messages.validation_error") }}');
                } else {
                    toastr.error('{{ __("ocadmin::messages.error") }}');
                }
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    // Enter key submit
    $('#form-user input').on('keypress', function(e) {
        if (e.which == 13) {
            e.preventDefault();
            $('#button-save').click();
        }
    });
});
</script>
@endpush
