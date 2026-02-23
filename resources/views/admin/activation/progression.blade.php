@extends('layouts.vertical', ['pageTitle' => 'Activation Progression'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Activation Progression', 'pageSubTitle1' => 'Feature Activation', 'pageSubText' => 'Track achievement-based progress toward feature unlocks'])
@endsection

@section('content')

    @if($paths->isEmpty())
    <div class="card shadow-0">
        <div class="card-body text-center py-5">
            <svg class="sa-icon mb-3" style="width:48px;height:48px;opacity:0.3">
                <use href="/icons/sprite.svg#unlock"></use>
            </svg>
            <h5>No Activation Paths Configured</h5>
            <p class="text-muted mb-0">
                Activation paths define the steps required to unlock each feature.
                Once configured, progress will appear here automatically.
            </p>
        </div>
    </div>
    @else
    <div class="row">
        @foreach($paths as $item)
        <div class="col-lg-6">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <div>
                        <h6 class="mb-0">{{ $item['path']->name }}</h6>
                        <code class="small text-muted">{{ $item['path']->feature_slug }}</code>
                    </div>
                    <span class="badge {{ $item['activated'] ? 'bg-success' : 'bg-warning' }}">
                        {{ $item['activated'] ? 'Unlocked' : $item['percent'] . '% Complete' }}
                    </span>
                </div>
                <div class="card-body">
                    @if($item['path']->description)
                    <p class="text-muted small mb-3">{{ $item['path']->description }}</p>
                    @endif

                    {{-- Progress bar --}}
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar {{ $item['activated'] ? 'bg-success' : 'bg-warning' }}"
                             style="width: {{ $item['percent'] }}%"></div>
                    </div>

                    {{-- Steps --}}
                    <div class="list-group list-group-flush">
                        @foreach($item['path']->steps as $step)
                        @php $done = $step->progress?->completed; @endphp
                        <div class="list-group-item px-0 d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 {{ $done ? 'bg-success' : 'bg-body-secondary' }}" style="width:24px;height:24px">
                                @if($done)
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="white">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                @else
                                <span class="small text-muted">{{ $step->order }}</span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small {{ $done ? 'text-decoration-line-through text-muted' : '' }}">
                                        {{ $step->name }}
                                    </span>
                                    <span class="badge bg-body-secondary text-body small">
                                        {{ str_replace('_', ' ', $step->step_type->value) }}
                                    </span>
                                </div>
                                @if(!$done && $step->progress)
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-info"
                                         style="width: {{ $step->progress->target_value > 0 ? min(100, ($step->progress->current_value / $step->progress->target_value) * 100) : 0 }}%"></div>
                                </div>
                                <span class="text-muted" style="font-size:0.7rem">
                                    {{ number_format($step->progress->current_value, 0) }} / {{ number_format($step->progress->target_value, 0) }}
                                </span>
                                @endif
                            </div>
                            @if(!$step->is_required)
                            <span class="badge bg-body-secondary text-muted" style="font-size:0.65rem">optional</span>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between small text-muted mt-3 pt-2 border-top">
                        <span>{{ $item['completed_required'] }}/{{ $item['total_required'] }} required steps</span>
                        <span>{{ $item['total_steps'] }} total steps</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

@endsection
