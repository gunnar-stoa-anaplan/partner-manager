@extends('layouts.vertical', ['pageTitle' => $scenario->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $scenario->name, 'pageSubTitle1' => 'Strategy', 'pageSubTitle1Url' => route('strategy.dashboard'), 'pageSubTitle2' => 'Scenarios', 'pageSubTitle2Url' => route('strategy.scenarios.index')])
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

{{-- Scenario Header --}}
<div class="card shadow-0 mb-g mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4 class="fw-500 m-0">{{ $scenario->name }}</h4>
                <div class="d-flex gap-2 mt-1 align-items-center">
                    <span class="badge bg-{{ \App\Models\StrategyScenario::STATUS_COLORS[$scenario->status] }}">{{ \App\Models\StrategyScenario::STATUSES[$scenario->status] }}</span>
                    <small class="text-muted">Based on: <a href="{{ route('strategy.plans.show', $scenario->basePlan) }}">{{ $scenario->basePlan->name }}</a></small>
                    <small class="text-muted">&middot; {{ $scenario->overrides->count() }} {{ Str::plural('override', $scenario->overrides->count()) }}</small>
                </div>
                @if($scenario->description)
                    <p class="text-muted mt-2 mb-0">{{ $scenario->description }}</p>
                @endif
            </div>
            @if(!$scenario->isFinalized())
            <form method="POST" action="{{ route('strategy.scenarios.finalize', $scenario) }}">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">Finalize Scenario</button>
            </form>
            @endif
        </div>
    </div>
</div>

{{-- Projected Targets --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title mb-0">Projected Targets</div></div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Metric</th>
                    <th class="text-end">Base Plan</th>
                    <th class="text-end">Scenario</th>
                    <th class="text-end">Delta</th>
                    @if(!$scenario->isFinalized())
                    <th class="text-end">Override</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($projectedTargets as $target)
                <tr class="{{ $target['has_override'] ? 'table-info' : '' }}">
                    <td><span class="badge bg-{{ \App\Models\StrategyPlanTarget::CATEGORY_COLORS[$target['category']] ?? 'secondary' }}">{{ \App\Models\StrategyPlanTarget::CATEGORIES[$target['category']] ?? $target['category'] }}</span></td>
                    <td class="fw-500">{{ $target['metric_label'] }}</td>
                    <td class="text-end">{{ $target['unit'] === 'currency' ? '$' . number_format($target['base_value']) : number_format($target['base_value'], $target['unit'] === 'percentage' ? 1 : 0) }}</td>
                    <td class="text-end fw-500">{{ $target['unit'] === 'currency' ? '$' . number_format($target['projected_value']) : number_format($target['projected_value'], $target['unit'] === 'percentage' ? 1 : 0) }}</td>
                    <td class="text-end">
                        @if($target['delta'] != 0)
                            <span class="text-{{ $target['delta'] > 0 ? 'success' : 'danger' }}">{{ $target['delta'] > 0 ? '+' : '' }}{{ $target['unit'] === 'currency' ? '$' . number_format($target['delta']) : number_format($target['delta'], 1) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    @if(!$scenario->isFinalized())
                    <td class="text-end">
                        <form method="POST" action="{{ route('strategy.scenarios.overrides.store', $scenario) }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="override_type" value="target">
                            <input type="hidden" name="reference_id" value="{{ $target['id'] }}">
                            <input type="hidden" name="original_value" value="{{ $target['base_value'] }}">
                            <div class="input-group input-group-sm" style="width:140px; display:inline-flex;">
                                <input type="number" name="override_value" class="form-control form-control-sm" step="0.01" value="{{ $target['projected_value'] }}">
                                <button type="submit" class="btn btn-outline-primary btn-sm">Set</button>
                            </div>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Projected Assumptions --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title mb-0">Projected Assumptions</div></div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>Assumption</th>
                    <th class="text-end">Base Plan</th>
                    <th class="text-end">Scenario</th>
                    @if(!$scenario->isFinalized())
                    <th class="text-end">Override</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($projectedAssumptions as $assumption)
                <tr class="{{ $assumption['has_override'] ? 'table-info' : '' }}">
                    <td class="fw-500">{{ $assumption['label'] }}</td>
                    <td class="text-end">{{ number_format($assumption['base_value'], 4) }}</td>
                    <td class="text-end fw-500">{{ number_format($assumption['projected_value'], 4) }}</td>
                    @if(!$scenario->isFinalized())
                    <td class="text-end">
                        <form method="POST" action="{{ route('strategy.scenarios.overrides.store', $scenario) }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="override_type" value="assumption">
                            <input type="hidden" name="reference_id" value="{{ $assumption['id'] }}">
                            <input type="hidden" name="original_value" value="{{ $assumption['base_value'] }}">
                            <div class="input-group input-group-sm" style="width:140px; display:inline-flex;">
                                <input type="number" name="override_value" class="form-control form-control-sm" step="0.0001" value="{{ $assumption['projected_value'] }}">
                                <button type="submit" class="btn btn-outline-primary btn-sm">Set</button>
                            </div>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Projected Budgets --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title mb-0">Projected Budget</div></div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Line Item</th>
                    <th class="text-end">Base Plan</th>
                    <th class="text-end">Scenario</th>
                    <th class="text-end">Delta</th>
                    @if(!$scenario->isFinalized())
                    <th class="text-end">Override</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($projectedBudgets as $budget)
                <tr class="{{ $budget['has_override'] ? 'table-info' : '' }}">
                    <td><span class="badge bg-{{ \App\Models\StrategyPlanBudget::CATEGORY_COLORS[$budget['category']] ?? 'secondary' }}">{{ \App\Models\StrategyPlanBudget::CATEGORIES[$budget['category']] ?? $budget['category'] }}</span></td>
                    <td class="fw-500">{{ $budget['label'] }}</td>
                    <td class="text-end">${{ number_format($budget['base_value']) }}</td>
                    <td class="text-end fw-500">${{ number_format($budget['projected_value']) }}</td>
                    <td class="text-end">
                        @if($budget['delta'] != 0)
                            <span class="text-{{ $budget['delta'] > 0 ? 'danger' : 'success' }}">{{ $budget['delta'] > 0 ? '+' : '' }}${{ number_format($budget['delta']) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    @if(!$scenario->isFinalized())
                    <td class="text-end">
                        <form method="POST" action="{{ route('strategy.scenarios.overrides.store', $scenario) }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="override_type" value="budget">
                            <input type="hidden" name="reference_id" value="{{ $budget['id'] }}">
                            <input type="hidden" name="original_value" value="{{ $budget['base_value'] }}">
                            <div class="input-group input-group-sm" style="width:140px; display:inline-flex;">
                                <input type="number" name="override_value" class="form-control form-control-sm" step="0.01" value="{{ $budget['projected_value'] }}">
                                <button type="submit" class="btn btn-outline-primary btn-sm">Set</button>
                            </div>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-500">
                    <td colspan="2">Total</td>
                    <td class="text-end">${{ number_format($projectedBudgets->sum('base_value')) }}</td>
                    <td class="text-end">${{ number_format($projectedBudgets->sum('projected_value')) }}</td>
                    <td colspan="{{ $scenario->isFinalized() ? 1 : 2 }}"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
