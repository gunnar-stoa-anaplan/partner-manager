{{-- KPI Cards --}}
<div class="row">
    @php
        $kpis = [
            ['label' => 'Active Projects', 'value' => $activeProjects, 'icon' => 'clipboard', 'color' => 'primary'],
            ['label' => 'On-Time Rate', 'value' => $onTimeRate . '%', 'icon' => 'clock', 'color' => $onTimeRate >= 75 ? 'success' : 'warning'],
            ['label' => 'Referenceable', 'value' => $refRate . '%', 'icon' => 'star', 'color' => 'info'],
            ['label' => 'Open Cases', 'value' => $openCases, 'icon' => 'alert-circle', 'color' => $openCases > 0 ? 'danger' : 'success'],
            ['label' => 'Escalation Rate', 'value' => $escalationRate . '%', 'icon' => 'alert-triangle', 'color' => $escalationRate > 15 ? 'danger' : 'success'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="col-xl-2 col-md-4 col-6">
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
    {{-- Projects by Status --}}
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Projects by Status</div></div>
            <div class="card-body" style="min-height:300px">
                <div id="chart-project-status"></div>
            </div>
        </div>
    </div>
    {{-- Cases by Partner --}}
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Cases by Partner</div></div>
            <div class="card-body" style="min-height:300px">
                <div id="chart-cases-partner"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const projStatus = @json($projectsByStatus);
    const statuses = Object.keys(projStatus);
    const statusValues = Object.values(projStatus);
    if (document.querySelector('#chart-project-status') && statuses.length > 0) {
        new ApexCharts(document.querySelector('#chart-project-status'), {
            chart: { type: 'donut', height: 280 },
            series: statusValues.map(Number),
            labels: statuses.map(s => s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())),
        }).render();
    }

    const caseData = @json($casesByPartner);
    const cPartners = [...new Set(caseData.map(d => d.name))];
    const sevs = [...new Set(caseData.map(d => d.severity))];
    const sevColors = { low: '#198754', medium: '#FFC107', high: '#FD7E14', critical: '#DC3545' };
    if (document.querySelector('#chart-cases-partner') && caseData.length > 0) {
        new ApexCharts(document.querySelector('#chart-cases-partner'), {
            chart: { type: 'bar', stacked: true, height: 280 },
            series: sevs.map(s => ({
                name: s, data: cPartners.map(p => caseData.filter(d => d.name === p && d.severity === s).reduce((sum, d) => sum + d.count, 0))
            })),
            xaxis: { categories: cPartners },
            colors: sevs.map(s => sevColors[s] || '#6C757D'),
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        }).render();
    }
});
</script>
@endpush
