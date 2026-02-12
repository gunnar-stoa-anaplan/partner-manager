{{-- Influencers Tab: Key Influencer Profiles, Breadth & Depth Analytics --}}

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 1: KPI Cards (6 cards)
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row mb-g">
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Total Influencers</p>
                    <span class="h3 mb-0 fw-500">{{ $influencers->count() }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-users fa-lg text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">New This Month</p>
                    <span class="h3 mb-0 fw-500">{{ $newThisMonth }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-user-plus fa-lg text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">3-Month Growth</p>
                    <span class="h3 mb-0 fw-500">
                        @if($breadthGrowthPct > 0)
                            <i class="fal fa-arrow-up text-success"></i>
                        @elseif($breadthGrowthPct < 0)
                            <i class="fal fa-arrow-down text-danger"></i>
                        @else
                            <i class="fal fa-minus text-muted"></i>
                        @endif
                        {{ abs($breadthGrowthPct) }}%
                    </span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-chart-line fa-lg text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Avg Interactions</p>
                    <span class="h3 mb-0 fw-500">{{ $avgInteractions }}</span>
                    <span class="text-muted small ms-1">per person</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-handshake fa-lg text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Deep Influencers</p>
                    <span class="h3 mb-0 fw-500">{{ $deepInfluencers }}</span>
                    <span class="text-muted small ms-1">3+ touches</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-layer-group fa-lg text-danger"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Role Coverage</p>
                    <span class="h3 mb-0 fw-500">{{ $rolesUsed }}/{{ $totalRoles }}</span>
                    <span class="text-muted small ms-1">({{ $roleCoveragePct }}%)</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-user-tag fa-lg text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 1.5: Breadth Analysis — Network Growth + Role Coverage
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-lg-7">
        <div class="panel panel-icon" id="panel-breadth-growth">
            <div class="panel-hdr">
                <h2>Network <span class="fw-light"><i>Breadth</i></span></h2>
                <div class="panel-toolbar">
                    <span class="badge bg-info bg-opacity-25 text-info">6-Month Trend</span>
                </div>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="breadth-growth-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel panel-icon" id="panel-role-coverage">
            <div class="panel-hdr">
                <h2>Role <span class="fw-light"><i>Coverage</i></span></h2>
                <div class="panel-toolbar">
                    <span class="badge bg-{{ $roleCoveragePct >= 80 ? 'success' : ($roleCoveragePct >= 50 ? 'warning' : 'danger') }} bg-opacity-25 text-{{ $roleCoveragePct >= 80 ? 'success' : ($roleCoveragePct >= 50 ? 'warning' : 'danger') }}">
                        {{ $roleCoveragePct }}% covered
                    </span>
                </div>
            </div>
            <div class="panel-container">
                <div class="panel-content p-0">
                    <div id="role-radar-chart" style="min-height: 380px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 2: Role Distribution + Recent Interactions (existing)
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    {{-- Influencers by Role --}}
    <div class="col-lg-5">
        <div class="panel panel-icon" id="panel-influencer-roles">
            <div class="panel-hdr">
                <h2>Influencers by <span class="fw-light"><i>Role</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    @if($byRole->count() > 0)
                        <div id="influencer-role-chart" style="height: 280px;"></div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fal fa-user-tag fa-2x mb-2 opacity-50"></i>
                            <p>No influencers added yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Interaction Timeline --}}
    <div class="col-lg-7">
        <div class="panel panel-icon" id="panel-recent-interactions">
            <div class="panel-hdr">
                <h2>Recent <span class="fw-light"><i>Interactions</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content" style="max-height: 340px; overflow-y: auto;">
                    @forelse($recentInteractions as $int)
                    <div class="d-flex align-items-start mb-3">
                        <div class="width-3 height-3 rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0 me-3 mt-1">
                            <i class="fal fa-comment-alt text-info"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <span class="fw-500 fs-sm">{{ $int->title }}</span>
                                <span class="text-muted fs-nano">{{ $int->interaction_date->format('M j') }}</span>
                            </div>
                            <span class="badge bg-info bg-opacity-25 text-info fs-nano">
                                {{ \App\Models\ContactInteraction::TYPES[$int->interaction_type] ?? $int->interaction_type }}
                            </span>
                            <span class="text-muted fs-sm ms-1">with {{ $int->contact->first_name ?? '' }} {{ $int->contact->last_name ?? '' }}</span>
                            @if($int->outcome)
                                <p class="mb-0 fs-sm text-muted mt-1">{{ Str::limit($int->outcome, 80) }}</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fal fa-handshake fa-2x mb-2 opacity-50"></i>
                        <p>No interactions logged yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 2.5: Depth Analysis — Interaction Frequency + Trend
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-lg-7">
        <div class="panel panel-icon" id="panel-depth-bars">
            <div class="panel-hdr">
                <h2>Engagement <span class="fw-light"><i>Depth</i></span></h2>
                <div class="panel-toolbar">
                    <span class="badge bg-primary bg-opacity-25 text-primary">Per Influencer</span>
                </div>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    @if($influencers->count() > 0)
                        <div id="depth-stacked-chart" style="height: 320px;"></div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fal fa-layer-group fa-2x mb-2 opacity-50"></i>
                            <p>No influencer data to display.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel panel-icon" id="panel-depth-trend">
            <div class="panel-hdr">
                <h2>Interaction <span class="fw-light"><i>Trend</i></span></h2>
                <div class="panel-toolbar">
                    <span class="badge bg-success bg-opacity-25 text-success">6-Month</span>
                </div>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="depth-trend-chart" style="height: 200px;"></div>
                    <hr class="mt-2 mb-3">
                    <h6 class="text-muted fw-500 mb-2 small">Interaction Type Mix</h6>
                    <div id="depth-type-donut" style="height: 140px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 3: Influencer Table
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">Influencers ({{ $influencers->count() }})</div>
                <a href="{{ route('influencers.create') }}" class="btn btn-xs btn-primary">
                    <i class="fal fa-plus me-1"></i> Add Influencer
                </a>
            </div>
            <div class="card-body">
                @if($influencers->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fal fa-users fa-2x mb-2 opacity-50"></i>
                        <p>No influencers tracked for this partner.</p>
                        <a href="{{ route('influencers.create') }}" class="btn btn-sm btn-outline-primary">Add Your First Influencer</a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" id="influencer-tab-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Title</th>
                                    <th>Organization</th>
                                    <th class="text-center">Depth Score</th>
                                    <th class="text-center">Type Diversity</th>
                                    <th>Last Interaction</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($influencers as $inf)
                                @php
                                    $dm = $depthMetrics->firstWhere('id', $inf->id);
                                    $totalInt = $dm['total'] ?? 0;
                                    $uniqueTypes = $dm['unique_types'] ?? 0;
                                    $totalTypes = count(\App\Models\ContactInteraction::TYPES);
                                    $trendDir = ($dm['trend'] ?? 0);
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('influencers.show', $inf) }}" class="fw-500">
                                            {{ $inf->first_name }} {{ $inf->last_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-25 text-primary">
                                            {{ \App\Models\Contact::INFLUENCER_ROLES[$inf->influencer_role] ?? $inf->influencer_role }}
                                        </span>
                                    </td>
                                    <td>{{ $inf->title ?? '-' }}</td>
                                    <td>{{ $inf->organization ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($totalInt >= 3)
                                            <span class="badge bg-success bg-opacity-25 text-success" title="{{ $totalInt }} interactions">
                                                <i class="fal fa-check-circle me-1"></i>{{ $totalInt }}
                                            </span>
                                        @elseif($totalInt >= 1)
                                            <span class="badge bg-warning bg-opacity-25 text-warning" title="{{ $totalInt }} interactions">
                                                <i class="fal fa-exclamation-circle me-1"></i>{{ $totalInt }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-25 text-danger" title="No interactions">
                                                <i class="fal fa-times-circle me-1"></i>0
                                            </span>
                                        @endif
                                        @if($trendDir > 0)
                                            <i class="fal fa-arrow-up text-success ms-1 fs-nano"></i>
                                        @elseif($trendDir < 0)
                                            <i class="fal fa-arrow-down text-danger ms-1 fs-nano"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 6px; max-width: 80px;">
                                                <div class="progress-bar bg-info" style="width: {{ $totalTypes > 0 ? round($uniqueTypes / $totalTypes * 100) : 0 }}%"></div>
                                            </div>
                                            <span class="fs-nano text-muted">{{ $uniqueTypes }}/{{ $totalTypes }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php $last = $inf->interactions->first(); @endphp
                                        {{ $last ? $last->interaction_date->diffForHumans() : 'Never' }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('influencers.show', $inf) }}" class="btn btn-xs btn-outline-info" title="View & Log Interaction">
                                            <i class="fal fa-eye"></i>
                                        </a>
                                        <a href="{{ route('influencers.edit', $inf) }}" class="btn btn-xs btn-outline-primary" title="Edit">
                                            <i class="fal fa-pencil"></i>
                                        </a>
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

    var roleColors = [primaryHex, successHex, infoHex, warningHex, dangerHex, '#20c997'];

    var typeColors = [
        primaryHex, successHex, infoHex, warningHex, dangerHex,
        '#20c997', '#6610f2', '#fd7e14', '#6c757d'
    ];

    // ══════════════════════════════════════════════════════════════════
    // BREADTH: Network Growth (mixed area + bar chart)
    // ══════════════════════════════════════════════════════════════════
    var breadthTrend = @json($breadthTrend);
    var breadthMonths = breadthTrend.map(function(d) { return d.month; });
    var breadthCounts = breadthTrend.map(function(d) { return d.count; });
    var breadthNew = breadthTrend.map(function(d) { return d.new; });

    new ApexCharts(document.querySelector('#breadth-growth-chart'), {
        series: [
            { name: 'Total Influencers', type: 'area', data: breadthCounts },
            { name: 'New Additions', type: 'bar', data: breadthNew }
        ],
        chart: {
            type: 'line',
            height: 280,
            fontFamily: 'inherit',
            toolbar: { show: false },
            stacked: false
        },
        stroke: { width: [2, 0], curve: 'smooth' },
        fill: {
            type: ['gradient', 'solid'],
            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 },
            opacity: [0.35, 0.85]
        },
        colors: [infoHex, successHex],
        plotOptions: { bar: { columnWidth: '40%', borderRadius: 3 } },
        xaxis: { categories: breadthMonths, labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
        yaxis: [
            {
                title: { text: 'Total Count', style: { color: bodyColorHex, fontSize: '11px' } },
                labels: { style: { colors: bodyColorHex }, formatter: function(v) { return Math.round(v); } },
                min: 0
            },
            {
                opposite: true,
                title: { text: 'New', style: { color: bodyColorHex, fontSize: '11px' } },
                labels: { style: { colors: bodyColorHex }, formatter: function(v) { return Math.round(v); } },
                min: 0
            }
        ],
        legend: { labels: { colors: bodyColorHex }, fontSize: '12px' },
        tooltip: { theme: 'dark' },
        grid: { borderColor: 'rgba(130,130,130,0.15)' }
    }).render();

    // ══════════════════════════════════════════════════════════════════
    // BREADTH: Role Coverage Radar
    // ══════════════════════════════════════════════════════════════════
    var roleCoverage = @json($roleCoverage);
    var roleMap = @json(\App\Models\Contact::INFLUENCER_ROLES);
    var radarLabels = Object.keys(roleCoverage).map(function(k) { return roleMap[k] || k; });
    var radarValues = Object.values(roleCoverage);

    new ApexCharts(document.querySelector('#role-radar-chart'), {
        series: [{ name: 'Influencers', data: radarValues }],
        chart: { type: 'radar', height: '100%', fontFamily: 'inherit', toolbar: { show: false } },
        colors: [primaryHex],
        fill: { opacity: 0.3 },
        stroke: { width: 2, colors: [primaryHex] },
        markers: { size: 4, colors: [primaryHex], strokeColors: '#fff', strokeWidth: 1 },
        xaxis: { categories: radarLabels, labels: { style: { colors: Array(radarLabels.length).fill(bodyColorHex), fontSize: '11px' } } },
        yaxis: {
            show: false,
            min: 0,
            tickAmount: 3
        },
        tooltip: { theme: 'dark', y: { formatter: function(v) { return v + ' influencer' + (v !== 1 ? 's' : ''); } } },
        plotOptions: { radar: { polygons: { strokeColors: 'rgba(130,130,130,0.15)', connectorColors: 'rgba(130,130,130,0.15)' } } }
    }).render();

    // ══════════════════════════════════════════════════════════════════
    // EXISTING: Influencers by Role Donut
    // ══════════════════════════════════════════════════════════════════
    var roleData = @json($byRole);
    var roleLabels = Object.keys(roleData);
    var roleValues = Object.values(roleData);

    if (roleLabels.length > 0) {
        var roleDisplayLabels = roleLabels.map(function(key) {
            return roleMap[key] || key;
        });

        new ApexCharts(document.querySelector('#influencer-role-chart'), {
            series: roleValues,
            chart: { type: 'donut', height: 260, fontFamily: 'inherit' },
            labels: roleDisplayLabels,
            colors: roleColors.slice(0, roleLabels.length),
            legend: { position: 'bottom', labels: { colors: bodyColorHex }, fontSize: '13px' },
            dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 600, colors: ['#fff'] } },
            plotOptions: { pie: { donut: { size: '55%' } } },
            tooltip: { theme: 'dark' }
        }).render();
    }

    // ══════════════════════════════════════════════════════════════════
    // DEPTH: Per-Influencer Stacked Bar (by interaction type)
    // ══════════════════════════════════════════════════════════════════
    var depthByType = @json($depthByType->values());
    var typeMap = @json(\App\Models\ContactInteraction::TYPES);
    var allTypes = Object.keys(typeMap);

    if (depthByType.length > 0) {
        var stackedSeries = allTypes.map(function(typeKey, idx) {
            return {
                name: typeMap[typeKey] || typeKey,
                data: depthByType.map(function(inf) {
                    return (inf.types && inf.types[typeKey]) ? inf.types[typeKey] : 0;
                })
            };
        }).filter(function(s) {
            return s.data.some(function(v) { return v > 0; });
        });

        var stackedCategories = depthByType.map(function(inf) {
            var parts = inf.name.split(' ');
            return parts[0] + (parts.length > 1 ? ' ' + parts[1].charAt(0) + '.' : '');
        });

        new ApexCharts(document.querySelector('#depth-stacked-chart'), {
            series: stackedSeries,
            chart: {
                type: 'bar',
                height: 300,
                stacked: true,
                fontFamily: 'inherit',
                toolbar: { show: false }
            },
            plotOptions: { bar: { horizontal: false, columnWidth: '60%', borderRadius: 2 } },
            colors: typeColors.slice(0, stackedSeries.length),
            xaxis: {
                categories: stackedCategories,
                labels: { style: { colors: bodyColorHex, fontSize: '11px' }, rotate: -45, rotateAlways: stackedCategories.length > 6 }
            },
            yaxis: {
                title: { text: 'Interactions', style: { color: bodyColorHex, fontSize: '11px' } },
                labels: { style: { colors: bodyColorHex }, formatter: function(v) { return Math.round(v); } },
                min: 0
            },
            legend: { position: 'bottom', labels: { colors: bodyColorHex }, fontSize: '11px' },
            tooltip: { theme: 'dark' },
            grid: { borderColor: 'rgba(130,130,130,0.15)' }
        }).render();
    }

    // ══════════════════════════════════════════════════════════════════
    // DEPTH: Interaction Trend (line chart)
    // ══════════════════════════════════════════════════════════════════
    var depthTrend = @json($depthTrend);
    var trendMonths = depthTrend.map(function(d) { return d.month; });
    var trendCounts = depthTrend.map(function(d) { return d.count; });

    new ApexCharts(document.querySelector('#depth-trend-chart'), {
        series: [{ name: 'Interactions', data: trendCounts }],
        chart: { type: 'area', height: 180, fontFamily: 'inherit', toolbar: { show: false }, sparkline: { enabled: false } },
        stroke: { width: 2, curve: 'smooth' },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
        colors: [successHex],
        xaxis: { categories: trendMonths, labels: { style: { colors: bodyColorHex, fontSize: '10px' } } },
        yaxis: {
            labels: { style: { colors: bodyColorHex }, formatter: function(v) { return Math.round(v); } },
            min: 0
        },
        tooltip: { theme: 'dark' },
        grid: { borderColor: 'rgba(130,130,130,0.15)' }
    }).render();

    // ══════════════════════════════════════════════════════════════════
    // DEPTH: Interaction Type Distribution (mini donut)
    // ══════════════════════════════════════════════════════════════════
    var interactionTypeDist = @json($interactionTypeDist);
    var typeDistKeys = Object.keys(interactionTypeDist);
    var typeDistValues = Object.values(interactionTypeDist);

    if (typeDistKeys.length > 0) {
        var typeDistLabels = typeDistKeys.map(function(k) { return typeMap[k] || k; });
        new ApexCharts(document.querySelector('#depth-type-donut'), {
            series: typeDistValues,
            chart: { type: 'donut', height: 130, fontFamily: 'inherit' },
            labels: typeDistLabels,
            colors: typeColors.slice(0, typeDistKeys.length),
            legend: { show: false },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '60%', labels: { show: true, total: { show: true, label: 'Types', fontSize: '10px', color: bodyColorHex } } } } },
            tooltip: { theme: 'dark' }
        }).render();
    }

    // ══════════════════════════════════════════════════════════════════
    // SmartTables
    // ══════════════════════════════════════════════════════════════════
    if (window.SmartTables && document.querySelector('#influencer-tab-table tbody tr td:not([colspan])')) {
        new window.SmartTables('influencer-tab-table', { perPage: 15, search: true, pagination: true });
    }
})();
</script>
