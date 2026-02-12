{{-- KPI Cards --}}
<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-success" style="width:24px;height:24px"><use href="/icons/sprite.svg#dollar-sign"></use></svg>
                <h2 class="fw-300 m-0 mt-1">${{ number_format($totalFunding / 1000, 0) }}K</h2>
                <small class="text-muted">Total Funding</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Funding by Type --}}
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Funding by Type</div></div>
            <div class="card-body" style="min-height:300px">
                <div id="chart-funding-type"></div>
            </div>
        </div>
    </div>
    {{-- Funding by Partner --}}
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Funding by Partner</div></div>
            <div class="card-body" style="min-height:300px">
                <div id="chart-funding-partner"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const byType = @json($byType);
    if (document.querySelector('#chart-funding-type') && byType.length > 0) {
        new ApexCharts(document.querySelector('#chart-funding-type'), {
            chart: { type: 'donut', height: 280 },
            series: byType.map(t => parseFloat(t.total)),
            labels: byType.map(t => t.type || 'Other'),
        }).render();
    }

    const byPartner = @json($byPartner);
    if (document.querySelector('#chart-funding-partner') && byPartner.length > 0) {
        new ApexCharts(document.querySelector('#chart-funding-partner'), {
            chart: { type: 'bar', height: 280 },
            series: [{ name: 'Funding', data: byPartner.map(p => parseFloat(p.total)) }],
            xaxis: { categories: byPartner.map(p => p.name) },
            yaxis: { labels: { formatter: v => '$' + (v/1000).toFixed(0) + 'K' } },
            colors: ['#0D6EFD'],
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        }).render();
    }
});
</script>
@endpush
