{{-- Historical Trends --}}
<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">NNACV Over Time</div></div>
            <div class="card-body">
                <div id="chart-nnacv-trend" style="min-height:300px"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Opportunity & Resource Count Over Time</div></div>
            <div class="card-body">
                <div id="chart-count-trend" style="min-height:300px"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">App vs Platform NNACV Over Time</div></div>
            <div class="card-body">
                <div id="chart-app-platform-trend" style="min-height:300px"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Trend Data</div></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Date</th><th class="text-end">NNACV</th><th class="text-end">Opps</th><th class="text-end">Resources</th><th class="text-end">App</th><th class="text-end">Platform</th></tr></thead>
                        <tbody>
                        @foreach($trends as $t)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($t['date'])->format('M j, Y') }}</td>
                            <td class="text-end">${{ number_format($t['nnacv'] / 1000, 0) }}K</td>
                            <td class="text-end">{{ $t['opps'] }}</td>
                            <td class="text-end">{{ $t['resources'] }}</td>
                            <td class="text-end">${{ number_format($t['app_nnacv'] / 1000, 0) }}K</td>
                            <td class="text-end">${{ number_format($t['platform_nnacv'] / 1000, 0) }}K</td>
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
    const trends = @json($trends->values());
    const dates = trends.map(t => t.date);

    if (document.querySelector('#chart-nnacv-trend')) {
        new ApexCharts(document.querySelector('#chart-nnacv-trend'), {
            chart: { type: 'area', height: 300 },
            series: [{ name: 'NNACV', data: trends.map(t => t.nnacv) }],
            xaxis: { categories: dates, type: 'datetime' },
            yaxis: { labels: { formatter: v => '$' + (v/1000000).toFixed(1) + 'M' } },
            colors: ['#198754'],
            stroke: { curve: 'smooth', width: 3 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0 } },
        }).render();
    }

    if (document.querySelector('#chart-count-trend')) {
        new ApexCharts(document.querySelector('#chart-count-trend'), {
            chart: { type: 'line', height: 300 },
            series: [
                { name: 'Opportunities', data: trends.map(t => t.opps) },
                { name: 'Resources', data: trends.map(t => t.resources) },
            ],
            xaxis: { categories: dates, type: 'datetime' },
            colors: ['#0D6EFD', '#FFC107'],
            stroke: { width: 3 },
            markers: { size: 5 },
        }).render();
    }

    if (document.querySelector('#chart-app-platform-trend')) {
        new ApexCharts(document.querySelector('#chart-app-platform-trend'), {
            chart: { type: 'bar', height: 300, stacked: true },
            series: [
                { name: 'App', data: trends.map(t => t.app_nnacv) },
                { name: 'Platform', data: trends.map(t => t.platform_nnacv) },
            ],
            xaxis: { categories: dates, type: 'datetime' },
            yaxis: { labels: { formatter: v => '$' + (v/1000000).toFixed(1) + 'M' } },
            colors: ['#0D6EFD', '#6F42C1'],
            plotOptions: { bar: { borderRadius: 4 } },
        }).render();
    }
});
</script>
@endpush
