<header id="header" class="navbar navbar-expand navbar-light bg-light">
    <div class="container-fluid">
        <a href="{{ route('ocadmin.dashboard') }}" class="navbar-brand d-none d-lg-block">
            <img src="{{ asset('assets-ocadmin/vendor/opencart/image/logo.png') }}" alt="Ocadmin" title="Ocadmin">
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
                    <span class="dropdown-item text-center">No results</span>
                </div>
            </li>

            {{-- User Profile --}}
            <li id="nav-profile" class="nav-item dropdown">
                <a href="#" data-bs-toggle="dropdown" class="nav-link dropdown-toggle">
                    <img src="{{ asset('assets-ocadmin/vendor/opencart/image/profile.png') }}" alt="Admin" title="Admin" class="rounded-circle">
                    <span class="d-none d-md-inline d-lg-inline">&nbsp;&nbsp;&nbsp;Admin <i class="fa-solid fa-caret-down fa-fw"></i></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a href="#" class="dropdown-item"><i class="fa-solid fa-user-circle fa-fw"></i> Your Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Store</h6></li>
                    <li><a href="/" target="_blank" class="dropdown-item">Your Store</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Help</h6></li>
                    <li><a href="https://www.opencart.com" target="_blank" class="dropdown-item"><i class="fa-brands fa-opencart fa-fw"></i> Homepage</a></li>
                    <li><a href="https://docs.opencart.com" target="_blank" class="dropdown-item"><i class="fa-solid fa-file fa-fw"></i> Documentation</a></li>
                </ul>
            </li>

            {{-- Logout --}}
            <li id="nav-logout" class="nav-item">
                <a href="#" class="nav-link"><i class="fa-solid fa-sign-out"></i> <span class="d-none d-md-inline">Logout</span></a>
            </li>
        </ul>
    </div>
</header>
