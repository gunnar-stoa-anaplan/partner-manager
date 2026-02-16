@extends('portal.layout', ['pageTitle' => 'Blueprint Progress'])

@section('content')
@php
    $assignment = $enrollment->assignment;
    $blueprint = $assignment->blueprint;
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $blueprint->name }}</h4>
        <span class="badge" style="background:{{ $assignment->role_color }}">{{ $assignment->role_label }}</span>
        <span class="badge" style="background:{{ $enrollment->status_color }}">{{ $enrollment->status_label }}</span>
        <span class="text-muted ms-2">{{ $enrollment->contact?->full_name }}</span>
    </div>
    <a href="{{ route('portal.blueprints.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card portal-card text-center py-3">
            @php $pct = $enrollment->completionPercentage(); @endphp
            <h2 class="text-{{ $pct >= 80 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') }}">{{ $pct }}%</h2>
            <small class="text-muted">Overall Completion</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card portal-card text-center py-3">
            <h2>{{ $enrollment->completedItemsCount() }}</h2>
            <small class="text-muted">Items Completed</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card portal-card text-center py-3">
            <h2>{{ $enrollment->totalApplicableItems() }}</h2>
            <small class="text-muted">Total Items</small>
        </div>
    </div>
</div>

@php
    $role = $assignment->role;
    $roleColumn = match($role) {
        'gtm' => 'gtm_applicable',
        'pre_sales' => 'pre_sales_applicable',
        'delivery' => 'delivery_applicable',
        default => 'delivery_applicable',
    };
@endphp

@foreach($blueprint->outcomes as $outcome)
@php $outcomeItems = $outcome->contentItems->where($roleColumn, '!=', 'na'); @endphp
@if($outcomeItems->count())
<div class="card portal-card mb-3">
    <div class="card-header"><strong>{{ $outcome->title }}</strong></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Content Item</th><th>Source</th><th>Duration</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($outcomeItems as $item)
                @php
                    $progress = $enrollment->progress->firstWhere('blueprint_content_item_id', $item->id);
                    $completed = $progress && $progress->completed;
                @endphp
                <tr class="{{ $completed ? 'table-success' : '' }}">
                    <td>
                        @if($item->url)
                            <a href="{{ $item->url }}" target="_blank">{{ $item->title }}</a>
                        @else
                            {{ $item->title }}
                        @endif
                    </td>
                    <td>{{ $item->source ?? '—' }}</td>
                    <td>{{ $item->duration_display ?? '—' }}</td>
                    <td>
                        @if($completed)
                            <span class="badge bg-success">Completed</span>
                        @else
                            <span class="badge bg-secondary">Pending</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endforeach
@endsection
