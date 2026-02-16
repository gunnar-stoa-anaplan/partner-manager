@extends('portal.layout')

@section('title', $program->name)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('portal.launch-programs.index') }}">Launch Programs</a></li>
            <li class="breadcrumb-item active">{{ $program->name }}</li>
        </ol>
    </nav>

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

    {{-- Risk Alert --}}
    @if($program->status === 'active' && $incompleteMilestones->isNotEmpty())
        @php
            $incompleteRequired = $incompleteMilestones->where('is_required', true);
            $borderClass = $incompleteRequired->isNotEmpty() ? 'border-danger' : 'border-warning';
            $bgClass = $incompleteRequired->isNotEmpty() ? 'bg-danger' : 'bg-warning';
            $textClass = $incompleteRequired->isNotEmpty() ? 'text-danger' : 'text-dark';
        @endphp
        <div class="card portal-card {{ $borderClass }} border-opacity-50 mb-4">
            <div class="card-body py-3">
                <div class="d-flex align-items-start gap-3">
                    <span class="fs-4 {{ $textClass }}">&#9888;</span>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="{{ $textClass }}">
                                @if($incompleteRequired->isNotEmpty())
                                    {{ $incompleteRequired->count() }} required milestone{{ $incompleteRequired->count() > 1 ? 's' : '' }} incomplete
                                @else
                                    {{ $incompleteMilestones->count() }} milestone{{ $incompleteMilestones->count() > 1 ? 's' : '' }} remaining
                                @endif
                            </strong>
                            @if($daysToGa !== null)
                                <span class="badge {{ $daysToGa <= 30 ? 'bg-danger' : ($daysToGa <= 60 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                    {{ $daysToGa }} days to GA
                                </span>
                            @endif
                        </div>
                        <div class="progress mb-2" style="height: 6px;">
                            <div class="progress-bar {{ $completionPct >= 70 ? 'bg-warning' : ($completionPct >= 40 ? 'bg-info' : 'bg-danger') }}" style="width: {{ $completionPct }}%"></div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($incompleteMilestones as $ms)
                                <span class="badge {{ $ms->is_required ? 'bg-danger bg-opacity-15 text-danger' : 'bg-secondary bg-opacity-15 text-secondary' }}" style="font-size: 0.7rem;">
                                    {{ $ms->title }}{{ $ms->is_required ? ' *' : '' }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Program Header --}}
    <div class="card portal-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-1">{{ $program->name }}</h4>
                    <p class="text-muted mb-2">{{ $program->product?->name ?? '' }}</p>
                </div>
                <span class="badge" style="background-color: {{ \App\Models\LaunchProgram::STATUS_COLORS[$program->status] ?? '#868e96' }}">
                    {{ \App\Models\LaunchProgram::STATUSES[$program->status] ?? $program->status }}
                </span>
            </div>
            @if($program->description)
                <p class="mb-2">{{ $program->description }}</p>
            @endif
            <div class="d-flex gap-4 text-muted small">
                @if($program->starts_at)
                    <span>Start: {{ $program->starts_at->format('M j, Y') }}</span>
                @endif
                @if($program->target_ga_date)
                    <span>GA: {{ $program->target_ga_date->format('M j, Y') }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        {{-- My Progress --}}
        <div class="col-lg-8 mb-4">
            <div class="card portal-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">My Milestones</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($milestones as $milestone)
                            @php $isCompleted = in_array($milestone->id, $completedIds); @endphp
                            <div class="list-group-item {{ $isCompleted ? 'bg-light' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($isCompleted)
                                            <span class="text-success fs-5">&#10003;</span>
                                        @else
                                            <span class="text-muted fs-5">&#9675;</span>
                                        @endif
                                        <div>
                                            <span class="{{ $isCompleted ? 'text-decoration-line-through text-muted' : 'fw-bold' }}">{{ $milestone->title }}</span>
                                            <div>
                                                <span class="badge" style="background-color: {{ \App\Models\LaunchProgramMilestone::TYPE_COLORS[$milestone->type] ?? '#868e96' }}; font-size: 0.65rem;">
                                                    {{ \App\Models\LaunchProgramMilestone::TYPES[$milestone->type] ?? $milestone->type }}
                                                </span>
                                                @if($milestone->is_required)
                                                    <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size: 0.65rem;">Required</span>
                                                @endif
                                                @if($milestone->due_date)
                                                    <small class="text-muted ms-1">Due {{ $milestone->due_date->format('M j') }}</small>
                                                @endif
                                            </div>
                                            @if($milestone->description)
                                                <small class="text-muted">{{ $milestone->description }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    @if(!$isCompleted && $participant)
                                        <form action="{{ route('portal.launch-programs.complete-milestone', [$enrollment, $milestone]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">Complete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Team & Feedback --}}
        <div class="col-lg-4 mb-4">
            {{-- Team --}}
            <div class="card portal-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Team</h5>
                    <small class="text-muted">{{ $enrollment->participants->count() }} member{{ $enrollment->participants->count() !== 1 ? 's' : '' }}</small>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($enrollment->participants as $p)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span>{{ $p->contact->full_name }}</span>
                                    @if($p->contact_id === $contact->id)
                                        <small class="text-muted">(you)</small>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge" style="background-color: {{ \App\Models\LaunchProgramParticipant::ROLE_COLORS[$p->role] ?? '#868e96' }}; font-size: 0.65rem;">
                                        {{ \App\Models\LaunchProgramParticipant::ROLES[$p->role] ?? $p->role }}
                                    </span>
                                    @if($p->contact_id !== $contact->id)
                                        <form action="{{ route('portal.launch-programs.remove-participant', [$enrollment, $p]) }}" method="POST" onsubmit="return confirm('Remove {{ $p->contact->full_name }} from the team?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Remove">&#10005;</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @if($availableContacts->isNotEmpty())
                    <div class="card-footer">
                        <button class="btn btn-sm btn-outline-primary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#addTeamMember">
                            + Add Team Member
                        </button>
                        <div class="collapse mt-2" id="addTeamMember">
                            <form action="{{ route('portal.launch-programs.add-participant', $enrollment) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <select class="form-select form-select-sm" name="contact_id" required>
                                        <option value="">Select colleague...</option>
                                        @foreach($availableContacts as $ac)
                                            <option value="{{ $ac->id }}">{{ $ac->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <select class="form-select form-select-sm" name="role">
                                        @foreach(\App\Models\LaunchProgramParticipant::ROLES as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary w-100">Add</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
