@extends('layouts.vertical', ['pageTitle' => 'Resource Path: ' . $path->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $path->name, 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubTitle2' => 'Resource Path Config', 'pageSubTitle2Url' => route('admin.resource-paths.index')])
@endsection

@section('content')

{{-- Path Details --}}
<div class="card shadow-0 mb-g">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="m-0">{{ $path->name }}</h4>
                <small class="text-muted">{{ $path->description ?? 'No description' }}</small>
            </div>
            <a href="{{ route('admin.resource-paths.index') }}" class="btn btn-sm btn-outline-secondary">Back to Paths</a>
        </div>
    </div>
</div>

{{-- Edit Path --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Edit Path</div></div>
    <div class="card-body">
        <form action="{{ route('admin.resource-paths.update', $path) }}" method="POST" class="row g-2 align-items-end">
            @csrf @method('PUT')
            <div class="col-md-4">
                <label class="form-label fs-sm">Path Name</label>
                <input type="text" name="name" value="{{ $path->name }}" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fs-sm">Description</label>
                <input type="text" name="description" value="{{ $path->description }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <div class="form-check mt-3">
                    <input type="checkbox" name="is_default" value="1" class="form-check-input" id="editDefault" {{ $path->is_default ? 'checked' : '' }}>
                    <label class="form-check-label fs-sm" for="editDefault">Default Path</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- Stage Flowchart --}}
@php
    $sortedStages = $path->stages->sortBy('sort_order')->values();
    $gatesByTransition = $path->gates->groupBy(fn($g) => $g->from_stage_id . '-' . $g->to_stage_id);
@endphp
@if($sortedStages->count() > 0)
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Stage Flow</div></div>
    <div class="card-body">
        <div class="d-flex align-items-center flex-wrap gap-2">
            @foreach($sortedStages as $idx => $stage)
                <div class="text-center">
                    <div class="badge px-3 py-2" style="background-color: {{ $stage->color }}; font-size: 0.85rem;">
                        <i class="{{ $stage->icon ?? 'fal fa-circle' }} me-1"></i>{{ $stage->name }}
                    </div>
                    <div class="small text-muted mt-1">Stage {{ $stage->stage_number }}</div>
                </div>
                @if(!$loop->last)
                    @php
                        $nextStage = $sortedStages[$idx + 1] ?? null;
                        $gateKey = $stage->id . '-' . ($nextStage?->id ?? '');
                        $gateCount = isset($gatesByTransition[$gateKey]) ? $gatesByTransition[$gateKey]->count() : 0;
                    @endphp
                    <div class="text-center text-muted">
                        <i class="fal fa-long-arrow-right fa-lg"></i>
                        @if($gateCount > 0)
                        <div class="small">{{ $gateCount }} gates</div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Stages Table --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">Stages ({{ $sortedStages->count() }})</div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Color</th>
                    <th>Icon</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sortedStages as $stage)
                <tr>
                    <td>{{ $stage->stage_number }}</td>
                    <td><span class="badge" style="background-color: {{ $stage->color }}">{{ $stage->name }}</span></td>
                    <td class="small text-muted">{{ Str::limit($stage->description, 60) }}</td>
                    <td><span class="d-inline-block rounded" style="width:20px;height:20px;background:{{ $stage->color }}"></span></td>
                    <td><i class="{{ $stage->icon ?? 'fal fa-circle' }}"></i></td>
                    <td class="text-end">
                        <form action="{{ route('admin.resource-path-stages.destroy', $stage) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this stage?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fal fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{-- Add Stage Form --}}
    <div class="card-footer">
        <form action="{{ route('admin.resource-paths.stages.store', $path) }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-2">
                <label class="form-label fs-sm">Stage #</label>
                <input type="number" name="stage_number" class="form-control form-control-sm" min="1" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fs-sm">Name</label>
                <input type="text" name="name" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fs-sm">Color</label>
                <input type="color" name="color" value="#6C757D" class="form-control form-control-sm form-control-color">
            </div>
            <div class="col-md-3">
                <label class="form-label fs-sm">Icon</label>
                <input type="text" name="icon" class="form-control form-control-sm" placeholder="fal fa-star">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Add Stage</button>
            </div>
        </form>
    </div>
</div>

{{-- Gate Requirements --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Gate Requirements ({{ $path->gates->count() }})</div></div>
    <div class="card-body p-0">
        <table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>From</th>
                    <th>To</th>
                    <th>Requirement</th>
                    <th>Comparison</th>
                    <th>Threshold</th>
                    <th>Unit</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($path->gates->sortBy('sort_order') as $gate)
                @php
                    $fromStage = $sortedStages->firstWhere('id', $gate->from_stage_id);
                    $toStage = $sortedStages->firstWhere('id', $gate->to_stage_id);
                @endphp
                <tr>
                    <td><span class="badge" style="background-color: {{ $fromStage?->color ?? '#ccc' }}">{{ $fromStage?->name ?? '?' }}</span></td>
                    <td><span class="badge" style="background-color: {{ $toStage?->color ?? '#ccc' }}">{{ $toStage?->name ?? '?' }}</span></td>
                    <td>{{ $gate->requirement_label }}</td>
                    <td><code>{{ $gate->comparison }}</code></td>
                    <td>{{ number_format($gate->threshold_value, $gate->unit === 'currency' ? 0 : ($gate->threshold_value == intval($gate->threshold_value) ? 0 : 1)) }}</td>
                    <td><span class="badge bg-light text-dark">{{ $gate->unit }}</span></td>
                    <td class="text-end">
                        <form action="{{ route('admin.resource-path-gates.destroy', $gate) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this gate?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fal fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No gate requirements defined.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- Add Gate Form --}}
    <div class="card-footer">
        <form action="{{ route('admin.resource-paths.gates.store', $path) }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-2">
                <label class="form-label fs-sm">From Stage</label>
                <select name="from_stage_id" class="form-select form-select-sm" required>
                    @foreach($sortedStages as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fs-sm">To Stage</label>
                <select name="to_stage_id" class="form-select form-select-sm" required>
                    @foreach($sortedStages as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fs-sm">Requirement Key</label>
                <input type="text" name="requirement_key" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fs-sm">Label</label>
                <input type="text" name="requirement_label" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-1">
                <label class="form-label fs-sm">Threshold</label>
                <input type="number" name="threshold_value" step="0.01" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-1">
                <label class="form-label fs-sm">Unit</label>
                <select name="unit" class="form-select form-select-sm">
                    <option value="count">Count</option>
                    <option value="percentage">%</option>
                    <option value="currency">$</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Add Gate</button>
            </div>
        </form>
    </div>
</div>

{{-- Task Templates --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Task Templates ({{ $path->taskTemplates->count() }})</div></div>
    <div class="card-body p-0">
        <table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>Stage</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Level</th>
                    <th>Owner</th>
                    <th>Due Offset</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($path->taskTemplates->sortBy('sort_order') as $tpl)
                @php $tplStage = $sortedStages->firstWhere('id', $tpl->stage_id); @endphp
                <tr>
                    <td>
                        @if($tplStage)
                        <span class="badge" style="background-color: {{ $tplStage->color }}">{{ $tplStage->name }}</span>
                        @else
                        <span class="text-muted">Any</span>
                        @endif
                    </td>
                    <td>{{ $tpl->title }}</td>
                    <td><span class="badge bg-light text-dark">{{ $tpl->category }}</span></td>
                    <td>{{ ucfirst($tpl->requirement_level) }}</td>
                    <td>{{ strtoupper($tpl->default_owner) }}</td>
                    <td>{{ $tpl->due_days_offset ? $tpl->due_days_offset . 'd' : '—' }}</td>
                    <td>
                        @if($tpl->is_active)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No task templates defined for this path.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
