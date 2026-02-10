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
                    <h2> Business <span class="fw-light"><i>Metrics</i></span></h2>
                </div>
                <div class="panel-container border-faded border-start-0 border-end-0 border-bottom-0">
                    <div class="panel-content p-0">
                        <div class="row row-grid g-0">
                            <div class="col-sm-4 p-3" style="background: var(--app-nav-item-active-bg);">
                                @php
                                    $metrics = [
                                        ['label' => 'Sourced Revenue', 'value' => $sourcedRevenuePct, 'color' => 'primary-700'],
                                        ['label' => 'Influenced Revenue', 'value' => $influencedRevenuePct, 'color' => 'primary-700'],
                                        ['label' => 'Delivery Blueprint Coverage', 'value' => $avgBlueprintCompletion, 'color' => 'primary-700'],
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
                                    <span class="h6 m-0 fw-300">Pipeline Growth (YoY)</span>
                                    <span class="h6 d-inline-block ms-auto {{ $pipelineGrowthYoY >= 0 ? 'text-success' : 'text-danger' }}">{{ $pipelineGrowthYoY >= 0 ? '+' : '' }}{{ $pipelineGrowthYoY }}%</span>
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

        {{-- Project Risk --}}
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2> Project <span class="fw-light"><i>Risk</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="project-risk-chart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Enablement Engagement --}}
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2> Enablement <span class="fw-light"><i>Engagement</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="enablement-chart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pipeline Progression --}}
    <div class="row">
        <div class="col-lg-6">
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2> Pipeline <span class="fw-light"><i>Progression</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="pipeline-funnel-chart" style="height: 350px;"></div>
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

    // ── Sourced + Influenced NNACV (stacked area) vs Opportunity Count (line) ──
    var opportunityHistory = @json($opportunityHistory);
    if (opportunityHistory.length > 0) {
        // Nice-number helper: returns a max divisible by ticks, producing clean round labels
        function niceMax(dataMax, ticks) {
            if (!dataMax || dataMax <= 0) return ticks;
            var rawStep = dataMax / ticks;
            var mag = Math.pow(10, Math.floor(Math.log10(rawStep)));
            var r = rawStep / mag;
            var nice;
            if (r <= 1) nice = 1;
            else if (r <= 1.5) nice = 1.5;
            else if (r <= 2) nice = 2;
            else if (r <= 3) nice = 3;
            else if (r <= 5) nice = 5;
            else nice = 10;
            return nice * mag * ticks;
        }

        // Compute y-axis max from stacked totals — tight fit with minimal headroom
        var maxStacked = Math.max.apply(null, opportunityHistory.map(function(h) {
            return parseFloat(h.sourced_amount) + parseFloat(h.influenced_amount);
        }));
        var yAxisMax = niceMax(maxStacked * 1.05, 6) || 600000;

        var maxCount = Math.max.apply(null, opportunityHistory.map(function(h) { return h.opportunity_count; }));
        var countAxisMax = niceMax(maxCount * 1.05, 6) || 30;

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
                colors: [primaryHex, successHex, bodyColor08],
                dashArray: [4, 0, 0]
            },
            fill: {
                type: ['solid', 'solid', 'solid'],
                opacity: [0.15, 0.15, 1]
            },
            markers: {
                size: [3, 3, 3],
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
                    min: 0,
                    max: yAxisMax,
                    tickAmount: 6,
                    labels: {
                        style: { colors: bodyColorHex, fontSize: '12px' },
                        formatter: function(val) {
                            if (val >= 1000000) return '$' + Math.round(val / 1000000) + 'M';
                            if (val >= 1000) return '$' + Math.round(val / 1000) + 'k';
                            return '$' + Math.round(val);
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
                    min: 0,
                    max: countAxisMax,
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
                    { formatter: function(val) { return '$' + Math.round(val).toLocaleString(); } },
                    { formatter: function(val) { return '$' + Math.round(val).toLocaleString(); } },
                    { formatter: function(val) { return Math.round(val).toLocaleString(); } }
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
        var radarColors = ['#00BCD4', '#fd7e14', '#886ab5'];
        new ApexCharts(document.querySelector('#blueprint-radar-chart'), {
            chart: {
                height: 350,
                width: '100%',
                type: 'radar',
                toolbar: { show: false },
                fontFamily: 'inherit',
                parentHeightOffset: 0,
                sparkline: { enabled: false }
            },
            series: blueprintSeries,
            labels: blueprintLabels,
            colors: radarColors,
            stroke: {
                width: 0,
                colors: radarColors
            },
            fill: {
                opacity: 0.2
            },
            markers: {
                size: 5,
                colors: radarColors,
                strokeColors: '#fff',
                strokeWidth: 2,
                hover: { sizeOffset: 2 }
            },
            xaxis: {
                labels: {
                    style: {
                        colors: bodyColorHex,
                        fontSize: '10px'
                    }
                }
            },
            yaxis: {
                max: 100,
                tickAmount: 5,
                show: true,
                labels: {
                    show: true,
                    style: {
                        colors: bodyColorHex,
                        fontSize: '10px'
                    }
                }
            },
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
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function(val) { return val + '%'; }
                }
            },
            grid: {
                borderColor: bodyColor01,
                strokeDashArray: 3
            },
            plotOptions: {
                radar: {
                    size: undefined,
                    offsetX: 0,
                    offsetY: 0,
                    polygons: {
                        strokeColors: bodyColor01,
                        connectorColors: bodyColor01,
                        fill: { colors: undefined }
                    }
                }
            }
        }).render();
    } else {
        document.querySelector('#blueprint-radar-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-layer-group fa-2x mb-2 opacity-50"></i><p>No blueprints assigned</p></div>';
    }

    // ── Project Risk (horizontal bar centered at 1.0) ──
    var projectRisk = @json($projectRisk);
    var riskLabels = Object.keys(projectRisk);
    var riskValues = Object.values(projectRisk);
    if (riskLabels.length > 0) {
        // Shift values so 1.0 is the center (0 on the axis)
        var shiftedValues = riskValues.map(function(v) { return +(v - 1).toFixed(2); });
        var maxAbs = Math.max.apply(null, shiftedValues.map(function(v) { return Math.abs(v); }));
        var axisLimit = Math.max(Math.ceil(maxAbs * 10) / 10, 0.5);

        new ApexCharts(document.querySelector('#project-risk-chart'), {
            chart: { type: 'bar', height: 350, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Risk Score', data: shiftedValues }],
            xaxis: {
                categories: riskLabels,
                labels: {
                    style: { colors: bodyColorHex, fontSize: '12px' },
                    formatter: function(val) { return (val + 1).toFixed(1); }
                }
            },
            yaxis: {
                labels: { style: { colors: bodyColorHex, fontSize: '12px' } }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '60%',
                    borderRadius: 4,
                    colors: {
                        ranges: [
                            { from: -100, to: -0.001, color: '#e74c3c' },
                            { from: 0, to: 100, color: '#28a745' }
                        ]
                    }
                }
            },
            annotations: {
                xaxis: [{
                    x: 0,
                    borderColor: bodyColorHex,
                    strokeDashArray: 0,
                    label: {
                        text: '1.0',
                        position: 'top',
                        style: { color: bodyColorHex, fontSize: '11px', background: 'transparent' }
                    }
                }]
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) { return (val + 1).toFixed(2); },
                style: { fontSize: '12px' }
            },
            grid: { borderColor: bodyColor01, strokeDashArray: 3 },
            tooltip: {
                theme: 'dark',
                y: { formatter: function(val) { return (val + 1).toFixed(2); } }
            }
        }).render();
    } else {
        document.querySelector('#project-risk-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-shield-alt fa-2x mb-2 opacity-50"></i><p>No project risk data yet</p></div>';
    }

    // ── Enablement Engagement Donut ──
    var enablementByStatus = @json($enablementByStatus);
    var engLabels = Object.keys(enablementByStatus);
    var engValues = Object.values(enablementByStatus);
    var engColors = {
        'scheduled': '#17a2b8', 'in_progress': '#886ab5',
        'completed': '#28a745', 'cancelled': '#dc3545'
    };
    if (engLabels.length > 0) {
        new ApexCharts(document.querySelector('#enablement-chart'), {
            chart: { type: 'donut', height: 350 },
            series: engValues,
            labels: engLabels.map(function(s) {
                return s.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
            }),
            colors: engLabels.map(function(s) { return engColors[s] || '#868e96'; }),
            legend: { position: 'bottom', labels: { colors: bodyColorHex } },
            plotOptions: { pie: { donut: { size: '55%' } } },
            dataLabels: { enabled: true }
        }).render();
    } else {
        document.querySelector('#enablement-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-award fa-2x mb-2 opacity-50"></i><p>No enablement sessions yet</p></div>';
    }

    // ── Pipeline Progression Funnel ──
    var pipelineFunnel = @json($pipelineFunnel);
    var funnelLabels = [];
    var funnelValues = [];
    Object.keys(pipelineFunnel).forEach(function(key) {
        if (pipelineFunnel[key] > 0) {
            funnelLabels.push(key);
            funnelValues.push(pipelineFunnel[key]);
        }
    });
    if (funnelValues.length > 0) {
        var cm = window.colorMap || {};
        var pri = (cm.primary || {});
        var funnelColors = [
            (pri[500] || {}).hex || '#886ab5',
            (pri[400] || {}).hex || '#9B7FC5',
            (pri[300] || {}).hex || '#B09AD5',
            (pri[200] || {}).hex || '#C5B5E5',
            (pri[100] || {}).hex || '#DAD0F5',
            ((cm.danger || {})[500] || {}).hex || '#fd3995',
            ((cm.success || {})[500] || {}).hex || '#1dc9b7'
        ];
        new ApexCharts(document.querySelector('#pipeline-funnel-chart'), {
            series: [{ name: 'NNACV', data: funnelValues }],
            chart: { type: 'bar', height: 350, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: {
                bar: {
                    borderRadius: 0,
                    horizontal: true,
                    distributed: true,
                    barHeight: '80%',
                    isFunnel: true
                }
            },
            states: {
                hover: {
                    filter: { type: 'darken', value: 0.85 }
                }
            },
            colors: funnelColors,
            xaxis: {
                categories: funnelLabels,
                labels: { style: { colors: bodyColorHex } }
            },
            yaxis: {
                labels: { show: false }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val, opt) {
                    var label = opt.w.globals.labels[opt.dataPointIndex];
                    var formatted;
                    if (val >= 1000000) formatted = '$' + (val / 1000000).toFixed(1) + 'M';
                    else if (val >= 1000) formatted = '$' + Math.round(val / 1000) + 'k';
                    else formatted = '$' + Math.round(val);
                    return label + ':  ' + formatted;
                },
                style: { fontSize: '12px', fontWeight: 600, colors: ['#fff'] },
                dropShadow: { enabled: false }
            },
            legend: { show: false },
            grid: {
                show: false,
                padding: { left: 20, right: 20 },
                borderColor: bodyColor01
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function(val) { return '$' + Math.round(val).toLocaleString(); }
                }
            }
        }).render();
    } else {
        document.querySelector('#pipeline-funnel-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-filter fa-2x mb-2 opacity-50"></i><p>No pipeline data yet</p></div>';
    }
});
</script>
@endsection
