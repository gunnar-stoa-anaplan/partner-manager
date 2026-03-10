{{-- Delivery Risk Scoring --}}
<div class="row">
    @php
        $riskCards = [
            ['label' => 'Very High Risk', 'count' => $riskSummary['very_high'], 'nnacv' => $riskSummary['very_high_nnacv'], 'color' => 'danger', 'desc' => '0 resources'],
            ['label' => 'High Risk', 'count' => $riskSummary['high'], 'nnacv' => $riskSummary['high_nnacv'], 'color' => 'warning', 'desc' => '1-2 resources'],
            ['label' => 'Low Risk', 'count' => $riskSummary['low'], 'nnacv' => $riskSummary['low_nnacv'], 'color' => 'success', 'desc' => '3+ resources'],
        ];
    @endphp
    @foreach($riskCards as $card)
    <div class="col-xl-4 col-md-6">
        <div class="card shadow-0 mb-g border-{{ $card['color'] }} border-opacity-25">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 text-{{ $card['color'] }}">{{ $card['count'] }}</h2>
                <h4 class="fw-500 m-0">${{ number_format($card['nnacv'] / 1000, 0) }}K</h4>
                <small class="text-muted">{{ $card['label'] }} ({{ $card['desc'] }})</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Risk Distribution</div></div>
            <div class="card-body">
                <div id="chart-risk-dist" style="min-height:280px"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">NNACV at Risk</div></div>
            <div class="card-body">
                <div id="chart-risk-nnacv" style="min-height:280px"></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Opportunities by Risk Level</div></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead><tr><th>Risk</th><th>Opportunity</th><th>Partner</th><th>Stage</th><th>Program</th><th class="text-end">Resources</th><th class="text-end">NNACV</th></tr></thead>
                <tbody>
                @foreach($scoredOpps->take(50) as $opp)
                @php
                    $badgeColor = match($opp->risk_level) {
                        'Very High Risk' => 'danger',
                        'High Risk' => 'warning',
                        'Low Risk' => 'success',
                        default => 'secondary',
                    };
                @endphp
                <tr>
                    <td><span class="badge bg-{{ $badgeColor }} bg-opacity-25 text-{{ $badgeColor }}">{{ $opp->risk_level }}</span></td>
                    <td class="text-truncate" style="max-width:250px">{{ $opp->opportunity_name }}</td>
                    <td>{{ $opp->partner_name }}</td>
                    <td class="small">{{ $opp->stage }}</td>
                    <td><span class="badge bg-secondary bg-opacity-25 text-secondary">{{ $opp->matched_program ?? 'N/A' }}</span></td>
                    <td class="text-end">{{ $opp->resource_count }}</td>
                    <td class="text-end">${{ number_format($opp->amount / 1000, 0) }}K</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const risk = @json($riskSummary);

    if (document.querySelector('#chart-risk-dist')) {
        new ApexCharts(document.querySelector('#chart-risk-dist'), {
            chart: { type: 'donut', height: 280 },
            series: [risk.very_high, risk.high, risk.low],
            labels: ['Very High', 'High', 'Low'],
            colors: ['#DC3545', '#FFC107', '#198754'],
            legend: { position: 'bottom' },
        }).render();
    }

    if (document.querySelector('#chart-risk-nnacv')) {
        new ApexCharts(document.querySelector('#chart-risk-nnacv'), {
            chart: { type: 'bar', height: 280 },
            series: [{ name: 'NNACV', data: [risk.very_high_nnacv, risk.high_nnacv, risk.low_nnacv] }],
            xaxis: { categories: ['Very High', 'High', 'Low'] },
            yaxis: { labels: { formatter: v => '$' + (v/1000).toFixed(0) + 'K' } },
            colors: ['#DC3545', '#FFC107', '#198754'],
            plotOptions: { bar: { distributed: true, borderRadius: 4 } },
            legend: { show: false },
        }).render();
    }
});
</script>
@endpush
