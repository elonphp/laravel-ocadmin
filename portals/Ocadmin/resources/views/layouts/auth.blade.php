<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '登入') - Ocadmin</title>

    {{-- OpenCart Styles --}}
    <link href="{{ asset('assets-ocadmin/vendor/opencart/stylesheet/bootstrap.css') }}" rel="stylesheet" media="screen">
    <link href="{{ asset('assets-ocadmin/vendor/opencart/stylesheet/fonts/fontawesome/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets-ocadmin/vendor/opencart/stylesheet/stylesheet.css') }}" rel="stylesheet" type="text/css">

    @yield('styles')
</head>
<body>
<div id="alert" class="toast-container position-fixed top-0 end-0 p-3"></div>

@yield('content')

{{-- Scripts --}}
<script src="{{ asset('assets-ocadmin/vendor/opencart/javascript/jquery/jquery-3.7.1.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets-ocadmin/vendor/opencart/javascript/bootstrap/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>

@yield('scripts')
</body>
</html>
