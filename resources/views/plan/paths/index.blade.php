@extends('layouts.vertical', ['pageTitle' => 'Partner Paths'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Partner Paths', 'pageSubTitle1' => 'Plan', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The Partner Paths dashboard shows where each of your partners sits along their assigned progression path — from onboarding through to strategic status. Use this view to monitor how long partners have been in their current stage, identify anyone falling behind, and check health scores at a glance. You can enroll new partners into a path, click any stage in the flowchart to focus on just those partners, and drill into a partner's detailed review page to see exactly which gate requirements they've met and what's still needed before they can advance to the next stage.</p>

@php
    $defaultPath = $paths->first();
    $stages = $defaultPath ? $defaultPath->stages->sortBy('sort_order')->values() : collect();
    $stagesById = $stages->keyBy('id');
    $gatesByTransition = $defaultPath ? $defaultPath->gates->groupBy(fn($g) => $g->from_stage_id . '-' . $g->to_stage_id) : collect();
@endphp

{{-- Filter Bar --}}
<div class="card shadow-0 mb-g">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('plan.paths.index') }}" class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                {{-- PSM Filter (manager/admin only) --}}
                @if($canSeeAll && $psmUsers->isNotEmpty())
                <label class="form-label mb-0 fw-500 text-muted">PSM:</label>
                <select name="psm_id" class="form-select form-select-sm" style="width: 200px;" onchange="this.form.submit()">
                    <option value="">All PSMs ({{ $psmUsers->count() }})</option>
                    @foreach($psmUsers as $psm)
                        <option value="{{ $psm->id }}" {{ $selectedPsmId == $psm->id ? 'selected' : '' }}>{{ $psm->name }}</option>
                    @endforeach
                </select>
                @endif

                <label class="form-label mb-0 fw-500 text-muted">Partner:</label>
                <select name="partner_id" class="form-select form-select-sm" style="width: 260px;" onchange="this.form.submit()">
                    <option value="">All Partners ({{ $partnersForDropdown->count() }})</option>
                    @foreach($partnersForDropdown as $p)
                        <option value="{{ $p->id }}" {{ $selectedPartnerId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>

                @if($selectedPartnerId || $selectedPsmId)
                    <a href="{{ route('plan.paths.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fal fa-times me-1"></i>Clear
                    </a>
                @endif
            </div>
            <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#enrollModal">
                <i class="fal fa-plus me-1"></i>Enroll Partner
            </button>
        </form>
    </div>
</div>

{{-- Management Summary Cards (manager/admin only) --}}
@if($canSeeAll && $managementMetrics)
<div class="row mb-g">
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center py-3">
                <div class="fs-xl fw-700 text-primary">{{ $enrollments->count() }}</div>
                <div class="text-muted fs-sm">Active Enrollments</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center py-3">
                <div class="fs-xl fw-700 text-danger">{{ $managementMetrics['atRiskPartners']->count() }}</div>
                <div class="text-muted fs-sm">At-Risk Partners</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center py-3">
                <div class="fs-xl fw-700 text-info">{{ $managementMetrics['avgEnrollmentAge'] }}d</div>
                <div class="text-muted fs-sm">Avg Enrollment Age</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center py-3">
                <div class="fs-xl fw-700 text-success">{{ $psmUsers->count() }}</div>
                <div class="text-muted fs-sm">Active PSMs</div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Path Flowchart --}}
