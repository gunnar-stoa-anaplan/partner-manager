@extends('layouts.vertical', ['pageTitle' => 'Enablement Dashboard'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Enablement Dashboard', 'pageSubTitle1' => 'Enablement', 'pageSubText' => 'Events, sessions, registrations & blueprint progress'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Track enablement program health across events, sessions, registrations, and blueprint adoption. Use this dashboard to identify engagement trends, monitor attendance, and measure partner readiness.</p>

    {{-- ════════════════════════════════════════════════════════════════
         Summary Cards
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($totalEvents) }}</h3>
                    <span class="m-0 l-h-n">Events</span>
                </div>
                <i class="fal fa-calendar position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-400 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($totalSessions) }}</h3>
                    <span class="m-0 l-h-n">Sessions</span>
                </div>
                <i class="fal fa-chalkboard-teacher position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-400 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($totalRegistrations) }}</h3>
                    <span class="m-0 l-h-n">Registrations</span>
                </div>
                <i class="fal fa-user-check position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-400 rounded overflow-hidden position-relative text-white">
                <div>
                    <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($totalBlueprints) }}</h3>
                    <span class="m-0 l-h-n">Blueprints</span>
                </div>
                <i class="fal fa-layer-group position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
            </div>
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-primary-100 rounded text-center">
                <h3 class="display-4 fw-500 m-0 text-primary">{{ $attendanceRate }}%</h3>
                <span class="text-muted">Attendance Rate</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-success-100 rounded text-center">
                <h3 class="display-4 fw-500 m-0 text-success">{{ $enrollmentCompletionRate }}%</h3>
                <span class="text-muted">Enrollment Completion</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-info-100 rounded text-center">
                <h3 class="display-4 fw-500 m-0 text-info">{{ $totalDurationHours }} <small class="fs-md">hrs</small></h3>
                <span class="text-muted">Total Content Hours</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 bg-warning-100 rounded text-center">
                <h3 class="display-4 fw-500 m-0 text-warning">{{ $upcomingSessions->count() }}</h3>
                <span class="text-muted">Upcoming (60 days)</span>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 1: Events
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#calendar"></use></svg>
                Events
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Events by Status</div></div>
                <div class="card-body"><div id="chart-event-status" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Events by Type</div></div>
                <div class="card-body"><div id="chart-event-type" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Events by Product Area</div></div>
                <div class="card-body"><div id="chart-event-product" style="height:280px"></div></div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 2: Sessions
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#monitor"></use></svg>
                Sessions
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Sessions by Status</div></div>
                <div class="card-body"><div id="chart-session-status" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Sessions by Month (6 Months)</div></div>
                <div class="card-body"><div id="chart-session-month" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Sessions by Geography</div></div>
                <div class="card-body"><div id="chart-session-geo" style="height:280px"></div></div>
            </div>
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-12">
            <div class="card shadow-0">
                <div class="card-header"><div class="card-title">Upcoming Sessions (60 days)</div></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="table-upcoming-sessions" class="table table-bordered table-hover table-striped w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Session</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Instructor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingSessions as $session)
                                <tr>
                                    <td>{{ $session->title_override ?? $session->event->title ?? '-' }}</td>
                                    <td>{{ $session->event->title ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $session->start_at->format('M j, Y') }}</td>
                                    <td>{{ $session->location ?? $session->geography ?? '-' }}</td>
                                    <td>{{ $session->instructor ?? '-' }}</td>
                                    <td><span class="badge bg-{{ $session->status === 'scheduled' ? 'primary' : ($session->status === 'completed' ? 'success' : 'secondary') }}">{{ ucfirst($session->status) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">No upcoming sessions</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 3: Registrations & Attendance
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#user-check"></use></svg>
                Registrations & Attendance
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Registrations by Status</div></div>
                <div class="card-body"><div id="chart-reg-status" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Registrations by Month (6 Months)</div></div>
                <div class="card-body"><div id="chart-reg-month" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Top Partners by Registrations</div></div>
                <div class="card-body"><div id="chart-top-partners" style="height:280px"></div></div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         Section 4: Blueprints & Enrollments
         ════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <h4 class="fw-500 mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#layers"></use></svg>
                Blueprints & Enrollments
            </h4>
            <hr class="mt-2 mb-3">
        </div>
    </div>

    <div class="row mb-g">
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Blueprints by Status</div></div>
                <div class="card-body"><div id="chart-bp-status" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Blueprints by Category</div></div>
                <div class="card-body"><div id="chart-bp-category" style="height:280px"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-0 h-100">
                <div class="card-header"><div class="card-title">Enrollment Progress</div></div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-center" style="height:200px">
                        <div id="chart-enrollment-completion" style="height:200px; width:100%"></div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-4">
                            <span class="badge bg-success fs-sm">{{ $assignmentsByStatus->get('completed', 0) }}</span>
                            <small class="d-block text-muted mt-1">Completed</small>
                        </div>
                        <div class="col-4">
                            <span class="badge bg-primary fs-sm">{{ $assignmentsByStatus->get('active', 0) }}</span>
                            <small class="d-block text-muted mt-1">Active</small>
                        </div>
                        <div class="col-4">
                            <span class="badge bg-warning fs-sm">{{ $assignmentsByStatus->get('paused', 0) }}</span>
                            <small class="d-block text-muted mt-1">Paused</small>
                        </div>
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
        var pri = cm.primary || {};
        var suc = cm.success || {};
        var inf = cm.info || {};
        var wrn = cm.warning || {};
        var dng = cm.danger || {};
        var bsVars = cm.bootstrapVars || {};
        var bodyColorHex = (bsVars.bodyColor || {}).hex || '#666';
        var bodyColor01 = (bsVars.bodyColor || { rgba: function(){ return 'rgba(0,0,0,0.1)'; }}).rgba(0.1);

        var chartColors = [
            (pri[500] || {}).hex || '#886ab5',
            (suc[500] || {}).hex || '#1dc9b7',
            (inf[500] || {}).hex || '#33b5e5',
            (wrn[500] || {}).hex || '#ffc241',
            (dng[500] || {}).hex || '#fd3995',
            (pri[300] || {}).hex || '#B09AD5',
            (suc[300] || {}).hex || '#5EDBC9',
            (inf[300] || {}).hex || '#70CCF0',
            (wrn[300] || {}).hex || '#FFD980',
            (pri[700] || {}).hex || '#5E4A80'
        ];

        function emptyState(el) {
            document.querySelector(el).innerHTML = '<div class="text-center text-muted py-5"><p>No data</p></div>';
        }

        // SmartTables
        var el = document.querySelector('#table-upcoming-sessions');
        if (window.SmartTables && el && el.querySelector('tbody tr td:not([colspan])')) {
            new window.SmartTables('table-upcoming-sessions', { perPage: 10, sortable: true });
        }

        // ═══════════════════════════════════════════════════════════════
        // Section 1: Events
        // ═══════════════════════════════════════════════════════════════

        // Events by Status (Donut)
        var esData = @json($eventsByStatus);
        var esLabels = Object.keys(esData);
        var esValues = Object.values(esData).map(Number);
        var statusColorMap = { draft: (wrn[500]||{}).hex||'#ffc241', published: (suc[500]||{}).hex||'#1dc9b7', archived: (pri[300]||{}).hex||'#B09AD5' };
        if (esLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-event-status'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: esValues,
                labels: esLabels.map(function(s){ return s.charAt(0).toUpperCase() + s.slice(1); }),
                colors: esLabels.map(function(s){ return statusColorMap[s] || chartColors[0]; }),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-event-status'); }

        // Events by Type (Bar)
        var etData = @json($eventsByType);
        var etLabels = Object.keys(etData);
        var etValues = Object.values(etData).map(Number);
        if (etLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-event-type'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Events', data: etValues }],
                plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3, distributed: true } },
                colors: chartColors,
                xaxis: { categories: etLabels.map(function(s){ return s.replace(/_/g, ' ').replace(/\b\w/g, function(c){ return c.toUpperCase(); }); }), labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else { emptyState('#chart-event-type'); }

        // Events by Product Area (Bar)
        var epData = @json($eventsByProductArea);
        var epLabels = Object.keys(epData);
        var epValues = Object.values(epData).map(Number);
        if (epLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-event-product'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Events', data: epValues }],
                plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3, distributed: true } },
                colors: chartColors,
                xaxis: { categories: epLabels, labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else { emptyState('#chart-event-product'); }

        // ═══════════════════════════════════════════════════════════════
        // Section 2: Sessions
        // ═══════════════════════════════════════════════════════════════

        // Sessions by Status (Donut)
        var ssData = @json($sessionsByStatus);
        var ssLabels = Object.keys(ssData);
        var ssValues = Object.values(ssData).map(Number);
        var sessColorMap = { scheduled: (pri[500]||{}).hex||'#886ab5', in_progress: (wrn[500]||{}).hex||'#ffc241', completed: (suc[500]||{}).hex||'#1dc9b7', cancelled: (dng[500]||{}).hex||'#fd3995' };
        if (ssLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-session-status'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: ssValues,
                labels: ssLabels.map(function(s){ return s.replace(/_/g, ' ').replace(/\b\w/g, function(c){ return c.toUpperCase(); }); }),
                colors: ssLabels.map(function(s){ return sessColorMap[s] || chartColors[0]; }),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-session-status'); }

        // Sessions by Month (Column)
        var smData = @json($sessionsByMonth);
        var smLabels = Object.keys(smData);
        var smValues = Object.values(smData).map(Number);
        if (smLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-session-month'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Sessions', data: smValues }],
                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, distributed: true } },
                colors: chartColors,
                xaxis: { categories: smLabels, labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else { emptyState('#chart-session-month'); }

        // Sessions by Geography (Donut)
        var sgData = @json($sessionsByGeography);
        var sgLabels = Object.keys(sgData);
        var sgValues = Object.values(sgData).map(Number);
        if (sgLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-session-geo'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: sgValues,
                labels: sgLabels,
                colors: chartColors.slice(0, sgLabels.length),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-session-geo'); }

        // ═══════════════════════════════════════════════════════════════
        // Section 3: Registrations
        // ═══════════════════════════════════════════════════════════════

        // Registrations by Status (Donut)
        var rsData = @json($registrationsByStatus);
        var rsLabels = Object.keys(rsData);
        var rsValues = Object.values(rsData).map(Number);
        var regColorMap = { nominated: (inf[500]||{}).hex||'#33b5e5', registered: (pri[500]||{}).hex||'#886ab5', confirmed: (wrn[500]||{}).hex||'#ffc241', attended: (suc[500]||{}).hex||'#1dc9b7', no_show: (dng[500]||{}).hex||'#fd3995', cancelled: (pri[300]||{}).hex||'#B09AD5' };
        if (rsLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-reg-status'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: rsValues,
                labels: rsLabels.map(function(s){ return s.replace(/_/g, ' ').replace(/\b\w/g, function(c){ return c.toUpperCase(); }); }),
                colors: rsLabels.map(function(s){ return regColorMap[s] || chartColors[0]; }),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-reg-status'); }

        // Registrations by Month (Area)
        var rmData = @json($registrationsByMonth);
        var rmLabels = Object.keys(rmData);
        var rmValues = Object.values(rmData).map(Number);
        if (rmLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-reg-month'), {
                chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Registrations', data: rmValues }],
                colors: [(pri[500]||{}).hex||'#886ab5'],
                xaxis: { categories: rmLabels, labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                stroke: { curve: 'smooth', width: 2 },
                fill: { opacity: 0.15 },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' },
                dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 600 } }
            }).render();
        } else { emptyState('#chart-reg-month'); }

        // Top Partners by Registrations (Bar)
        var tpData = @json($topPartnersByRegistrations);
        if (tpData.length > 0) {
            new ApexCharts(document.querySelector('#chart-top-partners'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [{ name: 'Registrations', data: tpData.map(function(r){ return r.reg_count; }) }],
                plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3, distributed: true } },
                colors: chartColors,
                xaxis: { categories: tpData.map(function(r){ return r.name; }), labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                legend: { show: false },
                dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 600 } },
                grid: { borderColor: bodyColor01 },
                tooltip: { theme: 'dark' }
            }).render();
        } else { emptyState('#chart-top-partners'); }

        // ═══════════════════════════════════════════════════════════════
        // Section 4: Blueprints
        // ═══════════════════════════════════════════════════════════════

        // Blueprints by Status (Donut)
        var bsData = @json($blueprintsByStatus);
        var bsLabels = Object.keys(bsData);
        var bsValues = Object.values(bsData).map(Number);
        var bpColorMap = { draft: (wrn[500]||{}).hex||'#ffc241', submitted: (inf[500]||{}).hex||'#33b5e5', published: (suc[500]||{}).hex||'#1dc9b7', archived: (pri[300]||{}).hex||'#B09AD5' };
        if (bsLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-bp-status'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: bsValues,
                labels: bsLabels.map(function(s){ return s.charAt(0).toUpperCase() + s.slice(1); }),
                colors: bsLabels.map(function(s){ return bpColorMap[s] || chartColors[0]; }),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-bp-status'); }

        // Blueprints by Category (Donut)
        var bcData = @json($blueprintsByCategory);
        var bcLabels = Object.keys(bcData);
        var bcValues = Object.values(bcData).map(Number);
        if (bcLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-bp-category'), {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: bcValues,
                labels: bcLabels.map(function(s){ return s.replace(/_/g, ' ').replace(/\b\w/g, function(c){ return c.toUpperCase(); }); }),
                colors: chartColors.slice(0, bcLabels.length),
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                stroke: { width: 2 }
            }).render();
        } else { emptyState('#chart-bp-category'); }

        // Enrollment Completion (Radial)
        var enrollPct = {{ $enrollmentCompletionRate }};
        new ApexCharts(document.querySelector('#chart-enrollment-completion'), {
            chart: { type: 'radialBar', height: 200, fontFamily: 'inherit' },
            series: [enrollPct],
            plotOptions: {
                radialBar: {
                    hollow: { size: '60%' },
                    dataLabels: {
                        name: { show: true, fontSize: '13px', color: bodyColorHex, offsetY: -10 },
                        value: { show: true, fontSize: '24px', fontWeight: 600, offsetY: 5, formatter: function(v){ return v + '%'; } }
                    },
                    track: { background: bodyColor01 }
                }
            },
            colors: [enrollPct >= 75 ? ((suc[500]||{}).hex||'#1dc9b7') : enrollPct >= 40 ? ((pri[500]||{}).hex||'#886ab5') : ((wrn[500]||{}).hex||'#ffc241')],
            labels: ['Completed']
        }).render();

    });
    </script>
@endsection
