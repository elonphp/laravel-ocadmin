@extends('ocadmin::layouts.app')

@section('title', $group->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">{{ $group->exists ? $lang->text_edit : $lang->text_add }}</h3>
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
            <button type="submit" form="form-group" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                <i class="bi bi-floppy"></i>
            </button>
            <a href="{{ $url_back }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-pencil"></i> {{ $group->exists ? $lang->text_edit : $lang->text_add }}</div>
            <div class="card-body">
                <form id="form-group" action="{{ $url_action }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($group->exists)
                    @method('PUT')
                    @endif

                    @php $translationsArray = $group->exists ? $group->getTranslationsArray() : []; @endphp

                    {{-- 群組資料 --}}
                    <fieldset>
                        <legend>{{ $lang->text_group_info }}</legend>
                        <div class="row mb-3 required">
                            <label for="input-code" class="col-sm-2 col-form-label">{{ $lang->column_code }}</label>
                            <div class="col-sm-10">
                                <input type="text" name="code" value="{{ old('code', $group->code ?? '') }}" placeholder="{{ $lang->placeholder_code }}" id="input-code" class="form-control" maxlength="50">
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
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">{{ $lang->column_description }}</label>
                            <div class="col-sm-10">
                                @foreach($locales as $locale)
                                <div class="input-group">
                                    <span class="input-group-text">{{ $localeNames[$locale] ?? $locale }}</span>
                                    <input type="text" name="translations[{{ $locale }}][description]" value="{{ old("translations.{$locale}.description", $translationsArray[$locale]['description'] ?? '') }}" placeholder="{{ $lang->placeholder_description }}" id="input-description-{{ $locale }}" class="form-control" maxlength="500">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="input-sort_order" class="col-sm-2 col-form-label">{{ $lang->column_sort_order }}</label>
                            <div class="col-sm-10">
                                <input type="number" name="sort_order" value="{{ old('sort_order', $group->sort_order ?? 0) }}" id="input-sort_order" class="form-control" min="0">
                                <div id="error-sort_order" class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="input-is_active" class="col-sm-2 col-form-label">{{ $lang->column_is_active }}</label>
                            <div class="col-sm-10">
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" id="input-is_active" class="form-check-input" {{ old('is_active', $group->is_active ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    {{-- 層級設定 --}}
                    <fieldset>
                        <legend>{{ $lang->text_levels }}</legend>
                        <table id="level-table" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 80px;" class="text-center">{{ $lang->column_level }}</th>
                                    <th class="required">{{ $lang->column_option }}</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="level-tbody">
                                @foreach($levels as $index => $level)
                                <tr id="level-row-{{ $index }}" data-index="{{ $index }}">
                                    <td class="text-center align-middle level-number">{{ $index }}</td>
                                    <td>
                                        <select name="levels[{{ $index }}][option_id]" class="form-select">
                                            <option value="">{{ $lang->text_select_option }}</option>
                                            @foreach($options as $option)
                                            <option value="{{ $option->id }}" {{ ($level->option_id ?? '') == $option->id ? 'selected' : '' }}>{{ $option->name }} ({{ $option->code }})</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" onclick="removeLevel({{ $index }});" data-bs-toggle="tooltip" title="{{ $lang->button_delete }}" class="btn btn-danger btn-sm"><i class="bi bi-dash-circle"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2"></td>
                                    <td class="text-end">
                                        <button type="button" onclick="addLevel();" data-bs-toggle="tooltip" title="{{ $lang->button_add }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i></button>
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
var level_row = {{ count($levels) }};
var optionsData = @json($options->map(fn($o) => ['id' => $o->id, 'name' => $o->name, 'code' => $o->code]));

function addLevel() {
    var html = '<tr id="level-row-' + level_row + '" data-index="' + level_row + '">';
    html += '<td class="text-center align-middle level-number">' + level_row + '</td>';
    html += '<td><select name="levels[' + level_row + '][option_id]" class="form-select">';
    html += '<option value="">{{ $lang->text_select_option }}</option>';

    for (var i = 0; i < optionsData.length; i++) {
        var opt = optionsData[i];
        var label = opt.name + (opt.code ? ' (' + opt.code + ')' : '');
        html += '<option value="' + opt.id + '">' + label + '</option>';
    }

    html += '</select><div class="invalid-feedback"></div></td>';
    html += '<td class="text-end"><button type="button" onclick="removeLevel(' + level_row + ');" data-bs-toggle="tooltip" title="{{ $lang->button_delete }}" class="btn btn-danger btn-sm"><i class="bi bi-dash-circle"></i></button></td>';
    html += '</tr>';

    $('#level-tbody').append(html);
    level_row++;
    renumberLevels();
}

function removeLevel(index) {
    $('#level-row-' + index).remove();
    renumberLevels();
}

function renumberLevels() {
    $('#level-tbody tr').each(function(i) {
        $(this).find('.level-number').text(i);
    });
}
</script>
@endsection
