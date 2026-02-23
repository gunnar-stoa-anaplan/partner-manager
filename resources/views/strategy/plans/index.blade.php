@extends('layouts.vertical', ['pageTitle' => 'Strategic Plans'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Strategic Plans', 'pageSubTitle1' => 'Strategy', 'pageSubTitle1Url' => route('strategy.dashboard')])
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mt-3 mb-4">
    <p class="text-muted fst-italic m-0">Create, commit, and manage strategic plans for the partner program. Each plan captures targets, assumptions, budgets, and initiatives for a fiscal period.</p>
    <a href="{{ route('strategy.plans.create') }}" class="btn btn-primary btn-sm">
        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> New Plan
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-0 mb-g">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>Plan Name</th>
                    <th>Fiscal Year</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th>Version</th>
                    <th class="text-center">Targets</th>
                    <th class="text-center">Budget Items</th>
                    <th class="text-center">Initiatives</th>
                    <th class="text-center">Scenarios</th>
                    <th class="text-center">Reviews</th>
                    <th>Created By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                <tr>
                    <td>
                        <a href="{{ route('strategy.plans.show', $plan) }}" class="fw-500 text-decoration-none">{{ $plan->name }}</a>
                        @if($plan->description)
                            <br><small class="text-muted">{{ Str::limit($plan->description, 60) }}</small>
                        @endif
                    </td>
                    <td>{{ $plan->fiscal_year }}</td>
                    <td>{{ \App\Models\StrategyPlan::PERIOD_TYPES[$plan->period_type] ?? $plan->period_type }}</td>
                    <td><span class="badge bg-{{ \App\Models\StrategyPlan::STATUS_COLORS[$plan->status] }}">{{ \App\Models\StrategyPlan::STATUSES[$plan->status] }}</span></td>
                    <td>v{{ $plan->version }}</td>
                    <td class="text-center">{{ $plan->targets_count }}</td>
                    <td class="text-center">{{ $plan->budgets_count }}</td>
                    <td class="text-center">{{ $plan->initiatives_count }}</td>
                    <td class="text-center">{{ $plan->scenarios_count }}</td>
                    <td class="text-center">{{ $plan->reviews_count }}</td>
                    <td>{{ $plan->creator->name ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('strategy.plans.show', $plan) }}" class="btn btn-outline-secondary btn-xs">View</a>
                        @if($plan->isCommitted())
                        <a href="{{ route('strategy.plans.variance', $plan) }}" class="btn btn-outline-primary btn-xs">Variance</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center text-muted py-4">No strategic plans yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
