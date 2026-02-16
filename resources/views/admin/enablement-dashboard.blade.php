@extends('layouts.vertical', ['pageTitle' => 'Enablement Dashboard'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Enablement Dashboard', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubText' => 'Cross-asset enablement analytics and metrics'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">A unified view of all enablement assets — blueprints, events, sessions, registrations, and labs. Use the charts and tables to track enablement health, identify gaps, and export data for reporting.</p>

    {{-- KPI Cards --}}
    <div class="row mb-g">
        @php
            $kpis = [
                ['label' => 'Blueprints', 'value' => $totalBlueprints, 'sub' => $publishedBlueprints . ' published', 'icon' => 'layers', 'color' => 'primary'],
                ['label' => 'Assignments', 'value' => $totalAssignments, 'sub' => $totalEnrollments . ' enrollments', 'icon' => 'users', 'color' => 'info'],
                ['label' => 'Avg Completion', 'value' => $avgCompletion . '%', 'sub' => 'across all enrollments', 'icon' => 'chart-pie', 'color' => 'warning'],
                ['label' => 'Events', 'value' => $totalEvents, 'sub' => $totalSessions . ' sessions', 'icon' => 'award', 'color' => 'success'],
                ['label' => 'Registrations', 'value' => $totalRegistrations, 'sub' => 'total registrations', 'icon' => 'clipboard-check', 'color' => 'danger'],
                ['label' => 'Labs', 'value' => $totalLabs, 'sub' => 'workshop labs', 'icon' => 'check-square', 'color' => 'purple'],
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

    {{-- Charts Row 1 --}}
    <div class="row">
        {{-- Blueprint Status Breakdown --}}
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-bp-status">
                <div class="panel-hdr">
                    <h2>Blueprint <span class="fw-light"><i>Status</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-blueprint-status" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Blueprint Completion by Category --}}
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-bp-category">
                <div class="panel-hdr">
                    <h2>Completion by <span class="fw-light"><i>Category</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-completion-category" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Events by Type --}}
        <div class="col-lg-4">
            <div class="panel panel-icon" id="panel-event-type">
                <div class="panel-hdr">
                    <h2>Events by <span class="fw-light"><i>Type</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-events-type" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="row">
        {{-- Top Partners by Assignments --}}
        <div class="col-lg-6">
            <div class="panel panel-icon" id="panel-top-partners">
                <div class="panel-hdr">
                    <h2>Top Partners by <span class="fw-light"><i>Assignments</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-top-partners" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sessions by Month --}}
        <div class="col-lg-6">
            <div class="panel panel-icon" id="panel-sessions-month">
                <div class="panel-hdr">
                    <h2>Sessions by <span class="fw-light"><i>Month</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="chart-sessions-month" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Assignments Table --}}
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Recent Blueprint Assignments</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="table-recent-assignments" class="table table-bordered table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Partner</th>
                                    <th>Blueprint</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAssignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->partner->name ?? '-' }}</td>
                                    <td>{{ $assignment->blueprint->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ \App\Models\PartnerBlueprintAssignment::ROLE_COLORS[$assignment->role] ?? '#868e96' }}">
                                            {{ \App\Models\PartnerBlueprintAssignment::ROLES[$assignment->role] ?? $assignment->role }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $assignment->status === 'active' ? 'success' : ($assignment->status === 'paused' ? 'warning' : 'info') }}">
                                            {{ ucfirst($assignment->status) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">{{ $assignment->created_at->format('M j, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upcoming Sessions Table --}}
        <div class="col-lg-6">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Upcoming Sessions</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="table-upcoming-sessions" class="table table-bordered table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Event</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingSessions as $session)
                                <tr>
                                    <td class="text-nowrap">{{ $session->start_at ? $session->start_at->format('M j, Y') : '-' }}</td>
                                    <td>{{ $session->title_override ?? $session->event->title ?? '-' }}</td>
                                    <td>
                                        @if($session->event)
                                            <span class="badge" style="background-color: {{ \App\Models\EnablementEvent::TYPE_COLORS[$session->event->type] ?? '#868e96' }}">
                                                {{ \App\Models\EnablementEvent::TYPES[$session->event->type] ?? $session->event->type }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $session->location ?? 'Virtual' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $session->status === 'completed' ? 'success' : ($session->status === 'cancelled' ? 'danger' : 'info') }}">
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
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
    @vite(['resources/scripts/pages/table-init.js'])
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

        var statusColors = {
            'draft': '#868e96',
            'submitted': '#17a2b8',
            'published': '#28a745',
            'archived': '#fd7e14'
        };

        var typeColors = @json(\App\Models\EnablementEvent::TYPE_COLORS);
        var typeLabels = @json(\App\Models\EnablementEvent::TYPES);

        // ── Blueprint Status Donut ──
        var bpStatus = @json($blueprintByStatus);
        var bpLabels = Object.keys(bpStatus).map(function(s) { return s.charAt(0).toUpperCase() + s.slice(1); });
        var bpValues = Object.values(bpStatus).map(Number);
        var bpColors = Object.keys(bpStatus).map(function(s) { return statusColors[s] || '#868e96'; });
        if (bpLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-blueprint-status'), {
                chart: { type: 'donut', height: 300, toolbar: dlToolbar, events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: bpValues,
                labels: bpLabels,
                colors: bpColors,
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%' } } },
                dataLabels: { enabled: true }
            }).render();
        } else {
            document.querySelector('#chart-blueprint-status').innerHTML = '<div class="text-center text-muted py-5"><p>No blueprint data</p></div>';
        }

        // ── Completion by Category (bar) ──
        var catData = @json($completionByCategory);
        var catLabels = Object.keys(catData).map(function(c) {
            var labels = @json(\App\Models\Blueprint::CATEGORIES);
            return labels[c] || c;
        });
        var catValues = Object.values(catData).map(Number);
        var catColors = ['#17a2b8', '#fd7e14', '#0d6efd'];
        if (catLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-completion-category'), {
                chart: { type: 'bar', height: 300, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: [{ name: 'Avg Completion %', data: catValues }],
                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, distributed: true } },
                colors: catColors,
                xaxis: { categories: catLabels, labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
                yaxis: { max: 100, labels: { style: { colors: bodyColorHex }, formatter: function(val) { return val + '%'; } } },
                legend: { show: false },
                tooltip: { y: { formatter: function(val) { return val + '%'; } } }
            }).render();
        } else {
            document.querySelector('#chart-completion-category').innerHTML = '<div class="text-center text-muted py-5"><p>No category data</p></div>';
        }

        // ── Events by Type (donut) ──
        var evtData = @json($eventsByType);
        var evtLabels = Object.keys(evtData).map(function(t) { return typeLabels[t] || t; });
        var evtValues = Object.values(evtData).map(Number);
        var evtColors = Object.keys(evtData).map(function(t) { return typeColors[t] || '#868e96'; });
        if (evtLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-events-type'), {
                chart: { type: 'donut', height: 300, toolbar: dlToolbar, events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: evtValues,
                labels: evtLabels,
                colors: evtColors,
                legend: { position: 'bottom', labels: { colors: bodyColorHex } },
                plotOptions: { pie: { donut: { size: '55%' } } },
                dataLabels: { enabled: true }
            }).render();
        } else {
            document.querySelector('#chart-events-type').innerHTML = '<div class="text-center text-muted py-5"><p>No event data</p></div>';
        }

        // ── Top Partners (horizontal bar) ──
        var partnersData = @json($topPartners);
        if (partnersData.length > 0) {
            new ApexCharts(document.querySelector('#chart-top-partners'), {
                chart: { type: 'bar', height: 350, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: [{ name: 'Assignments', data: partnersData.map(function(p) { return p.count; }) }],
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '65%' } },
                colors: ['#886ab5'],
                dataLabels: { enabled: true, style: { fontSize: '12px' } },
                xaxis: { categories: partnersData.map(function(p) { return p.name; }), labels: { style: { colors: bodyColorHex } } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                tooltip: { theme: 'dark' },
                grid: { borderColor: 'rgba(108,117,125,0.08)', strokeDashArray: 5 }
            }).render();
        } else {
            document.querySelector('#chart-top-partners').innerHTML = '<div class="text-center text-muted py-5"><p>No assignment data</p></div>';
        }

        // ── Sessions by Month (area) ──
        var sessMonths = @json($sessionsByMonth);
        var sessLabels = Object.keys(sessMonths);
        var sessValues = Object.values(sessMonths).map(Number);
        if (sessLabels.length > 0) {
            new ApexCharts(document.querySelector('#chart-sessions-month'), {
                chart: { type: 'area', height: 350, toolbar: dlToolbar, fontFamily: 'inherit', events: { mounted: addCopyMenuItem, updated: addCopyMenuItem } },
                series: [{ name: 'Sessions', data: sessValues }],
                colors: ['#00BCD4'],
                xaxis: { categories: sessLabels, labels: { style: { colors: bodyColorHex, fontSize: '10px' }, rotate: -45 } },
                yaxis: { labels: { style: { colors: bodyColorHex } } },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
                tooltip: { theme: 'dark' }
            }).render();
        } else {
            document.querySelector('#chart-sessions-month').innerHTML = '<div class="text-center text-muted py-5"><p>No session data</p></div>';
        }

        // ── SmartTables with Export ──
        var tables = ['table-recent-assignments', 'table-upcoming-sessions'];
        tables.forEach(function(id) {
            var el = document.querySelector('#' + id);
            if (window.SmartTables && el && el.querySelector('tbody tr td:not([colspan])')) {
                new window.SmartTables(id, {
                    perPage: 10,
                    sortable: true,
                    searchable: true,
                    export: true,
                    plugins: window.ImportExportPlugin ? [window.ImportExportPlugin] : []
                });
            }
        });
    });
    </script>
@endsection
