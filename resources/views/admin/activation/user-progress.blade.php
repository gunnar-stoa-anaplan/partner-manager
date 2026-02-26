@extends('layouts.vertical', ['pageTitle' => $user->name . ' — Activation Progress'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $user->name,
        'pageSubTitle1' => 'Feature Activation',
        'pageSubTitle1Url' => route('admin.activation.paths'),
        'pageSubTitle2' => 'Activation Progression',
        'pageSubTitle2Url' => route('admin.activation.progression'),
        'pageSubText' => 'View activation progress for this user across all assigned paths',
    ])
@endsection

@section('content')

{{-- User Info --}}
<div class="card shadow-0 mb-g">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-0">{{ $user->email }} &middot; <span class="badge bg-secondary">{{ $user->role?->display_name ?? 'No Role' }}</span></p>
            </div>
        </div>
    </div>
</div>

{{-- Path Progress --}}
@if($paths->isEmpty())
    <div class="card shadow-0">
        <div class="card-body text-muted">
            This user is not assigned to any activation paths.
        </div>
    </div>
@else
    @foreach($paths as $item)
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="card-title mb-0">
                        <a href="{{ route('admin.activation.paths.show', $item['path']) }}">{{ $item['path']->name }}</a>
                    </div>
                    <small class="text-muted">{{ $item['path']->feature_slug }}</small>
                </div>
                <div>
                    @if($item['completed_at'])
                        <span class="badge bg-success">Completed {{ \Carbon\Carbon::parse($item['completed_at'])->format('M d, Y') }}</span>
                    @else
                        <span class="badge bg-warning text-dark">In Progress</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="progress flex-grow-1" style="height: 8px;">
                        <div class="progress-bar {{ $item['percent'] >= 100 ? 'bg-success' : 'bg-primary' }}"
                             style="width: {{ $item['percent'] }}%"></div>
                    </div>
                    <span class="fw-medium">{{ $item['completed_required'] }}/{{ $item['total_required'] }} steps</span>
                    <span class="text-muted">({{ $item['percent'] }}%)</span>
                </div>
            </div>
        </div>
    @endforeach
@endif

@endsection
