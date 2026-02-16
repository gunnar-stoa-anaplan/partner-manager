@extends('layouts.vertical', ['pageTitle' => 'Field Depth'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Field Depth', 'pageSubTitle1' => 'Engage', 'pageSubTitle1Url' => route('welcome'), 'pageSubTitle2' => 'Field Enablement', 'pageSubTitle2Url' => route('influence_map'), 'pageSubText' => 'Contacts with deep project involvement (3+)'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Depth tracks how deeply your partners have invested in specific areas — looking at contacts with three or more project involvements. Use this view to understand which partners have battle-tested practitioners and which ones need more hands-on experience before they can take on complex engagements independently.</p>
    {{-- Summary Cards --}}
    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $deepCount }}
                    </h3>
                    <span class="m-0 l-h-n">Deep Contacts (3+)</span>
                </div>
                <i class="fal fa-bullseye position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $deepRatio }}%
                    </h3>
                    <span class="m-0 l-h-n">of All Contacts</span>
                </div>
                <i class="fal fa-percentage position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $avgProjectCount }}
                    </h3>
                    <span class="m-0 l-h-n">Avg Projects</span>
                </div>
                <i class="fal fa-chart-bar position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $maxProjectCount }}
                    </h3>
                    <span class="m-0 l-h-n">Max Projects</span>
                </div>
                <i class="fal fa-trophy position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-g">
        <div class="col-lg-6">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">Deep Contacts by Partner</div>
                </div>
                <div class="card-body">
                    <div id="deep-partner-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">Project Count Distribution</div>
                </div>
                <div class="card-body">
                    <div id="depth-spread-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contacts Table --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Deep Contacts (3+ Projects)</div>
                    <span class="badge bg-primary fs-sm">{{ $deepCount }} of {{ $totalPortfolioContacts }} contacts</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="depth-table" class="table table-bordered table-hover table-striped w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Partner</th>
                                    <th>Title</th>
                                    <th>Email</th>
                                    <th class="text-center">Projects</th>
                                    <th>Project Names</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contacts as $contact)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="d-inline-block rounded-circle bg-warning-100 text-warning text-center fw-500 me-2"
                                                      style="width: 30px; height: 30px; line-height: 30px; font-size: 11px;">
                                                    {{ strtoupper(substr($contact->first_name, 0, 1)) }}{{ strtoupper(substr($contact->last_name, 0, 1)) }}
                                                </span>
                                                <strong>{{ $contact->first_name }} {{ $contact->last_name }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            @if($contact->partner)
                                                <a href="{{ route('partners.show', $contact->partner_id) }}" class="text-muted">
                                                    {{ $contact->partner->name }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $contact->title ?? '-' }}</td>
                                        <td>
                                            @if($contact->email)
                                                <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success fs-sm">{{ $contact->projects_count }}</span>
                                        </td>
                                        <td>
                                            @foreach($contact->projects->take(3) as $project)
                                                <a href="{{ route('projects.show', $project) }}" class="badge bg-primary-100 text-primary me-1 mb-1">
                                                    {{ Str::limit($project->name, 25) }}
                                                </a>
                                            @endforeach
                                            @if($contact->projects->count() > 3)
                                                <span class="badge bg-fusion-100 text-fusion">+{{ $contact->projects->count() - 3 }} more</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="mb-3"><i class="fal fa-bullseye fa-3x text-muted opacity-50"></i></div>
                                            <h5 class="text-muted">No deeply involved contacts</h5>
                                            <p class="text-muted mb-0">No contacts are currently assigned to 3 or more projects.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/table-init.js'])
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var cm = window.colorMap || {};
        var pri = (cm.primary || {});
        var bsVars = (cm.bootstrapVars || {});
        var bodyColorHex = (bsVars.bodyColor || {}).hex || '#666';
        var bodyColor01 = (bsVars.bodyColor || { rgba: function() { return 'rgba(0,0,0,0.1)'; }}).rgba(0.1);

        // SmartTables
        if (window.SmartTables && document.querySelector('#depth-table tbody tr td:not([colspan])')) {
            new window.SmartTables('depth-table', {
                perPage: 15,
                sortable: true,
                responsive: {
                    enabled: true,
                    breakpoint: 768,
                    columnPriorities: { 0: 1, 4: 2, 1: 3, 5: 4, 2: 5, 3: 6 }
                }
            });
        }

        // Deep Contacts by Partner (Donut)
        var deepData = @json($deepByPartner);
        var deepLabels = Object.keys(deepData);
        var deepValues = Object.values(deepData);

        if (deepLabels.length > 0) {
            new ApexCharts(document.querySelector('#deep-partner-chart'), {
                chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
                series: deepValues,
                labels: deepLabels,
                colors: [
                    (pri[500] || {}).hex || '#886ab5',
                    ((cm.success || {})[500] || {}).hex || '#1dc9b7',
                    ((cm.info || {})[500] || {}).hex || '#33b5e5',
                    ((cm.warning || {})[500] || {}).hex || '#ffc241',
                    (pri[300] || {}).hex || '#B09AD5',
                    ((cm.danger || {})[500] || {}).hex || '#fd3995',
                    (pri[600] || {}).hex || '#7559a0',
                    (pri[200] || {}).hex || '#C5B5E5',
                    ((cm.info || {})[300] || {}).hex || '#78cef0',
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
            document.querySelector('#deep-partner-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }

        // Depth Spread (Column)
        var spreadData = @json($depthSpread);
        var spreadLabels = Object.keys(spreadData).map(function(k) { return k + ' projects'; });
        var spreadValues = Object.values(spreadData);

        if (spreadValues.length > 0) {
            new ApexCharts(document.querySelector('#depth-spread-chart'), {
                chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Contacts', data: spreadValues }],
                plotOptions: { bar: { columnWidth: '50%', borderRadius: 4, distributed: true } },
                colors: [
                    (pri[300] || {}).hex || '#B09AD5',
                    (pri[400] || {}).hex || '#9B7FC5',
                    (pri[500] || {}).hex || '#886ab5',
                    (pri[600] || {}).hex || '#7559a0',
                    (pri[700] || {}).hex || '#62498b',
                    ((cm.success || {})[500] || {}).hex || '#1dc9b7',
                    ((cm.warning || {})[500] || {}).hex || '#ffc241',
                    ((cm.danger || {})[500] || {}).hex || '#fd3995'
                ],
                xaxis: {
                    categories: spreadLabels,
                    labels: { style: { colors: bodyColorHex, fontSize: '11px' } }
                },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } }, forceNiceScale: true },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else {
            document.querySelector('#depth-spread-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }
    });
    </script>
@endsection
