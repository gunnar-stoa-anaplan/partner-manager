@extends('layouts.vertical', ['pageTitle' => $path->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $path->name,
        'pageSubTitle1' => 'My Training',
        'pageSubTitle1Url' => route('training.index'),
        'pageSubText' => $path->description ?? 'Complete these steps to unlock this feature',
    ])
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Overall Progress --}}
<div class="card shadow-0 mb-g">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold">Overall Progress</span>
            <span class="badge {{ $percent === 100 ? 'bg-success' : 'bg-warning text-dark' }}">
                {{ $percent }}% Complete
            </span>
        </div>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar {{ $percent === 100 ? 'bg-success' : 'bg-warning' }}" style="width: {{ $percent }}%"></div>
        </div>
        <div class="d-flex justify-content-between small text-muted mt-1">
            <span>{{ $completedRequired }}/{{ $totalRequired }} required steps complete</span>
            <span>{{ $path->steps->count() }} total steps</span>
        </div>
    </div>
</div>

{{-- Phase Timeline --}}
@if($path->steps->whereNotNull('phase')->isNotEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
            @foreach(['tell' => 'Tell', 'show' => 'Show', 'do' => 'Do'] as $phaseKey => $phaseLabel)
            @php
                $pc = $phaseCompletion[$phaseKey];
                $phaseComplete = $pc['all_complete'];
                $isLocked = false;
                if ($phaseKey === 'show') { $isLocked = !$phaseCompletion['tell']['all_complete']; }
                if ($phaseKey === 'do') { $isLocked = !$phaseCompletion['show']['all_complete']; }
            @endphp
            <div class="text-center flex-fill">
                <div class="rounded-pill px-3 py-2 {{ $phaseComplete ? 'bg-success text-white' : ($isLocked ? 'bg-body-secondary text-muted' : 'bg-warning text-dark') }}">
                    @if($isLocked)
                    <svg class="sa-icon me-1" style="width:14px;height:14px"><use href="/icons/sprite.svg#lock"></use></svg>
                    @elseif($phaseComplete)
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="white" class="me-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    @endif
                    <strong>{{ $phaseLabel }}</strong>
                    @if($pc['total'] > 0)
                    <span class="ms-1">({{ $pc['completed'] }}/{{ $pc['total'] }})</span>
                    @endif
                </div>
                <small class="text-muted d-block mt-1">
                    @if($phaseKey === 'tell') Learn about the feature
                    @elseif($phaseKey === 'show') See it in action
                    @else Prove your skills
                    @endif
                </small>
            </div>
            @if(!$loop->last)
            <div class="flex-shrink-0 mx-2 text-muted fs-4">&rarr;</div>
            @endif
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Steps grouped by phase --}}
@foreach(['tell' => 'Tell — Learn About the Feature', 'show' => 'Show — See It in Action', 'do' => 'Do — Prove Your Skills'] as $phaseKey => $phaseTitle)
@php
    $phaseSteps = $phasedSteps->get($phaseKey, collect());
    $isLocked = false;
    if ($phaseKey === 'show') { $isLocked = !$phaseCompletion['tell']['all_complete']; }
    if ($phaseKey === 'do') { $isLocked = !$phaseCompletion['show']['all_complete']; }
@endphp
@if($phaseSteps->isNotEmpty())
<div class="card shadow-0 mb-g {{ $isLocked ? 'opacity-50' : '' }}">
    <div class="card-header">
        <div class="card-title mb-0">{{ $phaseTitle }}</div>
    </div>
    <div class="card-body p-0">
        @foreach($phaseSteps as $step)
        @php
            $done = $step->progressFor($user)->first()?->completed ?? false;
            $progress = $step->progressFor($user)->first();
            $targetVal = (float) ($step->criteria['target'] ?? 1);
            $currentVal = (float) ($progress?->current_value ?? 0);
        @endphp
        <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center gap-2">
                    {{-- Completion indicator --}}
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 {{ $done ? 'bg-success' : 'bg-body-secondary' }}" style="width:28px;height:28px">
                        @if($done)
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        @else
                        <span class="small text-muted fw-semibold">{{ $step->order }}</span>
                        @endif
                    </div>
                    <div>
                        <strong class="{{ $done ? 'text-decoration-line-through text-muted' : '' }}">{{ $step->name }}</strong>
                        @if(!$step->is_required)
                        <span class="badge bg-body-secondary text-muted ms-1" style="font-size:0.65rem">optional</span>
                        @endif
                        @if($step->description)
                        <br><small class="text-muted">{{ $step->description }}</small>
                        @endif
                        @if(!$done && $progress && $targetVal > 1)
                        <br><small class="text-muted">Progress: {{ number_format($currentVal, 0) }} / {{ number_format($targetVal, 0) }}</small>
                        @endif
                    </div>
                </div>
                <div>
                    @if($isLocked)
                        <span class="badge bg-body-secondary text-muted">Locked</span>
                    @elseif($done)
                        <span class="badge bg-success">Done</span>
                    @elseif($phaseKey === 'tell')
                        <a href="{{ route('training.step', $step) }}" class="btn btn-sm btn-outline-primary">
                            Read
                        </a>
                    @elseif($phaseKey === 'show' && $step->tour_route && $step->tour_steps)
                        <a href="{{ route($step->tour_route) }}?tour={{ $step->id }}" class="btn btn-sm btn-outline-info">
                            Start Tour
                        </a>
                    @elseif($phaseKey === 'do')
                        <span class="badge bg-info text-white">Auto-tracked</span>
                    @else
                        <a href="{{ route('training.step', $step) }}" class="btn btn-sm btn-outline-primary">
                            View
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endforeach

{{-- Non-phased steps --}}
@if($phasedSteps->has('none'))
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title mb-0">Additional Steps</div>
    </div>
    <div class="card-body p-0">
        @foreach($phasedSteps->get('none') as $step)
        @php $done = $step->progressFor($user)->first()?->completed ?? false; @endphp
        <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 {{ $done ? 'bg-success' : 'bg-body-secondary' }}" style="width:28px;height:28px">
                        @if($done)
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        @else
                        <span class="small text-muted">{{ $step->order }}</span>
                        @endif
                    </div>
                    <span class="{{ $done ? 'text-decoration-line-through text-muted' : '' }}">{{ $step->name }}</span>
                </div>
                @if($done)
                <span class="badge bg-success">Done</span>
                @else
                <a href="{{ route('training.step', $step) }}" class="btn btn-sm btn-outline-primary">View</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
