@extends('layouts.vertical', ['pageTitle' => 'Launch Program Dashboard'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Launch Program Dashboard', 'pageSubTitle1' => 'Partner Enablement', 'pageSubTitle1Url' => route('admin.launch-programs.index'), 'pageSubText' => 'Program analytics, timelines, and partner enrollment metrics'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Track launch program progress, partner enrollment, milestone completion, and date changes across all programs. Use the timeline to visualize program schedules and GA targets.</p>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.launch-programs.dashboard') }}" class="card shadow-0 mb-g">
        <div class="card-body py-2">
            <div class="row align-items-end g-2">
                <div class="col-auto">
                    <label class="form-label small mb-0">Product</label>
                    <select name="product_id" class="form-select form-select-sm" style="min-width: 200px;" onchange="this.form.submit()">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ $filterProduct == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-0">Status</label>
                    <select name="status" class="form-select form-select-sm" style="min-width: 180px;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach(\App\Models\LaunchProgram::STATUSES as $key => $label)
                            <option value="{{ $key }}" {{ $filterStatus === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if($filterProduct || $filterStatus)
                    <div class="col-auto">
                        <a href="{{ route('admin.launch-programs.dashboard') }}" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
                    </div>
                @endif
            </div>
        </div>
    </form>

    {{-- KPI Cards --}}
    <div class="row mb-g">
        @php
            $kpis = [
                ['label' => 'Total Programs', 'value' => $totalPrograms, 'sub' => $activePrograms . ' active', 'icon' => 'zap', 'color' => 'primary'],
                ['label' => 'Active Programs', 'value' => $activePrograms, 'sub' => 'currently running', 'icon' => 'play-circle', 'color' => 'success'],
                ['label' => 'Enrolled Partners', 'value' => $enrolledPartners, 'sub' => 'across all programs', 'icon' => 'users', 'color' => 'info'],
                ['label' => 'Avg Completion', 'value' => $avgCompletion . '%', 'sub' => 'milestone progress', 'icon' => 'chart-pie', 'color' => 'warning'],
                ['label' => 'Feedback', 'value' => $totalFeedback, 'sub' => 'partner responses', 'icon' => 'message-square', 'color' => 'danger'],
            ];
        @endphp
        @foreach($kpis as $kpi)
        <div class="col-sm-6 col-xl mb-g mb-xl-0">
            <div class="card shadow-0">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 small">{{ $kpi['label'] }}</p>
                        <span class="h3 mb-0 fw-500">{{ $kpi['value'] }}</span>
                        <small class="d-block text-muted">{{ $kpi['sub'] }}</small>
                    </div>
                    <div class="width-4 height-4 bg-{{ $kpi['color'] }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fal fa-{{ $kpi['icon'] }} fa-lg text-{{ $kpi['color'] }}"></i>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Programs at Risk --}}
    @if($programsAtRisk->isNotEmpty())
    <div class="card shadow-0 border-danger border-opacity-25 mb-g">
        <div class="card-header bg-danger bg-opacity-10">
            <h5 class="card-title mb-0 text-danger">
                <i class="fal fa-exclamation-triangle me-1"></i>
                Programs at Risk
                <small class="fw-normal text-muted ms-2">Active programs with incomplete milestones</small>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Program</th>
                            <th>PSM Owner</th>
                            <th>GA Date</th>
                            <th>Days Left</th>
                            <th>Milestones</th>
                            <th>Completion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($programsAtRisk as $riskIdx => $risk)
                            <tr class="cursor-pointer" data-bs-toggle="collapse" data-bs-target="#risk-detail-{{ $risk->id }}">
                                <td class="text-center" style="width: 30px;">
                                    <svg class="sa-icon sa-icon-sm text-muted"><use href="/icons/sprite.svg#chevron-down"></use></svg>
                                </td>
                                <td>
                                    <a href="{{ route('admin.launch-programs.show', $risk) }}" class="fw-500" onclick="event.stopPropagation();">{{ $risk->name }}</a>
                                    <small class="d-block text-muted">{{ $risk->product?->name ?? '' }}</small>
                                </td>
                                <td><small>{{ $risk->owner?->name ?? '—' }}</small></td>
                                <td><small>{{ $risk->target_ga_date->format('M j, Y') }}</small></td>
                                <td>
                                    <span class="badge {{ $risk->days_to_ga <= 14 ? 'bg-danger' : ($risk->days_to_ga <= 30 ? 'bg-warning text-dark' : ($risk->days_to_ga <= 60 ? 'bg-info' : 'bg-secondary')) }}">
                                        {{ $risk->days_to_ga }} days
                                    </span>
                                </td>
                                <td>
                                    <small class="fw-500">{{ $risk->completed_milestone_count }}/{{ $risk->milestones->count() }}</small>
                                    @php $incompleteRequired = $risk->incomplete_milestones->where('is_required', true)->count(); @endphp
                                    @if($incompleteRequired > 0)
                                        <span class="badge bg-danger bg-opacity-15 text-danger ms-1" style="font-size: 0.6rem;">{{ $incompleteRequired }} req. pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; min-width: 60px;">
                                            @php $barColor = $risk->computed_completion >= 70 ? 'bg-warning' : ($risk->computed_completion >= 40 ? 'bg-info' : 'bg-danger'); @endphp
                                            <div class="progress-bar {{ $barColor }}" style="width: {{ $risk->computed_completion }}%"></div>
                                        </div>
                                        <small class="fw-500 {{ $risk->computed_completion < 40 ? 'text-danger' : '' }}">{{ $risk->computed_completion }}%</small>
                                    </div>
                                </td>
                            </tr>
                            <tr class="collapse" id="risk-detail-{{ $risk->id }}">
                                <td colspan="7" class="bg-light border-0 py-2 px-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="fw-500 text-danger d-block mb-1">Incomplete Milestones ({{ $risk->incomplete_milestones->count() }})</small>
                                            @foreach($risk->incomplete_milestones as $ms)
                                                <div class="d-flex align-items-center gap-1 mb-1">
                                                    <svg class="sa-icon sa-icon-sm text-muted"><use href="/icons/sprite.svg#circle"></use></svg>
                                                    <small>{{ $ms->title }}</small>
                                                    @if($ms->is_required)
                                                        <span class="badge bg-danger bg-opacity-15 text-danger" style="font-size: 0.55rem;">Required</span>
                                                    @endif
                                                    @if($ms->due_date)
                                                        <small class="text-muted ms-1">Due {{ $ms->due_date->format('M j') }}</small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="col-md-6">
                                            @if($risk->completed_milestone_count > 0)
                                                @php $completedMs = $risk->milestones->diff($risk->incomplete_milestones); @endphp
                                                <small class="fw-500 text-success d-block mb-1">Completed ({{ $completedMs->count() }})</small>
                                                @foreach($completedMs as $ms)
                                                    <div class="d-flex align-items-center gap-1 mb-1">
                                                        <svg class="sa-icon sa-icon-sm text-success"><use href="/icons/sprite.svg#check-circle"></use></svg>
                                                        <small class="text-muted text-decoration-line-through">{{ $ms->title }}</small>
                                                    </div>
                                                @endforeach
                                            @else
                                                <small class="text-muted">No milestones completed yet.</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Program Timeline --}}
    <div class="panel panel-icon mb-g" id="panel-timeline">
        <div class="panel-hdr">
            <h2>Program <span class="fw-light"><i>Timeline</i></span></h2>
        </div>
        <div class="panel-container">
            <div class="panel-content">
                <div id="chart-timeline" style="min-height: 200px;"></div>
                <div class="d-flex flex-wrap gap-3 mt-2 px-3 pb-2 justify-content-center small text-muted">
                    @foreach(\App\Models\LaunchProgram::STATUSES as $sKey => $sLabel)
                        <span><span class="d-inline-block rounded-1" style="width: 12px; height: 12px; background: {{ \App\Models\LaunchProgram::STATUS_COLORS[$sKey] ?? '#868e96' }}; vertical-align: middle;"></span> {{ $sLabel }}</span>
                    @endforeach
                    <span class="mx-1 text-muted">|</span>
                    <span><span class="d-inline-block rounded-1" style="width: 12px; height: 12px; background: #adb5bd; vertical-align: middle;"></span> Not open to partners</span>
                    <span><span class="d-inline-block rounded-1" style="width: 12px; height: 12px; background: #fd7e14; vertical-align: middle;"></span> Open &mdash; no partners enrolled</span>
                    <span class="mx-1 text-muted">|</span>
                    <span><span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background: #dc3545; vertical-align: middle;"></span> Current GA</span>
                    <span><span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background: #f8d7da; border: 1px solid #dc3545; vertical-align: middle;"></span> Previous GA (slipped)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row">
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-program-status">
                <div class="panel-hdr">
                    <h2>Programs by <span class="fw-light"><i>Status</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-programs-status" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-partner-funnel">
                <div class="panel-hdr">
                    <h2>Partner <span class="fw-light"><i>Funnel</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-partner-funnel" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-milestone-types">
                <div class="panel-hdr">
                    <h2>Milestone <span class="fw-light"><i>Types</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-milestone-types" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Trend + Owner Workload Row --}}
    <div class="row">
        <div class="col-lg-7">
            <div class="panel panel-icon mb-g" id="panel-milestone-trend">
                <div class="panel-hdr">
                    <h2>Milestone Completion <span class="fw-light"><i>Trend</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-milestone-trend" style="height: 280px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="panel panel-icon mb-g" id="panel-owner-workload">
                <div class="panel-hdr">
                    <h2>Owner <span class="fw-light"><i>Workload</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-owner-workload" style="height: 280px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Row: Recent Feedback + Date Changes --}}
    <div class="row">
        {{-- Recent Feedback --}}
        <div class="col-lg-6 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Feedback</h5>
                </div>
                <div class="card-body p-0">
                    @if($recentFeedback->isNotEmpty())
                    <div class="list-group list-group-flush">
                        @foreach($recentFeedback as $fb)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span class="fw-500">{{ $fb->contact->full_name ?? 'Unknown' }}</span>
                                        <span class="badge ms-1" style="background-color: {{ \App\Models\LaunchProgramFeedback::TYPE_COLORS[$fb->type] ?? '#868e96' }}; font-size: 0.65rem;">
                                            {{ \App\Models\LaunchProgramFeedback::TYPES[$fb->type] ?? $fb->type }}
                                        </span>
                                        @if($fb->rating)
                                            <span class="ms-1 text-warning">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $fb->rating) &#9733; @else &#9734; @endif
                                                @endfor
                                            </span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $fb->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-0 mt-1 text-muted small">{{ Str::limit($fb->body, 120) }}</p>
                                <small class="text-muted">{{ $fb->programPartner?->program?->name ?? '' }} &middot; {{ $fb->programPartner?->partner?->name ?? '' }}</small>
                            </div>
                        @endforeach
                    </div>
                    @else
                        <div class="text-center text-muted py-4"><small>No feedback yet.</small></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Date Changes --}}
        <div class="col-lg-6 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Date Changes</h5>
                </div>
                <div class="card-body p-0">
                    @if($recentDateChanges->isNotEmpty())
                    <div class="list-group list-group-flush">
                        @foreach($recentDateChanges as $dc)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span class="fw-500">{{ $dc->launchProgram->name ?? 'Unknown' }}</span>
                                        <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">
                                            {{ \App\Models\LaunchProgramDateChange::FIELD_LABELS[$dc->field] ?? $dc->field }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $dc->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="small mt-1">
                                    <span class="text-muted">{{ $dc->old_date?->format('M j, Y') ?? 'Not set' }}</span>
                                    <span class="mx-1">&rarr;</span>
                                    <span class="fw-500">{{ $dc->new_date?->format('M j, Y') ?? 'Cleared' }}</span>
                                </div>
                                @if($dc->reason)
                                    <p class="mb-0 mt-1 text-muted small fst-italic">"{{ $dc->reason }}"</p>
                                @endif
                                <small class="text-muted">by {{ $dc->changedBy->name ?? 'Unknown' }}</small>
                            </div>
                        @endforeach
                    </div>
                    @else
                        <div class="text-center text-muted py-4"><small>No date changes recorded yet.</small></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var cm = window.colorMap || {};
        var bv = (cm.bootstrapVars && cm.bootstrapVars.bodyColor) || null;
        var bodyColorHex = bv ? bv.hex : '#6c757d';

        var dlToolbar = { show: true, tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } };

        function addCopyMenuItem(chartContext) {
            var menuEl = chartContext.el.querySelector('.apexcharts-menu');
            if (!menuEl || menuEl.querySelector('.copy-clipboard-item')) return;
            var copyItem = document.createElement('div');
            copyItem.className = 'apexcharts-menu-item copy-clipboard-item';
            copyItem.textContent = 'Copy';
            copyItem.addEventListener('click', function() {
                chartContext.dataURI({ scale: 2 }).then(function(uri) {
                    fetch(uri.imgURI)
                        .then(function(res) { return res.blob(); })
                        .then(function(blob) {
                            return navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
                        })
                        .then(function() {
                            copyItem.textContent = 'Copied!';
                            setTimeout(function() { copyItem.textContent = 'Copy'; }, 1500);
                        });
                });
                menuEl.classList.remove('apexcharts-menu-open');
            });
            menuEl.appendChild(copyItem);
        }

        var programStatusColors = @json(\App\Models\LaunchProgram::STATUS_COLORS);
        var partnerStatusColors = @json(\App\Models\LaunchProgramPartner::STATUS_COLORS);
        var milestoneTypeColors = @json(\App\Models\LaunchProgramMilestone::TYPE_COLORS);
        var programStatusLabels = @json(\App\Models\LaunchProgram::STATUSES);
        var partnerStatusLabels = @json(\App\Models\LaunchProgramPartner::STATUSES);
        var milestoneTypeLabels = @json(\App\Models\LaunchProgramMilestone::TYPES);

        // ── Program Timeline (rangeBar) ──
        var timelineData = @json($timelinePrograms);
        if (timelineData.length > 0) {
            var series = [{
                data: timelineData.map(function(p) {
                    var color;
                    if (p.partnerStrategy === 'exclude') {
                        color = '#adb5bd';
                    } else if (p.partnerCount === 0) {
                        color = '#fd7e14';
                    } else {
                        color = programStatusColors[p.status] || '#868e96';
                    }
                    return {
                        x: p.name,
                        y: [new Date(p.start).getTime(), new Date(p.end).getTime()],
                        fillColor: color
                    };
                })
            }];

            var annotations = { points: [] };
            timelineData.forEach(function(p) {
                // Ghost markers for previous (slipped) GA dates
                if (p.previousGaDates && p.previousGaDates.length > 0) {
                    p.previousGaDates.forEach(function(oldGa) {
                        annotations.points.push({
                            x: new Date(oldGa).getTime(),
                            y: p.name,
                            marker: { size: 4, fillColor: '#f8d7da', strokeColor: '#dc3545', strokeWidth: 1, shape: 'circle' },
                            label: { text: '', borderWidth: 0 }
                        });
                    });
                }
                // Current GA marker
                if (p.ga) {
                    var hasSlipped = p.previousGaDates && p.previousGaDates.length > 0;
                    annotations.points.push({
                        x: new Date(p.ga).getTime(),
                        y: p.name,
                        marker: { size: 6, fillColor: '#dc3545', strokeColor: '#fff', strokeWidth: 2 },
                        label: {
                            text: hasSlipped ? 'GA (' + p.previousGaDates.length + 'x delayed)' : 'GA',
                            offsetX: -50,
                            offsetY: -1,
                            style: { color: '#fff', background: hasSlipped ? '#b02a37' : '#dc3545', fontSize: '10px', padding: { left: 4, right: 4, top: 2, bottom: 2 } }
                        }
                    });
                }
            });

            new ApexCharts(document.querySelector('#chart-timeline'), {
                chart: { type: 'rangeBar', height: Math.max(200, 60 + (timelineData.length * 50)), toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: series,
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
                xaxis: { type: 'datetime', labels: { style: { colors: bodyColorHex } } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                annotations: annotations,
                tooltip: {
                    custom: function(opts) {
                        var p = timelineData[opts.dataPointIndex];
                        var start = new Date(p.start).toLocaleDateString();
                        var end = new Date(p.end).toLocaleDateString();
                        var ga = p.ga ? new Date(p.ga).toLocaleDateString() : 'Not set';
                        var status = programStatusLabels[p.status] || p.status;
                        var extra = '';
                        if (p.partnerStrategy === 'exclude') {
                            extra = '<br><span style="color:#adb5bd;">&#9679;</span> Not open to partners';
                        } else if (p.partnerCount === 0) {
                            extra = '<br><span style="color:#fd7e14;">&#9679;</span> No partners enrolled yet';
                        } else {
                            extra = '<br>Partners: ' + p.partnerCount;
                        }
                        if (p.previousGaDates && p.previousGaDates.length > 0) {
                            extra += '<br><span style="color:#dc3545;">Delayed ' + p.previousGaDates.length + 'x:</span> ';
                            extra += p.previousGaDates.map(function(d) { return '<s>' + new Date(d).toLocaleDateString() + '</s>'; }).join(' &rarr; ');
                            extra += ' &rarr; ' + ga;
                        }
                        return '<div class="p-2"><strong>' + p.name + '</strong><br>' +
                               'Status: ' + status + '<br>Start: ' + start + '<br>End: ' + end + '<br>GA: ' + ga + extra + '</div>';
                    }
                },
                grid: { borderColor: 'rgba(108,117,125,0.08)', strokeDashArray: 5 },
                legend: { show: false }
            }).render();
        } else {
            document.querySelector('#chart-timeline').innerHTML = '<div class="text-center text-muted py-5"><p>No programs with date ranges to display</p></div>';
        }

        // ── Programs by Status (donut) ──
        var progStatus = @json($programsByStatus);
        var progLabels = Object.keys(progStatus).map(function(s) { return programStatusLabels[s] || s; });
        var progValues = Object.values(progStatus).map(Number);
        var progColors = Object.keys(progStatus).map(function(s) { return programStatusColors[s] || '#868e96'; });
        if (progLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-programs-status'), {
                chart: { type: 'donut', height: 300, toolbar: dlToolbar, events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: progValues,
                labels: progLabels,
                colors: progColors,
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%' } } },
                dataLabels: { enabled: true }
            }).render();
        } else {
            document.querySelector('#chart-programs-status').innerHTML = '<div class="text-center text-muted py-5"><p>No program data</p></div>';
        }

        // ── Partner Nomination Funnel (horizontal bar, ordered) ──
        var funnelOrder = @json($funnelOrder);
        var partStatus = @json($partnersByStatus);
        var funnelLabels = [];
        var funnelValues = [];
        var funnelColors = [];
        funnelOrder.forEach(function(s) {
            if (partStatus[s] !== undefined) {
                funnelLabels.push(partnerStatusLabels[s] || s);
                funnelValues.push(Number(partStatus[s]));
                funnelColors.push(partnerStatusColors[s] || '#868e96');
            }
        });
        if (funnelLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-partner-funnel'), {
                chart: { type: 'bar', height: 300, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: [{ name: 'Partners', data: funnelValues }],
                plotOptions: { bar: { horizontal: true, borderRadius: 4, distributed: true, barHeight: '65%' } },
                colors: funnelColors,
                xaxis: { categories: funnelLabels, labels: { style: { colors: bodyColorHex } } },
                yaxis: { labels: { style: { colors: bodyColorHex, fontWeight: 500 } } },
                dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 600 }, formatter: function(val) { return val; } },
                legend: { show: false },
                grid: { borderColor: 'rgba(108,117,125,0.08)', strokeDashArray: 5 },
                tooltip: { y: { formatter: function(val) { return val + ' partners'; } } }
            }).render();
        } else {
            document.querySelector('#chart-partner-funnel').innerHTML = '<div class="text-center text-muted py-5"><p>No partner enrollment data</p></div>';
        }

        // ── Milestone Types (donut) ──
        var msTypes = @json($milestonesByType);
        var msLabels = Object.keys(msTypes).map(function(t) { return milestoneTypeLabels[t] || t; });
        var msValues = Object.values(msTypes).map(Number);
        var msColors = Object.keys(msTypes).map(function(t) { return milestoneTypeColors[t] || '#868e96'; });
        if (msLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-milestone-types'), {
                chart: { type: 'donut', height: 300, toolbar: dlToolbar, events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: msValues,
                labels: msLabels,
                colors: msColors,
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%' } } },
                dataLabels: { enabled: true }
            }).render();
        } else {
            document.querySelector('#chart-milestone-types').innerHTML = '<div class="text-center text-muted py-5"><p>No milestone data</p></div>';
        }

        // ── Milestone Completion Trend (area chart) ──
        var trendData = @json($milestoneCompletionTrend);
        var trendCategories = Object.keys(trendData);
        var trendValues = Object.values(trendData).map(Number);
        if (trendCategories.length > 0) {
            new ApexCharts(document.querySelector('#chart-milestone-trend'), {
                chart: { type: 'area', height: 280, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: [{ name: 'Milestones Completed', data: trendValues }],
                xaxis: { categories: trendCategories, labels: { style: { colors: bodyColorHex } } },
                yaxis: { labels: { style: { colors: bodyColorHex } }, min: 0, forceNiceScale: true },
                colors: ['#28a745'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: true },
                grid: { borderColor: 'rgba(108,117,125,0.08)', strokeDashArray: 5 },
                tooltip: { y: { formatter: function(val) { return val + ' milestones'; } } }
            }).render();
        } else {
            document.querySelector('#chart-milestone-trend').innerHTML = '<div class="text-center text-muted py-5"><p>No milestone completions yet</p></div>';
        }

        // ── Owner Workload (horizontal bar) ──
        var ownerData = @json($ownerWorkload);
        if (ownerData.length > 0) {
            var ownerNames = ownerData.map(function(o) { return o.name; });
            var ownerCounts = ownerData.map(function(o) { return o.count; });
            var ownerPrograms = ownerData.map(function(o) { return o.programs; });
            new ApexCharts(document.querySelector('#chart-owner-workload'), {
                chart: { type: 'bar', height: 280, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: [{ name: 'Programs', data: ownerCounts }],
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '50%' } },
                colors: ['#007bff'],
                xaxis: { categories: ownerNames, labels: { style: { colors: bodyColorHex } } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 600 } },
                grid: { borderColor: 'rgba(108,117,125,0.08)', strokeDashArray: 5 },
                tooltip: {
                    custom: function(opts) {
                        var idx = opts.dataPointIndex;
                        var name = ownerNames[idx];
                        var progs = ownerPrograms[idx];
                        var html = '<div class="p-2"><strong>' + name + '</strong> (' + progs.length + ' programs)<br>';
                        progs.forEach(function(prog) { html += '<small>&bull; ' + prog + '</small><br>'; });
                        return html + '</div>';
                    }
                }
            }).render();
        } else {
            document.querySelector('#chart-owner-workload').innerHTML = '<div class="text-center text-muted py-5"><p>No owner assignments yet</p></div>';
        }
    });
    </script>
@endsection
