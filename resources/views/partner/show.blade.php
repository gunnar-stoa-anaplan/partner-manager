@extends('layouts.vertical', ['pageTitle' => $partner->name])


@section('page-title')
    <div class="d-flex align-items-end mb-4">
        <div>
            @include('layouts.partials/app-pagetitle', ['pageTitle'=> $partner->name, 'pageSubTitle1' => 'Partner Profile', 'pageSubTitle2'=> 'Dashboards'])
        </div>
        <div class="ms-auto d-none d-sm-flex align-items-center gap-2">
            <a href="{{ route('partners.blueprints', $partner->id) }}" class="btn btn-outline-warning">
                <i class="fal fa-layers me-1"></i> Blueprints
            </a>
            <a href="{{ route('partners.enablement', $partner->id) }}" class="btn btn-outline-info">
                <i class="fal fa-award me-1"></i> Enablement
            </a>
            <a href="{{ route('partners.calendar', $partner->id) }}" class="btn btn-outline-primary">
                <i class="fal fa-calendar me-1"></i> Calendar
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="row sortable-off">
        {{-- Active Projects --}}
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Active Projects</p>
                            <span class="h5 text-nowrap">
                                {{ number_format($activeProjects) }}
                                <small class="text-muted fw-400">/ {{ $partner->projects->count() }}</small>
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-check-circle h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Contacts --}}
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Contacts</p>
                            <span class="h5 text-nowrap">
                                {{ number_format($totalContacts) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-users h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Leads --}}
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Leads</p>
                            <span class="h5 text-nowrap">
                                {{ number_format($totalLeads) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex h-100 align-items-center justify-content-end pe-3">
                            <div class="width-5 height-5 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fad fa-bullseye h4 text-primary m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Total ACV --}}
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3">
                            <p class="p-0 mb-3 text-muted text-truncate">Total ACV</p>
                            <span class="h5 text-nowrap">
                                ${{ number_format($totalAcv, 0) }}
                            </span>
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

    <div class="row">
        {{-- Business Feed: Partner Metrics + Opportunity History --}}
        <div class="col-lg-12">
            <div class="panel panel-icon panel-locked" id="panel-1">
                <div class="panel-hdr">
                    <h2> Business <span class="fw-light"><i>Feed</i></span></h2>
                </div>
                <div class="panel-container border-faded border-start-0 border-end-0 border-bottom-0">
                    <div class="panel-content p-0">
                        <div class="row row-grid g-0">
                            <div class="col-sm-4 p-3" style="background: var(--app-nav-item-active-bg);">
                                @php
                                    $metrics = [
                                        ['label' => 'Project Delivery', 'value' => $avgProjectCompletion, 'color' => 'primary-700'],
                                        ['label' => 'Lead Acceptance', 'value' => $leadAcceptanceRate, 'color' => 'primary-700'],
                                        ['label' => 'Blueprint Progress', 'value' => $avgBlueprintCompletion, 'color' => 'primary-700'],
                                        ['label' => 'Contact Engagement', 'value' => $contactEngagement, 'color' => $contactEngagement > 80 ? 'success-500' : ($contactEngagement > 40 ? 'primary-700' : 'warning-500')],
                                    ];
                                @endphp
                                @foreach($metrics as $metric)
                                    <div class="d-flex mt-3">
                                        <span class="h6 m-0 fw-300">{{ $metric['label'] }}</span>
                                        <span class="h6 d-inline-block ms-auto">{{ $metric['value'] }}%</span>
                                    </div>
                                    <div class="progress progress mb-4 border shadow-inset-3">
                                        <div aria-label="{{ $metric['value'] }}%" aria-valuemax="100" aria-valuemin="0"
                                             aria-valuenow="{{ $metric['value'] }}"
                                             class="progress-bar bg-{{ $metric['color'] }} progress-bar-striped"
                                             role="progressbar" style="width: {{ $metric['value'] }}%;"></div>
                                    </div>
                                @endforeach
                                <div class="d-flex mt-3">
                                    <span class="h6 m-0 fw-300">Total Funding</span>
                                    <span class="h6 d-inline-block ms-auto">${{ number_format($totalFunding, 0) }}</span>
                                </div>
                            </div>
                            <div class="col-sm-8 p-3 bg-subtlelight-fade">
                                <div id="opportunity-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Blueprint Completion Radar --}}
        <div class="col-lg-4">
            <div class="panel panel-icon panel-locked" id="panel-2">
                <div class="panel-hdr">
                    <h2> Blueprint <span class="fw-light"><i>Completion</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="blueprint-radar-chart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lead Sources --}}
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2> Lead <span class="fw-light"><i>Sources</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="lead-sources-chart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Project Status --}}
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2> Project <span class="fw-light"><i>Status</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="project-status-chart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Sourced + Influenced NNACV (stacked area) vs Opportunity Count (line) ──
    var opportunityHistory = @json($opportunityHistory);
    if (opportunityHistory.length > 0) {
        // Resolve SmartAdmin theme colors (fallback to defaults if colorMap not ready)
        var cm = window.colorMap || {};
        var bv = (cm.bootstrapVars && cm.bootstrapVars.bodyColor) || null;
        var pr = (cm.primary && cm.primary[400]) || null;
        var pr6 = (cm.primary && cm.primary[600]) || null;
        var su = (cm.success && cm.success[400]) || null;

        var bodyColorHex = bv ? bv.hex : '#6c757d';
        var bodyColor01 = bv ? bv.rgba(0.1) : 'rgba(108,117,125,0.1)';
        var bodyColor07 = bv ? bv.rgba(0.7) : 'rgba(108,117,125,0.7)';
        var bodyColor08 = bv ? bv.rgba(0.8) : 'rgba(108,117,125,0.8)';
        var primaryHex = pr ? pr.hex : '#886ab5';
        var primary6Hex = pr6 ? pr6.hex : '#7454a5';
        var successHex = su ? su.hex : '#1dc9b7';

        new ApexCharts(document.querySelector('#opportunity-chart'), {
            series: [
                {
                    name: 'Sourced',
                    type: 'area',
                    data: opportunityHistory.map(function(h) { return parseFloat(h.sourced_amount); })
                },
                {
                    name: 'Influenced',
                    type: 'area',
                    data: opportunityHistory.map(function(h) { return parseFloat(h.influenced_amount); })
                },
                {
                    name: 'Opportunity Count',
                    type: 'line',
                    data: opportunityHistory.map(function(h) { return h.opportunity_count; })
                }
            ],
            chart: {
                height: 335,
                type: 'line',
                zoom: { enabled: false },
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'inherit',
                parentHeightOffset: 0
            },
            colors: [primaryHex, successHex, bodyColor08],
            stroke: {
                width: [1, 1, 2],
                curve: 'smooth',
                dashArray: [0, 0, 0]
            },
            fill: {
                type: ['solid', 'solid', 'solid'],
                opacity: [0.25, 0.15, 1]
            },
            markers: {
                size: [0, 0, 3],
                colors: [primaryHex, successHex, bodyColor07],
                strokeColors: [primaryHex, successHex, bodyColor07],
                strokeWidth: 2,
                hover: { sizeOffset: 2 }
            },
            xaxis: {
                categories: opportunityHistory.map(function(h) { return h.snapshot_month; }),
                labels: {
                    style: { colors: bodyColorHex, fontSize: '12px' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: [
                {
                    seriesName: 'Sourced',
                    tickAmount: 6,
                    labels: {
                        style: { colors: bodyColorHex, fontSize: '12px' },
                        formatter: function(val) {
                            if (val >= 1000000) return '$' + (val / 1000000).toFixed(1) + 'M';
                            if (val >= 1000) return '$' + (val / 1000).toFixed(0) + 'k';
                            return '$' + val;
                        }
                    }
                },
                {
                    seriesName: 'Sourced',
                    show: false
                },
                {
                    seriesName: 'Opportunity Count',
                    opposite: true,
                    tickAmount: 6,
                    labels: {
                        style: { colors: bodyColorHex, fontSize: '12px' },
                        formatter: function(val) { return Math.round(val).toLocaleString(); }
                    }
                }
            ],
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px',
                fontFamily: 'inherit',
                labels: { colors: bodyColorHex },
                markers: { width: 18, height: 6, radius: 2 },
                itemMargin: { horizontal: 12, vertical: 0 }
            },
            grid: {
                borderColor: bodyColor01,
                strokeDashArray: 3,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } }
            },
            dataLabels: { enabled: false },
            tooltip: {
                shared: true,
                intersect: false,
                theme: 'dark',
                y: [
                    { formatter: function(val) { return '$' + val.toLocaleString(); } },
                    { formatter: function(val) { return '$' + val.toLocaleString(); } },
                    { formatter: function(val) { return val.toLocaleString(); } }
                ]
            }
        }).render();
    } else {
        document.querySelector('#opportunity-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-chart-area fa-2x mb-2 opacity-50"></i><p>No opportunity history yet</p></div>';
    }

    // ── Blueprint Completion Radar (per-role series) ──
    var blueprintSeries = @json($blueprintByArea);
    var blueprintLabels = @json($blueprintAreas);
    if (blueprintSeries.length > 0 && blueprintLabels.length > 0) {
        new ApexCharts(document.querySelector('#blueprint-radar-chart'), {
            chart: { type: 'radar', height: 350, toolbar: { show: false }, fontFamily: 'inherit' },
            series: blueprintSeries,
            labels: blueprintLabels,
            yaxis: { max: 100, show: false, tickAmount: 4 },
            colors: ['#00BCD4', '#fd7e14', '#886ab5'],
            markers: { size: 3, strokeWidth: 1 },
            fill: { opacity: [0.15, 0.15, 0.15] },
            stroke: { width: 2 },
            legend: { show: true, position: 'bottom', fontSize: '13px', labels: { colors: '#6c757d' }, markers: { width: 10, height: 10, radius: 2 } },
            plotOptions: { radar: { polygons: { strokeDashArray: 3, connectorColors: 'rgba(108,117,125,0.15)', fill: { colors: ['rgba(108,117,125,0.03)', 'transparent'] } } } },
            tooltip: { y: { formatter: function(val) { return val + '%'; } } }
        }).render();
    } else {
        document.querySelector('#blueprint-radar-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-layer-group fa-2x mb-2 opacity-50"></i><p>No blueprints assigned</p></div>';
    }

    // ── Lead Sources Pie ──
    var leadSources = @json($leadSources);
    var sourceLabels = Object.keys(leadSources);
    var sourceValues = Object.values(leadSources);
    if (sourceLabels.length > 0) {
        new ApexCharts(document.querySelector('#lead-sources-chart'), {
            chart: { type: 'donut', height: 350 },
            series: sourceValues,
            labels: sourceLabels.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
            colors: ['#886ab5', '#fd7e14', '#00BCD4', '#28a745', '#dc3545', '#6c757d'],
            legend: { position: 'bottom' },
            plotOptions: { pie: { donut: { size: '55%' } } },
            dataLabels: { enabled: true }
        }).render();
    } else {
        document.querySelector('#lead-sources-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-bullseye fa-2x mb-2 opacity-50"></i><p>No leads yet</p></div>';
    }

    // ── Project Status Pie ──
    var projectStatuses = @json($projectStatuses);
    var statusLabels = Object.keys(projectStatuses);
    var statusValues = Object.values(projectStatuses);
    var statusColors = {
        'Completed': '#28a745', 'In Progress': '#886ab5', 'Draft': '#6c757d',
        'On Hold': '#fd7e14', 'planning': '#17a2b8', 'active': '#886ab5'
    };
    if (statusLabels.length > 0) {
        new ApexCharts(document.querySelector('#project-status-chart'), {
            chart: { type: 'donut', height: 350 },
            series: statusValues,
            labels: statusLabels,
            colors: statusLabels.map(s => statusColors[s] || '#868e96'),
            legend: { position: 'bottom' },
            plotOptions: { pie: { donut: { size: '55%' } } },
            dataLabels: { enabled: true }
        }).render();
    } else {
        document.querySelector('#project-status-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-clipboard fa-2x mb-2 opacity-50"></i><p>No projects yet</p></div>';
    }
});
</script>
@endsection
