@extends('layouts.vertical', ['pageTitle' => 'Partner Health'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Partner Health', 'pageSubTitle1' => 'Act', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Partner Health provides a composite risk assessment for each partner by combining scorecard performance, pipeline trends, goal attainment, enablement activity, contact freshness, and delivery satisfaction. Use the traffic-light dashboard to quickly identify at-risk partners and prioritize your outreach.</p>

{{-- Summary Cards --}}
<div class="row mb-g">
    @php
        $riskCards = [
            'healthy' => ['label' => 'Healthy', 'color' => 'success', 'icon' => 'check-circle'],
            'watch' => ['label' => 'Watch', 'color' => 'info', 'icon' => 'eye'],
            'at_risk' => ['label' => 'At Risk', 'color' => 'warning', 'icon' => 'alert-triangle'],
            'critical' => ['label' => 'Critical', 'color' => 'danger', 'icon' => 'alert-octagon'],
        ];
    @endphp
    @foreach($riskCards as $level => $meta)
    <div class="col-lg-3 col-sm-6">
        <div class="card shadow-0 mb-g border-top border-3 border-{{ $meta['color'] }}">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-{{ $meta['color'] }}" style="width:28px;height:28px"><use href="/icons/sprite.svg#{{ $meta['icon'] }}"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ $summary[$level] }}</h2>
                <small class="text-muted">{{ $meta['label'] }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Partner Health Grid --}}
<div class="row mb-g">
    @foreach($partners->sortBy(fn($p) => $healthData[$p->id]['score']) as $partner)
        @php
            $h = $healthData[$partner->id];
            $color = \App\Models\PartnerHealthSnapshot::RISK_COLORS[$h['risk_level']] ?? 'secondary';
        @endphp
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card shadow-0 mb-g border-left-{{ $color }}">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <a href="{{ route('act.health.show', $partner) }}" class="fw-500 text-decoration-none">{{ $partner->name }}</a>
                            <br><small class="text-muted">{{ $partner->geography }} {{ $partner->region ? '/ '.$partner->region : '' }}</small>
                        </div>
                        <span class="badge bg-{{ $color }} fs-nano">{{ \App\Models\PartnerHealthSnapshot::RISK_LEVELS[$h['risk_level']] ?? $h['risk_level'] }}</span>
                    </div>

                    {{-- Score Bar --}}
                    <div class="d-flex align-items-center mb-2">
                        <div class="flex-grow-1 me-2">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $color }}" style="width: {{ min($h['score'], 100) }}%"></div>
                            </div>
                        </div>
                        <strong class="text-{{ $color }}" style="min-width:38px">{{ $h['score'] }}</strong>
                    </div>

                    {{-- Top Signals --}}
                    @if(!empty($h['signals']))
                        @foreach(array_slice($h['signals'], 0, 2) as $signal)
                        <small class="d-block text-muted">
                            <svg class="sa-icon sa-icon-sm text-{{ $color }} me-1" style="width:12px;height:12px"><use href="/icons/sprite.svg#alert-circle"></use></svg>
                            {{ $signal }}
                        </small>
                        @endforeach
                        @if(count($h['signals']) > 2)
                        <small class="text-muted">+{{ count($h['signals']) - 2 }} more</small>
                        @endif
                    @else
                        <small class="text-muted">No risk signals detected</small>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($partners->isEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-body text-center py-5">
        <svg class="sa-icon text-muted mb-2" style="width:48px;height:48px"><use href="/icons/sprite.svg#heart"></use></svg>
        <h5 class="text-muted">No partners found</h5>
        <p class="text-muted mb-0">Partner health data will appear here once you have partners assigned.</p>
    </div>
</div>
@endif
@endsection
