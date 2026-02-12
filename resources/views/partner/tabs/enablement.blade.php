{{-- Enablement Tab: Blueprint Radar + Enablement Donut + Assignments + Sessions --}}

<div class="row">
    {{-- Blueprint Completion Radar --}}
    <div class="col-lg-6">
        <div class="panel panel-icon panel-locked" id="panel-enable-radar">
            <div class="panel-hdr">
                <h2> Blueprint <span class="fw-light"><i>Completion</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="enablement-blueprint-radar" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Enablement Engagement Donut --}}
    <div class="col-lg-6">
        <div class="panel panel-icon" id="panel-enable-donut">
            <div class="panel-hdr">
                <h2> Enablement <span class="fw-light"><i>Engagement</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="enablement-donut-chart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Links --}}
<div class="d-flex gap-2 mb-g">
    <a href="{{ route('partners.blueprints', $partner->id) }}" class="btn btn-outline-warning btn-sm">
        <i class="fal fa-layers me-1"></i> Full Blueprint Progress
    </a>
    <a href="{{ route('partners.enablement', $partner->id) }}" class="btn btn-outline-info btn-sm">
        <i class="fal fa-award me-1"></i> Enablement Calendar
    </a>
    <a href="{{ route('partners.calendar', $partner->id) }}" class="btn btn-outline-primary btn-sm">
        <i class="fal fa-calendar me-1"></i> Partner Calendar
    </a>
</div>

{{-- Blueprint Assignments --}}
@php
    $enrolledContactIds = $blueprintAssignments->flatMap(fn($a) => $a->enrollments->pluck('contact_id'))->unique();
    $assignmentsWithEnrollments = $blueprintAssignments->filter(fn($a) => $a->enrollments->count() > 0);
    $avgCompletion = $assignmentsWithEnrollments->count() > 0
        ? round($assignmentsWithEnrollments->avg(fn($a) => $a->completionPercentage()), 1) : 0;
    $completedAssignments = $assignmentsWithEnrollments->filter(fn($a) => $a->completionPercentage() >= 100)->count();
@endphp

{{-- Summary KPI Cards --}}
<div class="row mb-g">
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Total Assignments</p>
                    <span class="h3 mb-0 fw-500">{{ $blueprintAssignments->count() }}</span>
                </div>
                <div class="width-5 height-5 bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-layers fa-lg text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Enrolled Contacts</p>
                    <span class="h3 mb-0 fw-500">{{ $enrolledContactIds->count() }}</span>
                </div>
                <div class="width-5 height-5 bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-users fa-lg text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Avg Completion</p>
                    <span class="h3 mb-0 fw-500">{{ $avgCompletion }}%</span>
                </div>
                <div class="width-5 height-5 bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-chart-pie fa-lg text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Completed</p>
                    <span class="h3 mb-0 fw-500">{{ $completedAssignments }} <small class="text-muted fw-400">/ {{ $blueprintAssignments->count() }}</small></span>
                </div>
                <div class="width-5 height-5 bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-check-circle fa-lg text-success"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Assignment Cards --}}
@forelse($blueprintAssignments as $assignment)
    @php
        $pct = $assignment->completionPercentage();
        $progressColor = $pct >= 100 ? 'success' : ($pct >= 50 ? 'info' : ($pct > 0 ? 'warning' : 'secondary'));
        $applicableCount = $assignment->applicableContentItemCount();
    @endphp
    <div class="card shadow-0 mb-g">
        <div class="card-header d-flex align-items-center">
            <div class="card-title flex-grow-1 d-flex align-items-center gap-2">
                <span>{{ $assignment->blueprint->name }}</span>
                <span class="badge bg-{{ $assignment->blueprint->category === 'application' ? 'info' : 'warning' }} opacity-75 fw-400">
                    {{ \App\Models\Blueprint::CATEGORIES[$assignment->blueprint->category] ?? $assignment->blueprint->category }}
                </span>
                @if($assignment->blueprint->product_area)
                    <span class="badge bg-light text-dark border fw-400">{{ $assignment->blueprint->product_area }}</span>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('blueprint-assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fal fa-chart-bar me-1"></i> Details
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">
                    <small class="text-muted d-block">Role</small>
                    <span class="badge" style="background-color: {{ \App\Models\PartnerBlueprintAssignment::ROLE_COLORS[$assignment->role] ?? '#868e96' }}">
                        {{ \App\Models\PartnerBlueprintAssignment::ROLES[$assignment->role] ?? $assignment->role }}
                    </span>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge bg-{{ $assignment->status === 'active' ? 'success' : ($assignment->status === 'paused' ? 'warning' : 'info') }}">
                        {{ ucfirst($assignment->status) }}
                    </span>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Content Items</small>
                    <strong>{{ $applicableCount }}</strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Enrolled</small>
                    <strong>{{ $assignment->enrollments->count() }}</strong> contacts
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Assigned By</small>
                    <span>{{ $assignment->assignedBy->name }}</span>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Since</small>
                    <span>{{ $assignment->created_at->format('M j, Y') }}</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted text-nowrap fw-500">Overall:</small>
                <div class="progress flex-grow-1" style="height: 10px;">
                    <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $pct }}%"></div>
                </div>
                <small class="text-muted text-nowrap fw-500">{{ $pct }}%</small>
            </div>

            {{-- Enrolled Contacts --}}
            @if($assignment->enrollments->count())
                <div class="border-top pt-3 mt-3">
                    <small class="text-muted fw-500 d-block mb-2">Enrolled Contacts</small>
                    @foreach($assignment->enrollments as $enrollment)
                        @php
                            $ePct = $enrollment->completionPercentage();
                            $eCompleted = $enrollment->completedItemsCount();
                            $eTotal = $enrollment->totalApplicableItems();
                            $eColor = $ePct >= 100 ? 'success' : ($ePct >= 50 ? 'info' : ($ePct > 0 ? 'warning' : 'secondary'));
                        @endphp
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div style="min-width: 160px;">
                                <a href="{{ route('blueprint-enrollments.show', $enrollment) }}" class="text-dark">
                                    {{ $enrollment->contact->first_name }} {{ $enrollment->contact->last_name }}
                                </a>
                            </div>
                            <span class="badge" style="background-color: {{ \App\Models\BlueprintEnrollment::STATUS_COLORS[$enrollment->status] ?? '#868e96' }}; min-width: 80px;">
                                {{ \App\Models\BlueprintEnrollment::STATUSES[$enrollment->status] ?? $enrollment->status }}
                            </span>
                            <div class="progress flex-grow-1" style="height: 6px;">
                                <div class="progress-bar bg-{{ $eColor }}" style="width: {{ $ePct }}%"></div>
                            </div>
                            <small class="text-muted text-nowrap">{{ $eCompleted }}/{{ $eTotal }} ({{ $ePct }}%)</small>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3 text-muted border-top mt-3">
                    <i class="fal fa-user-plus me-1"></i> No contacts enrolled yet.
                    <a href="{{ route('blueprint-assignments.show', $assignment) }}">Enroll contacts</a>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="card shadow-0">
        <div class="card-body text-center py-5">
            <div class="mb-3"><i class="fal fa-layer-group fa-3x text-muted opacity-50"></i></div>
            <h5 class="text-muted">No blueprints assigned yet</h5>
            <p class="text-muted mb-3">Assign enablement blueprints to {{ $partner->name }} to track their readiness progression.</p>
        </div>
    </div>
