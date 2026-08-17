<li class="menu-tree-item" data-id="{{ $node->id }}">
    <div class="menu-tree-handle">
        <span class="drag-icon"><i class="fa-solid fa-grip-vertical"></i></span>
        @if($node->allChildren->count())
        <button type="button" class="menu-tree-toggle"><i class="fa-solid fa-caret-down"></i></button>
        @else
        <span style="width: 20px; margin-right: 6px;"></span>
        @endif
        @if($node->icon)
        <span class="menu-icon"><i class="{{ $node->icon }}"></i></span>
        @endif
        <span class="menu-name">{{ $node->display_name }}</span>
        @if(!$node->is_active)
        <span class="badge badge-inactive">{{ __('admin/system/menu.text_disabled') }}</span>
        @endif
        <span class="menu-meta">
            @if($node->permission_name)
            <code>{{ $node->permission_name }}</code>
            @endif
        </span>
        <span class="menu-actions">
            <a href="{{ route('lang.ocadmin.system.menus.edit', $node) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="{{ __('admin/default.button_edit') }}"><i class="fa-solid fa-pencil"></i></a>
        </span>
    </div>
    <ul class="menu-tree{{ $node->allChildren->isEmpty() ? ' nested-empty' : '' }}">
        @foreach($node->allChildren as $child)
            @include('ocadmin::system.menu.tree-node', ['node' => $child])
        @endforeach
    </ul>
</li>
