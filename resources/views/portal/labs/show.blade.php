@extends('portal.layout')

@section('title', $lab->name)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('portal.labs.index') }}">Workshop Labs</a></li>
            <li class="breadcrumb-item active">{{ $lab->name }}</li>
        </ol>
    </nav>

    {{-- Lab Header --}}
    <div class="card portal-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="mb-1">{{ $lab->name }}</h4>
                    @if($lab->session)
                        <p class="text-muted mb-2">{{ $lab->session->display_title }}</p>
                    @endif
                </div>
                <span class="badge" style="background-color: {{ \App\Models\LabParticipant::STATUS_COLORS[$participant->status] ?? '#868e96' }}">
                    {{ \App\Models\LabParticipant::STATUSES[$participant->status] ?? $participant->status }}
                </span>
            </div>

            {{-- Progress Bar --}}
            @php $pct = $participant->completionPercentage(); @endphp
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <small class="fw-bold">Progress</small>
                    <small class="fw-bold">{{ $pct }}% — {{ $participant->total_points_earned }}/{{ $participant->total_points_possible }} points</small>
                </div>
                <div class="progress" style="height: 12px;">
                    <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $pct }}%" id="overall-progress"></div>
                </div>
            </div>

            @if($lab->description)
                <p class="mb-0">{{ $lab->description }}</p>
            @endif
        </div>
    </div>

    {{-- Instructions --}}
    @if($lab->instructions)
        <div class="card portal-card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Instructions</h5>
            </div>
            <div class="card-body">
                {!! nl2br(e($lab->instructions)) !!}
            </div>
        </div>
    @endif

    {{-- Assigned Model Info --}}
    @if($participant->assignedModel)
        <div class="alert alert-info">
            <i class="fal fa-cube me-1"></i>
            Your assigned model: <strong>{{ $participant->assignedModel->name }}</strong>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="fal fa-exclamation-triangle me-1"></i>
            No Anaplan model has been assigned to you yet. Please ask your instructor.
        </div>
    @endif

    {{-- Checkpoints --}}
    <div class="accordion" id="checkpointsAccordion">
        @foreach($lab->checkpoints as $checkpoint)
            @php
                $progress = $checkpointProgress[$checkpoint->id] ?? ['status' => 'pending', 'attempts' => 0, 'error_message' => null, 'hint' => null];
                $isPassed = $progress['status'] === 'passed';
                $isFailed = $progress['status'] === 'failed';
            @endphp
            <div class="card portal-card mb-3" id="checkpoint-card-{{ $checkpoint->id }}">
                <div class="card-header p-0">
                    <button class="btn btn-link text-start text-decoration-none w-100 p-3 {{ $isPassed ? 'bg-light' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#cp{{ $checkpoint->id }}">
                        <div class="d-flex align-items-center">
                            <span class="me-3 fs-5" id="checkpoint-icon-{{ $checkpoint->id }}">
                                @if($isPassed)
                                    <span class="text-success">&#10003;</span>
                                @elseif($isFailed)
                                    <span class="text-danger">&#10007;</span>
                                @else
                                    <span class="text-muted">&#9675;</span>
                                @endif
                            </span>
                            <div class="flex-grow-1">
                                <span class="{{ $isPassed ? 'text-decoration-line-through text-muted' : 'fw-bold' }}">
                                    {{ $checkpoint->sort_order }}. {{ $checkpoint->title }}
                                </span>
                                <div>
                                    <span class="badge" style="background-color: {{ \App\Models\LabCheckpoint::TYPE_COLORS[$checkpoint->checkpoint_type] ?? '#868e96' }}; font-size: 0.65rem;">
                                        {{ \App\Models\LabCheckpoint::CHECKPOINT_TYPES[$checkpoint->checkpoint_type] ?? $checkpoint->checkpoint_type }}
                                    </span>
                                    @if($checkpoint->is_required)
                                        <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size: 0.65rem;">Required</span>
                                    @endif
                                    <small class="text-muted ms-1">{{ $checkpoint->points }} pt{{ $checkpoint->points > 1 ? 's' : '' }}</small>
                                    @if($progress['attempts'] > 0)
                                        <small class="text-muted ms-1">({{ $progress['attempts'] }} attempt{{ $progress['attempts'] > 1 ? 's' : '' }})</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                </div>
                <div id="cp{{ $checkpoint->id }}" class="collapse {{ $isFailed ? 'show' : '' }}">
                    <div class="card-body">
                        @if($checkpoint->description)
                            <p class="mb-3">{{ $checkpoint->description }}</p>
                        @endif

                        {{-- Result message --}}
                        <div id="checkpoint-result-{{ $checkpoint->id }}">
                            @if($isPassed)
                                <div class="alert alert-success py-2 mb-2">
                                    <i class="fas fa-check-circle me-1"></i> Checkpoint passed!
                                </div>
                            @elseif($isFailed)
                                <div class="alert alert-danger py-2 mb-2">
                                    <i class="fas fa-times-circle me-1"></i> {{ $progress['error_message'] ?? 'Validation failed.' }}
                                </div>
                                @if($progress['hint'])
                                    <div class="alert alert-warning py-2 mb-2">
                                        <i class="fal fa-lightbulb me-1"></i> <strong>Hint:</strong> {{ $progress['hint'] }}
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- Validate Button --}}
                        @if(!$isPassed && $participant->assignedModel)
                            <button class="btn btn-sm btn-primary" onclick="validateCheckpoint({{ $checkpoint->id }}, this)" id="validate-btn-{{ $checkpoint->id }}">
                                <i class="fal fa-play me-1"></i> Validate
                            </button>
                        @elseif($isPassed)
                            <small class="text-success">No further action needed.</small>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script>
    function validateCheckpoint(checkpointId, btn) {
        btn.disabled = true;
        btn.innerHTML = '<div class="spinner-border spinner-border-sm me-1" role="status"></div> Validating...';

        const resultDiv = document.getElementById('checkpoint-result-' + checkpointId);

        fetch("{{ url('/portal/labs/checkpoints') }}/" + checkpointId + "/validate", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.passed) {
                resultDiv.innerHTML = '<div class="alert alert-success py-2 mb-2"><i class="fas fa-check-circle me-1"></i> Checkpoint passed!</div>';
                document.getElementById('checkpoint-icon-' + checkpointId).innerHTML = '<span class="text-success">&#10003;</span>';
                btn.remove();
            } else {
                let html = '<div class="alert alert-danger py-2 mb-2"><i class="fas fa-times-circle me-1"></i> ' + (data.error_message || 'Validation failed.') + '</div>';
                if (data.hint) {
                    html += '<div class="alert alert-warning py-2 mb-2"><i class="fal fa-lightbulb me-1"></i> <strong>Hint:</strong> ' + data.hint + '</div>';
                }
                resultDiv.innerHTML = html;
                document.getElementById('checkpoint-icon-' + checkpointId).innerHTML = '<span class="text-danger">&#10007;</span>';
                btn.disabled = false;
                btn.innerHTML = '<i class="fal fa-play me-1"></i> Retry';
            }

            // Update progress bar
            if (data.completion_percentage !== undefined) {
                const bar = document.getElementById('overall-progress');
                bar.style.width = data.completion_percentage + '%';
                bar.parentElement.previousElementSibling.querySelector('small:last-child').textContent = data.completion_percentage + '% — updated';
                if (data.completion_percentage >= 100) {
                    bar.classList.remove('bg-primary');
                    bar.classList.add('bg-success');
                }
            }
        })
        .catch(() => {
            resultDiv.innerHTML = '<div class="alert alert-warning py-2 mb-2"><i class="fal fa-exclamation-triangle me-1"></i> Validation request failed. Please try again.</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="fal fa-play me-1"></i> Retry';
        });
    }
</script>
@endsection
