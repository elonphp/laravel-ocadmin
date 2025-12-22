@extends('ocadmin::layouts.app')

@section('title', '排程的程式')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <h1>排程的程式</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-clock"></i> 日誌相關排程任務</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>指令名稱</th>
                                <th>說明</th>
                                <th>排程時間</th>
                                <th>Cron 表達式</th>
                                <th style="width: 120px;">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedulerInfo as $task)
                            <tr>
                                <td><code>{{ $task['name'] }}</code></td>
                                <td>{{ $task['description'] }}</td>
                                <td>{{ $task['schedule'] }}</td>
                                <td><code>{{ $task['cron'] }}</code></td>
                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary btn-run-command"
                                            data-command="{{ $task['name'] }}"
                                            data-bs-toggle="tooltip"
                                            title="手動執行">
                                        <i class="fa-solid fa-play"></i> 執行
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header"><i class="fa-solid fa-info-circle"></i> 排程說明</div>
            <div class="card-body">
                <h5>1. 歸檔上月日誌 (logs:archive)</h5>
                <ul>
                    <li>每月 1 日 03:00 自動執行</li>
                    <li>將上個月的資料庫日誌匯出為每日一個檔案</li>
                    <li>壓縮成一個 ZIP 檔案 (例如: logs_2024-11.zip)</li>
                    <li>壓縮檔存放於 <code>storage/logs/archived/</code></li>
                </ul>

                <h5>2. 清理資料庫日誌 (logs:cleanup)</h5>
                <ul>
                    <li>每月 1 日 04:00 自動執行</li>
                    <li>刪除超過三個月的資料庫日誌記錄</li>
                    <li>保留近三個月的資料供即時查詢</li>
                </ul>

                <h5>3. 刪除檔案日誌 (app:delete-file-logs)</h5>
                <ul>
                    <li>每日 02:00 自動執行</li>
                    <li>刪除 storage/logs/app/ 目錄下超過 90 天的日誌檔案</li>
                    <li>壓縮檔不會被刪除</li>
                </ul>

                <div class="alert alert-info mt-4">
                    <i class="fa-solid fa-lightbulb"></i>
                    <strong>提示：</strong>
                    若要啟用自動排程，請確保伺服器已設定 Laravel Scheduler：
                    <pre class="mb-0 mt-2">* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1</pre>
                </div>
            </div>
        </div>

        {{-- 執行結果模態框 --}}
        <div class="modal fade" id="commandResultModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-terminal"></i> 執行結果</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="command-result-content">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">執行中...</span>
                                </div>
                                <p class="mt-2">正在執行指令，請稍候...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    var modal = new bootstrap.Modal(document.getElementById('commandResultModal'));

    $('.btn-run-command').on('click', function() {
        var command = $(this).data('command');
        var $btn = $(this);

        // 顯示模態框
        $('#command-result-content').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">執行中...</span>
                </div>
                <p class="mt-2">正在執行 <code>${command}</code>，請稍候...</p>
            </div>
        `);
        modal.show();

        // 禁用按鈕
        $btn.prop('disabled', true);

        // 發送請求
        $.ajax({
            url: "{{ route('lang.ocadmin.system.log.scheduler.run', ['command' => '__COMMAND__']) }}".replace('__COMMAND__', command),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#command-result-content').html(`
                        <div class="alert alert-success">
                            <i class="fa-solid fa-check-circle"></i> ${response.message}
                        </div>
                        <h6>輸出：</h6>
                        <pre class="bg-light p-3 rounded">${response.output || '(無輸出)'}</pre>
                    `);
                } else {
                    $('#command-result-content').html(`
                        <div class="alert alert-danger">
                            <i class="fa-solid fa-times-circle"></i> ${response.message}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                var message = '執行失敗';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $('#command-result-content').html(`
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-times-circle"></i> ${message}
                    </div>
                `);
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
