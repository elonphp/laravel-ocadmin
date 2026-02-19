@extends('ocadmin::layouts.app')

@section('title', $permission->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">{{ $permission->exists ? $lang->text_edit : $lang->text_add }}</h3>
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
            <button type="submit" form="form-permission" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                <i class="bi bi-floppy"></i>
            </button>
            <a href="{{ route('lang.ocadmin.system.permission.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil"></i> {{ $permission->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a href="#tab-trans" data-bs-toggle="tab" class="nav-link active">{{ $lang->tab_trans }}</a></li>
                    <li class="nav-item"><a href="#tab-data" data-bs-toggle="tab" class="nav-link">{{ $lang->tab_data }}</a></li>
                </ul>
                <form action="{{ $permission->exists ? route('lang.ocadmin.system.permission.update', $permission) : route('lang.ocadmin.system.permission.store') }}" method="post" id="form-permission" data-oc-toggle="ajax">
                    @csrf
                    @if($permission->exists)
                    @method('PUT')
                    @endif

                    @php $translationsArray = $permission->exists ? $permission->getTranslationsArray() : []; @endphp

                    <div class="tab-content">
                        <div id="tab-trans" class="tab-pane active">
                            <ul class="nav nav-tabs">
                                @foreach($locales as $locale)
                                <li class="nav-item"><a href="#language-{{ $locale }}" data-bs-toggle="tab" class="nav-link @if($loop->first) active @endif">{{ $localeNames[$locale] ?? $locale }}</a></li>
                                @endforeach
                            </ul>
                            <div class="tab-content">
                                @foreach($locales as $locale)
                                <div id="language-{{ $locale }}" class="tab-pane @if($loop->first) active @endif">
                                    <div class="row mb-3 required">
                                        <label for="input-display_name-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_display_name }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][display_name]" value="{{ old("translations.{$locale}.display_name", $translationsArray[$locale]['display_name'] ?? '') }}" placeholder="{{ $lang->placeholder_display_name }}" id="input-display_name-{{ $locale }}" class="form-control" maxlength="100">
                                            <div id="error-display_name-{{ $locale }}" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="input-note-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_note }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][note]" value="{{ old("translations.{$locale}.note", $translationsArray[$locale]['note'] ?? '') }}" placeholder="{{ $lang->placeholder_note }}" id="input-note-{{ $locale }}" class="form-control" maxlength="255">
                                            <div id="error-note-{{ $locale }}" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="tab-data" class="tab-pane">
                            <div class="row mb-3 required">
                                <label for="input-name" class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" value="{{ old('name', $permission->name) }}" placeholder="{{ $lang->placeholder_name }}" id="input-name" class="form-control" pattern="[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)*" maxlength="100">
                                    <div id="error-name" class="invalid-feedback"></div>
                                    <div class="form-text">{{ $lang->help_name }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-guard_name" class="col-sm-2 col-form-label">{{ $lang->column_guard_name }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="guard_name" value="{{ old('guard_name', $permission->guard_name ?? 'web') }}" id="input-guard_name" class="form-control" maxlength="50">
                                    <div id="error-guard_name" class="invalid-feedback"></div>
                                    <div class="form-text">{{ $lang->help_guard_name }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
