@extends('layouts.vertical', ['pageTitle' => 'Benchmark — '.$partner->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $partner->name.' — Benchmark', 'pageSubTitle1' => 'Benchmarks', 'pageSubTitle1Url' => route('measure.benchmarks.index')])
@endsection

@section('content')
@php
    $overallColor = \App\Services\BenchmarkService::QUARTILE_COLORS[$benchmark['overall_quartile']] ?? 'secondary';
@endphp

{{-- Overall Ranking --}}
<div class="row mb-g mt-3">
    <div class="col-lg-4">
        <div class="card shadow-0 mb-g border-top border-3 border-{{ $overallColor }}">
            <div class="card-body text-center py-4">
                <h1 class="display-4 fw-300 text-{{ $overallColor }} m-0">{{ $benchmark['overall_percentile'] }}%</h1>
                <span class="badge bg-{{ $overallColor }} fs-sm mb-2">{{ \App\Services\BenchmarkService::QUARTILE_LABELS[$benchmark['overall_quartile']] }}</span>
                <p class="text-muted mb-0">Overall Portfolio Ranking</p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Dimension Rankings</div></div>
            <div class="card-body">
                @foreach($dimensions as $dimKey => $dimLabel)
                    @php
                        $d = $benchmark['dimensions'][$dimKey] ?? null;
                        $avg = $benchmark['portfolio_averages'][$dimKey] ?? 0;
                    @endphp
                    @if($d)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-500">{{ $dimLabel }}</span>
                            <div>
                                <span class="badge bg-{{ \App\Services\BenchmarkService::QUARTILE_COLORS[$d['quartile']] }} me-1">{{ $d['quartile_label'] }}</span>
                                <small class="text-muted">{{ $d['percentile'] }}th percentile</small>
                            </div>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-{{ \App\Services\BenchmarkService::QUARTILE_COLORS[$d['quartile']] }}" style="width: {{ $d['percentile'] }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Your value: {{ number_format($d['value'], is_float($d['value']) && $d['value'] < 100 ? 1 : 0) }}</small>
                            <small class="text-muted">Portfolio avg: {{ number_format($avg, is_float($avg) && $avg < 100 ? 1 : 0) }}</small>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Radar Chart --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Benchmark Radar — You vs Portfolio Average</div></div>
    <div class="card-body">
        <div id="benchmarkRadar"></div>
    </div>
</div>

{{-- Strengths & Weaknesses --}}
<div class="row mb-g">
    <div class="col-md-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header bg-success-50"><div class="card-title text-success">Strengths</div></div>
            <div class="card-body">
                @php
                    $strengths = collect($benchmark['dimensions'])->filter(fn($d) => $d['quartile'] >= 3)->sortByDesc('percentile');
                @endphp
                @forelse($strengths as $dimKey => $d)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>{{ $dimensions[$dimKey] ?? $dimKey }}</span>
                    <span class="badge bg-{{ \App\Services\BenchmarkService::QUARTILE_COLORS[$d['quartile']] }}">{{ $d['quartile_label'] }}</span>
                </div>
                @empty
                <p class="text-muted mb-0">No dimensions in top 50%.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header bg-warning-50"><div class="card-title text-warning">Areas for Improvement</div></div>
            <div class="card-body">
                @php
                    $weaknesses = collect($benchmark['dimensions'])->filter(fn($d) => $d['quartile'] <= 2)->sortBy('percentile');
                @endphp
                @forelse($weaknesses as $dimKey => $d)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>{{ $dimensions[$dimKey] ?? $dimKey }}</span>
                    <span class="badge bg-{{ \App\Services\BenchmarkService::QUARTILE_COLORS[$d['quartile']] }}">{{ $d['quartile_label'] }}</span>
                </div>
                @empty
                <p class="text-muted mb-0">No dimensions below average.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var partnerData = [
        @foreach($dimensions as $dimKey => $dimLabel)
        {{ $benchmark['dimensions'][$dimKey]['percentile'] ?? 0 }},
        @endforeach
    ];
    var avgData = [
        @foreach($dimensions as $dimKey => $dimLabel)
        50,
        @endforeach
    ];

    var opts = {
        series: [
            { name: '{{ $partner->name }}', data: partnerData },
            { name: 'Portfolio Average', data: avgData },
        ],
        chart: { type: 'radar', height: 400 },
        xaxis: { categories: {!! json_encode(array_values($dimensions)) !!} },
        yaxis: { max: 100 },
        colors: ['#886ab5', '#cccccc'],
        fill: { opacity: [0.3, 0.1] },
        stroke: { width: [2, 1] },
        legend: { position: 'bottom' },
    };
    new ApexCharts(document.querySelector('#benchmarkRadar'), opts).render();
});
</script>
@endsection
