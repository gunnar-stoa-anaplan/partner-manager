{{-- LOB Breakdown --}}
<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Pipeline by Line of Business</div></div>
            <div class="card-body">
                <div id="chart-lob" style="min-height:300px"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Pipeline by Sales Stage</div></div>
            <div class="card-body">
                <div id="chart-stage" style="min-height:300px"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">LOB Detail</div></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>LOB / Product Area</th><th class="text-end">Opps</th><th class="text-end">NNACV</th></tr></thead>
                        <tbody>
                        @foreach($lobBreakdown as $lob => $data)
                        <tr>
                            <td>{{ $lob }}</td>
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
            <div class="card-header"><div class="card-title">Stage Detail</div></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Stage</th><th class="text-end">Opps</th><th class="text-end">NNACV</th></tr></thead>
                        <tbody>
                        @foreach($stageBreakdown as $stage => $data)
                        <tr>
                            <td>{{ $stage }}</td>
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
    const lobData = @json($lobBreakdown);
    const lobLabels = Object.keys(lobData);

    if (document.querySelector('#chart-lob') && lobLabels.length > 0) {
        new ApexCharts(document.querySelector('#chart-lob'), {
            chart: { type: 'bar', height: 300 },
            series: [
                { name: 'NNACV', data: lobLabels.map(k => lobData[k].nnacv) },
            ],
            xaxis: { categories: lobLabels },
            yaxis: { labels: { formatter: v => '$' + (v/1000).toFixed(0) + 'K' } },
            colors: ['#0D6EFD'],
            plotOptions: { bar: { borderRadius: 4 } },
        }).render();
    }

    const stageData = @json($stageBreakdown);
    const stageLabels = Object.keys(stageData);

    if (document.querySelector('#chart-stage') && stageLabels.length > 0) {
        new ApexCharts(document.querySelector('#chart-stage'), {
            chart: { type: 'bar', height: 300 },
            series: [
                { name: 'Opps', data: stageLabels.map(k => stageData[k].opps) },
            ],
            xaxis: { categories: stageLabels.map(l => l.replace(/^\d+\.\s*/, '')) },
            yaxis: { title: { text: 'Count' } },
            colors: ['#6F42C1'],
            plotOptions: { bar: { borderRadius: 4 } },
        }).render();
    }
});
</script>
@endpush
