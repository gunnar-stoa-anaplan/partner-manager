{{-- Enablement Tab: Charts + KPIs + Calendar --}}

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
</div>

{{-- KPI Cards (6 cards) --}}
<div class="row mb-g">
    @php
        $kpis = [
            ['label' => 'Avg Completion', 'value' => $avgCompletion . '%', 'sub' => 'across all assignments', 'icon' => 'chart-pie', 'color' => $avgCompletion >= 75 ? 'success' : ($avgCompletion >= 40 ? 'warning' : 'danger')],
            ['label' => 'Enrolled Contacts', 'value' => $enrolledContactIds->count(), 'sub' => 'of ' . $partner->contacts->count() . ' total', 'icon' => 'users', 'color' => 'info'],
            ['label' => 'Stalled', 'value' => $stalledCount, 'sub' => '> 30 days inactive', 'icon' => 'exclamation-triangle', 'color' => $stalledCount > 0 ? 'danger' : 'success'],
            ['label' => 'Completed', 'value' => $completedAssignments . ' / ' . $blueprintAssignments->count(), 'sub' => 'assignments done', 'icon' => 'check-circle', 'color' => 'success'],
            ['label' => 'Avg Depth', 'value' => $avgDepth, 'sub' => 'enrollees per blueprint', 'icon' => 'user-friends', 'color' => $avgDepth >= 3 ? 'success' : ($avgDepth >= 2 ? 'info' : 'warning')],
            ['label' => 'Avg Time', 'value' => $avgTimeToComplete !== null ? $avgTimeToComplete . 'd' : '-', 'sub' => 'to complete', 'icon' => 'clock', 'color' => 'info'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
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

{{-- Row 2: Coverage & Depth --}}
<div class="row">
    <div class="col-lg-7">
        <div class="panel panel-icon" id="panel-area-role">
            <div class="panel-hdr">
                <h2>Completion by <span class="fw-light"><i>Area & Role</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="chart-area-role" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel panel-icon" id="panel-depth">
            <div class="panel-hdr">
                <h2>Enrollment <span class="fw-light"><i>Depth</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="chart-enrollment-depth" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Row 3: Health & Velocity --}}
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-icon" id="panel-stalled">
            <div class="panel-hdr">
                <h2>Stalled <span class="fw-light"><i>Blueprints</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="chart-stalled" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="panel panel-icon" id="panel-time-complete">
            <div class="panel-hdr">
                <h2>Time to <span class="fw-light"><i>Complete</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="chart-time-complete" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Enablement Calendar with Sidebar --}}
<style>
    #draggable-sessions .fc-event {
        cursor: grab; padding: 5px 8px; margin-bottom: 5px;
        border-radius: 4px; font-size: 0.8rem; color: #fff; border: none;
    }
    #draggable-sessions .fc-event:active { cursor: grabbing; }
    #draggable-sessions .fc-event .event-meta { font-size: 0.7rem; opacity: 0.85; }
</style>
<div class="row mb-g">
    <div class="col-lg-9">
        <div class="card shadow-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">Enablement Calendar</div>
                <a href="{{ route('enablement.calendar') }}" class="btn btn-xs btn-outline-info">
                    <i class="fal fa-external-link me-1"></i> Full Calendar
                </a>
            </div>
            <div class="card-body">
                <div id="enablement-calendar" style="min-height: 500px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        {{-- Type Filter --}}
        <div class="card shadow-0 mb-3">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="card-title mb-0">Filter by Type</h6>
                <button class="btn btn-xs btn-link p-0" id="toggleAllTypes">All</button>
            </div>
            <div class="card-body py-2">
                <div id="type-filters">
                    @foreach(\App\Models\EnablementEvent::TYPES as $typeKey => $typeLabel)
                        <div class="form-check form-check-sm">
                            <input class="form-check-input type-filter" type="checkbox"
                                   value="{{ $typeKey }}" id="type-filter-{{ $typeKey }}" checked>
                            <label class="form-check-label small" for="type-filter-{{ $typeKey }}">
                                <span class="badge me-1" style="background-color: {{ \App\Models\EnablementEvent::TYPE_COLORS[$typeKey] ?? '#868e96' }}; width: 8px; height: 8px; display: inline-block; border-radius: 50%; padding: 0;"></span>
                                {{ $typeLabel }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Available Sessions (drag to register) --}}
        <div class="card shadow-0">
            <div class="card-header py-2">
                <h6 class="card-title mb-0">Available Sessions</h6>
            </div>
            <div class="card-body p-2">
                <div class="form-text mb-2 px-1 small">Drag a session onto the calendar to register this partner.</div>
                <div id="draggable-sessions" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center text-muted py-2"><small>Loading sessions...</small></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Contact Assignment Modal --}}
