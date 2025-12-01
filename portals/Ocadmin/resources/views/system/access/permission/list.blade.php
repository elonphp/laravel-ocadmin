<form id="form-permission" method="post" data-oc-toggle="ajax" data-oc-load="{{ $action }}" data-oc-target="#permission-list">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th class="text-center" style="width: 1px;">
                        <input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', $(this).prop('checked'));" class="form-check-input">
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.access.permission.list', array_merge(request()->all(), ['sort' => 'name', 'order' => request('order') === 'asc' && request('sort') === 'name' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'name'])>
                            {{ $lang->column_name }}
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.access.permission.list', array_merge(request()->all(), ['sort' => 'title', 'order' => request('order') === 'asc' && request('sort') === 'title' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'title'])>
                            {{ $lang->column_title }}
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.access.permission.list', array_merge(request()->all(), ['sort' => 'type', 'order' => request('order') === 'asc' && request('sort') === 'type' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort') === 'type'])>
                            {{ $lang->column_type }}
                        </a>
                    </th>
                    <th>{{ $lang->column_parent }}</th>
                    <th>
                        <a href="{{ route('lang.ocadmin.system.access.permission.list', array_merge(request()->all(), ['sort' => 'sort_order', 'order' => request('order') === 'asc' && request('sort', 'sort_order') === 'sort_order' ? 'desc' : 'asc'])) }}" @class([request('order', 'asc') => request('sort', 'sort_order') === 'sort_order'])>
                            {{ $lang->column_sort_order }}
                        </a>
                    </th>
                    <th class="text-end">{{ $lang->column_action }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $permission)
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="selected[]" value="{{ $permission->id }}" class="form-check-input">
                    </td>
                    <td>{{ $permission->name }}</td>
                    <td>{{ $permission->title }}</td>
                    <td>
                        @if($permission->type === 'menu')
                            <span class="badge bg-primary">{{ $lang->text_type_menu }}</span>
                        @else
                            <span class="badge bg-secondary">{{ $lang->text_type_action }}</span>
                        @endif
                    </td>
                    <td>
                        @if($permission->parent_id)
                            @php
                                $parent = $parentOptions->firstWhere('id', $permission->parent_id);
                            @endphp
                            {{ $parent ? ($parent->title ?: $parent->name) : '-' }}
                        @else
                            <span class="text-muted">{{ $lang->text_top_level }}</span>
                        @endif
                    </td>
                    <td>{{ $permission->sort_order }}</td>
                    <td class="text-end">
                        <a href="{{ route('lang.ocadmin.system.access.permission.edit', $permission) }}" data-bs-toggle="tooltip" title="{{ $lang->button_edit }}" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">{{ $lang->text_no_results }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-sm-6 text-start">{{ $permissions->links('ocadmin::pagination.default') }}</div>
        <div class="col-sm-6 text-end">{{ sprintf($lang->text_pagination, $permissions->firstItem() ?? 0, $permissions->lastItem() ?? 0, $permissions->total()) }}</div>
    </div>
</form>
