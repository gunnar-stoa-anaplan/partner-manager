@extends('layouts.vertical', ['pageTitle' => 'Peer Benchmarks'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Peer Benchmarks', 'pageSubTitle1' => 'Strategy'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Compare your partner program performance against anonymized peer cohort benchmarks. Data is aggregated from opted-in instances with similar portfolio characteristics — no individual partner or instance data is ever shared.</p>

@if(!$sharingEnabled)
<div class="alert alert-warning">
    <strong>Signal sharing is disabled.</strong> Enable it in <a href="{{ route('configuration.index') }}">Configuration</a> to participate in the benchmark network and receive peer comparisons.
</div>
@endif

@if($benchmark && isset($benchmark['metrics']))
{{-- Cohort Info --}}
<div class="row mb-g">
    <div class="col-12">
        <div class="card shadow-0 mb-g border-top border-3 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-500 mb-1">Cohort Benchmark</h5>
                        <span class="text-muted">{{ $benchmark['cohort_size'] ?? 'N/A' }} peer instances in your cohort</span>
                        @if(isset($benchmark['computed_date']))
                            <span class="text-muted ms-2">· Last computed: {{ \Carbon\Carbon::parse($benchmark['computed_date'])->format('M d, Y') }}</span>
                        @endif
                    </div>
                    <span class="badge bg-success fs-6">Active</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Benchmark Comparison Cards --}}
@php
    $metrics = $benchmark['metrics'] ?? [];
    $ours = $aggregate['portfolio_metrics'] ?? [];

    $comparisons = [
        [
            'label' => 'Avg Health Score',
            'key' => 'avg_health_score',
            'our_key' => 'avg_health_score',
            'unit' => '',
            'format' => 'number',
            'icon' => 'heart',
            'color' => 'success',
            'description' => 'Composite health score across your partner portfolio',
        ],
        [
            'label' => 'Avg Pipeline Coverage',
            'key' => 'avg_pipeline_coverage',
            'our_key' => 'avg_pipeline_coverage',
            'unit' => 'x',
            'format' => 'decimal',
            'icon' => 'trending-up',
            'color' => 'primary',
            'description' => 'Open pipeline relative to 12-month closed-won revenue',
        ],
        [
            'label' => 'Avg Win Rate',
            'key' => 'avg_win_rate',
            'our_key' => 'avg_win_rate',
            'unit' => '%',
            'format' => 'number',
            'icon' => 'award',
            'color' => 'info',
            'description' => 'Percentage of closed opportunities that were won',
        ],
        [
            'label' => 'Certification Density',
            'key' => 'certification_density',
            'our_key' => 'certification_density',
            'unit' => '',
            'format' => 'decimal',
            'icon' => 'shield',
            'color' => 'warning',
            'description' => 'Active certifications per managed partner',
        ],
        [
            'label' => 'Avg Active Signals',
            'key' => 'avg_active_signals',
            'our_key' => 'avg_active_signals',
            'unit' => '',
            'format' => 'decimal',
            'icon' => 'zap',
            'color' => 'danger',
            'description' => 'Average number of active signals per partner',
        ],
    ];
@endphp

<div class="row mb-g">
    @foreach($comparisons as $comp)
    @php
        $cohortData = $metrics[$comp['key']] ?? null;
        $ourValue = $ours[$comp['our_key']] ?? null;
        $median = $cohortData['median'] ?? null;
        $p25 = $cohortData['p25'] ?? null;
        $p75 = $cohortData['p75'] ?? null;
        $mean = $cohortData['mean'] ?? null;

        // Determine position relative to cohort
        $position = null;
        $positionClass = 'secondary';
        $positionLabel = 'N/A';
        if ($ourValue !== null && $median !== null) {
            if ($ourValue >= $p75) {
                $positionClass = 'success';
                $positionLabel = 'Top Quartile';
            } elseif ($ourValue >= $median) {
                $positionClass = 'info';
                $positionLabel = 'Above Median';
            } elseif ($ourValue >= $p25) {
                $positionClass = 'warning';
                $positionLabel = 'Below Median';
            } else {
                $positionClass = 'danger';
                $positionLabel = 'Bottom Quartile';
            }
        }

        $formatValue = fn($v) => $v !== null ? ($comp['format'] === 'decimal' ? number_format($v, 1) : number_format($v, 0)) : '—';
    @endphp
    <div class="col-xl col-lg-4 col-md-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header py-2 d-flex align-items-center">
                <svg class="sa-icon text-{{ $comp['color'] }} me-2" style="width:18px;height:18px"><use href="/icons/sprite.svg#{{ $comp['icon'] }}"></use></svg>
                <span class="fw-500 fs-sm">{{ $comp['label'] }}</span>
            </div>
            <div class="card-body text-center py-3">
                @if($ourValue !== null)
                    <h2 class="fw-300 m-0 text-{{ $comp['color'] }}">{{ $formatValue($ourValue) }}<small class="fs-6 text-muted">{{ $comp['unit'] }}</small></h2>
                    <small class="text-muted">Your Portfolio</small>
                @else
                    <h3 class="fw-300 m-0 text-secondary">—</h3>
                    <small class="text-muted">No data yet</small>
                @endif

                @if($ourValue !== null && $median !== null)
                    <div class="mt-2">
                        <span class="badge bg-{{ $positionClass }}">{{ $positionLabel }}</span>
                    </div>
                @endif
            </div>
            @if($cohortData)
            <div class="card-footer bg-light">
                <div class="row text-center" style="font-size: 0.75rem;">
                    <div class="col-3">
                        <div class="text-muted">P25</div>
                        <div class="fw-500">{{ $formatValue($p25) }}</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted">Median</div>
                        <div class="fw-500 text-primary">{{ $formatValue($median) }}</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted">P75</div>
                        <div class="fw-500">{{ $formatValue($p75) }}</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted">Mean</div>
                        <div class="fw-500">{{ $formatValue($mean) }}</div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>

