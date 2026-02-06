@extends('ocadmin::layouts.app')

@section('title', $setting->exists ? $lang->text_edit : $lang->text_add)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-setting" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.setting.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $setting->exists ? $lang->text_edit : $lang->text_add }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="card card-default">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ $setting->exists ? $lang->text_edit : $lang->text_add }}
            </div>
            <div class="card-body">
                <form action="{{ $setting->exists ? route('lang.ocadmin.system.setting.update', $setting) : route('lang.ocadmin.system.setting.store') }}" method="post" id="form-setting" data-oc-toggle="ajax">
                    @csrf
                    @if($setting->exists)
                    @method('PUT')
                    @endif

                    <div class="row mb-3 required">
                        <label for="input-code" class="col-sm-2 col-form-label">{{ $lang->column_code }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="code" value="{{ old('code', $setting->code) }}" placeholder="{{ $lang->placeholder_code }}" id="input-code" class="form-control">
                            <div id="error-code" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_code }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-group" class="col-sm-2 col-form-label">{{ $lang->column_group }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="group" value="{{ old('group', $setting->group) }}" placeholder="{{ $lang->placeholder_group }}" id="input-group" class="form-control">
                            <div id="error-group" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_group }}</div>
                        </div>
                    </div>

                    <div class="row mb-3 required">
                        <label for="input-type" class="col-sm-2 col-form-label">{{ $lang->column_type }}</label>
                        <div class="col-sm-10">
                            <select name="type" id="input-type" class="form-select">
                                @foreach($types as $type)
                                <option value="{{ $type->value }}" {{ old('type', $setting->type?->value) === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                            <div id="error-type" class="invalid-feedback"></div>
                        </div>
                    </div>

                    {{-- 一般內容欄位 --}}
                    <div class="row mb-3" id="content-normal">
                        <label for="input-content" class="col-sm-2 col-form-label">{{ $lang->column_value }}</label>
                        <div class="col-sm-10">
                            <textarea name="value" rows="6" placeholder="{{ $lang->placeholder_value }}" id="input-content" class="form-control">{{ old('value', $setting->value) }}</textarea>
                            <div id="error-value" class="invalid-feedback"></div>
                            <div class="form-text" id="content-hint">
                                根據類型輸入對應格式的內容
                            </div>
                        </div>
                    </div>

                    {{-- JSON 兩欄顯示 --}}
                    <div class="row mb-3 d-none" id="content-json">
                        <label class="col-sm-2 col-form-label">{{ $lang->column_value }}</label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">原始 JSON <small class="text-muted">（儲存值）</small></label>
                                    <textarea rows="12" id="input-json-raw" class="form-control font-monospace" style="font-size: 12px;" readonly></textarea>
                                    <div class="form-text">壓縮的 JSON 字串，此為實際儲存的值</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">展開 JSON <small class="text-muted">（編輯區）</small></label>
                                    <textarea rows="12" id="input-json-pretty" class="form-control font-monospace" style="font-size: 12px;" placeholder="請輸入 JSON 內容"></textarea>
                                    <div class="form-text">
                                        <span id="json-status" class="text-success">格式正確</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="btn-format-json">格式化</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 布林值 Radio --}}
                    <div class="row mb-3 d-none" id="content-bool">
                        <label class="col-sm-2 col-form-label">{{ $lang->column_value }}</label>
                        <div class="col-sm-10 pt-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="content_bool" id="input-bool-yes" value="1">
                                <label class="form-check-label" for="input-bool-yes">{{ $lang->text_yes }} (1)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="content_bool" id="input-bool-no" value="0">
                                <label class="form-check-label" for="input-bool-no">{{ $lang->text_no }} (0)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="content_bool" id="input-bool-null" value="">
                                <label class="form-check-label" for="input-bool-null">{{ $lang->text_none }} (null)</label>
                            </div>
                        </div>
                    </div>

                    {{-- 序列化兩欄顯示 --}}
                    <div class="row mb-3 d-none" id="content-serialized">
                        <label class="col-sm-2 col-form-label">{{ $lang->column_value }}</label>
                        <div class="col-sm-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">序列化字串 <small class="text-muted">（儲存值）</small></label>
                                    <textarea rows="12" id="input-serialize-raw" class="form-control font-monospace" style="font-size: 12px;" readonly></textarea>
                                    <div class="form-text">PHP serialize 格式，此為實際儲存的值</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">展開內容 <small class="text-muted">（JSON 編輯區）</small></label>
                                    <textarea rows="12" id="input-serialize-pretty" class="form-control font-monospace" style="font-size: 12px;" placeholder="請輸入 JSON 內容"></textarea>
                                    <div class="form-text">
                                        <span id="serialize-status" class="text-success">格式正確</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="btn-format-serialize">格式化</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="input-note" class="col-sm-2 col-form-label">{{ $lang->column_note }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="note" value="{{ old('note', $setting->note) }}" placeholder="{{ $lang->placeholder_note }}" id="input-note" class="form-control">
                            <div id="error-note" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_note }}</div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    var hints = {
        'text': '輸入純文字',
        'line': '一行一個項目',
        'json': '輸入有效的 JSON 格式',
        'serialized': '輸入 PHP 序列化格式',
        'bool': '輸入 1（是）或 0（否）',
        'int': '輸入整數',
        'float': '輸入小數',
        'array': '輸入逗號分隔的值'
    };

    $('#input-type').on('change', function() {
        var type = $(this).val();
        $('#content-hint').text(hints[type] || '根據類型輸入對應格式的內容');

        $('#content-normal, #content-json, #content-bool, #content-serialized').addClass('d-none');

        if (type === 'json') {
            $('#content-json').removeClass('d-none');
            var content = $('#input-content').val();
            if (content) {
                try {
                    var parsed = JSON.parse(content);
                    $('#input-json-pretty').val(JSON.stringify(parsed, null, 2));
                    $('#input-json-raw').val(JSON.stringify(parsed));
                    updateJsonStatus(true);
                } catch (e) {
                    $('#input-json-pretty').val(content);
                    $('#input-json-raw').val(content);
                    updateJsonStatus(false, e.message);
                }
            } else {
                $('#input-json-pretty').val('');
                $('#input-json-raw').val('');
                updateJsonStatus(true);
            }
        } else if (type === 'bool') {
            $('#content-bool').removeClass('d-none');
            var content = $('#input-content').val();
            if (content === '1' || content === 'true') {
                $('#input-bool-yes').prop('checked', true);
            } else if (content === '0' || content === 'false') {
                $('#input-bool-no').prop('checked', true);
            } else {
                $('#input-bool-null').prop('checked', true);
            }
        } else if (type === 'serialized') {
            $('#content-serialized').removeClass('d-none');
            var content = $('#input-content').val();
            $('#input-serialize-raw').val(content);
            if (content) {
                $.ajax({
                    url: '{{ route("lang.ocadmin.system.setting.parse-serialize") }}',
                    type: 'POST',
                    data: { value: content, _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function(json) {
                        if (json.success) {
                            $('#input-serialize-pretty').val(JSON.stringify(json.data, null, 2));
                            updateSerializeStatus(true);
                        } else {
                            $('#input-serialize-pretty').val(content);
                            updateSerializeStatus(false, json.message);
                        }
                    },
                    error: function() {
                        $('#input-serialize-pretty').val(content);
                        updateSerializeStatus(false, '解析失敗');
                    }
                });
            } else {
                $('#input-serialize-pretty').val('');
                updateSerializeStatus(true);
            }
        } else {
            $('#content-normal').removeClass('d-none');
        }
    }).trigger('change');

    $('input[name="content_bool"]').on('change', function() {
        $('#input-content').val($(this).val());
    });

    $('#input-json-pretty').on('input', function() {
        var content = $(this).val();
        if (!content) {
            $('#input-json-raw').val('');
            $('#input-content').val('');
            updateJsonStatus(true);
            return;
        }
        try {
            var parsed = JSON.parse(content);
            var minified = JSON.stringify(parsed);
            $('#input-json-raw').val(minified);
            $('#input-content').val(minified);
            updateJsonStatus(true);
        } catch (e) {
            $('#input-json-raw').val(content);
            $('#input-content').val(content);
            updateJsonStatus(false, e.message);
        }
    });

    $('#btn-format-json').on('click', function() {
        var content = $('#input-json-pretty').val();
        if (!content) return;
        try {
            var parsed = JSON.parse(content);
            $('#input-json-pretty').val(JSON.stringify(parsed, null, 2));
            updateJsonStatus(true);
        } catch (e) {
            updateJsonStatus(false, e.message);
        }
    });

    function updateJsonStatus(valid, errorMsg) {
        if (valid) {
            $('#json-status').removeClass('text-danger').addClass('text-success').text('格式正確');
        } else {
            $('#json-status').removeClass('text-success').addClass('text-danger').text('格式錯誤：' + errorMsg);
        }
    }

    var serializeTimer = null;
    $('#input-serialize-pretty').on('input', function() {
        var content = $(this).val();
        clearTimeout(serializeTimer);
        serializeTimer = setTimeout(function() {
            if (!content) {
                $('#input-serialize-raw').val('');
                $('#input-content').val('');
                updateSerializeStatus(true);
                return;
            }
            try {
                JSON.parse(content);
            } catch (e) {
                updateSerializeStatus(false, 'JSON 格式錯誤：' + e.message);
                return;
            }
            $.ajax({
                url: '{{ route("lang.ocadmin.system.setting.to-serialize") }}',
                type: 'POST',
                data: { value: content, _token: '{{ csrf_token() }}' },
                dataType: 'json',
                success: function(json) {
                    if (json.success) {
                        $('#input-serialize-raw').val(json.data);
                        $('#input-content').val(json.data);
                        updateSerializeStatus(true);
                    } else {
                        updateSerializeStatus(false, json.message);
                    }
                },
                error: function() {
                    updateSerializeStatus(false, '轉換失敗');
                }
            });
        }, 500);
    });

    $('#btn-format-serialize').on('click', function() {
        var content = $('#input-serialize-pretty').val();
        if (!content) return;
        try {
            var parsed = JSON.parse(content);
            $('#input-serialize-pretty').val(JSON.stringify(parsed, null, 2));
            updateSerializeStatus(true);
        } catch (e) {
            updateSerializeStatus(false, 'JSON 格式錯誤：' + e.message);
        }
    });

    function updateSerializeStatus(valid, errorMsg) {
        if (valid) {
            $('#serialize-status').removeClass('text-danger').addClass('text-success').text('格式正確');
        } else {
            $('#serialize-status').removeClass('text-success').addClass('text-danger').text('格式錯誤：' + errorMsg);
        }
    }
});
</script>
@endsection
