@extends('ocadmin::layouts.app')

@section('title', __('ocadmin::menu.dashboard'))

@push('styles')
<link type="text/css" href="{{ ocadmin_asset('vendor/opencart/javascript/jquery/jqvmap/jqvmap.css') }}" rel="stylesheet" media="screen">
@endpush

@section('content')
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="float-end">
                <button type="button" id="button-setting" data-bs-toggle="tooltip" title="{{ __('ocadmin::common.developer_settings') }}" class="btn btn-info">
                    <i class="fa-solid fa-cog"></i>
                </button>
            </div>
            <h1>{{ __('ocadmin::menu.dashboard') }}</h1>
            @include('ocadmin::layouts.partials.breadcrumb', ['breadcrumbs' => [
                ['text' => __('ocadmin::menu.dashboard'), 'href' => ocadmin_route('dashboard')]
            ]])
        </div>
    </div>

    <div class="container-fluid">
        {{-- Statistics Tiles --}}
        <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="tile tile-primary">
                    <div class="tile-heading">{{ __('ocadmin::dashboard.total_orders') }} <span class="float-end">--</span></div>
                    <div class="tile-body">
                        <i class="fa-solid fa-shopping-cart"></i>
                        <h2 class="float-end">--</h2>
                    </div>
                    <div class="tile-footer"><a href="#">{{ __('ocadmin::common.view_more') }}</a></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="tile tile-primary">
                    <div class="tile-heading">{{ __('ocadmin::dashboard.total_sales') }} <span class="float-end">--</span></div>
                    <div class="tile-body">
                        <i class="fa-solid fa-credit-card"></i>
                        <h2 class="float-end">--</h2>
                    </div>
                    <div class="tile-footer"><a href="#">{{ __('ocadmin::common.view_more') }}</a></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="tile tile-primary">
                    <div class="tile-heading">{{ __('ocadmin::dashboard.total_customers') }} <span class="float-end">--</span></div>
                    <div class="tile-body">
                        <i class="fa-solid fa-user"></i>
                        <h2 class="float-end">--</h2>
                    </div>
                    <div class="tile-footer"><a href="#">{{ __('ocadmin::common.view_more') }}</a></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="tile tile-primary">
                    <div class="tile-heading">{{ __('ocadmin::dashboard.people_online') }}</div>
                    <div class="tile-body">
                        <i class="fa-solid fa-users"></i>
                        <h2 class="float-end">--</h2>
                    </div>
                    <div class="tile-footer"><a href="#">{{ __('ocadmin::common.view_more') }}</a></div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="row">
            {{-- World Map --}}
            <div class="col-lg-6 col-md-12 col-sm-12">
                <div class="card mb-3">
                    <div class="card-header"><i class="fa-solid fa-globe"></i> {{ __('ocadmin::dashboard.world_map') }}</div>
                    <div class="card-body">
                        <div id="vmap" style="width: 100%; height: 260px;"></div>
                    </div>
                </div>
            </div>

            {{-- Sales Analytics --}}
            <div class="col-lg-6 col-md-12 col-sm-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="float-end">
                            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-calendar-alt"></i> <i class="fa-solid fa-caret-down"></i>
                            </a>
                            <div id="range" class="dropdown-menu dropdown-menu-end">
                                <a href="day" class="dropdown-item">{{ __('ocadmin::dashboard.today') }}</a>
                                <a href="week" class="dropdown-item">{{ __('ocadmin::dashboard.week') }}</a>
                                <a href="month" class="dropdown-item active">{{ __('ocadmin::dashboard.month') }}</a>
                                <a href="year" class="dropdown-item">{{ __('ocadmin::dashboard.year') }}</a>
                            </div>
                        </div>
                        <i class="fa-solid fa-chart-bar"></i> {{ __('ocadmin::dashboard.sales_analytics') }}
                    </div>
                    <div class="card-body">
                        <div id="chart-sale" style="width: 100%; height: 260px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Activity & Orders Row --}}
        <div class="row">
            {{-- Recent Activity --}}
            <div class="col-lg-4 col-md-12 col-sm-12">
                <div class="card mb-3">
                    <div class="card-header"><i class="fa-solid fa-calendar"></i> {{ __('ocadmin::dashboard.recent_activity') }}</div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item text-muted text-center">
                            {{ __('ocadmin::common.no_results') }}
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Latest Orders --}}
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="card mb-3">
                    <div class="card-header"><i class="fa-solid fa-shopping-cart"></i> {{ __('ocadmin::dashboard.latest_orders') }}</div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td class="text-end">{{ __('ocadmin::dashboard.order_id') }}</td>
                                    <td>{{ __('ocadmin::dashboard.customer') }}</td>
                                    <td>{{ __('ocadmin::dashboard.status') }}</td>
                                    <td>{{ __('ocadmin::dashboard.date_added') }}</td>
                                    <td class="text-end">{{ __('ocadmin::dashboard.total') }}</td>
                                    <td class="text-end">{{ __('ocadmin::common.action') }}</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">{{ __('ocadmin::common.no_results') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- jQVMap --}}
<script type="text/javascript" src="{{ ocadmin_asset('vendor/opencart/javascript/jquery/jqvmap/jquery.vmap.js') }}"></script>
<script type="text/javascript" src="{{ ocadmin_asset('vendor/opencart/javascript/jquery/jqvmap/maps/jquery.vmap.world.js') }}"></script>

{{-- Flot Charts --}}
<script type="text/javascript" src="{{ ocadmin_asset('vendor/opencart/javascript/jquery/flot/jquery.flot.js') }}"></script>
<script type="text/javascript" src="{{ ocadmin_asset('vendor/opencart/javascript/jquery/flot/jquery.flot.resize.min.js') }}"></script>

<script type="text/javascript">
$(document).ready(function() {
    // Initialize World Map
    $('#vmap').vectorMap({
        map: 'world_en',
        backgroundColor: '#FFFFFF',
        borderColor: '#FFFFFF',
        color: '#9FD5F1',
        hoverOpacity: 0.7,
        selectedColor: '#666666',
        enableZoom: true,
        showTooltip: true
    });

    // Initialize empty chart
    var option = {
        shadowSize: 0,
        colors: ['#9FD5F1', '#1065D2'],
        bars: {
            show: true,
            fill: true,
            lineWidth: 1
        },
        grid: {
            backgroundColor: '#FFFFFF',
            hoverable: true
        },
        points: {
            show: false
        }
    };

    $.plot('#chart-sale', [[]], option);
});
</script>
@endpush
