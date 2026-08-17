@extends('ocadmin::layouts.app')

@section('title', $lang->heading_title)

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <h1>{{ $lang->heading_title }}</h1>
        </div>
    </div>

    <div class="container-fluid">
        <input type="hidden" id="tr-token" value="{{ csrf_token() }}">

        <div class="alert alert-secondary">
            <i class="fa-solid fa-circle-info"></i> {{ $lang->text_intro }}
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                <span class="fw-bold"><i class="fa-solid fa-database"></i> {{ $lang->text_list }}</span>
                <span class="ms-2 small text-muted" id="tr-summary"></span>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-info" id="tr-preview">
                        <i class="fa-solid fa-eye"></i> {{ $lang->button_preview }}
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="tr-run">
                        <i class="fa-solid fa-play"></i> {{ $lang->button_run }}
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:8rem">{{ $lang->column_status }}</th>
                                <th style="width:9rem">{{ $lang->column_version }}</th>
                                <th>{{ $lang->column_description }}</th>
                                <th style="width:14rem" class="d-none d-lg-table-cell">{{ $lang->column_file }}</th>
                                <th style="width:10rem">{{ $lang->column_executed_at }}</th>
                            </tr>
                        </thead>
                        <tbody id="tr-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3 d-none" id="tr-output-card">
            <div class="card-header"><i class="fa-solid fa-terminal"></i> {{ $lang->text_output }}</div>
            <div class="card-body">
                <pre id="tr-output" class="mb-0 p-3 rounded" style="background:#1e1e1e;color:#e0e0e0;max-height:28rem;overflow:auto;white-space:pre-wrap;word-break:break-word;font-size:.85rem"></pre>
            </div>
        </div>

        <div class="alert alert-info mb-0">
            <i class="fa-solid fa-info-circle"></i> {{ $lang->text_help }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const previewUrl = @json($preview_url);
    const runUrl     = @json($run_url);
    const token      = document.getElementById('tr-token').value;

    const LANG = {
        success: @json($lang->status_success),
        failed:  @json($lang->status_failed),
        pending: @json($lang->status_pending),
        pendingLabel: @json($lang->text_pending),
        failedLabel:  @json($lang->text_failed),
        totalLabel:   @json($lang->text_total),
        unit:         @json($lang->unit_item),
        running:      @json($lang->text_running),
        previewing:   @json($lang->text_previewing),
        confirmRun:   @json($lang->text_confirm_run),
        successRun:   @json($lang->text_success_run),
        errorRun:     @json($lang->error_run),
    };
    const BADGE = { success: 'bg-success', failed: 'bg-danger', pending: 'bg-secondary' };

    const tbody   = document.getElementById('tr-tbody');
    const summary = document.getElementById('tr-summary');
    const outCard = document.getElementById('tr-output-card');
    const outPre  = document.getElementById('tr-output');
    const btnPrev = document.getElementById('tr-preview');
    const btnRun  = document.getElementById('tr-run');

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, c => (
            { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
        ));
    }

    function renderRows(rows) {
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">—</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (r) {
            const label = LANG[r.status] || r.status;
            const cls   = BADGE[r.status] || 'bg-secondary';
            const err   = r.error
                ? '<div class="small text-danger mt-1"><i class="fa-solid fa-triangle-exclamation"></i> ' + esc(r.error) + '</div>'
                : '';
            return '<tr>'
                + '<td><span class="badge ' + cls + '">' + esc(label) + '</span></td>'
                + '<td><code>' + esc(r.version) + '</code></td>'
                + '<td>' + esc(r.description) + err + '</td>'
                + '<td class="d-none d-lg-table-cell small text-muted">' + esc(r.file) + '</td>'
                + '<td class="small">' + (r.executed_at ? esc(r.executed_at) : '—') + '</td>'
                + '</tr>';
        }).join('');
    }

    function renderSummary(pending, failed, total) {
        let s = LANG.totalLabel + ' ' + total + ' ' + LANG.unit
              + '・' + LANG.pendingLabel + ' ' + pending + ' ' + LANG.unit;
        if (failed > 0) s += '・' + LANG.failedLabel + ' ' + failed + ' ' + LANG.unit;
        summary.textContent = s;
        btnRun.disabled = pending === 0 && failed === 0;
    }

    function applyState(rows, pending, failed) {
        renderRows(rows);
        renderSummary(pending, failed, rows.length);
    }

    function showOutput(text) {
        outCard.classList.remove('d-none');
        outPre.textContent = text || '';
        outPre.scrollTop = 0;
    }

    async function call(url, busyLabel) {
        btnPrev.disabled = true; btnRun.disabled = true;
        showOutput(busyLabel);
        try {
            const r = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
            });
            const j = await r.json();
            showOutput(j.output || '(no output)');
            // applyState → renderSummary 依 pending/failed 決定 btnRun 是否再啟用
            applyState(j.rows || [], j.pending || 0, j.failed || 0);
            btnPrev.disabled = false;
            return j;
        } catch (e) {
            // fetch / JSON 解析失敗：兩顆鈕都解鎖讓使用者可重試
            showOutput(String(e));
            btnPrev.disabled = false;
            btnRun.disabled = false;
            return { success: false };
        }
    }

    btnPrev.addEventListener('click', async function () {
        const j = await call(previewUrl, LANG.previewing);
        if (window.toastr && j.success) toastr.info('OK');
    });

    btnRun.addEventListener('click', async function () {
        if (!window.confirm(LANG.confirmRun)) return;
        const j = await call(runUrl, LANG.running);
        if (!window.toastr) return;
        if (j.success) toastr.success(LANG.successRun);
        else toastr.error(LANG.errorRun);
    });

    // 初始渲染
    applyState(@json($rows), {{ $pending }}, {{ $failed }});
})();
</script>
@endsection
