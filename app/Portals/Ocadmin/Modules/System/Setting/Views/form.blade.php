@extends('ocadmin::layouts.app')

@section('title', $setting->exists ? '編輯參數' : '新增參數')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-setting" data-bs-toggle="tooltip" title="儲存" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <a href="{{ route('lang.ocadmin.system.setting.index') }}" data-bs-toggle="tooltip" title="返回" class="btn btn-light">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $setting->exists ? '編輯參數' : '新增參數' }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-pencil"></i> {{ $setting->exists ? '編輯參數' : '新增參數' }}
            </div>
            <div class="card-body">
                <form id="form-setting" action="{{ $setting->exists ? route('lang.ocadmin.system.setting.update', $setting) : route('lang.ocadmin.system.setting.store') }}" method="post" data-oc-toggle="ajax">
                    @csrf
                    @if($setting->exists)
                    @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3 required" id="input-code">
                                <label class="form-label" for="input-code-field">命名空間</label>
                                <input type="text" name="code" value="{{ old('code', $setting->code) }}" placeholder="請輸入命名空間（如：ocadmin.config）" id="input-code-field" class="form-control">
                                <div id="error-code" class="invalid-feedback"></div>
                                <div class="form-text">模組代碼，用於將設定分類管理</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 required" id="input-key">
                                <label class="form-label" for="input-key-field">設定鍵</label>
                                <input type="text" name="key" value="{{ old('key', $setting->key) }}" placeholder="請輸入設定鍵（如：config_admin_limit）" id="input-key-field" class="form-control">
                                <div id="error-key" class="invalid-feedback"></div>
                                <div class="form-text">唯一識別碼，用於程式取得設定值</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3" id="input-locale">
                                <label class="form-label" for="input-locale-field">語系</label>
                                <input type="text" name="locale" value="{{ old('locale', $setting->locale) }}" placeholder="請輸入語系代碼（如：zh-TW、en）" id="input-locale-field" class="form-control">
                                <div id="error-locale" class="invalid-feedback"></div>
                                <div class="form-text">留空表示全域設定</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 required" id="input-type">
                                <label class="form-label" for="input-type-field">類型</label>
                                <select name="type" id="input-type-field" class="form-select">
                                    @foreach($types as $type)
                                    <option value="{{ $type->value }}" {{ old('type', $setting->type?->value) === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                                <div id="error-type" class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    {{-- 一般內容欄位 --}}
                    <div class="mb-3" id="content-normal">
                        <label class="form-label" for="input-value-field">內容</label>
                        <textarea name="value" rows="6" placeholder="請輸入設定值" id="input-value-field" class="form-control">{{ old('value', $setting->value) }}</textarea>
                        <div id="error-value" class="invalid-feedback"></div>
                        <div class="form-text" id="content-hint">
                            根據類型輸入對應格式的內容
                        </div>
                    </div>

                    {{-- JSON 兩欄顯示 --}}
                    <div class="mb-3 d-none" id="content-json">
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

                    {{-- 布林值 Radio --}}
                    <div class="mb-3 d-none" id="content-bool">
                        <label class="form-label">內容</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="value_bool" id="input-bool-yes" value="1">
                                <label class="form-check-label" for="input-bool-yes">是 (1)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="value_bool" id="input-bool-no" value="0">
                                <label class="form-check-label" for="input-bool-no">否 (0)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="value_bool" id="input-bool-null" value="">
                                <label class="form-check-label" for="input-bool-null">無 (null)</label>
                            </div>
                        </div>
                    </div>

                    {{-- 序列化兩欄顯示 --}}
                    <div class="mb-3 d-none" id="content-serialized">
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

                    <div class="mb-3" id="input-note">
                        <label class="form-label" for="input-note-field">備註</label>
                        <input type="text" name="note" value="{{ old('note', $setting->note) }}" placeholder="請輸入備註說明" id="input-note-field" class="form-control">
                        <div id="error-note" class="invalid-feedback"></div>
                        <div class="form-text">供管理人員參考用</div>
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
    // 類型提示
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

    // 切換類型時處理顯示
    $('#input-type-field').on('change', function() {
        var type = $(this).val();
        $('#content-hint').text(hints[type] || '根據類型輸入對應格式的內容');

        // 先隱藏所有特殊區塊
        $('#content-normal, #content-json, #content-bool, #content-serialized').addClass('d-none');

        if (type === 'json') {
            // 切換到 JSON 兩欄模式
            $('#content-json').removeClass('d-none');

            // 將現有內容同步到 JSON 編輯區
            var content = $('#input-value-field').val();
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
            // 切換到布林值模式
            $('#content-bool').removeClass('d-none');

            // 根據現有值設定 radio
            var content = $('#input-value-field').val();
            if (content === '1' || content === 'true') {
                $('#input-bool-yes').prop('checked', true);
            } else if (content === '0' || content === 'false') {
                $('#input-bool-no').prop('checked', true);
            } else {
                $('#input-bool-null').prop('checked', true);
            }
        } else if (type === 'serialized') {
            // 切換到序列化兩欄模式
            $('#content-serialized').removeClass('d-none');

            // 將現有內容解析並顯示
            var content = $('#input-value-field').val();
            $('#input-serialize-raw').val(content);

            if (content) {
                // 呼叫後端 API 解析序列化字串
                $.ajax({
                    url: '{{ route("lang.ocadmin.system.setting.parse-serialize") }}',
                    type: 'POST',
                    data: { content: content, _token: '{{ csrf_token() }}' },
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
            // 切換回一般模式
            $('#content-normal').removeClass('d-none');
        }
    }).trigger('change');

    // 布林值 radio 變更時同步到 value
    $('input[name="value_bool"]').on('change', function() {
        $('#input-value-field').val($(this).val());
    });

    // JSON 編輯區輸入時同步到原始欄位
    $('#input-json-pretty').on('input', function() {
        var content = $(this).val();
        if (!content) {
            $('#input-json-raw').val('');
            $('#input-value-field').val('');
            updateJsonStatus(true);
            return;
        }

        try {
            var parsed = JSON.parse(content);
            var minified = JSON.stringify(parsed);
            $('#input-json-raw').val(minified);
            $('#input-value-field').val(minified);
            updateJsonStatus(true);
        } catch (e) {
            $('#input-json-raw').val(content);
            $('#input-value-field').val(content);
            updateJsonStatus(false, e.message);
        }
    });

    // 格式化按鈕
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

    // 更新 JSON 狀態提示
    function updateJsonStatus(valid, errorMsg) {
        if (valid) {
            $('#json-status').removeClass('text-danger').addClass('text-success').text('格式正確');
        } else {
            $('#json-status').removeClass('text-success').addClass('text-danger').text('格式錯誤：' + errorMsg);
        }
    }

    // 序列化編輯區輸入時同步到原始欄位
    var serializeTimer = null;
    $('#input-serialize-pretty').on('input', function() {
        var content = $(this).val();

        // 延遲執行，避免頻繁請求
        clearTimeout(serializeTimer);
        serializeTimer = setTimeout(function() {
            if (!content) {
                $('#input-serialize-raw').val('');
                $('#input-value-field').val('');
                updateSerializeStatus(true);
                return;
            }

            // 先檢查是否為有效 JSON
            try {
                JSON.parse(content);
            } catch (e) {
                updateSerializeStatus(false, 'JSON 格式錯誤：' + e.message);
                return;
            }

            // 呼叫後端 API 轉換為序列化字串
            $.ajax({
                url: '{{ route("lang.ocadmin.system.setting.to-serialize") }}',
                type: 'POST',
                data: { content: content, _token: '{{ csrf_token() }}' },
                dataType: 'json',
                success: function(json) {
                    if (json.success) {
                        $('#input-serialize-raw').val(json.data);
                        $('#input-value-field').val(json.data);
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

    // 序列化格式化按鈕
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

    // 更新序列化狀態提示
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
