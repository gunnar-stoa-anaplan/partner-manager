@extends('layouts.vertical', ['pageTitle' => 'Path: ' . $path->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $path->name, 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubTitle2' => 'Path Configuration', 'pageSubTitle2Url' => route('admin.paths.index')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">This is the configuration detail for a single partner path. Use the flowchart to visualize the stage progression, then manage stages, gate requirements, and task templates below. Click any stage in the flowchart to filter the tables to just that stage's items. Task templates defined here are automatically assigned to partners when they enter the corresponding stage.</p>

{{-- Path Details --}}
<div class="card shadow-0 mb-g">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="m-0">{{ $path->name }}</h4>
                <small class="text-muted">{{ $path->description ?? 'No description' }}</small>
            </div>
            <a href="{{ route('admin.paths.index') }}" class="btn btn-sm btn-outline-secondary">Back to Paths</a>
        </div>
    </div>
</div>

{{-- Path Flowchart Visualization --}}
@php
    $sortedStages = $path->stages->sortBy('sort_order')->values();
    $stagesById = $sortedStages->keyBy('id');
    $gatesByTransition = $path->gates->groupBy(fn($g) => $g->from_stage_id . '-' . $g->to_stage_id);
@endphp
@if($sortedStages->count() > 0)
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">Path Flow <small class="text-muted fw-normal ms-2">Click a stage to filter</small></div>
        <button class="btn btn-sm btn-outline-secondary d-none" id="clearFilterBtn" onclick="clearStageFilter()">
            <i class="fal fa-times me-1"></i>Clear filter
        </button>
    </div>
    <div class="card-body">
        {{-- Active filter indicator --}}
        <div class="alert alert-info py-2 px-3 d-none mb-3" id="filterIndicator">
            <i class="fal fa-filter me-1"></i>
            Showing items for stage: <strong id="filterStageName"></strong>
        </div>

        <div class="path-flow-container">
            <div class="path-flow">
                @foreach($sortedStages as $idx => $stage)
                    @php
                        $taskCount = $path->taskTemplates->where('stage_id', $stage->id)->count();
                    @endphp

                    {{-- Stage Node --}}
                    <div class="path-stage-node" data-stage-id="{{ $stage->id }}" data-stage-name="{{ $stage->name }}" onclick="toggleStageFilter({{ $stage->id }}, '{{ $stage->name }}')">
                        <div class="stage-card" style="border-color: {{ $stage->color }}" id="stage-card-{{ $stage->id }}">
                            <div class="stage-header" style="background-color: {{ $stage->color }}">
                                <i class="{{ $stage->icon ?? 'fal fa-circle' }}"></i>
                            </div>
                            <div class="stage-body">
                                <div class="stage-number">Stage {{ $stage->stage_number }}</div>
                                <div class="stage-name">{{ $stage->name }}</div>
                                <div class="stage-stats">
                                    @if($taskCount > 0)
                                    <span class="stat-badge" title="Task templates"><i class="fal fa-tasks"></i> {{ $taskCount }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Gate Connector (between stages) --}}
                    @if(!$loop->last)
                        @php
                            $nextStage = $sortedStages[$idx + 1] ?? null;
                            $gateKey = $stage->id . '-' . ($nextStage?->id ?? '');
                            $gates = $gatesByTransition[$gateKey] ?? collect();
                        @endphp
                        <div class="path-gate-connector" data-from-stage="{{ $stage->id }}" data-to-stage="{{ $nextStage?->id }}">
                            <div class="gate-line"></div>
                            <div class="gate-badge {{ $gates->isEmpty() ? 'gate-empty' : '' }}" title="{{ $gates->count() }} gate criteria">
                                @if($gates->isNotEmpty())
                                    <i class="fal fa-shield-check"></i>
                                    <span class="gate-count">{{ $gates->count() }}</span>
                                @else
                                    <i class="fal fa-lock-open text-muted"></i>
                                @endif
                            </div>
                            <div class="gate-line"></div>
                            <div class="gate-arrow"><i class="fal fa-chevron-right"></i></div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Gate Detail Rows (below the flow) --}}
            <div class="gate-details mt-4">
                @foreach($sortedStages as $idx => $stage)
                    @if(!$loop->last)
                        @php
                            $nextStage = $sortedStages[$idx + 1] ?? null;
                            $gateKey = $stage->id . '-' . ($nextStage?->id ?? '');
                            $gates = $gatesByTransition[$gateKey] ?? collect();
                        @endphp
                        @if($gates->isNotEmpty())
                        <div class="gate-detail-row mb-3" data-from-stage="{{ $stage->id }}" data-to-stage="{{ $nextStage?->id }}">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge me-1" style="background-color: {{ $stage->color }}">{{ $stage->name }}</span>
                                <i class="fal fa-arrow-right mx-2 text-muted"></i>
                                <span class="badge" style="background-color: {{ $nextStage->color }}">{{ $nextStage->name }}</span>
                                <span class="ms-2 text-muted fs-sm">{{ $gates->count() }} criteria</span>
                            </div>
                            <div class="row g-2">
                                @foreach($gates->sortBy('sort_order') as $gate)
                                <div class="col-md-4 col-lg-3">
                                    <div class="gate-criteria-card">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="fw-medium fs-sm">{{ $gate->requirement_label }}</span>
                                            <span class="badge bg-light text-dark fs-xs">
                                                {{ $gate->comparison === 'gte' ? '≥' : ($gate->comparison === 'gt' ? '>' : ($gate->comparison === 'lte' ? '≤' : '=')) }}
                                                {{ $gate->unit === 'percentage' ? number_format($gate->threshold_value, 0) . '%' : ($gate->unit === 'currency' ? '$' . number_format($gate->threshold_value, 0) : number_format($gate->threshold_value, $gate->threshold_value == intval($gate->threshold_value) ? 0 : 1)) }}
                                            </span>
                                        </div>
                                        @if($gate->description)
                                        <small class="text-muted d-block mt-1">{{ $gate->description }}</small>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- Stages --}}