@if($stages->count() > 0)
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <div class="card-title mb-0">{{ $defaultPath->name }} <small class="text-muted fw-normal ms-2">Click a stage to filter</small></div>
        <button class="btn btn-sm btn-outline-secondary d-none" id="clearFilterBtn" onclick="clearStageFilter()">
            <i class="fal fa-times me-1"></i>Clear filter
        </button>
    </div>
    <div class="card-body pt-2">
        {{-- Active filter indicator --}}
        <div class="alert alert-info py-2 px-3 d-none mb-3" id="filterIndicator">
            <i class="fal fa-filter me-1"></i>
            Showing partners in: <strong id="filterStageName"></strong>
        </div>

        <div class="path-flow-container">
            <div class="path-flow">
                @foreach($stages as $idx => $stage)
                    @php
                        $partnerCount = $stageGroups->get($stage->id, collect())->count();
                    @endphp

                    {{-- Stage Node --}}
                    <div class="path-stage-node" data-stage-id="{{ $stage->id }}" data-stage-name="{{ $stage->name }}" onclick="toggleStageFilter({{ $stage->id }}, '{{ $stage->name }}')">
                        <div class="stage-card" style="border-color: {{ $stage->color }}" id="stage-card-{{ $stage->id }}">
                            <div class="stage-header" style="background-color: {{ $stage->color }}">
                                <i class="{{ $stage->icon ?? 'fal fa-circle' }}"></i>
                            </div>
                            <div class="stage-body">
                                <div class="stage-number">Stage {{ $stage->stage_number }}</div>
                                <div class="stage-name">{{ $stage->name }}</div>
                                <div class="stage-stats">
                                    <span class="partner-count-badge" style="color: {{ $stage->color }}">
                                        <i class="fal fa-building"></i> {{ $partnerCount }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Gate Connector (between stages) --}}
                    @if(!$loop->last)
                        @php
                            $nextStage = $stages[$idx + 1] ?? null;
                            $gateKey = $stage->id . '-' . ($nextStage?->id ?? '');
                            $gates = $gatesByTransition[$gateKey] ?? collect();
                        @endphp
                        <div class="path-gate-connector" data-from-stage="{{ $stage->id }}" data-to-stage="{{ $nextStage?->id }}">
                            <div class="gate-line"></div>
                            <div class="gate-badge {{ $gates->isEmpty() ? 'gate-empty' : '' }}" title="{{ $gates->count() }} gate criteria">
                                @if($gates->isNotEmpty())
                                    <i class="fal fa-shield-check"></i>
                                    <span class="gate-count">{{ $gates->count() }}</span>
                                @else
                                    <i class="fal fa-lock-open text-muted"></i>
                                @endif
                            </div>
                            <div class="gate-line"></div>
                            <div class="gate-arrow"><i class="fal fa-chevron-right"></i></div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- Stage Duration Analytics --}}
@if($stages->count() > 0)
<div class="row mb-g">
    {{-- Analytics Table --}}
    <div class="col-xl-5">
        <div class="card shadow-0 h-100">
            <div class="card-header py-2">
                <div class="card-title mb-0">Stage Analytics</div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm m-0">
                    <thead>
                        <tr>
                            <th>Stage</th>
                            <th class="text-center">Partners</th>
                            <th class="text-center">Avg Days</th>
                            <th class="text-center">Median</th>
                            <th class="text-center">Range</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stages as $stage)
                        @php
                            $stats = $stageDurations[$stage->id] ?? ['avg' => 0, 'median' => 0, 'min' => 0, 'max' => 0, 'count' => 0];
                            $stagePartnerCount = $stageGroups->get($stage->id, collect())->count();
                        @endphp
                        <tr data-analytics-stage="{{ $stage->id }}">
                            <td>
                                <span class="badge" style="background-color: {{ $stage->color }}">{{ $stage->name }}</span>
                            </td>
                            <td class="text-center">{{ $stagePartnerCount }}</td>
                            <td class="text-center fw-500">{{ $stats['avg'] }}</td>
                            <td class="text-center">{{ $stats['median'] }}</td>
                            <td class="text-center text-muted fs-sm">
                                @if($stats['count'] > 0)
                                    {{ $stats['min'] }}–{{ $stats['max'] }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Duration Chart --}}
    <div class="col-xl-7">
        <div class="card shadow-0 h-100">
            <div class="card-header py-2">
                <div class="card-title mb-0">Avg. Days in Stage</div>
            </div>
            <div class="card-body">
                <div id="stageDurationChart" style="min-height: 220px;"></div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- At-Risk Partners (manager/admin only) --}}
