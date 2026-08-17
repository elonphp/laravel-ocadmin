@extends('ocadmin::layouts.app')

@section('title', $lang->text_edit . '：' . $table_name)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" id="button-preview" class="btn btn-info">
                    <i class="fa-solid fa-eye"></i> {{ $lang->button_preview_sql }}
                </button>
                <button type="button" id="button-apply" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> {{ $lang->button_apply }}
                </button>
                <a href="{{ $back_url }}" data-bs-toggle="tooltip" title="{{ $lang->button_back }}" class="btn btn-secondary">
                    <i class="fa-solid fa-reply"></i>
                </a>
            </div>
            <h1>{{ $lang->text_edit }}：<code>{{ $table_name }}</code></h1>
            @if(!empty($table_comment))
            <div class="text-muted small">{{ $table_comment }}</div>
            @endif
        </div>
    </div>

    <div class="container-fluid">
        <form id="form-schema">
            @csrf
            <input type="hidden" name="_table" value="{{ $table_name }}">

            <div class="card">
                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th style="width: 140px;">{{ $lang->column_original_name }}</th>
                                    <th style="width: 160px;" class="required">{{ $lang->column_name }}</th>
                                    <th style="width: 130px;" class="required">{{ $lang->column_type }}</th>
                                    <th style="width: 90px;">{{ $lang->column_length }}</th>
                                    <th class="text-center" style="width: 70px;">{{ $lang->column_unsigned }}</th>
                                    <th class="text-center" style="width: 60px;">{{ $lang->column_nullable }}</th>
                                    <th style="width: 120px;">{{ $lang->column_default }}</th>
                                    <th class="text-center" style="width: 60px;">{{ $lang->column_auto_inc }}</th>
                                    <th class="text-center" style="width: 50px;">{{ $lang->column_primary }}</th>
                                    <th>{{ $lang->column_note }}</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="column-rows">
                                @foreach($columns as $i => $col)
                                    @include('ocadmin::system.schema._row', [
                                        'idx'            => $i,
                                        'originalName'   => $col['name'],
                                        'name'           => $col['name'],
                                        'type'           => $col['type'],
                                        'length'         => $col['length'],
                                        'unsigned'       => $col['unsigned'],
                                        'nullable'       => $col['nullable'],
                                        'default'        => $col['default'],
                                        'auto_increment' => $col['auto_increment'],
                                        'primary'        => $col['primary'],
                                        'comment'        => $col['comment'],
                                        'supportedTypes' => $supportedTypes,
                                        'lang'           => $lang,
                                    ])
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="11">
                                        <button type="button" id="button-add-row" class="btn btn-outline-success btn-sm">
                                            <i class="fa-solid fa-plus"></i> {{ $lang->button_add_column }}
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- SQL Preview Modal --}}
<div class="modal fade" id="modal-preview" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-eye"></i> {{ $lang->text_sql_preview }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="preview-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $lang->button_close }}</button>
                <button type="button" class="btn btn-primary" id="button-modal-apply" style="display:none;">
                    <i class="fa-solid fa-save"></i> {{ $lang->button_confirm }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 列模板（新增時複製用） --}}
<template id="row-template">
    @include('ocadmin::system.schema._row', [
        'idx'            => '__INDEX__',
        'originalName'   => '',
        'name'           => '',
        'type'           => 'varchar',
        'length'         => '',
        'unsigned'       => false,
        'nullable'       => true,
        'default'        => '',
        'auto_increment' => false,
        'primary'        => false,
        'comment'        => '',
        'supportedTypes' => $supportedTypes,
        'lang'           => $lang,
    ])
