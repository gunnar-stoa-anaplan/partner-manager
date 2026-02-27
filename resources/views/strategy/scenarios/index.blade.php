@extends('layouts.vertical', ['pageTitle' => 'Scenarios'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Scenarios', 'pageSubTitle1' => 'Strategy', 'pageSubTitle1Url' => route('strategy.dashboard')])
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mt-3 mb-4">
    <p class="text-muted fst-italic m-0">Model what-if scenarios by adjusting targets, assumptions, and budgets from a committed plan. Compare scenarios side-by-side to evaluate trade-offs before making strategic decisions.</p>
    <div class="d-flex gap-2">
        @if($scenarios->count() >= 2)
        <form method="GET" action="{{ route('strategy.scenarios.compare') }}" class="d-flex gap-1" id="compareForm">
            @foreach($scenarios->take(3) as $s)
            <label class="btn btn-outline-secondary btn-sm">
                <input type="checkbox" name="scenarios[]" value="{{ $s->id }}" class="d-none compare-check"> {{ Str::limit($s->name, 15) }}
            </label>
            @endforeach
            <button type="submit" class="btn btn-primary btn-sm">Compare</button>
        </form>
        @endif
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createScenarioModal">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> New Scenario
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    @forelse($scenarios as $scenario)
    <div class="col-lg-4">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-500 m-0">{{ $scenario->name }}</h6>
                    <span class="badge bg-{{ \App\Models\StrategyScenario::STATUS_COLORS[$scenario->status] }}">{{ \App\Models\StrategyScenario::STATUSES[$scenario->status] }}</span>
                </div>
                @if($scenario->description)
                    <p class="text-muted small mb-2">{{ Str::limit($scenario->description, 100) }}</p>
                @endif
                <div class="d-flex gap-2 small text-muted mb-3">
                    <span>Base: {{ $scenario->basePlan->name ?? '—' }}</span>
                    <span>&middot;</span>
                    <span>{{ $scenario->overrides_count }} {{ Str::plural('override', $scenario->overrides_count) }}</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('strategy.scenarios.show', $scenario) }}" class="btn btn-outline-primary btn-sm">View Details</a>
                    @if(!$scenario->isFinalized())
                    <form method="POST" action="{{ route('strategy.scenarios.finalize', $scenario) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-success btn-sm">Finalize</button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="card-footer small text-muted">
                Created by {{ $scenario->creator->name ?? 'Unknown' }} &middot; {{ $scenario->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center text-muted py-5">
            No scenarios yet. Create one from a committed plan to model what-if changes.
        </div>
    </div>
    @endforelse
</div>

{{-- Create Scenario Modal --}}
<div class="modal fade" id="createScenarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('strategy.scenarios.store') }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">New Scenario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-500">Scenario Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Aggressive Growth" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Base Plan</label>
                        <select name="base_plan_id" class="form-select" required>
                            <option value="">Select a committed plan...</option>
                            @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->fiscal_year }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe the strategic thesis for this scenario..."></textarea>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create Scenario</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.compare-check').forEach(cb => {
    cb.closest('label').addEventListener('click', function() {
        const checkbox = this.querySelector('input');
        setTimeout(() => {
            this.classList.toggle('btn-primary', checkbox.checked);
            this.classList.toggle('btn-outline-secondary', !checkbox.checked);
        }, 10);
    });
});
</script>
@endsection
