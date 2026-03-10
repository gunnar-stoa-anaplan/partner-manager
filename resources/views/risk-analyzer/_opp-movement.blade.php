{{-- Opportunity Movement Tracking --}}
@if($movement)
<div class="row mb-3">
    <div class="col-auto">
        <span class="text-muted small">Comparing <strong>{{ \Carbon\Carbon::parse($fromDate)->format('M j') }}</strong> to <strong>{{ \Carbon\Carbon::parse($toDate)->format('M j, Y') }}</strong></span>
    </div>
</div>

<div class="row">
    @php
        $movementKpis = [
            ['label' => 'New Opps', 'value' => $movement['new']->count(), 'color' => 'success', 'icon' => 'plus-circle'],
            ['label' => 'Dropped Opps', 'value' => $movement['dropped']->count(), 'color' => 'danger', 'icon' => 'minus-circle'],
            ['label' => 'Stage Changes', 'value' => $movement['stage_changes']->count(), 'color' => 'info', 'icon' => 'refresh-cw'],
            ['label' => 'NNACV Changes', 'value' => $movement['amount_changes']->count(), 'color' => 'warning', 'icon' => 'dollar-sign'],
        ];
    @endphp
    @foreach($movementKpis as $kpi)
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

{{-- New Opportunities --}}
@if($movement['new']->isNotEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">New Opportunities</div></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead><tr><th>Opportunity</th><th>Partner</th><th>Stage</th><th class="text-end">NNACV</th></tr></thead>
                <tbody>
                @foreach($movement['new']->take(25) as $opp)
                <tr>
                    <td class="text-truncate" style="max-width:300px">{{ $opp->opportunity_name }}</td>
                    <td>{{ $opp->partner_name }}</td>
                    <td><span class="badge bg-info bg-opacity-25 text-info">{{ $opp->stage }}</span></td>
                    <td class="text-end">${{ number_format($opp->amount / 1000, 0) }}K</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Stage Changes --}}
@if($movement['stage_changes']->isNotEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Stage Changes</div></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead><tr><th>Opportunity</th><th>Partner</th><th>From</th><th>To</th><th class="text-end">NNACV</th></tr></thead>
                <tbody>
                @foreach($movement['stage_changes']->take(25) as $change)
                <tr>
                    <td class="text-truncate" style="max-width:250px">{{ $change['opportunity_name'] }}</td>
                    <td>{{ $change['partner_name'] }}</td>
                    <td><span class="badge bg-secondary bg-opacity-25 text-secondary">{{ $change['from_stage'] }}</span></td>
                    <td><span class="badge bg-primary bg-opacity-25 text-primary">{{ $change['to_stage'] }}</span></td>
                    <td class="text-end">${{ number_format($change['amount'] / 1000, 0) }}K</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- NNACV Changes --}}
@if($movement['amount_changes']->isNotEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">NNACV Changes</div></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead><tr><th>Opportunity</th><th>Partner</th><th class="text-end">From</th><th class="text-end">To</th><th class="text-end">Delta</th></tr></thead>
                <tbody>
                @foreach($movement['amount_changes']->take(25) as $change)
                <tr>
                    <td class="text-truncate" style="max-width:250px">{{ $change['opportunity_name'] }}</td>
                    <td>{{ $change['partner_name'] }}</td>
                    <td class="text-end">${{ number_format($change['from_amount'] / 1000, 0) }}K</td>
                    <td class="text-end">${{ number_format($change['to_amount'] / 1000, 0) }}K</td>
                    <td class="text-end text-{{ $change['delta'] > 0 ? 'success' : 'danger' }}">
                        {{ $change['delta'] > 0 ? '+' : '' }}${{ number_format($change['delta'] / 1000, 0) }}K
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@else
<div class="alert alert-info">Need at least two snapshots to show opportunity movement.</div>
@endif
