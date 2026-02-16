@extends('portal.layout', ['pageTitle' => 'Partner Path'])

@section('content')
@if(!$enrollment)
    <h2 class="fw-bold mb-1">Partner Path</h2>
    <p class="text-muted mb-4">{{ $partner->name }}</p>
    <div class="card portal-card">
        <div class="card-body text-center py-5">
            <div class="mb-3 fs-1 text-muted">&#128736;</div>
            <h5 class="text-muted">No Active Partner Path</h5>
            <p class="text-muted mb-0">Your organization has not been enrolled in a partner path yet. Contact your Anaplan Partner Success Manager for more information.</p>
        </div>
    </div>
@else
    <h2 class="fw-bold mb-1">{{ $enrollment->path->name }}</h2>
    <p class="text-muted mb-4">{{ $partner->name }} &mdash; Enrolled {{ $enrollment->enrolled_at->format('M j, Y') }}</p>

    {{-- Metric Cards --}}
    <div class="row mb-4">
        {{-- Stage Progress --}}
        <div class="col-md col-sm-6 mb-3">
            <div class="card portal-card h-100">
                <div class="card-body text-center py-4">
                    <h1 class="display-5 fw-bold mb-0" style="color: {{ $enrollment->currentStage->color }}">{{ $currentStageNumber }}<small class="text-muted fw-normal">/{{ $totalStages }}</small></h1>
                    <div class="progress mx-auto mt-2" style="height:6px; max-width:120px;">
                        <div class="progress-bar" style="width:{{ $stageProgress }}%; background:{{ $enrollment->currentStage->color }}"></div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Stage Progress</p>
                </div>
            </div>
        </div>
        {{-- Days in Stage --}}
        <div class="col-md col-sm-6 mb-3">
            <div class="card portal-card h-100">
                <div class="card-body text-center py-4">
                    @php $daysColor = $daysInStage > 90 ? 'danger' : ($daysInStage > 60 ? 'warning' : 'info'); @endphp
                    <h1 class="display-5 fw-bold text-{{ $daysColor }} mb-0">{{ $daysInStage }}</h1>
                    <small class="text-muted">since {{ $enrollment->stage_entered_at->format('M j') }}</small>
                    <p class="text-muted mt-2 mb-0">Days in Stage</p>
                </div>
            </div>
        </div>
        {{-- Required Tasks --}}
        <div class="col-md col-sm-6 mb-3">
            <div class="card portal-card h-100">
                <div class="card-body text-center py-4">
                    @php $reqColor = $requiredCompletionPct >= 80 ? 'success' : ($requiredCompletionPct >= 50 ? 'warning' : 'danger'); @endphp
                    <h1 class="display-5 fw-bold text-{{ $reqColor }} mb-0">{{ $requiredCompleted }}<small class="text-muted fw-normal">/{{ $requiredTotal }}</small></h1>
                    <div class="progress mx-auto mt-2" style="height:6px; max-width:120px;">
                        <div class="progress-bar bg-{{ $reqColor }}" style="width:{{ $requiredCompletionPct }}%"></div>
                    </div>
                    <p class="text-muted mt-2 mb-0">Required Tasks</p>
                </div>
            </div>
        </div>
        {{-- Overdue Tasks --}}
        <div class="col-md col-sm-6 mb-3">
            <div class="card portal-card h-100">
                <div class="card-body text-center py-4">
                    @php $overdueColor = $overdueCount === 0 ? 'success' : ($overdueCount <= 2 ? 'warning' : 'danger'); @endphp
                    <h1 class="display-5 fw-bold text-{{ $overdueColor }} mb-0">{{ $overdueCount }}</h1>
                    <small class="text-muted">{{ $overdueCount === 0 ? 'on track' : 'need attention' }}</small>
                    <p class="text-muted mt-2 mb-0">Overdue Tasks</p>
                </div>
            </div>
        </div>
        {{-- Gate Readiness --}}
        <div class="col-md col-sm-6 mb-3">
            <div class="card portal-card h-100">
                <div class="card-body text-center py-4">
                    @if($nextStage)
                        @php $gateColor = $gateReadinessPct >= 100 ? 'success' : ($gateReadinessPct >= 50 ? 'warning' : 'danger'); @endphp
                        <h1 class="display-5 fw-bold text-{{ $gateColor }} mb-0">{{ $metCount }}<small class="text-muted fw-normal">/{{ $totalGates }}</small></h1>
                        <div class="progress mx-auto mt-2" style="height:6px; max-width:120px;">
                            <div class="progress-bar bg-{{ $gateColor }}" style="width:{{ $gateReadinessPct }}%"></div>
                        </div>
                    @else
                        <h1 class="display-5 fw-bold text-success mb-0">&#10003;</h1>
                        <small class="text-muted">final stage</small>
                    @endif
                    <p class="text-muted mt-2 mb-0">Gate Readiness</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Stage Progression --}}
    <div class="card portal-card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Stage Progression</h5></div>
        <div class="card-body">
            <div class="d-flex align-items-center">
                @foreach($stages as $stage)
                <div class="text-center flex-fill position-relative">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto
                        {{ $stage->id === $enrollment->current_stage_id ? 'border border-3 shadow' : '' }}"
                         style="width:48px; height:48px;
                                background-color: {{ $stage->stage_number <= $currentStageNumber ? $stage->color : '#e9ecef' }};
                                border-color: {{ $stage->color }} !important;">
                        @if($stage->stage_number < $currentStageNumber)
                            <span class="text-white" style="font-size:1.1rem;">&#10003;</span>
                        @elseif($stage->stage_number === $currentStageNumber)
                            <span class="text-white fw-bold" style="font-size:1rem;">{{ $stage->stage_number }}</span>
                        @else
                            <span class="text-muted" style="font-size:1rem;">{{ $stage->stage_number }}</span>
                        @endif
                    </div>
                    <div class="mt-1 small {{ $stage->id === $enrollment->current_stage_id ? 'fw-bold' : 'text-muted' }}">
                        {{ $stage->name }}
                    </div>
                </div>
                @if(!$loop->last)
                <div class="flex-fill" style="height:3px; background:{{ $stage->stage_number < $currentStageNumber ? $stage->color : '#e9ecef' }}; margin-top:-22px; min-width:30px;"></div>
                @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Current Stage Detail --}}
    <div class="row">
        {{-- Tasks --}}
        <div class="col-lg-8 mb-4">
            <div class="card portal-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Current Stage Tasks</h5>
                    <span class="badge" style="background:{{ $enrollment->currentStage->color }}">{{ $enrollment->currentStage->name }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($currentStageTasks as $task)
                        <div class="list-group-item {{ $task->status === 'completed' || $task->status === 'waived' ? 'bg-light' : '' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    @if($task->status === 'completed')
                                        <span class="text-success fs-5">&#10003;</span>
                                    @elseif($task->status === 'in_progress')
                                        <span class="text-primary fs-5">&#9654;</span>
                                    @elseif($task->status === 'blocked')
                                        <span class="text-danger fs-5">&#9888;</span>
                                    @elseif($task->status === 'waived')
                                        <span class="text-secondary fs-5">&#8212;</span>
                                    @else
                                        <span class="text-muted fs-5">&#9675;</span>
                                    @endif
                                    <div>
                                        <span class="{{ in_array($task->status, ['completed', 'waived']) ? 'text-decoration-line-through text-muted' : '' }}">{{ $task->title }}</span>
                                        <div>
                                            @php $lvlColors = ['required' => 'danger', 'recommended' => 'warning', 'optional' => 'secondary']; @endphp
                                            <span class="badge bg-{{ $lvlColors[$task->requirement_level] ?? 'secondary' }} bg-opacity-15 text-{{ $lvlColors[$task->requirement_level] ?? 'secondary' }}" style="font-size:0.65rem;">
                                                {{ ucfirst($task->requirement_level) }}
                                            </span>
                                            @if($task->due_date)
                                                <small class="text-{{ $task->isOverdue() ? 'danger fw-bold' : 'muted' }} ms-1">
                                                    Due {{ $task->due_date->format('M j') }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @php
                                    $statusColors = ['completed' => 'success', 'in_progress' => 'primary', 'blocked' => 'danger', 'waived' => 'secondary', 'not_started' => 'light text-dark'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }}">
                                    {{ \App\Models\PartnerTask::STATUSES[$task->status] ?? $task->status }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-3">No tasks for this stage.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Gate Requirements --}}
        <div class="col-lg-4 mb-4">
            <div class="card portal-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        @if($nextStage)
                            Gate to <span class="badge" style="background:{{ $nextStage->color }}">{{ $nextStage->name }}</span>
                        @else
                            Final Stage
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($nextStage && $gateResults->isNotEmpty())
                        <p class="text-muted small mb-3">{{ $metCount }}/{{ $totalGates }} requirements met</p>
                        @foreach($gateResults as $gate)
                        <div class="border rounded p-2 mb-2 {{ $gate['met'] ? 'border-success bg-success bg-opacity-10' : 'border-danger bg-danger bg-opacity-10' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small fw-bold">{{ $gate['requirement_label'] }}</span>
                                <span class="{{ $gate['met'] ? 'text-success' : 'text-danger' }}">{{ $gate['met'] ? '&#10003;' : '&#10007;' }}</span>
                            </div>
                            <div class="small text-muted mt-1">
                                @if($gate['unit'] === 'currency')
                                    ${{ number_format($gate['actual_value']) }} / ${{ number_format($gate['threshold']) }}
                                @elseif($gate['unit'] === 'percentage')
                                    {{ number_format($gate['actual_value'], 1) }}% / {{ $gate['threshold'] }}%
                                @else
                                    {{ $gate['actual_value'] }} / {{ $gate['threshold'] }}
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @elseif(!$nextStage)
                        <div class="text-center py-3">
                            <span class="fs-2">&#127942;</span>
                            <p class="text-muted mt-2 mb-0">You have reached the final stage of the partner path.</p>
                        </div>
                    @else
                        <p class="text-muted small mb-0">No gate requirements configured for this transition.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Stage History --}}
    @if($history->isNotEmpty())
    <div class="card portal-card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Stage History</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Date</th><th>From</th><th></th><th>To</th><th>Notes</th></tr></thead>
                    <tbody>
                    @foreach($history as $h)
                        <tr>
                            <td class="text-nowrap">{{ $h->transitioned_at->format('M j, Y') }}</td>
                            <td>
                                @if($h->fromStage)
                                    <span class="badge" style="background:{{ $h->fromStage->color }}">{{ $h->fromStage->name }}</span>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td class="text-muted">&#8594;</td>
                            <td><span class="badge" style="background:{{ $h->toStage->color }}">{{ $h->toStage->name }}</span></td>
                            <td class="text-muted small">{{ $h->notes ?? '&mdash;' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
@endif
@endsection
