@extends('ocadmin::layouts.app')

@section('title', $lang->heading_title)

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">{{ $lang->heading_title }}</h3>
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
            <button type="button" data-bs-toggle="tooltip" title="{{ $lang->button_filter }}" onclick="$('#filter-schema').toggleClass('d-none');" class="btn btn-light d-lg-none">
                <i class="bi bi-funnel"></i>
            </button>
            <button type="button" id="button-export-all" data-bs-toggle="tooltip" title="{{ $lang->button_export_all }}" class="btn btn-info">
                <i class="bi bi-download"></i>
            </button>
            <a href="{{ route('lang.ocadmin.system.schema.create') }}" data-bs-toggle="tooltip" title="{{ $lang->button_add }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
            </a>
        </div>

        <div class="row">
            {{-- 篩選區塊 --}}
            <div id="filter-schema" class="col-lg-3 col-md-12 order-lg-last d-none d-lg-block mb-3">
                <div class="card">
                    <div class="card-header"><i class="bi bi-funnel"></i> {{ $lang->text_filter }}</div>
                    <div class="card-body">
                        <form id="form-filter">
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_table_name }}</label>
                                <input type="text" name="filter_name" value="{{ request('filter_name') }}" placeholder="{{ $lang->placeholder_search_table }}" id="input-filter-name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ $lang->column_status }}</label>
                                <select name="filter_status" id="input-filter-status" class="form-select">
                                    <option value="">-- {{ $lang->text_all }} --</option>
                                    <option value="synced" {{ request('filter_status') === 'synced' ? 'selected' : '' }}>{{ $lang->text_synced }}</option>
                                    <option value="diff" {{ request('filter_status') === 'diff' ? 'selected' : '' }}>{{ $lang->text_diff }}</option>
                                    <option value="db_only" {{ request('filter_status') === 'db_only' ? 'selected' : '' }}>{{ $lang->text_db_only }}</option>
                                    <option value="schema_only" {{ request('filter_status') === 'schema_only' ? 'selected' : '' }}>{{ $lang->text_schema_only }}</option>
                                </select>
                            </div>
                            <div class="text-end">
                                <button type="button" id="button-clear" class="btn btn-light"><i class="bi bi-eraser"></i> {{ $lang->button_clear }}</button>
                                <button type="button" id="button-filter" class="btn btn-light"><i class="bi bi-funnel"></i> {{ $lang->button_filter }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 列表區塊 --}}
            <div class="col-lg-9 col-md-12">
                <div class="card">
                    <div class="card-header"><i class="bi bi-list-ul"></i> {{ $lang->text_list }}</div>
                    <div id="schema-list" class="card-body">
                        {!! $list !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 差異比對 Modal --}}
