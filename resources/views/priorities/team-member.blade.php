@extends('layouts.vertical', ['pageTitle' => $member->name . ' — Priorities'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $member->name,
        'pageSubTitle1' => 'Partner Management',
        'pageSubTitle2' => 'Team Overview',
        'pageSubTitle2Url' => route('priorities.team'),
    ])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- This Week -->
        <div class="col-xl-6">
            <div class="card mb-g">
                <div class="card-header bg-primary-100">
                    <h5 class="card-title mb-0">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#calendar"></use></svg>
                        This Week
                    </h5>
                </div>
                <div class="card-body">
                    @if($focus->summary)
                        <div class="alert alert-light mb-3">
                            <small class="text-muted d-block mb-1">Weekly Summary:</small>
                            {{ $focus->summary }}
                        </div>
                    @endif

                    <h6 class="fw-500 mb-2">Active Objectives</h6>
                    @forelse($objectives as $objective)
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="fw-500">{{ $objective->title }}</span>
                                    <br><small class="text-muted">{{ $objective->partner->name }}</small>
                                </div>
                                <small class="text-muted">Due {{ $objective->target_date->format('M j') }}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-1 mt-2">
                                <small class="text-muted">{{ \App\Models\PsmObjective::METRIC_TYPES[$objective->metric_type] ?? $objective->metric_type }}</small>
                                <small class="fw-500">{{ number_format($objective->current_value) }} / {{ number_format($objective->target_value) }}</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                @php $pct = $objective->progressPercentage(); @endphp
                                <div class="progress-bar bg-{{ $pct >= 75 ? 'success' : ($pct >= 40 ? 'primary' : 'warning') }}"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No active objectives.</p>
                    @endforelse

                    <h6 class="fw-500 mb-2 mt-3">Action Items</h6>
                    @forelse($items as $item)
                        <div class="d-flex align-items-center mb-1">
                            @if($item->status === 'completed')
                                <svg class="sa-icon sa-icon-sm text-success me-2"><use href="/icons/sprite.svg#check-square"></use></svg>
                            @else
                                <svg class="sa-icon sa-icon-sm text-muted me-2"><use href="/icons/sprite.svg#square"></use></svg>
                            @endif
                            <span class="{{ $item->status === 'completed' ? 'text-decoration-line-through text-muted' : '' }}">
                                {{ $item->description }}
                            </span>
                            <small class="text-muted ms-auto">{{ $item->partner?->name }}</small>
                        </div>
                    @empty
                        <p class="text-muted">No action items this week.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Last Week -->
        <div class="col-xl-6">
            <div class="card mb-g">
                <div class="card-header bg-warning-100">
                    <h5 class="card-title mb-0">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#clock"></use></svg>
                        Last Week
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-500 mb-2">Objectives Completed</h6>
                    @forelse($lastWeek['objectives_completed'] as $obj)
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2">Done</span>
                            <span>{{ $obj->title }}</span>
                            <small class="text-muted ms-auto">{{ $obj->partner->name }}</small>
                        </div>
                    @empty
                        <p class="text-muted">No objectives completed last week.</p>
                    @endforelse

                    <h6 class="fw-500 mb-2 mt-3">Action Item Results</h6>
                    @forelse($lastWeek['items'] as $item)
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-{{ \App\Models\WeeklyFocusItem::STATUS_COLORS[$item->status] ?? 'secondary' }} me-2">
                                {{ \App\Models\WeeklyFocusItem::STATUSES[$item->status] ?? $item->status }}
                            </span>
                            <span>{{ $item->description }}</span>
                        </div>
                    @empty
                        <p class="text-muted">No action items last week.</p>
                    @endforelse
                </div>
            </div>

            <!-- Manager Review -->
            <div class="card mb-g">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#edit"></use></svg>
                        Manager Review
                    </h5>
                </div>
                <div class="card-body">
                    @if($focus->isReviewed())
                        <div class="alert alert-success">
                            <small class="text-muted">Reviewed {{ $focus->reviewed_at->diffForHumans() }}</small>
                            @if($focus->manager_notes)
                                <p class="mb-0 mt-1">{{ $focus->manager_notes }}</p>
                            @endif
                        </div>
                    @endif

                    <form action="{{ route('priorities.review', $focus) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Manager Notes</label>
                            <textarea name="manager_notes" class="form-control" rows="3"
                                      placeholder="Add feedback or notes...">{{ $focus->manager_notes }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#check"></use></svg>
                            {{ $focus->isReviewed() ? 'Update Review' : 'Mark Reviewed' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Suggest Objective -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#target"></use></svg>
                        Suggest Objective
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('priorities.suggest', $member) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Partner <span class="text-danger">*</span></label>
                            <select name="partner_id" class="form-select" required>
                                <option value="">Select partner...</option>
                                @foreach($member->psmPartners as $partner)
                                    <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required maxlength="255">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    @foreach(\App\Models\PsmObjective::CATEGORIES as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Metric Type <span class="text-danger">*</span></label>
                                <select name="metric_type" class="form-select" required>
                                    @foreach(\App\Models\PsmObjective::METRIC_TYPES as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Target <span class="text-danger">*</span></label>
                                <input type="number" name="target_value" class="form-control" required min="1" step="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Target Date <span class="text-danger">*</span></label>
                                <input type="date" name="target_date" class="form-control" required
                                       min="{{ now()->addDay()->toDateString() }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#send"></use></svg>
                            Suggest Objective
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
