{{-- Week-over-Week Deltas --}}
@if($deltas)
<div class="row">
    @php
        $deltaCards = [
            ['label' => 'NNACV', 'current' => '$' . number_format($current['total_nnacv'] / 1000, 0) . 'K', 'delta' => $deltas['nnacv'], 'format' => 'currency'],
            ['label' => 'Opportunities', 'current' => $current['opp_count'], 'delta' => $deltas['opps'], 'format' => 'number'],
            ['label' => 'Resources', 'current' => $current['resource_count'], 'delta' => $deltas['resources'], 'format' => 'number'],
            ['label' => 'Sourced NNACV', 'current' => '$' . number_format($current['sourced_nnacv'] / 1000, 0) . 'K', 'delta' => $deltas['sourced_nnacv'], 'format' => 'currency'],
            ['label' => 'DA Coverage', 'current' => $current['delivery_assurance_pct'] . '%', 'delta' => $deltas['da_pct'], 'format' => 'pct'],
        ];
    @endphp
    @foreach($deltaCards as $card)
    <div class="col-xl col-md-4 col-6">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h3 class="fw-300 m-0">{{ $card['current'] }}</h3>
                <small class="text-muted d-block">{{ $card['label'] }}</small>
                @php
                    $deltaVal = $card['delta'];
                    $isPositive = $deltaVal > 0;
                    $color = $isPositive ? 'success' : ($deltaVal < 0 ? 'danger' : 'muted');
                    $arrow = $isPositive ? '&#9650;' : ($deltaVal < 0 ? '&#9660;' : '&#8212;');
                    $formatted = match($card['format']) {
                        'currency' => ($isPositive ? '+$' : '-$') . number_format(abs($deltaVal) / 1000, 0) . 'K',
                        'pct' => ($isPositive ? '+' : '') . number_format($deltaVal, 1) . 'pp',
                        default => ($isPositive ? '+' : '') . $deltaVal,
                    };
                @endphp
                <span class="badge bg-{{ $color }} bg-opacity-25 text-{{ $color }} mt-1">
                    {!! $arrow !!} {{ $formatted }}
                </span>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Previous snapshot comparison table --}}
<div class="row">
    <div class="col-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Snapshot Comparison</div></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Metric</th><th class="text-end">Previous</th><th class="text-end">Current</th><th class="text-end">Change</th></tr></thead>
                        <tbody>
                        @php
                            $rows = [
                                ['Total NNACV', '$'.number_format($previous['total_nnacv']/1000,0).'K', '$'.number_format($current['total_nnacv']/1000,0).'K', $deltas['nnacv']],
                                ['Opportunities', $previous['opp_count'], $current['opp_count'], $deltas['opps']],
                                ['Delivery Resources', $previous['resource_count'], $current['resource_count'], $deltas['resources']],
                                ['Partners', $previous['partner_count'], $current['partner_count'], $current['partner_count'] - $previous['partner_count']],
                                ['Sourced NNACV', '$'.number_format($previous['sourced_nnacv']/1000,0).'K', '$'.number_format($current['sourced_nnacv']/1000,0).'K', $deltas['sourced_nnacv']],
                                ['DA %', $previous['delivery_assurance_pct'].'%', $current['delivery_assurance_pct'].'%', $deltas['da_pct']],
                            ];
                        @endphp
                        @foreach($rows as $row)
                        <tr>
                            <td>{{ $row[0] }}</td>
                            <td class="text-end">{{ $row[1] }}</td>
                            <td class="text-end fw-500">{{ $row[2] }}</td>
                            <td class="text-end text-{{ $row[3] > 0 ? 'success' : ($row[3] < 0 ? 'danger' : 'muted') }}">
                                {{ $row[3] > 0 ? '+' : '' }}{{ is_float($row[3]) ? number_format($row[3], 1) : $row[3] }}
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="alert alert-info">Need at least two snapshots to show week-over-week changes.</div>
@endif