<div class="modal fade" id="modal-diff" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $lang->text_diff_preview }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="diff-content">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $lang->button_close }}</button>
                <button type="button" class="btn btn-warning" id="button-sync-confirm" style="display:none;">
                    <i class="bi bi-arrow-repeat"></i> {{ $lang->button_sync }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    // 篩選
    $('#button-filter').on('click', function() {
        var url = '{{ route('lang.ocadmin.system.schema.list') }}?';
        var params = [];

        var v = $('#input-filter-name').val();
        if (v) params.push('filter_name=' + encodeURIComponent(v));

        v = $('#input-filter-status').val();
        if (v) params.push('filter_status=' + encodeURIComponent(v));

        url += params.join('&');
        window.history.pushState({}, null, url.replace('/list?', '?'));
        $('#schema-list').load(url);
    });

    // 清除
    $('#button-clear').on('click', function() {
        $('#form-filter').find('input[type="text"]').val('');
        $('#form-filter').find('select').each(function() { $(this).prop('selectedIndex', 0); });
        var url = '{{ route('lang.ocadmin.system.schema.list') }}';
        window.history.pushState({}, null, '{{ route('lang.ocadmin.system.schema.index') }}');
        $('#schema-list').load(url);
    });

    // 匯出全部
    $('#button-export-all').on('click', function() {
        if (!confirm('{{ $lang->text_confirm_export_all }}')) return;

        $.ajax({
            url: '{{ route('lang.ocadmin.system.schema.export-all') }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            beforeSend: function() {
                $('#button-export-all').prop('disabled', true);
            },
            success: function(json) {
                if (json.success) {
                    alert(json.message);
                    $('#schema-list').load('{{ route('lang.ocadmin.system.schema.list') }}');
                } else {
                    alert(json.message || '匯出失敗');
                }
            },
            error: function(xhr) {
                alert('匯出失敗：' + (xhr.responseJSON?.message || xhr.statusText));
            },
            complete: function() {
                $('#button-export-all').prop('disabled', false);
            }
        });
    });

    // 差異比對
    var currentDiffTable = null;
    $(document).on('click', '.btn-diff', function() {
        currentDiffTable = $(this).data('table');
        var url = $(this).data('url');

        $('#diff-content').html('<div class="text-center"><i class="bi bi-arrow-repeat spinning"></i> 載入中...</div>');
        $('#button-sync-confirm').hide();
        $('#modal-diff').modal('show');

        $.ajax({
            url: url,
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
            html = '<div class="alert alert-success"><i class="bi bi-check-circle"></i> {{ $lang->text_no_changes }}</div>';
        } else {
            // 變更清單
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

                if (change.diffs) {
                    detail = change.diffs.join(', ');
                } else if (change.definition) {
                    detail = change.definition;
                }

                html += '<tr><td>' + label + '</td><td>' + (change.column || '-') + '</td><td>' + detail + '</td></tr>';
            });

            html += '</tbody></table>';

            // SQL 預覽
            if (sqls.length > 0) {
                html += '<h6 class="mt-3">{{ $lang->text_sql_preview }}：</h6>';
                html += '<pre class="bg-dark text-light p-3 rounded" style="font-size: 12px; max-height: 300px; overflow-y: auto;">';
                sqls.forEach(function(sql) {
                    html += escapeHtml(sql) + ';\n\n';
                });
                html += '</pre>';

                $('#button-sync-confirm').show();
            }
        }

        $('#diff-content').html(html);
    }

    // 執行同步
    $('#button-sync-confirm').on('click', function() {
        if (!currentDiffTable) return;
        if (!confirm('{{ $lang->text_confirm_sync }}')) return;

        var syncUrl = '{{ route('lang.ocadmin.system.schema.sync', '__TABLE__') }}'.replace('__TABLE__', currentDiffTable);

        $.ajax({
            url: syncUrl,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            beforeSend: function() {
                $('#button-sync-confirm').prop('disabled', true).html('<i class="bi bi-arrow-repeat spinning"></i> 同步中...');
            },
            success: function(json) {
                if (json.success) {
                    alert(json.message);
                    $('#modal-diff').modal('hide');
                    $('#schema-list').load('{{ route('lang.ocadmin.system.schema.list') }}');
                } else {
                    alert(json.message || '同步失敗');
                }
            },
            error: function(xhr) {
                alert('同步失敗：' + (xhr.responseJSON?.message || xhr.statusText));
            },
            complete: function() {
                $('#button-sync-confirm').prop('disabled', false).html('<i class="bi bi-arrow-repeat"></i> {{ $lang->button_sync }}');
            }
        });
    });

    // 單表匯出
    $(document).on('click', '.btn-export', function() {
        var btn = $(this);
        var url = btn.data('url');

        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            beforeSend: function() {
                btn.prop('disabled', true);
            },
            success: function(json) {
                if (json.success) {
                    alert(json.message);
                    $('#schema-list').load('{{ route('lang.ocadmin.system.schema.list') }}');
                }
            },
            error: function(xhr) {
                alert('匯出失敗：' + (xhr.responseJSON?.message || xhr.statusText));
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
});
</script>
@endsection
