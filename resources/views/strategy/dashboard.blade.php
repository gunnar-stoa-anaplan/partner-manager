@extends('layouts.vertical', ['pageTitle' => 'Strategy Workbench'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Strategy Workbench', 'pageSubTitle1' => 'Strategy'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The Strategy Workbench is the CoE's command center for partner program planning. Create and commit strategic plans, track actuals against targets, model what-if scenarios, and conduct periodic reviews to keep the program on track.</p>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- KPI Summary Cards --}}
<div class="row mb-g">
    @php
        $kpiCards = [
            ['label' => 'Total Plans', 'value' => $stats['total_plans'], 'color' => 'primary', 'icon' => 'file-text'],
            ['label' => 'Active Plans', 'value' => $stats['active_plans'], 'color' => 'success', 'icon' => 'check-circle'],
            ['label' => 'Draft Plans', 'value' => $stats['draft_plans'], 'color' => 'secondary', 'icon' => 'edit'],
            ['label' => 'Scenarios', 'value' => $stats['total_scenarios'], 'color' => 'info', 'icon' => 'git-branch'],
            ['label' => 'Pending Reviews', 'value' => $stats['pending_reviews'], 'color' => 'warning', 'icon' => 'calendar'],
        ];
    @endphp
    @foreach($kpiCards as $kpi)
    <div class="col-lg col-sm-6">
        <div class="card shadow-0 mb-g border-top border-3 border-{{ $kpi['color'] }}">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-{{ $kpi['color'] }}" style="width:24px;height:24px"><use href="/icons/sprite.svg#{{ $kpi['icon'] }}"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ $kpi['value'] }}</h2>
                <small class="text-muted">{{ $kpi['label'] }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($activePlan && $varianceSummary)
{{-- Active Plan Variance Overview --}}
<div class="row mb-g">
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#compass"></use></svg>
                    {{ $activePlan->name }}
                    <span class="badge bg-{{ \App\Models\StrategyPlan::STATUS_COLORS[$activePlan->status] }} ms-2">{{ \App\Models\StrategyPlan::STATUSES[$activePlan->status] }}</span>
                </div>
                <div>
                    <a href="{{ route('strategy.plans.variance', $activePlan) }}" class="btn btn-outline-primary btn-sm">View Variance</a>
                    <a href="{{ route('strategy.plans.show', $activePlan) }}" class="btn btn-primary btn-sm">View Plan</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <h3 class="fw-300 text-success m-0">{{ $varianceSummary['onTrack'] }}</h3>
                        <small class="text-muted">On Track</small>
                    </div>
                    <div class="col-3">
                        <h3 class="fw-300 text-warning m-0">{{ $varianceSummary['atRisk'] }}</h3>
                        <small class="text-muted">At Risk</small>
                    </div>
                    <div class="col-3">
                        <h3 class="fw-300 text-danger m-0">{{ $varianceSummary['behind'] }}</h3>
                        <small class="text-muted">Behind</small>
                    </div>
                    <div class="col-3">
                        <h3 class="fw-300 text-secondary m-0">{{ $varianceSummary['noData'] }}</h3>
                        <small class="text-muted">No Data</small>
                    </div>
                </div>

                @if($varianceSummary['totalBudget'] > 0)
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="fw-500">Budget Utilization</small>
                    <small class="text-muted">${{ number_format($varianceSummary['actualBudget']) }} / ${{ number_format($varianceSummary['totalBudget']) }}</small>
                </div>
                <div class="progress" style="height: 8px;">
                    @php $budgetPct = $varianceSummary['totalBudget'] > 0 ? min(($varianceSummary['actualBudget'] / $varianceSummary['totalBudget']) * 100, 100) : 0; @endphp
                    <div class="progress-bar bg-{{ $budgetPct > 90 ? 'danger' : ($budgetPct > 70 ? 'warning' : 'success') }}" style="width: {{ $budgetPct }}%"></div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Upcoming Reviews --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title mb-0">Upcoming Reviews</div>
            </div>
            <div class="card-body p-0">
                @if($upcomingReviews->isEmpty())
                    <div class="p-3 text-muted text-center">No upcoming reviews scheduled.</div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($upcomingReviews as $review)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-{{ \App\Models\StrategyPlanReview::REVIEW_TYPE_COLORS[$review->review_type] ?? 'secondary' }} me-1">{{ \App\Models\StrategyPlanReview::REVIEW_TYPES[$review->review_type] }}</span>
                                <small class="text-muted">{{ $review->plan->name ?? '' }}</small>
                            </div>
                            <small class="fw-500">{{ $review->review_date->format('M d') }}</small>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- Recent Plans --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title mb-0">Strategic Plans</div>
        <a href="{{ route('strategy.plans.create') }}" class="btn btn-primary btn-sm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> New Plan
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Fiscal Year</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th class="text-center">Targets</th>
                    <th class="text-center">Scenarios</th>
                    <th>Version</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                <tr>
                    <td>
                        <a href="{{ route('strategy.plans.show', $plan) }}" class="fw-500 text-decoration-none">{{ $plan->name }}</a>
                    </td>
                    <td>{{ $plan->fiscal_year }}</td>
                    <td>{{ \App\Models\StrategyPlan::PERIOD_TYPES[$plan->period_type] ?? $plan->period_type }}</td>
                    <td><span class="badge bg-{{ \App\Models\StrategyPlan::STATUS_COLORS[$plan->status] }}">{{ \App\Models\StrategyPlan::STATUSES[$plan->status] }}</span></td>
                    <td class="text-center">{{ $plan->targets_count }}</td>
                    <td class="text-center">{{ $plan->scenarios_count }}</td>
                    <td>v{{ $plan->version }}</td>
                    <td class="text-end">
                        <a href="{{ route('strategy.plans.show', $plan) }}" class="btn btn-outline-secondary btn-xs">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No strategic plans yet. <a href="{{ route('strategy.plans.create') }}">Create your first plan</a>.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
