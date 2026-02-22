<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th>
                    <a href="{{ $sort_name }}" @class([request('order', 'asc') => request('sort') === 'name'])>
                        {{ $lang->column_name }}
                    </a>
                </th>
                <th>{{ $lang->column_code }}</th>
                <th class="text-center">{{ $lang->column_levels_count }}</th>
                <th class="text-center">{{ $lang->column_is_active }}</th>
                <th class="text-center">
                    <a href="{{ $sort_sort_order }}" @class([request('order', 'asc') => request('sort') === 'sort_order'])>
                        {{ $lang->column_sort_order }}
                    </a>
                </th>
                <th class="text-end">{{ $lang->column_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groups as $group)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $group->id }}" class="form-check-input">
                </td>
                <td>{{ $group->name }}</td>
                <td><code>{{ $group->code }}</code></td>
                <td class="text-center">
                    <span class="badge bg-info">{{ $group->levels_count }}</span>
                </td>
                <td class="text-center">
                    @if($group->is_active)
                    <span class="badge bg-success">{{ $lang->text_active }}</span>
                    @else
                    <span class="badge bg-secondary">{{ $lang->text_inactive }}</span>
                    @endif
                </td>
                <td class="text-center">{{ $group->sort_order }}</td>
                <td class="text-end">
                    <a href="{{ str_replace('__ID__', $group->id, $url_edit) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
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
    <div class="col-sm-6 text-end">顯示 {{ $groups->firstItem() ?? 0 }} 到 {{ $groups->lastItem() ?? 0 }}，共 {{ $groups->total() }} 筆</div>
</div>
