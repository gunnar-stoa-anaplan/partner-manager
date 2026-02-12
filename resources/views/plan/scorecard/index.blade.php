@extends('layouts.vertical', ['pageTitle' => 'Partner Scorecard'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Partner Scorecard', 'pageSubTitle1' => 'Plan', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The Partner Scorecard gives you a health-at-a-glance view of your entire portfolio. Each partner is scored across six dimensions — certification readiness, pipeline maturity, delivery quality, engagement velocity, growth trajectory, and self-sufficiency — with an overall composite score. Use the heatmap table to quickly spot partners who need attention, and check the score distribution chart to understand how your portfolio is balanced across risk bands.</p>

{{-- Portfolio Average KPIs --}}
<div class="row">
    @php
        $dimensions = [
            ['key' => 'overall', 'label' => 'Overall', 'color' => 'primary', 'icon' => 'activity'],
            ['key' => 'certification', 'label' => 'Certification', 'color' => 'info', 'icon' => 'award'],
            ['key' => 'pipeline', 'label' => 'Pipeline', 'color' => 'success', 'icon' => 'trending-up'],
            ['key' => 'delivery', 'label' => 'Delivery', 'color' => 'warning', 'icon' => 'clipboard'],
            ['key' => 'engagement', 'label' => 'Engagement', 'color' => 'primary', 'icon' => 'users'],
            ['key' => 'growth', 'label' => 'Growth', 'color' => 'success', 'icon' => 'bar-chart-2'],
        ];
    @endphp
    @foreach($dimensions as $dim)
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-{{ $dim['color'] }}" style="width:24px;height:24px"><use href="/icons/sprite.svg#{{ $dim['icon'] }}"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ number_format($avgScores[$dim['key']], 0) }}</h2>
                <small class="text-muted">{{ $dim['label'] }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Heatmap Table --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Portfolio Scorecard</div></div>
    <div class="card-body">
        <table class="table table-hover m-0" id="scorecard-table">
            <thead>
                <tr>
                    <th>Partner</th>
                    <th class="text-center">Overall</th>
                    <th class="text-center">Certification</th>
                    <th class="text-center">Pipeline</th>
                    <th class="text-center">Delivery</th>
                    <th class="text-center">Engagement</th>
                    <th class="text-center">Growth</th>
                    <th class="text-center">Self-Suff.</th>
                    <th class="text-center">Gate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($partners as $partner)
                @php $sc = $scorecards->get($partner->id); @endphp
                <tr>
                    <td>
                        <a href="{{ route('plan.scorecard.show', $partner) }}">{{ $partner->name }}</a>
                        <small class="d-block text-muted">{{ $partner->geography }}</small>
                    </td>
                    @if($sc)
                        @foreach(['overall_score','certification_score','pipeline_score','delivery_score','engagement_score','growth_score','self_sufficiency_score'] as $col)
                        <td class="text-center">
                            @php $v = $sc->$col; $bg = $v >= 80 ? 'primary' : ($v >= 55 ? 'success' : ($v >= 30 ? 'warning' : 'danger')); @endphp
                            <span class="badge bg-{{ $bg }}">{{ number_format($v, 0) }}</span>
                        </td>
                        @endforeach
                        <td class="text-center">
                            @if($sc->gate_eligible)<span class="badge bg-success">Eligible</span>@else<span class="badge bg-secondary">—</span>@endif
                        </td>
                    @else
                        <td colspan="8" class="text-center text-muted">Not scored yet</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Score Distribution Chart --}}
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Score Distribution</div></div>
            <div class="card-body"><div id="score-distribution-chart" style="height: 300px;"></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Dimension Weights</div></div>
            <div class="card-body"><div id="weights-chart" style="height: 300px;"></div></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof SmartTable !== 'undefined') {
        new SmartTable('#scorecard-table', { sortable: true, perPage: 15 });
    }

    // Score distribution
    var scores = @json($scorecards->pluck('overall_score')->values());
    var bands = [0, 0, 0, 0];
    scores.forEach(function(s) {
        s = parseFloat(s);
        if (s >= 80) bands[3]++;
        else if (s >= 55) bands[2]++;
        else if (s >= 30) bands[1]++;
        else bands[0]++;
    });
    new ApexCharts(document.querySelector('#score-distribution-chart'), {
        chart: { type: 'bar', height: 300, toolbar: { show: false } },
        series: [{ name: 'Partners', data: bands }],
        xaxis: { categories: ['0-29 (At Risk)', '30-54 (Watch)', '55-79 (Healthy)', '80-100 (Strong)'] },
        colors: ['#dc3545', '#ffc107', '#198754', '#0d6efd'],
        plotOptions: { bar: { distributed: true, borderRadius: 4 } },
        legend: { show: false }
    }).render();

    // Weights donut
    var w = @json($weights);
    var wValues = Object.values(w).map(function(v) { return Math.round(parseFloat(v) * 100); });
    var wLabels = Object.keys(w).map(function(k) { return k.charAt(0).toUpperCase() + k.slice(1).replace('_', ' '); });
    if (wValues.length > 0) {
        new ApexCharts(document.querySelector('#weights-chart'), {
            chart: { type: 'donut', height: 300 },
            series: wValues,
            labels: wLabels,
            colors: ['#17a2b8', '#198754', '#ffc107', '#0d6efd', '#28a745', '#6c757d']
        }).render();
    }
});
</script>
@endsection
