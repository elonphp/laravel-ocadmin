<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <td class="text-start" style="width: 160px;">時間</td>
                <td class="text-start" style="width: 80px;">Method</td>
                <td class="text-start" style="width: 80px;">狀態</td>
                <td class="text-start">URL</td>
                <td class="text-start" style="width: 120px;">Client IP</td>
                <td class="text-start">備註</td>
                <td class="text-center" style="width: 80px;">操作</td>
            </tr>
        </thead>
        <tbody>
            @if($logs->count() > 0)
                @foreach($logs as $log)
                <tr>
                    <td class="text-start">{{ $log->created_at }}</td>
                    <td class="text-start">
                        @php
                            $methodClass = match($log->method ?? '') {
                                'GET' => 'badge bg-info',
                                'POST' => 'badge bg-success',
                                'PUT', 'PATCH' => 'badge bg-warning',
                                'DELETE' => 'badge bg-danger',
                                default => 'badge bg-secondary'
                            };
                        @endphp
                        <span class="{{ $methodClass }}">{{ $log->method ?? '' }}</span>
                    </td>
                    <td class="text-start">
                        @php
                            $status = $log->status ?? '';
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
                    <td class="text-start" title="{{ $log->url ?? '' }}">
                        <small>{{ Str::limit($log->url ?? '', 60) }}</small>
                    </td>
                    <td class="text-start">{{ $log->client_ip ?? '' }}</td>
                    <td class="text-start" title="{{ $log->note ?? '' }}">
                        <small>{{ Str::limit($log->note ?? '', 100) }}</small>
                    </td>
                    <td class="text-center">
                        <button
                            type="button"
                            class="btn btn-sm btn-primary view-detail"
                            data-id="{{ $log->id }}"
                            data-bs-toggle="tooltip"
                            title="查看詳情">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="text-center">沒有找到日誌記錄</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="row">
    <div class="col-sm-6 text-start">
        顯示 {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }}，共 {{ $logs->total() }} 筆
    </div>
    <div class="col-sm-6 text-end">
        @if($logs->hasPages())
            <ul class="pagination justify-content-end mb-0">
                {{-- Previous Page Link --}}
                @if($logs->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">上一頁</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $logs->previousPageUrl() }}&filter_date={{ $filters['date'] ?? '' }}&filter_method={{ $filters['method'] ?? '' }}&filter_status={{ $filters['status'] ?? '' }}&filter_keyword={{ $filters['keyword'] ?? '' }}">上一頁</a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach(range(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page)
                    <li class="page-item {{ $page == $logs->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $logs->url($page) }}&filter_date={{ $filters['date'] ?? '' }}&filter_method={{ $filters['method'] ?? '' }}&filter_status={{ $filters['status'] ?? '' }}&filter_keyword={{ $filters['keyword'] ?? '' }}">{{ $page }}</a>
                    </li>
                @endforeach

                {{-- Next Page Link --}}
                @if($logs->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $logs->nextPageUrl() }}&filter_date={{ $filters['date'] ?? '' }}&filter_method={{ $filters['method'] ?? '' }}&filter_status={{ $filters['status'] ?? '' }}&filter_keyword={{ $filters['keyword'] ?? '' }}">下一頁</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">下一頁</span></li>
                @endif
            </ul>
        @endif
    </div>
</div>
