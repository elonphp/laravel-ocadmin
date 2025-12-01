@extends('ocadmin::layouts.app')

@section('title', $term->exists ? '編輯詞彙' : '新增詞彙')

@section('styles')
<link href="{{ asset('assets-ocadmin/vendor/select2/select2.min.css') }}" rel="stylesheet">
<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>
@endsection

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-term" data-bs-toggle="tooltip" title="儲存" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ $term->taxonomy_id ? route('lang.ocadmin.system.taxonomy.term.index', ['filter_taxonomy_id' => $term->taxonomy_id]) : route('lang.ocadmin.system.taxonomy.term.index') }}" data-bs-toggle="tooltip" title="返回" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $term->exists ? '編輯詞彙' : '新增詞彙' }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-pencil"></i> {{ $term->exists ? '編輯詞彙' : '新增詞彙' }}</div>
            <div class="card-body">
                <form id="form-term" action="{{ $term->exists ? route('lang.ocadmin.system.taxonomy.term.update', ['id' => $term->id]) : route('lang.ocadmin.system.taxonomy.term.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($term->exists)
                    @method('PUT')
                    @endif

                    @if($term->exists)
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" value="{{ $term->id }}" class="form-control" readonly disabled>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3 required" id="input-taxonomy">
                        <label for="input-taxonomy-field" class="col-sm-2 col-form-label">分類法</label>
                        <div class="col-sm-10">
                            <select name="taxonomy_id" id="input-taxonomy-field" class="form-select" {{ $term->exists ? '' : '' }}>
                                <option value="">-- 請選擇 --</option>
                                @foreach($taxonomies as $taxonomy)
                                <option value="{{ $taxonomy->id }}" {{ old('taxonomy_id', $term->taxonomy_id) == $taxonomy->id ? 'selected' : '' }}>
                                    {{ $taxonomy->name }} ({{ $taxonomy->code }})
                                </option>
                                @endforeach
                            </select>
                            <div id="error-taxonomy_id" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-parent">
                        <label for="input-parent-field" class="col-sm-2 col-form-label">父層</label>
                        <div class="col-sm-10">
                            <select name="parent_id" id="input-parent-field" class="form-select">
                                <option value="">-- 無（頂層）--</option>
                                @foreach($parentTerms as $parentTerm)
                                @if(!$term->exists || $parentTerm->id != $term->id)
                                <option value="{{ $parentTerm->id }}" {{ old('parent_id', $term->parent_id) == $parentTerm->id ? 'selected' : '' }}>
                                    {{ $parentTerm->name }} ({{ $parentTerm->code }})
                                </option>
                                @endif
                                @endforeach
                            </select>
                            <div id="error-parent_id" class="invalid-feedback"></div>
                            <div class="form-text">選擇父層後，此詞彙將成為該父層的子項目</div>
                        </div>
                    </div>

                    <div class="row mb-3 required" id="input-code">
                        <label for="input-code-field" class="col-sm-2 col-form-label">代碼</label>
                        <div class="col-sm-10">
                            <input type="text" name="code" value="{{ old('code', $term->code) }}" placeholder="如：pending, electronics, dashboard" id="input-code-field" class="form-control" maxlength="50" pattern="[a-z][a-z0-9_]*">
                            <div id="error-code" class="invalid-feedback"></div>
                            <div class="form-text">只能使用小寫英文、數字和底線，必須以英文字母開頭</div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-sort-order">
                        <label for="input-sort-order-field" class="col-sm-2 col-form-label">排序</label>
                        <div class="col-sm-10">
                            <input type="number" name="sort_order" value="{{ old('sort_order', $term->sort_order ?? 0) }}" id="input-sort-order-field" class="form-control" min="0">
                            <div id="error-sort_order" class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3" id="input-is-active">
                        <label class="col-sm-2 col-form-label">狀態</label>
                        <div class="col-sm-10">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" id="input-is-active-field" class="form-check-input" {{ old('is_active', $term->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="input-is-active-field">啟用</label>
                            </div>
                        </div>
                    </div>

                    {{-- 多語系名稱 --}}
                    <fieldset class="mb-3">
                        <legend>名稱（多語系）</legend>
                        @foreach($locales as $locale)
                        @php
                            $translation = $term->exists ? $term->getTranslation($locale) : null;
                            $localeName = config("localization.locale_names.{$locale}", $locale);
                        @endphp
                        <div class="row mb-3 required" id="input-translation-{{ $locale }}">
                            <label for="input-name-{{ $locale }}" class="col-sm-2 col-form-label">{{ $localeName }}</label>
                            <div class="col-sm-10">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" name="translations[{{ $locale }}][name]" value="{{ old("translations.{$locale}.name", $translation?->name) }}" placeholder="名稱" id="input-name-{{ $locale }}" class="form-control" maxlength="100">
                                        <div id="error-translations-{{ $locale }}-name" class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="translations[{{ $locale }}][short_name]" value="{{ old("translations.{$locale}.short_name", $translation?->short_name) }}" placeholder="簡稱（選填）" class="form-control" maxlength="50">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </fieldset>

                    {{-- Meta 欄位 --}}
                    @if($metaKeys->isNotEmpty())
                    <fieldset class="mb-3">
                        <legend>擴展欄位</legend>
                        @foreach($metaKeys as $metaKey)
                        @php
                            $metaValue = $term->exists ? $term->getMeta($metaKey->name) : null;
                        @endphp
                        <div class="row mb-3" id="input-meta-{{ $metaKey->name }}">
                            <label for="input-meta-{{ $metaKey->name }}-field" class="col-sm-2 col-form-label">
                                {{ $metaKey->description ?: $metaKey->name }}
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="metas[{{ $metaKey->name }}]" value="{{ old("metas.{$metaKey->name}", $metaValue) }}" placeholder="{{ $metaKey->name }}" id="input-meta-{{ $metaKey->name }}-field" class="form-control">
                                <div class="form-text">欄位代碼：<code>{{ $metaKey->name }}</code></div>
                            </div>
                        </div>
                        @endforeach
                    </fieldset>
                    @endif

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets-ocadmin/vendor/select2/select2.min.js') }}"></script>
<script>
$(document).ready(function() {
    // 當分類法改變時，重新載入父層選項
    $('#input-taxonomy-field').on('change', function() {
        var taxonomyId = $(this).val();
        var $parentSelect = $('#input-parent-field');

        if (!taxonomyId) {
            $parentSelect.html('<option value="">-- 無（頂層）--</option>');
            return;
        }

        // 載入該分類法的 terms
        $.ajax({
            url: '{{ route("lang.ocadmin.system.taxonomy.term.index") }}'.replace('/term', '/term/by-taxonomy/' + taxonomyId),
            type: 'GET',
            dataType: 'json',
            success: function(terms) {
                var html = '<option value="">-- 無（頂層）--</option>';
                terms.forEach(function(term) {
                    @if($term->exists)
                    if (term.id != {{ $term->id }}) {
                        html += '<option value="' + term.id + '">' + term.name + ' (' + term.code + ')</option>';
                    }
                    @else
                    html += '<option value="' + term.id + '">' + term.name + ' (' + term.code + ')</option>';
                    @endif
                });
                $parentSelect.html(html);
            }
        });
    });
});
</script>
@endsection
