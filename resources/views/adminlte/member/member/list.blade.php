<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th>
                    <a href="{{ $sort_username }}" @class([$order => $sort === 'username'])>{{ $lang->column_username }}</a>
                </th>
                <th>
                    <a href="{{ $sort_email }}" @class([$order => $sort === 'email'])>{{ $lang->column_email }}</a>
                </th>
                <th>{{ $lang->column_first_name }}</th>
                <th>{{ $lang->column_last_name }}</th>
                <th class="text-center">
                    <a href="{{ $sort_created_at }}" @class([$order => $sort === 'created_at'])>{{ $lang->column_created_at }}</a>
                </th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $user->id }}" class="form-check-input">
                </td>
                <td>{{ $user->username }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->first_name ?: '-' }}</td>
                <td>{{ $user->last_name ?: '-' }}</td>
                <td class="text-center">{{ $user->created_at?->format('Y-m-d H:i') }}</td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.member.member.edit', $user) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">{{ $lang->text_no_data }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $users->firstItem() ?? 0, $users->lastItem() ?? 0, $users->total()) !!}</div>
</div>
