@extends('layouts.vertical', ['pageTitle' => $path->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $path->name,
        'pageSubTitle1' => 'Feature Activation',
        'pageSubTitle1Url' => route('admin.activation.paths'),
        'pageSubText' => 'Manage steps for this activation path',
    ])
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Path Details --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title mb-0">Path Details</div>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge {{ $path->is_active ? 'bg-success' : 'bg-secondary' }}">
                {{ $path->is_active ? 'Active' : 'Inactive' }}
            </span>
            <span class="badge {{ $pennantActive ? 'bg-info' : 'bg-warning text-dark' }}">
                Pennant: {{ $pennantActive ? 'Unlocked' : 'Locked' }}
            </span>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.activation.paths.update', $path) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-2 mb-2">
                <div class="col-md-3">
                    <label class="form-label fs-sm">Feature Slug</label>
                    <input type="text" class="form-control form-control-sm bg-body-secondary" value="{{ $path->feature_slug }}" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-sm">Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" value="{{ $path->name }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-sm">Description</label>
                    <input type="text" name="description" class="form-control form-control-sm" value="{{ $path->description }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <div class="form-check mb-1">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $path->is_active ? 'checked' : '' }}>
                        <label class="form-check-label fs-sm">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </div>
        </form>

        <div class="d-flex gap-2 mt-2">
            <form action="{{ route('admin.activation.paths.destroy', $path) }}" method="POST" onsubmit="return confirm('Delete this activation path and all its steps?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">Delete Path</button>
            </form>
        </div>
    </div>
</div>

{{-- Overall Progress --}}
@php
    $totalRequired = $path->steps->where('is_required', true)->count();
    $completedRequired = $path->steps->where('is_required', true)->filter(fn($s) => $s->progress?->completed)->count();
    $percent = $totalRequired > 0 ? round(($completedRequired / $totalRequired) * 100) : 0;
@endphp

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

{{-- Steps --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title mb-0">Activation Steps</div>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addStepForm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> Add Step
        </button>
    </div>

    {{-- Add Step Form --}}
    <div class="collapse" id="addStepForm">
        <div class="card-body border-bottom">
            <form action="{{ route('admin.activation.steps.store', $path) }}" method="POST">
                @csrf
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <label class="form-label fs-sm">Step Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Complete onboarding training" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-sm">Type</label>
                        <select name="step_type" class="form-select form-select-sm" required>
                            @foreach($stepTypes as $type)
                            <option value="{{ $type->value }}">{{ str_replace('_', ' ', ucfirst($type->value)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-sm">Target Value</label>
                        <input type="number" name="target_value" class="form-control form-control-sm" value="1" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-sm">Description</label>
                        <input type="text" name="description" class="form-control form-control-sm" placeholder="Optional details">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <div class="form-check mb-1">
                            <input type="checkbox" name="is_required" value="1" class="form-check-input" checked>
                            <label class="form-check-label fs-sm">Required</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Step List --}}
    <div class="card-body p-0">
        @forelse($path->steps as $step)
        @php
            $done = $step->progress?->completed;
            $currentVal = (float) ($step->progress?->current_value ?? 0);
            $targetVal = (float) ($step->criteria['target'] ?? 1);
            $stepPercent = $targetVal > 0 ? min(100, ($currentVal / $targetVal) * 100) : 0;
        @endphp
        <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div class="d-flex justify-content-between align-items-start mb-2">
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
                        <br>
                        <span class="badge bg-body-secondary text-body small">{{ str_replace('_', ' ', $step->step_type->value) }}</span>
                        <span class="text-muted small ms-1">Target: {{ number_format($targetVal, 0) }}</span>
                        @if($step->description)
                        <br><small class="text-muted">{{ $step->description }}</small>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-1">
                    {{-- Toggle progress --}}
                    <form action="{{ route('admin.activation.steps.toggle-progress', $step) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $done ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $done ? 'Reset step' : 'Mark complete' }}">
                            {{ $done ? 'Reset' : 'Complete' }}
                        </button>
                    </form>
                    {{-- Edit toggle --}}
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editStep-{{ $step->id }}">Edit</button>
                    {{-- Delete --}}
                    <form action="{{ route('admin.activation.steps.destroy', $step) }}" method="POST" onsubmit="return confirm('Delete this step?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>

            {{-- Progress bar --}}
            @if(!$done && $step->progress)
            <div class="ms-5">
                <div class="progress mt-1" style="height: 4px;">
                    <div class="progress-bar bg-info" style="width: {{ $stepPercent }}%"></div>
                </div>
                <span class="text-muted" style="font-size:0.7rem">
                    {{ number_format($currentVal, 0) }} / {{ number_format($targetVal, 0) }}
                </span>
            </div>
            @endif

            {{-- Inline Edit Form --}}
            <div class="collapse mt-2" id="editStep-{{ $step->id }}">
                <form action="{{ route('admin.activation.steps.update', $step) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-2">
                        <div class="col-md-1">
                            <label class="form-label fs-sm">Order</label>
                            <input type="number" name="order" class="form-control form-control-sm" value="{{ $step->order }}" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-sm">Name</label>
                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $step->name }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-sm">Type</label>
                            <select name="step_type" class="form-select form-select-sm" required>
                                @foreach($stepTypes as $type)
                                <option value="{{ $type->value }}" {{ $step->step_type === $type ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($type->value)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-sm">Target</label>
                            <input type="number" name="target_value" class="form-control form-control-sm" value="{{ $targetVal }}" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-sm">Description</label>
                            <input type="text" name="description" class="form-control form-control-sm" value="{{ $step->description }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <div class="form-check mb-1">
                                <input type="checkbox" name="is_required" value="1" class="form-check-input" {{ $step->is_required ? 'checked' : '' }}>
                                <label class="form-check-label fs-sm">Req</label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-4">
            No steps defined yet. Add steps above to define the requirements for unlocking
            <code>{{ $path->feature_slug }}</code>.
        </div>
        @endforelse
    </div>
</div>

@endsection
