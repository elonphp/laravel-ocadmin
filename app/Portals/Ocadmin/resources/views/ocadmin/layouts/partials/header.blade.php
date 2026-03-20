<header id="header" class="navbar navbar-expand navbar-light bg-light">
    <div class="container-fluid">
        <a href="{{ route('lang.ocadmin.dashboard') }}" class="navbar-brand d-none d-lg-block">
            <img src="{{ versioned_asset('assets/ocadmin/image/logo.png') }}" alt="Ocadmin" title="Ocadmin">
        </a>
        <button type="button" id="button-menu" class="btn btn-link d-inline-block d-lg-none">
            <i class="fa-solid fa-bars"></i>
        </button>
        <ul class="nav navbar-nav">
            {{-- Language --}}
            @php
                $currentLocale = app()->getLocale();
                $localeNames = config('localization.locale_names', []);
                $urlMapping = config('localization.url_mapping', []);
                $urlMappingReverse = array_flip($urlMapping);
            @endphp
            <li id="nav-language" class="nav-item dropdown">
                <a href="#" data-bs-toggle="dropdown" class="nav-link dropdown-toggle">
                    <i class="fa-solid fa-globe"></i>
                    <span class="d-none d-md-inline">{{ $localeNames[$currentLocale] ?? $currentLocale }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($localeNames as $locale => $name)
                    <li>
                        <a href="#" class="dropdown-item {{ $locale === $currentLocale ? 'active' : '' }}"
                           data-locale="{{ $urlMappingReverse[$locale] ?? $locale }}">
                            {{ $name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </li>

            {{-- Notifications --}}
            <li id="nav-notification" class="nav-item dropdown">
                <a href="#" data-bs-toggle="dropdown" class="nav-link dropdown-toggle">
                    <i class="fa-regular fa-bell"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <span class="dropdown-item text-center">{{ __('admin/default.text_no_results') }}</span>
                </div>
            </li>

            {{-- User Profile --}}
            <li id="nav-profile" class="nav-item dropdown">
                <a href="#" data-bs-toggle="dropdown" class="nav-link dropdown-toggle">
                    <img src="{{ versioned_asset('assets/ocadmin/image/profile.png') }}" alt="{{ auth()->user()->name ?? 'Admin' }}" title="{{ auth()->user()->name ?? 'Admin' }}" class="rounded-circle">
                    <span class="d-none d-md-inline d-lg-inline">&nbsp;&nbsp;&nbsp;{{ auth()->user()->name ?? 'Admin' }} <i class="fa-solid fa-caret-down fa-fw"></i></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a href="{{ route('lang.ocadmin.account.profile') }}" class="dropdown-item"><i class="fa-solid fa-user-circle fa-fw"></i> {{ __('admin/account/profile.heading_title') }}</a></li>
                    <li><a href="{{ route('lang.ocadmin.account.user-devices.index') }}" class="dropdown-item"><i class="fa-solid fa-desktop fa-fw"></i> {{ __('admin/account/user_device.heading_title') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a href="/" target="_blank" class="dropdown-item">{{ __('admin/default.text_frontend') }}</a></li>
                </ul>
            </li>

            {{-- Logout --}}
            <li id="nav-logout" class="nav-item">
                <a href="{{ route('lang.ocadmin.logout') }}" class="nav-link" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class="fa-solid fa-sign-out"></i> <span class="d-none d-md-inline">{{ __('admin/default.button_logout') }}</span>
                </a>
                <form id="logout-form" action="{{ route('lang.ocadmin.logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</header>
