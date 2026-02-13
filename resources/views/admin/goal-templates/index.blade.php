@extends('layouts.vertical', ['pageTitle' => 'Goal Templates'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Goal Templates', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Goal Templates define the common goals that can be assigned to partners across your program. Create templates for revenue growth, enablement milestones, delivery readiness, and more — then assign them to partners with specific targets. PSMs can also create partner-specific goals from the Goals & Targets page.</p>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Create New Template --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">Create New Goal Template</div>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#createForm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> New Template
        </button>
    </div>
    <div class="collapse" id="createForm">
        <div class="card-body">
            <form action="{{ route('admin.goal-templates.store') }}" method="POST">
                @csrf
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <label class="form-label fs-sm">Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Sourced NNACV Growth" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-sm">Category</label>
                        <select name="category" class="form-select form-select-sm" required>
                            <option value="">Select category...</option>
                            @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-sm">Metric</label>
                        <select name="metric_key" class="form-select form-select-sm" required>
                            <option value="">Select metric...</option>
                            @foreach($metricKeys as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <label class="form-label fs-sm">Unit</label>
                        <select name="unit" class="form-select form-select-sm" required>
                            @foreach($units as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-sm">Cadence</label>
                        <select name="cadence" class="form-select form-select-sm" required>
                            @foreach($cadences as $key => $label)
                            <option value="{{ $key }}" {{ $key === 'quarterly' ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-sm">Default Target</label>
                        <input type="number" name="default_target" class="form-control form-control-sm" step="1" placeholder="Optional">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-sm">Description</label>
                        <input type="text" name="description" class="form-control form-control-sm" placeholder="Optional description">
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-sm">Create Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Templates by Category --}}
@php $grouped = $templates->groupBy('category'); @endphp

@foreach(\App\Models\GoalTemplate::CATEGORIES as $catKey => $catLabel)
    @if($grouped->has($catKey))
    <div class="card shadow-0 mb-g">
        <div class="card-header d-flex justify-content-between align-items-center" role="button" data-bs-toggle="collapse" data-bs-target="#cat-{{ $catKey }}">
            <div class="card-title mb-0">{{ $catLabel }}</div>
            <div>
                <span class="badge bg-secondary">{{ $grouped[$catKey]->count() }} {{ Str::plural('template', $grouped[$catKey]->count()) }}</span>
                <svg class="sa-icon ms-1" style="width:16px;height:16px;transition:transform .2s"><use href="/icons/sprite.svg#chevron-down"></use></svg>
            </div>
        </div>
        <div class="collapse show" id="cat-{{ $catKey }}">
            <div class="card-body p-0">
                @foreach($grouped[$catKey] as $template)
                <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <form action="{{ route('admin.goal-templates.update', $template) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong>{{ $template->name }}</strong>
                                <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }} ms-2">{{ $template->is_active ? 'Active' : 'Inactive' }}</span>
                                <span class="badge bg-info ms-1">{{ \App\Models\GoalTemplate::CADENCES[$template->cadence] ?? $template->cadence }}</span>
                                <span class="badge bg-outline-secondary ms-1">{{ $template->assigned_goals_count }} assigned</span>
                                @if($template->description)
                                    <br><small class="text-muted">{{ $template->description }}</small>
                                @endif
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="submit" class="btn btn-outline-primary btn-sm">Save</button>
                                <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#assignModal-{{ $template->id }}">Assign</button>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label fs-sm">Name</label>
                                <input type="text" name="name" class="form-control form-control-sm" value="{{ $template->name }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fs-sm">Category</label>
                                <select name="category" class="form-select form-select-sm" required>
                                    @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ $template->category === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fs-sm">Metric</label>
                                <select name="metric_key" class="form-select form-select-sm" required>
                                    @foreach($metricKeys as $key => $label)
                                    <option value="{{ $key }}" {{ $template->metric_key === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label fs-sm">Unit</label>
                                <select name="unit" class="form-select form-select-sm">
                                    @foreach($units as $key => $label)
                                    <option value="{{ $key }}" {{ $template->unit === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label fs-sm">Cadence</label>
                                <select name="cadence" class="form-select form-select-sm">
                                    @foreach($cadences as $key => $label)
                                    <option value="{{ $key }}" {{ $template->cadence === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label fs-sm">Default</label>
                                <input type="number" name="default_target" class="form-control form-control-sm" value="{{ $template->default_target ? (int) $template->default_target : '' }}" step="1">
                            </div>
                            <div class="col-md-1 d-flex align-items-center justify-content-center">
                                <div class="form-check mt-3">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $template->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label fs-sm">Active</label>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <form action="{{ route('admin.goal-templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Delete this template?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Assign Modal --}}
                <div class="modal fade" id="assignModal-{{ $template->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('admin.goal-templates.assign', $template) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Assign "{{ $template->name }}" to Partners</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Fiscal Year</label>
                                        <input type="text" name="fiscal_year" class="form-control" value="{{ 'FY' . substr(now()->year + 1, 2) }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Target Value</label>
                                        <input type="number" name="target_value" class="form-control" value="{{ $template->default_target ? (int) $template->default_target : '' }}" step="1" required>
                                        <small class="text-muted">Unit: {{ \App\Models\GoalTemplate::UNITS[$template->unit] ?? $template->unit }}</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Select Partners</label>
                                        <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                            @foreach(\App\Models\Partner::orderBy('name')->get() as $partner)
                                            <div class="form-check">
                                                <input type="checkbox" name="partner_ids[]" value="{{ $partner->id }}" class="form-check-input" id="assign-{{ $template->id }}-{{ $partner->id }}">
                                                <label class="form-check-label" for="assign-{{ $template->id }}-{{ $partner->id }}">{{ $partner->name }}</label>
                                            </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-sm btn-link p-0 mt-1" onclick="this.closest('.mb-3').querySelectorAll('input[type=checkbox]').forEach(c => c.checked = true)">Select All</button>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Assign Goal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
@endforeach

@if($templates->isEmpty())
<div class="card shadow-0 mb-g">
    <div class="card-body text-center py-5 text-muted">
        No goal templates configured. Create one above, or run the GoalTemplateSeeder.
    </div>
</div>
@endif

@endsection
