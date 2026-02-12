@extends('layouts.vertical', ['pageTitle' => 'Influence Map'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Influence Map', 'pageSubTitle1' => 'Engage', 'pageSubTitle1Url' => route('welcome'), 'pageSubTitle2' => 'Field Enablement', 'pageSubText' => 'Portfolio-wide influence and engagement dashboard'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The Influence Map gives you a portfolio-wide view of partner engagement and relationship strength. Use the summary cards and trend charts to identify which partners are highly active and which are disengaging — then drill into engagement history, role distribution, and interaction patterns to inform your outreach strategy.</p>
    {{-- Summary Cards --}}
    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $totalInfluencers }}
                    </h3>
                    <span class="m-0 l-h-n">Total Influencers</span>
                </div>
                <i class="fal fa-users position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $activeInfluencers }}
                    </h3>
                    <span class="m-0 l-h-n">Active (on Projects)</span>
                </div>
                <i class="fal fa-user-check position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $totalProjects }}
                    </h3>
                    <span class="m-0 l-h-n">Total Projects</span>
                </div>
                <i class="fal fa-clipboard position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $totalLeads }}
                    </h3>
                    <span class="m-0 l-h-n">Total Leads</span>
                </div>
                <i class="fal fa-bullhorn position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
    </div>

    {{-- Engagement Metrics Panel --}}
    <div class="row mb-g">
        <div class="col-lg-12">
            <div class="card shadow-0">
                <div class="card-header">
                    <div class="card-title">Engagement Overview</div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Engagement Rate</span>
                                    <strong>{{ $engagementRate }}%</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $engagementRate }}%"></div>
                                </div>
                                <small class="text-muted">Contacts with at least 1 project</small>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Deep Engagement</span>
                                    <strong>{{ $deepEngagement }}%</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $deepEngagement }}%"></div>
                                </div>
                                <small class="text-muted">Contacts with 3+ projects</small>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Active Ratio</span>
                                    <strong>{{ $totalInfluencers > 0 ? round(($activeInfluencers / $totalInfluencers) * 100) : 0 }}%</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    @php $activeRatio = $totalInfluencers > 0 ? round(($activeInfluencers / $totalInfluencers) * 100) : 0; @endphp
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $activeRatio }}%"></div>
                                </div>
                                <small class="text-muted">Active vs total influencers</small>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div id="projects-partner-chart" style="height: 280px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-g">
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">Contacts by Partner</div>
                </div>
                <div class="card-body">
                    <div id="contacts-partner-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">Industry Distribution</div>
                </div>
                <div class="card-body">
                    <div id="industry-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">Role Distribution</div>
                </div>
                <div class="card-body">
                    <div id="title-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Geographic Distribution --}}
    @if($geoDistribution->count() > 1)
    <div class="row mb-g">
        <div class="col-lg-12">
            <div class="card shadow-0">
                <div class="card-header">
                    <div class="card-title">Geographic Distribution</div>
                </div>
                <div class="card-body">
                    <div id="geo-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var cm = window.colorMap || {};
        var pri = (cm.primary || {});
        var bsVars = (cm.bootstrapVars || {});
        var bodyColorHex = (bsVars.bodyColor || {}).hex || '#666';
        var bodyColor01 = (bsVars.bodyColor || { rgba: function() { return 'rgba(0,0,0,0.1)'; }}).rgba(0.1);

        // ── Projects by Partner (Horizontal Bar in engagement panel) ──
        var projData = @json($projectsByPartner);
        var projLabels = Object.keys(projData);
        var projValues = Object.values(projData);

        if (projLabels.length > 0) {
            new ApexCharts(document.querySelector('#projects-partner-chart'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Projects', data: projValues }],
                plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 3, distributed: true } },
                colors: [
                    (pri[500] || {}).hex || '#886ab5',
                    (pri[400] || {}).hex || '#9B7FC5',
                    (pri[300] || {}).hex || '#B09AD5',
                    ((cm.info || {})[500] || {}).hex || '#33b5e5',
                    ((cm.success || {})[500] || {}).hex || '#1dc9b7',
                    ((cm.warning || {})[500] || {}).hex || '#ffc241',
                    (pri[600] || {}).hex || '#7559a0',
                    ((cm.danger || {})[500] || {}).hex || '#fd3995',
                    (pri[200] || {}).hex || '#C5B5E5',
                    (pri[100] || {}).hex || '#DAD0F5'
                ],
                xaxis: {
                    categories: projLabels,
                    labels: { style: { colors: bodyColorHex, fontSize: '11px' } }
                },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#fff'] } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else {
            document.querySelector('#projects-partner-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }

        // ── Contacts by Partner (Radar) ──
        var contactsData = @json($contactsByPartner);
        var contactsLabels = Object.keys(contactsData);
        var contactsValues = Object.values(contactsData);

        if (contactsLabels.length > 0) {
            new ApexCharts(document.querySelector('#contacts-partner-chart'), {
                chart: { type: 'radar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Contacts', data: contactsValues }],
                labels: contactsLabels,
                colors: [(pri[500] || {}).hex || '#886ab5'],
                fill: { opacity: 0.2 },
                stroke: { width: 2 },
                markers: { size: 4 },
                yaxis: { show: false },
                xaxis: { labels: { style: { colors: Array(contactsLabels.length).fill(bodyColorHex), fontSize: '11px' } } },
                plotOptions: { radar: { polygons: { strokeColors: bodyColor01, connectorColors: bodyColor01 } } },
                tooltip: { theme: 'dark' }
            }).render();
        } else {
            document.querySelector('#contacts-partner-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }

        // ── Industry Distribution (Donut) ──
        var industryData = @json($industryDistribution);
        var industryLabels = Object.keys(industryData);
        var industryValues = Object.values(industryData);

        if (industryLabels.length > 0) {
            new ApexCharts(document.querySelector('#industry-chart'), {
                chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
                series: industryValues,
                labels: industryLabels,
                colors: [
                    (pri[500] || {}).hex || '#886ab5',
                    ((cm.success || {})[500] || {}).hex || '#1dc9b7',
                    ((cm.info || {})[500] || {}).hex || '#33b5e5',
                    ((cm.warning || {})[500] || {}).hex || '#ffc241',
                    ((cm.danger || {})[500] || {}).hex || '#fd3995',
                    (pri[300] || {}).hex || '#B09AD5',
                    (pri[600] || {}).hex || '#7559a0',
                    ((cm.success || {})[300] || {}).hex || '#5ee0d1'
                ],
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: {
                    show: true,
                    total: { show: true, label: 'Total', formatter: function(w) { return w.globals.seriesTotals.reduce(function(a,b){return a+b;}, 0); }}
                }}}},
                dataLabels: { enabled: true, style: { fontSize: '12px' } },
                stroke: { width: 2 }
            }).render();
        } else {
            document.querySelector('#industry-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }

        // ── Role/Title Distribution (Horizontal Bar) ──
        var titleData = @json($titleDistribution);
        var titleLabels = Object.keys(titleData);
        var titleValues = Object.values(titleData);

        if (titleLabels.length > 0) {
            new ApexCharts(document.querySelector('#title-chart'), {
                chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Contacts', data: titleValues }],
                plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 3, distributed: true } },
                colors: [
                    ((cm.info || {})[500] || {}).hex || '#33b5e5',
                    ((cm.info || {})[400] || {}).hex || '#5cc3ea',
                    ((cm.info || {})[300] || {}).hex || '#78cef0',
                    (pri[500] || {}).hex || '#886ab5',
                    (pri[400] || {}).hex || '#9B7FC5',
                    ((cm.success || {})[500] || {}).hex || '#1dc9b7',
                    ((cm.warning || {})[500] || {}).hex || '#ffc241',
                    ((cm.danger || {})[500] || {}).hex || '#fd3995'
                ],
                xaxis: {
                    categories: titleLabels,
                    labels: { style: { colors: bodyColorHex, fontSize: '11px' } }
                },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#fff'] } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else {
            document.querySelector('#title-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }

        // ── Geographic Distribution (Column) ──
        @if($geoDistribution->count() > 1)
        var geoData = @json($geoDistribution);
        var geoLabels = Object.keys(geoData);
        var geoValues = Object.values(geoData);

        if (geoLabels.length > 0) {
            new ApexCharts(document.querySelector('#geo-chart'), {
                chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Contacts', data: geoValues }],
                plotOptions: { bar: { columnWidth: '50%', borderRadius: 4, distributed: true } },
                colors: [
                    (pri[500] || {}).hex || '#886ab5',
                    ((cm.success || {})[500] || {}).hex || '#1dc9b7',
                    ((cm.info || {})[500] || {}).hex || '#33b5e5',
                    ((cm.warning || {})[500] || {}).hex || '#ffc241',
                    ((cm.danger || {})[500] || {}).hex || '#fd3995',
                    (pri[300] || {}).hex || '#B09AD5',
                    (pri[600] || {}).hex || '#7559a0',
                    ((cm.success || {})[300] || {}).hex || '#5ee0d1'
                ],
                xaxis: {
                    categories: geoLabels,
                    labels: { style: { colors: bodyColorHex, fontSize: '11px' } }
                },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } }, forceNiceScale: true },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        }
        @endif
    });
    </script>
@endsection
