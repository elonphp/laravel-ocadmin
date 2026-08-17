<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th><a href="{{ $sort_device_name }}" @class([$order => $sort === 'device_name'])>{{ $lang->column_device_name }}</a></th>
                <th><a href="{{ $sort_ip_address }}" @class([$order => $sort === 'ip_address'])>{{ $lang->column_ip_address }}</a></th>
                <th><a href="{{ $sort_last_active_at }}" @class([$order => $sort === 'last_active_at'])>{{ $lang->column_last_active_at }}</a></th>
                <th><a href="{{ $sort_created_at }}" @class([$order => $sort === 'created_at'])>{{ $lang->column_created_at }}</a></th>
                <th class="text-center">{{ $lang->column_status }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devices as $row)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $row->id }}" class="form-check-input" @if($row->is_current) disabled @endif>
                </td>
                <td>{{ $row->device_name }}</td>
                <td>{{ $row->ip_address }}</td>
                <td>{{ $row->last_active_at ?? '-' }}</td>
                <td>{{ $row->created_at }}</td>
                <td class="text-center">
                    @if($row->is_current)
                        <span class="badge bg-success">{{ $lang->text_current_device }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination ?? '' !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $devices->firstItem() ?? 0, $devices->lastItem() ?? 0, $devices->total()) !!}</div>
</div>
