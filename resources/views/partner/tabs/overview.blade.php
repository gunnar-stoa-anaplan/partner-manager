{{-- Overview Tab: Partner Path + Business Metrics + Health Score --}}

{{-- Partner Path Progress --}}
@if($pathData->isNotEmpty())
<div class="row">
    <div class="col-12">
        @foreach($pathData as $pd)
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                    <i class="fal fa-route me-1"></i>
                    {{ $pd['enrollment']->path->name }}
                    @if($pd['enrollment']->path->is_default)
                        <span class="badge bg-primary ms-1 fs-xs">Default</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted fs-sm">
                        Enrolled {{ $pd['enrollment']->enrolled_at->diffForHumans() }}
                    </span>
                    <a href="{{ route('plan.paths.show', $pd['enrollment']) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fal fa-arrow-right me-1"></i>Full Review
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- Stage progress bar --}}
                <div class="d-flex align-items-center mb-4">
                    @foreach($pd['stages'] as $stage)
                    <div class="text-center flex-fill position-relative">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto
                            {{ $stage->id === $pd['enrollment']->current_stage_id ? 'border border-3 shadow' : '' }}"
                             style="width: 44px; height: 44px;
                                    background-color: {{ $stage->stage_number <= $pd['enrollment']->currentStage->stage_number ? $stage->color : '#e9ecef' }};
                                    border-color: {{ $stage->color }} !important;">
                            <i class="{{ $stage->icon ?? 'fal fa-circle' }} {{ $stage->stage_number <= $pd['enrollment']->currentStage->stage_number ? 'text-white' : 'text-muted' }}" style="font-size: 1rem;"></i>
                        </div>
                        <div class="mt-1 fs-xs {{ $stage->id === $pd['enrollment']->current_stage_id ? 'fw-700' : 'text-muted' }}">
                            {{ $stage->name }}
                        </div>
                    </div>
                    @if(!$loop->last)
                    <div class="flex-fill" style="height: 3px; background: {{ $stage->stage_number < $pd['enrollment']->currentStage->stage_number ? $stage->color : '#e9ecef' }}; margin-top: -18px; min-width: 30px;"></div>
                    @endif
                    @endforeach
                </div>

                <div class="row">
                    {{-- Current stage info --}}
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge fs-sm me-2" style="background-color: {{ $pd['enrollment']->currentStage->color }}">
                                {{ $pd['enrollment']->currentStage->name }}
                            </span>
                            <span class="text-muted fs-sm">{{ $pd['daysInStage'] }} days</span>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between fs-xs text-muted mb-1">
                                <span>Path Progress</span>
                                <span>{{ $pd['stageProgress'] }}%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: {{ $pd['stageProgress'] }}%; background-color: {{ $pd['enrollment']->currentStage->color }}"></div>
                            </div>
                        </div>
                        @if($latestScorecard)
                        <div class="d-flex align-items-center fs-sm mt-2">
                            <span class="text-muted me-2">Health Score:</span>
                            <span class="badge bg-{{ $latestScorecard->overall_score >= 55 ? 'success' : ($latestScorecard->overall_score >= 30 ? 'warning' : 'danger') }}">
                                {{ number_format($latestScorecard->overall_score, 0) }}
                            </span>
                            @if($latestScorecard->tier)
                            <span class="text-muted ms-2">{{ $latestScorecard->tier }}</span>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Gate requirements for next stage --}}
                    <div class="col-md-8">
                        @if($pd['nextStage'])
                        <div class="mb-2 fs-sm fw-500">
                            Gate Requirements
                            <i class="fal fa-arrow-right mx-1 text-muted"></i>
                            <span class="badge" style="background-color: {{ $pd['nextStage']->color }}">{{ $pd['nextStage']->name }}</span>
                            @if($pd['totalGates'] > 0)
                            <span class="ms-2 text-muted">({{ $pd['metCount'] }}/{{ $pd['totalGates'] }} met)</span>
                            @endif
                        </div>
                        @if($pd['gateResults']->isNotEmpty())
                        <div class="row g-2">
                            @foreach($pd['gateResults'] as $gate)
                            <div class="col-md-6 col-lg-4">
                                <div class="border rounded p-2 {{ $gate['met'] ? 'border-success bg-success bg-opacity-10' : 'border-danger bg-danger bg-opacity-10' }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-xs fw-500">{{ $gate['requirement_label'] }}</span>
                                        <i class="fal {{ $gate['met'] ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }}"></i>
                                    </div>
                                    <div class="fs-xs text-muted mt-1">
                                        @if($gate['unit'] === 'currency')
                                            ${{ number_format($gate['actual_value']) }} / ${{ number_format($gate['threshold']) }}
                                        @elseif($gate['unit'] === 'percentage')
                                            {{ number_format($gate['actual_value'], 1) }}% / {{ $gate['threshold'] }}%
                                        @else
                                            {{ $gate['actual_value'] }} / {{ $gate['threshold'] }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-muted fs-sm mb-0">No gate requirements configured for this transition.</p>
                        @endif
                        @else
                        <div class="text-center py-3">
                            <i class="fal fa-crown text-warning fa-2x mb-2"></i>
                            <p class="text-muted mb-0">Partner has reached the final stage.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if(app(\App\Services\ModuleRegistry::class)->isEnabled('ai_agents'))
{{-- AI Partner Insights --}}
<div class="row mb-g" id="ai-insights-partner-panel" data-advisor-url="{{ route('partners.advisor', $partner) }}">
    <div class="col-12">
        <div class="card shadow-0 border-primary border-opacity-25 mb-0">
            <div class="card-header bg-primary bg-opacity-10">
                <div class="d-flex align-items-center">
                    <svg class="sa-icon sa-icon-sm me-2"><use href="/icons/sprite.svg#cpu"></use></svg>
                    <span class="fw-medium">AI Partner Insights</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted fs-sm mb-3">
                    Get AI-powered health assessment and strategic recommendations for this partner based on their metrics, scorecards, and activity.
                </p>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-primary" id="ai-insights-btn">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#cpu"></use></svg>
                        Get AI Insights
                    </button>
                    <div class="ai-generate-spinner d-none">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="text-muted fs-sm ms-1">Analyzing partner (10-30s)...</span>
                    </div>
                </div>
                <div class="ai-generate-error text-danger fs-sm d-none"></div>
                <div class="ai-insights-results d-none"></div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    {{-- Business Metrics --}}
    <div class="col-lg-{{ $latestScorecard ? '6' : '12' }}">
        <div class="panel panel-icon panel-locked" id="panel-overview-metrics">
            <div class="panel-hdr">
                <h2> Business <span class="fw-light"><i>Metrics</i></span></h2>
                <div class="panel-toolbar">
                    <div class="btn-group btn-group-sm" id="metrics-period-toggle" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-period="quarter">{{ $businessMetrics['quarter']['label'] }}</button>
                        <button type="button" class="btn btn-outline-primary" data-period="annual">{{ $businessMetrics['annual']['label'] }}</button>
                    </div>
                </div>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div class="d-flex mt-3">
                        <span class="h6 m-0 fw-300">Sourced Revenue</span>
                        <span class="h6 d-inline-block ms-auto" id="metric-sourced-value">{{ $businessMetrics['quarter']['sourced_pct'] }}%</span>
                    </div>
                    <div class="progress progress mb-4 border shadow-inset-3">
                        <div id="metric-sourced-bar" class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar"
                             style="width: {{ $businessMetrics['quarter']['sourced_pct'] }}%; transition: width 0.4s ease;"></div>
                    </div>

                    <div class="d-flex mt-3">
                        <span class="h6 m-0 fw-300">Influenced Revenue</span>
                        <span class="h6 d-inline-block ms-auto" id="metric-influenced-value">{{ $businessMetrics['quarter']['influenced_pct'] }}%</span>
                    </div>
                    <div class="progress progress mb-4 border shadow-inset-3">
                        <div id="metric-influenced-bar" class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar"
                             style="width: {{ $businessMetrics['quarter']['influenced_pct'] }}%; transition: width 0.4s ease;"></div>
                    </div>

                    <div class="d-flex mt-3">
                        <span class="h6 m-0 fw-300">Delivery Blueprint Coverage</span>
                        <span class="h6 d-inline-block ms-auto">{{ $avgBlueprintCompletion }}%</span>
                    </div>
                    <div class="progress progress mb-4 border shadow-inset-3">
                        <div class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar"
                             style="width: {{ $avgBlueprintCompletion }}%;"></div>
                    </div>

                    @php $engColor = $contactEngagement > 80 ? 'success-500' : ($contactEngagement > 40 ? 'primary-700' : 'warning-500'); @endphp
                    <div class="d-flex mt-3">
                        <span class="h6 m-0 fw-300">Contact Engagement</span>
                        <span class="h6 d-inline-block ms-auto">{{ $contactEngagement }}%</span>
                    </div>
                    <div class="progress progress mb-4 border shadow-inset-3">
                        <div class="progress-bar bg-{{ $engColor }} progress-bar-striped" role="progressbar"
                             style="width: {{ $contactEngagement }}%;"></div>
                    </div>

                    <div class="d-flex mt-3">
                        <span class="h6 m-0 fw-300">Pipeline Growth (YoY)</span>
                        <span class="h6 d-inline-block ms-auto {{ $pipelineGrowthYoY >= 0 ? 'text-success' : 'text-danger' }}">{{ $pipelineGrowthYoY >= 0 ? '+' : '' }}{{ $pipelineGrowthYoY }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Health Scorecard + History --}}
    @if($latestScorecard)
    <div class="col-lg-6">
        <div class="panel panel-icon panel-locked" id="panel-overview-scorecard">
            <div class="panel-hdr">
                <h2> Health <span class="fw-light"><i>Score</i></span></h2>
                <div class="panel-toolbar">
                    <span class="badge bg-{{ $latestScorecard->overall_score >= 55 ? 'success' : ($latestScorecard->overall_score >= 30 ? 'warning' : 'danger') }} fs-md">
                        {{ number_format($latestScorecard->overall_score, 0) }}
                    </span>
                </div>
            </div>
            <div class="panel-container">
                <div class="panel-content p-0">
                    <div id="overview-scorecard-radar" style="min-height: 400px;"></div>
                </div>
            </div>
        </div>

        @if($scorecardHistory->isNotEmpty())
        <div class="panel panel-icon" id="panel-overview-history">
            <div class="panel-hdr">
                <h2> Scorecard <span class="fw-light"><i>History</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center">Overall Score</th>
                                    <th>Tier</th>
                                    <th class="text-center">Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scorecardHistory as $index => $scorecard)
                                @php
                                    $prevScore = $scorecardHistory->get($index + 1)?->overall_score;
                                    $trend = $prevScore !== null ? $scorecard->overall_score - $prevScore : null;
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $scorecard->scored_at->format('M j, Y') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $scorecard->overall_score >= 55 ? 'success' : ($scorecard->overall_score >= 30 ? 'warning' : 'danger') }} fs-sm">
                                            {{ number_format($scorecard->overall_score, 0) }}
                                        </span>
                                    </td>
                                    <td>{{ $scorecard->tier ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($trend !== null)
                                            <span class="{{ $trend >= 0 ? 'text-success' : 'text-danger' }}">
                                                <i class="fal fa-arrow-{{ $trend >= 0 ? 'up' : 'down' }} me-1"></i>{{ abs(round($trend, 1)) }}
                                            </span>
                                        @else
                                            <span class="text-muted">&mdash;</span>
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
    </div>
    @endif
</div>

{{-- Chart Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    var cm = window.colorMap || {};
    var bv = (cm.bootstrapVars && cm.bootstrapVars.bodyColor) || null;
    var bodyColorHex = bv ? bv.hex : '#6c757d';
    var bodyColor01 = bv ? bv.rgba(0.1) : 'rgba(108,117,125,0.1)';

    // Business Metrics period toggle
    var metricsData = @json($businessMetrics);
    var toggleBtns = document.querySelectorAll('#metrics-period-toggle button');
    toggleBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            toggleBtns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            var period = this.dataset.period;
            var data = metricsData[period];
            document.getElementById('metric-sourced-value').textContent = data.sourced_pct + '%';
            document.getElementById('metric-sourced-bar').style.width = data.sourced_pct + '%';
            document.getElementById('metric-influenced-value').textContent = data.influenced_pct + '%';
            document.getElementById('metric-influenced-bar').style.width = data.influenced_pct + '%';
        });
    });

    @if($latestScorecard)
    var scorecardData = @json($latestScorecard->getDimensionScores());
    var scLabels = Object.keys(scorecardData).map(function(k) {
        return k.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); }).replace(' Score', '');
    });
    var scValues = Object.values(scorecardData).map(function(v) { return parseFloat(v) || 0; });
    if (scLabels.length > 0 && scValues.some(function(v) { return v > 0; })) {
        new ApexCharts(document.querySelector('#overview-scorecard-radar'), {
            chart: { type: 'radar', height: '100%', toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Score', data: scValues }],
            labels: scLabels,
            colors: ['#886ab5'],
            fill: { opacity: 0.2 },
            stroke: { width: 2 },
            markers: { size: 4, colors: ['#886ab5'], strokeColors: '#fff', strokeWidth: 2 },
            xaxis: { labels: { style: { colors: bodyColorHex, fontSize: '11px' } } },
            yaxis: { max: 100, tickAmount: 5, labels: { show: true, style: { fontSize: '10px' } } },
            legend: { show: false },
            tooltip: { theme: 'dark', y: { formatter: function(val) { return val.toFixed(1); } } },
            plotOptions: { radar: { polygons: { strokeColors: bodyColor01, connectorColors: bodyColor01 } } }
        }).render();
    } else if (scLabels.length > 0) {
        document.querySelector('#overview-scorecard-radar').innerHTML = '<div class="text-center text-muted py-5"><i class="fal fa-heartbeat fa-2x mb-2 opacity-50"></i><p>Health scores not yet calculated</p></div>';
    }
    @endif
});
</script>
