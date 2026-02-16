{{-- Active Program Enrollments --}}
@if($enrolledPrograms->isNotEmpty())
<div class="row mb-3">
    @foreach($enrolledPrograms as $enrollment)
        @php
            $prog = $enrollment->program;
            $completion = $enrollment->completionPercentage();
        @endphp
        <div class="col-lg-6 mb-3">
            <div class="card shadow-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ $prog->name }}</h6>
                    <span class="badge" style="background-color: {{ \App\Models\LaunchProgramPartner::STATUS_COLORS[$enrollment->status] ?? '#868e96' }}">
                        {{ \App\Models\LaunchProgramPartner::STATUSES[$enrollment->status] ?? $enrollment->status }}
                    </span>
                </div>
                <div class="card-body">
                    {{-- Program Info --}}
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted">{{ $prog->product?->name ?? 'No product' }}</small>
                        @if($prog->target_ga_date)
                            <small class="text-muted">GA: {{ $prog->target_ga_date->format('M j, Y') }}</small>
                        @endif
                    </div>

                    {{-- Completion Bar --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-500">Milestone Completion</small>
                            <small class="fw-500">{{ $completion }}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $completion >= 80 ? 'bg-success' : ($completion >= 40 ? 'bg-primary' : 'bg-warning') }}" style="width: {{ $completion }}%"></div>
                        </div>
                    </div>

                    {{-- Risk Alert --}}
                    @php
                        $milestones = $prog->milestones;
                        $completedIds = $enrollment->participants->flatMap(fn($p) => $p->milestoneCompletions->pluck('launch_program_milestone_id'))->unique();
                        $incompleteRequired = $milestones->where('is_required', true)->reject(fn($ms) => $completedIds->contains($ms->id));
                        $incompleteOptional = $milestones->where('is_required', false)->reject(fn($ms) => $completedIds->contains($ms->id));
                        $daysToGa = $prog->target_ga_date ? (int) now()->diffInDays($prog->target_ga_date, false) : null;
                    @endphp
                    @if($prog->status === 'active' && ($incompleteRequired->isNotEmpty() || $incompleteOptional->isNotEmpty()))
                        <div class="alert py-2 px-3 mb-3 {{ $incompleteRequired->isNotEmpty() ? 'alert-danger' : 'alert-warning' }}" style="font-size: 0.8rem;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>
                                    @if($incompleteRequired->isNotEmpty())
                                        &#9888; {{ $incompleteRequired->count() }} required milestone{{ $incompleteRequired->count() > 1 ? 's' : '' }} incomplete
                                    @else
                                        {{ $incompleteOptional->count() }} optional milestone{{ $incompleteOptional->count() > 1 ? 's' : '' }} remaining
                                    @endif
                                </span>
                                @if($daysToGa !== null)
                                    <span class="badge {{ $daysToGa <= 30 ? 'bg-danger' : ($daysToGa <= 60 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                        {{ $daysToGa }} days to GA
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Milestones --}}
                    <div class="mb-3">
                        <small class="fw-500 d-block mb-1">Milestones ({{ $completedIds->count() }}/{{ $milestones->count() }})</small>
                        @foreach($milestones as $ms)
                            <div class="d-flex align-items-center gap-1 mb-1">
                                @if($completedIds->contains($ms->id))
                                    <svg class="sa-icon sa-icon-sm text-success"><use href="/icons/sprite.svg#check-circle"></use></svg>
                                @else
                                    <svg class="sa-icon sa-icon-sm text-muted"><use href="/icons/sprite.svg#circle"></use></svg>
                                @endif
                                <small class="{{ $completedIds->contains($ms->id) ? 'text-decoration-line-through text-muted' : '' }}">{{ $ms->title }}</small>
                                @if($ms->is_required)
                                    <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size: 0.55rem;">Req</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Participants --}}
                    <small class="fw-500 d-block mb-1">Participants ({{ $enrollment->participants->count() }})</small>
                    @foreach($enrollment->participants as $participant)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small>{{ $participant->contact->full_name }}</small>
                            <span class="badge bg-opacity-15" style="font-size: 0.6rem; background-color: {{ \App\Models\LaunchProgramParticipant::ROLE_COLORS[$participant->role] ?? '#868e96' }}; color: #fff;">
                                {{ \App\Models\LaunchProgramParticipant::ROLES[$participant->role] ?? $participant->role }}
                            </span>
                        </div>
                    @endforeach

                    {{-- Add Participant --}}
                    <button class="btn btn-xs btn-outline-primary mt-2" data-bs-toggle="collapse" data-bs-target="#addParticipant-{{ $enrollment->id }}">
                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#user-plus"></use></svg> Add Participant
                    </button>
                    <div class="collapse mt-2" id="addParticipant-{{ $enrollment->id }}">
                        <form action="{{ route('launch-program-partners.participants.store', $enrollment) }}" method="POST">
                            @csrf
                            <div class="row g-2">
                                <div class="col-7">
                                    <select class="form-select form-select-sm" name="contact_id" required>
                                        <option value="">Select contact...</option>
                                        @foreach($partner->contacts as $contact)
                                            <option value="{{ $contact->id }}">{{ $contact->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <select class="form-select form-select-sm" name="role">
                                        @foreach(\App\Models\LaunchProgramParticipant::ROLES as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Add</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif

{{-- Available Programs --}}
@if($availablePrograms->isNotEmpty())
<div class="card shadow-0">
    <div class="card-header">
        <h5 class="card-title mb-0">Available Programs</h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($availablePrograms as $prog)
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="border rounded p-3 h-100 d-flex flex-column">
                        <h6 class="mb-1">{{ $prog->name }}</h6>
                        <small class="text-muted d-block mb-2">{{ $prog->product?->name ?? 'No product' }}</small>
                        @if($prog->description)
                            <small class="text-muted d-block mb-2">{{ Str::limit($prog->description, 100) }}</small>
                        @endif
                        <div class="mt-auto">
                            <form action="{{ route('partners.launch-programs.nominate', [$partner, $prog]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#send"></use></svg> Nominate
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@if($enrolledPrograms->isEmpty() && $availablePrograms->isEmpty())
<div class="text-center text-muted py-5">
    <svg class="sa-icon" style="width:48px;height:48px;opacity:0.3"><use href="/icons/sprite.svg#zap"></use></svg>
    <p class="mt-2">No launch programs available.</p>
</div>
@endif