@if($aggregate)
{{-- Health Distribution Comparison --}}
<div class="row mb-g">
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title mb-0">Your Health Distribution</div>
            </div>
            <div class="card-body">
                @php
                    $health = $aggregate['health_distribution'] ?? [];
                    $totalPartners = array_sum($health);
                @endphp
                @if($totalPartners > 0)
                <div class="row text-center">
                    @foreach([
                        ['key' => 'healthy', 'label' => 'Healthy', 'color' => 'success'],
                        ['key' => 'watch', 'label' => 'Watch', 'color' => 'info'],
                        ['key' => 'at_risk', 'label' => 'At Risk', 'color' => 'warning'],
                        ['key' => 'critical', 'label' => 'Critical', 'color' => 'danger'],
                    ] as $level)
                        @php $count = $health[$level['key']] ?? 0; $pct = round(($count / $totalPartners) * 100); @endphp
                        <div class="col-3">
                            <h3 class="fw-300 m-0 text-{{ $level['color'] }}">{{ $count }}</h3>
                            <div class="progress mb-1" style="height: 4px;">
                                <div class="progress-bar bg-{{ $level['color'] }}" style="width: {{ $pct }}%"></div>
                            </div>
                            <small class="text-muted">{{ $level['label'] }} ({{ $pct }}%)</small>
                        </div>
                    @endforeach
                </div>
                @else
                    <p class="text-muted text-center mb-0">No health distribution data available.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title mb-0">Your Signal Summary</div>
            </div>
            <div class="card-body">
                @php $signals = $aggregate['signal_counts'] ?? []; @endphp
                @if(count($signals) > 0)
                <div class="row text-center">
                    @foreach([
                        ['key' => 'risk', 'label' => 'Risk', 'color' => 'danger'],
                        ['key' => 'trend', 'label' => 'Trend', 'color' => 'warning'],
                        ['key' => 'anomaly', 'label' => 'Anomaly', 'color' => 'purple'],
                        ['key' => 'recommendation', 'label' => 'Recom.', 'color' => 'success'],
                        ['key' => 'benchmark', 'label' => 'Bench.', 'color' => 'info'],
                        ['key' => 'opportunity', 'label' => 'Opport.', 'color' => 'primary'],
                    ] as $ch)
                        <div class="col-2">
                            <h4 class="fw-300 m-0">{{ $signals[$ch['key']] ?? 0 }}</h4>
                            <small class="text-muted">{{ $ch['label'] }}</small>
                        </div>
                    @endforeach
                </div>
                @else
                    <p class="text-muted text-center mb-0">No signal data. Run <code>signal:process</code> first.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Dimension Averages --}}
@if(isset($aggregate['dimension_averages']) && count($aggregate['dimension_averages']) > 0)
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title mb-0">Dimension Averages (Your Portfolio)</div>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach([
                ['key' => 'scorecard', 'label' => 'Scorecard', 'weight' => '25%'],
                ['key' => 'pipeline_trend', 'label' => 'Pipeline Trend', 'weight' => '20%'],
                ['key' => 'goal_attainment', 'label' => 'Goal Attainment', 'weight' => '20%'],
                ['key' => 'enablement', 'label' => 'Enablement', 'weight' => '15%'],
                ['key' => 'contact_freshness', 'label' => 'Contact Freshness', 'weight' => '10%'],
                ['key' => 'delivery_csat', 'label' => 'Delivery CSAT', 'weight' => '10%'],
            ] as $dim)
                @php
                    $val = $aggregate['dimension_averages'][$dim['key']] ?? null;
                    $barColor = $val >= 70 ? 'success' : ($val >= 50 ? 'info' : ($val >= 30 ? 'warning' : 'danger'));
                @endphp
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-500">{{ $dim['label'] }} <span class="text-muted">({{ $dim['weight'] }})</span></small>
                        <small class="fw-500 text-{{ $barColor }}">{{ $val !== null ? number_format($val, 1) : '—' }}</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-{{ $barColor }}" style="width: {{ $val ?? 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endif

@elseif($sharingEnabled)
{{-- No benchmark data received yet --}}
<div class="card shadow-0 mb-g">
    <div class="card-body text-center py-5">
        <svg class="sa-icon text-muted mb-3" style="width:48px;height:48px"><use href="/icons/sprite.svg#bar-chart-2"></use></svg>
        <h5 class="fw-500">Waiting for Benchmark Data</h5>
        <p class="text-muted mb-0">Signal sharing is enabled. Benchmark data will appear here once enough peer instances (minimum 5) have opted in to form your cohort. The agent syncs benchmarks periodically.</p>
    </div>
</div>
@else
<div class="card shadow-0 mb-g">
    <div class="card-body text-center py-5">
        <svg class="sa-icon text-muted mb-3" style="width:48px;height:48px"><use href="/icons/sprite.svg#lock"></use></svg>
        <h5 class="fw-500">Signal Sharing Disabled</h5>
        <p class="text-muted mb-0">To view peer benchmarks, an administrator must enable Signal Network sharing in <a href="{{ route('configuration.index') }}">Configuration</a>. Only anonymized, portfolio-level aggregates are shared — no partner names or raw data.</p>
    </div>
</div>
@endif
@endsection