@if($canSeeAll && $managementMetrics && $managementMetrics['atRiskPartners']->isNotEmpty())
<div class="card shadow-0 mb-g" style="border-left: 4px solid #dc3545;">
    <div class="card-header py-2">
        <div class="card-title mb-0">
            <i class="fal fa-exclamation-triangle text-danger me-1"></i>
            At-Risk Partners ({{ $managementMetrics['atRiskPartners']->count() }})
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover m-0">
            <thead>
                <tr>
                    <th>Partner</th>
                    <th>PSM</th>
                    <th>Current Stage</th>
                    <th class="text-center">Days in Stage</th>
                    <th class="text-center">Score</th>
                    <th>Risk Factors</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($managementMetrics['atRiskPartners'] as $risk)
                <tr>
                    <td><strong>{{ $risk['enrollment']->partner->name }}</strong></td>
                    <td class="text-muted">{{ $risk['psm_name'] }}</td>
                    <td>
                        <span class="badge" style="background-color: {{ $risk['enrollment']->currentStage->color }}">
                            {{ $risk['enrollment']->currentStage->name }}
                        </span>
                    </td>
                    <td class="text-center text-danger fw-500">{{ $risk['days_in_stage'] }}</td>
                    <td class="text-center">
                        @if($risk['score'] !== null)
                            <span class="badge bg-{{ $risk['score'] >= 55 ? 'success' : ($risk['score'] >= 30 ? 'warning' : 'danger') }}">{{ number_format($risk['score'], 0) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @foreach($risk['reasons'] as $reason)
                            <span class="badge bg-danger bg-opacity-10 text-danger fs-xs me-1">{{ $reason }}</span>
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('plan.paths.show', $risk['enrollment']) }}" class="btn btn-xs btn-outline-primary">Review</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- PSM Portfolio Distribution & Task Completion (manager/admin only) --}}
@if($canSeeAll && $managementMetrics && $managementMetrics['psmBreakdown']->isNotEmpty())
<div class="row mb-g">
    <div class="col-xl-7">
        <div class="card shadow-0 h-100">
            <div class="card-header py-2">
                <div class="card-title mb-0">PSM Portfolio Distribution</div>
            </div>
            <div class="card-body">
                <div id="psmBreakdownChart" style="min-height: 260px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card shadow-0 h-100">
            <div class="card-header py-2">
                <div class="card-title mb-0">Required Task Completion by Stage</div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm m-0">
                    <thead>
                        <tr>
                            <th>Stage</th>
                            <th class="text-center">Required</th>
                            <th class="text-center">Done</th>
                            <th class="text-center">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stages as $stage)
                        @php $ts = $managementMetrics['taskStats'][$stage->id] ?? ['total' => 0, 'completed' => 0, 'rate' => 0]; @endphp
                        <tr>
                            <td><span class="badge" style="background-color: {{ $stage->color }}">{{ $stage->name }}</span></td>
                            <td class="text-center">{{ $ts['total'] }}</td>
                            <td class="text-center">{{ $ts['completed'] }}</td>
                            <td class="text-center">
                                @if($ts['total'] > 0)
                                <span class="fw-500 {{ $ts['rate'] >= 80 ? 'text-success' : ($ts['rate'] >= 50 ? 'text-warning' : 'text-danger') }}">{{ $ts['rate'] }}%</span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Enrollment Table --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <div class="card-title mb-0">Partner Enrollments (<span id="enrollmentCount">{{ $enrollments->count() }}</span>)</div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover m-0" id="enrollments-table">
            <thead>
                <tr>
                    <th>Partner</th>
                    @if($canSeeAll)<th>PSM</th>@endif
                    <th>Path</th>
                    <th>Current Stage</th>
                    <th class="text-center">Days in Stage</th>
                    <th class="text-center">Health Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enrollment)
                <tr data-stage-id="{{ $enrollment->current_stage_id }}">
                    <td>
                        <strong>{{ $enrollment->partner->name }}</strong>
                    </td>
                    @if($canSeeAll)
                    <td class="text-muted">{{ $enrollment->partner->user?->name ?? 'Unassigned' }}</td>
                    @endif
                    <td>{{ $enrollment->path->name }}</td>
                    <td>
                        <span class="badge" style="background-color: {{ $enrollment->currentStage->color }}">
                            {{ $enrollment->currentStage->name }}
                        </span>
                    </td>
                    <td class="text-center">
                        @php $days = (int) $enrollment->stage_entered_at->diffInDays(now()); @endphp
                        @php
                            $durationStats = $stageDurations[$enrollment->current_stage_id] ?? null;
                            $isOverAvg = $durationStats && $durationStats['avg'] > 0 && $days > ($durationStats['avg'] * 1.5);
                        @endphp
                        <span class="{{ $isOverAvg ? 'text-danger fw-500' : '' }}">
                            {{ $days }}
                            @if($isOverAvg)
                                <i class="fal fa-exclamation-triangle text-danger ms-1" title="Above 1.5x average ({{ $durationStats['avg'] }} days)"></i>
                            @endif
                        </span>
                    </td>
                    <td class="text-center">
                        @php $score = $scoreValues[$enrollment->partner_id] ?? null; @endphp
                        @if($score !== null)
                            <span class="badge bg-{{ $score >= 55 ? 'success' : ($score >= 30 ? 'warning' : 'danger') }}">{{ number_format($score, 0) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('plan.paths.show', $enrollment) }}" class="btn btn-xs btn-outline-primary">View</a>
                    </td>
                </tr>
                @empty
                <tr class="empty-row"><td colspan="{{ $canSeeAll ? 7 : 6 }}" class="text-center text-muted py-4">No partners enrolled in paths yet. Click "Enroll Partner" to get started.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Enroll Modal --}}