</template>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let rowIndex = {{ count($columns) }};
    const previewUrl = @json($preview_url);
    const updateUrl  = @json($update_url);

    // reload 後顯示上一次的 flash 訊息（交給 common.js 的 handleJsonResponse 處理）
    (function() {
        const flash = sessionStorage.getItem('schema_flash');
        if (flash) {
            sessionStorage.removeItem('schema_flash');
            try {
                handleJsonResponse(JSON.parse(flash));
            } catch (e) {}
        }
    })();

    // 新增列
    $('#button-add-row').on('click', function() {
        const tpl = $('#row-template').html().replaceAll('__INDEX__', rowIndex);
        $('#column-rows').append(tpl);
        rowIndex++;
    });

    // 移除/還原列
    $(document).on('click', '.btn-remove-row', function() {
        const row = $(this).closest('tr');
        const originalName = row.find('.input-original-name').val();

        if (originalName === '') {
            // 新增列 → 直接移除 DOM
            row.remove();
            return;
        }

        // 既有列 → toggle delete 狀態
        const deleteInput = row.find('.input-delete');
        if (deleteInput.val() === '1') {
            deleteInput.val('0');
            row.removeClass('row-deleted');
            row.find('input, select').not('.input-delete').not('.input-original-name').prop('disabled', false);
            $(this).html('<i class="fa-solid fa-xmark"></i>').removeClass('btn-warning').addClass('btn-danger');
        } else {
            deleteInput.val('1');
            row.addClass('row-deleted');
            row.find('input, select').not('.input-delete').not('.input-original-name').prop('disabled', true);
            $(this).html('<i class="fa-solid fa-rotate-left"></i>').removeClass('btn-danger').addClass('btn-warning');
        }
    });

    // 預覽 SQL（僅預覽）
    $('#button-preview').on('click', function() {
        loadPreview(false);
    });

    // 儲存 → 先顯示預覽 + 啟用 modal 的「確認執行」
    $('#button-apply').on('click', function() {
        loadPreview(true);
    });

    function loadPreview(allowConfirm) {
        const formData = $('#form-schema').serialize();

        $.ajax({
            url: previewUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(json) {
                renderPreview(json.sqls || []);
                if (allowConfirm && json.sqls && json.sqls.length > 0) {
                    $('#button-modal-apply').show();
                } else {
                    $('#button-modal-apply').hide();
                }
                $('#modal-preview').modal('show');
            },
            error: function(xhr) {
                handleJsonResponse(xhr.responseJSON || { success: false, message: 'Preview failed' });
            }
        });
    }

    function renderPreview(sqls) {
        if (!sqls || sqls.length === 0) {
            $('#preview-content').html('<div class="alert alert-secondary mb-0">{{ $lang->text_no_changes }}</div>');
            return;
        }

        let html = '<pre class="bg-dark text-light p-3 rounded mb-0" style="font-size: 13px; max-height: 400px; overflow: auto;">';
        sqls.forEach(function(sql) {
            html += escapeHtml(sql) + ';\n\n';
        });
        html += '</pre>';
        $('#preview-content').html(html);
    }

    // Modal 內的「確認執行」
    $('#button-modal-apply').on('click', function() {
        if (!confirm('{{ $lang->text_confirm_apply }}')) return;

        const btn = $(this);
        const formData = $('#form-schema').serialize();

        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

        $.ajax({
            url: updateUrl,
            type: 'POST',
            data: formData + '&_method=PUT',
            dataType: 'json',
            success: function(json) {
                if (json.success) {
                    // 存 flash，reload 後交給 common.js 的 handleJsonResponse 顯示
                    sessionStorage.setItem('schema_flash', JSON.stringify(json));
                    $('#modal-preview').modal('hide');
                    window.location.reload();
                } else {
                    handleJsonResponse(json);
                    btn.prop('disabled', false).html('<i class="fa-solid fa-save"></i> {{ $lang->button_confirm }}');
                }
            },
            error: function(xhr) {
                handleJsonResponse(xhr.responseJSON || { success: false, message: 'Apply failed' });
                btn.prop('disabled', false).html('<i class="fa-solid fa-save"></i> {{ $lang->button_confirm }}');
            }
        });
    });

    function escapeHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
});
</script>

<style>
.row-deleted { opacity: 0.5; background-color: #ffe3e3 !important; }
.row-deleted .input-name { text-decoration: line-through; }
#column-rows td { padding: 0.25rem; vertical-align: middle; }
#column-rows .form-control, #column-rows .form-select { font-size: 13px; }
.input-original-name { background-color: #f8f9fa; color: #6c757d; font-family: monospace; }
</style>
@endsection
