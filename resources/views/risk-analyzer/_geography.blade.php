{{-- Geography Breakdown --}}
<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">By Geography</div></div>
            <div class="card-body">
                <div id="chart-geo" style="min-height:300px"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">By Region</div></div>
            <div class="card-body">
                <div id="chart-region" style="min-height:300px"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Geography Detail</div></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Geo</th><th class="text-end">Opps</th><th class="text-end">NNACV</th></tr></thead>
                        <tbody>
                        @foreach($geoBreakdown as $geo => $data)
                        <tr>
                            <td>{{ $geo }}</td>
                            <td class="text-end">{{ $data['opps'] }}</td>
                            <td class="text-end">${{ number_format($data['nnacv'] / 1000, 0) }}K</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Region Detail</div></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Region</th><th class="text-end">Opps</th><th class="text-end">NNACV</th></tr></thead>
                        <tbody>
                        @foreach($regionBreakdown as $region => $data)
                        <tr>
                            <td>{{ $region }}</td>
                            <td class="text-end">{{ $data['opps'] }}</td>
                            <td class="text-end">${{ number_format($data['nnacv'] / 1000, 0) }}K</td>
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
    const geoData = @json($geoBreakdown);
    const geoLabels = Object.keys(geoData);
    const geoValues = geoLabels.map(k => geoData[k].nnacv);

    if (document.querySelector('#chart-geo') && geoLabels.length > 0) {
        new ApexCharts(document.querySelector('#chart-geo'), {
            chart: { type: 'bar', height: 300 },
            series: [{ name: 'NNACV', data: geoValues }],
            xaxis: { categories: geoLabels },
            yaxis: { labels: { formatter: v => '$' + (v/1000).toFixed(0) + 'K' } },
            colors: ['#0D6EFD'],
            plotOptions: { bar: { borderRadius: 4, distributed: true } },
            legend: { show: false },
        }).render();
    }

    const regionData = @json($regionBreakdown);
    const regionLabels = Object.keys(regionData);
    const regionValues = regionLabels.map(k => regionData[k].nnacv);

    if (document.querySelector('#chart-region') && regionLabels.length > 0) {
        new ApexCharts(document.querySelector('#chart-region'), {
            chart: { type: 'bar', height: 300 },
            series: [{ name: 'NNACV', data: regionValues }],
            xaxis: { categories: regionLabels },
            yaxis: { labels: { formatter: v => '$' + (v/1000).toFixed(0) + 'K' } },
            colors: ['#198754'],
            plotOptions: { bar: { borderRadius: 4, horizontal: true } },
        }).render();
    }
});
</script>
@endpush
