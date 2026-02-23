@extends('layouts.vertical', ['pageTitle' => $plan->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $plan->name, 'pageSubTitle1' => 'Strategy', 'pageSubTitle1Url' => route('strategy.dashboard'), 'pageSubTitle2' => 'Plans', 'pageSubTitle2Url' => route('strategy.plans.index')])
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Plan Header --}}
<div class="card shadow-0 mb-g mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4 class="fw-500 m-0">{{ $plan->name }}</h4>
                <div class="d-flex gap-2 mt-1 align-items-center">
                    <span class="badge bg-{{ \App\Models\StrategyPlan::STATUS_COLORS[$plan->status] }}">{{ \App\Models\StrategyPlan::STATUSES[$plan->status] }}</span>
                    <small class="text-muted">{{ $plan->fiscal_year }} &middot; {{ \App\Models\StrategyPlan::PERIOD_TYPES[$plan->period_type] }} &middot; v{{ $plan->version }}</small>
                    @if($plan->committed_at)
                        <small class="text-muted">Committed {{ $plan->committed_at->format('M d, Y') }} by {{ $plan->committedByUser->name ?? 'Unknown' }}</small>
                    @endif
                </div>
                @if($plan->description)
                    <p class="text-muted mt-2 mb-0">{{ $plan->description }}</p>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if($plan->isEditable())
                    <form method="POST" action="{{ route('strategy.plans.commit', $plan) }}" onsubmit="return confirm('Commit this plan? Targets and assumptions will be locked.')">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#lock"></use></svg> Commit Plan
                        </button>
                    </form>
                @endif
                @if($plan->isCommitted())
                    <a href="{{ route('strategy.plans.variance', $plan) }}" class="btn btn-outline-primary btn-sm">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#bar-chart-2"></use></svg> View Variance
                    </a>
                    <form method="POST" action="{{ route('strategy.plans.refresh-actuals', $plan) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-success btn-sm">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#refresh-cw"></use></svg> Refresh Actuals
                        </button>
                    </form>
                    <form method="POST" action="{{ route('strategy.plans.version', $plan) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#copy"></use></svg> New Version
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#targets">Targets ({{ $plan->targets->count() }})</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#assumptions">Assumptions ({{ $plan->assumptions->count() }})</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#budget">Budget ({{ $plan->budgets->count() }})</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#initiatives">Initiatives ({{ $plan->initiatives->count() }})</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#reviews">Reviews ({{ $plan->reviews->count() }})</a></li>
</ul>

