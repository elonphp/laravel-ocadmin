@extends('ocadmin::layouts.app')

@section('title', $is_new ? $lang->text_add : $lang->text_edit . '：' . $table_name)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="submit" form="form-schema" data-bs-toggle="tooltip" title="{{ $lang->button_save }}" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                </button>
                @if(!$is_new)
                <button type="button" id="button-diff" data-bs-toggle="tooltip" title="{{ $lang->button_diff }}" class="btn btn-warning">
                    <i class="fa-solid fa-code-compare"></i>
                </button>
                @endif
                <a href="{{ route('lang.ocadmin.system.schema.index') }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $is_new ? $lang->text_add : $lang->text_edit . '：' . $table_name }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        @if(!$is_new)
        <div id="alert-pending" class="alert alert-warning alert-dismissible fade" role="alert" style="display:none;">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ $lang->text_pending_changes }}
            <button type="button" class="btn btn-sm btn-success ms-2" id="button-apply-inline">
                <i class="fa-solid fa-database"></i> {{ $lang->button_apply }}
            </button>
        </div>
        @endif
        <form action="{{ $is_new ? route('lang.ocadmin.system.schema.store') : route('lang.ocadmin.system.schema.update', $table_name) }}" method="post" id="form-schema" data-oc-toggle="ajax">
            @csrf
            @if(!$is_new)
            @method('PUT')
            @endif

            {{-- 基本資訊 --}}
            <div class="card mb-3">
                <div class="card-header"><i class="fa-solid fa-info-circle"></i> 基本資訊</div>
                <div class="card-body">
                    <div class="row mb-3 required">
                        <label for="input-table-name" class="col-sm-2 col-form-label">{{ $lang->column_table_name }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="table_name" value="{{ $table_name }}" placeholder="sal_orders" id="input-table-name" class="form-control" {{ !$is_new ? 'readonly' : '' }}>
                            <div id="error-table_name" class="invalid-feedback"></div>
                            <div class="form-text">{{ $lang->help_table_name }}</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="input-comment" class="col-sm-2 col-form-label">{{ $lang->column_table_comment }}</label>
                        <div class="col-sm-10">
                            <input type="text" name="comment" value="{{ $comment }}" id="input-comment" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 切換 --}}
            <ul class="nav nav-tabs" id="schema-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-columns">
                        <i class="fa-solid fa-table-columns"></i> {{ $lang->tab_columns }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-translations">
                        <i class="fa-solid fa-language"></i> {{ $lang->tab_translations }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-indexes">
                        <i class="fa-solid fa-list-ol"></i> {{ $lang->tab_indexes }}
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                {{-- 欄位定義 Tab --}}
                <div class="tab-pane fade show active" id="tab-columns">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="table-columns">
                                    <thead>
                                        <tr class="table-light">
                                            <th style="width:20px;"></th>
                                            <th style="width:130px;">{{ $lang->column_column_name }}</th>
                                            <th style="width:120px;">{{ $lang->column_type }}</th>
                                            <th style="width:80px;">{{ $lang->column_length }}</th>
                                            <th style="width:70px;" class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $lang->help_unsigned }}">Unsigned</th>
                                            <th style="width:50px;" class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $lang->help_nullable }}">Null</th>
                                            <th style="width:90px;">{{ $lang->column_default }}</th>
                                            <th style="width:40px;" class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $lang->help_primary }}">{{ $lang->column_primary }}</th>
                                            <th style="width:40px;" class="text-center">{{ $lang->column_auto_inc }}</th>
                                            <th style="width:40px;" class="text-center">{{ $lang->column_index }}</th>
                                            <th style="width:60px;" class="text-center">{{ $lang->column_unique }}</th>
                                            <th style="width:120px;">{{ $lang->column_foreign }}(table.id)</th>
                                            <th style="width:120px;">{{ $lang->column_comment }}</th>
                                            <th style="width:40px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="column-rows">
                                        @foreach($columns as $i => $col)
                                        <tr class="column-row">
                                            <td class="text-center sort-handle" style="cursor:grab;"><i class="fa-solid fa-grip-vertical text-muted"></i></td>
                                            <td><input type="text" name="columns[{{ $i }}][name]" value="{{ $col['name'] }}" class="form-control form-control-sm"></td>
                                            <td>
                                                <select name="columns[{{ $i }}][type]" class="form-select form-select-sm">
                                                    @foreach($supportedTypes as $group => $types)
                                                    <optgroup label="{{ $group }}">
                                                        @foreach($types as $type)
                                                        <option value="{{ $type }}" {{ ($col['type'] ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" name="columns[{{ $i }}][length]" value="{{ $col['length'] ?? '' }}" class="form-control form-control-sm"></td>
                                            <td class="text-center"><input type="checkbox" name="columns[{{ $i }}][unsigned]" value="1" {{ !empty($col['unsigned']) ? 'checked' : '' }} class="form-check-input"></td>
                                            <td class="text-center"><input type="checkbox" name="columns[{{ $i }}][nullable]" value="1" {{ !empty($col['nullable']) ? 'checked' : '' }} class="form-check-input"></td>
                                            <td><input type="text" name="columns[{{ $i }}][default]" value="{{ $col['default'] ?? '' }}" class="form-control form-control-sm"></td>
                                            <td class="text-center"><input type="checkbox" name="columns[{{ $i }}][primary]" value="1" {{ !empty($col['primary']) ? 'checked' : '' }} class="form-check-input"></td>
                                            <td class="text-center"><input type="checkbox" name="columns[{{ $i }}][auto_increment]" value="1" {{ !empty($col['auto_increment']) ? 'checked' : '' }} class="form-check-input"></td>
                                            <td class="text-center"><input type="checkbox" name="columns[{{ $i }}][index]" value="1" {{ !empty($col['index']) ? 'checked' : '' }} class="form-check-input"></td>
                                            <td class="text-center"><input type="checkbox" name="columns[{{ $i }}][unique]" value="1" {{ !empty($col['unique']) ? 'checked' : '' }} class="form-check-input"></td>
                                            <td><input type="text" name="columns[{{ $i }}][foreign]" value="{{ $col['foreign'] ?? '' }}" class="form-control form-control-sm"></td>
                                            <td><input type="text" name="columns[{{ $i }}][comment]" value="{{ $col['comment'] ?? '' }}" class="form-control form-control-sm"></td>
                                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fa-solid fa-times"></i></button></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="button-add-column" class="btn btn-outline-primary btn-sm">
                                <i class="fa-solid fa-plus"></i> {{ $lang->button_add_column }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 翻譯欄位 Tab --}}
                <div class="tab-pane fade" id="tab-translations">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="table-translations">
                                    <thead>
                                        <tr class="table-light">
                                            <th style="width:20px;"></th>
                                            <th style="width:150px;">{{ $lang->column_column_name }}</th>
                                            <th style="width:140px;">{{ $lang->column_type }}</th>
                                            <th style="width:100px;">{{ $lang->column_length }}</th>
                                            <th style="width:50px;" class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $lang->help_nullable }}">Null</th>
                                            <th>{{ $lang->column_comment }}</th>
                                            <th style="width:40px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="translation-rows">
                                        @foreach($translations as $i => $col)
                                        <tr class="translation-row">
                                            <td class="text-center sort-handle" style="cursor:grab;"><i class="fa-solid fa-grip-vertical text-muted"></i></td>
                                            <td><input type="text" name="translations[{{ $i }}][name]" value="{{ $col['name'] }}" class="form-control form-control-sm"></td>
                                            <td>
                                                <select name="translations[{{ $i }}][type]" class="form-select form-select-sm">
                                                    @foreach($supportedTypes as $group => $types)
                                                    <optgroup label="{{ $group }}">
                                                        @foreach($types as $type)
                                                        <option value="{{ $type }}" {{ ($col['type'] ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" name="translations[{{ $i }}][length]" value="{{ $col['length'] ?? '' }}" class="form-control form-control-sm"></td>
                                            <td class="text-center"><input type="checkbox" name="translations[{{ $i }}][nullable]" value="1" {{ !empty($col['nullable']) ? 'checked' : '' }} class="form-check-input"></td>
                                            <td><input type="text" name="translations[{{ $i }}][comment]" value="{{ $col['comment'] ?? '' }}" class="form-control form-control-sm"></td>
                                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fa-solid fa-times"></i></button></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="button-add-translation" class="btn btn-outline-primary btn-sm">
                                <i class="fa-solid fa-plus"></i> {{ $lang->button_add_translation }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 索引 Tab --}}
                <div class="tab-pane fade" id="tab-indexes">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="table-indexes">
                                    <thead>
                                        <tr class="table-light">
                                            <th style="width:250px;">{{ $lang->column_index_name }}</th>
                                            <th style="width:120px;">{{ $lang->column_index_type }}</th>
                                            <th>{{ $lang->column_index_columns }}</th>
                                            <th style="width:40px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="index-rows">
                                        @foreach($compositeIndexes as $i => $idx)
                                        <tr class="index-row">
                                            <td><input type="text" name="composite_indexes[{{ $i }}][name]" value="{{ $idx['name'] }}" class="form-control form-control-sm"></td>
                                            <td>
                                                <select name="composite_indexes[{{ $i }}][type]" class="form-select form-select-sm">
                                                    <option value="INDEX" {{ $idx['type'] === 'INDEX' ? 'selected' : '' }}>INDEX</option>
                                                    <option value="UNIQUE" {{ $idx['type'] === 'UNIQUE' ? 'selected' : '' }}>UNIQUE</option>
                                                </select>
                                            </td>
                                            <td><input type="text" name="composite_indexes[{{ $i }}][columns]" value="{{ $idx['columns'] }}" class="form-control form-control-sm"></td>
                                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fa-solid fa-times"></i></button></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="button-add-index" class="btn btn-outline-primary btn-sm">
                                <i class="fa-solid fa-plus"></i> {{ $lang->button_add_index }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 差異比對 Modal --}}
@if(!$is_new)
<div class="modal fade" id="modal-diff" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $lang->text_diff_preview }}：{{ $table_name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="diff-content">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $lang->button_close }}</button>
                <button type="button" class="btn btn-warning" id="button-sync" style="display:none;">
                    <i class="fa-solid fa-sync"></i> {{ $lang->button_sync }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    var columnIndex = {{ count($columns) }};
    var translationIndex = {{ count($translations) }};
    var indexIndex = {{ count($compositeIndexes) }};

    // 欄位類型選項 HTML
    var typeOptions = '';
    @foreach($supportedTypes as $group => $types)
    typeOptions += '<optgroup label="{{ $group }}">';
    @foreach($types as $type)
    typeOptions += '<option value="{{ $type }}">{{ $type }}</option>';
    @endforeach
    typeOptions += '</optgroup>';
    @endforeach

    // 新增欄位行
    $('#button-add-column').on('click', function() {
        var html = '<tr class="column-row">' +
            '<td class="text-center sort-handle" style="cursor:grab;"><i class="fa-solid fa-grip-vertical text-muted"></i></td>' +
            '<td><input type="text" name="columns[' + columnIndex + '][name]" class="form-control form-control-sm"></td>' +
            '<td><select name="columns[' + columnIndex + '][type]" class="form-select form-select-sm">' + typeOptions + '</select></td>' +
            '<td><input type="text" name="columns[' + columnIndex + '][length]" class="form-control form-control-sm"></td>' +
            '<td class="text-center"><input type="checkbox" name="columns[' + columnIndex + '][unsigned]" value="1" class="form-check-input"></td>' +
            '<td class="text-center"><input type="checkbox" name="columns[' + columnIndex + '][nullable]" value="1" class="form-check-input"></td>' +
            '<td><input type="text" name="columns[' + columnIndex + '][default]" class="form-control form-control-sm"></td>' +
            '<td class="text-center"><input type="checkbox" name="columns[' + columnIndex + '][primary]" value="1" class="form-check-input"></td>' +
            '<td class="text-center"><input type="checkbox" name="columns[' + columnIndex + '][auto_increment]" value="1" class="form-check-input"></td>' +
            '<td class="text-center"><input type="checkbox" name="columns[' + columnIndex + '][index]" value="1" class="form-check-input"></td>' +
            '<td class="text-center"><input type="checkbox" name="columns[' + columnIndex + '][unique]" value="1" class="form-check-input"></td>' +
            '<td><input type="text" name="columns[' + columnIndex + '][foreign]" class="form-control form-control-sm"></td>' +
            '<td><input type="text" name="columns[' + columnIndex + '][comment]" class="form-control form-control-sm"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fa-solid fa-times"></i></button></td>' +
            '</tr>';

        $('#column-rows').append(html);
        columnIndex++;
    });

    // 新增翻譯欄位行
    $('#button-add-translation').on('click', function() {
        var html = '<tr class="translation-row">' +
            '<td class="text-center sort-handle" style="cursor:grab;"><i class="fa-solid fa-grip-vertical text-muted"></i></td>' +
            '<td><input type="text" name="translations[' + translationIndex + '][name]" class="form-control form-control-sm"></td>' +
            '<td><select name="translations[' + translationIndex + '][type]" class="form-select form-select-sm">' + typeOptions + '</select></td>' +
            '<td><input type="text" name="translations[' + translationIndex + '][length]" class="form-control form-control-sm"></td>' +
            '<td class="text-center"><input type="checkbox" name="translations[' + translationIndex + '][nullable]" value="1" class="form-check-input"></td>' +
            '<td><input type="text" name="translations[' + translationIndex + '][comment]" class="form-control form-control-sm"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fa-solid fa-times"></i></button></td>' +
            '</tr>';

        $('#translation-rows').append(html);
        translationIndex++;
    });

    // 新增索引行
    $('#button-add-index').on('click', function() {
        var html = '<tr class="index-row">' +
            '<td><input type="text" name="composite_indexes[' + indexIndex + '][name]" class="form-control form-control-sm"></td>' +
            '<td><select name="composite_indexes[' + indexIndex + '][type]" class="form-select form-select-sm"><option value="INDEX">INDEX</option><option value="UNIQUE">UNIQUE</option></select></td>' +
            '<td><input type="text" name="composite_indexes[' + indexIndex + '][columns]" class="form-control form-control-sm"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fa-solid fa-times"></i></button></td>' +
            '</tr>';

        $('#index-rows').append(html);
        indexIndex++;
    });

    // 刪除行
    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
    });

    @if(!$is_new)
    // 比對預覽
    $('#button-diff').on('click', function() {
        $('#diff-content').html('<div class="text-center"><i class="fa-solid fa-spinner fa-spin"></i> 載入中...</div>');
        $('#button-sync').hide();
        $('#modal-diff').modal('show');

        $.ajax({
            url: '{{ route('lang.ocadmin.system.schema.diff', $table_name) }}',
            type: 'GET',
            dataType: 'json',
            success: function(json) {
                if (json.success) {
                    renderDiff(json.diff, json.sqls);
                }
            },
            error: function(xhr) {
                $('#diff-content').html('<div class="alert alert-danger">載入失敗</div>');
            }
        });
    });

    function renderDiff(diff, sqls) {
        var html = '';

        if (diff.status === 'synced') {
            html = '<div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> {{ $lang->text_no_changes }}</div>';
        } else {
            html += '<h6>變更項目：</h6>';
            html += '<table class="table table-sm table-bordered">';
            html += '<thead><tr><th>動作</th><th>欄位</th><th>說明</th></tr></thead><tbody>';

            var actionLabels = {
                'add_column': '<span class="badge bg-success">新增欄位</span>',
                'modify_column': '<span class="badge bg-warning">修改欄位</span>',
                'extra_column': '<span class="badge bg-secondary">多餘欄位</span>',
                'create_table': '<span class="badge bg-primary">建立表</span>',
                'create_translation_table': '<span class="badge bg-info">建立翻譯表</span>',
                'add_translation_column': '<span class="badge bg-success">新增翻譯欄位</span>',
                'modify_translation_column': '<span class="badge bg-warning">修改翻譯欄位</span>'
            };

            diff.changes.forEach(function(change) {
                var label = actionLabels[change.action] || change.action;
                var detail = '';
                if (change.diffs) detail = change.diffs.join(', ');
                else if (change.definition) detail = change.definition;

                html += '<tr><td>' + label + '</td><td>' + (change.column || '-') + '</td><td>' + detail + '</td></tr>';
            });

            html += '</tbody></table>';

            if (sqls.length > 0) {
                html += '<h6 class="mt-3">{{ $lang->text_sql_preview }}：</h6>';
                html += '<pre class="bg-dark text-light p-3 rounded" style="font-size: 12px; max-height: 300px; overflow-y: auto;">';
                sqls.forEach(function(sql) {
                    html += escapeHtml(sql) + ';\n\n';
                });
                html += '</pre>';
                $('#button-sync').show();
            }
        }

        $('#diff-content').html(html);
    }

    // 執行同步
    function doSync(source) {
        if (!confirm('{{ $lang->text_confirm_sync }}')) return;

        var $btn = $(source);
        var originalHtml = $btn.html();

        $.ajax({
            url: '{{ route('lang.ocadmin.system.schema.sync', $table_name) }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            beforeSend: function() {
                $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
            },
            success: function(json) {
                if (json.success) {
                    alert(json.message);
                    $('#modal-diff').modal('hide');
                    $('#alert-pending').removeClass('show').hide();
                }
            },
            error: function(xhr) {
                alert('同步失敗：' + (xhr.responseJSON?.message || xhr.statusText));
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    }

    $('#button-sync').on('click', function() { doSync(this); });

    // 套用變更按鈕（頁首 + 提示列）
    $('#button-apply-inline').on('click', function() {
        doSync(this);
    });

    // 檢查是否有待套用的變更
    function checkPendingChanges() {
        $.ajax({
            url: '{{ route('lang.ocadmin.system.schema.diff', $table_name) }}',
            type: 'GET',
            dataType: 'json',
            success: function(json) {
                if (json.success && json.diff.status !== 'synced') {
                    $('#alert-pending').show().addClass('show');
                } else {
                    $('#alert-pending').removeClass('show').hide();
                }
            }
        });
    }

    // 儲存成功後自動檢查（Laravel 的 PUT 實際上是 POST + _method=PUT）
    $(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.url && settings.type && settings.type.toUpperCase() === 'POST'
            && settings.url.indexOf('/schema/') !== -1
            && settings.url.indexOf('/sync') === -1
            && settings.url.indexOf('/export') === -1
            && settings.data && settings.data.indexOf('_method=PUT') !== -1
        ) {
            try {
                var json = JSON.parse(xhr.responseText);
                if (json.success) {
                    setTimeout(checkPendingChanges, 300);
                }
            } catch(e) {}
        }
    });

    // 頁面載入時也檢查
    checkPendingChanges();
    @endif

    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // 拖拉排序後重新編號 name 屬性
    function reindexRows(tbody, prefix) {
        $(tbody).children('tr').each(function(i) {
            $(this).find('input, select').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\w+\[\d+\]/, prefix + '[' + i + ']'));
                }
            });
        });
    }

    // 欄位定義拖拉排序
    Sortable.create(document.getElementById('column-rows'), {
        handle: '.sort-handle',
        animation: 150,
        onEnd: function() {
            reindexRows('#column-rows', 'columns');
        }
    });

    // 翻譯欄位拖拉排序
    Sortable.create(document.getElementById('translation-rows'), {
        handle: '.sort-handle',
        animation: 150,
        onEnd: function() {
            reindexRows('#translation-rows', 'translations');
        }
    });
});
</script>
@endsection
