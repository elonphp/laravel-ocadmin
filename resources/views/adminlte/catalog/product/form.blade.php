@extends('ocadmin::layouts.app')

@section('title', $product->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">{{ $product->exists ? $lang->text_edit : $lang->text_add }}</h3>
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
            <button type="submit" form="form-product" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                <i class="bi bi-floppy"></i>
            </button>
            <a href="{{ route('lang.ocadmin.catalog.product.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-pencil"></i> {{ $product->exists ? $lang->text_edit : $lang->text_add }}</div>
            <div class="card-body">
                <form id="form-product" action="{{ $product->exists ? route('lang.ocadmin.catalog.product.update', $product) : route('lang.ocadmin.catalog.product.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($product->exists)
                    @method('PUT')
                    @endif

                    @php $translationsArray = $product->exists ? $product->getTranslationsArray() : []; @endphp

                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a href="#tab-general" data-bs-toggle="tab" class="nav-link active">{{ $lang->text_general }}</a></li>
                        <li class="nav-item"><a href="#tab-data" data-bs-toggle="tab" class="nav-link">{{ $lang->text_data }}</a></li>
                        <li class="nav-item"><a href="#tab-option" data-bs-toggle="tab" class="nav-link">{{ $lang->text_option }}</a></li>
                    </ul>

                    <div class="tab-content">
                        {{-- Tab 1: 一般資料（多語子 Tab） --}}
                        <div id="tab-general" class="tab-pane active">
                            <ul class="nav nav-tabs" id="language-tabs">
                                @foreach($locales as $locale)
                                <li class="nav-item">
                                    <a href="#language-{{ $locale }}" data-bs-toggle="tab" class="nav-link @if($loop->first) active @endif">
                                        {{ $localeNames[$locale] ?? $locale }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            <div class="tab-content">
                                @foreach($locales as $locale)
                                <div id="language-{{ $locale }}" class="tab-pane @if($loop->first) active @endif">
                                    <div class="row mb-3 required">
                                        <label for="input-name-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_name }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][name]" value="{{ old("translations.{$locale}.name", $translationsArray[$locale]['name'] ?? '') }}" placeholder="{{ $lang->placeholder_name }}" id="input-name-{{ $locale }}" class="form-control" maxlength="255">
                                            <div id="error-name-{{ $locale }}" class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="input-description-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_description }}</label>
                                        <div class="col-sm-10">
                                            <textarea name="translations[{{ $locale }}][description]" id="input-description-{{ $locale }}" class="form-control" rows="5">{{ old("translations.{$locale}.description", $translationsArray[$locale]['description'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="input-meta_title-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_meta_title }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][meta_title]" value="{{ old("translations.{$locale}.meta_title", $translationsArray[$locale]['meta_title'] ?? '') }}" id="input-meta_title-{{ $locale }}" class="form-control" maxlength="255">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="input-meta_keyword-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_meta_keyword }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="translations[{{ $locale }}][meta_keyword]" value="{{ old("translations.{$locale}.meta_keyword", $translationsArray[$locale]['meta_keyword'] ?? '') }}" id="input-meta_keyword-{{ $locale }}" class="form-control" maxlength="255">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="input-meta_description-{{ $locale }}" class="col-sm-2 col-form-label">{{ $lang->column_meta_description }}</label>
                                        <div class="col-sm-10">
                                            <textarea name="translations[{{ $locale }}][meta_description]" id="input-meta_description-{{ $locale }}" class="form-control" rows="3">{{ old("translations.{$locale}.meta_description", $translationsArray[$locale]['meta_description'] ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Tab 2: 資料 --}}
                        <div id="tab-data" class="tab-pane">
                            <fieldset>
                                <legend>{{ $lang->text_model }}</legend>
                                <div class="row mb-3 required">
                                    <label for="input-model" class="col-sm-2 col-form-label">{{ $lang->column_model }}</label>
                                    <div class="col-sm-10">
                                        <input type="text" name="model" value="{{ old('model', $product->model) }}" id="input-model" class="form-control" maxlength="64" placeholder="{{ $lang->placeholder_model }}">
                                        <div id="error-model" class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend>{{ $lang->text_price }}</legend>
                                <div class="row mb-3">
                                    <label for="input-price" class="col-sm-2 col-form-label">{{ $lang->column_price }}</label>
                                    <div class="col-sm-10">
                                        <input type="number" name="price" value="{{ old('price', $product->price ?? 0) }}" id="input-price" class="form-control" step="1">
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend>{{ $lang->text_stock }}</legend>
                                <div class="row mb-3">
                                    <label for="input-quantity" class="col-sm-2 col-form-label">{{ $lang->column_quantity }}</label>
                                    <div class="col-sm-10">
                                        <input type="number" name="quantity" value="{{ old('quantity', $product->quantity ?? 0) }}" id="input-quantity" class="form-control" min="0">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="input-minimum" class="col-sm-2 col-form-label">{{ $lang->column_minimum }}</label>
                                    <div class="col-sm-10">
                                        <input type="number" name="minimum" value="{{ old('minimum', $product->minimum ?? 1) }}" id="input-minimum" class="form-control" min="1">
                                        <div class="form-text">{{ $lang->help_minimum }}</div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="input-subtract" class="col-sm-2 col-form-label">{{ $lang->column_subtract }}</label>
                                    <div class="col-sm-10">
                                        <select name="subtract" id="input-subtract" class="form-select">
                                            <option value="1" {{ old('subtract', $product->subtract ?? true) ? 'selected' : '' }}>{{ $lang->text_yes }}</option>
                                            <option value="0" {{ !old('subtract', $product->subtract ?? true) ? 'selected' : '' }}>{{ $lang->text_no }}</option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <div class="row mb-3">
                                    <label for="input-shipping" class="col-sm-2 col-form-label">{{ $lang->column_shipping }}</label>
                                    <div class="col-sm-10">
                                        <select name="shipping" id="input-shipping" class="form-select">
                                            <option value="1" {{ old('shipping', $product->shipping ?? true) ? 'selected' : '' }}>{{ $lang->text_yes }}</option>
                                            <option value="0" {{ !old('shipping', $product->shipping ?? true) ? 'selected' : '' }}>{{ $lang->text_no }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="input-status" class="col-sm-2 col-form-label">{{ $lang->column_status }}</label>
                                    <div class="col-sm-10">
                                        <select name="status" id="input-status" class="form-select">
                                            <option value="1" {{ old('status', $product->status ?? true) ? 'selected' : '' }}>{{ $lang->text_enabled }}</option>
                                            <option value="0" {{ !old('status', $product->status ?? true) ? 'selected' : '' }}>{{ $lang->text_disabled }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="input-sort_order" class="col-sm-2 col-form-label">{{ $lang->column_sort_order }}</label>
                                    <div class="col-sm-10">
                                        <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" id="input-sort_order" class="form-control" min="0">
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        {{-- Tab 3: 選項 --}}
                        <div id="tab-option" class="tab-pane">
                            <div id="product-option-container">
                                @foreach($productOptions as $row => $productOption)
                                @php
                                    $opt = $productOption->option;
                                    $isChoice = in_array($opt->type, \App\Models\Catalog\Option::CHOICE_TYPES);
                                @endphp
                                <fieldset id="option-row-{{ $row }}">
                                    <legend>
                                        {{ $opt->getTranslatedAttribute('name') }}
                                        <small class="text-muted">({{ $lang->{'text_' . $opt->type} }})</small>
                                        <button type="button" onclick="$('#option-row-{{ $row }}').remove();" class="btn btn-danger btn-sm float-end"><i class="bi bi-dash-circle"></i></button>
                                    </legend>
                                    <input type="hidden" name="product_option[{{ $row }}][option_id]" value="{{ $opt->id }}">
                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">{{ $lang->column_required }}</label>
                                        <div class="col-sm-10">
                                            <select name="product_option[{{ $row }}][required]" class="form-select">
                                                <option value="1" {{ $productOption->required ? 'selected' : '' }}>{{ $lang->text_yes }}</option>
                                                <option value="0" {{ !$productOption->required ? 'selected' : '' }}>{{ $lang->text_no }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    @if($isChoice)
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ $lang->column_option_value }}</th>
                                                <th class="text-end" style="width:80px;">{{ $lang->column_quantity }}</th>
                                                <th class="text-center" style="width:80px;">{{ $lang->column_subtract }}</th>
                                                <th class="text-end" style="width:120px;">{{ $lang->column_price }}</th>
                                                <th class="text-end" style="width:120px;">{{ $lang->column_weight }}</th>
                                                <th style="width:80px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="option-value-tbody-{{ $row }}">
                                            @foreach($productOption->productOptionValues as $valRow => $pov)
                                            @php $ovName = $pov->optionValue ? $pov->optionValue->getTranslatedAttribute('name') : ''; @endphp
                                            <tr id="option-value-row-{{ $row }}-{{ $valRow }}">
                                                <td>
                                                    {{ $ovName }}
                                                    <input type="hidden" name="product_option[{{ $row }}][product_option_value][{{ $valRow }}][option_value_id]" value="{{ $pov->option_value_id }}">
                                                </td>
                                                <td class="text-end">
                                                    {{ $pov->quantity }}
                                                    <input type="hidden" name="product_option[{{ $row }}][product_option_value][{{ $valRow }}][quantity]" value="{{ $pov->quantity }}">
                                                </td>
                                                <td class="text-center">
                                                    {{ $pov->subtract ? $lang->text_yes : $lang->text_no }}
                                                    <input type="hidden" name="product_option[{{ $row }}][product_option_value][{{ $valRow }}][subtract]" value="{{ $pov->subtract ? 1 : 0 }}">
                                                </td>
                                                <td class="text-end">
                                                    {{ $pov->price_prefix }}{{ number_format($pov->price, 0) }}
                                                    <input type="hidden" name="product_option[{{ $row }}][product_option_value][{{ $valRow }}][price_prefix]" value="{{ $pov->price_prefix }}">
                                                    <input type="hidden" name="product_option[{{ $row }}][product_option_value][{{ $valRow }}][price]" value="{{ $pov->price }}">
                                                </td>
                                                <td class="text-end">
                                                    {{ $pov->weight_prefix }}{{ $pov->weight }}
                                                    <input type="hidden" name="product_option[{{ $row }}][product_option_value][{{ $valRow }}][weight_prefix]" value="{{ $pov->weight_prefix }}">
                                                    <input type="hidden" name="product_option[{{ $row }}][product_option_value][{{ $valRow }}][weight]" value="{{ $pov->weight }}">
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" data-option-row="{{ $row }}" data-option-value-row="{{ $valRow }}" class="btn btn-primary btn-sm btn-edit-option-value"><i class="bi bi-pencil"></i></button>
                                                    <button type="button" onclick="$('#option-value-row-{{ $row }}-{{ $valRow }}').remove();" class="btn btn-danger btn-sm"><i class="bi bi-dash-circle"></i></button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="5"></td>
                                                <td class="text-end">
                                                    <button type="button" onclick="openModalAdd({{ $row }});" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i></button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    @else
                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">{{ $lang->column_option_value }}</label>
                                        <div class="col-sm-10">
                                            @if($opt->type == 'textarea')
                                            <textarea name="product_option[{{ $row }}][value]" class="form-control" rows="3">{{ $productOption->value }}</textarea>
                                            @else
                                            <input type="{{ $opt->type == 'date' ? 'date' : ($opt->type == 'time' ? 'time' : ($opt->type == 'datetime' ? 'datetime-local' : 'text')) }}" name="product_option[{{ $row }}][value]" value="{{ $productOption->value }}" class="form-control">
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </fieldset>
                                @endforeach
                            </div>

                            {{-- 新增選項 --}}
                            <fieldset>
                                <legend>{{ $lang->text_option_add }}</legend>
                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">{{ $lang->column_option }}</label>
                                    <div class="col-sm-10">
                                        <div class="input-group">
                                            <select id="input-option-select" class="form-select">
                                                <option value="">{{ $lang->text_select_option }}</option>
                                                @foreach($availableOptions as $opt)
                                                <option value="{{ $opt['option_id'] }}">{{ $opt['name'] }} ({{ $lang->{'text_' . $opt['type']} }})</option>
                                                @endforeach
                                            </select>
                                            <button type="button" id="button-option-add" class="btn btn-primary"><i class="bi bi-plus-lg"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal: 選項值編輯 --}}
<div class="modal fade" id="modal-option-value" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $lang->text_option_value }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-option-row">
                <input type="hidden" id="modal-option-value-row">
                <div class="mb-3">
                    <label class="form-label">{{ $lang->column_option_value }}</label>
                    <select id="modal-option-value-id" class="form-select"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ $lang->column_quantity }}</label>
                    <input type="number" id="modal-quantity" class="form-control" value="0" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ $lang->column_subtract }}</label>
                    <select id="modal-subtract" class="form-select">
                        <option value="0">{{ $lang->text_no }}</option>
                        <option value="1">{{ $lang->text_yes }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ $lang->column_price }}</label>
                    <div class="input-group">
                        <select id="modal-price-prefix" class="form-select" style="max-width:60px;">
                            <option value="+">+</option>
                            <option value="-">-</option>
                        </select>
                        <input type="number" id="modal-price" class="form-control" value="0" step="1">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ $lang->column_weight }}</label>
                    <div class="input-group">
                        <select id="modal-weight-prefix" class="form-select" style="max-width:60px;">
                            <option value="+">+</option>
                            <option value="-">-</option>
                        </select>
                        <input type="number" id="modal-weight" class="form-control" value="0" step="0.00000001">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $lang->button_cancel }}</button>
                <button type="button" id="modal-save" class="btn btn-primary">{{ $lang->button_save }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
var option_row = {{ count($productOptions) }};
var option_value_rows = {};
var choiceTypes = ['select', 'radio', 'checkbox'];
var availableOptions = @json($availableOptions);
var lang = {
    text_yes: '{{ $lang->text_yes }}',
    text_no: '{{ $lang->text_no }}',
    column_option_value: '{{ $lang->column_option_value }}',
    column_quantity: '{{ $lang->column_quantity }}',
    column_subtract: '{{ $lang->column_subtract }}',
    column_price: '{{ $lang->column_price }}',
    column_weight: '{{ $lang->column_weight }}',
    column_required: '{{ $lang->column_required }}'
};

// 初始化既有選項的 value_row 計數器
@foreach($productOptions as $row => $productOption)
option_value_rows[{{ $row }}] = {{ count($productOption->productOptionValues) }};
@endforeach

// ========== 新增選項 ==========
$('#button-option-add').on('click', function() {
    var optionId = $('#input-option-select').val();
    if (!optionId) return;

    var opt = availableOptions.find(function(o) { return o.option_id == optionId; });
    if (!opt) return;

    var isChoice = choiceTypes.indexOf(opt.type) !== -1;
    option_value_rows[option_row] = 0;

    var html = '<fieldset id="option-row-' + option_row + '">';
    html += '<legend>' + opt.name + ' <small class="text-muted">(' + $('#input-option-select option:selected').text().match(/\((.+)\)/)[1] + ')</small>';
    html += ' <button type="button" onclick="$(\'#option-row-' + option_row + '\').remove();" class="btn btn-danger btn-sm float-end"><i class="bi bi-dash-circle"></i></button>';
    html += '</legend>';
    html += '<input type="hidden" name="product_option[' + option_row + '][option_id]" value="' + opt.option_id + '">';

    // Required
    html += '<div class="row mb-3">';
    html += '<label class="col-sm-2 col-form-label">' + lang.column_required + '</label>';
    html += '<div class="col-sm-10"><select name="product_option[' + option_row + '][required]" class="form-select">';
    html += '<option value="1">' + lang.text_yes + '</option>';
    html += '<option value="0" selected>' + lang.text_no + '</option>';
    html += '</select></div></div>';

    if (isChoice) {
        html += '<table class="table table-bordered table-hover">';
        html += '<thead><tr>';
        html += '<th>' + lang.column_option_value + '</th>';
        html += '<th class="text-end" style="width:80px;">' + lang.column_quantity + '</th>';
        html += '<th class="text-center" style="width:80px;">' + lang.column_subtract + '</th>';
        html += '<th class="text-end" style="width:120px;">' + lang.column_price + '</th>';
        html += '<th class="text-end" style="width:120px;">' + lang.column_weight + '</th>';
        html += '<th style="width:80px;"></th>';
        html += '</tr></thead>';
        html += '<tbody id="option-value-tbody-' + option_row + '"></tbody>';
        html += '<tfoot><tr><td colspan="5"></td>';
        html += '<td class="text-end"><button type="button" onclick="openModalAdd(' + option_row + ');" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i></button></td>';
        html += '</tr></tfoot></table>';
    } else {
        html += '<div class="row mb-3">';
        html += '<label class="col-sm-2 col-form-label">' + lang.column_option_value + '</label>';
        html += '<div class="col-sm-10">';
        if (opt.type === 'textarea') {
            html += '<textarea name="product_option[' + option_row + '][value]" class="form-control" rows="3"></textarea>';
        } else {
            var inputType = opt.type === 'date' ? 'date' : (opt.type === 'time' ? 'time' : (opt.type === 'datetime' ? 'datetime-local' : 'text'));
            html += '<input type="' + inputType + '" name="product_option[' + option_row + '][value]" value="" class="form-control">';
        }
        html += '</div></div>';
    }

    html += '</fieldset>';

    $('#product-option-container').append(html);
    option_row++;
    $('#input-option-select').val('');
});

// ========== Modal: 開窗新增選項值 ==========
function openModalAdd(optRow) {
    var optionId = $('input[name="product_option[' + optRow + '][option_id]"]').val();
    var opt = availableOptions.find(function(o) { return o.option_id == optionId; });
    if (!opt || !opt.option_values.length) return;

    // 填充選項值下拉
    var $select = $('#modal-option-value-id').empty();
    for (var i = 0; i < opt.option_values.length; i++) {
        var ov = opt.option_values[i];
        $select.append('<option value="' + ov.option_value_id + '">' + ov.name + '</option>');
    }

    // 重設欄位
    $('#modal-option-row').val(optRow);
    $('#modal-option-value-row').val(''); // 空 = 新增模式
    $('#modal-quantity').val(0);
    $('#modal-subtract').val('0');
    $('#modal-price-prefix').val('+');
    $('#modal-price').val(0);
    $('#modal-weight-prefix').val('+');
    $('#modal-weight').val(0);

    new bootstrap.Modal('#modal-option-value').show();
}

// ========== Modal: 開窗編輯選項值 ==========
$(document).on('click', '.btn-edit-option-value', function() {
    var optRow = $(this).data('option-row');
    var valRow = $(this).data('option-value-row');
    var prefix = 'product_option[' + optRow + '][product_option_value][' + valRow + ']';
    var $tr = $('#option-value-row-' + optRow + '-' + valRow);

    var optionId = $('input[name="product_option[' + optRow + '][option_id]"]').val();
    var opt = availableOptions.find(function(o) { return o.option_id == optionId; });
    if (!opt) return;

    // 填充選項值下拉
    var currentValueId = $tr.find('input[name="' + prefix + '[option_value_id]"]').val();
    var $select = $('#modal-option-value-id').empty();
    for (var i = 0; i < opt.option_values.length; i++) {
        var ov = opt.option_values[i];
        $select.append('<option value="' + ov.option_value_id + '"' + (ov.option_value_id == currentValueId ? ' selected' : '') + '>' + ov.name + '</option>');
    }

    // 填入現有值
    $('#modal-option-row').val(optRow);
    $('#modal-option-value-row').val(valRow); // 有值 = 編輯模式
    $('#modal-quantity').val($tr.find('input[name="' + prefix + '[quantity]"]').val());
    $('#modal-subtract').val($tr.find('input[name="' + prefix + '[subtract]"]').val());
    $('#modal-price-prefix').val($tr.find('input[name="' + prefix + '[price_prefix]"]').val());
    $('#modal-price').val($tr.find('input[name="' + prefix + '[price]"]').val());
    $('#modal-weight-prefix').val($tr.find('input[name="' + prefix + '[weight_prefix]"]').val());
    $('#modal-weight').val($tr.find('input[name="' + prefix + '[weight]"]').val());

    new bootstrap.Modal('#modal-option-value').show();
});

// ========== Modal: 儲存 ==========
$('#modal-save').on('click', function() {
    var optRow = $('#modal-option-row').val();
    var valRow = $('#modal-option-value-row').val();

    var optionValueId = $('#modal-option-value-id').val();
    var optionValueName = $('#modal-option-value-id option:selected').text();
    var quantity = $('#modal-quantity').val();
    var subtract = $('#modal-subtract').val();
    var subtractText = subtract == '1' ? lang.text_yes : lang.text_no;
    var pricePrefix = $('#modal-price-prefix').val();
    var price = $('#modal-price').val();
    var weightPrefix = $('#modal-weight-prefix').val();
    var weight = $('#modal-weight').val();

    var isEdit = (valRow !== '');

    if (!isEdit) {
        // 新增模式：產生新的 valRow
        valRow = option_value_rows[optRow] || 0;
        option_value_rows[optRow] = valRow + 1;
    }

    var prefix = 'product_option[' + optRow + '][product_option_value][' + valRow + ']';

    var html = '<tr id="option-value-row-' + optRow + '-' + valRow + '">';
    html += '<td>' + optionValueName + '<input type="hidden" name="' + prefix + '[option_value_id]" value="' + optionValueId + '"></td>';
    html += '<td class="text-end">' + quantity + '<input type="hidden" name="' + prefix + '[quantity]" value="' + quantity + '"></td>';
    html += '<td class="text-center">' + subtractText + '<input type="hidden" name="' + prefix + '[subtract]" value="' + subtract + '"></td>';
    html += '<td class="text-end">' + pricePrefix + parseFloat(price).toLocaleString() + '<input type="hidden" name="' + prefix + '[price_prefix]" value="' + pricePrefix + '"><input type="hidden" name="' + prefix + '[price]" value="' + price + '"></td>';
    html += '<td class="text-end">' + weightPrefix + weight + '<input type="hidden" name="' + prefix + '[weight_prefix]" value="' + weightPrefix + '"><input type="hidden" name="' + prefix + '[weight]" value="' + weight + '"></td>';
    html += '<td class="text-end">';
    html += '<button type="button" data-option-row="' + optRow + '" data-option-value-row="' + valRow + '" class="btn btn-primary btn-sm btn-edit-option-value"><i class="bi bi-pencil"></i></button> ';
    html += '<button type="button" onclick="$(\'#option-value-row-' + optRow + '-' + valRow + '\').remove();" class="btn btn-danger btn-sm"><i class="bi bi-dash-circle"></i></button>';
    html += '</td>';
    html += '</tr>';

    if (isEdit) {
        $('#option-value-row-' + optRow + '-' + valRow).replaceWith(html);
    } else {
        $('#option-value-tbody-' + optRow).append(html);
    }

    bootstrap.Modal.getInstance(document.getElementById('modal-option-value')).hide();
});
</script>
@endsection