<div class="modal fade" id="contactAssignModal" tabindex="-1" aria-labelledby="contactAssignTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactAssignTitle">Assign {{ $partner->name }} Contacts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="assignSessionId">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <h6 class="text-muted mb-1">Session</h6>
                        <h5 id="assignSessionTitle" class="mb-0"></h5>
                    </div>
                    <div class="col-md-4 text-end">
                        <span id="assignTypeBadge" class="badge"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><small class="text-muted d-block">Date</small><strong id="assignSessionDate"></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Location</small><strong id="assignSessionLocation"></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Instructor</small><strong id="assignSessionInstructor"></strong></div>
                </div>
                <hr>
                <h6 class="text-muted mb-2"><i class="fal fa-users me-1"></i> Assigned Contacts</h6>
                <div id="assignedContactsList" class="mb-3"><p class="text-muted">Loading...</p></div>
                <hr>
                <h6 class="text-muted mb-2"><i class="fal fa-user-plus me-1"></i> Add Contacts</h6>
                <div id="availableContacts">
                    @foreach($partner->contacts as $contact)
                        <div class="form-check" id="contact-row-{{ $contact->id }}">
                            <input class="form-check-input assign-contact-cb" type="checkbox"
                                   value="{{ $contact->id }}" id="assign-contact-{{ $contact->id }}">
                            <label class="form-check-label" for="assign-contact-{{ $contact->id }}">
                                {{ $contact->first_name }} {{ $contact->last_name }}
                                @if($contact->email) <small class="text-muted">({{ $contact->email }})</small> @endif
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-sm btn-primary" id="assignContactsBtn">
                        <i class="fal fa-user-plus me-1"></i> Assign Selected
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2 d-none" id="removePartnerBtn">
                        <i class="fal fa-times me-1"></i> Unregister Partner
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1100" id="toast-container"></div>

