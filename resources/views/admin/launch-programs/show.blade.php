@extends('layouts.vertical', ['pageTitle' => $program->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $program->name, 'pageSubTitle1' => 'Program Details', 'pageSubTitle1Url' => route('admin.launch-programs.index'), 'pageSubText' => $program->product?->name ?? 'No product assigned'])
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Status & Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <div>
            <span class="badge fs-nano" style="background-color: {{ \App\Models\LaunchProgram::STATUS_COLORS[$program->status] ?? '#868e96' }}">
                {{ \App\Models\LaunchProgram::STATUSES[$program->status] ?? $program->status }}
            </span>
            @if($program->owner)
                <span class="ms-2 text-muted small">Owner: <strong>{{ $program->owner->name }}</strong></span>
            @endif
            @if(!$program->isIncludingPartners())
                <span class="badge bg-secondary ms-2">No Launch Partners</span>
            @endif
            @if($program->description)
                <p class="text-muted mt-2 mb-0">{{ $program->description }}</p>
            @endif
            @if(!$program->isIncludingPartners() && $program->exclusion_reason)
                <p class="text-muted small mt-1 mb-0 fst-italic">Exclusion reason: {{ $program->exclusion_reason }}</p>
            @endif
        </div>
        <a href="{{ route('admin.launch-programs.edit', $program) }}" class="btn btn-outline-primary btn-sm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#edit"></use></svg> Edit
        </a>
    </div>

    {{-- KPI Cards --}}
    @php
        $activePartners = $program->programPartners->whereNotIn('status', ['withdrawn'])->count();
        $avgCompletion = $program->programPartners->isEmpty() ? 0 : round($program->programPartners->avg(fn($pp) => $pp->completionPercentage()), 1);
        $totalMilestones = $program->milestones->count();
        $daysToGa = $program->target_ga_date ? (int) now()->diffInDays($program->target_ga_date, false) : null;
    @endphp
    <div class="row mb-3">
        <div class="col-sm-6 col-xl-3 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ $program->isIncludingPartners() ? $activePartners : '—' }}</h3>
                    <small class="text-muted">Partners Enrolled</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ $program->isIncludingPartners() ? $avgCompletion . '%' : '—' }}</h3>
                    <small class="text-muted">Avg Completion</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ $totalMilestones }}</h3>
                    <small class="text-muted">Milestones</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center">
                    @if($daysToGa !== null)
                        <h3 class="mb-1 {{ $daysToGa < 0 ? 'text-danger' : '' }}">{{ abs($daysToGa) }}</h3>
                        <small class="text-muted">Days {{ $daysToGa < 0 ? 'Past' : 'to' }} GA</small>
                    @else
                        <h3 class="mb-1 text-muted">—</h3>
                        <small class="text-muted">No GA Date</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Milestones --}}
        <div class="col-lg-6 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Milestones</h5>
                    <button class="btn btn-xs btn-primary" data-bs-toggle="collapse" data-bs-target="#addMilestoneForm">
                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#plus"></use></svg>
                    </button>
                </div>
                <div class="card-body p-0">
                    {{-- Add Milestone Form (collapsed) --}}
                    <div class="collapse p-3 border-bottom" id="addMilestoneForm">
                        <form action="{{ route('admin.launch-programs.milestones.store', $program) }}" method="POST">
                            @csrf
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <input type="text" class="form-control form-control-sm" name="title" placeholder="Milestone title" required>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm" name="type">
                                        @foreach(\App\Models\LaunchProgramMilestone::TYPES as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" class="form-control form-control-sm" name="due_date" placeholder="Due date">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <textarea class="form-control form-control-sm" name="description" rows="1" placeholder="Description (optional)"></textarea>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="is_required" value="1" id="is_required" checked>
                                        <label class="form-check-label" for="is_required">Required</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">Add</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Milestone List --}}
                    <div class="list-group list-group-flush">
                        @forelse($program->milestones as $milestone)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-500">
                                            {{ $milestone->title }}
                                            @if($milestone->is_required)
                                                <span class="badge bg-danger bg-opacity-10 text-danger ms-1" style="font-size: 0.65rem;">Required</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size: 0.65rem;">Optional</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">
                                            <span class="badge" style="background-color: {{ \App\Models\LaunchProgramMilestone::TYPE_COLORS[$milestone->type] ?? '#868e96' }}; font-size: 0.65rem;">
                                                {{ \App\Models\LaunchProgramMilestone::TYPES[$milestone->type] ?? $milestone->type }}
                                            </span>
                                            @if($milestone->due_date)
                                                &middot; Due {{ $milestone->due_date->format('M j, Y') }}
                                            @endif
                                        </small>
                                        @if($milestone->description)
                                            <div class="text-muted small mt-1">{{ $milestone->description }}</div>
                                        @endif
                                    </div>
                                    <form action="{{ route('admin.launch-program-milestones.destroy', $milestone) }}" method="POST" onsubmit="return confirm('Remove this milestone?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Remove">
                                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#x"></use></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted py-3">
                                <small>No milestones yet. Add one above.</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Enrolled Partners --}}
        @if($program->isIncludingPartners())
        <div class="col-lg-6 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Enrolled Partners</h5>
                    @if(auth()->user()->isAdmin())
                        <button class="btn btn-xs btn-primary" data-bs-toggle="collapse" data-bs-target="#adminNominateForm">
                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#plus"></use></svg>
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    {{-- Admin Nomination Form (collapsed) --}}
                    @if(auth()->user()->isAdmin())
                    <div class="collapse p-3 border-bottom" id="adminNominateForm">
                        <form action="{{ route('admin.launch-programs.admin-nominate', $program) }}" method="POST">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <select class="form-select form-select-sm" name="partner_id" required>
                                        <option value="">Select Partner...</option>
                                        @foreach($partners as $partner)
                                            <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control form-control-sm" name="notes" placeholder="Notes (optional)">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">Nominate</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif

                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Partner</th>
                                <th>Status</th>
                                <th class="text-center">Participants</th>
                                <th>Completion</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($program->programPartners as $pp)
                                @php $completion = $pp->completionPercentage(); @endphp
                                <tr>
                                    <td class="fw-500">{{ $pp->partner?->name ?? '—' }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ \App\Models\LaunchProgramPartner::STATUS_COLORS[$pp->status] ?? '#868e96' }}; font-size: 0.7rem;">
                                            {{ \App\Models\LaunchProgramPartner::STATUSES[$pp->status] ?? $pp->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $pp->participants->count() }}</td>
                                    <td>
                                        <div class="progress" style="height: 6px; min-width: 60px;">
                                            <div class="progress-bar bg-success" style="width: {{ $completion }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $completion }}%</small>
                                    </td>
                                    <td class="text-end">
                                        @if($pp->status === 'nominated' && (auth()->user()->isAdmin() || auth()->id() === $program->owner_id))
                                            <form action="{{ route('admin.launch-program-partners.update', $pp) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="accepted">
                                                <button type="submit" class="btn btn-xs btn-outline-success" title="Accept">
                                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#check"></use></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3"><small>No partners enrolled yet.</small></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @else
        <div class="col-lg-6 mb-g">
            <div class="card shadow-0 h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Enrolled Partners</h5>
                </div>
                <div class="card-body text-center text-muted py-4">
                    <p class="mb-1">This program excludes launch partners.</p>
                    @if($program->exclusion_reason)
                        <small class="fst-italic">{{ $program->exclusion_reason }}</small>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Feedback --}}
    @php
        $allFeedback = $program->programPartners->flatMap(fn($pp) => $pp->feedback)->sortByDesc('created_at')->take(10);
    @endphp
    @if($allFeedback->isNotEmpty())
    <div class="card shadow-0 mb-g">
        <div class="card-header">
            <h5 class="card-title mb-0">Recent Feedback</h5>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($allFeedback as $fb)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="fw-500">{{ $fb->contact->full_name }}</span>
                                <span class="badge ms-1" style="background-color: {{ \App\Models\LaunchProgramFeedback::TYPE_COLORS[$fb->type] ?? '#868e96' }}; font-size: 0.65rem;">
                                    {{ \App\Models\LaunchProgramFeedback::TYPES[$fb->type] ?? $fb->type }}
                                </span>
                                @if($fb->rating)
                                    <span class="ms-1 text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $fb->rating) &#9733; @else &#9734; @endif
                                        @endfor
                                    </span>
                                @endif
                            </div>
                            <small class="text-muted">{{ $fb->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-0 mt-1 text-muted small">{{ $fb->body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Date Info --}}
    <div class="card shadow-0 mb-g">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <small class="text-muted d-block">Start Date</small>
                    <strong>{{ $program->starts_at?->format('M j, Y') ?? '—' }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Target GA</small>
                    <strong>{{ $program->target_ga_date?->format('M j, Y') ?? '—' }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">End Date</small>
                    <strong>{{ $program->ends_at?->format('M j, Y') ?? '—' }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Created By</small>
                    <strong>{{ $program->creator->name ?? '—' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Date Change History --}}
    @if($program->dateChanges->isNotEmpty())
    <div class="card shadow-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Date Change History</h5>
            <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#dateChangeHistory">
                <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#clock"></use></svg>
                {{ $program->dateChanges->count() }} {{ Str::plural('change', $program->dateChanges->count()) }}
            </button>
        </div>
        <div class="collapse" id="dateChangeHistory">
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($program->dateChanges as $dc)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="badge bg-secondary" style="font-size: 0.65rem;">
                                        {{ \App\Models\LaunchProgramDateChange::FIELD_LABELS[$dc->field] ?? $dc->field }}
                                    </span>
                                    <span class="ms-2 small">
                                        <span class="text-muted">{{ $dc->old_date?->format('M j, Y') ?? 'Not set' }}</span>
                                        <span class="mx-1">&rarr;</span>
                                        <span class="fw-500">{{ $dc->new_date?->format('M j, Y') ?? 'Cleared' }}</span>
                                    </span>
                                </div>
                                <small class="text-muted">{{ $dc->created_at->diffForHumans() }}</small>
                            </div>
                            @if($dc->reason)
                                <p class="mb-0 mt-1 text-muted small fst-italic">"{{ $dc->reason }}"</p>
                            @endif
                            <small class="text-muted">by {{ $dc->changedBy->name ?? 'Unknown' }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection
