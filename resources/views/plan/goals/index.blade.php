@extends('layouts.vertical', ['pageTitle' => 'Goals & Targets'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Goals & Targets', 'pageSubTitle1' => 'Plan', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Goals & Targets tracks partner performance across revenue, enablement, delivery, and more. Assign goals from admin-created templates or create custom goals for specific partners. Use the category dashboard to monitor attainment at a glance and compare progress year-over-year.</p>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Fiscal Year Selector --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-300 m-0">{{ $fy }} Goals</h3>
    <div class="d-flex gap-2 align-items-center">
        <form action="{{ route('plan.goals.refresh-actuals', ['fy' => $fy]) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-success btn-sm" title="Recompute actuals from live data">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#refresh-cw"></use></svg> Refresh Actuals
            </button>
        </form>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignGoalModal">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> Assign Goal
        </button>
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#customGoalModal">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#edit-3"></use></svg> Custom Goal
        </button>
    </div>
</div>

{{-- Category Summary Cards --}}
<div class="row mb-g">
    @php
        $catIcons = ['revenue' => 'dollar-sign', 'enablement' => 'award', 'product_expertise' => 'cpu', 'delivery' => 'truck', 'blueprint' => 'book-open', 'beta_testing' => 'zap'];
        $catColors = ['revenue' => 'success', 'enablement' => 'primary', 'product_expertise' => 'info', 'delivery' => 'warning', 'blueprint' => 'secondary', 'beta_testing' => 'danger'];
    @endphp
    @foreach($categorySummary as $catKey => $cat)
        @if($cat['count'] > 0)
        <div class="col-lg-4 col-xl-2">
            <div class="card shadow-0 mb-g border-top border-3 border-{{ $catColors[$catKey] ?? 'secondary' }}">
                <div class="card-body text-center py-3">
                    <svg class="sa-icon text-{{ $catColors[$catKey] ?? 'secondary' }}" style="width:24px;height:24px"><use href="/icons/sprite.svg#{{ $catIcons[$catKey] ?? 'target' }}"></use></svg>
                    <h2 class="fw-300 m-0 mt-1">{{ $cat['attainment'] }}%</h2>
                    <small class="text-muted d-block">{{ $cat['label'] }}</small>
                    <small class="text-muted">{{ $cat['count'] }} {{ Str::plural('goal', $cat['count']) }}</small>
                </div>
            </div>
        </div>
        @endif
    @endforeach
</div>

{{-- Revenue Planning Calculator --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center" role="button" data-bs-toggle="collapse" data-bs-target="#revenueCalc">
        <div class="card-title mb-0">Revenue Planning Calculator</div>
        <svg class="sa-icon" style="width:16px;height:16px"><use href="/icons/sprite.svg#chevron-down"></use></svg>
    </div>
    <div class="collapse" id="revenueCalc">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-500">Historical ASP</label>
                    <input type="text" class="form-control" id="calc-asp" value="{{ number_format($historicalAsp, 0) }}" readonly>
                    <small class="text-muted">Avg license sales price (trailing 24m)</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-500">Win Rate</label>
                    <input type="text" class="form-control" id="calc-winrate" value="{{ number_format($historicalWinRate * 100, 1) }}%" readonly>
                    <small class="text-muted">Won / (Won + Lost) trailing 12m</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-500">Pipeline Multiple</label>
                    <input type="text" class="form-control" id="calc-multiple" value="{{ $historicalWinRate > 0 ? number_format(1/$historicalWinRate, 1) : '—' }}x" readonly>
                    <small class="text-muted">1 / Win Rate</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-500">Target NNACV</label>
                    <input type="number" class="form-control" id="calc-target" placeholder="e.g. 2000000">
                    <small class="text-muted">Enter target to calculate</small>
                </div>
            </div>
            <div class="row mt-2" id="calc-results" style="display:none;">
                <div class="col-md-3"><div class="p-3 bg-light rounded text-center"><h4 class="m-0" id="res-deals">—</h4><small>Required Closed Deals</small></div></div>
                <div class="col-md-3"><div class="p-3 bg-light rounded text-center"><h4 class="m-0" id="res-pipeline">—</h4><small>Required Pipeline</small></div></div>
                <div class="col-md-3"><div class="p-3 bg-light rounded text-center"><h4 class="m-0" id="res-opps">—</h4><small>New Opps / Quarter</small></div></div>
                <div class="col-md-3"><div class="p-3 bg-light rounded text-center"><h4 class="m-0" id="res-gap">—</h4><small>Pipeline Gap</small></div></div>
            </div>
        </div>
    </div>
</div>

{{-- Year-over-Year Comparison --}}
@if(collect($prevByCategory)->sum('target') > 0)
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center" role="button" data-bs-toggle="collapse" data-bs-target="#yoyComparison">
        <div class="card-title mb-0">Year-over-Year Comparison — {{ $prevFy }} vs {{ $fy }}</div>
        <svg class="sa-icon" style="width:16px;height:16px"><use href="/icons/sprite.svg#chevron-down"></use></svg>
    </div>
    <div class="collapse" id="yoyComparison">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-end">{{ $prevFy }} Target</th>
                        <th class="text-end">{{ $prevFy }} Actual</th>
                        <th class="text-end">{{ $fy }} Target</th>
                        <th class="text-end">{{ $fy }} Actual</th>
                        <th class="text-end">YoY Target Change</th>
                    </tr>
                </thead>
                <tbody>
                @foreach(\App\Models\GoalTemplate::CATEGORIES as $catKey => $catLabel)
                    @php
                        $prev = $prevByCategory[$catKey] ?? ['target' => 0, 'actual' => 0];
                        $curr = $categorySummary[$catKey] ?? ['target' => 0, 'actual' => 0];
                        $change = $prev['target'] > 0 ? round((($curr['target'] - $prev['target']) / $prev['target']) * 100, 1) : 0;
                    @endphp
                    @if($prev['target'] > 0 || ($curr['target'] ?? 0) > 0)
                    <tr>
                        <td><strong>{{ $catLabel }}</strong></td>
                        <td class="text-end">{{ number_format($prev['target']) }}</td>
                        <td class="text-end">{{ number_format($prev['actual']) }}</td>
                        <td class="text-end">{{ number_format($curr['target'] ?? 0) }}</td>
                        <td class="text-end">{{ number_format($curr['actual'] ?? 0) }}</td>
                        <td class="text-end">
                            @if($change > 0)
                                <span class="text-success">+{{ $change }}%</span>
                            @elseif($change < 0)
                                <span class="text-danger">{{ $change }}%</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Goals by Category --}}
@foreach(\App\Models\GoalTemplate::CATEGORIES as $catKey => $catLabel)
    @if(($goalsByCategory[$catKey] ?? collect())->isNotEmpty())
    <div class="card shadow-0 mb-g">
        <div class="card-header d-flex justify-content-between align-items-center" role="button" data-bs-toggle="collapse" data-bs-target="#goalCat-{{ $catKey }}">
            <div class="card-title mb-0">
                <svg class="sa-icon sa-icon-sm text-{{ $catColors[$catKey] ?? 'secondary' }} me-1"><use href="/icons/sprite.svg#{{ $catIcons[$catKey] ?? 'target' }}"></use></svg>
                {{ $catLabel }}
            </div>
            <div>
                <span class="badge bg-{{ $catColors[$catKey] ?? 'secondary' }}">{{ $categorySummary[$catKey]['attainment'] }}% attainment</span>
                <span class="badge bg-secondary ms-1">{{ ($goalsByCategory[$catKey] ?? collect())->count() }} {{ Str::plural('goal', ($goalsByCategory[$catKey] ?? collect())->count()) }}</span>
                <svg class="sa-icon ms-1" style="width:16px;height:16px"><use href="/icons/sprite.svg#chevron-down"></use></svg>
            </div>
        </div>
        <div class="collapse show" id="goalCat-{{ $catKey }}">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Partner</th>
                                <th>Goal</th>
                                <th>Cadence</th>
                                <th class="text-end">Target</th>
                                <th class="text-end">Actual</th>
                                <th style="width: 150px;">Attainment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($goalsByCategory[$catKey] as $ag)
                            @php $pct = $ag->getAttainmentPercentage(); @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('partners.show', $ag->partner_id) }}" class="text-decoration-none">
                                        {{ $ag->partner->name }}
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ $ag->name }}</strong>
                                    @if($ag->notes)
                                        <br><small class="text-muted">{{ Str::limit($ag->notes, 60) }}</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-outline-secondary">{{ \App\Models\GoalTemplate::CADENCES[$ag->cadence] ?? $ag->cadence }}</span></td>
                                <td class="text-end">{{ $ag->formatted_target }}</td>
                                <td class="text-end">{{ $ag->formatted_actual }}</td>
                                <td>
                                    <div class="progress" style="height: 16px;">
                                        <div class="progress-bar bg-{{ $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') }}"
                                             style="width: {{ min($pct, 100) }}%">
                                            {{ $pct }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ \App\Models\PartnerAssignedGoal::STATUS_COLORS[$ag->status] ?? 'secondary' }}">
                                        {{ \App\Models\PartnerAssignedGoal::STATUSES[$ag->status] ?? $ag->status }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editGoal-{{ $ag->id }}" title="Edit">
                                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit-2"></use></svg>
                                    </button>
                                    <form action="{{ route('plan.goals.destroy', $ag) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this goal?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger" title="Remove">
                                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#x"></use></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Edit Goal Modal --}}
                            <div class="modal fade" id="editGoal-{{ $ag->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('plan.goals.update', $ag) }}" method="POST">
                                            @csrf @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit: {{ $ag->name }} — {{ $ag->partner->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label">Target Value</label>
                                                        <input type="number" name="target_value" class="form-control" value="{{ (int) $ag->target_value }}" step="1" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Actual Value</label>
                                                        <input type="number" name="actual_value" class="form-control" value="{{ (int) $ag->actual_value }}" step="1">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        @foreach(\App\Models\PartnerAssignedGoal::STATUSES as $sKey => $sLabel)
                                                        <option value="{{ $sKey }}" {{ $ag->status === $sKey ? 'selected' : '' }}>{{ $sLabel }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-3"><label class="form-label fs-sm">Q1 Target</label><input type="number" name="q1_target" class="form-control form-control-sm" value="{{ $ag->q1_target ? (int) $ag->q1_target : '' }}" step="1"></div>
                                                    <div class="col-3"><label class="form-label fs-sm">Q2 Target</label><input type="number" name="q2_target" class="form-control form-control-sm" value="{{ $ag->q2_target ? (int) $ag->q2_target : '' }}" step="1"></div>
                                                    <div class="col-3"><label class="form-label fs-sm">Q3 Target</label><input type="number" name="q3_target" class="form-control form-control-sm" value="{{ $ag->q3_target ? (int) $ag->q3_target : '' }}" step="1"></div>
                                                    <div class="col-3"><label class="form-label fs-sm">Q4 Target</label><input type="number" name="q4_target" class="form-control form-control-sm" value="{{ $ag->q4_target ? (int) $ag->q4_target : '' }}" step="1"></div>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-3"><label class="form-label fs-sm">Q1 Actual</label><input type="number" name="q1_actual" class="form-control form-control-sm" value="{{ $ag->q1_actual ? (int) $ag->q1_actual : '' }}" step="1"></div>
                                                    <div class="col-3"><label class="form-label fs-sm">Q2 Actual</label><input type="number" name="q2_actual" class="form-control form-control-sm" value="{{ $ag->q2_actual ? (int) $ag->q2_actual : '' }}" step="1"></div>
                                                    <div class="col-3"><label class="form-label fs-sm">Q3 Actual</label><input type="number" name="q3_actual" class="form-control form-control-sm" value="{{ $ag->q3_actual ? (int) $ag->q3_actual : '' }}" step="1"></div>
                                                    <div class="col-3"><label class="form-label fs-sm">Q4 Actual</label><input type="number" name="q4_actual" class="form-control form-control-sm" value="{{ $ag->q4_actual ? (int) $ag->q4_actual : '' }}" step="1"></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Notes</label>
                                                    <textarea name="notes" class="form-control" rows="2">{{ $ag->notes }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

{{-- Legacy Partner Goals (NNACV) --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center" role="button" data-bs-toggle="collapse" data-bs-target="#legacyGoals">
        <div class="card-title mb-0">Legacy NNACV Goals</div>
        <svg class="sa-icon" style="width:16px;height:16px"><use href="/icons/sprite.svg#chevron-down"></use></svg>
    </div>
    <div class="collapse" id="legacyGoals">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th class="text-end">Sourced Target</th>
                            <th class="text-end">Sourced Actual</th>
                            <th class="text-end">Influenced Target</th>
                            <th class="text-end">Pipeline</th>
                            <th>Attainment</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($partners as $partner)
                        @php
                            $goal = $goals->get($partner->id);
                            $won = $wonData->get($partner->id);
                            $pipeline = $pipelineData->get($partner->id, 0);
                            $sTarget = $goal ? $goal->sourced_nnacv_target : 0;
                            $iTarget = $goal ? $goal->influenced_nnacv_target : 0;
                            $sActual = $won ? $won->won : 0;
                            $totalTarget = $sTarget + $iTarget;
                            $attainment = $totalTarget > 0 ? round(($sActual / $totalTarget) * 100) : 0;
                        @endphp
                        <tr>
                            <td><a href="{{ route('partners.show', $partner->id) }}" class="text-decoration-none">{{ $partner->name }}</a></td>
                            <td class="text-end">${{ number_format($sTarget) }}</td>
                            <td class="text-end">${{ number_format($sActual) }}</td>
                            <td class="text-end">${{ number_format($iTarget) }}</td>
                            <td class="text-end">${{ number_format($pipeline) }}</td>
                            <td><span class="badge bg-{{ $attainment >= 80 ? 'success' : ($attainment >= 50 ? 'warning' : 'danger') }}">{{ $attainment }}%</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Assign Goal from Template Modal --}}
<div class="modal fade" id="assignGoalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('plan.goals.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Goal from Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Partner</label>
                        <select name="partner_id" class="form-select" required>
                            <option value="">Select partner...</option>
                            @foreach($partners as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Goal Template</label>
                        <select name="goal_template_id" class="form-select" id="templateSelect" required>
                            <option value="">Select template...</option>
                            @foreach($availableTemplates->groupBy('category') as $cat => $temps)
                                <optgroup label="{{ \App\Models\GoalTemplate::CATEGORIES[$cat] ?? $cat }}">
                                    @foreach($temps as $t)
                                    <option value="{{ $t->id }}"
                                            data-name="{{ $t->name }}"
                                            data-category="{{ $t->category }}"
                                            data-metric="{{ $t->metric_key }}"
                                            data-unit="{{ $t->unit }}"
                                            data-cadence="{{ $t->cadence }}"
                                            data-target="{{ $t->default_target }}">
                                        {{ $t->name }}
                                    </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="fiscal_year" value="{{ $fy }}">
                    <input type="hidden" name="name" id="assignName">
                    <input type="hidden" name="category" id="assignCategory">
                    <input type="hidden" name="metric_key" id="assignMetric">
                    <input type="hidden" name="unit" id="assignUnit">
                    <input type="hidden" name="cadence" id="assignCadence">
                    <div class="mb-3">
                        <label class="form-label">Target Value</label>
                        <input type="number" name="target_value" class="form-control" id="assignTarget" step="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Custom Goal Modal --}}
<div class="modal fade" id="customGoalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('plan.goals.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Custom Goal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Partner</label>
                        <select name="partner_id" class="form-select" required>
                            <option value="">Select partner...</option>
                            @foreach($partners as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="fiscal_year" value="{{ $fy }}">
                    <div class="mb-3">
                        <label class="form-label">Goal Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Close 3 Supply Chain deals" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" required>
                                @foreach(\App\Models\GoalTemplate::CATEGORIES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Metric</label>
                            <select name="metric_key" class="form-select" required>
                                @foreach(\App\Models\GoalTemplate::METRIC_KEYS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label">Unit</label>
                            <select name="unit" class="form-select">
                                @foreach(\App\Models\GoalTemplate::UNITS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Cadence</label>
                            <select name="cadence" class="form-select">
                                @foreach(\App\Models\GoalTemplate::CADENCES as $key => $label)
                                <option value="{{ $key }}" {{ $key === 'quarterly' ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Target</label>
                            <input type="number" name="target_value" class="form-control" step="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($assignedGoals->isEmpty() && $goals->isEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-body text-center py-5">
        <svg class="sa-icon text-muted mb-2" style="width:48px;height:48px"><use href="/icons/sprite.svg#target"></use></svg>
        <h5 class="text-muted">No goals assigned yet</h5>
        <p class="text-muted mb-0">Use the "Assign Goal" button to assign goals from templates, or "Custom Goal" to create one from scratch.</p>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Template select auto-fill
    var sel = document.getElementById('templateSelect');
    if (sel) {
        sel.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            document.getElementById('assignName').value = opt.dataset.name || '';
            document.getElementById('assignCategory').value = opt.dataset.category || '';
            document.getElementById('assignMetric').value = opt.dataset.metric || '';
            document.getElementById('assignUnit').value = opt.dataset.unit || '';
            document.getElementById('assignCadence').value = opt.dataset.cadence || '';
            var target = opt.dataset.target;
            if (target && parseInt(target) > 0) {
                document.getElementById('assignTarget').value = parseInt(target);
            }
        });
    }

    // Revenue calculator
    var asp = {{ $historicalAsp ?: 200000 }};
    var winRate = {{ $historicalWinRate ?: 0.25 }};
    var totalPipeline = {{ collect($pipelineData)->sum() }};
    var calcTarget = document.getElementById('calc-target');
    if (calcTarget) {
        calcTarget.addEventListener('input', function() {
            var target = parseInt(this.value) || 0;
            if (target <= 0) { document.getElementById('calc-results').style.display = 'none'; return; }
            var multiple = winRate > 0 ? 1/winRate : 4;
            var deals = Math.ceil(target / asp);
            var pipeline = target * multiple;
            var oppsPerQ = Math.ceil((deals / winRate) / 4);
            var gap = pipeline - totalPipeline;
            document.getElementById('res-deals').textContent = deals;
            document.getElementById('res-pipeline').textContent = '$' + Math.round(pipeline).toLocaleString();
            document.getElementById('res-opps').textContent = oppsPerQ;
            var gapEl = document.getElementById('res-gap');
            gapEl.textContent = '$' + Math.round(gap).toLocaleString();
            gapEl.className = gap > 0 ? 'text-danger' : 'text-success';
            document.getElementById('calc-results').style.display = '';
        });
    }
});
</script>
@endsection