<div class="tab-content">
    {{-- Targets Tab --}}
    <div class="tab-pane fade show active" id="targets">
        <div class="card shadow-0 mb-g border-top-0 rounded-top-0">
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Metric</th>
                            <th class="text-end">Target</th>
                            <th class="text-end">Actual</th>
                            <th class="text-end">Attainment</th>
                            <th class="text-end">Q1</th>
                            <th class="text-end">Q2</th>
                            <th class="text-end">Q3</th>
                            <th class="text-end">Q4</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plan->targets->groupBy('category') as $category => $targets)
                            @foreach($targets as $i => $target)
                            <tr>
                                @if($i === 0)
                                <td rowspan="{{ $targets->count() }}" class="align-middle">
                                    <span class="badge bg-{{ \App\Models\StrategyPlanTarget::CATEGORY_COLORS[$category] ?? 'secondary' }}">{{ \App\Models\StrategyPlanTarget::CATEGORIES[$category] ?? $category }}</span>
                                </td>
                                @endif
                                <td>{{ $target->metric_label }}</td>
                                <td class="text-end fw-500">{{ $target->formatted_target }}</td>
                                <td class="text-end">{{ $target->actual_value !== null ? $target->formatted_actual : '—' }}</td>
                                <td class="text-end">
                                    @if($target->actual_value !== null)
                                        @php $pct = $target->getAttainmentPercentage(); @endphp
                                        <span class="text-{{ $pct >= 70 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') }}">{{ $pct }}%</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end"><small>{{ $target->q1_actual !== null ? number_format($target->q1_actual) : '—' }}</small></td>
                                <td class="text-end"><small>{{ $target->q2_actual !== null ? number_format($target->q2_actual) : '—' }}</small></td>
                                <td class="text-end"><small>{{ $target->q3_actual !== null ? number_format($target->q3_actual) : '—' }}</small></td>
                                <td class="text-end"><small>{{ $target->q4_actual !== null ? number_format($target->q4_actual) : '—' }}</small></td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($plan->isEditable())
            <div class="card-footer">
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addTargetModal">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> Add Target
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- Assumptions Tab --}}
    <div class="tab-pane fade" id="assumptions">
        <div class="card shadow-0 mb-g border-top-0 rounded-top-0">
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Assumption</th>
                            <th class="text-end">Planned</th>
                            <th class="text-end">Actual</th>
                            <th class="text-end">Variance</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plan->assumptions as $assumption)
                        <tr>
                            <td class="fw-500">{{ $assumption->label }}</td>
                            <td class="text-end">{{ $assumption->formatted_planned }}</td>
                            <td class="text-end">{{ $assumption->actual_value !== null ? $assumption->formatted_actual : '—' }}</td>
                            <td class="text-end">
                                @if($assumption->actual_value !== null)
                                    @php $var = $assumption->getVariancePercentage(); @endphp
                                    <span class="text-{{ $var >= 0 ? 'success' : 'danger' }}">{{ $var > 0 ? '+' : '' }}{{ $var }}%</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $assumption->notes }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($plan->isEditable())
            <div class="card-footer">
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addAssumptionModal">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> Add Assumption
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- Budget Tab --}}
    <div class="tab-pane fade" id="budget">
        <div class="card shadow-0 mb-g border-top-0 rounded-top-0">
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Line Item</th>
                            <th class="text-end">Planned</th>
                            <th class="text-end">Actual</th>
                            <th class="text-end">Utilization</th>
                            <th style="width:200px">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plan->budgets as $budget)
                        <tr>
                            <td><span class="badge bg-{{ \App\Models\StrategyPlanBudget::CATEGORY_COLORS[$budget->category] ?? 'secondary' }}">{{ \App\Models\StrategyPlanBudget::CATEGORIES[$budget->category] ?? $budget->category }}</span></td>
                            <td class="fw-500">{{ $budget->label }}</td>
                            <td class="text-end">{{ $budget->formatted_planned }}</td>
                            <td class="text-end">{{ $budget->actual_amount !== null ? $budget->formatted_actual : '—' }}</td>
                            <td class="text-end">
                                @if($budget->actual_amount !== null)
                                    {{ $budget->getUtilizationPercentage() }}%
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @php $util = $budget->getUtilizationPercentage(); @endphp
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $util > 90 ? 'danger' : ($util > 70 ? 'warning' : 'success') }}" style="width: {{ min($util, 100) }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($plan->budgets->isNotEmpty())
                    <tfoot>
                        <tr class="fw-500">
                            <td colspan="2">Total</td>
                            <td class="text-end">${{ number_format($plan->budgets->sum('planned_amount')) }}</td>
                            <td class="text-end">${{ number_format($plan->budgets->sum('actual_amount')) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @if($plan->isEditable())
            <div class="card-footer">
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> Add Budget Item
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- Initiatives Tab --}}
    <div class="tab-pane fade" id="initiatives">
        <div class="row mt-3">
            @foreach($plan->initiatives as $initiative)
            <div class="col-lg-6">
                <div class="card shadow-0 mb-g">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-500 mb-1">{{ $initiative->title }}</h6>
                                <span class="badge bg-{{ \App\Models\StrategyPlanInitiative::CATEGORY_COLORS[$initiative->category] ?? 'secondary' }} me-1">{{ \App\Models\StrategyPlanInitiative::CATEGORIES[$initiative->category] ?? $initiative->category }}</span>
                                <span class="badge bg-{{ \App\Models\StrategyPlanInitiative::STATUS_COLORS[$initiative->status] }}">{{ \App\Models\StrategyPlanInitiative::STATUSES[$initiative->status] }}</span>
                                @if($initiative->isOverdue())
                                    <span class="badge bg-danger">Overdue</span>
                                @endif
                            </div>
                            @if(!$plan->isEditable() || true)
                            <form method="POST" action="{{ route('strategy.initiatives.update', $initiative) }}" class="d-inline">
                                @csrf @method('PUT')
                                <select name="status" class="form-select form-select-sm" style="width:130px" onchange="this.form.submit()">
                                    @foreach(\App\Models\StrategyPlanInitiative::STATUSES as $key => $label)
                                    <option value="{{ $key }}" {{ $initiative->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                            @endif
                        </div>
                        @if($initiative->description)
                            <p class="text-muted small mb-2">{{ $initiative->description }}</p>
                        @endif
                        <div class="d-flex gap-3 small text-muted">
                            @if($initiative->owner)
                                <span><svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#user"></use></svg>{{ $initiative->owner->name }}</span>
                            @endif
                            @if($initiative->target_start_date && $initiative->target_end_date)
                                <span><svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#calendar"></use></svg>{{ $initiative->target_start_date->format('M d') }} — {{ $initiative->target_end_date->format('M d, Y') }}</span>
                            @endif
                        </div>
                        @if($initiative->expected_impact)
                            <div class="mt-2 p-2 bg-light rounded small">
                                <strong>Expected Impact:</strong> {{ $initiative->expected_impact }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($plan->isEditable())
        <button class="btn btn-sm btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#addInitiativeModal">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> Add Initiative
        </button>
        @endif
    </div>

    {{-- Reviews Tab --}}
    <div class="tab-pane fade" id="reviews">
        <div class="card shadow-0 mb-g border-top-0 rounded-top-0">
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Reviewer</th>
                            <th>Status</th>
                            <th>Summary</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plan->reviews->sortByDesc('review_date') as $review)
                        <tr>
                            <td><span class="badge bg-{{ \App\Models\StrategyPlanReview::REVIEW_TYPE_COLORS[$review->review_type] ?? 'secondary' }}">{{ \App\Models\StrategyPlanReview::REVIEW_TYPES[$review->review_type] }}</span></td>
                            <td>{{ $review->review_date->format('M d, Y') }}</td>
                            <td>{{ $review->reviewer->name ?? '—' }}</td>
                            <td><span class="badge bg-{{ \App\Models\StrategyPlanReview::STATUS_COLORS[$review->status] }}">{{ \App\Models\StrategyPlanReview::STATUSES[$review->status] }}</span></td>
                            <td><small class="text-muted">{{ Str::limit($review->summary, 80) }}</small></td>
                            <td class="text-end"><a href="{{ route('strategy.reviews.show', $review) }}" class="btn btn-outline-secondary btn-xs">View</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#scheduleReviewModal">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#calendar"></use></svg> Schedule Review
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add Target Modal --}}
<div class="modal fade" id="addTargetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('strategy.plans.targets.store', $plan) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Target</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-500">Category</label>
                        <select name="category" class="form-select" required>
                            @foreach(\App\Models\StrategyPlanTarget::CATEGORIES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Metric Key</label>
                        <input type="text" name="metric_key" class="form-control" placeholder="e.g. partner_sourced_revenue" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Metric Label</label>
                        <input type="text" name="metric_label" class="form-control" placeholder="e.g. Partner-Sourced Revenue" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-500">Unit</label>
                            <select name="unit" class="form-select" required>
                                @foreach(\App\Models\StrategyPlanTarget::UNITS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-500">Annual Target</label>
                            <input type="number" name="target_value" class="form-control" step="0.01" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-3"><label class="form-label small">Q1</label><input type="number" name="q1_target" class="form-control form-control-sm" step="0.01"></div>
                        <div class="col-3"><label class="form-label small">Q2</label><input type="number" name="q2_target" class="form-control form-control-sm" step="0.01"></div>
                        <div class="col-3"><label class="form-label small">Q3</label><input type="number" name="q3_target" class="form-control form-control-sm" step="0.01"></div>
                        <div class="col-3"><label class="form-label small">Q4</label><input type="number" name="q4_target" class="form-control form-control-sm" step="0.01"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Target</button></div>
            </form>
        </div>
    </div>
</div>

{{-- Add Assumption Modal --}}
<div class="modal fade" id="addAssumptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('strategy.plans.assumptions.store', $plan) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Assumption</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-500">Key</label>
                        <input type="text" name="assumption_key" class="form-control" placeholder="e.g. conversion_rate" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Label</label>
                        <input type="text" name="label" class="form-control" placeholder="e.g. Pipeline Conversion Rate" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-500">Planned Value</label>
                            <input type="number" name="planned_value" class="form-control" step="0.0001" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-500">Unit</label>
                            <select name="unit" class="form-select" required>
                                @foreach(\App\Models\StrategyPlanAssumption::UNITS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Assumption</button></div>
            </form>
        </div>
    </div>
</div>

{{-- Add Budget Modal --}}
<div class="modal fade" id="addBudgetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('strategy.plans.budgets.store', $plan) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Budget Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-500">Category</label>
                            <select name="category" class="form-select" required>
                                @foreach(\App\Models\StrategyPlanBudget::CATEGORIES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-500">Label</label>
                            <input type="text" name="label" class="form-control" placeholder="e.g. APAC MDF" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Planned Amount</label>
                        <input type="number" name="planned_amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-3"><label class="form-label small">Q1</label><input type="number" name="q1_planned" class="form-control form-control-sm" step="0.01"></div>
                        <div class="col-3"><label class="form-label small">Q2</label><input type="number" name="q2_planned" class="form-control form-control-sm" step="0.01"></div>
                        <div class="col-3"><label class="form-label small">Q3</label><input type="number" name="q3_planned" class="form-control form-control-sm" step="0.01"></div>
                        <div class="col-3"><label class="form-label small">Q4</label><input type="number" name="q4_planned" class="form-control form-control-sm" step="0.01"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Budget Item</button></div>
            </form>
        </div>
    </div>
</div>

{{-- Add Initiative Modal --}}
<div class="modal fade" id="addInitiativeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('strategy.plans.initiatives.store', $plan) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Initiative</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-500">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label class="form-label fw-500">Category</label>
                            <select name="category" class="form-select" required>
                                @foreach(\App\Models\StrategyPlanInitiative::CATEGORIES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-500">Owner</label>
                            <select name="owner_id" class="form-select">
                                <option value="">Unassigned</option>
                                @foreach($coeUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-500">Expected Impact</label>
                            <input type="text" name="expected_impact" class="form-control" placeholder="e.g. +15% pipeline in APAC">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-500">Target Start</label>
                            <input type="date" name="target_start_date" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-500">Target End</label>
                            <input type="date" name="target_end_date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Initiative</button></div>
            </form>
        </div>
    </div>
</div>

{{-- Schedule Review Modal --}}
<div class="modal fade" id="scheduleReviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('strategy.reviews.store') }}">
                @csrf
                <input type="hidden" name="strategy_plan_id" value="{{ $plan->id }}">
                <div class="modal-header"><h5 class="modal-title">Schedule Review</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-500">Review Type</label>
                            <select name="review_type" class="form-select" required>
                                @foreach(\App\Models\StrategyPlanReview::REVIEW_TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-500">Review Date</label>
                            <input type="date" name="review_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Schedule</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
