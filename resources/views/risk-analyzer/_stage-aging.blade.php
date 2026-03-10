{{-- Stage Aging Analysis --}}
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Average Days in Stage</div></div>
            <div class="card-body">
                <div id="chart-aging" style="min-height:300px"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Aging Summary</div></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Stage</th><th class="text-end">Avg</th><th class="text-end">Max</th><th class="text-end">Opps</th><th class="text-end">NNACV</th></tr></thead>
                        <tbody>
                        @foreach($aging as $stage => $data)
                        @php
                            $agingColor = match(true) {
                                $data['avg_days'] > 90 => 'danger',
                                $data['avg_days'] > 60 => 'warning',
                                $data['avg_days'] > 30 => 'info',
                                default => 'success',
                            };
                        @endphp
                        <tr>
                            <td class="small">{{ $stage }}</td>
                            <td class="text-end text-{{ $agingColor }} fw-500">{{ $data['avg_days'] }}d</td>
                            <td class="text-end">{{ $data['max_days'] }}d</td>
                            <td class="text-end">{{ $data['count'] }}</td>
                            <td class="text-end">${{ number_format($data['total_nnacv'] / 1000, 0) }}K</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const aging = @json($aging);
    const stages = Object.keys(aging);
    const avgDays = stages.map(s => aging[s].avg_days);
    const maxDays = stages.map(s => aging[s].max_days);
    const colors = avgDays.map(d => d > 90 ? '#DC3545' : d > 60 ? '#FFC107' : d > 30 ? '#0DCAF0' : '#198754');

    if (document.querySelector('#chart-aging') && stages.length > 0) {
        new ApexCharts(document.querySelector('#chart-aging'), {
            chart: { type: 'bar', height: 300 },
            series: [
                { name: 'Avg Days', data: avgDays },
                { name: 'Max Days', data: maxDays },
            ],
            xaxis: { categories: stages.map(s => s.replace(/^\d+\.\s*/, '')) },
            colors: ['#0D6EFD', '#E0E0E0'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
        }).render();
    }
});
</script>
@endpush