@endforelse

{{-- Upcoming Sessions --}}
@if($upcomingSessions->isNotEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title">Upcoming Sessions</div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Event</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($upcomingSessions as $session)
                    <tr>
                        <td class="text-nowrap">{{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->format('M j, Y') : '-' }}</td>
                        <td class="fw-500">{{ $session->title ?? $session->event_title ?? '-' }}</td>
                        <td>{{ $session->location ?? 'Virtual' }}</td>
                        <td>
                            <span class="badge bg-{{ $session->status === 'completed' ? 'success' : ($session->status === 'cancelled' ? 'danger' : 'info') }} opacity-75">
                                {{ ucfirst(str_replace('_', ' ', $session->status ?? 'scheduled')) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Chart Scripts --}}
<script>
(function() {
    var cm = window.colorMap || {};
    var bv = (cm.bootstrapVars && cm.bootstrapVars.bodyColor) || null;
    var bodyColorHex = bv ? bv.hex : '#6c757d';
    var bodyColor01 = bv ? bv.rgba(0.1) : 'rgba(108,117,125,0.1)';

    // ── Blueprint Completion Radar ──
    var blueprintSeries = @json($blueprintByArea);
    var blueprintLabels = @json($blueprintAreas);
    if (blueprintSeries.length > 0 && blueprintLabels.length > 0) {
        var radarColors = ['#00BCD4', '#fd7e14', '#886ab5'];
        new ApexCharts(document.querySelector('#enablement-blueprint-radar'), {
            chart: { height: 350, width: '100%', type: 'radar', toolbar: { show: false }, fontFamily: 'inherit', parentHeightOffset: 0 },
            series: blueprintSeries,
            labels: blueprintLabels,
            colors: radarColors,
            stroke: { width: 0, colors: radarColors },
            fill: { opacity: 0.2 },
            markers: { size: 5, colors: radarColors, strokeColors: '#fff', strokeWidth: 2, hover: { sizeOffset: 2 } },
            xaxis: { labels: { style: { colors: bodyColorHex, fontSize: '10px' } } },
            yaxis: { max: 100, tickAmount: 5, show: true, labels: { show: true, style: { colors: bodyColorHex, fontSize: '10px' } } },
            legend: { show: true, position: 'top', horizontalAlign: 'center', fontSize: '14px', fontFamily: 'inherit', labels: { colors: bodyColorHex }, markers: { width: 18, height: 6, radius: 2 }, itemMargin: { horizontal: 12, vertical: 0 } },
            tooltip: { theme: 'dark', y: { formatter: function(val) { return val + '%'; } } },
            grid: { borderColor: bodyColor01, strokeDashArray: 3 },
            plotOptions: { radar: { polygons: { strokeColors: bodyColor01, connectorColors: bodyColor01 } } }
        }).render();
    } else {
        document.querySelector('#enablement-blueprint-radar').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-layer-group fa-2x mb-2 opacity-50"></i><p>No blueprints assigned</p></div>';
    }

    // ── Enablement Engagement Donut ──
    var enablementByStatus = @json($enablementByStatus);
    var engLabels = Object.keys(enablementByStatus);
    var engValues = Object.values(enablementByStatus);
    var engColors = { 'scheduled': '#17a2b8', 'in_progress': '#886ab5', 'completed': '#28a745', 'cancelled': '#dc3545' };
    if (engLabels.length > 0) {
        new ApexCharts(document.querySelector('#enablement-donut-chart'), {
            chart: { type: 'donut', height: 350 },
            series: engValues,
            labels: engLabels.map(function(s) { return s.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); }); }),
            colors: engLabels.map(function(s) { return engColors[s] || '#868e96'; }),
            legend: { position: 'bottom', labels: { colors: bodyColorHex } },
            plotOptions: { pie: { donut: { size: '55%' } } },
            dataLabels: { enabled: true }
        }).render();
    } else {
        document.querySelector('#enablement-donut-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-award fa-2x mb-2 opacity-50"></i><p>No enablement sessions yet</p></div>';
    }
})();
</script>