<div class="modal fade" id="enrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('plan.paths.enroll') }}" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Enroll Partner in Path</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Partner</label>
                        <select name="partner_id" class="form-select" required>
                            <option value="">Select Partner...</option>
                            @foreach($partners as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Path</label>
                        <select name="partner_path_id" class="form-select" required>
                            @foreach($paths as $path)
                                <option value="{{ $path->id }}">{{ $path->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Enroll</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Styles --}}
<style>
    .path-flow-container {
        overflow-x: auto;
        padding-bottom: 4px;
    }
    .path-flow {
        display: flex;
        align-items: center;
        min-width: max-content;
        padding: 8px 0;
        justify-content: center;
    }

    /* Stage Node */
    .path-stage-node {
        flex-shrink: 0;
        cursor: pointer;
    }
    .stage-card {
        width: 150px;
        border: 2px solid;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        text-align: center;
        transition: transform 0.15s, box-shadow 0.15s, opacity 0.2s;
    }
    .stage-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .stage-card.active {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.18);
        border-width: 3px;
    }
    .stage-card.dimmed {
        opacity: 0.35;
        transform: none;
    }
    .stage-card.dimmed:hover {
        opacity: 0.6;
        transform: translateY(-1px);
    }
    .stage-header {
        padding: 12px 8px 10px;
        color: #fff;
        font-size: 1.4rem;
        line-height: 1;
    }
    .stage-body {
        padding: 10px 8px 12px;
    }
    .stage-number {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #868e96;
        margin-bottom: 2px;
    }
    .stage-name {
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 6px;
    }
    .stage-stats {
        display: flex;
        justify-content: center;
        gap: 6px;
    }
    .partner-count-badge {
        font-size: 0.8rem;
        font-weight: 700;
    }
    .partner-count-badge i {
        margin-right: 3px;
    }

    /* Gate Connector */
    .path-gate-connector {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        position: relative;
        transition: opacity 0.2s;
    }
    .path-gate-connector.dimmed {
        opacity: 0.25;
    }
    .gate-line {
        width: 24px;
        height: 2px;
        background: #dee2e6;
    }
    .gate-badge {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        background: #e8f5e9;
        border: 1px solid #c8e6c9;
        font-size: 0.75rem;
        font-weight: 600;
        color: #2e7d32;
        white-space: nowrap;
        cursor: default;
    }
    .gate-badge.gate-empty {
        background: #f5f5f5;
        border-color: #e0e0e0;
        color: #9e9e9e;
    }
    .gate-count {
        font-size: 0.7rem;
    }
    .gate-arrow {
        margin-left: -1px;
        color: #dee2e6;
        font-size: 0.8rem;
    }

    /* Filtered table rows */
    #enrollments-table tr.filtered-out {
        display: none;
    }
    tr[data-analytics-stage].filtered-out {
        display: none;
    }
    .fs-xs {
        font-size: 0.7rem;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- ApexCharts: Stage Duration ---
    const chartData = @json($chartData);

    if (chartData.length > 0 && document.getElementById('stageDurationChart')) {
        new ApexCharts(document.getElementById('stageDurationChart'), {
            chart: {
                type: 'bar',
                height: 220,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '55%',
                    borderRadius: 4,
                    distributed: true,
                }
            },
            colors: chartData.map(d => d.color),
            series: [{
                name: 'Avg Days',
                data: chartData.map(d => d.avg_days),
            }],
            xaxis: {
                categories: chartData.map(d => d.stage),
                title: { text: 'Days', style: { fontSize: '11px', color: '#868e96' } },
            },
            yaxis: {
                labels: { style: { fontSize: '12px' } },
            },
            tooltip: {
                custom: function({ seriesIndex, dataPointIndex }) {
                    const d = chartData[dataPointIndex];
                    return '<div style="padding:8px 12px">'
                        + '<strong>' + d.stage + '</strong><br>'
                        + 'Avg: <b>' + d.avg_days + '</b> days<br>'
                        + 'Median: <b>' + d.median_days + '</b> days<br>'
                        + 'Partners: <b>' + d.partners + '</b>'
                        + '</div>';
                }
            },
            dataLabels: {
                enabled: true,
                formatter: (val) => val > 0 ? val + 'd' : '',
                style: { fontSize: '11px' },
            },
            legend: { show: false },
            grid: { borderColor: '#f1f1f1' },
        }).render();
    }

    // --- ApexCharts: PSM Breakdown (manager/admin only) ---
    @if($canSeeAll && $managementMetrics && $managementMetrics['psmBreakdown']->isNotEmpty())
    const psmData = @json($managementMetrics['psmBreakdown']);
    const stageNames = @json($stages->pluck('name'));
    const stageColors = @json($stages->pluck('color'));

    if (psmData.length > 0 && document.getElementById('psmBreakdownChart')) {
        const series = stageNames.map((stageName, idx) => ({
            name: stageName,
            data: psmData.map(psm => psm.by_stage[stageName] || 0),
            color: stageColors[idx],
        }));

        new ApexCharts(document.getElementById('psmBreakdownChart'), {
            chart: { type: 'bar', height: 260, stacked: true, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3 } },
            series: series,
            xaxis: {
                categories: psmData.map(p => p.psm_name),
                title: { text: 'Partners', style: { fontSize: '11px', color: '#868e96' } },
            },
            yaxis: { labels: { style: { fontSize: '12px' } } },
            tooltip: { shared: true, intersect: false },
            legend: { position: 'top', fontSize: '11px' },
            grid: { borderColor: '#f1f1f1' },
        }).render();
    }
    @endif

    // --- SmartTable ---
    if (typeof SmartTable !== 'undefined') {
        new SmartTable('#enrollments-table', { sortable: true, perPage: 20 });
    }
});

