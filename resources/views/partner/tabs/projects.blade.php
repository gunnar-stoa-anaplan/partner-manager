{{-- Projects Tab: Dual Risk Charts + Project Summary + Project Table --}}

<div class="row">
    {{-- Active Project Risk Chart --}}
    <div class="col-lg-6">
        <div class="panel panel-icon" id="panel-active-risk">
            <div class="panel-hdr">
                <h2> Active Project <span class="fw-light"><i>Risk</i></span></h2>
                <div class="panel-toolbar">
                    <span class="badge bg-info bg-opacity-25 text-info fs-nano">In Progress & On Hold</span>
                </div>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="active-risk-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Future Project Risk Chart --}}
    <div class="col-lg-6">
        <div class="panel panel-icon" id="panel-upcoming-risk">
            <div class="panel-hdr">
                <h2> Future Project <span class="fw-light"><i>Risk</i></span></h2>
                <div class="panel-toolbar">
                    <span class="badge bg-warning bg-opacity-25 text-warning fs-nano">Stage 3+</span>
                </div>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="upcoming-risk-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Project Summary --}}
    <div class="col-12">
        <div class="panel panel-icon" id="panel-project-summary">
            <div class="panel-hdr">
                <h2> Project <span class="fw-light"><i>Summary</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    @php
                        $activeCount = $projects->whereIn('status', ['In Progress', 'Active'])->count();
                        $completedCount = $projects->where('status', 'Completed')->count();
                        $draftCount = $projects->where('status', 'Draft')->count();
                        $onHoldCount = $projects->where('status', 'On Hold')->count();
                    @endphp
                    <div class="row text-center mb-3">
                        <div class="col">
                            <div class="h2 mb-0 fw-500 text-primary">{{ $projects->count() }}</div>
                            <div class="text-muted fs-sm">Total</div>
                        </div>
                        <div class="col">
                            <div class="h2 mb-0 fw-500 text-info">{{ $activeCount }}</div>
                            <div class="text-muted fs-sm">In Progress</div>
                        </div>
                        <div class="col">
                            <div class="h2 mb-0 fw-500 text-success">{{ $completedCount }}</div>
                            <div class="text-muted fs-sm">Completed</div>
                        </div>
                        <div class="col">
                            <div class="h2 mb-0 fw-500 text-secondary">{{ $draftCount }}</div>
                            <div class="text-muted fs-sm">Planning</div>
                        </div>
                        <div class="col">
                            <div class="h2 mb-0 fw-500 text-warning">{{ $onHoldCount }}</div>
                            <div class="text-muted fs-sm">On Hold</div>
                        </div>
                    </div>
                    <hr>
                    <div class="row fs-sm">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total ACV</span>
                                <span class="fw-500">${{ number_format($projects->sum('acv'), 0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Active ACV</span>
                                <span class="fw-500">${{ number_format($projects->whereIn('status', ['In Progress', 'Active'])->sum('acv'), 0) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @php $areaGroups = $projects->groupBy(fn ($p) => $p->product_area ?? 'Other')->sortKeys(); @endphp
                            @foreach($areaGroups as $area => $areaProjects)
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">{{ $area }}</span>
                                <span class="fw-500">{{ $areaProjects->count() }} projects</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Projects Table --}}
<div class="row">
    <div class="col-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Projects ({{ $projects->count() }})</div>
            </div>
            <div class="card-body">
                @if($projects->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fal fa-project-diagram fa-2x mb-2 opacity-50"></i>
                        <p>No projects found for this partner.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover table-striped mb-0" id="projects-tab-table">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Status</th>
                                    <th class="text-end">ACV</th>
                                    <th>Product Area</th>
                                    <th>Start Date</th>
                                    <th>Go Live</th>
                                    <th class="text-center">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projects as $project)
                                <tr>
                                    <td class="fw-500">
                                        <a href="{{ route('projects.show', $project->id) }}" class="text-decoration-none">
                                            {{ $project->name }}
                                        </a>
                                    </td>
                                    <td>
                                        @php
                                            $statusColor = match($project->status) {
                                                'Completed' => 'success',
                                                'Active', 'In Progress' => 'info',
                                                'At Risk' => 'danger',
                                                'On Hold' => 'warning',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }} opacity-75">{{ $project->status ?? 'Unknown' }}</span>
                                    </td>
                                    <td class="text-end text-nowrap">${{ number_format($project->acv ?? 0, 0) }}</td>
                                    <td>{{ $project->product_area ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $project->start_date?->format('M j, Y') ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $project->go_live_date?->format('M j, Y') ?? '-' }}</td>
                                    <td class="text-center" style="min-width:80px">
                                        @if($project->status !== 'Draft')
                                        <div class="progress" style="height:6px">
                                            <div class="progress-bar bg-{{ $project->percent_complete >= 100 ? 'success' : 'info' }}" style="width:{{ $project->percent_complete }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $project->percent_complete }}%</small>
                                        @else
                                        <span class="text-muted fs-nano">—</span>
                                        @endif
                                    </td>
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

{{-- Chart Scripts --}}
<script>
(function() {
    var cm = window.colorMap || {};
    var bv = (cm.bootstrapVars && cm.bootstrapVars.bodyColor) || null;
    var bodyColorHex = bv ? bv.hex : '#6c757d';
    var bodyColor01 = bv ? bv.rgba(0.1) : 'rgba(108,117,125,0.1)';

    function renderRiskChart(selector, riskData, emptyMessage) {
        var labels = Object.keys(riskData);
        var values = Object.values(riskData);
        if (labels.length === 0) {
            document.querySelector(selector).innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-shield-alt fa-2x mb-2 opacity-50"></i><p>' + emptyMessage + '</p></div>';
            return;
        }
        var shifted = values.map(function(v) { return +(v - 1).toFixed(2); });
        var maxAbs = Math.max.apply(null, shifted.map(function(v) { return Math.abs(v); }));
        var axisLimit = Math.max(Math.ceil(maxAbs * 10) / 10, 0.5);

        new ApexCharts(document.querySelector(selector), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Readiness', data: shifted }],
            xaxis: {
                categories: labels,
                labels: { style: { colors: bodyColorHex, fontSize: '11px' }, formatter: function(val) { return (val + 1).toFixed(1); } }
            },
            yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
            plotOptions: {
                bar: {
                    horizontal: true, barHeight: '55%', borderRadius: 4,
                    colors: { ranges: [
                        { from: -100, to: -0.001, color: '#e74c3c' },
                        { from: 0, to: 100, color: '#28a745' }
                    ] }
                }
            },
            annotations: { xaxis: [{ x: 0, borderColor: bodyColorHex, strokeDashArray: 0, label: { text: '1.0', position: 'top', style: { color: bodyColorHex, fontSize: '10px', background: 'transparent' } } }] },
            dataLabels: { enabled: true, formatter: function(val) { return (val + 1).toFixed(2); }, style: { fontSize: '11px' } },
            grid: { borderColor: bodyColor01, strokeDashArray: 3 },
            tooltip: { theme: 'dark', y: { formatter: function(val) { return 'Ratio: ' + (val + 1).toFixed(2); } } }
        }).render();
    }

    renderRiskChart('#active-risk-chart', @json($activeRisk), 'No active project risk data');
    renderRiskChart('#upcoming-risk-chart', @json($upcomingRisk), 'No upcoming pipeline risk data');

    // SmartTables for project list
    if (window.SmartTables && document.querySelector('#projects-tab-table tbody tr td:not([colspan])')) {
        new window.SmartTables('projects-tab-table', {
            perPage: 15,
            sortable: true,
            responsive: { enabled: true, breakpoint: 768, columnPriorities: { 0: 1, 2: 2, 1: 3, 3: 4, 5: 5, 4: 6, 6: 7 } }
        });
    }
})();
</script>
