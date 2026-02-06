@extends('ocadmin::layouts.app')

@section('title', $term->exists ? $lang->text_edit : $lang->text_add)

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
                <button type="submit" form="form-term" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.config.term.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $term->exists ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ $term->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a href="#tab-trans" data-bs-toggle="tab" class="nav-link active">{{ $lang->tab_trans }}</a></li>
                    <li class="nav-item"><a href="#tab-data" data-bs-toggle="tab" class="nav-link">{{ $lang->tab_data }}</a></li>
                </ul>
                <form action="{{ $term->exists ? route('lang.ocadmin.config.term.update', $term) : route('lang.ocadmin.config.term.store') }}" method="post" id="form-term" data-oc-toggle="ajax">
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
                                <label for="input-taxonomy_id" class="col-sm-2 col-form-label">{{ $lang->column_taxonomy }}</label>
                                <div class="col-sm-10">
                                    <select name="taxonomy_id" id="input-taxonomy_id" class="form-select">
                                        <option value="">{{ $lang->text_select_taxonomy }}</option>
                                        @foreach($taxonomies as $taxonomy)
                                        <option value="{{ $taxonomy->id }}" {{ old('taxonomy_id', $term->taxonomy_id) == $taxonomy->id ? 'selected' : '' }}>{{ $taxonomy->name }} ({{ $taxonomy->code }})</option>
                                        @endforeach
                                    </select>
                                    <div id="error-taxonomy_id" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-parent_id" class="col-sm-2 col-form-label">{{ $lang->column_parent }}</label>
                                <div class="col-sm-10">
                                    <select name="parent_id" id="input-parent_id" class="form-select">
                                        <option value="">{{ $lang->text_select_parent }}</option>
                                        @foreach($parentTerms as $parentTerm)
                                        <option value="{{ $parentTerm->id }}" {{ old('parent_id', $term->parent_id) == $parentTerm->id ? 'selected' : '' }}>{{ $parentTerm->name }} ({{ $parentTerm->code }})</option>
                                        @endforeach
                                    </select>
                                    <div id="error-parent_id" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3 required">
                                <label for="input-code" class="col-sm-2 col-form-label">{{ $lang->column_code }}</label>
                                <div class="col-sm-10">
                                    <input type="text" name="code" value="{{ old('code', $term->code) }}" placeholder="{{ $lang->placeholder_code }}" id="input-code" class="form-control" pattern="[a-z][a-z0-9_]*" maxlength="50">
                                    <div id="error-code" class="invalid-feedback"></div>
                                    <div class="form-text">{{ $lang->help_code }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="input-sort_order" class="col-sm-2 col-form-label">{{ $lang->column_sort_order }}</label>
                                <div class="col-sm-10">
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $term->sort_order ?? 0) }}" id="input-sort_order" class="form-control" min="0">
                                    <div id="error-sort_order" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label">{{ $lang->column_is_active }}</label>
                                <div class="col-sm-10">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="input-is_active" value="1" {{ old('is_active', $term->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="input-is_active">{{ $lang->text_active }}</label>
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
        $('#input-parent_id').select2({
            placeholder: '{{ $lang->text_select_parent }}',
            allowClear: true,
            width: '100%'
        });
    }

    initSelect2Parent();

    $('#input-taxonomy_id').on('change', function() {
        var taxonomyId = $(this).val();
        var $parent = $('#input-parent_id');

        $parent.val(null).trigger('change');
        $parent.html('<option value="">{{ $lang->text_select_parent }}</option>');

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
