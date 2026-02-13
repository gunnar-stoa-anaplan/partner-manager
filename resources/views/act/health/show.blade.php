@extends('layouts.vertical', ['pageTitle' => 'Health — '.$partner->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $partner->name.' — Health', 'pageSubTitle1' => 'Partner Health', 'pageSubTitle1Url' => route('act.health.index')])
@endsection

@section('content')
@php
    $color = \App\Models\PartnerHealthSnapshot::RISK_COLORS[$current['risk_level']] ?? 'secondary';
    $dimensionLabels = [
        'scorecard' => 'Scorecard',
        'pipeline_trend' => 'Pipeline Trend',
        'goal_attainment' => 'Goal Attainment',
        'enablement' => 'Enablement',
        'contact_freshness' => 'Contact Freshness',
        'delivery_csat' => 'Delivery CSAT',
    ];
@endphp

{{-- Overall Score --}}
<div class="row mb-g mt-3">
    <div class="col-lg-4">
        <div class="card shadow-0 mb-g border-top border-3 border-{{ $color }}">
            <div class="card-body text-center py-4">
                <h1 class="display-4 fw-300 text-{{ $color }} m-0">{{ $current['score'] }}</h1>
                <span class="badge bg-{{ $color }} fs-sm mb-2">{{ \App\Models\PartnerHealthSnapshot::RISK_LEVELS[$current['risk_level']] ?? $current['risk_level'] }}</span>
                <p class="text-muted mb-0">Composite Health Score</p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Dimension Breakdown</div></div>
            <div class="card-body">
                @foreach($current['dimensions'] as $dim => $score)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-500">{{ $dimensionLabels[$dim] ?? $dim }}</small>
                        <small class="fw-500">{{ round($score, 1) }}/100</small>
                    </div>
                    <div class="progress" style="height: 10px;">
                        @php
                            $barColor = $score >= 70 ? 'success' : ($score >= 50 ? 'info' : ($score >= 30 ? 'warning' : 'danger'));
                        @endphp
                        <div class="progress-bar bg-{{ $barColor }}" style="width: {{ min($score, 100) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Risk Signals --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Risk Signals</div></div>
    <div class="card-body">
        @if(!empty($current['signals']))
            <div class="list-group list-group-flush">
            @foreach($current['signals'] as $signal)
                <div class="list-group-item d-flex align-items-center">
                    <svg class="sa-icon text-{{ $color }} me-2" style="width:18px;height:18px"><use href="/icons/sprite.svg#alert-circle"></use></svg>
                    {{ $signal }}
                </div>
            @endforeach
            </div>
        @else
            <p class="text-muted mb-0">No risk signals detected. This partner appears healthy across all dimensions.</p>
        @endif
    </div>
</div>

{{-- 30-Day Trend --}}
@if($trend->isNotEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">30-Day Health Trend</div></div>
    <div class="card-body">
        <div id="healthTrendChart"></div>
    </div>
</div>
@endif

{{-- Radar Chart --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Health Radar</div></div>
    <div class="card-body">
        <div id="healthRadarChart"></div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Radar Chart
    var radarOpts = {
        series: [{
            name: '{{ $partner->name }}',
            data: [
                @foreach($current['dimensions'] as $score)
                {{ round($score, 1) }},
                @endforeach
            ]
        }],
        chart: { type: 'radar', height: 350 },
        xaxis: {
            categories: {!! json_encode(array_values($dimensionLabels)) !!}
        },
        yaxis: { max: 100 },
        colors: ['#{{ $color === "success" ? "1dc9b7" : ($color === "warning" ? "ffc241" : ($color === "danger" ? "fd3995" : "886ab5")) }}'],
        fill: { opacity: 0.2 },
        stroke: { width: 2 },
    };
    new ApexCharts(document.querySelector('#healthRadarChart'), radarOpts).render();

    @if($trend->isNotEmpty())
    // Trend Line
    var trendOpts = {
        series: [{
            name: 'Health Score',
            data: {!! json_encode($trend->pluck('score')->map(fn($s) => (float) $s)->values()) !!}
        }],
        chart: { type: 'area', height: 250, sparkline: { enabled: false } },
        xaxis: {
            categories: {!! json_encode($trend->pluck('snapshot_date')->map(fn($d) => $d->format('M d'))->values()) !!},
            labels: { rotate: -45 }
        },
        yaxis: { min: 0, max: 100 },
        colors: ['#886ab5'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: { enabled: false },
    };
    new ApexCharts(document.querySelector('#healthTrendChart'), trendOpts).render();
    @endif
});
</script>
@endsection
