@extends('ocadmin::layouts.app')

@section('title', '查看歷史日誌')

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" onclick="window.close();" class="btn btn-light"><i class="fa-solid fa-times"></i> 關閉</button>
            </div>
            <h1>查看歷史日誌</h1>
            @include('ocadmin::layouts.partials.breadcrumb')
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-file-lines"></i>
                {{ $filename }} / {{ $logFile }}
                <span class="badge bg-secondary ms-2">{{ count($logs) }} 筆</span>
            </div>
            <div class="card-body">
                @if(count($logs) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 160px;">時間</th>
                                    <th style="width: 80px;">Method</th>
                                    <th style="width: 80px;">狀態</th>
                                    <th>URL</th>
                                    <th style="width: 120px;">Client IP</th>
                                    <th>備註</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td class="text-start">{{ $log['timestamp'] ?? '' }}</td>
                                    <td class="text-start">
                                        @php
                                            $methodClass = match($log['method'] ?? '') {
                                                'GET' => 'badge bg-info',
                                                'POST' => 'badge bg-success',
                                                'PUT', 'PATCH' => 'badge bg-warning',
                                                'DELETE' => 'badge bg-danger',
                                                default => 'badge bg-secondary'
                                            };
                                        @endphp
                                        <span class="{{ $methodClass }}">{{ $log['method'] ?? '' }}</span>
                                    </td>
                                    <td class="text-start">
                                        @php
                                            $status = $log['status'] ?? '';
                                            $statusClass = match($status) {
                                                'success' => 'badge bg-success',
                                                'error' => 'badge bg-danger',
                                                'warning' => 'badge bg-warning',
                                                '' => '',
                                                default => 'badge bg-secondary'
                                            };
                                            $statusText = $status ?: '-';
                                        @endphp
                                        @if($statusClass)
                                            <span class="{{ $statusClass }}">{{ $statusText }}</span>
                                        @else
                                            <span class="text-muted">{{ $statusText }}</span>
                                        @endif
                                    </td>
                                    <td class="text-start" title="{{ $log['url'] ?? '' }}">
                                        <small>{{ Str::limit($log['url'] ?? '', 60) }}</small>
                                    </td>
                                    <td class="text-start">{{ $log['client_ip'] ?? '' }}</td>
                                    <td class="text-start" title="{{ $log['note'] ?? '' }}">
                                        <small>{{ Str::limit($log['note'] ?? '', 80) }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                        <p>此檔案沒有日誌記錄</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
