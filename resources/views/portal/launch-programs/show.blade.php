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
                <div class="card-header">
                    <h5 class="mb-0">Team</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($enrollment->participants as $p)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $p->contact->full_name }}</span>
                                <span class="badge" style="background-color: {{ \App\Models\LaunchProgramParticipant::ROLE_COLORS[$p->role] ?? '#868e96' }}; font-size: 0.65rem;">
                                    {{ \App\Models\LaunchProgramParticipant::ROLES[$p->role] ?? $p->role }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Submit Feedback --}}
            @if($participant)
            <div class="card portal-card">
                <div class="card-header">
                    <h5 class="mb-0">Submit Feedback</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('portal.launch-programs.submit-feedback', $enrollment) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <select class="form-select form-select-sm" name="type" required>
                                @foreach(\App\Models\LaunchProgramFeedback::TYPES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Rating</label>
                            <select class="form-select form-select-sm" name="rating">
                                <option value="">No rating</option>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control form-control-sm" name="body" rows="3" placeholder="Your feedback..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Submit Feedback</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Previous Feedback --}}
    @php
        $myFeedback = $enrollment->feedback->where('contact_id', $contact->id)->sortByDesc('created_at');
    @endphp
    @if($myFeedback->isNotEmpty())
    <div class="card portal-card">
        <div class="card-header">
            <h5 class="mb-0">My Previous Feedback</h5>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($myFeedback as $fb)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <span class="badge" style="background-color: {{ \App\Models\LaunchProgramFeedback::TYPE_COLORS[$fb->type] ?? '#868e96' }}; font-size: 0.65rem;">
                                {{ \App\Models\LaunchProgramFeedback::TYPES[$fb->type] ?? $fb->type }}
                            </span>
                            <small class="text-muted">{{ $fb->created_at->diffForHumans() }}</small>
                        </div>
                        @if($fb->rating)
                            <span class="text-warning small">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $fb->rating) &#9733; @else &#9734; @endif
                                @endfor
                            </span>
                        @endif
                        <p class="mb-0 mt-1 small">{{ $fb->body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
