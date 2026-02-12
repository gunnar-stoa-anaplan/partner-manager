{{-- KPI Cards --}}
<div class="row">
    @php
        $kpis = [
            ['label' => 'Active Certs', 'value' => $activeCerts, 'icon' => 'award', 'color' => 'primary'],
            ['label' => 'Expiring (90d)', 'value' => $expiring90, 'icon' => 'alert-circle', 'color' => $expiring90 > 0 ? 'danger' : 'success'],
            ['label' => 'Attendance Rate', 'value' => $attendanceRate . '%', 'icon' => 'users', 'color' => 'info'],
            ['label' => 'Blueprint Completion', 'value' => $bpCompletionRate . '%', 'icon' => 'layers', 'color' => 'success'],
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
    {{-- Certifications by Type --}}
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Certifications by Type</div></div>
            <div class="card-body" style="min-height:300px">
                <div id="chart-certs-type"></div>
            </div>
        </div>
    </div>
    {{-- Blueprint Progress by Partner --}}
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Blueprint Progress by Partner</div></div>
            <div class="card-body" style="min-height:300px">
                <div id="chart-bp-partner"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const certsByType = @json($certsByType);
    const typeLabels = Object.keys(certsByType);
    const typeValues = Object.values(certsByType);
    if (document.querySelector('#chart-certs-type') && typeLabels.length > 0) {
        new ApexCharts(document.querySelector('#chart-certs-type'), {
            chart: { type: 'donut', height: 280 },
            series: typeValues.map(Number),
            labels: typeLabels.map(t => t.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())),
        }).render();
    }

    const bpData = @json($bpByPartner);
    const bpPartners = [...new Set(bpData.map(d => d.name))];
    const bpStatuses = [...new Set(bpData.map(d => d.status))];
    const bpColors = { completed: '#198754', active: '#0D6EFD', assigned: '#FFC107' };
    if (document.querySelector('#chart-bp-partner') && bpData.length > 0) {
        new ApexCharts(document.querySelector('#chart-bp-partner'), {
            chart: { type: 'bar', stacked: true, height: 280 },
            series: bpStatuses.map(s => ({
                name: s, data: bpPartners.map(p => bpData.filter(d => d.name === p && d.status === s).reduce((sum, d) => sum + d.count, 0))
            })),
            xaxis: { categories: bpPartners },
            colors: bpStatuses.map(s => bpColors[s] || '#6C757D'),
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        }).render();
    }
});
</script>
@endpush
