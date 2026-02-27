@extends('layouts.vertical', ['pageTitle' => 'Plan Review'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Plan Review', 'pageSubTitle1' => 'Strategy', 'pageSubTitle1Url' => route('strategy.dashboard'), 'pageSubTitle2' => $review->plan->name, 'pageSubTitle2Url' => route('strategy.plans.show', $review->plan)])
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Review Header --}}
<div class="card shadow-0 mb-g mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4 class="fw-500 m-0">
                    <span class="badge bg-{{ \App\Models\StrategyPlanReview::REVIEW_TYPE_COLORS[$review->review_type] ?? 'secondary' }} me-2">{{ \App\Models\StrategyPlanReview::REVIEW_TYPES[$review->review_type] }}</span>
                    Review — {{ $review->review_date->format('F d, Y') }}
                </h4>
                <div class="mt-1">
                    <small class="text-muted">Plan: <a href="{{ route('strategy.plans.show', $review->plan) }}">{{ $review->plan->name }}</a></small>
                    <small class="text-muted ms-2">Reviewer: {{ $review->reviewer->name ?? 'Unknown' }}</small>
                    <span class="badge bg-{{ \App\Models\StrategyPlanReview::STATUS_COLORS[$review->status] }} ms-2">{{ \App\Models\StrategyPlanReview::STATUSES[$review->status] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@if($review->isCompleted())
    {{-- Completed Review --}}
    @if($review->summary)
    <div class="card shadow-0 mb-g">
        <div class="card-header"><div class="card-title mb-0">Executive Summary</div></div>
        <div class="card-body">
            <p class="mb-0">{{ $review->summary }}</p>
        </div>
    </div>
    @endif

    @if($review->findings && is_array($review->findings))
    <div class="card shadow-0 mb-g">
        <div class="card-header"><div class="card-title mb-0">Findings</div></div>
        <div class="card-body">
            <div class="row">
                @foreach($review->findings as $finding)
                <div class="col-lg-6 mb-3">
                    <div class="p-3 rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>{{ $finding['category'] ?? 'General' }}</strong>
                            @if(isset($finding['status']))
                                @php
                                    $fColor = match($finding['status']) {
                                        'on_track' => 'success',
                                        'at_risk' => 'warning',
                                        'behind' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $fColor }}">{{ str_replace('_', ' ', ucfirst($finding['status'])) }}</span>
                            @endif
                        </div>
                        <p class="mb-0 small">{{ $finding['notes'] ?? '—' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
@else
    {{-- Scheduled Review — Complete Form --}}
    <div class="card shadow-0 mb-g">
        <div class="card-header"><div class="card-title mb-0">Complete Review</div></div>
        <div class="card-body">
            <form method="POST" action="{{ route('strategy.reviews.complete', $review) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-500">Executive Summary</label>
                    <textarea name="summary" class="form-control" rows="4" required placeholder="Summarize the key findings and decisions from this review..."></textarea>
                </div>

                <h6 class="fw-500 mb-2">Findings by Category</h6>
                @php $categories = ['Revenue', 'Pipeline', 'Growth', 'Enablement', 'Certification', 'Delivery', 'Budget']; @endphp
                @foreach($categories as $i => $cat)
                <div class="row mb-2">
                    <div class="col-3">
                        <input type="hidden" name="findings[{{ $i }}][category]" value="{{ $cat }}">
                        <label class="form-label fw-500 small">{{ $cat }}</label>
                    </div>
                    <div class="col-3">
                        <select name="findings[{{ $i }}][status]" class="form-select form-select-sm">
                            <option value="on_track">On Track</option>
                            <option value="at_risk">At Risk</option>
                            <option value="behind">Behind</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <input type="text" name="findings[{{ $i }}][notes]" class="form-control form-control-sm" placeholder="Notes...">
                    </div>
                </div>
                @endforeach

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Complete Review</button>
                    <a href="{{ route('strategy.plans.show', $review->plan) }}" class="btn btn-light ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- Plan Snapshot at Review Time --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title mb-0">Plan Targets at Review</div></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Metric</th>
                    <th class="text-end">Target</th>
                    <th class="text-end">Actual</th>
                    <th class="text-end">Attainment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($review->plan->targets as $target)
                @php $pct = $target->getAttainmentPercentage(); @endphp
                <tr>
                    <td><span class="badge bg-{{ \App\Models\StrategyPlanTarget::CATEGORY_COLORS[$target->category] ?? 'secondary' }}">{{ \App\Models\StrategyPlanTarget::CATEGORIES[$target->category] ?? $target->category }}</span></td>
                    <td>{{ $target->metric_label }}</td>
                    <td class="text-end">{{ $target->formatted_target }}</td>
                    <td class="text-end">{{ $target->actual_value !== null ? $target->formatted_actual : '—' }}</td>
                    <td class="text-end">
                        @if($target->actual_value !== null)
                            <span class="text-{{ $pct >= 70 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') }}">{{ $pct }}%</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
