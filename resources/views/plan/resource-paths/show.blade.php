@extends('layouts.vertical', ['pageTitle' => $enrollment->contact->full_name . ' — Resource Path'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $enrollment->contact->full_name,
        'pageSubTitle1' => 'Resource Paths',
        'pageSubTitle1Url' => route('plan.resource-paths.index'),
        'pageSubTitle2' => $enrollment->path->name,
    ])
@endsection

@section('content')

@php
    $stages = $enrollment->path->stages->sortBy('sort_order')->values();
    $currentStageIdx = $stages->search(fn($s) => $s->id === $enrollment->current_stage_id);
    $progressPct = $stages->count() > 1 ? round(($currentStageIdx / ($stages->count() - 1)) * 100) : 100;
@endphp

{{-- Resource Info + Stage Progress --}}
<div class="row mb-g">
    <div class="col-xl-4">
        <div class="card shadow-0 h-100">
            <div class="card-body">
                <h5 class="mb-1">{{ $enrollment->contact->full_name }}</h5>
                <p class="text-muted mb-2">{{ $enrollment->contact->title ?? 'No title' }}</p>
                <div class="mb-2">
                    <small class="text-muted d-block">Partner</small>
                    <span class="fw-500">{{ $enrollment->partner->name }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Role</small>
                    <span>{{ $enrollment->contact->resource_role ? \App\Models\Contact::RESOURCE_ROLES[$enrollment->contact->resource_role] ?? $enrollment->contact->resource_role : '—' }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Email</small>
                    <span>{{ $enrollment->contact->email ?? '—' }}</span>
                </div>
                <div>
                    <small class="text-muted d-block">Path</small>
                    <span class="fw-500">{{ $enrollment->path->name }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card shadow-0 h-100">
            <div class="card-header"><div class="card-title">Stage Progression</div></div>
            <div class="card-body">
                {{-- Stage indicators --}}
                <div class="d-flex align-items-center justify-content-between mb-3">
                    @foreach($stages as $idx => $stage)
                    <div class="text-center flex-fill">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-1"
                             style="width:40px;height:40px;background-color: {{ $idx <= $currentStageIdx ? $stage->color : '#e9ecef' }}; color: {{ $idx <= $currentStageIdx ? '#fff' : '#adb5bd' }}">
                            <i class="{{ $stage->icon ?? 'fal fa-circle' }}"></i>
                        </div>
                        <small class="{{ $idx === $currentStageIdx ? 'fw-bold' : 'text-muted' }}">{{ $stage->name }}</small>
                    </div>
                    @if(!$loop->last)
                    <div class="flex-fill" style="max-width: 60px;">
                        <hr class="m-0" style="border-color: {{ $idx < $currentStageIdx ? $stage->color : '#dee2e6' }}; border-width: 2px;">
                    </div>
                    @endif
                    @endforeach
                </div>

                {{-- Progress bar --}}
                <div class="progress mb-3" style="height: 6px;">
                    <div class="progress-bar" style="width: {{ $progressPct }}%; background-color: {{ $enrollment->currentStage->color }}"></div>
                </div>

                <div class="row text-center">
                    <div class="col">
                        <small class="text-muted d-block">Current Stage</small>
                        <span class="badge" style="background-color: {{ $enrollment->currentStage->color }}">{{ $enrollment->currentStage->name }}</span>
                    </div>
                    <div class="col">
                        <small class="text-muted d-block">Days in Stage</small>
                        <span class="fw-500">{{ (int) $enrollment->stage_entered_at->diffInDays(now()) }}</span>
                    </div>
                    <div class="col">
                        <small class="text-muted d-block">Enrolled</small>
                        <span>{{ $enrollment->enrolled_at->format('M j, Y') }}</span>
                    </div>
                    <div class="col">
                        <small class="text-muted d-block">Progress</small>
                        <span class="fw-500">{{ $progressPct }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Gate Requirements for Next Stage --}}
@if($nextStage)
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title">
            Gate Requirements: {{ $enrollment->currentStage->name }}
            <i class="fal fa-long-arrow-right mx-2"></i>
            <span class="badge" style="background-color: {{ $nextStage->color }}">{{ $nextStage->name }}</span>
        </div>
        @php $allMet = $gateResults->every(fn($g) => $g['met']); @endphp
        <div class="d-flex gap-2">
            <span class="badge bg-{{ $allMet ? 'success' : 'warning' }}">
                {{ $gateResults->where('met', true)->count() }}/{{ $gateResults->count() }} met
            </span>
            <form action="{{ route('plan.resource-paths.advance', $enrollment) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-{{ $allMet ? 'success' : 'outline-secondary' }}"
                        {{ $allMet ? '' : 'disabled' }}
                        onclick="return confirm('Advance {{ $enrollment->contact->full_name }} to {{ $nextStage->name }}?')">
                    <i class="fal fa-arrow-right me-1"></i>Advance
                </button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table m-0">
            <thead>
                <tr>
                    <th>Requirement</th>
                    <th class="text-center">Threshold</th>
                    <th class="text-center">Actual</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gateResults as $gate)
                <tr>
                    <td>
                        <span class="fw-500">{{ $gate['requirement_label'] }}</span>
                    </td>
                    <td class="text-center">
                        {{ $gate['unit'] === 'currency' ? '$' . number_format($gate['threshold']) : number_format($gate['threshold'], $gate['threshold'] == intval($gate['threshold']) ? 0 : 1) }}
                        <small class="text-muted ms-1">{{ $gate['unit'] !== 'count' && $gate['unit'] !== 'currency' ? $gate['unit'] : '' }}</small>
                    </td>
                    <td class="text-center fw-500">
                        {{ $gate['unit'] === 'currency' ? '$' . number_format($gate['actual_value']) : number_format($gate['actual_value'], $gate['actual_value'] == intval($gate['actual_value']) ? 0 : 1) }}
                    </td>
                    <td class="text-center">
                        @if($gate['met'])
                        <span class="badge bg-success"><i class="fal fa-check me-1"></i>Met</span>
                        @else
                        <span class="badge bg-danger"><i class="fal fa-times me-1"></i>Not Met</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="card shadow-0 mb-g">
    <div class="card-body text-center py-4">
        <i class="fal fa-trophy fa-2x text-success mb-2"></i>
        <h5 class="text-success">Final Stage Reached</h5>
        <p class="text-muted mb-0">{{ $enrollment->contact->full_name }} has reached the final stage of the {{ $enrollment->path->name }} path.</p>
    </div>
</div>
@endif

{{-- Stage History --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Stage History</div></div>
    <div class="card-body p-0">
        <table class="table m-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transition</th>
                    <th>Approved By</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $entry)
                <tr>
                    <td><small>{{ $entry->transitioned_at->format('M j, Y g:ia') }}</small></td>
                    <td>
                        @if($entry->fromStage)
                        <span class="badge" style="background-color: {{ $entry->fromStage->color }}">{{ $entry->fromStage->name }}</span>
                        <i class="fal fa-long-arrow-right mx-1 text-muted"></i>
                        @endif
                        <span class="badge" style="background-color: {{ $entry->toStage->color }}">{{ $entry->toStage->name }}</span>
                    </td>
                    <td>{{ $entry->approver?->name ?? '—' }}</td>
                    <td><small class="text-muted">{{ $entry->notes }}</small></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No stage history yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
