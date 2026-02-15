@extends('portal.layout')

@section('title', 'Workshop Labs')

@section('content')
<div class="container py-4">
    <h4 class="mb-1">Workshop Labs</h4>
    <p class="text-muted mb-4">Validate your Anaplan model configuration against checkpoints defined by your instructor.</p>

    @if($labs->isEmpty())
        <div class="text-center text-muted py-5">
            <p>No labs are currently available for your enrolled sessions.</p>
        </div>
    @else
        <div class="row">
            @foreach($labs as $lab)
                @php
                    $participant = $participantMap[$lab->id] ?? null;
                    $pct = $participant ? $participant->completionPercentage() : 0;
                    $checkpointCount = $lab->checkpoints->count();
                @endphp
                <div class="col-md-6 mb-4">
                    <div class="card portal-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">{{ $lab->name }}</h5>
                                @if($participant)
                                    <span class="badge" style="background-color: {{ \App\Models\LabParticipant::STATUS_COLORS[$participant->status] ?? '#868e96' }}">
                                        {{ \App\Models\LabParticipant::STATUSES[$participant->status] ?? $participant->status }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Not enrolled</span>
                                @endif
                            </div>

                            @if($lab->session)
                                <p class="text-muted small mb-2">{{ $lab->session->display_title }} — {{ $lab->session->start_at->format('M j, Y') }}</p>
                            @endif

                            @if($lab->description)
                                <p class="small mb-3">{{ Str::limit($lab->description, 120) }}</p>
                            @endif

                            @if($participant)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="fw-bold">Progress</small>
                                        <small class="fw-bold">{{ $pct }}%</small>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between text-muted small mb-3">
                                    <span>{{ $checkpointCount }} checkpoints</span>
                                    <span>{{ $participant->total_points_earned }}/{{ $participant->total_points_possible }} points</span>
                                </div>

                                <a href="{{ route('portal.labs.show', $lab) }}" class="btn btn-sm btn-outline-primary w-100">View Checkpoints</a>
                            @else
                                <div class="d-flex justify-content-between text-muted small mb-3">
                                    <span>{{ $checkpointCount }} checkpoints</span>
                                    @if($lab->duration_minutes)
                                        <span>{{ $lab->duration_minutes }} min</span>
                                    @endif
                                </div>
                                <p class="text-muted small mb-0">Ask your instructor to enroll you in this lab.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
