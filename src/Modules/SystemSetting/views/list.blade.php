<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <td style="width: 1px;"><input type="checkbox" id="select-all"></td>
                <td>
                    <a href="{{ $action }}&sort=code&order={{ request('sort') === 'code' && request('order') === 'asc' ? 'desc' : 'asc' }}">
                        {{ __('system-setting::setting.code') }}
                        @if(request('sort') === 'code')
                            <i class="fa-solid fa-sort-{{ request('order') === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </td>
                <td>
                    <a href="{{ $action }}&sort=group&order={{ request('sort') === 'group' && request('order') === 'asc' ? 'desc' : 'asc' }}">
                        {{ __('system-setting::setting.group') }}
                        @if(request('sort') === 'group')
                            <i class="fa-solid fa-sort-{{ request('order') === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </td>
                <td>{{ __('system-setting::setting.content') }}</td>
                <td>{{ __('system-setting::setting.type') }}</td>
                <td class="text-end" style="width: 80px;">{{ __('ocadmin::common.action') }}</td>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td><input type="checkbox" name="selected[]" value="{{ $item->id }}"></td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->group ?: '-' }}</td>
                <td>
                    <span title="{{ $item->content }}">
                        {{ Str::limit($item->content, 50) }}
                    </span>
                </td>
                <td>
                    <span class="badge bg-secondary">{{ $item->type->label() }}</span>
                </td>
                <td class="text-end">
                    <a href="{{ ocadmin_route('settings.edit', $item->id) }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-pencil"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    {{ __('ocadmin::common.no_data') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($items->hasPages())
<div class="row">
    <div class="col-sm-6">{{ $items->appends(request()->query())->links() }}</div>
    <div class="col-sm-6 text-end text-muted">
        {{ __('ocadmin::common.showing', ['from' => $items->firstItem() ?? 0, 'to' => $items->lastItem() ?? 0, 'total' => $items->total()]) }}
    </div>
</div>
@endif
