<div class="card">
    <div class="card-header">
        <i class="fa-solid fa-list"></i> {{ __('user::user.list') }}
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <td style="width: 1px;" class="text-center">
                            <input type="checkbox" id="select-all">
                        </td>
                        <td class="text-end" style="width: 60px;">
                            <a href="{{ $action }}&sort=id&order={{ request('order') == 'asc' && request('sort') == 'id' ? 'desc' : 'asc' }}">
                                ID
                                @if(request('sort') == 'id')
                                    <i class="fa-solid fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </td>
                        <td>
                            <a href="{{ $action }}&sort=name&order={{ request('order') == 'asc' && request('sort') == 'name' ? 'desc' : 'asc' }}">
                                {{ __('user::user.name') }}
                                @if(request('sort') == 'name')
                                    <i class="fa-solid fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </td>
                        <td>
                            <a href="{{ $action }}&sort=email&order={{ request('order') == 'asc' && request('sort') == 'email' ? 'desc' : 'asc' }}">
                                {{ __('user::user.email') }}
                                @if(request('sort') == 'email')
                                    <i class="fa-solid fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </td>
                        <td>
                            <a href="{{ $action }}&sort=created_at&order={{ request('order') == 'asc' && request('sort') == 'created_at' ? 'desc' : 'asc' }}">
                                {{ __('ocadmin::common.created_at') }}
                                @if(request('sort') == 'created_at')
                                    <i class="fa-solid fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </a>
                        </td>
                        <td style="width: 80px;" class="text-end">{{ __('ocadmin::common.actions') }}</td>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="selected[]" value="{{ $item->id }}">
                        </td>
                        <td class="text-end">{{ $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ ocadmin_route('users.edit', $item->id) }}" class="btn btn-primary btn-sm">
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
    </div>
    @if($items->hasPages())
    <div class="card-footer">
        <div class="row">
            <div class="col-sm-6 text-start">
                {{ __('ocadmin::common.showing', [
                    'from' => $items->firstItem() ?? 0,
                    'to' => $items->lastItem() ?? 0,
                    'total' => $items->total()
                ]) }}
            </div>
            <div class="col-sm-6">
                {{ $items->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
