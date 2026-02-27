@extends('layouts.vertical', ['pageTitle' => $step->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $step->name,
        'pageSubTitle1' => 'My Training',
        'pageSubTitle1Url' => route('training.index'),
        'pageSubTitle2' => $path->name,
        'pageSubTitle2Url' => route('training.path', $path),
        'pageSubText' => $step->description ?? '',
    ])
@endsection

@section('content')

@php $done = $progress?->completed ?? false; @endphp

<div class="row">
    <div class="col-lg-8">
        {{-- Step Content --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                    @if($step->phase)
                    <span class="badge {{ $step->phase === \App\Enums\TrainingPhase::Tell ? 'bg-primary' : ($step->phase === \App\Enums\TrainingPhase::Show ? 'bg-info' : 'bg-success') }} me-2">
                        {{ ucfirst($step->phase->value) }}
                    </span>
                    @endif
                    {{ $step->name }}
                </div>
                @if($done)
                <span class="badge bg-success">Completed</span>
                @endif
            </div>
            <div class="card-body">
                @if($step->content_body)
                    <div class="training-content mb-4">
                        {!! \Illuminate\Support\Str::markdown($step->content_body) !!}
                    </div>
                @elseif($step->description)
                    <p class="text-muted">{{ $step->description }}</p>
                @else
                    <p class="text-muted">No content available for this step yet.</p>
                @endif

                {{-- Objectives --}}
                @if(!empty($step->objectives))
                <div class="mt-4 p-3 bg-body-secondary rounded">
                    <h6 class="fw-semibold mb-2">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#check-circle"></use></svg>
                        Learning Objectives
                    </h6>
                    <ul class="mb-0">
                        @foreach($step->objectives as $objective)
                        <li>{{ $objective }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Tour launch for Show steps --}}
                @if($step->phase === \App\Enums\TrainingPhase::Show && $step->tour_route && $step->tour_steps)
                <div class="mt-4 p-3 bg-info bg-opacity-10 rounded">
                    <h6 class="fw-semibold mb-2">Interactive Tour</h6>
                    <p class="text-muted small mb-2">This step includes a guided tour of the feature. Click below to start the interactive walkthrough.</p>
                    <a href="{{ route($step->tour_route) }}?tour={{ $step->id }}" class="btn btn-info">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#play"></use></svg>
                        Start Guided Tour
                    </a>
                </div>
                @endif

                {{-- Do step auto-tracking info --}}
                @if($step->phase === \App\Enums\TrainingPhase::Do && !$done)
                <div class="mt-4 p-3 bg-warning bg-opacity-10 rounded">
                    <h6 class="fw-semibold mb-2">Auto-Tracked Progress</h6>
                    <p class="text-muted small mb-2">This step tracks your actual usage of the feature. Complete the required actions and your progress will update automatically.</p>
                    @if($progress)
                    <div class="progress mb-1" style="height: 6px;">
                        @php $pct = $progress->target_value > 0 ? min(100, ($progress->current_value / $progress->target_value) * 100) : 0; @endphp
                        <div class="progress-bar bg-warning" style="width: {{ $pct }}%"></div>
                    </div>
                    <small class="text-muted">{{ number_format($progress->current_value, 0) }} / {{ number_format($progress->target_value, 0) }}</small>
                    @else
                    <small class="text-muted">Target: {{ number_format($step->criteria['target'] ?? 1, 0) }}</small>
                    @endif
                </div>
                @endif

                {{-- Mark complete button for Tell steps --}}
                @if(!$done && $step->phase === \App\Enums\TrainingPhase::Tell)
                <div class="mt-4 pt-3 border-top">
                    <form action="{{ route('training.step.complete', $step) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#check"></use></svg>
                            Mark as Read &amp; Continue
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Step Info Sidebar --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title mb-0">Step Details</div>
            </div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt class="text-muted small">Phase</dt>
                    <dd>{{ $step->phase ? ucfirst($step->phase->value) : 'None' }}</dd>

                    <dt class="text-muted small">Type</dt>
                    <dd><span class="badge bg-body-secondary text-body">{{ str_replace('_', ' ', $step->step_type->value) }}</span></dd>

                    <dt class="text-muted small">Required</dt>
                    <dd>{{ $step->is_required ? 'Yes' : 'No' }}</dd>

                    <dt class="text-muted small">Status</dt>
                    <dd>
                        @if($done)
                        <span class="badge bg-success">Completed</span>
                        <br><small class="text-muted">{{ $progress->completed_at?->diffForHumans() }}</small>
                        @else
                        <span class="badge bg-warning text-dark">In Progress</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="d-flex justify-content-between">
            <a href="{{ route('training.path', $path) }}" class="btn btn-outline-secondary btn-sm">
                &larr; Back to Path
            </a>
        </div>
    </div>
</div>

@endsection
