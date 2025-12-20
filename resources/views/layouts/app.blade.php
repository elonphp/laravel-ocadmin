<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Ocadmin') }}</title>

    {{-- OpenCart Styles --}}
    <link href="{{ ocadmin_asset('vendor/opencart/stylesheet/bootstrap.css') }}" rel="stylesheet" media="screen">
    <link href="{{ ocadmin_asset('vendor/opencart/stylesheet/fonts/fontawesome/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ ocadmin_asset('vendor/opencart/stylesheet/stylesheet.css') }}" rel="stylesheet" type="text/css">

    @stack('styles')
</head>
<body>
<div id="alert" class="toast-container position-fixed top-0 end-0 p-3"></div>
<div id="container">
    {{-- Header --}}
    @include('ocadmin::layouts.partials.header')

    {{-- Sidebar --}}
    @include('ocadmin::layouts.partials.sidebar')

    {{-- Main Content --}}
    @yield('content')

    {{-- Footer --}}
    @include('ocadmin::layouts.partials.footer')
</div>

{{-- Scripts --}}
<script src="{{ ocadmin_asset('vendor/opencart/javascript/jquery/jquery-3.7.1.min.js') }}" type="text/javascript"></script>
<script src="{{ ocadmin_asset('vendor/opencart/javascript/bootstrap/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>
<script src="{{ ocadmin_asset('vendor/opencart/javascript/common.js') }}" type="text/javascript"></script>
<script type="text/javascript">
// Menu toggle
$('#button-menu').on('click', function() {
    $('#column-left').toggleClass('active');
});

// Sidebar collapse navigation
$('#menu a.parent').on('click', function(e) {
    e.preventDefault();
});
</script>

@stack('scripts')
</body>
</html>
