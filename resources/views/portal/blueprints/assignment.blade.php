@extends('portal.layout', ['pageTitle' => $assignment->blueprint->name])

@section('content')
@php
    $blueprint = $assignment->blueprint;
    $role = $assignment->role;
    $roleColumn = match($role) {
        'gtm' => 'gtm_applicable',
        'pre_sales' => 'pre_sales_applicable',
        'delivery' => 'delivery_applicable',
        default => 'delivery_applicable',
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $blueprint->name }}</h4>
        <span class="badge" style="background:{{ $assignment->role_color }}">{{ $assignment->role_label }}</span>
        <span class="badge bg-{{ $assignment->status === 'active' ? 'success' : ($assignment->status === 'paused' ? 'warning' : 'info') }}">{{ \App\Models\PartnerBlueprintAssignment::STATUSES[$assignment->status] ?? $assignment->status }}</span>
        @if($blueprint->product_area)
            <span class="text-muted ms-2">{{ $blueprint->product_area }}</span>
        @endif
    </div>
    <a href="{{ route('portal.blueprints.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

{{-- KPI Cards --}}
@php
    $overallPct = $assignment->completionPercentage();
    $totalEnrollments = $assignment->enrollments->count();
    $completedEnrollments = $assignment->enrollments->where('status', 'completed')->count();
    $applicableCount = $assignment->applicableContentItemCount();
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card portal-card text-center py-3">
            <h2 class="text-{{ $overallPct >= 80 ? 'success' : ($overallPct >= 40 ? 'warning' : 'danger') }}">{{ $overallPct }}%</h2>
            <small class="text-muted">Avg Completion</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card portal-card text-center py-3">
            <h2>{{ $totalEnrollments }}</h2>
            <small class="text-muted">Enrolled</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card portal-card text-center py-3">
            <h2 class="text-success">{{ $completedEnrollments }}</h2>
            <small class="text-muted">Completed</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card portal-card text-center py-3">
            <h2>{{ $applicableCount }}</h2>
            <small class="text-muted">Content Items</small>
        </div>
    </div>
</div>

{{-- Enrolled Contacts --}}
<div class="card portal-card mb-4">
    <div class="card-header"><strong>Enrolled Resources</strong></div>
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignment->enrollments->sortByDesc(fn ($e) => $e->completionPercentage()) as $enrollment)
                @php
                    $pct = $enrollment->completionPercentage();
                    $completed = $enrollment->completedItemsCount();
                @endphp
                <tr>
                    <td>{{ $enrollment->contact?->full_name ?? 'Unknown' }}</td>
                    <td><small class="text-muted">{{ $enrollment->contact?->title ?? '—' }}</small></td>
                    <td>
                        <span class="badge" style="background:{{ $enrollment->status_color }}">{{ $enrollment->status_label }}</span>
                    </td>
                    <td style="min-width:180px">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px">
                                <div class="progress-bar bg-{{ $pct >= 80 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') }}" style="width:{{ $pct }}%"></div>
                            </div>
                            <small class="text-muted text-nowrap">{{ $completed }}/{{ $applicableCount }} ({{ $pct }}%)</small>
                        </div>
                    </td>
                    <td class="text-end">
                        @if($contact->isPortalAdmin() || $enrollment->contact_id === $contact->id)
                            <a href="{{ route('portal.blueprints.show', $enrollment) }}" class="btn btn-sm btn-outline-primary">View</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">No one enrolled yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Outcome Summary --}}
<div class="card portal-card">
    <div class="card-header"><strong>Blueprint Outcomes</strong></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Outcome</th>
                    <th class="text-center">Content Items</th>
                </tr>
            </thead>
            <tbody>
                @foreach($blueprint->outcomes as $outcome)
                @php $outcomeItems = $outcome->contentItems->where($roleColumn, '!=', 'na'); @endphp
                @if($outcomeItems->count())
                <tr>
                    <td>{{ $outcome->title }}</td>
                    <td class="text-center">{{ $outcomeItems->count() }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
