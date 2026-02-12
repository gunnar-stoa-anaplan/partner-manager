@extends('layouts.vertical', ['pageTitle' => 'Task Templates'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Task Templates', 'pageSubTitle1' => 'Admin', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Task Templates define the default tasks that are created when a partner enters a path stage. Filter by path, stage, or category to find specific templates. Each template specifies the task title, effort level, owner, and completion criteria — these values are copied to the partner's task board when they progress through stages.</p>

{{-- Filter by Path --}}
<div class="card shadow-0 mb-g">
    <div class="card-body py-2">
        <form action="{{ route('admin.task-templates.index') }}" method="GET" class="row align-items-end g-2">
            <div class="col-md-3">
                <label class="form-label fs-sm">Path</label>
                <select name="path_id" class="form-select form-select-sm">
                    <option value="">All Paths</option>
                    @foreach($paths as $p)
                    <option value="{{ $p->id }}" {{ request('path_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fs-sm">Category</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach(\App\Models\TaskTemplate::CATEGORIES as $key => $label)
                    <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fs-sm">Level</label>
                <select name="requirement_level" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach(\App\Models\TaskTemplate::REQUIREMENT_LEVELS as $key => $label)
                    <option value="{{ $key }}" {{ request('requirement_level') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Templates Table --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Task Templates ({{ $templates->count() }})</div></div>
    <div class="card-body p-0">
        <table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Path</th>
                    <th>Stage</th>
                    <th>Category</th>
                    <th class="text-center">Level</th>
                    <th class="text-center">Effort</th>
                    <th>Owner</th>
                    <th class="text-center">Due (days)</th>
                    <th class="text-center">Active</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                <tr>
                    <td>
                        <strong>{{ $template->title }}</strong>
                        @if($template->description)
                        <small class="d-block text-muted">{{ Str::limit($template->description, 50) }}</small>
                        @endif
                    </td>
                    <td>{{ $template->path->name ?? '—' }}</td>
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
                    <td class="text-center">{{ \App\Models\TaskTemplate::EFFORT_SIZES[$template->effort_size] ?? '—' }}</td>
                    <td>{{ \App\Models\TaskTemplate::OWNERS[$template->default_owner] ?? '—' }}</td>
                    <td class="text-center">{{ $template->due_days_offset ?? '—' }}</td>
                    <td class="text-center">
                        @if($template->is_active)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">No task templates found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
