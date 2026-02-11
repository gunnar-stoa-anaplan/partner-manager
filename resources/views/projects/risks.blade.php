@extends('layouts.vertical', ['pageTitle' => 'Project Risks'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Project Risks', 'pageSubTitle1' => 'Project Health', 'pageSubText' => 'Risk analysis across active projects'])
@endsection

@section('content')

    {{-- Summary Cards --}}
    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-danger-300 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ $criticalCount }}</h3>
                    <span class="m-0 l-h-n">Critical Risk</span>
                </div>
                <i class="fal fa-exclamation-triangle position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-400 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ $highCount }}</h3>
                    <span class="m-0 l-h-n">High Risk</span>
                </div>
                <i class="fal fa-exclamation-circle position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-400 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ $totalAtRisk }}</h3>
                    <span class="m-0 l-h-n">Projects at Risk</span>
                </div>
                <i class="fal fa-shield position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-500 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">
                        ${{ $totalExposedAcv >= 1000000 ? number_format($totalExposedAcv / 1000000, 1) . 'M' : number_format($totalExposedAcv / 1000, 0) . 'k' }}
                    </h3>
                    <span class="m-0 l-h-n">Exposed ACV</span>
                </div>
                <i class="fal fa-dollar-sign position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-g">
        <div class="col-lg-3">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Risk Distribution</div></div>
                <div class="card-body"><div id="chart-risk-dist" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Risk Types</div></div>
                <div class="card-body"><div id="chart-risk-types" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Avg Risk by Partner</div></div>
                <div class="card-body"><div id="chart-risk-partner" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">At-Risk ACV by Partner</div></div>
                <div class="card-body"><div id="chart-risk-acv" style="height:280px"></div></div>
            </div>
        </div>
    </div>

    {{-- Projects Risk Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">All Active Projects — Risk Assessment</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="risk-table" class="table table-bordered table-hover table-striped w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Partner</th>
                                    <th class="text-center">Risk Score</th>
                                    <th>Level</th>
                                    <th>Completion</th>
                                    <th class="text-end">ACV</th>
                                    <th>Status</th>
                                    <th>Risk Factors</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riskedProjects as $project)
                                <tr>
                                    <td>
                                        <a href="{{ route('projects.show', $project) }}" class="fw-500">{{ $project->name }}</a>
                                    </td>
                                    <td>
                                        <a href="{{ route('partners.show', $project->partner_id) }}" class="text-muted">{{ $project->partner->name ?? '-' }}</a>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center">
                                            @php
                                                $scoreColor = $project->risk_score >= 60 ? 'danger' : ($project->risk_score >= 35 ? 'warning' : ($project->risk_score >= 15 ? 'info' : 'success'));
                                            @endphp
                                            <div class="progress flex-grow-1 me-2" style="height:8px; max-width:80px">
                                                <div class="progress-bar bg-{{ $scoreColor }}" style="width:{{ $project->risk_score }}%"></div>
                                            </div>
                                            <span class="fw-500 text-{{ $scoreColor }}">{{ $project->risk_score }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $levelColor = match($project->risk_level) {
                                                'Critical' => 'danger',
                                                'High' => 'warning',
                                                'Medium' => 'info',
                                                default => 'success'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $levelColor }}">{{ $project->risk_level }}</span>
                                    </td>
                                    <td style="min-width:130px">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1 me-2">
                                                @php
                                                    $pct = $project->percent_complete;
                                                    $barColor = $pct >= 75 ? 'success' : ($pct >= 40 ? 'primary' : ($pct >= 15 ? 'warning' : 'danger'));
                                                @endphp
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-{{ $barColor }}" style="width:{{ $pct }}%"></div>
                                                </div>
                                            </div>
                                            <small class="text-muted fw-500">{{ $pct }}%</small>
                                        </div>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @if($project->acv)
                                            ${{ number_format($project->acv, 0) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'Draft' => 'secondary',
                                                'In Progress' => 'primary',
                                                'On Hold' => 'warning',
                                                'planning' => 'secondary',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$project->status] ?? 'secondary' }}">{{ ucfirst($project->status) }}</span>
                                    </td>
                                    <td>
                                        @if(count($project->risks) > 0)
                                            @foreach($project->risks as $risk)
                                                @php
                                                    $sevColor = match($risk['severity']) {
                                                        'critical' => 'danger',
                                                        'high' => 'warning',
                                                        'medium' => 'info',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $sevColor }} mb-1" title="{{ $risk['detail'] }}">
                                                    {{ $risk['type'] }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-success"><i class="fal fa-check-circle"></i> Clear</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="mb-3"><i class="fal fa-shield-check fa-3x text-success opacity-50"></i></div>
                                        <h5 class="text-muted">No active projects</h5>
                                        <p class="text-muted mb-0">All projects are completed or there are no projects assigned.</p>
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

    {{-- Risk Details Expandable --}}
    @if($riskedProjects->where('risk_score', '>=', 35)->count() > 0)
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#alert-triangle"></use></svg>
                High & Critical Risk Details
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>
    <div class="row">
        @foreach($riskedProjects->where('risk_score', '>=', 35)->take(12) as $project)
        <div class="col-lg-4 col-md-6 mb-g">
            <div class="card shadow-0 h-100 border-left-{{ $project->risk_level === 'Critical' ? 'danger' : 'warning' }}" style="border-left: 3px solid;">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a>
                    </div>
                    <span class="badge bg-{{ $project->risk_level === 'Critical' ? 'danger' : 'warning' }}">{{ $project->risk_score }}</span>
                </div>
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between text-muted fs-sm mb-2">
                        <span>{{ $project->partner->name ?? '-' }}</span>
                        @if($project->acv)
                        <span>${{ number_format($project->acv, 0) }}</span>
                        @endif
                    </div>
                    <div class="progress mb-3" style="height:6px">
                        <div class="progress-bar bg-{{ $project->percent_complete >= 50 ? 'primary' : 'danger' }}" style="width:{{ $project->percent_complete }}%"></div>
                    </div>
                    @foreach($project->risks as $risk)
                    <div class="d-flex align-items-start mb-1">
                        @php
                            $sevIcon = match($risk['severity']) {
                                'critical' => 'fa-times-circle text-danger',
                                'high' => 'fa-exclamation-triangle text-warning',
                                'medium' => 'fa-info-circle text-info',
                                default => 'fa-check-circle text-success'
                            };
                        @endphp
                        <i class="fal {{ $sevIcon }} me-2 mt-1"></i>
                        <small class="text-muted">
                            <strong>{{ $risk['type'] }}:</strong> {{ $risk['detail'] }}
                        </small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

@endsection

@section('scripts')
    @vite(['resources/scripts/pages/table-init.js'])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var cm = window.colorMap || {};
        var pri = cm.primary || {};
        var suc = cm.success || {};
        var inf = cm.info || {};
        var wrn = cm.warning || {};
        var dng = cm.danger || {};
        var bsVars = cm.bootstrapVars || {};
        var bodyColorHex = (bsVars.bodyColor || {}).hex || '#666';
        var bodyColor01 = (bsVars.bodyColor || { rgba: function(){ return 'rgba(0,0,0,0.1)'; }}).rgba(0.1);

        function fmtCurrency(val) {
            if (val >= 1000000) return '$' + (val / 1000000).toFixed(1) + 'M';
            if (val >= 1000) return '$' + Math.round(val / 1000) + 'k';
            return '$' + val;
        }

        function emptyState(el) {
            document.querySelector(el).innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }

        // SmartTables
        if (window.SmartTables && document.querySelector('#risk-table tbody tr td:not([colspan])')) {
            new window.SmartTables('risk-table', {
                perPage: 15,
                sortable: true,
                responsive: {
                    enabled: true,
                    breakpoint: 768,
                    columnPriorities: { 0: 1, 2: 2, 3: 3, 7: 4, 4: 5, 5: 6, 1: 7, 6: 8 }
                }
            });
        }

        // Risk Distribution (Donut)
        var rdData = @json($riskDistribution);
        var rdLabels = Object.keys(rdData);
        var rdValues = Object.values(rdData).map(Number);
        var rdColorMap = {
            Critical: (dng[500] || {}).hex || '#fd3995',
            High: (wrn[500] || {}).hex || '#ffc241',
            Medium: (inf[500] || {}).hex || '#33b5e5',
            Low: (suc[500] || {}).hex || '#1dc9b7'
        };
        if (rdValues.some(function(v){ return v > 0; })) {
            new ApexCharts(document.querySelector('#chart-risk-dist'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: rdValues,
                labels: rdLabels,
                colors: rdLabels.map(function(l){ return rdColorMap[l] || '#868e96'; }),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Projects' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-risk-dist'); }

        // Risk Types (Donut)
        var rtData = @json($riskTypeBreakdown);
        var rtLabels = Object.keys(rtData);
        var rtValues = Object.values(rtData).map(Number);
        var rtColors = [
            (dng[500] || {}).hex || '#fd3995',
            (wrn[500] || {}).hex || '#ffc241',
            (pri[500] || {}).hex || '#886ab5',
            (inf[500] || {}).hex || '#33b5e5',
            (suc[500] || {}).hex || '#1dc9b7',
            (pri[300] || {}).hex || '#B09AD5',
            (dng[300] || {}).hex || '#FE83BE'
        ];
        if (rtLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-risk-types'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: rtValues,
                labels: rtLabels,
                colors: rtColors.slice(0, rtLabels.length),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Issues' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-risk-types'); }

        // Avg Risk by Partner (Horizontal Bar)
        var rpData = @json($riskByPartner);
        var rpLabels = Object.keys(rpData);
        var rpValues = Object.values(rpData).map(Number);
        if (rpLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-risk-partner'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Avg Risk Score', data: rpValues }],
                plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 3, distributed: true } },
                colors: rpValues.map(function(v){ return v >= 60 ? ((dng[500]||{}).hex||'#fd3995') : v >= 35 ? ((wrn[500]||{}).hex||'#ffc241') : ((inf[500]||{}).hex||'#33b5e5'); }),
                xaxis: { labels: { style: { colors: bodyColorHex } }, max: 100 },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '10px' } } },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else { emptyState('#chart-risk-partner'); }

        // At-Risk ACV by Partner (Horizontal Bar)
        var raData = @json($atRiskAcvByPartner);
        var raLabels = Object.keys(raData);
        var raValues = Object.values(raData).map(Number);
        if (raLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-risk-acv'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'At-Risk ACV', data: raValues }],
                plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 3, distributed: true } },
                colors: [
                    (dng[500] || {}).hex || '#fd3995',
                    (wrn[500] || {}).hex || '#ffc241',
                    (pri[500] || {}).hex || '#886ab5',
                    (inf[500] || {}).hex || '#33b5e5',
                    (dng[300] || {}).hex || '#FE83BE',
                    (wrn[300] || {}).hex || '#FFD980',
                    (pri[300] || {}).hex || '#B09AD5',
                    (inf[300] || {}).hex || '#70CCF0',
                    (suc[500] || {}).hex || '#1dc9b7',
                    (suc[300] || {}).hex || '#5EDBC9'
                ],
                xaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' }, formatter: fmtCurrency } },
                yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '10px' } } },
                legend: { show: false },
                dataLabels: { enabled: true, formatter: fmtCurrency, style: { fontSize: '10px', colors: ['#fff'] } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark', y: { formatter: function(v){ return '$' + Math.round(v).toLocaleString(); } } }
            }).render();
        } else { emptyState('#chart-risk-acv'); }
    });
    </script>
@endsection
