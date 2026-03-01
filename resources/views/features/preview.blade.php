@extends('layouts.vertical', ['pageTitle' => $module->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle' => $module->name, 'pageSubTitle1' => 'Feature Preview'])
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card mb-g">
            <div class="card-body text-center py-5">
                <div class="width-8 height-8 bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4">
                    <svg class="sa-icon" style="width: 2rem; height: 2rem;">
                        <use href="/icons/sprite.svg#{{ $module->icon }}"></use>
                    </svg>
                </div>

                <h3 class="fw-bold mb-2">{{ $module->name }}</h3>

                @if($module->description)
                <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">{{ $module->description }}</p>
                @endif

                @if($state === 'locked')
                <span class="badge bg-warning text-dark mb-4">
                    <i class="fal fa-lock me-1"></i> Training Required
                </span>
                @elseif($state === 'nudge')
                <span class="badge bg-info mb-4">
                    <i class="fal fa-info-circle me-1"></i> Training Recommended
                </span>
                @else
                <span class="badge bg-success mb-4">
                    <i class="fal fa-check-circle me-1"></i> Unlocked
                </span>
                @endif
            </div>
        </div>

        @if($activationPath && ($state === 'locked' || $state === 'nudge'))
        <div class="card mb-g">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fal fa-route me-1"></i> Training Progress
                </h5>
            </div>
            <div class="card-body">
                @if($progress['total'] > 0)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ $progress['completed'] }} of {{ $progress['total'] }} steps completed</span>
                    <span class="fw-semibold">{{ $progress['total'] > 0 ? round(($progress['completed'] / $progress['total']) * 100) : 0 }}%</span>
                </div>
                <div class="progress mb-4" style="height: 8px;">
                    <div class="progress-bar bg-primary" style="width: {{ $progress['total'] > 0 ? ($progress['completed'] / $progress['total']) * 100 : 0 }}%"></div>
                </div>
                @endif

                <div class="list-group list-group-flush">
                    @foreach($activationPath->steps as $step)
                    <div class="list-group-item d-flex align-items-center px-0">
                        @php $stepCompleted = $step->progressFor(auth()->user())->first()?->completed; @endphp
                        <div class="width-3 height-3 rounded-circle d-flex align-items-center justify-content-center me-3 {{ $stepCompleted ? 'bg-success' : 'bg-light' }}">
                            <i class="fal {{ $stepCompleted ? 'fa-check text-white' : 'fa-circle text-muted' }} fa-sm"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="{{ $stepCompleted ? 'text-muted text-decoration-line-through' : '' }}">{{ $step->title }}</span>
                            @if($step->description)
                            <small class="d-block text-muted">{{ $step->description }}</small>
                            @endif
                        </div>
                        @if(! $step->is_required)
                        <span class="badge bg-light text-muted">Optional</span>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="text-center mt-4">
                    @if($progress['assigned'])
                    <a href="{{ route('training.path', $activationPath) }}" class="btn btn-primary">
                        <i class="fal fa-play-circle me-1"></i>
                        {{ $progress['completed'] > 0 ? 'Continue Training' : 'Start Training' }}
                    </a>
                    @else
                    <a href="{{ route('training.index', ['feature' => $module->slug]) }}" class="btn btn-primary">
                        <i class="fal fa-book-open me-1"></i> Begin Setup
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @elseif($state === 'unlocked')
        <div class="text-center">
            <a href="{{ route('welcome') }}" class="btn btn-outline-primary">
                <i class="fal fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
