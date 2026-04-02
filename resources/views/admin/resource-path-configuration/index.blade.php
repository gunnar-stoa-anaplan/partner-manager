@extends('layouts.vertical', ['pageTitle' => 'Resource Path Configuration'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Resource Path Configuration', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubText' => 'Define resource progression paths with stages, gates, and task templates. Click a path name to configure its stages and progression rules.'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Resource paths define how individual partner resources (contacts) progress through capability stages — from registration through expert-level mastery. Each path is tailored to a specific role (Model Builder, Solution Architect, Sales, etc.) and contains stage-specific gate requirements and task templates.</p>

{{-- Create New Path --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Create New Resource Path</div></div>
    <div class="card-body">
        <form action="{{ route('admin.resource-paths.store') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label fs-sm">Path Name</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g., Model Builder" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fs-sm">Description</label>
                <input type="text" name="description" class="form-control form-control-sm" placeholder="Optional description">
            </div>
            <div class="col-md-2">
                <div class="form-check mt-3">
                    <input type="checkbox" name="is_default" value="1" class="form-check-input" id="isDefault">
                    <label class="form-check-label fs-sm" for="isDefault">Default Path</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Create Path</button>
            </div>
        </form>
    </div>
</div>

{{-- Existing Paths --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Resource Paths</div></div>
    <div class="card-body p-0">
        <table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>Path Name</th>
                    <th>Description</th>
                    <th class="text-center">Stages</th>
                    <th class="text-center">Gates</th>
                    <th class="text-center">Enrollments</th>
                    <th class="text-center">Default</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paths as $path)
                <tr>
                    <td><a href="{{ route('admin.resource-paths.show', $path) }}" class="fw-bold text-primary">{{ $path->name }}</a></td>
                    <td><small class="text-muted">{{ Str::limit($path->description, 60) }}</small></td>
                    <td>
                        <div class="d-flex align-items-center flex-wrap gap-1">
                            @foreach($path->stages->sortBy('stage_number') as $stage)
                                <span class="badge" style="background-color: {{ $stage->color }}">{{ $stage->name }}</span>
                                @if(!$loop->last)
                                <i class="fal fa-chevron-right text-muted" style="font-size:0.6rem"></i>
                                @endif
                            @endforeach
                        </div>
                    </td>
                    <td class="text-center">{{ $path->gates->count() }}</td>
                    <td class="text-center">{{ $path->enrollments_count ?? $path->enrollments()->count() }}</td>
                    <td class="text-center">
                        @if($path->is_default)
                        <span class="badge bg-success">Yes</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($path->is_active)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">No resource paths configured. Create one above.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
