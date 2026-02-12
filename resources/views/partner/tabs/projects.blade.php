{{-- Projects Tab: Project Risk + Project Table --}}

<div class="row">
    {{-- Project Risk Chart --}}
    <div class="col-lg-6">
        <div class="panel panel-icon" id="panel-project-risk">
            <div class="panel-hdr">
                <h2> Project <span class="fw-light"><i>Risk</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="projects-risk-chart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Project Summary --}}
    <div class="col-lg-6">
        <div class="panel panel-icon" id="panel-project-summary">
            <div class="panel-hdr">
                <h2> Project <span class="fw-light"><i>Summary</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    @php
                        $activeCount = $projects->whereNotIn('status', ['Completed'])->count();
                        $completedCount = $projects->where('status', 'Completed')->count();
                    @endphp
                    <div class="row text-center mb-4">
                        <div class="col-4">
                            <div class="h2 mb-0 fw-500 text-primary">{{ $projects->count() }}</div>
                            <div class="text-muted fs-sm">Total Projects</div>
                        </div>
                        <div class="col-4">
                            <div class="h2 mb-0 fw-500 text-info">{{ $activeCount }}</div>
                            <div class="text-muted fs-sm">Active</div>
                        </div>
                        <div class="col-4">
                            <div class="h2 mb-0 fw-500 text-success">{{ $completedCount }}</div>
                            <div class="text-muted fs-sm">Completed</div>
                        </div>
                    </div>
                    <hr>
                    <div class="fs-sm">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total ACV</span>
                            <span class="fw-500">${{ number_format($projects->sum('acv'), 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Active ACV</span>
                            <span class="fw-500">${{ number_format($projects->whereNotIn('status', ['Completed'])->sum('acv'), 0) }}</span>
                        </div>
                        @php $statusGroups = $projects->groupBy('status')->map->count(); @endphp
                        @foreach($statusGroups as $status => $count)
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ $status ?: 'Unknown' }}</span>
                            <span class="fw-500">{{ $count }}</span>
                        </div>
                        @endforeach
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
                                    <td class="text-nowrap">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M j, Y') : '-' }}</td>
                                    <td class="text-nowrap">{{ $project->go_live_date ? \Carbon\Carbon::parse($project->go_live_date)->format('M j, Y') : '-' }}</td>
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

    // ── Project Risk (horizontal bar centered at 1.0) ──
    var projectRisk = @json($projectRisk);
    var riskLabels = Object.keys(projectRisk);
    var riskValues = Object.values(projectRisk);
    if (riskLabels.length > 0) {
        var shiftedValues = riskValues.map(function(v) { return +(v - 1).toFixed(2); });
        var maxAbs = Math.max.apply(null, shiftedValues.map(function(v) { return Math.abs(v); }));
        var axisLimit = Math.max(Math.ceil(maxAbs * 10) / 10, 0.5);

        new ApexCharts(document.querySelector('#projects-risk-chart'), {
            chart: { type: 'bar', height: 350, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Risk Score', data: shiftedValues }],
            xaxis: {
                categories: riskLabels,
                labels: { style: { colors: bodyColorHex, fontSize: '12px' }, formatter: function(val) { return (val + 1).toFixed(1); } }
            },
            yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '12px' } } },
            plotOptions: {
                bar: {
                    horizontal: true, barHeight: '60%', borderRadius: 4,
                    colors: { ranges: [
                        { from: -100, to: -0.001, color: '#e74c3c' },
                        { from: 0, to: 100, color: '#28a745' }
                    ] }
                }
            },
            annotations: { xaxis: [{ x: 0, borderColor: bodyColorHex, strokeDashArray: 0, label: { text: '1.0', position: 'top', style: { color: bodyColorHex, fontSize: '11px', background: 'transparent' } } }] },
            dataLabels: { enabled: true, formatter: function(val) { return (val + 1).toFixed(2); }, style: { fontSize: '12px' } },
            grid: { borderColor: bodyColor01, strokeDashArray: 3 },
            tooltip: { theme: 'dark', y: { formatter: function(val) { return (val + 1).toFixed(2); } } }
        }).render();
    } else {
        document.querySelector('#projects-risk-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-shield-alt fa-2x mb-2 opacity-50"></i><p>No project risk data yet</p></div>';
    }

    // SmartTables for project list
    if (window.SmartTables && document.querySelector('#projects-tab-table tbody tr td:not([colspan])')) {
        new window.SmartTables('projects-tab-table', {
            perPage: 15,
            sortable: true,
            responsive: { enabled: true, breakpoint: 768, columnPriorities: { 0: 1, 2: 2, 1: 3, 4: 4, 5: 5, 3: 6 } }
        });
    }
})();
</script>
