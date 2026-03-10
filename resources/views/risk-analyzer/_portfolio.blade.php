{{-- Portfolio Dashboard --}}
<div class="row">
    @php
        $kpis = [
            ['label' => 'Total NNACV', 'value' => '$' . number_format($summary['total_nnacv'] / 1000, 0) . 'K', 'icon' => 'dollar-sign', 'color' => 'success'],
            ['label' => 'Opportunities', 'value' => number_format($summary['opp_count']), 'icon' => 'briefcase', 'color' => 'primary'],
            ['label' => 'Delivery Resources', 'value' => number_format($summary['resource_count']), 'icon' => 'users', 'color' => 'info'],
            ['label' => 'Partners', 'value' => number_format($summary['partner_count']), 'icon' => 'building', 'color' => 'secondary'],
            ['label' => 'Partner Sourced', 'value' => $summary['sourced_count'] . ' ($' . number_format($summary['sourced_nnacv'] / 1000, 0) . 'K)', 'icon' => 'trending-up', 'color' => 'success'],
            ['label' => 'Delivery Assurance', 'value' => $summary['delivery_assurance_pct'] . '%', 'icon' => 'shield', 'color' => $summary['delivery_assurance_pct'] > 50 ? 'success' : 'warning'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-{{ $kpi['color'] }}" style="width:24px;height:24px"><use href="/icons/sprite.svg#{{ $kpi['icon'] }}"></use></svg>
                <h3 class="fw-300 m-0 mt-1">{{ $kpi['value'] }}</h3>
                <small class="text-muted">{{ $kpi['label'] }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- CS Health & App vs Platform --}}
<div class="row">
    <div class="col-lg-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">CS Health Score</div></div>
            <div class="card-body">
                <div id="chart-cs-health" style="min-height:250px"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">App vs Platform Split</div></div>
            <div class="card-body">
                <div id="chart-app-platform" style="min-height:250px"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Portfolio Metrics</div></div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">App Opps</td><td class="fw-500">{{ $summary['app_count'] }}</td><td class="text-end">${{ number_format($summary['app_nnacv'] / 1000, 0) }}K</td></tr>
                    <tr><td class="text-muted">Platform Opps</td><td class="fw-500">{{ $summary['platform_count'] }}</td><td class="text-end">${{ number_format($summary['platform_nnacv'] / 1000, 0) }}K</td></tr>
                    <tr><td class="text-muted">CS Red</td><td class="fw-500 text-danger">{{ $summary['cs_health']['red'] }}</td><td></td></tr>
                    <tr><td class="text-muted">CS Yellow</td><td class="fw-500 text-warning">{{ $summary['cs_health']['yellow'] }}</td><td></td></tr>
                    <tr><td class="text-muted">CS Green</td><td class="fw-500 text-success">{{ $summary['cs_health']['green'] }}</td><td></td></tr>
                    <tr><td class="text-muted">DA Coverage</td><td class="fw-500">{{ $summary['delivery_assurance_count'] }}/{{ $summary['opp_count'] }}</td><td class="text-end">{{ $summary['delivery_assurance_pct'] }}%</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csHealth = @json($summary['cs_health']);
    if (document.querySelector('#chart-cs-health')) {
        new ApexCharts(document.querySelector('#chart-cs-health'), {
            chart: { type: 'donut', height: 250 },
            series: [csHealth.red, csHealth.yellow, csHealth.green],
            labels: ['Red', 'Yellow', 'Green'],
            colors: ['#DC3545', '#FFC107', '#198754'],
            legend: { position: 'bottom' },
        }).render();
    }

    if (document.querySelector('#chart-app-platform')) {
        new ApexCharts(document.querySelector('#chart-app-platform'), {
            chart: { type: 'donut', height: 250 },
            series: [{{ $summary['app_nnacv'] }}, {{ $summary['platform_nnacv'] }}],
            labels: ['App', 'Platform'],
            colors: ['#0D6EFD', '#6F42C1'],
            legend: { position: 'bottom' },
            tooltip: { y: { formatter: v => '$' + (v/1000).toFixed(0) + 'K' } },
        }).render();
    }
});
</script>
@endpush
