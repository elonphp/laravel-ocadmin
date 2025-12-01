@extends('ocadmin::layouts.app')

@section('title', $taxonomy->exists ? '編輯分類法' : '新增分類法')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-taxonomy" data-bs-toggle="tooltip" title="儲存" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.taxonomy.taxonomy.index') }}" data-bs-toggle="tooltip" title="返回" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $taxonomy->exists ? '編輯分類法' : '新增分類法' }}</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('lang.ocadmin.dashboard') }}">首頁</a></li>
                <li class="breadcrumb-item"><a href="#">系統管理</a></li>
                <li class="breadcrumb-item"><a href="#">詞彙管理</a></li>
                <li class="breadcrumb-item"><a href="{{ route('lang.ocadmin.system.taxonomy.taxonomy.index') }}">分類法</a></li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-pencil"></i> {{ $taxonomy->exists ? '編輯分類法' : '新增分類法' }}</div>
            <div class="card-body">
                <form id="form-taxonomy" action="{{ $taxonomy->exists ? route('lang.ocadmin.system.taxonomy.taxonomy.update', ['id' => $taxonomy->id]) : route('lang.ocadmin.system.taxonomy.taxonomy.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($taxonomy->exists)
                    @method('PUT')
                    @endif

                    @if($taxonomy->exists)
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" value="{{ $taxonomy->id }}" class="form-control" readonly disabled>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3 required" id="input-code">
                        <label for="input-code-field" class="col-sm-2 col-form-label">代碼</label>
                        <div class="col-sm-10">
                            <input type="text" name="code" value="{{ old('code', $taxonomy->code) }}" placeholder="如：order_status, product_category" id="input-code-field" class="form-control" maxlength="50" pattern="[a-z][a-z0-9_]*">
                            <div id="error-code" class="invalid-feedback"></div>
                            <div class="form-text">只能使用小寫英文、數字和底線，必須以英文字母開頭</div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-sort-order">
                        <label for="input-sort-order-field" class="col-sm-2 col-form-label">排序</label>
                        <div class="col-sm-10">
                            <input type="number" name="sort_order" value="{{ old('sort_order', $taxonomy->sort_order ?? 0) }}" id="input-sort-order-field" class="form-control" min="0">
                            <div id="error-sort_order" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-is-active">
                        <label class="col-sm-2 col-form-label">狀態</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" id="input-is-active-field" class="form-check-input" {{ old('is_active', $taxonomy->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="input-is-active-field">啟用</label>
                            </div>
                        </div>
                    </div>

                    {{-- 多語系名稱 --}}
                    <fieldset class="mb-3">
                        <legend>名稱（多語系）</legend>
                        @foreach($locales as $locale)
                        @php
                            $translation = $taxonomy->exists ? $taxonomy->getTranslation($locale) : null;
                            $localeName = config("localization.locale_names.{$locale}", $locale);
                        @endphp
                        <div class="row mb-3 required" id="input-translation-{{ $locale }}">
                            <label for="input-name-{{ $locale }}" class="col-sm-2 col-form-label">{{ $localeName }}</label>
                            <div class="col-sm-10">
                                <input type="text" name="translations[{{ $locale }}][name]" value="{{ old("translations.{$locale}.name", $translation?->name) }}" placeholder="名稱" id="input-name-{{ $locale }}" class="form-control" maxlength="100">
                                <div id="error-translations-{{ $locale }}-name" class="invalid-feedback"></div>
                            </div>
                        </div>
                        @endforeach
                    </fieldset>

                </form>
            </div>
        </div>

        @if($taxonomy->exists)
        <div class="card mt-3">
            <div class="card-header"><i class="fa-solid fa-tags"></i> 詞彙管理</div>
            <div class="card-body">
                <p>此分類法下目前有 <strong>{{ $taxonomy->terms->count() }}</strong> 個詞彙。</p>
                <a href="{{ route('lang.ocadmin.system.taxonomy.term.index', ['filter_taxonomy_id' => $taxonomy->id]) }}" class="btn btn-info">
                    <i class="fa-solid fa-tags"></i> 管理詞彙
                </a>
                <a href="{{ route('lang.ocadmin.system.taxonomy.term.create', ['taxonomy_id' => $taxonomy->id]) }}" class="btn btn-success">
                    <i class="fa-solid fa-plus"></i> 新增詞彙
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
