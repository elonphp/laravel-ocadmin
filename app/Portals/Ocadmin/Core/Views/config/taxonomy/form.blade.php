@extends('ocadmin::layouts.app')

@section('title', $taxonomy->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-taxonomy" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.config.taxonomy.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $taxonomy->exists ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ $taxonomy->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a href="#tab-trans" data-bs-toggle="tab" class="nav-link active">{{ $lang->tab_trans }}</a></li>
                    <li class="nav-item"><a href="#tab-data" data-bs-toggle="tab" class="nav-link">{{ $lang->tab_data }}</a></li>
                </ul>
                <form action="{{ $taxonomy->exists ? route('lang.ocadmin.config.taxonomy.update', $taxonomy) : route('lang.ocadmin.config.taxonomy.store') }}" method="post" id="form-taxonomy" data-oc-toggle="ajax">
                    @csrf
                    @if($taxonomy->exists)
                    @method('PUT')
                    @endif

                    @php $translationsArray = $taxonomy->exists ? $taxonomy->getTranslationsArray() : []; @endphp

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
                                        <label for="input-name-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][name]" value="{{ old("translations.{$locale}.name", $translationsArray[$locale]['name'] ?? '') }}" placeholder="{{ $lang->placeholder_name }}" id="input-name-{{ $locale }}" class="form-control" maxlength="100">
                                            <div id="error-name-{{ $locale }}" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="tab-data" class="tab-pane">
                            <div class="row mb-3 required">
                                <label for="input-code" class="col-sm-2 col-form-label">{{ $lang->column_code }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="code" value="{{ old('code', $taxonomy->code) }}" placeholder="{{ $lang->placeholder_code }}" id="input-code" class="form-control" pattern="[a-z][a-z0-9_]*" maxlength="50">
                                    <div id="error-code" class="invalid-feedback"></div>
                                    <div class="form-text">{{ $lang->help_code }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-description" class="col-sm-2 col-form-label">{{ $lang->column_description }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="description" value="{{ old('description', $taxonomy->description) }}" placeholder="{{ $lang->placeholder_description }}" id="input-description" class="form-control" maxlength="255">
                                    <div id="error-description" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-sort_order" class="col-sm-2 col-form-label">{{ $lang->column_sort_order }}</label>
                                <div class="col-sm-10">
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $taxonomy->sort_order ?? 0) }}" id="input-sort_order" class="form-control" min="0">
                                    <div id="error-sort_order" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">{{ $lang->column_is_active }}</label>
                                <div class="col-sm-10">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="input-is_active" value="1" {{ old('is_active', $taxonomy->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="input-is_active">{{ $lang->text_active }}</label>
                                    </div>
                                </div>
                            </div>

                            @if($taxonomy->exists)
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">{{ $lang->column_terms_count }}</label>
                                <div class="col-sm-10">
                                    <a href="{{ route('lang.ocadmin.config.term.index', ['filter_taxonomy_id' => $taxonomy->id]) }}" class="btn btn-outline-info btn-sm">
                                        <i class="fa-solid fa-tags"></i> {{ $lang->text_view_terms ?? '查看詞彙項目' }}（{{ $taxonomy->terms()->count() }} 筆）
                                    </a>
                                    <a href="{{ route('lang.ocadmin.config.term.create', ['taxonomy_id' => $taxonomy->id]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fa-solid fa-plus"></i> {{ $lang->text_add_term ?? '新增詞彙' }}
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
