<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            @php
                $currentLocale = app()->getLocale();
                $localeNames = config('localization.locale_names', []);
                $urlMapping = config('localization.url_mapping', []);
                $urlMappingReverse = array_flip($urlMapping);
                $currentPath = request()->path();
            @endphp
            <li id="nav-language" class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-globe"></i>
                    <span class="d-none d-md-inline">{{ $localeNames[$currentLocale] ?? $currentLocale }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($localeNames as $locale => $name)
                    @php
                        $urlLocale = $urlMappingReverse[$locale] ?? $locale;
                        $currentUrlLocale = $urlMappingReverse[$currentLocale] ?? $currentLocale;
                        $newPath = preg_replace('#^' . preg_quote($currentUrlLocale, '#') . '(/|$)#', $urlLocale . '$1', $currentPath);
                    @endphp
                    <li>
                        <a href="/{{ $newPath }}" class="dropdown-item {{ $locale === $currentLocale ? 'active' : '' }}">
                            {{ $name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </li>

            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                    <span class="d-none d-md-inline">{{ auth()->user()->name ?? 'Admin' }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('lang.ocadmin.account.profile') }}">
                            <i class="bi bi-person"></i> {{ $lang->button_profile ?? 'Profile' }}
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#"
                   onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right"></i> {{ $lang->button_logout ?? 'Logout' }}
                </a>
            </li>
        </ul>
        <form id="logout-form" action="{{ route('lang.ocadmin.logout') }}" method="POST" class="d-none">@csrf</form>
    </div>
</nav>
