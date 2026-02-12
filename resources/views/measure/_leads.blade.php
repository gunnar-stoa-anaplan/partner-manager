{{-- KPI Cards --}}
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-primary" style="width:24px;height:24px"><use href="/icons/sprite.svg#users"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ $totalLeads }}</h2>
                <small class="text-muted">Total Leads</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-success" style="width:24px;height:24px"><use href="/icons/sprite.svg#check-circle"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ $byStatus->get('accepted', 0) }}</h2>
                <small class="text-muted">Accepted</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-warning" style="width:24px;height:24px"><use href="/icons/sprite.svg#clock"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ $byStatus->get('assigned', 0) }}</h2>
                <small class="text-muted">Assigned (Pending)</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-danger" style="width:24px;height:24px"><use href="/icons/sprite.svg#x-circle"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ $byStatus->get('rejected', 0) }}</h2>
                <small class="text-muted">Rejected</small>
            </div>
        </div>
    </div>
</div>

{{-- Lead Quality by Partner --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Lead Quality by Partner</div></div>
    <div class="card-body">
        <div id="chart-leads-by-partner" style="min-height:300px"></div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = @json($byPartner);
    const partners = [...new Set(data.map(d => d.name))];
    const statuses = [...new Set(data.map(d => d.status))];
    const colors = { accepted: '#198754', assigned: '#FFC107', rejected: '#DC3545', unassigned: '#6C757D' };

    if (document.querySelector('#chart-leads-by-partner') && data.length > 0) {
        new ApexCharts(document.querySelector('#chart-leads-by-partner'), {
            chart: { type: 'bar', stacked: true, height: 280 },
            series: statuses.map(s => ({
                name: s,
                data: partners.map(p => data.filter(d => d.name === p && d.status === s).reduce((sum, d) => sum + d.count, 0))
            })),
            xaxis: { categories: partners },
            colors: statuses.map(s => colors[s] || '#6C757D'),
            plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        }).render();
    }
});
</script>
@endpush
