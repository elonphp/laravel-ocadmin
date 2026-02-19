<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th>{{ $lang->column_portal }}</th>
                <th>
                    <a href="{{ $sort_method }}" @class([$order => $sort === 'method'])>{{ $lang->column_method }}</a>
                </th>
                <th>{{ $lang->column_url }}</th>
                <th>
                    <a href="{{ $sort_status_code }}" @class([$order => $sort === 'status_code'])>{{ $lang->column_status_code }}</a>
                </th>
                <th>{{ $lang->column_status }}</th>
                <th>
                    <a href="{{ $sort_created_at }}" @class([$order => $sort === 'created_at'])>{{ $lang->column_created_at }}</a>
                </th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->portal ?: '-' }}</td>
                <td>{{ $log->method }}</td>
                <td title="{{ $log->url }}">{{ Str::limit($log->url, 60) }}</td>
                <td>
                    @if($log->status_code)
                    <span class="badge bg-{{ $log->status_code >= 500 ? 'danger' : ($log->status_code >= 400 ? 'warning' : ($log->status_code >= 300 ? 'info' : 'success')) }}">{{ $log->status_code }}</span>
                    @else
                    -
                    @endif
                </td>
                <td>
                    @if($log->status)
                    <span class="badge bg-{{ match($log->status) { 'success' => 'success', 'warning' => 'warning', 'error' => 'danger', default => 'secondary' } }}">{{ $log->status }}</span>
                    @else
                    -
                    @endif
                </td>
                <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.system.log.form', $log) }}" data-bs-toggle="tooltip" title="{{ $lang->button_view }}" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $logs->firstItem() ?? 0, $logs->lastItem() ?? 0, $logs->total()) !!}</div>
</div>
