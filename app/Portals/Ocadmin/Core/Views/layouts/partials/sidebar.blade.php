<nav id="column-left">
    <div id="navigation"><span class="fa-solid fa-bars"></span> Navigation</div>
    <ul id="menu">
        @php $i = 0; @endphp
        @foreach($menus as $menu)
            <li id="{{ $menu['id'] }}">
                @if(!empty($menu['href']))
                    <a href="{{ $menu['href'] }}"><i class="{{ $menu['icon'] }}"></i> {{ $menu['name'] }}</a>
                @else
                    <a href="#collapse-{{ $i }}" data-bs-toggle="collapse" class="parent collapsed"><i class="{{ $menu['icon'] }}"></i> {{ $menu['name'] }}</a>
                @endif

                @if(!empty($menu['children']))
                    <ul id="collapse-{{ $i }}" class="collapse">
                        @php $j = 0; @endphp
                        @foreach($menu['children'] as $children_1)
                            <li>
                                @if(!empty($children_1['href']))
                                    <a href="{{ $children_1['href'] }}">{{ $children_1['name'] }}</a>
                                @else
                                    <a href="#collapse-{{ $i }}-{{ $j }}" data-bs-toggle="collapse" class="parent collapsed">{{ $children_1['name'] }}</a>
                                @endif

                                @if(!empty($children_1['children']))
                                    <ul id="collapse-{{ $i }}-{{ $j }}" class="collapse">
                                        @php $k = 0; @endphp
                                        @foreach($children_1['children'] as $children_2)
                                            <li>
                                                @if(!empty($children_2['href']))
                                                    <a href="{{ $children_2['href'] }}">{{ $children_2['name'] }}</a>
                                                @else
                                                    <a href="#collapse-{{ $i }}-{{ $j }}-{{ $k }}" data-bs-toggle="collapse" class="parent collapsed">{{ $children_2['name'] }}</a>
                                                @endif

                                                @if(!empty($children_2['children']))
                                                    <ul id="collapse-{{ $i }}-{{ $j }}-{{ $k }}" class="collapse">
                                                        @foreach($children_2['children'] as $children_3)
                                                            <li><a href="{{ $children_3['href'] ?? '#' }}">{{ $children_3['name'] }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                            @php $k++; @endphp
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                            @php $j++; @endphp
                        @endforeach
                    </ul>
                @endif
            </li>
            @php $i++; @endphp
        @endforeach
    </ul>
</nav>
