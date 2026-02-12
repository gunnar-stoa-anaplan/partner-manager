@extends('layouts.vertical', ['pageTitle' => 'Task Board'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Task Board', 'pageSubTitle1' => 'Plan', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The Task Board is your operational checklist for partner management activities. Tasks are organized by partner, category, and priority level — with required tasks highlighted so you know what must get done first. Use the filters to focus on a specific partner or category, and mark tasks complete as you go. Overdue items are flagged in red so nothing falls through the cracks.</p>

{{-- Stats Row --}}
<div class="row">
    <div class="col-xl-3 col-md-6"><div class="card shadow-0 mb-g"><div class="card-body text-center py-3"><h2 class="fw-300 m-0">{{ $stats['total'] }}</h2><small class="text-muted">Total Tasks</small></div></div></div>
    <div class="col-xl-3 col-md-6"><div class="card shadow-0 mb-g"><div class="card-body text-center py-3"><h2 class="fw-300 m-0 text-danger">{{ $stats['overdue'] }}</h2><small class="text-muted">Overdue</small></div></div></div>
    <div class="col-xl-3 col-md-6"><div class="card shadow-0 mb-g"><div class="card-body text-center py-3"><h2 class="fw-300 m-0 text-success">{{ $stats['completed_this_week'] }}</h2><small class="text-muted">Completed This Week</small></div></div></div>
    <div class="col-xl-3 col-md-6"><div class="card shadow-0 mb-g"><div class="card-body text-center py-3"><h2 class="fw-300 m-0 text-warning">{{ $stats['required_incomplete'] }}</h2><small class="text-muted">Required Incomplete</small></div></div></div>
</div>

{{-- Filter Bar --}}
<div class="card shadow-0 mb-g">
    <div class="card-body py-2">
        <form class="row g-2 align-items-center" method="GET">
            <div class="col-auto">
                <select name="partner_id" class="form-select form-select-sm">
                    <option value="">All Partners</option>
                    @foreach($partners as $p)<option value="{{ $p->id }}" {{ request('partner_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach(['account','enablement','solution','delivery','business_development','program'] as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$cat)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['not_started','in_progress','completed','blocked','waived'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-primary">Filter</button></div>
            <div class="col-auto"><a href="{{ route('plan.tasks.index') }}" class="btn btn-sm btn-secondary">Reset</a></div>
        </form>
    </div>
</div>

{{-- Task List --}}
<div class="card shadow-0 mb-g">
    <div class="card-body">
        <table class="table table-hover m-0" id="tasks-table">
            <thead><tr><th>Partner</th><th>Task</th><th>Category</th><th>Required</th><th>Effort</th><th>Owner</th><th>Due Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($tasks as $task)
            <tr class="{{ $task->due_date && $task->due_date < now() && !in_array($task->status, ['completed','waived']) ? 'table-danger' : '' }}">
                <td>{{ $task->partner->name }}</td>
                <td>
                    <strong>{{ $task->title }}</strong>
                    @if($task->description)<br><small class="text-muted">{{ Str::limit($task->description, 60) }}</small>@endif
                </td>
                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ',$task->category)) }}</span></td>
                <td>
                    @if($task->requirement_level === 'required')<span class="badge bg-danger">Required</span>
                    @elseif($task->requirement_level === 'recommended')<span class="badge bg-warning">Recommended</span>
                    @else<span class="badge bg-info">Optional</span>@endif
                </td>
                <td>{{ strtoupper(substr($task->effort_size, 0, 1)) }}</td>
                <td>{{ ucfirst($task->owner) }}</td>
                <td>{{ $task->due_date ? $task->due_date->format('M j') : '—' }}</td>
                <td>
                    @php $statusColors = ['not_started'=>'secondary','in_progress'=>'primary','completed'=>'success','blocked'=>'danger','waived'=>'dark']; @endphp
                    <span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span>
                </td>
                <td>
                    @if($task->status !== 'completed' && $task->status !== 'waived')
                    <form action="{{ route('plan.tasks.update-status', $task) }}" method="POST" class="d-inline">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="completed">
                        <button class="btn btn-xs btn-outline-success" title="Complete"><svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#check"></use></svg></button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center text-muted py-4">No tasks found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>$(document).ready(function() { new SmartTable('#tasks-table', { sortable: true, perPage: 20 }); });</script>
@endsection