<div class="card shadow-0 mb-g" id="stagesCard">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">Stages (<span id="stagesCount">{{ $path->stages->count() }}</span>)</div>
        <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addStageForm">Add Stage</button>
    </div>

    {{-- Add Stage Form --}}
    <div class="collapse" id="addStageForm">
        <div class="card-body border-bottom bg-light">
            <form action="{{ route('admin.paths.stages.store', $path) }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-2">
                    <label class="form-label fs-sm">Stage #</label>
                    <input type="number" name="stage_number" class="form-control form-control-sm" value="{{ $path->stages->count() + 1 }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-sm">Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-sm">Color</label>
                    <input type="color" name="color" class="form-control form-control-sm form-control-color" value="#6C757D">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-sm">Icon (CSS class)</label>
                    <input type="text" name="icon" class="form-control form-control-sm" placeholder="fal fa-flag">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Add Stage</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <table class="table m-0" id="stagesTable">
            <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Stage Name</th>
                    <th class="text-center">Color</th>
                    <th>Icon</th>
                    <th class="text-center">Tasks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($path->stages->sortBy('stage_number') as $stage)
                <tr data-stage-id="{{ $stage->id }}">
                    <td><strong>{{ $stage->stage_number }}</strong></td>
                    <td>
                        <span class="badge" style="background-color: {{ $stage->color }}">{{ $stage->name }}</span>
                        @if($stage->description)
                        <small class="d-block text-muted mt-1">{{ $stage->description }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="d-inline-block rounded" style="width:20px;height:20px;background-color:{{ $stage->color }}"></span>
                    </td>
                    <td><small class="text-muted">{{ $stage->icon ?? '—' }}</small></td>
                    <td class="text-center">
                        {{ $path->taskTemplates->where('stage_id', $stage->id)->count() }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Gate Requirements --}}
