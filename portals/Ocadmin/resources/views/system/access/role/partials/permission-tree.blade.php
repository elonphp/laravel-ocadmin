@foreach($items as $item)
    <div class="form-check {{ !empty($item['children']) ? 'mb-2' : '' }}">
        <input type="checkbox"
            class="form-check-input permission-checkbox group-{{ $groupId }}"
            name="permissions[]"
            value="{{ $item['id'] }}"
            id="permission-{{ $item['id'] }}"
            {{ $rolePermissions->contains($item['id']) ? 'checked' : '' }}>
        <label class="form-check-label" for="permission-{{ $item['id'] }}">
            {{ $item['title'] }}
            @if($item['type'] === 'action')
                <span class="badge bg-secondary">{{ __('system/access/role.text_type_action') }}</span>
            @endif
        </label>
    </div>

    @if(!empty($item['children']))
        <div class="ms-3">
            @include('ocadmin::system.access.role.partials.permission-tree', ['items' => $item['children'], 'groupId' => $groupId, 'rolePermissions' => $rolePermissions])
        </div>
    @endif
@endforeach
