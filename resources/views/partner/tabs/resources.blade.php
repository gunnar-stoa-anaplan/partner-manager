{{-- Resources Tab: Partner Specialists — Certifications, Blueprints, Projects, Enablement --}}

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 1: KPI Cards (6 cards)
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row mb-g">
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Total Resources</p>
                    <span class="h3 mb-0 fw-500">{{ $resources->count() }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-user-hard-hat fa-lg text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Active Certs</p>
                    <span class="h3 mb-0 fw-500">{{ $activeCerts }}</span>
                    <span class="text-muted small ms-1">/ {{ $totalCerts }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-certificate fa-lg text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Expiring Soon</p>
                    <span class="h3 mb-0 fw-500 {{ $expiringSoon > 0 ? 'text-warning' : '' }}">{{ $expiringSoon }}</span>
                    <span class="text-muted small ms-1">90 days</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-exclamation-triangle fa-lg text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Blueprint Avg</p>
                    <span class="h3 mb-0 fw-500">{{ $avgCompletion }}%</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-tasks fa-lg text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Sessions Attended</p>
                    <span class="h3 mb-0 fw-500">{{ $attendedSessions }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-chalkboard-teacher fa-lg text-danger"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Projects Staffed</p>
                    <span class="h3 mb-0 fw-500">{{ $projectsStaffed }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-project-diagram fa-lg text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 2: Resource Growth + Certification Breakdown
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-lg-7">
        <div class="panel panel-icon" id="panel-resource-growth">
            <div class="panel-hdr">
                <h2>Resource & Certification <span class="fw-light"><i>Trend</i></span></h2>
                <div class="panel-toolbar">
                    <span class="badge bg-info bg-opacity-25 text-info">6-Month</span>
                </div>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="resource-growth-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel panel-icon" id="panel-cert-breakdown">
            <div class="panel-hdr">
                <h2>Certifications by <span class="fw-light"><i>Type</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    @if($totalCerts > 0)
                        <div id="cert-type-chart" style="height: 280px;"></div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fal fa-certificate fa-2x mb-2 opacity-50"></i>
                            <p>No certifications recorded yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 3: Role Distribution + Blueprint/Enablement Status
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-lg-5">
        <div class="panel panel-icon" id="panel-resource-roles">
            <div class="panel-hdr">
                <h2>Resources by <span class="fw-light"><i>Role</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    @if($byRole->count() > 0)
                        <div id="resource-role-chart" style="height: 260px;"></div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fal fa-user-tag fa-2x mb-2 opacity-50"></i>
                            <p>No resources added yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="panel panel-icon" id="panel-capability-matrix">
            <div class="panel-hdr">
                <h2>Capability <span class="fw-light"><i>Matrix</i></span></h2>
                <div class="panel-toolbar">
                    <span class="badge bg-primary bg-opacity-25 text-primary">Per Resource</span>
                </div>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    @if($resources->count() > 0)
                        <div id="capability-bar-chart" style="height: 300px;"></div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fal fa-chart-bar fa-2x mb-2 opacity-50"></i>
                            <p>No resource data to display.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 4: Resource Table
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">Resources ({{ $resources->count() }})</div>
                {{-- Future: Add Resource button --}}
            </div>
            <div class="card-body">
                @if($resources->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fal fa-user-hard-hat fa-2x mb-2 opacity-50"></i>
                        <p>No resources tracked for this partner.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" id="resource-tab-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Title</th>
                                    <th class="text-center">Certifications</th>
                                    <th class="text-center">Blueprints</th>
                                    <th class="text-center">Projects</th>
                                    <th class="text-center">Sessions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resources as $res)
                                @php
                                    $rm = $resourceMetrics->firstWhere('id', $res->id);
                                @endphp
                                <tr>
                                    <td class="fw-500">{{ $res->first_name }} {{ $res->last_name }}</td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-25 text-primary">
                                            {{ \App\Models\Contact::RESOURCE_ROLES[$res->resource_role] ?? $res->resource_role ?? '-' }}
                                        </span>
                                    </td>
                                    <td>{{ $res->title ?? '-' }}</td>
                                    <td class="text-center">
                                        @php $certCount = $rm['certs'] ?? 0; @endphp
                                        <span class="badge {{ $certCount > 0 ? 'bg-success' : 'bg-secondary' }} bg-opacity-25 {{ $certCount > 0 ? 'text-success' : 'text-secondary' }}">
                                            {{ $certCount }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php $bpCount = $rm['blueprints'] ?? 0; @endphp
                                        <span class="badge {{ $bpCount > 0 ? 'bg-info' : 'bg-secondary' }} bg-opacity-25 {{ $bpCount > 0 ? 'text-info' : 'text-secondary' }}">
                                            {{ $bpCount }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php $projCount = $rm['projects'] ?? 0; @endphp
                                        <span class="badge {{ $projCount > 0 ? 'bg-primary' : 'bg-secondary' }} bg-opacity-25 {{ $projCount > 0 ? 'text-primary' : 'text-secondary' }}">
                                            {{ $projCount }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php $sessCount = $rm['sessions'] ?? 0; @endphp
                                        <span class="badge {{ $sessCount > 0 ? 'bg-warning' : 'bg-secondary' }} bg-opacity-25 {{ $sessCount > 0 ? 'text-warning' : 'text-secondary' }}">
                                            {{ $sessCount }}
                                        </span>
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

{{-- ══════════════════════════════════════════════════════════════════════
     Chart Scripts
     ══════════════════════════════════════════════════════════════════════ --}}
<script>
(function() {
    var cm = window.colorMap || {};
    var pr = (cm.primary && cm.primary[400]) || null;
    var su = (cm.success && cm.success[400]) || null;
    var inf = (cm.info && cm.info[500]) || null;
    var wrn = (cm.warning && cm.warning[500]) || null;
    var dng = (cm.danger && cm.danger[500]) || null;
    var bv = (cm.bootstrapVars && cm.bootstrapVars.bodyColor) || null;
    var bodyColorHex = bv ? bv.hex : '#6c757d';

    var primaryHex = pr ? pr.hex : '#886ab5';
    var successHex = su ? su.hex : '#1dc9b7';
    var infoHex = inf ? inf.hex : '#33b5e5';
    var warningHex = wrn ? wrn.hex : '#ffc241';
    var dangerHex = dng ? dng.hex : '#fd3995';

    var roleColors = [primaryHex, successHex, infoHex, warningHex, dangerHex, '#20c997', '#6610f2'];

    // ══════════════════════════════════════════════════════════════════
    // Resource Growth + Certification Trend (dual line)
    // ══════════════════════════════════════════════════════════════════
    var growthTrend = @json($growthTrend);
    var certTrend = @json($certTrend);
    var trendMonths = growthTrend.map(function(d) { return d.month; });
    var resourceCounts = growthTrend.map(function(d) { return d.count; });
    var certCounts = certTrend.map(function(d) { return d.active; });

    new ApexCharts(document.querySelector('#resource-growth-chart'), {
        series: [
            { name: 'Resources', data: resourceCounts },
            { name: 'Active Certifications', data: certCounts }
        ],
        chart: {
            type: 'area',
            height: 280,
            fontFamily: 'inherit',
            toolbar: { show: false }
        },
        stroke: { width: 2, curve: 'smooth' },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 }
        },
        colors: [primaryHex, successHex],
        xaxis: { categories: trendMonths, labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
        yaxis: {
            labels: { style: { colors: bodyColorHex }, formatter: function(v) { return Math.round(v); } },
            min: 0
        },
        legend: { labels: { colors: bodyColorHex }, fontSize: '12px' },
        tooltip: { theme: 'dark' },
        grid: { borderColor: 'rgba(130,130,130,0.15)' }
    }).render();

    // ══════════════════════════════════════════════════════════════════
    // Certifications by Type (donut)
    // ══════════════════════════════════════════════════════════════════
    var certsByType = @json($certsByType);
    var certTypeMap = @json(\App\Models\Certification::TYPES);
    var certTypeKeys = Object.keys(certsByType);
    var certTypeValues = Object.values(certsByType);

    if (certTypeKeys.length > 0) {
        var certTypeLabels = certTypeKeys.map(function(k) { return certTypeMap[k] || k; });

        new ApexCharts(document.querySelector('#cert-type-chart'), {
            series: certTypeValues,
            chart: { type: 'donut', height: 260, fontFamily: 'inherit' },
            labels: certTypeLabels,
            colors: roleColors.slice(0, certTypeKeys.length),
            legend: { position: 'bottom', labels: { colors: bodyColorHex }, fontSize: '13px' },
            dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 600, colors: ['#fff'] } },
            plotOptions: { pie: { donut: { size: '55%' } } },
            tooltip: { theme: 'dark' }
        }).render();
    }

    // ══════════════════════════════════════════════════════════════════
    // Resources by Role (donut)
    // ══════════════════════════════════════════════════════════════════
    var byRole = @json($byRole);
    var roleMap = @json(\App\Models\Contact::RESOURCE_ROLES);
    var roleLabels = Object.keys(byRole);
    var roleValues = Object.values(byRole);

    if (roleLabels.length > 0) {
        var roleDisplayLabels = roleLabels.map(function(k) { return roleMap[k] || k; });

        new ApexCharts(document.querySelector('#resource-role-chart'), {
            series: roleValues,
            chart: { type: 'donut', height: 240, fontFamily: 'inherit' },
            labels: roleDisplayLabels,
            colors: roleColors.slice(0, roleLabels.length),
            legend: { position: 'bottom', labels: { colors: bodyColorHex }, fontSize: '13px' },
            dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 600, colors: ['#fff'] } },
            plotOptions: { pie: { donut: { size: '50%' } } },
            tooltip: { theme: 'dark' }
        }).render();
    }

    // ══════════════════════════════════════════════════════════════════
    // Capability Matrix (grouped bar — per resource)
    // ══════════════════════════════════════════════════════════════════
    var resourceMetrics = @json($resourceMetrics->values());
    if (resourceMetrics.length > 0) {
        var capCategories = resourceMetrics.map(function(r) {
            var parts = r.name.split(' ');
            return parts[0] + (parts.length > 1 ? ' ' + parts[1].charAt(0) + '.' : '');
        });

        new ApexCharts(document.querySelector('#capability-bar-chart'), {
            series: [
                { name: 'Certifications', data: resourceMetrics.map(function(r) { return r.certs; }) },
                { name: 'Blueprints', data: resourceMetrics.map(function(r) { return r.blueprints; }) },
                { name: 'Projects', data: resourceMetrics.map(function(r) { return r.projects; }) },
                { name: 'Sessions', data: resourceMetrics.map(function(r) { return r.sessions; }) }
            ],
            chart: {
                type: 'bar',
                height: 280,
                fontFamily: 'inherit',
                toolbar: { show: false }
            },
            plotOptions: { bar: { horizontal: false, columnWidth: '65%', borderRadius: 2 } },
            colors: [successHex, infoHex, primaryHex, warningHex],
            xaxis: {
                categories: capCategories,
                labels: { style: { colors: bodyColorHex, fontSize: '11px' }, rotate: -45, rotateAlways: capCategories.length > 6 }
            },
            yaxis: {
                labels: { style: { colors: bodyColorHex }, formatter: function(v) { return Math.round(v); } },
                min: 0
            },
            legend: { position: 'bottom', labels: { colors: bodyColorHex }, fontSize: '11px' },
            tooltip: { theme: 'dark' },
            grid: { borderColor: 'rgba(130,130,130,0.15)' }
        }).render();
    }

    // ══════════════════════════════════════════════════════════════════
    // SmartTables
    // ══════════════════════════════════════════════════════════════════
    if (window.SmartTables && document.querySelector('#resource-tab-table tbody tr td:not([colspan])')) {
        new window.SmartTables('resource-tab-table', { perPage: 15, search: true, pagination: true });
    }
})();
</script>
