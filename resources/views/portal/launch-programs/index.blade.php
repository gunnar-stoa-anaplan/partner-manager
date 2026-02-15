@extends('portal.layout')

@section('title', 'Launch Programs')

@section('content')
<div class="container py-4">
    <h4 class="mb-1">Launch Programs</h4>
    <p class="text-muted mb-4">Track your participation in product launch programs, complete milestones, and provide feedback.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($enrollments->isEmpty())
        <div class="text-center text-muted py-5">
            <p>Your organization is not currently enrolled in any launch programs.</p>
        </div>
    @else
        <div class="row">
            @foreach($enrollments as $enrollment)
                @php
                    $prog = $enrollment->program;
                    $completion = $enrollment->completionPercentage();
                    $milestoneCount = $prog->milestones->count();
                    $requiredCount = $prog->milestones->where('is_required', true)->count();
                @endphp
                <div class="col-md-6 mb-4">
                    <div class="card portal-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">{{ $prog->name }}</h5>
                                <span class="badge" style="background-color: {{ \App\Models\LaunchProgram::STATUS_COLORS[$prog->status] ?? '#868e96' }}">
                                    {{ \App\Models\LaunchProgram::STATUSES[$prog->status] ?? $prog->status }}
                                </span>
                            </div>
                            <p class="text-muted small mb-3">{{ $prog->product?->name ?? '' }}</p>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="fw-bold">Progress</small>
                                    <small class="fw-bold">{{ $completion }}%</small>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar {{ $completion >= 80 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $completion }}%"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between text-muted small mb-3">
                                <span>{{ $milestoneCount }} milestones ({{ $requiredCount }} required)</span>
                                @if($prog->target_ga_date)
                                    <span>GA: {{ $prog->target_ga_date->format('M j, Y') }}</span>
                                @endif
                            </div>

                            <a href="{{ route('portal.launch-programs.show', $enrollment) }}" class="btn btn-sm btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
