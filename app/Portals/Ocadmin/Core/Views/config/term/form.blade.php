@extends('ocadmin::layouts.app')

@section('title', $term->exists ? '編輯詞彙' : '新增詞彙')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/select2/select2.min.css') }}">
<style>
.select2-container .select2-selection--single { height: 100% !important; }
</style>
@endsection

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-term" data-bs-toggle="tooltip" title="儲存" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.config.term.index') }}" data-bs-toggle="tooltip" title="返回" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $term->exists ? '編輯詞彙' : '新增詞彙' }}</h1>
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
                <i class="fa-solid fa-pencil"></i> {{ $term->exists ? '編輯詞彙' : '新增詞彙' }}
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a href="#tab-trans" data-bs-toggle="tab" class="nav-link active">翻譯</a></li>
                    <li class="nav-item"><a href="#tab-data" data-bs-toggle="tab" class="nav-link">資料</a></li>
                </ul>
                <form action="{{ $term->exists ? route('lang.ocadmin.config.term.update', $term) : route('lang.ocadmin.config.term.store') }}" method="post" id="form-term">
                    @csrf
                    @if($term->exists)
                    @method('PUT')
                    @endif

                    @php $translationsArray = $term->exists ? $term->getTranslationsArray() : []; @endphp

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
                                <label for="input-taxonomy" class="col-sm-2 col-form-label">分類</label>
                                <div class="col-sm-10">
                                    <select name="taxonomy_id" id="input-taxonomy" class="form-select @error('taxonomy_id') is-invalid @enderror">
                                        <option value="">-- 請選擇 --</option>
                                        @foreach($taxonomies as $taxonomy)
                                        <option value="{{ $taxonomy->id }}" {{ old('taxonomy_id', $term->taxonomy_id) == $taxonomy->id ? 'selected' : '' }}>{{ $taxonomy->name }} ({{ $taxonomy->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('taxonomy_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-parent" class="col-sm-2 col-form-label">父項目</label>
                                <div class="col-sm-10">
                                    <select name="parent_id" id="input-parent" class="form-select @error('parent_id') is-invalid @enderror">
                                        <option value="">-- 無（根項目）--</option>
                                        @foreach($parentTerms as $parentTerm)
                                        <option value="{{ $parentTerm->id }}" {{ old('parent_id', $term->parent_id) == $parentTerm->id ? 'selected' : '' }}>{{ $parentTerm->name }} ({{ $parentTerm->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3 required">
                                <label for="input-code" class="col-sm-2 col-form-label">代碼</label>
                                <div class="col-sm-10">
                                    <input type="text" name="code" value="{{ old('code', $term->code) }}" placeholder="小寫英文加底線（如：php）" id="input-code" class="form-control @error('code') is-invalid @enderror" pattern="[a-z][a-z0-9_]*" maxlength="50">
                                    @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">同分類下不可重複</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-sort-order" class="col-sm-2 col-form-label">排序</label>
                                <div class="col-sm-10">
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $term->sort_order ?? 0) }}" id="input-sort-order" class="form-control" min="0">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">狀態</label>
                                <div class="col-sm-10">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="input-is-active" value="1" {{ old('is_active', $term->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="input-is-active">啟用</label>
                                    </div>
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

@section('scripts')
<script src="{{ asset('assets/vendor/select2/select2.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {
    var currentTermId = {{ $term->id ?? 'null' }};

    function initSelect2Parent() {
        $('#input-parent').select2({
            placeholder: '-- 無（根項目）--',
            allowClear: true,
            width: '100%'
        });
    }

    initSelect2Parent();

    $('#input-taxonomy').on('change', function() {
        var taxonomyId = $(this).val();
        var $parent = $('#input-parent');

        $parent.val(null).trigger('change');
        $parent.html('<option value="">-- 無（根項目）--</option>');

        if (!taxonomyId) return;

        $.ajax({
            url: '{{ route('lang.ocadmin.config.term.by-taxonomy', ':id') }}'.replace(':id', taxonomyId),
            type: 'GET',
            dataType: 'json',
            success: function(terms) {
                $.each(terms, function(i, term) {
                    if (currentTermId && term.id == currentTermId) return;
                    $parent.append('<option value="' + term.id + '">' + term.name + ' (' + term.code + ')</option>');
                });
                $parent.trigger('change.select2');
            }
        });
    });
});
</script>
@endsection
