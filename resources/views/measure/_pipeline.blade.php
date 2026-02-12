{{-- KPI Cards --}}
<div class="row">
    @php
        $kpis = [
            ['label' => 'Pipeline Value', 'value' => '$' . number_format($pipelineValue / 1000, 0) . 'K', 'icon' => 'trending-up', 'color' => 'success'],
            ['label' => 'Avg Deal Size', 'value' => '$' . number_format($avgDeal / 1000, 0) . 'K', 'icon' => 'dollar-sign', 'color' => 'info'],
            ['label' => 'Stale Deals', 'value' => $staleDeals, 'icon' => 'alert-circle', 'color' => $staleDeals > 0 ? 'danger' : 'success'],
            ['label' => 'Product Areas', 'value' => $productAreas, 'icon' => 'grid', 'color' => 'primary'],
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
    {{-- Pipeline by Stage --}}
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Pipeline by Stage</div></div>
            <div class="card-body" style="min-height:300px">
                <div id="chart-pipeline-stage"></div>
            </div>
        </div>
    </div>
    {{-- Pipeline by Origin --}}
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Pipeline by Origin</div></div>
            <div class="card-body" style="min-height:300px">
                <div id="chart-pipeline-origin"></div>
            </div>
        </div>
    </div>
</div>

{{-- Stale Deals Table --}}
@if($staleList->count() > 0)
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title">Stale Deals (No Movement in 30+ Days)</div>
    </div>
    <div class="card-body">
        <table class="table table-hover m-0">
            <thead>
                <tr><th>Opportunity</th><th>Partner</th><th>Stage</th><th class="text-end">Amount</th><th>Last Updated</th></tr>
            </thead>
            <tbody>
                @foreach($staleList as $deal)
                <tr>
                    <td>{{ $deal->opportunity_name }}</td>
                    <td>{{ $deal->partner_name }}</td>
                    <td><span class="badge bg-secondary">{{ $deal->stage }}</span></td>
                    <td class="text-end">${{ number_format($deal->amount, 0) }}</td>
                    <td>{{ \Carbon\Carbon::parse($deal->updated_at)->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const byStage = @json($byStage);
    if (document.querySelector('#chart-pipeline-stage') && byStage.length > 0) {
        new ApexCharts(document.querySelector('#chart-pipeline-stage'), {
            chart: { type: 'bar', height: 280 },
            series: [{ name: 'Value', data: byStage.map(s => parseFloat(s.total)) }],
            xaxis: { categories: byStage.map(s => s.stage) },
            yaxis: { labels: { formatter: v => '$' + (v/1000).toFixed(0) + 'K' } },
            colors: ['#0D6EFD'],
            plotOptions: { bar: { borderRadius: 4 } },
        }).render();
    }

    const byOrigin = @json($byOrigin);
    const origins = [...new Set(byOrigin.map(o => o.origin))];
    const oQuarters = [...new Set(byOrigin.map(o => o.fiscal_quarter))].sort();
    if (document.querySelector('#chart-pipeline-origin') && byOrigin.length > 0) {
        new ApexCharts(document.querySelector('#chart-pipeline-origin'), {
            chart: { type: 'bar', stacked: true, height: 280 },
            series: origins.map(origin => ({
                name: origin,
                data: oQuarters.map(q => byOrigin.filter(o => o.origin === origin && o.fiscal_quarter === q).reduce((s, o) => s + parseFloat(o.total), 0))
            })),
            xaxis: { categories: oQuarters },
            yaxis: { labels: { formatter: v => '$' + (v/1000).toFixed(0) + 'K' } },
            plotOptions: { bar: { borderRadius: 4 } },
        }).render();
    }
});
</script>
@endpush
