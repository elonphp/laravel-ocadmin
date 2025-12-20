<header id="header" class="navbar navbar-expand navbar-light bg-light">
    <div class="container-fluid">
        <a href="{{ ocadmin_route('dashboard') }}" class="navbar-brand d-none d-lg-block">
            <img src="{{ ocadmin_asset('vendor/opencart/image/logo.png') }}" alt="{{ config('app.name', 'Ocadmin') }}" title="{{ config('app.name', 'Ocadmin') }}">
        </a>
        <button type="button" id="button-menu" class="btn btn-link d-inline-block d-lg-none">
            <i class="fa-solid fa-bars"></i>
        </button>
        <ul class="nav navbar-nav">
            {{-- Notifications --}}
            <li id="nav-notification" class="nav-item dropdown">
                <a href="#" data-bs-toggle="dropdown" class="nav-link dropdown-toggle">
                    <i class="fa-regular fa-bell"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <span class="dropdown-item text-center">{{ __('ocadmin::common.no_results') }}</span>
                </div>
            </li>

            {{-- Language Switcher --}}
            @include('ocadmin::components.locale-switcher')

            {{-- User Profile --}}
            <li id="nav-profile" class="nav-item dropdown">
                <a href="#" data-bs-toggle="dropdown" class="nav-link dropdown-toggle">
                    <img src="{{ ocadmin_asset('vendor/opencart/image/profile.png') }}" alt="{{ auth()->user()->name ?? 'Admin' }}" title="{{ auth()->user()->name ?? 'Admin' }}" class="rounded-circle">
                    <span class="d-none d-md-inline d-lg-inline">&nbsp;&nbsp;&nbsp;{{ auth()->user()->name ?? 'Admin' }} <i class="fa-solid fa-caret-down fa-fw"></i></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a href="#" class="dropdown-item"><i class="fa-solid fa-user-circle fa-fw"></i> {{ __('ocadmin::common.profile') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">{{ __('ocadmin::common.store') }}</h6></li>
                    <li><a href="/" target="_blank" class="dropdown-item">{{ __('ocadmin::common.storefront') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">{{ __('ocadmin::common.help') }}</h6></li>
                    <li><a href="https://www.opencart.com" target="_blank" class="dropdown-item"><i class="fa-brands fa-opencart fa-fw"></i> OpenCart</a></li>
                    <li><a href="https://docs.opencart.com" target="_blank" class="dropdown-item"><i class="fa-solid fa-file fa-fw"></i> {{ __('ocadmin::common.documentation') }}</a></li>
                </ul>
            </li>

            {{-- Logout --}}
            <li id="nav-logout" class="nav-item">
                <a href="#" class="nav-link" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class="fa-solid fa-sign-out"></i> <span class="d-none d-md-inline">{{ __('ocadmin::auth.logout') }}</span>
                </a>
                <form id="logout-form" action="{{ ocadmin_route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</header>
