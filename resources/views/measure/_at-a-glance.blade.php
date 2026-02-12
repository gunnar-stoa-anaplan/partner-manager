{{-- KPI Cards --}}
<div class="row">
    @php
        $kpis = [
            ['label' => 'Portfolio Health', 'value' => number_format($avgScore, 0), 'icon' => 'activity', 'color' => 'primary'],
            ['label' => 'Total Pipeline', 'value' => '$' . number_format($totalPipeline / 1000, 0) . 'K', 'icon' => 'trending-up', 'color' => 'success'],
            ['label' => 'Win Rate', 'value' => $winRate . '%', 'icon' => 'target', 'color' => 'info'],
            ['label' => 'Sourced Revenue', 'value' => '$' . number_format($sourcedRevenue / 1000, 0) . 'K', 'icon' => 'dollar-sign', 'color' => 'success'],
            ['label' => 'Influenced Revenue', 'value' => '$' . number_format($influencedRevenue / 1000, 0) . 'K', 'icon' => 'dollar-sign', 'color' => 'info'],
            ['label' => 'Active Projects', 'value' => $activeProjects, 'icon' => 'clipboard', 'color' => 'warning'],
            ['label' => 'Active Certs', 'value' => $activeCerts, 'icon' => 'award', 'color' => 'primary'],
            ['label' => 'Overdue Tasks', 'value' => $overdueTasks, 'icon' => 'alert-circle', 'color' => $overdueTasks > 0 ? 'danger' : 'success'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="col-xl-3 col-md-4 col-6">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-{{ $kpi['color'] }}" style="width:24px;height:24px"><use href="/icons/sprite.svg#{{ $kpi['icon'] }}"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ $kpi['value'] }}</h2>
                <small class="text-muted">{{ $kpi['label'] }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Charts Row --}}
<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Revenue vs Target by Quarter</div></div>
            <div class="card-body" style="min-height:300px">
                <div id="chart-revenue-by-quarter"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Path Stage Distribution</div></div>
            <div class="card-body" style="min-height:300px">
                <div id="chart-stage-distribution"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue by Quarter
    const revData = @json($revenueByQuarter);
    const quarters = [...new Set(revData.map(r => r.fiscal_quarter))].sort();
    const sourced = quarters.map(q => revData.filter(r => r.fiscal_quarter === q && r.revenue_type === 'sourced').reduce((s, r) => s + parseFloat(r.total), 0));
    const influenced = quarters.map(q => revData.filter(r => r.fiscal_quarter === q && r.revenue_type === 'influenced').reduce((s, r) => s + parseFloat(r.total), 0));

    if (document.querySelector('#chart-revenue-by-quarter')) {
        new ApexCharts(document.querySelector('#chart-revenue-by-quarter'), {
            chart: { type: 'bar', height: 280 },
            series: [{ name: 'Sourced', data: sourced }, { name: 'Influenced', data: influenced }],
            xaxis: { categories: quarters },
            yaxis: { labels: { formatter: v => '$' + (v/1000).toFixed(0) + 'K' } },
            colors: ['#198754', '#0D6EFD'],
            plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
        }).render();
    }

    // Stage Distribution
    const stageData = @json($stageDistribution);
    if (document.querySelector('#chart-stage-distribution') && stageData.length > 0) {
        new ApexCharts(document.querySelector('#chart-stage-distribution'), {
            chart: { type: 'bar', height: 280 },
            series: [{ name: 'Partners', data: stageData.map(s => s.count) }],
            xaxis: { categories: stageData.map(s => s.name) },
            colors: stageData.map(s => s.color),
            plotOptions: { bar: { distributed: true, borderRadius: 4 } },
            legend: { show: false },
        }).render();
    }
});
</script>
@endpush
