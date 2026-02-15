@extends('ocadmin::layouts.app')

@section('title', $option->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-option" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.catalog.option.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $option->exists ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-pencil"></i> {{ $option->exists ? $lang->text_edit : $lang->text_add }}</div>
            <div class="card-body">
                <form id="form-option" action="{{ $option->exists ? route('lang.ocadmin.catalog.option.update', $option) : route('lang.ocadmin.catalog.option.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($option->exists)
                    @method('PUT')
                    @endif

                    @php $translationsArray = $option->exists ? $option->getTranslationsArray() : []; @endphp

                    {{-- 選項資料 --}}
                    <fieldset>
                        <legend>{{ $lang->text_option }}</legend>
                        <div class="row mb-3">
                            <label for="input-code" class="col-sm-2 col-form-label">{{ $lang->column_code }}</label>
                            <div class="col-sm-10">
                                <input type="text" name="code" value="{{ old('code', $option->code ?? '') }}" placeholder="{{ $lang->placeholder_code }}" id="input-code" class="form-control" maxlength="50">
                                <div id="error-code" class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row mb-3 required">
                            <label class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
                            <div class="col-sm-10">
                                @foreach($locales as $locale)
                                <div class="input-group">
                                    <span class="input-group-text">{{ $localeNames[$locale] ?? $locale }}</span>
                                    <input type="text" name="translations[{{ $locale }}][name]" value="{{ old("translations.{$locale}.name", $translationsArray[$locale]['name'] ?? '') }}" placeholder="{{ $lang->placeholder_name }}" id="input-name-{{ $locale }}" class="form-control" maxlength="128">
                                </div>
                                <div id="error-name-{{ $locale }}" class="invalid-feedback"></div>
                                @endforeach
                            </div>
                        </div>
                        <div class="row mb-3 required">
                            <label for="input-type" class="col-sm-2 col-form-label">{{ $lang->column_type }}</label>
                            <div class="col-sm-10">
                                <select name="type" id="input-type" class="form-select">
                                    <optgroup label="{{ $lang->text_choose }}">
                                        <option value="select" {{ old('type', $option->type ?? 'select') == 'select' ? 'selected' : '' }}>{{ $lang->text_select }}</option>
                                        <option value="radio" {{ old('type', $option->type) == 'radio' ? 'selected' : '' }}>{{ $lang->text_radio }}</option>
                                        <option value="checkbox" {{ old('type', $option->type) == 'checkbox' ? 'selected' : '' }}>{{ $lang->text_checkbox }}</option>
                                    </optgroup>
                                    <optgroup label="{{ $lang->text_input }}">
                                        <option value="text" {{ old('type', $option->type) == 'text' ? 'selected' : '' }}>{{ $lang->text_text }}</option>
                                        <option value="textarea" {{ old('type', $option->type) == 'textarea' ? 'selected' : '' }}>{{ $lang->text_textarea }}</option>
                                    </optgroup>
                                    <optgroup label="{{ $lang->text_file }}">
                                        <option value="file" {{ old('type', $option->type) == 'file' ? 'selected' : '' }}>{{ $lang->text_file }}</option>
                                    </optgroup>
                                    <optgroup label="{{ $lang->text_date }}">
                                        <option value="date" {{ old('type', $option->type) == 'date' ? 'selected' : '' }}>{{ $lang->text_date }}</option>
                                        <option value="time" {{ old('type', $option->type) == 'time' ? 'selected' : '' }}>{{ $lang->text_time }}</option>
                                        <option value="datetime" {{ old('type', $option->type) == 'datetime' ? 'selected' : '' }}>{{ $lang->text_datetime }}</option>
                                    </optgroup>
                                </select>
                                <div id="error-type" class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="input-sort_order" class="col-sm-2 col-form-label">{{ $lang->column_sort_order }}</label>
                            <div class="col-sm-10">
                                <input type="number" name="sort_order" value="{{ old('sort_order', $option->sort_order ?? 0) }}" id="input-sort_order" class="form-control" min="0">
                                <div id="error-sort_order" class="invalid-feedback"></div>
                            </div>
                        </div>
                    </fieldset>

                    {{-- 選項值 --}}
                    <fieldset id="fieldset-option-value">
                        <legend>{{ $lang->text_value }}</legend>
                        <table id="option-value" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="required">{{ $lang->column_value_name }}</th>
                                    <th style="width: 150px;">{{ $lang->column_code }}</th>
                                    <th class="text-end" style="width: 100px;">{{ $lang->column_sort_order }}</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($optionValues as $index => $optionValue)
                                @php $valueTranslations = $optionValue->getTranslationsArray(); @endphp
                                <tr id="option-value-row-{{ $index }}">
                                    <td>
                                        @foreach($locales as $locale)
                                        <div class="input-group">
                                            <span class="input-group-text">{{ $localeNames[$locale] ?? $locale }}</span>
                                            <input type="text" name="option_value[{{ $index }}][translations][{{ $locale }}][name]" value="{{ $valueTranslations[$locale]['name'] ?? '' }}" placeholder="{{ $lang->placeholder_value_name }}" id="input-option-value-{{ $index }}-{{ $locale }}" class="form-control" maxlength="128">
                                        </div>
                                        <div id="error-option-value-{{ $index }}-{{ $locale }}" class="invalid-feedback"></div>
                                        @endforeach
                                    </td>
                                    <td>
                                        <input type="text" name="option_value[{{ $index }}][code]" value="{{ $optionValue->code ?? '' }}" placeholder="{{ $lang->placeholder_code }}" class="form-control" maxlength="50">
                                    </td>
                                    <td class="text-end">
                                        <input type="number" name="option_value[{{ $index }}][sort_order]" value="{{ $optionValue->sort_order ?? $index }}" class="form-control" min="0">
                                    </td>
                                    <td class="text-end">
                                        <button type="button" onclick="$('#option-value-row-{{ $index }}').remove();" data-bs-toggle="tooltip" title="{{ $lang->button_delete }}" class="btn btn-danger"><i class="fa-solid fa-minus-circle"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-end">
                                        <button type="button" onclick="addOptionValue();" data-bs-toggle="tooltip" title="{{ $lang->button_add }}" class="btn btn-primary"><i class="fa-solid fa-plus-circle"></i></button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
var option_value_row = {{ count($optionValues) }};
var choiceTypes = ['select', 'radio', 'checkbox'];
var locales = @json($locales);
var localeNames = @json($localeNames);

$('#input-type').on('change', function() {
    if (choiceTypes.indexOf(this.value) !== -1) {
        $('#fieldset-option-value').show();
    } else {
        $('#fieldset-option-value').hide();
    }
});

$('#input-type').trigger('change');

function addOptionValue() {
    var html = '<tr id="option-value-row-' + option_value_row + '">';
    html += '<td>';

    for (var i = 0; i < locales.length; i++) {
        var locale = locales[i];
        var localeName = localeNames[locale] || locale;
        html += '<div class="input-group">';
        html += '<span class="input-group-text">' + localeName + '</span>';
        html += '<input type="text" name="option_value[' + option_value_row + '][translations][' + locale + '][name]" value="" placeholder="{{ $lang->placeholder_value_name }}" id="input-option-value-' + option_value_row + '-' + locale + '" class="form-control" maxlength="128">';
        html += '</div>';
        html += '<div id="error-option-value-' + option_value_row + '-' + locale + '" class="invalid-feedback"></div>';
    }

    html += '</td>';
    html += '<td><input type="text" name="option_value[' + option_value_row + '][code]" value="" placeholder="{{ $lang->placeholder_code }}" class="form-control" maxlength="50"></td>';
    html += '<td class="text-end"><input type="number" name="option_value[' + option_value_row + '][sort_order]" value="' + option_value_row + '" class="form-control" min="0"></td>';
    html += '<td class="text-end"><button type="button" onclick="$(\'#option-value-row-' + option_value_row + '\').remove();" data-bs-toggle="tooltip" title="{{ $lang->button_delete }}" class="btn btn-danger"><i class="fa-solid fa-minus-circle"></i></button></td>';
    html += '</tr>';

    $('#option-value tbody').append(html);
    option_value_row++;
}
</script>
@endsection
