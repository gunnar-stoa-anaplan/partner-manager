@extends('layouts.vertical', ['pageTitle' => 'Create Strategic Plan'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Create Strategic Plan', 'pageSubTitle1' => 'Strategy', 'pageSubTitle1Url' => route('strategy.dashboard'), 'pageSubTitle2' => 'Plans', 'pageSubTitle2Url' => route('strategy.plans.index')])
@endsection

@section('content')
<div class="row mt-3">
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title mb-0">New Strategic Plan</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('strategy.plans.store') }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-500">Plan Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. FY27 Annual Partner Strategy" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Fiscal Year</label>
                            <select name="fiscal_year" class="form-select @error('fiscal_year') is-invalid @enderror" required>
                                <option value="">Select...</option>
                                @for($y = 26; $y <= 30; $y++)
                                <option value="FY{{ $y }}" {{ old('fiscal_year') === "FY{$y}" ? 'selected' : '' }}>FY{{ $y }}</option>
                                @endfor
                            </select>
                            @error('fiscal_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Period Type</label>
                            <select name="period_type" id="period_type" class="form-select @error('period_type') is-invalid @enderror" required>
                                @foreach(\App\Models\StrategyPlan::PERIOD_TYPES as $key => $label)
                                <option value="{{ $key }}" {{ old('period_type', 'annual') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('period_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6" id="quarter-field" style="display:none;">
                            <label class="form-label fw-500">Quarter</label>
                            <select name="quarter" class="form-select">
                                <option value="">Select...</option>
                                @for($q = 1; $q <= 4; $q++)
                                <option value="{{ $q }}" {{ old('quarter') == $q ? 'selected' : '' }}>Q{{ $q }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe the strategic goals and context for this planning period...">{{ old('description') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create Plan</button>
                        <a href="{{ route('strategy.plans.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-0 mb-g bg-primary-50">
            <div class="card-body">
                <h6 class="fw-500 mb-2">Plan Lifecycle</h6>
                <div class="d-flex flex-column gap-2">
                    <div><span class="badge bg-secondary me-1">Draft</span> Add targets, assumptions, budgets, and initiatives</div>
                    <div><span class="badge bg-success me-1">Committed</span> Lock the plan as your baseline. Actuals start flowing in.</div>
                    <div><span class="badge bg-dark me-1">Closed</span> Archive the plan after the fiscal period ends.</div>
                </div>
                <hr>
                <small class="text-muted">After committing, you can create new versions to adjust the plan while preserving the original baseline.</small>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('period_type').addEventListener('change', function() {
    document.getElementById('quarter-field').style.display = this.value === 'quarterly' ? '' : 'none';
});
</script>
@endsection
