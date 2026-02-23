@extends('layouts.vertical', ['pageTitle' => 'Plan vs Actuals — ' . $plan->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Plan vs Actuals', 'pageSubTitle1' => 'Strategy', 'pageSubTitle1Url' => route('strategy.dashboard'), 'pageSubTitle2' => $plan->name, 'pageSubTitle2Url' => route('strategy.plans.show', $plan)])
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mt-3 mb-4">
    <p class="text-muted fst-italic m-0">Variance analysis for <strong>{{ $plan->name }}</strong> ({{ $plan->fiscal_year }}). Compare committed targets against actual performance to identify areas that need attention.</p>
    <form method="POST" action="{{ route('strategy.plans.refresh-actuals', $plan) }}">
        @csrf
        <button type="submit" class="btn btn-outline-success btn-sm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#refresh-cw"></use></svg> Refresh Actuals
        </button>
    </form>
</div>

{{-- Status Summary Cards --}}
@php
    $allTargets = $plan->targets;
    $withActuals = $allTargets->filter(fn($t) => $t->actual_value !== null);
    $onTrack = $withActuals->filter(fn($t) => $t->getAttainmentPercentage() >= 70)->count();
    $atRisk = $withActuals->filter(fn($t) => $t->getAttainmentPercentage() >= 40 && $t->getAttainmentPercentage() < 70)->count();
    $behind = $withActuals->filter(fn($t) => $t->getAttainmentPercentage() < 40)->count();
@endphp
<div class="row mb-g">
    <div class="col-lg-3">
        <div class="card shadow-0 mb-g border-top border-3 border-success">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-success" style="width:24px;height:24px"><use href="/icons/sprite.svg#check-circle"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ $onTrack }}</h2>
                <small class="text-muted">On Track (70%+)</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card shadow-0 mb-g border-top border-3 border-warning">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-warning" style="width:24px;height:24px"><use href="/icons/sprite.svg#alert-triangle"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ $atRisk }}</h2>
                <small class="text-muted">At Risk (40-70%)</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card shadow-0 mb-g border-top border-3 border-danger">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-danger" style="width:24px;height:24px"><use href="/icons/sprite.svg#alert-octagon"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ $behind }}</h2>
                <small class="text-muted">Behind (&lt;40%)</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card shadow-0 mb-g border-top border-3 border-secondary">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-secondary" style="width:24px;height:24px"><use href="/icons/sprite.svg#minus-circle"></use></svg>
                <h2 class="fw-300 m-0 mt-1">{{ $allTargets->count() - $withActuals->count() }}</h2>
                <small class="text-muted">No Data</small>
            </div>
        </div>
    </div>
</div>

{{-- Target Variance by Category --}}
@foreach($targetsByCategory as $category => $targets)
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <span class="badge bg-{{ \App\Models\StrategyPlanTarget::CATEGORY_COLORS[$category] ?? 'secondary' }} me-2">{{ \App\Models\StrategyPlanTarget::CATEGORIES[$category] ?? $category }}</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th class="text-end">Target</th>
                    <th class="text-end">Actual</th>
                    <th class="text-end">Variance</th>
                    <th class="text-end">Attainment</th>
                    <th style="width:200px">Progress</th>
                    <th class="text-end">Q1</th>
                    <th class="text-end">Q2</th>
                    <th class="text-end">Q3</th>
                    <th class="text-end">Q4</th>
                </tr>
            </thead>
            <tbody>
                @foreach($targets as $target)
                @php
                    $pct = $target->getAttainmentPercentage();
                    $variance = $target->getVariancePercentage();
                    $statusColor = $pct >= 70 ? 'success' : ($pct >= 40 ? 'warning' : 'danger');
                @endphp
                <tr>
                    <td class="fw-500">{{ $target->metric_label }}</td>
                    <td class="text-end">{{ $target->formatted_target }}</td>
                    <td class="text-end">{{ $target->actual_value !== null ? $target->formatted_actual : '—' }}</td>
                    <td class="text-end">
                        @if($target->actual_value !== null)
                            <span class="text-{{ $variance >= 0 ? 'success' : 'danger' }}">{{ $variance > 0 ? '+' : '' }}{{ $variance }}%</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-end">
                        @if($target->actual_value !== null)
                            <span class="fw-500 text-{{ $statusColor }}">{{ $pct }}%</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $statusColor }}" style="width: {{ min($pct, 100) }}%"></div>
                        </div>
                    </td>
                    <td class="text-end"><small>{{ $target->q1_actual !== null ? number_format($target->q1_actual) : '—' }}<br><span class="text-muted">/ {{ number_format($target->q1_target) }}</span></small></td>
                    <td class="text-end"><small>{{ $target->q2_actual !== null ? number_format($target->q2_actual) : '—' }}<br><span class="text-muted">/ {{ number_format($target->q2_target) }}</span></small></td>
                    <td class="text-end"><small>{{ $target->q3_actual !== null ? number_format($target->q3_actual) : '—' }}<br><span class="text-muted">/ {{ number_format($target->q3_target) }}</span></small></td>
                    <td class="text-end"><small>{{ $target->q4_actual !== null ? number_format($target->q4_actual) : '—' }}<br><span class="text-muted">/ {{ number_format($target->q4_target) }}</span></small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach

{{-- Assumption Validation --}}
@if($plan->assumptions->isNotEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title mb-0">Assumption Validation</div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Assumption</th>
                    <th class="text-end">Planned</th>
                    <th class="text-end">Actual</th>
                    <th class="text-end">Variance</th>
                    <th>Assessment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plan->assumptions as $assumption)
                @php $var = $assumption->getVariancePercentage(); @endphp
                <tr>
                    <td class="fw-500">{{ $assumption->label }}</td>
                    <td class="text-end">{{ $assumption->formatted_planned }}</td>
                    <td class="text-end">{{ $assumption->actual_value !== null ? $assumption->formatted_actual : '—' }}</td>
                    <td class="text-end">
                        @if($assumption->actual_value !== null)
                            <span class="text-{{ abs($var) <= 10 ? 'success' : (abs($var) <= 25 ? 'warning' : 'danger') }}">{{ $var > 0 ? '+' : '' }}{{ $var }}%</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($assumption->actual_value !== null)
                            @if(abs($var) <= 10)
                                <span class="badge bg-success">Validated</span>
                            @elseif(abs($var) <= 25)
                                <span class="badge bg-warning">Diverging</span>
                            @else
                                <span class="badge bg-danger">Invalid</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">Pending</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Budget Utilization --}}
@if($plan->budgets->isNotEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title mb-0">Budget Utilization</div>
    </div>
    <div class="card-body">
        @foreach($plan->budgets as $budget)
        @php $util = $budget->getUtilizationPercentage(); @endphp
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="fw-500">{{ $budget->label }} <span class="badge bg-{{ \App\Models\StrategyPlanBudget::CATEGORY_COLORS[$budget->category] ?? 'secondary' }} ms-1">{{ \App\Models\StrategyPlanBudget::CATEGORIES[$budget->category] ?? $budget->category }}</span></span>
                <span class="text-muted">${{ number_format($budget->actual_amount ?? 0) }} / ${{ number_format($budget->planned_amount) }} ({{ $util }}%)</span>
            </div>
            <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-{{ $util > 90 ? 'danger' : ($util > 70 ? 'warning' : 'success') }}" style="width: {{ min($util, 100) }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
