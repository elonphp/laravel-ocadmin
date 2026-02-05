@extends('ocadmin::layouts.app')

@section('title', $taxonomy->exists ? '編輯分類' : '新增分類')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-taxonomy" data-bs-toggle="tooltip" title="儲存" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.config.taxonomy.index') }}" data-bs-toggle="tooltip" title="返回" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $taxonomy->exists ? '編輯分類' : '新增分類' }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <i class="fa-solid fa-exclamation-circle"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card card-default">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ $taxonomy->exists ? '編輯分類' : '新增分類' }}
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a href="#tab-trans" data-bs-toggle="tab" class="nav-link active">翻譯</a></li>
                    <li class="nav-item"><a href="#tab-data" data-bs-toggle="tab" class="nav-link">資料</a></li>
                </ul>
                <form action="{{ $taxonomy->exists ? route('lang.ocadmin.config.taxonomy.update', $taxonomy) : route('lang.ocadmin.config.taxonomy.store') }}" method="post" id="form-taxonomy">
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
                                        <label for="input-name-{{ $locale }}" class="col-sm-2 col-form-label">名稱</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][name]" value="{{ old("translations.{$locale}.name", $translationsArray[$locale]['name'] ?? '') }}" placeholder="請輸入名稱" id="input-name-{{ $locale }}" class="form-control @error("translations.{$locale}.name") is-invalid @enderror" maxlength="100">
                                            @error("translations.{$locale}.name")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="tab-data" class="tab-pane">
                            <div class="row mb-3 required">
                                <label for="input-code" class="col-sm-2 col-form-label">代碼</label>
                                <div class="col-sm-10">
                                    <input type="text" name="code" value="{{ old('code', $taxonomy->code) }}" placeholder="小寫英文加底線（如：skill）" id="input-code" class="form-control @error('code') is-invalid @enderror" pattern="[a-z][a-z0-9_]*" maxlength="50">
                                    @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">唯一識別碼，僅限小寫英文、數字、底線</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-description" class="col-sm-2 col-form-label">說明</label>
                                <div class="col-sm-10">
                                    <input type="text" name="description" value="{{ old('description', $taxonomy->description) }}" placeholder="請輸入說明" id="input-description" class="form-control" maxlength="255">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-sort-order" class="col-sm-2 col-form-label">排序</label>
                                <div class="col-sm-10">
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $taxonomy->sort_order ?? 0) }}" id="input-sort-order" class="form-control" min="0">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">狀態</label>
                                <div class="col-sm-10">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="input-is-active" value="1" {{ old('is_active', $taxonomy->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="input-is-active">啟用</label>
                                    </div>
                                </div>
                            </div>

                            @if($taxonomy->exists)
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">詞彙項目</label>
                                <div class="col-sm-10">
                                    <a href="{{ route('lang.ocadmin.config.term.index', ['filter_taxonomy_id' => $taxonomy->id]) }}" class="btn btn-outline-info btn-sm">
                                        <i class="fa-solid fa-tags"></i> 查看詞彙項目（{{ $taxonomy->terms()->count() }} 筆）
                                    </a>
                                    <a href="{{ route('lang.ocadmin.config.term.create', ['taxonomy_id' => $taxonomy->id]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fa-solid fa-plus"></i> 新增詞彙
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
