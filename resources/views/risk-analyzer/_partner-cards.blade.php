{{-- Partner Risk Cards --}}
<div class="row">
@foreach($cards as $card)
@php
    $riskScore = $card['very_high_risk'] * 3 + $card['high_risk'] * 2;
    $riskColor = match(true) {
        $card['very_high_risk'] > 0 => 'danger',
        $card['high_risk'] > 0 => 'warning',
        default => 'success',
    };
@endphp
<div class="col-xl-4 col-md-6 mb-g">
    <div class="card shadow-0 h-100 border-start border-{{ $riskColor }} border-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title mb-0">
                    <a href="{{ route('partners.show', $card['partner_id']) }}" class="text-decoration-none">{{ $card['partner'] }}</a>
                </h5>
                <span class="badge bg-{{ $riskColor }} bg-opacity-25 text-{{ $riskColor }}">{{ $card['coverage'] }}</span>
            </div>
            <div class="row text-center mb-2">
                <div class="col-4">
                    <div class="fw-500 text-danger">{{ $card['very_high_risk'] }}</div>
                    <small class="text-muted" style="font-size:0.7rem">V.High</small>
                </div>
                <div class="col-4">
                    <div class="fw-500 text-warning">{{ $card['high_risk'] }}</div>
                    <small class="text-muted" style="font-size:0.7rem">High</small>
                </div>
                <div class="col-4">
                    <div class="fw-500 text-success">{{ $card['low_risk'] }}</div>
                    <small class="text-muted" style="font-size:0.7rem">Low</small>
                </div>
            </div>
            <div class="d-flex justify-content-between small text-muted">
                <span>{{ $card['total_opps'] }} opps</span>
                <span class="fw-500">${{ number_format($card['total_nnacv'] / 1000, 0) }}K</span>
            </div>
            {{-- Mini program bar --}}
            @if(!empty($card['program_counts']))
            <div class="d-flex gap-1 mt-2">
                @foreach($card['program_counts'] as $prog => $count)
                @php
                    $progColor = $count === 0 ? 'secondary' : ($count <= 2 ? 'warning' : 'success');
                @endphp
                <span class="badge bg-{{ $progColor }} bg-opacity-25 text-{{ $progColor }}" style="font-size:0.6rem" title="{{ $prog }}: {{ $count }}">{{ $prog }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endforeach
</div>