{{-- Chart Scripts --}}
<script>
(function() {
    var cm = window.colorMap || {};
    var bv = (cm.bootstrapVars && cm.bootstrapVars.bodyColor) || null;
    var bodyColorHex = bv ? bv.hex : '#6c757d';
    var bodyColor01 = bv ? bv.rgba(0.1) : 'rgba(108,117,125,0.1)';
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

    var roleColors = { delivery: '#00BCD4', gtm: '#fd7e14', pre_sales: '#886ab5' };

    // ── Blueprint Completion Radar ──
    var blueprintSeries = @json($blueprintByArea);
    var blueprintLabels = @json($blueprintAreas);
    if (blueprintSeries.length > 0 && blueprintLabels.length > 0) {
        var radarColors = ['#00BCD4', '#fd7e14', '#886ab5'];
        new ApexCharts(document.querySelector('#enablement-blueprint-radar'), {
            chart: { height: 350, width: '100%', type: 'radar', toolbar: dlToolbar, fontFamily: 'inherit', parentHeightOffset: 0, events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
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
            chart: { type: 'donut', height: 350, toolbar: dlToolbar, events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
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

    // ── Completion by Area & Role (grouped bar) ──
    if (blueprintSeries.length > 0 && blueprintLabels.length > 0) {
        new ApexCharts(document.querySelector('#chart-area-role'), {
            chart: { type: 'bar', height: 350, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
            series: blueprintSeries,
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '70%' } },
            colors: ['#00BCD4', '#fd7e14', '#886ab5'],
            dataLabels: { enabled: true, formatter: function(val) { return val + '%'; }, style: { fontSize: '11px' } },
            xaxis: { categories: blueprintLabels, labels: { style: { colors: bodyColorHex } } },
            yaxis: { max: 100, labels: { style: { colors: bodyColorHex } } },
            legend: { show: true, position: 'top', labels: { colors: bodyColorHex } },
            tooltip: { theme: 'dark', y: { formatter: function(val) { return val + '%'; } } },
            grid: { borderColor: bodyColor01, strokeDashArray: 5 }
        }).render();
    } else {
        document.querySelector('#chart-area-role').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-chart-bar fa-2x mb-2 opacity-50"></i><p>No assignment data</p></div>';
    }

    // ── Enrollment Depth (horizontal bar) ──
    var depthData = @json($enrollmentDepth);
    if (depthData.length > 0) {
        var depthColors = depthData.map(function(d) {
            return d.enrolled >= 3 ? '#28a745' : (d.enrolled >= 2 ? '#fd7e14' : '#dc3545');
        });
        new ApexCharts(document.querySelector('#chart-enrollment-depth'), {
            chart: { type: 'bar', height: 350, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
            series: [{ name: 'Enrollees', data: depthData.map(function(d) { return d.enrolled; }) }],
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '65%', distributed: true } },
            colors: depthColors,
            xaxis: { categories: depthData.map(function(d) { return d.name; }), labels: { style: { colors: bodyColorHex } } },
            yaxis: { labels: { style: { colors: bodyColorHex } } },
            legend: { show: false },
            tooltip: { theme: 'dark', custom: function(opts) {
                var d = depthData[opts.dataPointIndex];
                return '<div class="px-3 py-2"><b>' + d.name + '</b><br>' + d.enrolled + ' enrollees | ' + d.completion + '% complete</div>';
            } },
            grid: { borderColor: bodyColor01, strokeDashArray: 5 }
        }).render();
    } else {
        document.querySelector('#chart-enrollment-depth').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-users fa-2x mb-2 opacity-50"></i><p>No enrollments yet</p></div>';
    }

    // ── Stalled Blueprints (horizontal bar) ──
    var stalledData = @json($stalledAssignments);
    if (stalledData.length > 0) {
        var stalledColors = stalledData.map(function(d) {
            return d.days_since_activity > 30 ? '#dc3545' : (d.days_since_activity > 14 ? '#fd7e14' : '#28a745');
        });
        new ApexCharts(document.querySelector('#chart-stalled'), {
            chart: { type: 'bar', height: 350, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
            series: [{ name: 'Days Inactive', data: stalledData.map(function(d) { return d.days_since_activity; }) }],
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '65%', distributed: true } },
            colors: stalledColors,
            dataLabels: { enabled: true, formatter: function(val) { return val + 'd'; }, style: { fontSize: '11px' } },
            xaxis: { categories: stalledData.map(function(d) { return d.name; }), labels: { style: { colors: bodyColorHex } } },
            yaxis: { labels: { style: { colors: bodyColorHex } } },
            legend: { show: false },
            tooltip: { theme: 'dark', custom: function(opts) {
                var d = stalledData[opts.dataPointIndex];
                return '<div class="px-3 py-2"><b>' + d.name + '</b><br>' + d.days_since_activity + ' days idle | ' + d.completion + '% complete</div>';
            } },
            grid: { borderColor: bodyColor01, strokeDashArray: 5 }
        }).render();
    } else {
        document.querySelector('#chart-stalled').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-check-circle fa-2x mb-2 text-success opacity-50"></i><p>No stalled blueprints</p></div>';
    }

    // ── Time to Complete (vertical bar) ──
    var ttcData = @json($timeToCompleteBuckets);
    var ttcLabels = ttcData.map(function(b) { return b.bucket; });
    var ttcValues = ttcData.map(function(b) { return b.count; });
    var ttcTotal = ttcValues.reduce(function(a, b) { return a + b; }, 0);
    if (ttcTotal > 0) {
        new ApexCharts(document.querySelector('#chart-time-complete'), {
            chart: { type: 'bar', height: 350, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
            series: [{ name: 'Completions', data: ttcValues }],
            plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, distributed: true } },
            colors: ['#28a745', '#17a2b8', '#886ab5', '#fd7e14', '#dc3545'],
            xaxis: { categories: ttcLabels, labels: { style: { colors: bodyColorHex, fontSize: '10px' } } },
            yaxis: { labels: { style: { colors: bodyColorHex } } },
            legend: { show: false },
            tooltip: { theme: 'dark' },
            grid: { borderColor: bodyColor01 }
        }).render();
    } else {
        document.querySelector('#chart-time-complete').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-clock fa-2x mb-2 opacity-50"></i><p>No completed enrollments yet</p></div>';
    }

    // ── Embedded Enablement Calendar ──
    @if(isset($contacts))
    window.enablementCalendarConfig = {
        mode: 'partner',
        partnerId: {{ $partner->id }},
        partnerName: @json($partner->name),
        contacts: @json($contacts),
        eventTypes: @json(\App\Models\EnablementEvent::TYPES),
        typeColors: @json(\App\Models\EnablementEvent::TYPE_COLORS),
        apiBaseUrl: '{{ url("/api") }}',
        csrfToken: '{{ csrf_token() }}',
    };
    @endif
})();
</script>
@vite(['resources/scripts/pages/enablement-calendar.js'])
