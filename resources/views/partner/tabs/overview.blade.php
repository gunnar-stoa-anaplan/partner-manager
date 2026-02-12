{{-- Overview Tab: Business Metrics + Health Score + Partner Path --}}

<div class="row">
    {{-- Business Metrics --}}
    <div class="col-lg-{{ $latestScorecard ? '6' : '12' }}">
        <div class="panel panel-icon panel-locked" id="panel-overview-metrics">
            <div class="panel-hdr">
                <h2> Business <span class="fw-light"><i>Metrics</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content">
                    @php
                        $metrics = [
                            ['label' => 'Sourced Revenue', 'value' => $sourcedRevenuePct, 'color' => 'primary-700'],
                            ['label' => 'Influenced Revenue', 'value' => $influencedRevenuePct, 'color' => 'primary-700'],
                            ['label' => 'Delivery Blueprint Coverage', 'value' => $avgBlueprintCompletion, 'color' => 'primary-700'],
                            ['label' => 'Contact Engagement', 'value' => $contactEngagement, 'color' => $contactEngagement > 80 ? 'success-500' : ($contactEngagement > 40 ? 'primary-700' : 'warning-500')],
                        ];
                    @endphp
                    @foreach($metrics as $metric)
                        <div class="d-flex mt-3">
                            <span class="h6 m-0 fw-300">{{ $metric['label'] }}</span>
                            <span class="h6 d-inline-block ms-auto">{{ $metric['value'] }}%</span>
                        </div>
                        <div class="progress progress mb-4 border shadow-inset-3">
                            <div aria-label="{{ $metric['value'] }}%" aria-valuemax="100" aria-valuemin="0"
                                 aria-valuenow="{{ $metric['value'] }}"
                                 class="progress-bar bg-{{ $metric['color'] }} progress-bar-striped"
                                 role="progressbar" style="width: {{ $metric['value'] }}%;"></div>
                        </div>
                    @endforeach
                    <div class="d-flex mt-3">
                        <span class="h6 m-0 fw-300">Pipeline Growth (YoY)</span>
                        <span class="h6 d-inline-block ms-auto {{ $pipelineGrowthYoY >= 0 ? 'text-success' : 'text-danger' }}">{{ $pipelineGrowthYoY >= 0 ? '+' : '' }}{{ $pipelineGrowthYoY }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Health Scorecard --}}
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
                <div class="panel-content">
                    <div id="overview-scorecard-radar" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

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

{{-- Chart Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    var cm = window.colorMap || {};
    var bv = (cm.bootstrapVars && cm.bootstrapVars.bodyColor) || null;
    var bodyColorHex = bv ? bv.hex : '#6c757d';
    var bodyColor01 = bv ? bv.rgba(0.1) : 'rgba(108,117,125,0.1)';

    @if($latestScorecard)
    var scorecardData = @json($latestScorecard->getDimensionScores());
    var scLabels = Object.keys(scorecardData).map(function(k) {
        return k.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); }).replace(' Score', '');
    });
    var scValues = Object.values(scorecardData).map(function(v) { return parseFloat(v) || 0; });
    if (scLabels.length > 0 && scValues.some(function(v) { return v > 0; })) {
        new ApexCharts(document.querySelector('#overview-scorecard-radar'), {
            chart: { type: 'radar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
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
