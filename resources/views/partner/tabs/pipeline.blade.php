{{-- Pipeline Tab: Comprehensive Pipeline Analytics Dashboard --}}

@php
    // Currency formatting helper
    function fmtCurrency($val) {
        if (abs($val) >= 1000000) return '$' . number_format($val / 1000000, 1) . 'M';
        if (abs($val) >= 1000) return '$' . number_format($val / 1000, 0) . 'k';
        return '$' . number_format($val, 0);
    }

    $winRateColor = $winRate >= 40 ? 'success' : ($winRate >= 20 ? 'warning' : 'danger');
    $coverageColor = $coverageRatio === null ? 'secondary' : ($coverageRatio >= 3 ? 'success' : ($coverageRatio >= 1 ? 'warning' : 'danger'));
@endphp

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 1: KPI Command Bar
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row mb-g">
    {{-- Open Pipeline --}}
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Open Pipeline</p>
                    <span class="h3 mb-0 fw-500">{{ fmtCurrency($currentPipelineValue) }}</span>
                    @if($historicalPipelineValue > 0)
                    <div class="mt-1">
                        <span class="badge bg-{{ $pipelineChangePct >= 0 ? 'success' : 'danger' }} bg-opacity-25 text-{{ $pipelineChangePct >= 0 ? 'success' : 'danger' }} small">
                            <i class="fal fa-arrow-{{ $pipelineChangePct >= 0 ? 'up' : 'down' }} me-1"></i>{{ abs($pipelineChangePct) }}% <span class="fw-400">vs 90d</span>
                        </span>
                    </div>
                    @endif
                </div>
                <div class="width-5 height-5 bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-chart-bar fa-lg text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Win Rate --}}
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Win Rate (12mo)</p>
                    <span class="h3 mb-0 fw-500">{{ $winRate }}%</span>
                    <div class="mt-1">
                        <small class="text-muted">{{ $won12m }}W / {{ $lost12m }}L</small>
                    </div>
                </div>
                <div class="width-5 height-5 bg-{{ $winRateColor }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-trophy fa-lg text-{{ $winRateColor }}"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- New Business 30d --}}
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">New Biz (30d)</p>
                    <span class="h3 mb-0 fw-500">{{ fmtCurrency($newBiz30Amount) }}</span>
                    <div class="mt-1">
                        <small class="text-muted">{{ $newBiz30Count }} {{ Str::plural('deal', $newBiz30Count) }}</small>
                    </div>
                </div>
                <div class="width-5 height-5 bg-{{ $newBiz30Count > 0 ? 'success' : 'secondary' }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-plus-circle fa-lg text-{{ $newBiz30Count > 0 ? 'success' : 'secondary' }}"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Stale Deals --}}
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Stale Deals</p>
                    <span class="h3 mb-0 fw-500 {{ $staleDealCount > 0 ? 'text-danger' : '' }}">{{ $staleDealCount }}</span>
                    @if($staleDealCount > 0)
                    <div class="mt-1">
                        <small class="text-danger">{{ fmtCurrency($staleDealAmount) }} at risk</small>
                    </div>
                    @else
                    <div class="mt-1">
                        <small class="text-success">All active</small>
                    </div>
                    @endif
                </div>
                <div class="width-5 height-5 bg-{{ $staleDealCount > 0 ? 'danger' : 'success' }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-{{ $staleDealCount > 0 ? 'exclamation-triangle' : 'check-circle' }} fa-lg text-{{ $staleDealCount > 0 ? 'danger' : 'success' }}"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Closing This Quarter --}}
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Closing {{ $currentFQ }}</p>
                    <span class="h3 mb-0 fw-500">{{ fmtCurrency($closingThisQtrAmount) }}</span>
                    <div class="mt-1">
                        <small class="text-muted">{{ $closingThisQtrCount }} {{ Str::plural('deal', $closingThisQtrCount) }}</small>
                    </div>
                </div>
                <div class="width-5 height-5 bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-calendar-check fa-lg text-info"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Weighted Coverage --}}
    <div class="col-sm-6 col-xl-2 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Weighted Coverage</p>
                    @if($coverageRatio !== null)
                        <span class="h3 mb-0 fw-500 text-{{ $coverageColor }}">{{ $coverageRatio }}x</span>
                        <div class="mt-1">
                            <small class="text-muted">vs FY{{ $currentFY }} target</small>
                        </div>
                    @else
                        <span class="h3 mb-0 fw-500 text-muted">N/A</span>
                        <div class="mt-1">
                            <small class="text-muted">No goals set</small>
                        </div>
                    @endif
                </div>
                <div class="width-5 height-5 bg-{{ $coverageColor }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-shield-check fa-lg text-{{ $coverageColor }}"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 2: Goal Attainment + History Chart
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    {{-- FY Goal Attainment --}}
    <div class="col-lg-5">
        <div class="panel panel-icon" id="panel-goal-attainment">
            <div class="panel-hdr">
                <h2> FY{{ $currentFY }} <span class="fw-light"><i>Goal Attainment</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    @if($goal)
                        <div class="row mb-3">
                            <div class="col-6 text-center">
                                <div id="gauge-sourced" style="height: 170px;"></div>
                                <div class="fs-sm fw-500 mt-n2">Sourced</div>
                            </div>
                            <div class="col-6 text-center">
                                <div id="gauge-influenced" style="height: 170px;"></div>
                                <div class="fs-sm fw-500 mt-n2">Influenced</div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 fs-sm">
                                <thead>
                                    <tr class="text-muted">
                                        <th></th>
                                        <th class="text-end">Sourced</th>
                                        <th class="text-end">Influenced</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-muted">Target</td>
                                        <td class="text-end fw-500">{{ fmtCurrency($sourcedTarget) }}</td>
                                        <td class="text-end fw-500">{{ fmtCurrency($influencedTarget) }}</td>
                                        <td class="text-end fw-500">{{ fmtCurrency($sourcedTarget + $influencedTarget) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Closed Won</td>
                                        <td class="text-end text-success fw-500">{{ fmtCurrency($closedWonSourced) }}</td>
                                        <td class="text-end text-success fw-500">{{ fmtCurrency($closedWonInfluenced) }}</td>
                                        <td class="text-end text-success fw-500">{{ fmtCurrency($closedWonSourced + $closedWonInfluenced) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Shortfall</td>
                                        <td class="text-end {{ $sourcedShortfall > 0 ? 'text-danger' : 'text-success' }} fw-500">
                                            {{ $sourcedShortfall > 0 ? fmtCurrency($sourcedShortfall) : '-' }}
                                        </td>
                                        <td class="text-end {{ $influencedShortfall > 0 ? 'text-danger' : 'text-success' }} fw-500">
                                            {{ $influencedShortfall > 0 ? fmtCurrency($influencedShortfall) : '-' }}
                                        </td>
                                        <td class="text-end {{ ($sourcedShortfall + $influencedShortfall) > 0 ? 'text-danger' : 'text-success' }} fw-500">
                                            {{ ($sourcedShortfall + $influencedShortfall) > 0 ? fmtCurrency($sourcedShortfall + $influencedShortfall) : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Weighted Pipeline</td>
                                        <td class="text-end" colspan="2"></td>
                                        <td class="text-end fw-500">{{ fmtCurrency($weightedPipeline) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fal fa-bullseye fa-2x mb-2 opacity-50"></i>
                            <p>No goals configured for FY{{ $currentFY }}.</p>
                            <a href="{{ route('partner-goals.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fal fa-cog me-1"></i> Set Goals
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Opportunity History Chart (existing) --}}
    <div class="col-lg-7">
        <div class="panel panel-icon panel-locked" id="panel-pipeline-history">
            <div class="panel-hdr">
                <h2> Sourced + Influenced <span class="fw-light"><i>NNACV (52 weeks)</i></span></h2>
            </div>
            <div class="panel-container border-faded border-start-0 border-end-0 border-bottom-0">
                <div class="panel-content">
                    <div id="pipeline-opportunity-chart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 3: Pipeline Velocity + Product Area
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    {{-- Pipeline Velocity by Stage --}}
    <div class="col-lg-7">
        <div class="panel panel-icon" id="panel-pipeline-velocity">
            <div class="panel-hdr">
                <h2> Pipeline <span class="fw-light"><i>Velocity by Stage</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="pipeline-velocity-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pipeline by Product Area --}}
    <div class="col-lg-5">
        <div class="panel panel-icon" id="panel-pipeline-product-area">
            <div class="panel-hdr">
                <h2> Pipeline by <span class="fw-light"><i>Product Area</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="pipeline-product-area-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 3.5: Product Whitespace & Mix
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    {{-- Product Mix Treemap --}}
    <div class="col-lg-7">
        <div class="panel panel-icon" id="panel-product-whitespace">
            <div class="panel-hdr">
                <h2>Product Mix <span class="fw-light"><i>& Whitespace</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="whitespace-treemap-chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Product Area Goals --}}
    <div class="col-lg-5">
        <div class="panel panel-icon" id="panel-whitespace-goals">
            <div class="panel-hdr">
                <h2>Product Area <span class="fw-light"><i>Goals</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Product Area</th>
                                <th class="text-end">Pipeline</th>
                                <th class="text-end">Target</th>
                                <th class="text-end pe-3">Attainment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($whitespaceSummary as $ws)
                            <tr class="{{ !$ws['in_pipeline'] ? 'table-danger bg-opacity-10' : '' }}">
                                <td class="ps-3">
                                    @if(!$ws['in_pipeline'])
                                        <i class="fal fa-exclamation-triangle text-danger me-1" title="Whitespace — not in pipeline"></i>
                                    @else
                                        <i class="fal fa-check-circle text-success me-1"></i>
                                    @endif
                                    {{ $ws['area'] }}
                                    <span class="text-muted small">({{ $ws['opp_count'] }})</span>
                                </td>
                                <td class="text-end">{{ fmtCurrency($ws['pipeline_value']) }}</td>
                                <td class="text-end">{{ $ws['target'] > 0 ? fmtCurrency($ws['target']) : '—' }}</td>
                                <td class="text-end pe-3">
                                    @if($ws['attainment_pct'] !== null)
                                        <span class="badge bg-{{ $ws['attainment_pct'] >= 80 ? 'success' : ($ws['attainment_pct'] >= 30 ? 'warning' : 'danger') }} bg-opacity-25 text-{{ $ws['attainment_pct'] >= 80 ? 'success' : ($ws['attainment_pct'] >= 30 ? 'warning' : 'danger') }}">
                                            {{ $ws['attainment_pct'] }}%
                                        </span>
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
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 4: Pipeline Funnel + Risk & Alerts
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    {{-- Pipeline Funnel --}}
    <div class="col-lg-6">
        <div class="panel panel-icon" id="panel-pipeline-funnel">
            <div class="panel-hdr">
                <h2> Pipeline <span class="fw-light"><i>Progression</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div id="pipeline-funnel-chart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Risk & Alerts (enhanced) --}}
    <div class="col-lg-6">
        <div class="panel panel-icon" id="panel-pipeline-alerts">
            <div class="panel-hdr">
                <h2> Pipeline <span class="fw-light"><i>Insights & Alerts</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content" style="max-height: 400px; overflow-y: auto;">
                    {{-- Computed Pipeline Insights --}}
                    @forelse($pipelineAlerts as $alert)
                        <div class="d-flex align-items-start mb-3">
                            <div class="width-3 height-3 rounded-circle bg-{{ $alert['level'] }} bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0 me-3 mt-1">
                                <i class="fal {{ $alert['icon'] }} text-{{ $alert['level'] }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="badge bg-{{ $alert['level'] }} bg-opacity-25 text-{{ $alert['level'] }} mb-1 fw-500">{{ $alert['title'] }}</span>
                                    @if($alert['action_url'])
                                    <a href="{{ $alert['action_url'] }}" class="btn btn-xs btn-outline-{{ $alert['level'] }} ms-2 flex-shrink-0">
                                        {{ $alert['action_label'] }} <i class="fal fa-arrow-right ms-1"></i>
                                    </a>
                                    @endif
                                </div>
                                <p class="mb-0 fs-sm mt-1">{!! e($alert['message']) !!}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3">
                            <i class="fal fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted mb-0">Pipeline looks healthy. No alerts.</p>
                        </div>
                    @endforelse

                    {{-- Server-side Change Alerts --}}
                    @if($serverAlerts->count() > 0)
                    <hr class="my-3">
                    <h6 class="text-muted small mb-3"><i class="fal fa-bell me-1"></i> Recent Changes</h6>
                    @foreach($serverAlerts as $sa)
                        @php
                            $saColor = match($sa->severity) {
                                'critical' => 'danger',
                                'warning' => 'warning',
                                'positive' => 'success',
                                default => 'info',
                            };
                            $saIcon = match($sa->type) {
                                'new_opportunity' => 'fa-star',
                                'stage_change' => 'fa-exchange-alt',
                                'close_date_slip' => 'fa-calendar-minus',
                                default => 'fa-info-circle',
                            };
                        @endphp
                        <div class="d-flex align-items-start mb-3">
                            <div class="width-3 height-3 rounded-circle bg-{{ $saColor }} bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0 me-3 mt-1">
                                <i class="fal {{ $saIcon }} text-{{ $saColor }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="badge bg-{{ $saColor }} bg-opacity-25 text-{{ $saColor }} mb-1 fw-500">{{ $sa->title }}</span>
                                    <span class="text-muted fs-nano flex-shrink-0 ms-2">{{ $sa->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mb-1 fs-sm">{{ $sa->description }}</p>
                                <div>
                                    <form method="POST" action="{{ route('act.alerts.dismiss', $sa) }}" class="d-inline">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-xs btn-outline-secondary">Dismiss</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ROW 5: Opportunities Table (enhanced)
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Opportunities ({{ $opportunities->count() }})</div>
            </div>
            <div class="card-body">
                @if($opportunities->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fal fa-chart-line fa-2x mb-2 opacity-50"></i>
                        <p>No opportunities found for this partner.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover table-striped mb-0" id="pipeline-opps-table">
                            <thead>
                                <tr>
                                    <th>Opportunity Name</th>
                                    <th>Stage</th>
                                    <th class="text-end">Amount</th>
                                    <th>Revenue Type</th>
                                    <th>Probability</th>
                                    <th>Product Area</th>
                                    <th>Owner</th>
                                    <th>Close Date</th>
                                    <th>Age</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($opportunities as $opp)
                                @php
                                    $prob = (float) ($opp->probability ?? 0);
                                    $probColor = $prob >= 70 ? 'success' : ($prob >= 30 ? 'warning' : 'danger');
                                    $ageDays = (int) abs($opp->created_at->diffInDays(now()));
                                @endphp
                                <tr>
                                    <td class="fw-500">{{ $opp->opportunity_name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $opp->stage === 'Closed Won' ? 'success' : ($opp->stage === 'Closed Lost' ? 'danger' : 'info') }} opacity-75">
                                            {{ $opp->stage ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">${{ number_format($opp->amount, 0) }}</td>
                                    <td>
                                        @if($opp->revenue_type)
                                            <span class="badge bg-{{ $opp->revenue_type === 'sourced' ? 'primary' : 'warning' }} opacity-75">
                                                {{ ucfirst($opp->revenue_type) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($opp->probability !== null)
                                            <span class="text-{{ $probColor }} fw-500">{{ number_format($prob, 0) }}%</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $opp->product_area ?? '-' }}</td>
                                    <td class="text-truncate" style="max-width: 120px;">{{ $opp->owner ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $opp->close_date ? \Carbon\Carbon::parse($opp->close_date)->format('M j, Y') : '-' }}</td>
                                    <td class="text-nowrap {{ $ageDays > 90 ? 'text-danger fw-500' : 'text-muted' }}">{{ $ageDays }}d</td>
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
    var bv = (cm.bootstrapVars && cm.bootstrapVars.bodyColor) || null;
    var pr = (cm.primary && cm.primary[400]) || null;
    var pr6 = (cm.primary && cm.primary[600]) || null;
    var su = (cm.success && cm.success[400]) || null;
    var dng = (cm.danger && cm.danger[500]) || null;
    var wrn = (cm.warning && cm.warning[500]) || null;
    var inf = (cm.info && cm.info[500]) || null;

    var bodyColorHex = bv ? bv.hex : '#6c757d';
    var bodyColor01 = bv ? bv.rgba(0.1) : 'rgba(108,117,125,0.1)';
    var bodyColor07 = bv ? bv.rgba(0.7) : 'rgba(108,117,125,0.7)';
    var bodyColor08 = bv ? bv.rgba(0.8) : 'rgba(108,117,125,0.8)';
    var primaryHex = pr ? pr.hex : '#886ab5';
    var primary6Hex = pr6 ? pr6.hex : '#7454a5';
    var successHex = su ? su.hex : '#1dc9b7';
    var dangerHex = dng ? dng.hex : '#fd3995';
    var warningHex = wrn ? wrn.hex : '#ffc241';
    var infoHex = inf ? inf.hex : '#33b5e5';

    function fmtCurrency(val) {
        if (val >= 1000000) return '$' + (val / 1000000).toFixed(1) + 'M';
        if (val >= 1000) return '$' + Math.round(val / 1000) + 'k';
        return '$' + Math.round(val);
    }

    function attainmentColor(pct) {
        if (pct >= 80) return successHex;
        if (pct >= 50) return warningHex;
        return dangerHex;
    }

    // ── Goal Attainment Gauges ──
    @if($goal)
    var sourcedPct = {{ $sourcedAttainmentPct }};
    var influencedPct = {{ $influencedAttainmentPct }};

    function renderGauge(el, value, label, color) {
        new ApexCharts(document.querySelector(el), {
            chart: { type: 'radialBar', height: 170, sparkline: { enabled: true } },
            series: [Math.min(value, 100)],
            plotOptions: {
                radialBar: {
                    startAngle: -135, endAngle: 135,
                    hollow: { size: '60%' },
                    track: { background: bodyColor01, strokeWidth: '100%' },
                    dataLabels: {
                        name: { show: false },
                        value: { show: true, offsetY: 5, color: bodyColorHex, fontSize: '22px', fontWeight: 600,
                            formatter: function(val) { return val + '%'; } }
                    }
                }
            },
            colors: [color],
            stroke: { lineCap: 'round' }
        }).render();
    }

    renderGauge('#gauge-sourced', sourcedPct, 'Sourced', attainmentColor(sourcedPct));
    renderGauge('#gauge-influenced', influencedPct, 'Influenced', attainmentColor(influencedPct));
    @endif

    // ── Sourced + Influenced NNACV (stacked area) vs Opportunity Count (line) ──
    var opportunityHistory = @json($opportunityHistory);
    if (opportunityHistory.length > 0) {
        function niceMax(dataMax, ticks) {
            if (!dataMax || dataMax <= 0) return ticks;
            var rawStep = dataMax / ticks;
            var mag = Math.pow(10, Math.floor(Math.log10(rawStep)));
            var r = rawStep / mag;
            var nice;
            if (r <= 1) nice = 1;
            else if (r <= 1.5) nice = 1.5;
            else if (r <= 2) nice = 2;
            else if (r <= 3) nice = 3;
            else if (r <= 5) nice = 5;
            else nice = 10;
            return nice * mag * ticks;
        }

        var maxStacked = Math.max.apply(null, opportunityHistory.map(function(h) {
            return parseFloat(h.sourced_amount) + parseFloat(h.influenced_amount);
        }));
        var yAxisMax = niceMax(maxStacked * 1.05, 6) || 600000;

        var maxCount = Math.max.apply(null, opportunityHistory.map(function(h) { return h.opportunity_count; }));
        var countAxisMax = niceMax(maxCount * 1.05, 6) || 30;

        new ApexCharts(document.querySelector('#pipeline-opportunity-chart'), {
            series: [
                { name: 'Sourced', type: 'area', data: opportunityHistory.map(function(h) { return parseFloat(h.sourced_amount); }) },
                { name: 'Influenced', type: 'area', data: opportunityHistory.map(function(h) { return parseFloat(h.influenced_amount); }) },
                { name: 'Opportunity Count', type: 'line', data: opportunityHistory.map(function(h) { return h.opportunity_count; }) }
            ],
            chart: { height: 335, type: 'line', zoom: { enabled: false }, stacked: true, toolbar: { show: false }, fontFamily: 'inherit', parentHeightOffset: 0 },
            colors: [primaryHex, successHex, bodyColor08],
            stroke: { width: [1, 1, 2], curve: 'smooth', colors: [primaryHex, successHex, bodyColor08], dashArray: [4, 0, 0] },
            fill: { type: ['solid', 'solid', 'solid'], opacity: [0.15, 0.15, 1] },
            markers: { size: [3, 3, 3], colors: [primaryHex, successHex, bodyColor07], strokeColors: [primaryHex, successHex, bodyColor07], strokeWidth: 2, hover: { sizeOffset: 2 } },
            xaxis: { categories: opportunityHistory.map(function(h) { return h.snapshot_month; }), labels: { style: { colors: bodyColorHex, fontSize: '12px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: [
                { seriesName: 'Sourced', min: 0, max: yAxisMax, tickAmount: 6, labels: { style: { colors: bodyColorHex, fontSize: '12px' }, formatter: function(val) { if (val >= 1000000) return '$' + Math.round(val / 1000000) + 'M'; if (val >= 1000) return '$' + Math.round(val / 1000) + 'k'; return '$' + Math.round(val); } } },
                { seriesName: 'Sourced', show: false },
                { seriesName: 'Opportunity Count', opposite: true, min: 0, max: countAxisMax, tickAmount: 6, labels: { style: { colors: bodyColorHex, fontSize: '12px' }, formatter: function(val) { return Math.round(val).toLocaleString(); } } }
            ],
            legend: { show: true, position: 'top', horizontalAlign: 'center', fontSize: '14px', fontFamily: 'inherit', labels: { colors: bodyColorHex }, markers: { width: 18, height: 6, radius: 2 }, itemMargin: { horizontal: 12, vertical: 0 } },
            grid: { borderColor: bodyColor01, strokeDashArray: 3, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } },
            dataLabels: { enabled: false },
            tooltip: { shared: true, intersect: false, theme: 'dark', y: [
                { formatter: function(val) { return '$' + Math.round(val).toLocaleString(); } },
                { formatter: function(val) { return '$' + Math.round(val).toLocaleString(); } },
                { formatter: function(val) { return Math.round(val).toLocaleString(); } }
            ] }
        }).render();
    } else {
        document.querySelector('#pipeline-opportunity-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-chart-area fa-2x mb-2 opacity-50"></i><p>No opportunity history yet</p></div>';
    }

    // ── Pipeline Velocity by Stage ──
    var velocityData = @json($stageVelocity);
    if (velocityData.length > 0) {
        new ApexCharts(document.querySelector('#pipeline-velocity-chart'), {
            series: [{ name: 'Avg Days in Stage', data: velocityData.map(function(s) { return s.avg_days; }) }],
            chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3, distributed: true } },
            colors: velocityData.map(function(s) {
                if (s.avg_days > 60) return dangerHex;
                if (s.avg_days > 30) return warningHex;
                return successHex;
            }),
            xaxis: { categories: velocityData.map(function(s) { return s.stage; }), labels: { style: { colors: bodyColorHex, fontSize: '12px' } } },
            yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '12px' } } },
            dataLabels: {
                enabled: true,
                formatter: function(val, opt) {
                    var s = velocityData[opt.dataPointIndex];
                    return val + 'd  |  ' + s.count + ' deals';
                },
                style: { fontSize: '11px', colors: ['#fff'] }
            },
            tooltip: {
                theme: 'dark',
                custom: function(opts) {
                    var idx = opts.dataPointIndex;
                    var s = velocityData[idx];
                    return '<div style="padding:8px 12px"><b>' + s.stage + '</b><br>' +
                        'Avg Days: ' + s.avg_days + '<br>' +
                        'Deals: ' + s.count + '<br>' +
                        'Amount: ' + fmtCurrency(s.amount) + '</div>';
                }
            },
            grid: { borderColor: bodyColor01, strokeDashArray: 3 },
            legend: { show: false }
        }).render();
    } else {
        document.querySelector('#pipeline-velocity-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-tachometer-alt fa-2x mb-2 opacity-50"></i><p>No open pipeline to measure velocity</p></div>';
    }

    // ── Pipeline by Product Area ──
    var productAreaData = @json($pipelineByProductArea);
    var paLabels = Object.keys(productAreaData);
    var paValues = Object.values(productAreaData);
    if (paLabels.length > 0) {
        var pri = (cm.primary || {});
        var paColors = [
            (pri[500] || {}).hex || '#886ab5',
            (pri[400] || {}).hex || '#9B7FC5',
            (pri[300] || {}).hex || '#B09AD5',
            (pri[200] || {}).hex || '#C5B5E5',
            (pri[100] || {}).hex || '#DAD0F5',
            infoHex, warningHex, successHex
        ];
        new ApexCharts(document.querySelector('#pipeline-product-area-chart'), {
            series: [{ name: 'Pipeline', data: paValues }],
            chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { horizontal: true, barHeight: '65%', borderRadius: 3, distributed: true } },
            colors: paColors,
            xaxis: { categories: paLabels, labels: { style: { colors: bodyColorHex, fontSize: '12px' }, formatter: function(val) { return fmtCurrency(val); } } },
            yaxis: { labels: { style: { colors: bodyColorHex, fontSize: '12px' } } },
            dataLabels: {
                enabled: true,
                formatter: function(val) { return fmtCurrency(val); },
                style: { fontSize: '11px', fontWeight: 600, colors: ['#fff'] }
            },
            tooltip: { theme: 'dark', y: { formatter: function(val) { return '$' + Math.round(val).toLocaleString(); } } },
            legend: { show: false },
            grid: { borderColor: bodyColor01, strokeDashArray: 3 }
        }).render();
    } else {
        document.querySelector('#pipeline-product-area-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-cubes fa-2x mb-2 opacity-50"></i><p>No product area data</p></div>';
    }

    // ── Product Whitespace Treemap ──
    var wsData = @json($whitespaceSummary);
    if (wsData.length > 0) {
        var treemapData = wsData.map(function(ws) {
            var val = ws.in_pipeline ? Math.max(ws.pipeline_value, 10000) : 50000;
            var color;
            if (!ws.in_pipeline) {
                color = '#adb5bd';
            } else if (ws.attainment_pct !== null && ws.attainment_pct >= 80) {
                color = successHex;
            } else if (ws.attainment_pct !== null && ws.attainment_pct >= 30) {
                color = warningHex;
            } else if (ws.target > 0) {
                color = dangerHex;
            } else {
                color = primaryHex;
            }
            return { x: ws.area + (ws.in_pipeline ? '' : ' (whitespace)'), y: val, fillColor: color };
        });

        new ApexCharts(document.querySelector('#whitespace-treemap-chart'), {
            series: [{ data: treemapData }],
            chart: { type: 'treemap', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { treemap: { distributed: true, enableShades: false } },
            dataLabels: {
                enabled: true,
                formatter: function(text, op) {
                    var ws = wsData[op.dataPointIndex];
                    return ws.area + '\n' + (ws.in_pipeline ? fmtCurrency(ws.pipeline_value) : 'No pipeline');
                },
                style: { fontSize: '12px', fontWeight: 600, colors: ['#fff'] }
            },
            tooltip: {
                theme: 'dark',
                custom: function(opts) {
                    var idx = opts.dataPointIndex;
                    var ws = wsData[idx];
                    return '<div style="padding:8px 12px"><b>' + ws.area + '</b><br>' +
                        'Pipeline: ' + fmtCurrency(ws.pipeline_value) + ' (' + ws.opp_count + ' opps)<br>' +
                        (ws.target > 0 ? 'Target: ' + fmtCurrency(ws.target) + '<br>Gap: ' + fmtCurrency(ws.gap) : 'No target set') +
                        '</div>';
                }
            },
            legend: { show: false }
        }).render();
    } else {
        document.querySelector('#whitespace-treemap-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-th-large fa-2x mb-2 opacity-50"></i><p>No product areas configured</p></div>';
    }

    // ── Pipeline Funnel ──
    var pipelineFunnel = @json($pipelineFunnel);
    var funnelLabels = [];
    var funnelValues = [];
    Object.keys(pipelineFunnel).forEach(function(key) {
        if (pipelineFunnel[key] > 0) {
            funnelLabels.push(key);
            funnelValues.push(pipelineFunnel[key]);
        }
    });
    if (funnelValues.length > 0) {
        var pri = (cm.primary || {});
        var funnelColors = [
            (pri[500] || {}).hex || '#886ab5',
            (pri[400] || {}).hex || '#9B7FC5',
            (pri[300] || {}).hex || '#B09AD5',
            (pri[200] || {}).hex || '#C5B5E5',
            (pri[100] || {}).hex || '#DAD0F5',
            ((cm.danger || {})[500] || {}).hex || '#fd3995',
            ((cm.success || {})[500] || {}).hex || '#1dc9b7'
        ];
        new ApexCharts(document.querySelector('#pipeline-funnel-chart'), {
            series: [{ name: 'NNACV', data: funnelValues }],
            chart: { type: 'bar', height: 350, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { borderRadius: 0, horizontal: true, distributed: true, barHeight: '80%', isFunnel: true } },
            states: { hover: { filter: { type: 'darken', value: 0.85 } } },
            colors: funnelColors,
            xaxis: { categories: funnelLabels, labels: { style: { colors: bodyColorHex } } },
            yaxis: { labels: { show: false } },
            dataLabels: {
                enabled: true,
                formatter: function(val, opt) {
                    var label = opt.w.globals.labels[opt.dataPointIndex];
                    return label + ':  ' + fmtCurrency(val);
                },
                style: { fontSize: '12px', fontWeight: 600, colors: ['#fff'] },
                dropShadow: { enabled: false }
            },
            legend: { show: false },
            grid: { show: false, padding: { left: 20, right: 20 }, borderColor: bodyColor01 },
            tooltip: { theme: 'dark', y: { formatter: function(val) { return '$' + Math.round(val).toLocaleString(); } } }
        }).render();
    } else {
        document.querySelector('#pipeline-funnel-chart').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-filter fa-2x mb-2 opacity-50"></i><p>No pipeline data yet</p></div>';
    }

    // ── SmartTables ──
    if (window.SmartTables && document.querySelector('#pipeline-opps-table tbody tr td:not([colspan])')) {
        new window.SmartTables('pipeline-opps-table', {
            perPage: 15,
            sortable: true,
            responsive: { enabled: true, breakpoint: 768, columnPriorities: { 0: 1, 2: 2, 1: 3, 7: 4, 4: 5, 3: 6, 5: 7, 6: 8, 8: 9 } }
        });
    }
})();
</script>
