<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th><a href="{{ $sort_id }}" @class([$order => $sort === 'id'])>ID</a></th>
                <th><a href="{{ $sort_name }}" @class([$order => $sort === 'name'])>{{ $lang->column_name }}</a></th>
                <th>{{ $lang->column_user }}</th>
                <th>{{ $lang->column_abilities }}</th>
                <th><a href="{{ $sort_last_used_at }}" @class([$order => $sort === 'last_used_at'])>{{ $lang->column_last_used_at }}</a></th>
                <th>{{ $lang->column_expires_at }}</th>
                <th class="d-none d-lg-table-cell"><a href="{{ $sort_created_at }}" @class([$order => $sort === 'created_at'])>{{ $lang->column_created_at }}</a></th>
                <th class="text-center">{{ $lang->column_status }}</th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tokens as $row)
            @php
                $isExpired = $row->expires_at && \Carbon\Carbon::parse($row->expires_at)->isPast();
            @endphp
            <tr @if($isExpired) class="table-secondary" @endif>
                <td class="text-center"><input type="checkbox" name="selected[]" value="{{ $row->id }}" class="form-check-input"></td>
                <td>{{ $row->id }}</td>
                <td>{{ $row->name }}</td>
                <td>{{ $row->user_name }}</td>
                <td><small>{{ $row->abilities_display }}</small></td>
                <td>{{ $row->last_used_at ?? '-' }}</td>
                <td>{{ $row->expires_at ?? $lang->text_no_expiry }}</td>
                <td class="d-none d-lg-table-cell">{{ $row->created_at }}</td>
                <td class="text-center">
                    @if($isExpired)
                        <span class="badge bg-secondary">{{ $lang->text_expired }}</span>
                    @else
                        <span class="badge bg-success">{{ $lang->text_active }}</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ $row->edit_url . $urlParams }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination ?? '' !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $tokens->firstItem() ?? 0, $tokens->lastItem() ?? 0, $tokens->total()) !!}</div>
</div>
