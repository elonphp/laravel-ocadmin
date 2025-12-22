@extends('ocadmin::layouts.app')

@section('title', isset($setting->id) ? __('system-setting::setting.edit') : __('system-setting::setting.create'))

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" id="button-save" data-bs-toggle="tooltip" title="{{ __('ocadmin::common.save') }}" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ ocadmin_route('settings.index') }}" data-bs-toggle="tooltip" title="{{ __('ocadmin::common.back') }}" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ isset($setting->id) ? __('system-setting::setting.edit') : __('system-setting::setting.create') }}</h1>
            <ol class="breadcrumb">
                @foreach($breadcrumbs as $breadcrumb)
                    <li class="breadcrumb-item"><a href="{{ $breadcrumb->href }}">{{ $breadcrumb->text }}</a></li>
                @endforeach
                <li class="breadcrumb-item active">{{ isset($setting->id) ? __('system-setting::setting.edit') : __('system-setting::setting.create') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ isset($setting->id) ? __('system-setting::setting.edit') : __('system-setting::setting.create') }}
            </div>
            <div class="card-body">
                <form id="form-setting">
                    @csrf
                    @if(isset($setting->id))
                        @method('PUT')
                    @endif

                    <div class="row mb-3 required">
                        <label for="input-code" class="col-sm-2 col-form-label">{{ __('system-setting::setting.code') }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="code" id="input-code" class="form-control"
                                   value="{{ old('code', $setting->code) }}" placeholder="{{ __('system-setting::setting.code_placeholder') }}">
                            <div class="invalid-feedback"></div>
                            <div class="form-text">{{ __('system-setting::setting.code_hint') }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-group" class="col-sm-2 col-form-label">{{ __('system-setting::setting.group') }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="group" id="input-group" class="form-control"
                                   value="{{ old('group', $setting->group) }}" placeholder="{{ __('system-setting::setting.group_placeholder') }}">
                            <div class="invalid-feedback"></div>
                            <div class="form-text">{{ __('system-setting::setting.group_hint') }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-locale" class="col-sm-2 col-form-label">{{ __('system-setting::setting.locale') }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="locale" id="input-locale" class="form-control"
                                   value="{{ old('locale', $setting->locale) }}" placeholder="{{ __('system-setting::setting.locale_placeholder') }}">
                            <div class="invalid-feedback"></div>
                            <div class="form-text">{{ __('system-setting::setting.locale_hint') }}</div>
                        </div>
                    </div>

                    <div class="row mb-3 required">
                        <label for="input-type" class="col-sm-2 col-form-label">{{ __('system-setting::setting.type') }}</label>
                        <div class="col-sm-10">
                            <select name="type" id="input-type" class="form-select">
                                @foreach($types as $type)
                                <option value="{{ $type->value }}" {{ old('type', $setting->type?->value ?? 'text') === $type->value ? 'selected' : '' }}>
                                    {{ $type->label() }}
                                </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-content" class="col-sm-2 col-form-label">{{ __('system-setting::setting.content') }}</label>
                        <div class="col-sm-10">
                            <textarea name="content" id="input-content" class="form-control" rows="6"
                                      placeholder="{{ __('system-setting::setting.content_placeholder') }}">{{ old('content', $setting->content) }}</textarea>
                            <div class="invalid-feedback"></div>
                            <div class="form-text" id="content-hint">{{ __('system-setting::setting.content_hint') }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-note" class="col-sm-2 col-form-label">{{ __('system-setting::setting.note') }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="note" id="input-note" class="form-control"
                                   value="{{ old('note', $setting->note) }}" placeholder="{{ __('system-setting::setting.note_placeholder') }}">
                            <div class="invalid-feedback"></div>
                            <div class="form-text">{{ __('system-setting::setting.note_hint') }}</div>
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
    // Type hints
    var hints = {
        'text': '{{ __("system-setting::setting.hint_text") }}',
        'line': '{{ __("system-setting::setting.hint_line") }}',
        'json': '{{ __("system-setting::setting.hint_json") }}',
        'serialized': '{{ __("system-setting::setting.hint_serialized") }}',
        'bool': '{{ __("system-setting::setting.hint_bool") }}',
        'int': '{{ __("system-setting::setting.hint_int") }}',
        'float': '{{ __("system-setting::setting.hint_float") }}',
        'array': '{{ __("system-setting::setting.hint_array") }}'
    };

    // Update hint when type changes
    $('#input-type').on('change', function() {
        var type = $(this).val();
        $('#content-hint').text(hints[type] || '{{ __("system-setting::setting.content_hint") }}');
    }).trigger('change');

    $('#button-save').on('click', function() {
        var $btn = $(this);
        var $form = $('#form-setting');

        // Clear previous errors
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');

        // Disable button
        $btn.prop('disabled', true);

        $.ajax({
            url: '{{ isset($setting->id) ? ocadmin_route("settings.update", $setting->id) : ocadmin_route("settings.store") }}',
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
    $('#form-setting input').on('keypress', function(e) {
        if (e.which == 13) {
            e.preventDefault();
            $('#button-save').click();
        }
    });
});
</script>
@endpush
