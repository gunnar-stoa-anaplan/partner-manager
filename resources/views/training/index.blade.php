@extends('layouts.vertical', ['pageTitle' => 'My Training'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle' => 'My Training', 'pageSubText' => 'Complete your assigned training paths to unlock features'])
@endsection

@section('content')

@if($paths->isEmpty())
<div class="card shadow-0">
    <div class="card-body text-center py-5">
        <svg class="sa-icon mb-3" style="width:48px;height:48px;opacity:0.3">
            <use href="/icons/sprite.svg#check-circle"></use>
        </svg>
        <h5>No Training Assigned</h5>
        <p class="text-muted mb-0">You have no active training paths. Once assigned, they will appear here.</p>
    </div>
</div>
@else
<div class="row">
    @foreach($paths as $item)
    @php $isComplete = $item['completed_at'] !== null; @endphp
    <div class="col-lg-6">
        <a href="{{ route('training.path', $item['path']) }}" class="text-decoration-none">
            <div class="card shadow-0 mb-g {{ $isComplete ? 'border-success' : '' }}">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <div>
                        <h6 class="mb-0 text-body">{{ $item['path']->name }}</h6>
                        @if($item['path']->description)
                        <small class="text-muted">{{ Str::limit($item['path']->description, 80) }}</small>
                        @endif
                    </div>
                    <span class="badge {{ $isComplete ? 'bg-success' : ($item['percent'] > 0 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                        {{ $isComplete ? 'Completed' : $item['percent'] . '% Complete' }}
                    </span>
                </div>
                <div class="card-body">
                    {{-- Progress bar --}}
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar {{ $isComplete ? 'bg-success' : 'bg-warning' }}"
                             style="width: {{ $item['percent'] }}%"></div>
                    </div>

                    @if($item['has_phases'])
                    {{-- Phase timeline --}}
                    <div class="d-flex align-items-center justify-content-between">
                        @foreach(['tell' => 'Tell', 'show' => 'Show', 'do' => 'Do'] as $phaseKey => $phaseLabel)
                        @php
                            $pc = $item['phase_completion'][$phaseKey];
                            $phaseComplete = $pc['all_complete'];
                            $isLocked = false;
                            if ($phaseKey === 'show') { $isLocked = !$item['phase_completion']['tell']['all_complete']; }
                            if ($phaseKey === 'do') { $isLocked = !$item['phase_completion']['show']['all_complete']; }
                        @endphp
                        <div class="text-center flex-fill">
                            <div class="rounded-pill px-3 py-1 small {{ $phaseComplete ? 'bg-success text-white' : ($isLocked ? 'bg-body-secondary text-muted' : 'bg-warning text-dark') }}">
                                @if($isLocked)
                                <svg class="sa-icon" style="width:12px;height:12px"><use href="/icons/sprite.svg#lock"></use></svg>
                                @endif
                                {{ $phaseLabel }}
                                @if($pc['total'] > 0)
                                ({{ $pc['completed'] }}/{{ $pc['total'] }})
                                @endif
                            </div>
                        </div>
                        @if(!$loop->last)
                        <div class="flex-shrink-0 mx-1 text-muted">&rarr;</div>
                        @endif
                        @endforeach
                    </div>
                    @else
                    <div class="d-flex justify-content-between small text-muted">
                        <span>{{ $item['completed_required'] }}/{{ $item['total_required'] }} required steps</span>
                        <span>{{ $item['path']->steps->count() }} total steps</span>
                    </div>
                    @endif
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>
@endif

@endsection
