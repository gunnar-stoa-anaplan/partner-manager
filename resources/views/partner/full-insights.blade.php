@extends('layouts.vertical', ['pageTitle' => 'Full Insights'])

@section('css')
<style>
    .bg-success-600 { background-color: #059669 !important; }
    .bg-warning-600 { background-color: #d97706 !important; }
    .bg-danger-600 { background-color: #dc2626 !important; }
</style>
@endsection

@section('page-title')
    <div class="d-flex align-items-end mb-4">
        <div>
            @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Full Business Insights', 'pageSubTitle1' => 'Measure', 'pageSubTitle1Url' => route('measure.index')])
        </div>
        <div class="ms-auto d-none d-sm-flex align-items-center">
            <span class="badge bg-info fs-6 me-2">
                <i class="fad fa-building me-1"></i>
                {{ $partners->count() }} {{ Str::plural('Partner', $partners->count()) }}
            </span>
        </div>
    </div>
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Full Insights goes beyond the numbers to surface patterns, anomalies, and opportunities across your portfolio. The analysis cards highlight where partners are outperforming or underperforming expectations, helping you prioritize interventions and celebrate wins with data-driven confidence.</p>
    <!-- Top KPI Cards -->
    <div class="row sortable-off">
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Total Projects</p>
                            <span class="h5 text-nowrap">{{ number_format($totalProjects) }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-project-diagram h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Total Opportunities</p>
                            <span class="h5 text-nowrap">{{ number_format($totalOpportunities) }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-bullseye-arrow h4 text-success m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Certified Resources</p>
                            <span class="h5 text-nowrap">{{ number_format($totalResources) }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-user-graduate h4 text-info m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Total Pipeline Value</p>
                            <span class="h5 text-nowrap">${{ number_format($totalPipelineValue / 1000, 0) }}K</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-chart-line h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Trends: Quota Feed (Left) + Chart (Right) -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-icon panel-locked" id="panel-1">
                <div class="panel-hdr">
                    <h2> Performance <span class="fw-light"><i>Trends</i></span>
                    </h2>
                </div>
                <div class="panel-container border-faded border-start-0 border-end-0 border-bottom-0">
                    <div class="panel-content p-0">
                        <div class="row row-grid g-0">
                            <!-- Opportunity Growth Feed (Left Side) -->
                            <div class="col-sm-4 p-3" style="background: var(--app-nav-item-active-bg);">
                                @foreach($topPartners->take(5) as $index => $quotaPartner)
                                    @php
                                        $totalAmount = $quotaPartner->opportunities->sum('amount');
                                        $target = 1000000;
                                        $percentage = $target > 0 ? min(100, ($totalAmount / $target) * 100) : 0;
                                        $color = $percentage >= 100 ? 'primary-700' : ($percentage >= 75 ? 'warning-500' : 'danger-500');
                                    @endphp

                                    <div class="d-flex {{ $loop->first ? 'mt-3' : '' }}">
                                        <span class="h6 m-0 fw-300 text-truncate" style="max-width: 150px;" title="{{ $quotaPartner->name }}">
                                            {{ $quotaPartner->name }} Quota
                                        </span>
                                        <span class="h6 d-inline-block ms-auto">{{ number_format($percentage) }}%</span>
                                    </div>
                                    <div class="progress progress mb-4 border shadow-inset-3">
                                        <div class="progress-bar bg-{{ $color }} progress-bar-striped"
                                             role="progressbar"
                                             style="width: {{ $percentage }}%;"
                                             aria-valuenow="{{ $percentage }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100"
                                             aria-label="{{ $percentage }}%"
                                             aria-labelledby="{{ $quotaPartner->name }} Quota"></div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Large Chart: NNACV and Opportunity Count (Right Side) -->
                            <div class="col-sm-8 p-3 bg-subtlelight-fade">
                                <div id="performanceChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Charts Row -->
        <div class="col-lg-4">
            <div class="panel panel-icon panel-locked" id="panel-2">
                <div class="panel-hdr">
                    <h2> Blueprint <span class="fw-light"><i>Completion</i></span>
                    </h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="blueprintChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2> Data <span class="fw-light"><i>Stream</i></span>
                    </h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="dataStreamChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2> Partner <span class="fw-light"><i>Distribution</i></span>
                    </h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="partnerDistributionChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data from controller
    const performanceData = @json($opportunityHistory);
    const blueprintData = @json($blueprintCompletion);
    const topPartners = @json($topPartners->values());

    // Extract dates and values for performance chart
    const perfDates = performanceData.map(function(item) {
        const date = new Date(item.snapshot_date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    const nacvValues = performanceData.map(function(item) {
        return parseFloat(item.total_amount) / 1000 || 0;
    });
    const oppCounts = performanceData.map(function(item) {
        return parseInt(item.opportunity_count) || 0;
    });

    // Performance Trends Chart (Dual-axis: NNACV bars + Opportunity Count line)
    if (document.getElementById('performanceChart')) {
        new ApexCharts(document.getElementById('performanceChart'), {
            series: [
                {
                    name: 'NNACV ($K)',
                    type: 'bar',
                    data: nacvValues
                },
                {
                    name: 'Opportunity Count',
                    type: 'line',
                    data: oppCounts
                }
            ],
            chart: {
                height: 335,
                type: 'line',
                stacked: false,
                zoom: { enabled: false },
                toolbar: { show: false },
                fontFamily: 'inherit',
                parentHeightOffset: 0
            },
            colors: ['rgba(102, 126, 234, 0.7)', '#28a745'],
            stroke: {
                width: [0, 2],
                curve: 'smooth'
            },
            fill: {
                opacity: [0.7, 1]
            },
            plotOptions: {
                bar: { columnWidth: '50%', borderRadius: 2 }
            },
            markers: {
                size: [0, 4],
                strokeColors: ['#667eea', '#28a745'],
                strokeWidth: 2,
                hover: { sizeOffset: 2 }
            },
            xaxis: {
                categories: perfDates,
                labels: { style: { fontSize: '12px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: [
                {
                    seriesName: 'NNACV ($K)',
                    labels: {
                        style: { fontSize: '12px' },
                        formatter: function(val) { return '$' + Math.round(val) + 'K'; }
                    },
                    title: { text: 'NNACV ($K)', style: { fontSize: '12px' } }
                },
                {
                    seriesName: 'Opportunity Count',
                    opposite: true,
                    labels: {
                        style: { fontSize: '12px' },
                        formatter: function(val) { return Math.round(val); }
                    },
                    title: { text: 'Opportunities', style: { fontSize: '12px' } }
                }
            ],
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontFamily: 'inherit',
                markers: { width: 18, height: 6, radius: 2 },
                itemMargin: { horizontal: 12, vertical: 0 }
            },
            grid: {
                borderColor: 'var(--bs-border-color)',
                strokeDashArray: 3,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } }
            },
            tooltip: {
                shared: true,
                intersect: false,
                theme: 'dark',
                y: [
                    { formatter: function(val) { return '$' + Math.round(val) + 'K'; } },
                    { formatter: function(val) { return Math.round(val); } }
                ]
            }
        }).render();
    }

    // Blueprint Completion Radar Chart
    if (document.getElementById('blueprintChart')) {
        const roles = ['Delivery', 'Sales', 'Pre-Sales'];
        const blueprintValues = roles.map(function(role) {
            const data = blueprintData[role];
            if (!data || !data.total) return 0;
            return parseFloat((data.completed / data.total * 100).toFixed(1));
        });

        new ApexCharts(document.getElementById('blueprintChart'), {
            series: [{
                name: 'Completion %',
                data: blueprintValues
            }],
            chart: {
                height: 350,
                width: '100%',
                type: 'radar',
                toolbar: { show: false },
                fontFamily: 'inherit',
                parentHeightOffset: 0
            },
            colors: ['#667eea'],
            stroke: { width: 2, colors: ['#667eea'] },
            fill: { opacity: 0.2 },
            markers: {
                size: 5,
                colors: ['#667eea'],
                strokeColors: '#fff',
                strokeWidth: 2,
                hover: { sizeOffset: 2 }
            },
            xaxis: {
                categories: roles,
                labels: { style: { fontSize: '12px' } }
            },
            yaxis: {
                max: 100,
                tickAmount: 5,
                show: true,
                labels: {
                    show: true,
                    style: { fontSize: '10px' },
                    formatter: function(val) { return val + '%'; }
                }
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontFamily: 'inherit',
                markers: { width: 18, height: 6, radius: 2 },
                itemMargin: { horizontal: 12, vertical: 0 }
            },
            tooltip: {
                theme: 'dark',
                y: { formatter: function(val) { return val + '%'; } }
            },
            grid: {
                borderColor: 'var(--bs-border-color)',
                strokeDashArray: 3
            },
            plotOptions: {
                radar: {
                    size: void 0,
                    offsetX: 0,
                    offsetY: 0,
                    polygons: {
                        strokeColors: 'var(--bs-border-color)',
                        connectorColors: 'var(--bs-border-color)',
                        fill: { colors: void 0 }
                    }
                }
            }
        }).render();
    }

    // Data Stream Chart (Stacked Bar)
    if (document.getElementById('dataStreamChart')) {
        new ApexCharts(document.getElementById('dataStreamChart'), {
            series: [
                { name: 'Positive', data: [30, 65, 80, 90, 40, 55] },
                { name: 'Negative', data: [-45, -50, -35, -25, -60, -75] }
            ],
            chart: {
                type: 'bar',
                height: 350,
                maxHeight: '100%',
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'inherit',
                parentHeightOffset: 0
            },
            colors: ['rgba(52, 211, 153, 0.8)', 'rgba(167, 139, 250, 0.8)'],
            plotOptions: {
                bar: { columnWidth: '50%', borderRadius: 1 }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                labels: { style: { fontSize: '12px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '12px' },
                    formatter: function(val) { return val; }
                }
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontFamily: 'inherit',
                markers: { width: 18, height: 6, radius: 2 },
                itemMargin: { horizontal: 12, vertical: 0 }
            },
            grid: {
                borderColor: 'var(--bs-border-color)',
                strokeDashArray: 3,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: true } }
            },
            tooltip: {
                shared: true,
                intersect: false,
                theme: 'dark',
                y: { formatter: function(val) { return val; } }
            }
        }).render();
    }

    // Partner Pipeline Distribution Chart (Donut)
    if (document.getElementById('partnerDistributionChart')) {
        const partnerNames = topPartners.map(function(p) {
            const name = p.name || 'Unknown';
            return name.length > 15 ? name.substring(0, 15) + '...' : name;
        });
        const partnerValues = topPartners.map(function(p) {
            const total = (p.opportunities || []).reduce(function(sum, opp) {
                return sum + (parseFloat(opp.amount) || 0);
            }, 0);
            return parseFloat((total / 1000).toFixed(0));
        });

        new ApexCharts(document.getElementById('partnerDistributionChart'), {
            series: partnerValues,
            chart: {
                type: 'pie',
                height: 350,
                maxHeight: '100%',
                toolbar: { show: false },
                fontFamily: 'inherit',
                parentHeightOffset: 0
            },
            colors: [
                'rgba(139, 92, 246, 0.8)',
                'rgba(52, 211, 153, 0.8)',
                'rgba(99, 179, 237, 0.8)',
                'rgba(96, 165, 250, 0.8)',
                'rgba(52, 211, 153, 0.6)'
            ],
            labels: partnerNames,
            dataLabels: { enabled: false },
            plotOptions: {
                pie: { donut: { size: '0%' } }
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontFamily: 'inherit',
                markers: { width: 12, height: 12, radius: 2 },
                itemMargin: { horizontal: 10, vertical: 2 }
            },
            tooltip: {
                theme: 'dark',
                y: { formatter: function(val) { return '$' + val + 'K'; } },
                style: { fontSize: '14px', fontFamily: 'inherit' },
                custom: function(opts) {
                    const label = opts.w.globals.labels[opts.seriesIndex];
                    const value = opts.series[opts.seriesIndex];
                    return '<div class="apexcharts-tooltip-box" style="padding: 2px 7px; background-color: var(--bs-body-bg);">' +
                           '<span style="color: var(--bs-body-color); font-size: 0.825rem !important;">' +
                           label + ': $' + value + 'K</span></div>';
                }
            },
            stroke: {
                width: 1,
                colors: ['var(--bs-body-bg)']
            }
        }).render();
    }
});
</script>
@endsection
