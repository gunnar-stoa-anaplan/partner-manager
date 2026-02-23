@extends('layouts.vertical', ['pageTitle' => 'Activation Paths'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Activation Paths',
        'pageSubTitle1' => 'Feature Activation',
        'pageSubTitle1Url' => route('admin.activation.paths'),
        'pageSubText' => 'Define achievement-based paths to progressively unlock features',
    ])
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Paired Feature Concept --}}
<div class="alert alert-info alert-dismissible fade show small mb-g">
    <strong>Paired Features:</strong> Each capability typically has two activation paths &mdash; one for
    <em>Administration</em> (setup, configuration) and one for <em>End-User Access</em> (day-to-day usage).
    For example: "Enablement Administration" + "Field Enablement End Users".
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

{{-- Create New Path --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">Create New Activation Path</div>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#createForm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> New Path
        </button>
    </div>
    <div class="collapse" id="createForm">
        <div class="card-body">
            <form action="{{ route('admin.activation.paths.store') }}" method="POST">
                @csrf
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <label class="form-label fs-sm">Feature Slug</label>
                        <div class="input-group input-group-sm">
                            <select name="feature_slug" class="form-select form-select-sm" id="slugSelect" onchange="document.getElementById('slugCustom').value = this.value === '__custom__' ? '' : this.value; document.getElementById('customSlugRow').style.display = this.value === '__custom__' ? 'block' : 'none';">
                                <option value="">Select feature...</option>
                                @foreach($moduleSlugs as $module)
                                <option value="{{ $module['slug'] }}">{{ $module['name'] }} ({{ $module['slug'] }})</option>
                                @endforeach
                                <option value="__custom__">Custom slug...</option>
                            </select>
                        </div>
                        <div id="customSlugRow" style="display:none" class="mt-1">
                            <input type="text" id="slugCustom" class="form-control form-control-sm" placeholder="e.g. deal_registration_admin" onchange="document.querySelector('[name=feature_slug]').value = this.value">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-sm">Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Enablement Administration" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-sm">Description</label>
                        <input type="text" name="description" class="form-control form-control-sm" placeholder="Steps to unlock this feature">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <div class="form-check mb-1">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                            <label class="form-check-label fs-sm">Active</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Create</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Existing Paths --}}
@if($paths->isEmpty())
<div class="card shadow-0">
    <div class="card-body text-center py-5">
        <svg class="sa-icon mb-3" style="width:48px;height:48px;opacity:0.3">
            <use href="/icons/sprite.svg#unlock"></use>
        </svg>
        <h5>No Activation Paths</h5>
        <p class="text-muted mb-0">Create your first activation path above to define the steps required to unlock a feature.</p>
    </div>
</div>
@else
<div class="row">
    @foreach($paths as $path)
    <div class="col-lg-6 col-xl-4">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <h6 class="mb-0">
                            <a href="{{ route('admin.activation.paths.show', $path) }}" class="text-decoration-none">
                                {{ $path->name }}
                            </a>
                        </h6>
                        <code class="small text-muted">{{ $path->feature_slug }}</code>
                    </div>
                    <div class="d-flex gap-1">
                        <span class="badge {{ $path->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $path->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="badge {{ $path->pennant_active ? 'bg-info' : 'bg-warning text-dark' }}">
                            {{ $path->pennant_active ? 'Unlocked' : 'Locked' }}
                        </span>
                    </div>
                </div>

                @if($path->description)
                <p class="text-muted small mb-2">{{ $path->description }}</p>
                @endif

                <div class="d-flex justify-content-between small text-muted mb-2">
                    <span>{{ $path->steps_count }} {{ Str::plural('step', $path->steps_count) }}</span>
                </div>

                <a href="{{ route('admin.activation.paths.show', $path) }}" class="btn btn-sm btn-outline-primary w-100">
                    Manage Steps
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
