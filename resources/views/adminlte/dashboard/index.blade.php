@extends('ocadmin::layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Dashboard</h3>
            </div>
            <div class="col-sm-6">
                @include('ocadmin::layouts.partials.breadcrumb')
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        {{-- Statistics Tiles --}}
        <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="tile tile-primary">
                    <div class="tile-heading">Total Orders <span class="float-end">+12%</span></div>
                    <div class="tile-body">
                        <i class="bi bi-cart"></i>
                        <h2 class="float-end">1,234</h2>
                    </div>
                    <div class="tile-footer"><a href="#">View more...</a></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="tile tile-primary">
                    <div class="tile-heading">Total Sales <span class="float-end">+8%</span></div>
                    <div class="tile-body">
                        <i class="bi bi-credit-card"></i>
                        <h2 class="float-end">NT$56,789</h2>
                    </div>
                    <div class="tile-footer"><a href="#">View more...</a></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="tile tile-primary">
                    <div class="tile-heading">Total Customers <span class="float-end">+5%</span></div>
                    <div class="tile-body">
                        <i class="bi bi-person"></i>
                        <h2 class="float-end">456</h2>
                    </div>
                    <div class="tile-footer"><a href="#">View more...</a></div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="tile tile-primary">
                    <div class="tile-heading">People Online</div>
                    <div class="tile-body">
                        <i class="bi bi-people"></i>
                        <h2 class="float-end">23</h2>
                    </div>
                    <div class="tile-footer"><a href="#">View more...</a></div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="row">
            {{-- World Map --}}
            <div class="col-lg-6 col-md-12 col-sm-12">
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-globe"></i> World Map</div>
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
                                <i class="bi bi-calendar"></i> <i class="bi bi-caret-down"></i>
                            </a>
                            <div id="range" class="dropdown-menu dropdown-menu-end">
                                <a href="day" class="dropdown-item">Today</a>
                                <a href="week" class="dropdown-item">Week</a>
                                <a href="month" class="dropdown-item active">Month</a>
                                <a href="year" class="dropdown-item">Year</a>
                            </div>
                        </div>
                        <i class="bi bi-bar-chart"></i> Sales Analytics
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
                    <div class="card-header"><i class="bi bi-calendar-event"></i> Recent Activity</div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <i class="bi bi-person text-primary me-2"></i>
                            New customer registered
                            <small class="text-muted d-block">2 minutes ago</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-cart text-success me-2"></i>
                            Order #1234 placed
                            <small class="text-muted d-block">15 minutes ago</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-truck text-info me-2"></i>
                            Order #1230 shipped
                            <small class="text-muted d-block">1 hour ago</small>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-star text-warning me-2"></i>
                            New product review
                            <small class="text-muted d-block">3 hours ago</small>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Latest Orders --}}
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-cart"></i> Latest Orders</div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td class="text-end">Order ID</td>
                                    <td>Customer</td>
                                    <td>Status</td>
                                    <td>Date Added</td>
                                    <td class="text-end">Total</td>
                                    <td class="text-end">Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-end">#1234</td>
                                    <td>John Doe</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                    <td>2025-01-27</td>
                                    <td class="text-end">NT$1,500</td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-end">#1233</td>
                                    <td>Jane Smith</td>
                                    <td><span class="badge bg-info">Processing</span></td>
                                    <td>2025-01-27</td>
                                    <td class="text-end">NT$2,300</td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-end">#1232</td>
                                    <td>Bob Wilson</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>2025-01-26</td>
                                    <td class="text-end">NT$890</td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>
                                    </td>
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

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    // World Map
    $.ajax({
        url: "{{ route('lang.ocadmin.dashboard.map-data') }}",
        dataType: 'json',
        success: function(json) {
            var data = {};

            for (var i in json) {
                data[i] = json[i]['total'];
            }

            $('#vmap').vectorMap({
                map: 'world_en',
                backgroundColor: '#FFFFFF',
                borderColor: '#FFFFFF',
                color: '#9FD5F1',
                hoverOpacity: 0.7,
                selectedColor: '#666666',
                enableZoom: true,
                showTooltip: true,
                values: data,
                normalizeFunction: 'polynomial',
                onLabelShow: function(event, label, code) {
                    if (json[code]) {
                        label.html('<strong>' + label.text() + '</strong><br />Orders: ' + json[code]['total'] + '<br />Sales: ' + json[code]['amount']);
                    }
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.error('Map Error:', thrownError);
        }
    });

    // Sales Chart
    function loadSalesChart(range) {
        $.ajax({
            type: 'get',
            url: '{{ route('lang.ocadmin.dashboard.chart-sales') }}',
            data: { range: range },
            dataType: 'json',
            success: function(json) {
                if (typeof json['order'] == 'undefined') {
                    return false;
                }

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
                    },
                    xaxis: {
                        show: true,
                        ticks: json['xaxis']
                    }
                };

                $.plot('#chart-sale', [json['order'], json['customer']], option);

                $('#chart-sale').bind('plothover', function(event, pos, item) {
                    $('.tooltip').remove();

                    if (item) {
                        $('<div id="tooltip" class="tooltip top show"><div class="tooltip-arrow"></div><div class="tooltip-inner">' + item.datapoint[1].toFixed(0) + '</div></div>').prependTo('body');

                        $('#tooltip').css({
                            position: 'absolute',
                            left: item.pageX - ($('#tooltip').outerWidth() / 2),
                            top: item.pageY - $('#tooltip').outerHeight(),
                            pointer: 'cursor'
                        }).fadeIn('slow');

                        $('#chart-sale').css('cursor', 'pointer');
                    } else {
                        $('#chart-sale').css('cursor', 'auto');
                    }
                });
            },
            error: function(xhr, ajaxOptions, thrownError) {
                console.error('Chart Error:', thrownError);
            }
        });
    }

    // Range selector
    $('#range a').on('click', function(e) {
        e.preventDefault();

        $(this).parent().find('a').removeClass('active');
        $(this).addClass('active');

        var range = $(this).attr('href');
        loadSalesChart(range);
    });

    // Initial load
    loadSalesChart('month');
});
</script>
@endsection
