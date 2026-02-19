<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ route('lang.ocadmin.dashboard') }}" class="brand-link">
            <span class="brand-text fw-light">AdminLTE</span>
        </a>
    </div>
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
                @foreach($menus as $menu)
                @php $hasChildren = !empty($menu['children']); @endphp
                <li class="nav-item">
                    <a href="{{ $menu['href'] ?: '#' }}" class="nav-link">
                        <i class="nav-icon {{ $menu['icon'] ?? 'bi bi-circle' }}"></i>
                        <p>
                            {{ $menu['name'] }}
                            @if($hasChildren)
                            <i class="nav-arrow bi bi-chevron-right"></i>
                            @endif
                        </p>
                    </a>
                    @if($hasChildren)
                    <ul class="nav nav-treeview">
                        @foreach($menu['children'] as $child1)
                        @php $hasChildren1 = !empty($child1['children']); @endphp
                        <li class="nav-item">
                            <a href="{{ $child1['href'] ?: '#' }}" class="nav-link">
                                <i class="nav-icon bi bi-chevron-double-right"></i>
                                <p>
                                    {{ $child1['name'] }}
                                    @if($hasChildren1)
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                    @endif
                                </p>
                            </a>
                            @if($hasChildren1)
                            <ul class="nav nav-treeview">
                                @foreach($child1['children'] as $child2)
                                <li class="nav-item">
                                    <a href="{{ $child2['href'] ?: '#' }}" class="nav-link">
                                        <i class="nav-icon bi bi-chevron-double-right"></i>
                                        <p>{{ $child2['name'] }}</p>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
