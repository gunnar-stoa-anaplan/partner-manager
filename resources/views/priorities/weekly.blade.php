@extends('layouts.vertical', ['pageTitle' => 'Weekly Focus'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Weekly Focus',
        'pageSubTitle1' => 'Partner Management',
        'pageSubTitle2' => 'Priorities',
        'pageSubTitle2Url' => route('priorities.index'),
    ])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Manager Review Banner -->
    @if($focus->isReviewed())
        <div class="alert alert-success d-flex align-items-start">
            <svg class="sa-icon sa-icon-sm me-2 mt-1"><use href="/icons/sprite.svg#check-circle"></use></svg>
            <div>
                <strong>Reviewed by {{ $focus->reviewer?->name ?? 'Manager' }}</strong>
                <span class="text-muted ms-1">{{ $focus->reviewed_at->diffForHumans() }}</span>
                @if($focus->manager_notes)
                    <p class="mb-0 mt-1">{{ $focus->manager_notes }}</p>
                @endif
            </div>
        </div>
    @endif

    <!-- Last Week Review (collapsible) -->
    @if($lastWeek['focus'] || $lastWeek['objectives_completed']->count() > 0)
        <div class="card mb-g">
            <div class="card-header cursor-pointer" data-bs-toggle="collapse" data-bs-target="#lastWeekReview">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#clock"></use></svg>
                    Last Week's Review
                    <span class="badge bg-secondary ms-2">
                        {{ $lastWeek['objectives_completed']->count() }} completed
                    </span>
                    <svg class="sa-icon sa-icon-sm ms-auto"><use href="/icons/sprite.svg#chevron-down"></use></svg>
                </h5>
            </div>
            <div id="lastWeekReview" class="collapse {{ now()->isMonday() ? 'show' : '' }}">
                <div class="card-body">
                    @if($lastWeek['objectives_completed']->count() > 0)
                        <h6 class="fw-500 mb-2">Objectives Completed</h6>
                        @foreach($lastWeek['objectives_completed'] as $obj)
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-success me-2">Done</span>
                                <span>{{ $obj->title }}</span>
                                <small class="text-muted ms-2">{{ $obj->partner->name }}</small>
                            </div>
                        @endforeach
                    @endif

                    @if($lastWeek['items']->count() > 0)
                        <h6 class="fw-500 mb-2 mt-3">Action Items</h6>
                        @foreach($lastWeek['items'] as $item)
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-{{ \App\Models\WeeklyFocusItem::STATUS_COLORS[$item->status] ?? 'secondary' }} me-2">
                                    {{ \App\Models\WeeklyFocusItem::STATUSES[$item->status] ?? $item->status }}
                                </span>
                                <span class="{{ $item->status === 'completed' ? 'text-decoration-line-through text-muted' : '' }}">
                                    {{ $item->description }}
                                </span>
                                <small class="text-muted ms-2">{{ $item->partner?->name }}</small>
                            </div>
                        @endforeach
                    @endif

                    <!-- Carry Over Button -->
                    @php
                        $incompleteCount = $lastWeek['items']->whereIn('status', ['planned', 'in_progress'])->count();
                    @endphp
                    @if($incompleteCount > 0)
                        <form action="{{ route('priorities.carry-over') }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#corner-down-right"></use></svg>
                                Carry Over {{ $incompleteCount }} Incomplete Item{{ $incompleteCount > 1 ? 's' : '' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- This Week: Objectives + Action Items -->
        <div class="col-xl-8">
            <!-- Active Objectives -->
            <div class="row mb-2">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h4 class="fw-500 mb-0">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#target"></use></svg>
                        Active Objectives
                    </h4>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addObjectiveModal">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                        Add Objective
                    </button>
                </div>
            </div>
            <hr class="mt-1 mb-3">

            @forelse($objectives->groupBy('partner_id') as $partnerId => $partnerObjectives)
                <h6 class="text-muted mb-2">{{ $partnerObjectives->first()->partner->name }}</h6>
                @foreach($partnerObjectives as $objective)
                    <div class="card mb-2">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="fw-500">{{ $objective->title }}</span>
                                    <span class="badge bg-{{ \App\Models\PsmObjective::CATEGORY_COLORS[$objective->category] ?? 'secondary' }} ms-1">
                                        {{ \App\Models\PsmObjective::CATEGORIES[$objective->category] ?? $objective->category }}
                                    </span>
                                </div>
                                <small class="text-muted">
                                    Due {{ $objective->target_date->format('M j') }}
                                    @if($objective->isOverdue())
                                        <span class="text-danger fw-500">(Overdue)</span>
                                    @endif
                                </small>
                            </div>
                            <!-- Progress Bar -->
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">
                                    {{ \App\Models\PsmObjective::METRIC_TYPES[$objective->metric_type] ?? $objective->metric_type }}
                                </small>
                                <small class="fw-500">
                                    {{ number_format($objective->current_value) }} / {{ number_format($objective->target_value) }}
                                </small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                @php $pct = $objective->progressPercentage(); @endphp
                                <div class="progress-bar bg-{{ $pct >= 75 ? 'success' : ($pct >= 40 ? 'primary' : 'warning') }}"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">{{ number_format($pct, 0) }}% complete</small>
                                @if($objective->metric_type === 'manual')
                                    <form action="{{ route('priorities.objectives.update', $objective) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <div class="input-group input-group-sm" style="width: 140px;">
                                            <input type="number" name="current_value" value="{{ $objective->current_value }}"
                                                   class="form-control form-control-sm" step="1" min="0">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm">Update</button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @empty
                <div class="card mb-g">
                    <div class="card-body text-center text-muted p-4">
                        <p class="mb-0">No active objectives. Create one to start tracking progress.</p>
                    </div>
                </div>
            @endforelse

            <!-- Action Items -->
            <div class="row mb-2 mt-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h4 class="fw-500 mb-0">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#check-square"></use></svg>
                        Action Items
                    </h4>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addActionItemModal">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                        Add Item
                    </button>
                </div>
            </div>
            <hr class="mt-1 mb-3">

            @forelse($items->groupBy('partner_id') as $partnerId => $partnerItems)
                <h6 class="text-muted mb-2">{{ $partnerItems->first()->partner?->name ?? 'General' }}</h6>
                @foreach($partnerItems as $item)
                    <div class="d-flex align-items-center mb-2 p-2 bg-white rounded border">
                        <form action="{{ route('priorities.items.update', $item) }}" method="POST" class="me-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $item->status === 'completed' ? 'planned' : 'completed' }}">
                            <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                                @if($item->status === 'completed')
                                    <svg class="sa-icon sa-icon-sm text-success"><use href="/icons/sprite.svg#check-square"></use></svg>
                                @else
                                    <svg class="sa-icon sa-icon-sm text-muted"><use href="/icons/sprite.svg#square"></use></svg>
                                @endif
                            </button>
                        </form>
                        <span class="{{ $item->status === 'completed' ? 'text-decoration-line-through text-muted' : '' }} flex-grow-1">
                            {{ $item->description }}
                        </span>
                        <span class="badge bg-{{ \App\Models\WeeklyFocusItem::STATUS_COLORS[$item->status] ?? 'secondary' }} me-2">
                            {{ \App\Models\WeeklyFocusItem::STATUSES[$item->status] ?? $item->status }}
                        </span>
                        <form action="{{ route('priorities.items.destroy', $item) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent text-danger">
                                <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#x"></use></svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            @empty
                <div class="card mb-g">
                    <div class="card-body text-center text-muted p-4">
                        <p class="mb-0">No action items this week. Add tasks to track your activities.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Summary Sidebar -->
        <div class="col-xl-4">
            <div class="card mb-g">
                <div class="card-header">
                    <h5 class="card-title mb-0">Weekly Summary</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('priorities.summary.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="summary" class="form-control" rows="4"
                                      placeholder="This week I'm focused on...">{{ $focus->summary }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Save Summary</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Active Objectives</span>
                        <span class="fw-500">{{ $objectives->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Action Items</span>
                        <span class="fw-500">{{ $items->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Items Completed</span>
                        <span class="fw-500 text-success">{{ $items->where('status', 'completed')->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Completion Rate</span>
                        <span class="fw-500">{{ number_format($focus->completionRate(), 0) }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Objective Modal -->
    <div class="modal fade" id="addObjectiveModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('priorities.objectives.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Objective</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Partner <span class="text-danger">*</span></label>
                            <select name="partner_id" class="form-select" required>
                                <option value="">Select partner...</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required maxlength="255"
                                   placeholder="e.g., 8 IFP delivery blueprint completions">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
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
                                <input type="number" name="target_value" class="form-control" required min="1" step="1"
                                       placeholder="e.g., 8">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Target Date <span class="text-danger">*</span></label>
                                <input type="date" name="target_date" class="form-control" required
                                       min="{{ now()->addDay()->toDateString() }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Objective</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Action Item Modal -->
    <div class="modal fade" id="addActionItemModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('priorities.items.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Action Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Partner <span class="text-danger">*</span></label>
                            <select name="partner_id" class="form-select" required>
                                <option value="">Select partner...</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" required maxlength="500"
                                   placeholder="e.g., Schedule QBR with partner CEO">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Item</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
