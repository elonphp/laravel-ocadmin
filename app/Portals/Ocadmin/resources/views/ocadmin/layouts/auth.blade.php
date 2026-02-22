<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') - Ocadmin</title>

    <link href="{{ versioned_asset('assets/ocadmin/stylesheet/bootstrap.css') }}" rel="stylesheet" media="screen">
    <link href="{{ versioned_asset('assets/ocadmin/stylesheet/fonts/fontawesome/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ versioned_asset('assets/ocadmin/stylesheet/stylesheet.css') }}" rel="stylesheet" type="text/css">

    <style>
        .login-box {
            max-width: 400px;
            margin: 100px auto;
        }
    </style>
</head>
<body class="bg-light">
    @yield('content')

    <script src="{{ versioned_asset('assets/ocadmin/javascript/jquery/jquery-3.7.1.min.js') }}" type="text/javascript"></script>
    <script src="{{ versioned_asset('assets/ocadmin/javascript/bootstrap/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>
</body>
</html>
