@extends('layouts.vertical', ['pageTitle' => 'Full Summary'])

@section('page-title')
    <div class="d-flex align-items-end mb-4">
        <div>
            @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Full Business Summary', 'pageSubTitle1' => 'Measure', 'pageSubTitle1Url' => route('measure.index')])
        </div>
        <div class="ms-auto d-none d-sm-flex align-items-center">
            <span class="badge bg-info fs-6 me-2">
                <i class="fal fa-building me-1"></i> {{ $partners->count() }} {{ Str::plural('Partner', $partners->count()) }}
            </span>
            <a href="{{ route('partners.my') }}" class="btn btn-outline-secondary">
                <i class="fal fa-arrow-left me-1"></i> Back to Partners
            </a>
        </div>
    </div>
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Full Summary aggregates key business metrics across your entire partner portfolio — pipeline, revenue, certifications, and engagement — into a single consolidated view. Use this dashboard to spot portfolio-wide trends, compare partner performance at a glance, and identify where to focus your time and resources for maximum impact.</p>
    <!-- Aggregate Key Metrics -->
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
                                <i class="fad fa-dollar-sign h4 text-warning m-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historical Graphs Row -->
    <div class="row">
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-opp">
                <div class="panel-hdr">
                    <h2>Opportunity Pipeline <span class="fw-light"><i>12-Week Trend</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="opportunityChart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-nacv">
                <div class="panel-hdr">
                    <h2>nACV Trend <span class="fw-light"><i>12-Week Pipeline Value</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="nacvChart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-res">
                <div class="panel-hdr">
                    <h2>Resource Growth <span class="fw-light"><i>Certified Resources Over Time</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="resourceChart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-win">
                <div class="panel-hdr">
                    <h2>Win Rate Progress <span class="fw-light"><i>Closed Won Opportunities</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="winRateChart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics Section -->
    <div class="row">
        <div class="col-xl-8">
            <!-- Current State Overview -->
            <div class="panel panel-icon" id="panel-state">
                <div class="panel-hdr">
                    <h2>Current State <span class="fw-light"><i>Overview</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-500">Average Deal Size</small>
                                        <small class="text-muted">${{ $totalOpportunities > 0 ? number_format($totalPipelineValue / $totalOpportunities) : '0' }}</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-500">Total Contacts</small>
                                        <small class="text-muted">{{ number_format($totalContacts) }}</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-500">Resources per Partner</small>
                                        <small class="text-muted">{{ $partners->count() > 0 ? number_format($totalResources / $partners->count(), 1) : '0' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-500">Projects per Partner</small>
                                        <small class="text-muted">{{ $partners->count() > 0 ? number_format($totalProjects / $partners->count(), 1) : '0' }}</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-500">Opportunities per Partner</small>
                                        <small class="text-muted">{{ $partners->count() > 0 ? number_format($totalOpportunities / $partners->count(), 1) : '0' }}</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-500">Active Partners</small>
                                        <small class="text-muted">{{ $partners->count() }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Partners -->
            <div class="panel panel-icon" id="panel-top">
                <div class="panel-hdr">
                    <h2>Top Partners <span class="fw-light"><i>By Pipeline Value</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        @php
                            $topPartners = $partners->sortByDesc(function($partner) {
                                return $partner->opportunities->sum('amount');
                            })->take(5);
                        @endphp

                        @if($topPartners->isEmpty())
                            <div class="alert alert-info border-0 mb-0">
                                <i class="fal fa-info-circle me-1"></i> No partner data available.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover m-0">
                                    <thead>
                                        <tr>
                                            <th>Partner</th>
                                            <th>Projects</th>
                                            <th>Opportunities</th>
                                            <th>Resources</th>
                                            <th>Pipeline Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topPartners as $partner)
                                        <tr>
                                            <td>
                                                <a href="{{ route('partners.summary', $partner->id) }}" class="text-primary">
                                                    {{ $partner->name }}
                                                </a>
                                            </td>
                                            <td>{{ $partner->projects->count() }}</td>
                                            <td>{{ $partner->opportunities->count() }}</td>
                                            <td>{{ $partner->resources->count() }}</td>
                                            <td>${{ number_format($partner->opportunities->sum('amount')) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <!-- Stage Distribution -->
            <div class="panel panel-icon" id="panel-stage">
                <div class="panel-hdr">
                    <h2>Sales Stage <span class="fw-light"><i>Distribution</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        @if($stageDistribution->isEmpty())
                            <div class="alert alert-info border-0 mb-0">
                                <i class="fal fa-info-circle me-1"></i> No opportunity data available.
                            </div>
                        @else
                            @foreach($stageDistribution as $stage => $count)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-500">{{ $stage ?: 'Unknown' }}</small>
                                    <small class="text-muted">{{ $count }} {{ Str::plural('opp', $count) }}</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    @php
                                        $percentage = $totalOpportunities > 0 ? ($count / $totalOpportunities) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-primary-700" role="progressbar" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="panel panel-icon" id="panel-actions">
                <div class="panel-hdr">
                    <h2>Quick <span class="fw-light"><i>Actions</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="d-grid gap-2">
                            <a href="{{ route('partners.my') }}" class="btn btn-primary">
                                <i class="fal fa-handshake me-1"></i> View All Partners
                            </a>
                            <a href="{{ route('analysis.index') }}" class="btn btn-info">
                                <i class="fal fa-chart-line me-1"></i> Analysis & Insights
                            </a>
                            <a href="{{ route('imports.index') }}" class="btn btn-secondary">
                                <i class="fal fa-upload me-1"></i> Import Data
                            </a>
                        </div>
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
    // Prepare data from controller
    const opportunityData = @json($opportunityHistory);
    const resourceData = @json($resourceHistory);

    // Shared chart theme options
    const chartTheme = {
        fontFamily: 'inherit',
        toolbar: { show: false },
        parentHeightOffset: 0
    };

    const gridStyle = {
        borderColor: 'var(--bs-border-color)',
        strokeDashArray: 3,
        yaxis: { lines: { show: true } },
        xaxis: { lines: { show: false } }
    };

    const tooltipStyle = {
        theme: 'dark',
        shared: true,
        intersect: false
    };

    // Extract dates and values for opportunity chart
    const oppDates = opportunityData.map(item => {
        const date = new Date(item.snapshot_date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    const oppCounts = opportunityData.map(item => parseInt(item.opportunity_count) || 0);
    const nacvValues = opportunityData.map(item => parseFloat(item.total_amount) / 1000 || 0);
    const closedWonCounts = opportunityData.map(item => parseInt(item.closed_won_count) || 0);

    // Extract dates and values for resource chart
    const resDates = resourceData.map(item => {
        const date = new Date(item.snapshot_date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    const totalResources = resourceData.map(item => item.total_resources);
    const certifiedResources = resourceData.map(item => item.certified_resources);

    // Opportunity Pipeline Chart
    if (document.getElementById('opportunityChart')) {
        new ApexCharts(document.getElementById('opportunityChart'), {
            series: [{
                name: 'Total Opportunities',
                data: oppCounts
            }],
            chart: Object.assign({ height: 300, type: 'area' }, chartTheme),
            colors: ['#28a745'],
            stroke: { width: 2, curve: 'smooth' },
            fill: { type: 'solid', opacity: 0.1 },
            markers: { size: 3, strokeWidth: 2, hover: { sizeOffset: 2 } },
            xaxis: {
                categories: oppDates,
                labels: { style: { fontSize: '11px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { fontSize: '11px' }, formatter: function(val) { return Math.round(val); } }
            },
            legend: { show: true, position: 'bottom', fontSize: '13px', fontFamily: 'inherit' },
            grid: gridStyle,
            tooltip: tooltipStyle
        }).render();
    }

    // nACV Chart
    if (document.getElementById('nacvChart')) {
        new ApexCharts(document.getElementById('nacvChart'), {
            series: [{
                name: 'Pipeline Value ($K)',
                data: nacvValues
            }],
            chart: Object.assign({ height: 300, type: 'bar' }, chartTheme),
            colors: ['rgba(102, 126, 234, 0.7)'],
            plotOptions: { bar: { columnWidth: '60%', borderRadius: 2 } },
            dataLabels: { enabled: false },
            xaxis: {
                categories: oppDates,
                labels: { style: { fontSize: '11px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px' },
                    formatter: function(val) { return '$' + Math.round(val) + 'K'; }
                }
            },
            legend: { show: true, position: 'bottom', fontSize: '13px', fontFamily: 'inherit' },
            grid: gridStyle,
            tooltip: {
                theme: 'dark',
                y: { formatter: function(val) { return '$' + Math.round(val) + 'K'; } }
            }
        }).render();
    }

    // Resource Growth Chart
    if (document.getElementById('resourceChart')) {
        new ApexCharts(document.getElementById('resourceChart'), {
            series: [
                { name: 'Total Resources', data: totalResources },
                { name: 'Certified Resources', data: certifiedResources }
            ],
            chart: Object.assign({ height: 300, type: 'area' }, chartTheme),
            colors: ['#17a2b8', '#28a745'],
            stroke: { width: 2, curve: 'smooth' },
            fill: { type: 'solid', opacity: 0.1 },
            markers: { size: 3, strokeWidth: 2, hover: { sizeOffset: 2 } },
            xaxis: {
                categories: resDates,
                labels: { style: { fontSize: '11px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { fontSize: '11px' }, formatter: function(val) { return Math.round(val); } }
            },
            legend: { show: true, position: 'bottom', fontSize: '13px', fontFamily: 'inherit' },
            grid: gridStyle,
            tooltip: tooltipStyle
        }).render();
    }

    // Win Rate Chart
    if (document.getElementById('winRateChart')) {
        new ApexCharts(document.getElementById('winRateChart'), {
            series: [{
                name: 'Closed Won',
                data: closedWonCounts
            }],
            chart: Object.assign({ height: 300, type: 'area' }, chartTheme),
            colors: ['#ffc107'],
            stroke: { width: 2, curve: 'smooth' },
            fill: { type: 'solid', opacity: 0.1 },
            markers: { size: 3, strokeWidth: 2, hover: { sizeOffset: 2 } },
            xaxis: {
                categories: oppDates,
                labels: { style: { fontSize: '11px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { fontSize: '11px' }, formatter: function(val) { return Math.round(val); } }
            },
            legend: { show: true, position: 'bottom', fontSize: '13px', fontFamily: 'inherit' },
            grid: gridStyle,
            tooltip: tooltipStyle
        }).render();
    }
});
</script>
@endsection
