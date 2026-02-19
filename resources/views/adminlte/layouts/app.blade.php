<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Admin</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ versioned_asset('assets/adminlte/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('assets/vendor/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('assets/adminlte/vendor/jqvmap/jqvmap.css') }}">

    <style>
        .app-main { background-color: #f6f6f6; }
        .sidebar-menu .nav-treeview { padding-left: 1rem; }
        .sidebar-menu .nav-treeview .nav-icon { font-size: 0.8rem; }
        .card { box-shadow: none; }
        .nav-tabs .nav-link:not(.active) { color: #6c757d; }
        #alert { z-index: 9999; position: fixed; top: 30%; left: 50%; transform: translateX(-50%); width: 500px; }
        #alert .alert-success { box-shadow: 0 0 0 5px rgb(var(--bs-success-rgb), 0.1); }
        #alert .alert-danger { box-shadow: 0 0 0 5px rgb(var(--bs-danger-rgb), 0.1); }
        .spinning { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>

    @yield('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div id="alert"></div>

<div class="app-wrapper">
    @include('ocadmin::layouts.partials.header')
    @include('ocadmin::layouts.partials.sidebar')

    <main class="app-main">
        @yield('content')
    </main>

    @include('ocadmin::layouts.partials.footer')
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script src="{{ versioned_asset('assets/adminlte/js/adminlte.min.js') }}"></script>
<script src="{{ versioned_asset('assets/vendor/select2/select2.min.js') }}"></script>
<script src="{{ versioned_asset('assets/vendor/sortablejs/Sortable.min.js') }}"></script>
<script src="{{ versioned_asset('assets/adminlte/vendor/jqvmap/jquery.vmap.js') }}"></script>
<script src="{{ versioned_asset('assets/adminlte/vendor/jqvmap/maps/jquery.vmap.world.js') }}"></script>
<script src="{{ versioned_asset('assets/adminlte/vendor/flot/jquery.flot.js') }}"></script>
<script src="{{ versioned_asset('assets/adminlte/vendor/flot/jquery.flot.resize.min.js') }}"></script>
<script src="{{ versioned_asset('assets/adminlte/js/common.js') }}"></script>

@yield('scripts')
</body>
</html>
