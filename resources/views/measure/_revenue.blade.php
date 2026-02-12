{{-- KPI Cards --}}
<div class="row">
    @php
        $kpis = [
            ['label' => 'Total Bookings (FY)', 'value' => '$' . number_format($totalBookings / 1000, 0) . 'K', 'icon' => 'dollar-sign', 'color' => 'success'],
            ['label' => 'Sourced NNACV', 'value' => '$' . number_format($sourcedNnacv / 1000, 0) . 'K', 'icon' => 'trending-up', 'color' => 'success'],
            ['label' => 'Influenced NNACV', 'value' => '$' . number_format($influencedNnacv / 1000, 0) . 'K', 'icon' => 'trending-up', 'color' => 'info'],
            ['label' => 'Avg ACV', 'value' => '$' . number_format($avgAcv / 1000, 0) . 'K', 'icon' => 'bar-chart', 'color' => 'primary'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="col-xl-3 col-md-6">
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

<div class="row">
    {{-- Revenue by Quarter --}}
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Revenue by Quarter</div></div>
            <div class="card-body" style="min-height:300px">
                <div id="chart-rev-quarter"></div>
            </div>
        </div>
    </div>
    {{-- Revenue by Product Area --}}
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Revenue by Product Area</div></div>
            <div class="card-body" style="min-height:300px">
                <div id="chart-rev-product"></div>
            </div>
        </div>
    </div>
</div>

{{-- Revenue by Partner --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Revenue by Partner</div></div>
    <div class="card-body" style="min-height:300px">
        <div id="chart-rev-partner"></div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const byQuarter = @json($byQuarter);
    const quarters = [...new Set(byQuarter.map(r => r.fiscal_quarter))].sort();
    if (document.querySelector('#chart-rev-quarter') && byQuarter.length > 0) {
        new ApexCharts(document.querySelector('#chart-rev-quarter'), {
            chart: { type: 'bar', height: 280 },
            series: [
                { name: 'Sourced', data: quarters.map(q => byQuarter.filter(r => r.fiscal_quarter === q && r.revenue_type === 'sourced').reduce((s,r) => s + parseFloat(r.total), 0)) },
                { name: 'Influenced', data: quarters.map(q => byQuarter.filter(r => r.fiscal_quarter === q && r.revenue_type === 'influenced').reduce((s,r) => s + parseFloat(r.total), 0)) },
            ],
            xaxis: { categories: quarters },
            yaxis: { labels: { formatter: v => '$' + (v/1000).toFixed(0) + 'K' } },
            colors: ['#198754', '#0D6EFD'],
            plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
        }).render();
    }

    const byProduct = @json($byProductArea);
    if (document.querySelector('#chart-rev-product') && byProduct.length > 0) {
        new ApexCharts(document.querySelector('#chart-rev-product'), {
            chart: { type: 'bar', height: 280 },
            series: [{ name: 'Revenue', data: byProduct.map(p => parseFloat(p.total)) }],
            xaxis: { categories: byProduct.map(p => p.product_area) },
            yaxis: { labels: { formatter: v => '$' + (v/1000).toFixed(0) + 'K' } },
            colors: ['#0D6EFD'],
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        }).render();
    }

    const byPartner = @json($byPartner);
    if (document.querySelector('#chart-rev-partner') && byPartner.length > 0) {
        new ApexCharts(document.querySelector('#chart-rev-partner'), {
            chart: { type: 'treemap', height: 300 },
            series: [{ data: byPartner.map(p => ({ x: p.name, y: parseFloat(p.total) })) }],
        }).render();
    }
});
</script>
@endpush
