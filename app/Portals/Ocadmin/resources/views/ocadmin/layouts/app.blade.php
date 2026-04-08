<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Ocadmin</title>

    {{-- OpenCart Styles --}}
    <link href="{{ versioned_asset('assets/ocadmin/stylesheet/bootstrap.css') }}" rel="stylesheet" media="screen">
    <link href="{{ versioned_asset('assets/ocadmin/stylesheet/fonts/fontawesome/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ versioned_asset('assets/ocadmin/stylesheet/stylesheet.css') }}" rel="stylesheet" type="text/css">

    {{-- Vendor Styles --}}
    <link href="{{ versioned_asset('assets/ocadmin/javascript/jquery/jqvmap/jqvmap.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ versioned_asset('assets/vendor/select2/select2.min.css') }}" rel="stylesheet" type="text/css">

    <style>
    /* 大螢幕時也允許手動收合左側欄 */
    @media (min-width: 992px) {
        #column-left.collapsed {
            left: -235px;
        }
        #column-left.collapsed + #content,
        #column-left.collapsed + #content + #footer {
            margin-left: 0;
        }
    }
    </style>

    @yield('styles')
</head>
<body>
<div id="alert"></div>
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
<script src="{{ versioned_asset('assets/ocadmin/javascript/jquery/jquery-3.7.1.min.js') }}" type="text/javascript"></script>
<script src="{{ versioned_asset('assets/ocadmin/javascript/bootstrap/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>
<script src="{{ versioned_asset('assets/ocadmin/javascript/common.js') }}" type="text/javascript"></script>

{{-- Vendor Scripts --}}
<script src="{{ versioned_asset('assets/ocadmin/javascript/jquery/jqvmap/jquery.vmap.js') }}" type="text/javascript"></script>
<script src="{{ versioned_asset('assets/ocadmin/javascript/jquery/jqvmap/maps/jquery.vmap.world.js') }}" type="text/javascript"></script>
<script src="{{ versioned_asset('assets/ocadmin/javascript/jquery/flot/jquery.flot.js') }}" type="text/javascript"></script>
<script src="{{ versioned_asset('assets/ocadmin/javascript/jquery/flot/jquery.flot.resize.min.js') }}" type="text/javascript"></script>
<script src="{{ versioned_asset('assets/vendor/sortablejs/Sortable.min.js') }}" type="text/javascript"></script>
<script src="{{ versioned_asset('assets/vendor/select2/select2.min.js') }}" type="text/javascript"></script>
<script type="text/javascript">
// Sidebar collapse navigation
$('#menu a.parent').on('click', function(e) {
    e.preventDefault();
});
</script>

@yield('scripts')
</body>
</html>
