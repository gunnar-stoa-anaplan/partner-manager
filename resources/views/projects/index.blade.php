@extends('layouts.vertical', ['pageTitle' => 'Projects'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Projects', 'pageSubTitle1' => 'Partner Manager', 'pageSubText' => 'Client projects across your partner portfolio'])
@endsection

@section('content')
    {{-- Summary Cards --}}
    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $projects->count() }}
                    </h3>
                    <span class="m-0 l-h-n">Total Projects</span>
                </div>
                <i class="fal fa-clipboard position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $activeCount }}
                    </h3>
                    <span class="m-0 l-h-n">Active Projects</span>
                </div>
                <i class="fal fa-bolt position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        {{ $avgCompletion }}%
                    </h3>
                    <span class="m-0 l-h-n">Avg Completion</span>
                </div>
                <i class="fal fa-chart-pie position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-400 rounded overflow-hidden position-relative text-white">
                <div class="">
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        ${{ $totalAcv >= 1000000 ? number_format($totalAcv / 1000000, 1) . 'M' : number_format($totalAcv / 1000, 0) . 'k' }}
                    </h3>
                    <span class="m-0 l-h-n">Total ACV</span>
                </div>
                <i class="fal fa-dollar-sign position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 6rem;"></i>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-g">
        {{-- Status Distribution --}}
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">Status Distribution</div>
                </div>
                <div class="card-body">
                    <div id="status-chart" style="height: 280px;"></div>
                </div>
            </div>
        </div>

        {{-- ACV by Partner --}}
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">ACV by Partner</div>
                </div>
                <div class="card-body">
                    <div id="acv-partner-chart" style="height: 280px;"></div>
                </div>
            </div>
        </div>

        {{-- Completion Distribution --}}
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <div class="card-title">Completion Distribution</div>
                </div>
                <div class="card-body">
                    <div id="completion-chart" style="height: 280px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Projects Table --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">All Projects</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="projects-table" class="table table-bordered table-hover table-striped w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Partner</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th>Completion</th>
                                    <th>Sprint</th>
                                    <th class="text-end">ACV</th>
                                    <th>Kickoff</th>
                                    <th>Team</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $project)
                                    <tr>
                                        <td>
                                            <a href="{{ route('projects.show', $project) }}" class="fw-500">
                                                {{ $project->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('partners.show', $project->partner_id) }}" class="text-muted">
                                                {{ $project->partner->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $project->account->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'Draft' => 'secondary',
                                                    'In Progress' => 'primary',
                                                    'Completed' => 'success',
                                                    'On Hold' => 'warning',
                                                    'planning' => 'secondary',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$project->status] ?? 'secondary' }}">
                                                {{ ucfirst($project->status) }}
                                            </span>
                                        </td>
                                        <td style="min-width: 140px;">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1 me-2">
                                                    <div class="progress progress-sm">
                                                        @php
                                                            $pct = $project->percent_complete;
                                                            $barColor = $pct >= 75 ? 'success' : ($pct >= 40 ? 'primary' : ($pct >= 15 ? 'warning' : 'danger'));
                                                        @endphp
                                                        <div class="progress-bar bg-{{ $barColor }}" role="progressbar"
                                                             style="width: {{ $pct }}%"
                                                             aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                </div>
                                                <small class="text-muted fw-500">{{ $pct }}%</small>
                                            </div>
                                        </td>
                                        <td class="text-center text-nowrap">
                                            @if($project->total_sprints > 0)
                                                {{ $project->current_sprint }}/{{ $project->total_sprints }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap">
                                            @if($project->acv)
                                                ${{ number_format($project->acv, 0) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            {{ $project->start_date ? $project->start_date->format('M j, Y') : '-' }}
                                        </td>
                                        <td>
                                            @php
                                                $teamContacts = $project->contacts->take(4);
                                                $extra = $project->contacts->count() - $teamContacts->count();
                                            @endphp
                                            @if($teamContacts->isNotEmpty())
                                                <div class="d-flex align-items-center">
                                                    @foreach($teamContacts as $contact)
                                                        <span class="d-inline-block rounded-circle bg-primary-100 text-primary text-center fw-500 me-1"
                                                              style="width: 28px; height: 28px; line-height: 28px; font-size: 11px;"
                                                              title="{{ $contact->first_name }} {{ $contact->last_name }}">
                                                            {{ strtoupper(substr($contact->first_name, 0, 1)) }}{{ strtoupper(substr($contact->last_name, 0, 1)) }}
                                                        </span>
                                                    @endforeach
                                                    @if($extra > 0)
                                                        <span class="d-inline-block rounded-circle bg-fusion-100 text-fusion text-center fw-500"
                                                              style="width: 28px; height: 28px; line-height: 28px; font-size: 11px;">
                                                            +{{ $extra }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="mb-3"><i class="fal fa-clipboard fa-3x text-muted opacity-50"></i></div>
                                            <h5 class="text-muted">No projects yet</h5>
                                            <p class="text-muted mb-0">Projects will appear here once they are imported or created for your partners.</p>
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // SmartAdmin theme colors
        var cm = window.colorMap || {};
        var pri = (cm.primary || {});
        var bsVars = (cm.bootstrapVars || {});
        var bodyColorHex = (bsVars.bodyColor || {}).hex || '#666';
        var bodyColor01 = (bsVars.bodyColor || { rgba: function() { return 'rgba(0,0,0,0.1)'; }}).rgba(0.1);

        // SmartTables
        if (window.SmartTables && document.querySelector('#projects-table tbody tr td:not([colspan])')) {
            new window.SmartTables('projects-table', {
                perPage: 15,
                sortable: true,
                responsive: {
                    enabled: true,
                    breakpoint: 768,
                    columnPriorities: { 0: 1, 3: 2, 4: 3, 1: 4, 6: 5, 2: 6, 5: 7, 7: 8, 8: 9 }
                }
            });
        }

        // ── Status Distribution (Donut) ──
        var statusData = @json($statusDistribution);
        var statusLabels = Object.keys(statusData);
        var statusValues = Object.values(statusData);
        var statusColorMap = {
            'Draft': (pri[200] || {}).hex || '#C5B5E5',
            'In Progress': (pri[500] || {}).hex || '#886ab5',
            'Completed': ((cm.success || {})[500] || {}).hex || '#1dc9b7',
            'On Hold': ((cm.warning || {})[500] || {}).hex || '#ffc241',
            'planning': (pri[100] || {}).hex || '#DAD0F5'
        };

        if (statusLabels.length > 0) {
            new ApexCharts(document.querySelector('#status-chart'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: statusValues,
                labels: statusLabels.map(function(s) { return s.charAt(0).toUpperCase() + s.slice(1); }),
                colors: statusLabels.map(function(s) { return statusColorMap[s] || '#868e96'; }),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: {
                    show: true,
                    total: { show: true, label: 'Total', formatter: function(w) { return w.globals.seriesTotals.reduce(function(a,b){return a+b;}, 0); }}
                }}}},
                dataLabels: { enabled: true, style: { fontSize: '12px' } },
                stroke: { width: 2 }
            }).render();
        } else {
            document.querySelector('#status-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }

        // ── ACV by Partner (Horizontal Bar) ──
        var acvData = @json($acvByPartner);
        var acvLabels = Object.keys(acvData);
        var acvValues = Object.values(acvData);

        if (acvLabels.length > 0) {
            new ApexCharts(document.querySelector('#acv-partner-chart'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'ACV', data: acvValues }],
                plotOptions: {
                    bar: { horizontal: true, barHeight: '60%', borderRadius: 3, distributed: true }
                },
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
                    categories: acvLabels,
                    labels: {
                        style: { colors: bodyColorHex, fontSize: '11px' },
                        formatter: function(val) {
                            if (val >= 1000000) return '$' + (val / 1000000).toFixed(1) + 'M';
                            if (val >= 1000) return '$' + Math.round(val / 1000) + 'k';
                            return '$' + val;
                        }
                    }
                },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                legend: { show: false },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        if (val >= 1000000) return '$' + (val / 1000000).toFixed(1) + 'M';
                        if (val >= 1000) return '$' + Math.round(val / 1000) + 'k';
                        return '$' + val;
                    },
                    style: { fontSize: '11px', colors: ['#fff'] }
                },
                grid: { borderColor: bodyColor01 },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: function(val) { return '$' + Math.round(val).toLocaleString(); } }
                }
            }).render();
        } else {
            document.querySelector('#acv-partner-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }

        // ── Completion Distribution (Column) ──
        var completionData = @json($completionBuckets);
        var compLabels = Object.keys(completionData);
        var compValues = Object.values(completionData);

        if (compValues.some(function(v){ return v > 0; })) {
            new ApexCharts(document.querySelector('#completion-chart'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Projects', data: compValues }],
                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, distributed: true } },
                colors: [
                    ((cm.danger || {})[500] || {}).hex || '#fd3995',
                    ((cm.warning || {})[500] || {}).hex || '#ffc241',
                    (pri[500] || {}).hex || '#886ab5',
                    ((cm.success || {})[500] || {}).hex || '#1dc9b7'
                ],
                xaxis: {
                    categories: compLabels,
                    labels: { style: { colors: bodyColorHex, fontSize: '11px' } }
                },
                yaxis: {
                    labels: { style: { colors: bodyColorHex, fontSize: '11px' } },
                    forceNiceScale: true
                },
                legend: { show: false },
                dataLabels: {
                    enabled: true,
                    style: { fontSize: '13px', fontWeight: 600 }
                },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else {
            document.querySelector('#completion-chart').innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }
    });
    </script>
@endsection
