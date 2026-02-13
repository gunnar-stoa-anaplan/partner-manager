@extends('portal.layout', ['pageTitle' => 'Dashboard'])

@section('content')
<h2 class="fw-bold mb-1">Welcome, {{ $contact->first_name }}</h2>
<p class="text-muted mb-4">{{ $partner->name }} — Partner Performance Overview</p>

<div class="row mb-4">
    {{-- Health Score --}}
    <div class="col-md-3">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                @php $hColor = \App\Models\PartnerHealthSnapshot::RISK_COLORS[$health['risk_level']] ?? 'secondary'; @endphp
                <h1 class="display-5 fw-bold text-{{ $hColor }} mb-0">{{ $health['score'] }}</h1>
                <span class="badge bg-{{ $hColor }}">{{ \App\Models\PartnerHealthSnapshot::RISK_LEVELS[$health['risk_level']] ?? $health['risk_level'] }}</span>
                <p class="text-muted mt-2 mb-0">Health Score</p>
            </div>
        </div>
    </div>

    {{-- Scorecard --}}
    <div class="col-md-3">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                <h1 class="display-5 fw-bold text-primary mb-0">{{ $scorecard ? $scorecard->overall_score : '—' }}</h1>
                <p class="text-muted mt-2 mb-0">Scorecard</p>
            </div>
        </div>
    </div>

    {{-- Pipeline --}}
    <div class="col-md-3">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                <h1 class="display-5 fw-bold text-success mb-0">${{ number_format($pipeline->total ?? 0) }}</h1>
                <small class="text-muted">{{ $pipeline->count ?? 0 }} opportunities</small>
                <p class="text-muted mt-2 mb-0">Open Pipeline</p>
            </div>
        </div>
    </div>

    {{-- Goals --}}
    <div class="col-md-3">
        <div class="card portal-card mb-3">
            <div class="card-body text-center py-4">
                @php
                    $goalsWithTarget = $goals->where('target_value', '>', 0);
                    $avgAttainment = $goalsWithTarget->isNotEmpty()
                        ? round($goalsWithTarget->avg(fn($g) => $g->getAttainmentPercentage()), 1)
                        : 0;
                @endphp
                <h1 class="display-5 fw-bold text-info mb-0">{{ $avgAttainment }}%</h1>
                <small class="text-muted">{{ $goals->count() }} active goals</small>
                <p class="text-muted mt-2 mb-0">Goal Attainment</p>
            </div>
        </div>
    </div>
</div>

{{-- Health Signals --}}
@if(!empty($health['signals']))
<div class="card portal-card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Risk Signals</h5></div>
    <div class="card-body">
        @foreach($health['signals'] as $signal)
        <div class="d-flex align-items-center mb-2">
            <span class="badge bg-{{ $hColor }} me-2">!</span>
            {{ $signal }}
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Goals Table --}}
@if($goals->isNotEmpty())
<div class="card portal-card mb-4">
    <div class="card-header"><h5 class="card-title mb-0">Assigned Goals</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Goal</th>
                        <th>Category</th>
                        <th class="text-end">Target</th>
                        <th class="text-end">Actual</th>
                        <th style="width: 150px">Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($goals as $g)
                    @php $pct = $g->getAttainmentPercentage(); @endphp
                    <tr>
                        <td class="fw-500">{{ $g->name }}</td>
                        <td>{{ \App\Models\GoalTemplate::CATEGORIES[$g->category] ?? $g->category }}</td>
                        <td class="text-end">{{ $g->formatted_target }}</td>
                        <td class="text-end">{{ $g->formatted_actual }}</td>
                        <td>
                            <div class="progress" style="height: 14px;">
                                <div class="progress-bar bg-{{ $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') }}"
                                     style="width: {{ min($pct, 100) }}%">
                                    {{ $pct }}%
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ \App\Models\PartnerAssignedGoal::STATUS_COLORS[$g->status] ?? 'secondary' }}">
                                {{ \App\Models\PartnerAssignedGoal::STATUSES[$g->status] ?? $g->status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Recent Deal Registrations --}}
<div class="card portal-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Recent Deal Registrations</h5>
        <a href="{{ route('portal.deal-registrations.create') }}" class="btn btn-sm btn-primary">Register New Deal</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Deal Name</th>
                        <th>Customer</th>
                        <th class="text-end">Est. Value</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($dealRegs as $deal)
                    <tr>
                        <td class="fw-500">{{ $deal->deal_name }}</td>
                        <td>{{ $deal->customer_name }}</td>
                        <td class="text-end">${{ number_format($deal->estimated_value) }}</td>
                        <td>
                            <span class="badge bg-{{ \App\Models\DealRegistration::STATUS_COLORS[$deal->status] ?? 'secondary' }}">
                                {{ \App\Models\DealRegistration::STATUSES[$deal->status] ?? $deal->status }}
                            </span>
                        </td>
                        <td>{{ $deal->submitted_at?->format('M d') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No deal registrations yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