<div class="card shadow-0 mb-g" id="gatesCard">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">Gate Requirements (<span id="gatesCount">{{ $path->gates->count() }}</span>)</div>
        <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addGateForm">Add Gate</button>
    </div>

    {{-- Add Gate Form --}}
    <div class="collapse" id="addGateForm">
        <div class="card-body border-bottom bg-light">
            <form action="{{ route('admin.paths.gates.store', $path) }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-2">
                    <label class="form-label fs-sm">From Stage</label>
                    <select name="from_stage_id" class="form-select form-select-sm" required>
                        @foreach($path->stages->sortBy('stage_number') as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->stage_number }}. {{ $stage->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-sm">To Stage</label>
                    <select name="to_stage_id" class="form-select form-select-sm" required>
                        @foreach($path->stages->sortBy('stage_number') as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->stage_number }}. {{ $stage->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-sm">Requirement Key</label>
                    <input type="text" name="requirement_key" class="form-control form-control-sm" placeholder="e.g., cert_mb_count" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-sm">Label</label>
                    <input type="text" name="requirement_label" class="form-control form-control-sm" placeholder="e.g., MB Certifications" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label fs-sm">Compare</label>
                    <select name="comparison" class="form-select form-select-sm">
                        <option value="gte">&ge;</option>
                        <option value="gt">&gt;</option>
                        <option value="lte">&le;</option>
                        <option value="eq">=</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label fs-sm">Threshold</label>
                    <input type="number" name="threshold_value" class="form-control form-control-sm" step="0.01" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label fs-sm">Unit</label>
                    <select name="unit" class="form-select form-select-sm">
                        <option value="count">Count</option>
                        <option value="percentage">%</option>
                        <option value="currency">$</option>
                        <option value="days">Days</option>
                        <option value="score">Score</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <table class="table m-0" id="gatesTable">
            <thead>
                <tr>
                    <th>Transition</th>
                    <th>Requirement</th>
                    <th class="text-center">Comparison</th>
                    <th class="text-center">Threshold</th>
                    <th>Description</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($path->gates->sortBy(['from_stage_id', 'to_stage_id', 'sort_order']) as $gate)
                @php
                    $from = $stagesById[$gate->from_stage_id] ?? null;
                    $to = $stagesById[$gate->to_stage_id] ?? null;
                @endphp
                <tr data-from-stage="{{ $gate->from_stage_id }}" data-to-stage="{{ $gate->to_stage_id }}">
                    <td>
                        @if($from && $to)
                        <span class="badge" style="background-color: {{ $from->color }}">{{ $from->name }}</span>
                        &rarr;
                        <span class="badge" style="background-color: {{ $to->color }}">{{ $to->name }}</span>
                        @endif
                    </td>
                    <td><strong>{{ $gate->requirement_label }}</strong><br><small class="text-muted">{{ $gate->requirement_key }}</small></td>
                    <td class="text-center">{{ $gate->comparison }}</td>
                    <td class="text-center">{{ number_format($gate->threshold_value, $gate->unit === 'percentage' ? 1 : 0) }}{{ $gate->unit === 'percentage' ? '%' : '' }}</td>
                    <td><small class="text-muted">{{ $gate->description ?? '—' }}</small></td>
                    <td>
                        <form action="{{ route('admin.path-gates.destroy', $gate) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this gate?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">&times;</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if($path->gates->isEmpty())
                <tr class="empty-row"><td colspan="6" class="text-center py-4 text-muted">No gate requirements defined.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- Task Templates for this Path --}}
<div class="card shadow-0 mb-g" id="tasksCard">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">Task Templates (<span id="tasksCount">{{ $path->taskTemplates->count() }}</span>)</div>
        <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addTaskForm">Add Task Template</button>
    </div>

    {{-- Add Task Template Form --}}
    <div class="collapse" id="addTaskForm">
        <div class="card-body border-bottom bg-light">
            <form action="{{ route('admin.task-templates.store') }}" method="POST">
                @csrf
                <input type="hidden" name="partner_path_id" value="{{ $path->id }}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label fs-sm">Title</label>
                        <input type="text" name="title" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-sm">Stage</label>
                        <select name="stage_id" class="form-select form-select-sm">
                            <option value="">All Stages</option>
                            @foreach($path->stages->sortBy('stage_number') as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->stage_number }}. {{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-sm">Category</label>
                        <select name="category" class="form-select form-select-sm" required>
                            @foreach(\App\Models\TaskTemplate::CATEGORIES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-sm">Requirement</label>
                        <select name="requirement_level" class="form-select form-select-sm" required>
                            @foreach(\App\Models\TaskTemplate::REQUIREMENT_LEVELS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-sm">Effort</label>
                        <select name="effort_size" class="form-select form-select-sm">
                            @foreach(\App\Models\TaskTemplate::EFFORT_SIZES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-2 mt-1">
                    <div class="col-md-4">
                        <label class="form-label fs-sm">Description</label>
                        <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-sm">Completion Criteria</label>
                        <textarea name="completion_criteria" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-sm">Owner</label>
                        <select name="default_owner" class="form-select form-select-sm">
                            @foreach(\App\Models\TaskTemplate::OWNERS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fs-sm">Due (days)</label>
                        <input type="number" name="due_days_offset" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Add Template</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <table class="table m-0" id="tasksTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Stage</th>
                    <th>Category</th>
                    <th class="text-center">Level</th>
                    <th class="text-center">Effort</th>
                    <th>Owner</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($path->taskTemplates as $template)
                <tr data-stage-id="{{ $template->stage_id ?? 'all' }}">
                    <td>
                        <strong>{{ $template->title }}</strong>
                        @if($template->description)
                        <small class="d-block text-muted">{{ Str::limit($template->description, 60) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($template->stage)
                        <span class="badge" style="background-color: {{ $template->stage->color }}">{{ $template->stage->name }}</span>
                        @else
                        <span class="text-muted">All</span>
                        @endif
                    </td>
                    <td>{{ \App\Models\TaskTemplate::CATEGORIES[$template->category] ?? $template->category }}</td>
                    <td class="text-center">
                        @php $lvlColors = ['required' => 'danger', 'recommended' => 'warning', 'optional' => 'secondary']; @endphp
                        <span class="badge bg-{{ $lvlColors[$template->requirement_level] ?? 'secondary' }}">{{ ucfirst($template->requirement_level) }}</span>
                    </td>
                    <td class="text-center">{{ \App\Models\TaskTemplate::EFFORT_SIZES[$template->effort_size] ?? $template->effort_size }}</td>
                    <td>{{ \App\Models\TaskTemplate::OWNERS[$template->default_owner] ?? $template->default_owner }}</td>
                    <td class="text-end text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-primary me-1"
                            data-id="{{ $template->id }}"
                            data-title="{{ e($template->title) }}"
                            data-description="{{ e($template->description) }}"
                            data-completion-criteria="{{ e($template->completion_criteria) }}"
                            data-stage-id="{{ $template->stage_id }}"
                            data-category="{{ $template->category }}"
                            data-requirement-level="{{ $template->requirement_level }}"
                            data-effort-size="{{ $template->effort_size }}"
                            data-default-owner="{{ $template->default_owner }}"
                            data-due-days-offset="{{ $template->due_days_offset }}"
                            onclick="openEditTaskModal(this)">
                            <i class="fal fa-pencil"></i>
                        </button>
                        <form action="{{ route('admin.task-templates.destroy', $template) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this template?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">&times;</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr class="empty-row"><td colspan="7" class="text-center py-4 text-muted">No task templates for this path.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Edit Task Template Modal --}}
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editTaskForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Task Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label fs-sm">Title</label>
                            <input type="text" name="title" id="editTaskTitle" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-sm">Stage</label>
                            <select name="stage_id" id="editTaskStage" class="form-select form-select-sm">
                                <option value="">All Stages</option>
                                @foreach($path->stages->sortBy('stage_number') as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->stage_number }}. {{ $stage->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-sm">Category</label>
                            <select name="category" id="editTaskCategory" class="form-select form-select-sm" required>
                                @foreach(\App\Models\TaskTemplate::CATEGORIES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label fs-sm">Requirement</label>
                            <select name="requirement_level" id="editTaskRequirement" class="form-select form-select-sm" required>
                                @foreach(\App\Models\TaskTemplate::REQUIREMENT_LEVELS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-sm">Effort</label>
                            <select name="effort_size" id="editTaskEffort" class="form-select form-select-sm">
                                @foreach(\App\Models\TaskTemplate::EFFORT_SIZES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-sm">Owner</label>
                            <select name="default_owner" id="editTaskOwner" class="form-select form-select-sm">
                                @foreach(\App\Models\TaskTemplate::OWNERS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-sm">Due (days)</label>
                            <input type="number" name="due_days_offset" id="editTaskDueDays" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fs-sm">Description</label>
                            <textarea name="description" id="editTaskDescription" class="form-control form-control-sm" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-sm">Completion Criteria</label>
                            <textarea name="completion_criteria" id="editTaskCriteria" class="form-control form-control-sm" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Styles --}}
<style>
    .path-flow-container {
        overflow-x: auto;
        padding-bottom: 4px;
    }
    .path-flow {
        display: flex;
        align-items: center;
        min-width: max-content;
        padding: 8px 0;
    }

    /* Stage Node */
    .path-stage-node {
        flex-shrink: 0;
        cursor: pointer;
    }
    .stage-card {
        width: 140px;
        border: 2px solid;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        text-align: center;
        transition: transform 0.15s, box-shadow 0.15s, opacity 0.2s;
    }
    .stage-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .stage-card.active {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.18);
        border-width: 3px;
    }
    .stage-card.dimmed {
        opacity: 0.35;
        transform: none;
    }
    .stage-card.dimmed:hover {
        opacity: 0.6;
        transform: translateY(-1px);
    }
    .stage-header {
        padding: 12px 8px 10px;
        color: #fff;
        font-size: 1.4rem;
        line-height: 1;
    }
    .stage-body {
        padding: 10px 8px 12px;
    }
    .stage-number {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #868e96;
        margin-bottom: 2px;
    }
    .stage-name {
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 6px;
    }
    .stage-stats {
        display: flex;
        justify-content: center;
        gap: 6px;
    }
    .stat-badge {
        font-size: 0.7rem;
        color: #868e96;
    }
    .stat-badge i {
        margin-right: 2px;
    }

    /* Gate Connector */
    .path-gate-connector {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        position: relative;
        transition: opacity 0.2s;
    }
    .path-gate-connector.dimmed {
        opacity: 0.25;
    }
    .gate-line {
        width: 24px;
        height: 2px;
        background: #dee2e6;
    }
    .gate-badge {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        background: #e8f5e9;
        border: 1px solid #c8e6c9;
        font-size: 0.75rem;
        font-weight: 600;
        color: #2e7d32;
        white-space: nowrap;
        cursor: default;
    }
    .gate-badge.gate-empty {
        background: #f5f5f5;
        border-color: #e0e0e0;
        color: #9e9e9e;
    }
    .gate-count {
        font-size: 0.7rem;
    }
    .gate-arrow {
        margin-left: -1px;
        color: #dee2e6;
        font-size: 0.8rem;
    }

    /* Gate Detail Cards */
    .gate-detail-row {
        padding: 12px 16px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: #fafbfc;
        transition: opacity 0.2s;
    }
    .gate-detail-row.filtered-out {
        display: none;
    }
    .gate-criteria-card {
        padding: 8px 10px;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        background: #fff;
        height: 100%;
    }
    .fs-xs {
        font-size: 0.7rem;
    }

    /* Filtered table rows */
    #stagesTable tr.filtered-out,
    #gatesTable tr.filtered-out,
    #tasksTable tr.filtered-out {
        display: none;
    }
</style>

{{-- Stage Filter JavaScript --}}
<script>
    let activeStageId = null;

    function toggleStageFilter(stageId, stageName) {
        if (activeStageId === stageId) {
            clearStageFilter();
            return;
        }
        activeStageId = stageId;
        applyFilter(stageId, stageName);
    }

    function clearStageFilter() {
        activeStageId = null;

        // Reset flowchart nodes
        document.querySelectorAll('.stage-card').forEach(c => {
            c.classList.remove('active', 'dimmed');
        });
        document.querySelectorAll('.path-gate-connector').forEach(c => {
            c.classList.remove('dimmed');
        });

        // Show all gate detail rows
        document.querySelectorAll('.gate-detail-row').forEach(r => {
            r.classList.remove('filtered-out');
        });

        // Show all table rows
        document.querySelectorAll('#stagesTable tbody tr, #gatesTable tbody tr, #tasksTable tbody tr').forEach(r => {
            r.classList.remove('filtered-out');
        });

        // Hide filter UI
        document.getElementById('filterIndicator').classList.add('d-none');
        document.getElementById('clearFilterBtn').classList.add('d-none');

        // Reset counts
        updateCounts();
    }

    function applyFilter(stageId, stageName) {
        const sid = String(stageId);

        // --- Flowchart: highlight selected, dim others ---
        document.querySelectorAll('.stage-card').forEach(c => {
            c.classList.remove('active', 'dimmed');
        });
        document.querySelectorAll('.path-stage-node').forEach(node => {
            const card = node.querySelector('.stage-card');
            if (node.dataset.stageId === sid) {
                card.classList.add('active');
            } else {
                card.classList.add('dimmed');
            }
        });

        // Connectors: highlight ones adjacent to selected stage
        document.querySelectorAll('.path-gate-connector').forEach(conn => {
            const from = conn.dataset.fromStage;
            const to = conn.dataset.toStage;
            if (from === sid || to === sid) {
                conn.classList.remove('dimmed');
            } else {
                conn.classList.add('dimmed');
            }
        });

        // --- Gate detail rows: show only adjacent ---
        document.querySelectorAll('.gate-detail-row').forEach(row => {
            const from = row.dataset.fromStage;
            const to = row.dataset.toStage;
            if (from === sid || to === sid) {
                row.classList.remove('filtered-out');
            } else {
                row.classList.add('filtered-out');
            }
        });

        // --- Stages table: show only selected ---
        document.querySelectorAll('#stagesTable tbody tr').forEach(row => {
            if (row.classList.contains('empty-row')) return;
            row.classList.toggle('filtered-out', row.dataset.stageId !== sid);
        });

        // --- Gates table: show gates where from or to matches ---
        document.querySelectorAll('#gatesTable tbody tr').forEach(row => {
            if (row.classList.contains('empty-row')) return;
            const from = row.dataset.fromStage;
            const to = row.dataset.toStage;
            row.classList.toggle('filtered-out', from !== sid && to !== sid);
        });

        // --- Tasks table: show tasks for selected stage + "all" ---
        document.querySelectorAll('#tasksTable tbody tr').forEach(row => {
            if (row.classList.contains('empty-row')) return;
            const taskStage = row.dataset.stageId;
            row.classList.toggle('filtered-out', taskStage !== sid && taskStage !== 'all');
        });

        // Show filter UI
        document.getElementById('filterStageName').textContent = stageName;
        document.getElementById('filterIndicator').classList.remove('d-none');
        document.getElementById('clearFilterBtn').classList.remove('d-none');

        // Update visible counts
        updateCounts();
    }

    function updateCounts() {
        const countVisible = (tableId) => {
            const rows = document.querySelectorAll(`#${tableId} tbody tr:not(.empty-row)`);
            let count = 0;
            rows.forEach(r => { if (!r.classList.contains('filtered-out')) count++; });
            return count;
        };

        const stagesTotal = document.querySelectorAll('#stagesTable tbody tr:not(.empty-row)').length;
        const gatesTotal = document.querySelectorAll('#gatesTable tbody tr:not(.empty-row)').length;
        const tasksTotal = document.querySelectorAll('#tasksTable tbody tr:not(.empty-row)').length;

        const stagesVis = countVisible('stagesTable');
        const gatesVis = countVisible('gatesTable');
        const tasksVis = countVisible('tasksTable');

        document.getElementById('stagesCount').textContent = activeStageId ? `${stagesVis}/${stagesTotal}` : stagesTotal;
        document.getElementById('gatesCount').textContent = activeStageId ? `${gatesVis}/${gatesTotal}` : gatesTotal;
        document.getElementById('tasksCount').textContent = activeStageId ? `${tasksVis}/${tasksTotal}` : tasksTotal;
    }

    // ─── Edit Task Template Modal ────────────────────────────
    const taskTemplateBaseUrl = "{{ url('/admin/task-templates') }}";

    function openEditTaskModal(btn) {
        const form = document.getElementById('editTaskForm');
        form.action = taskTemplateBaseUrl + '/' + btn.dataset.id;

        document.getElementById('editTaskTitle').value = btn.dataset.title || '';
        document.getElementById('editTaskDescription').value = btn.dataset.description || '';
        document.getElementById('editTaskCriteria').value = btn.dataset.completionCriteria || '';
        document.getElementById('editTaskStage').value = btn.dataset.stageId || '';
        document.getElementById('editTaskCategory').value = btn.dataset.category || '';
        document.getElementById('editTaskRequirement').value = btn.dataset.requirementLevel || '';
        document.getElementById('editTaskEffort').value = btn.dataset.effortSize || 'medium';
        document.getElementById('editTaskOwner').value = btn.dataset.defaultOwner || 'psm';
        document.getElementById('editTaskDueDays').value = btn.dataset.dueDaysOffset || '';

        new bootstrap.Modal(document.getElementById('editTaskModal')).show();
    }
</script>

@endsection
