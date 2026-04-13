<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 1px;">
                    <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                </th>
                <th>
                    <a href="{{ $sort_display_name }}" @class([$order => $sort === 'id'])>
                        {{ $lang->column_display_name }}
                    </a>
                </th>
                <th>{{ $lang->column_parent }}</th>
                <th>{{ $lang->column_permission_name }}</th>
                <th>{{ $lang->column_icon }}</th>
                <th>
                    <a href="{{ $sort_sort_order }}" @class([$order => $sort === 'sort_order'])>
                        {{ $lang->column_sort_order }}
                    </a>
                </th>
                <th>{{ $lang->column_is_active }}</th>
                <th class="text-end">{{ $lang->text_action }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($menus as $menu)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $menu->id }}" class="form-check-input">
                </td>
                <td>
                    @if($menu->icon)
                    <i class="{{ $menu->icon }}"></i>
                    @endif
                    {{ $menu->display_name }}
                </td>
                <td>{{ $menu->parent?->display_name ?? '-' }}</td>
                <td><code>{{ $menu->permission_name ?? '-' }}</code></td>
                <td><code>{{ $menu->icon ?? '-' }}</code></td>
                <td>{{ $menu->sort_order }}</td>
                <td>
                    @if($menu->is_active)
                    <span class="badge bg-success">{{ $lang->text_enabled }}</span>
                    @else
                    <span class="badge bg-secondary">{{ $lang->text_disabled }}</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('lang.ocadmin.system.menus.edit', $menu) . $urlParams }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">{{ $lang->text_no_results }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-6 text-start">{!! $pagination !!}</div>
    <div class="col-sm-6 text-end">{!! sprintf($lang->text_showing, $menus->firstItem() ?? 0, $menus->lastItem() ?? 0, $menus->total()) !!}</div>
</div>