// --- Stage Filter (click flowchart nodes) ---
let activeStageId = null;

function toggleStageFilter(stageId, stageName) {
    if (activeStageId === stageId) {
        clearStageFilter();
        return;
    }
    activeStageId = stageId;
    applyFilter(stageId, stageName);
}

function clearStageFilter() {
    activeStageId = null;

    // Reset flowchart nodes
    document.querySelectorAll('.stage-card').forEach(c => c.classList.remove('active', 'dimmed'));
    document.querySelectorAll('.path-gate-connector').forEach(c => c.classList.remove('dimmed'));

    // Show all enrollment rows
    document.querySelectorAll('#enrollments-table tbody tr').forEach(r => r.classList.remove('filtered-out'));

    // Show all analytics rows
    document.querySelectorAll('tr[data-analytics-stage]').forEach(r => r.classList.remove('filtered-out'));

    // Hide filter UI
    document.getElementById('filterIndicator').classList.add('d-none');
    document.getElementById('clearFilterBtn').classList.add('d-none');

    updateEnrollmentCount();
}

function applyFilter(stageId, stageName) {
    const sid = String(stageId);

    // --- Flowchart: highlight selected, dim others ---
    document.querySelectorAll('.stage-card').forEach(c => c.classList.remove('active', 'dimmed'));
    document.querySelectorAll('.path-stage-node').forEach(node => {
        const card = node.querySelector('.stage-card');
        if (node.dataset.stageId === sid) {
            card.classList.add('active');
        } else {
            card.classList.add('dimmed');
        }
    });

    // Connectors: highlight ones adjacent to selected stage
    document.querySelectorAll('.path-gate-connector').forEach(conn => {
        const from = conn.dataset.fromStage;
        const to = conn.dataset.toStage;
        if (from === sid || to === sid) {
            conn.classList.remove('dimmed');
        } else {
            conn.classList.add('dimmed');
        }
    });

    // --- Enrollment table: filter to selected stage ---
    document.querySelectorAll('#enrollments-table tbody tr').forEach(row => {
        if (row.classList.contains('empty-row')) return;
        row.classList.toggle('filtered-out', row.dataset.stageId !== sid);
    });

    // --- Analytics table: highlight selected stage ---
    document.querySelectorAll('tr[data-analytics-stage]').forEach(row => {
        row.classList.toggle('filtered-out', row.dataset.analyticsStage !== sid);
    });

    // Show filter UI
    document.getElementById('filterStageName').textContent = stageName;
    document.getElementById('filterIndicator').classList.remove('d-none');
    document.getElementById('clearFilterBtn').classList.remove('d-none');

    updateEnrollmentCount();
}

function updateEnrollmentCount() {
    const allRows = document.querySelectorAll('#enrollments-table tbody tr:not(.empty-row)');
    let visible = 0;
    allRows.forEach(r => { if (!r.classList.contains('filtered-out')) visible++; });
    const label = activeStageId ? visible + '/' + allRows.length : allRows.length;
    document.getElementById('enrollmentCount').textContent = label;
}
</script>
@endsection
