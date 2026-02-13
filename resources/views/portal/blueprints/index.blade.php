@extends('portal.layout', ['pageTitle' => 'Blueprints'])

@section('content')
<h4 class="mb-4">Blueprints — {{ $partner->name }}</h4>

{{-- My Enrollments --}}
@if($myEnrollments->isNotEmpty())
<div class="card portal-card mb-4">
    <div class="card-header"><strong>My Enrollments</strong></div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($myEnrollments as $enrollment)
            <div class="col-md-4">
                <div class="card h-100 border">
                    <div class="card-body">
                        <h6>{{ $enrollment->assignment?->blueprint?->name ?? 'Blueprint' }}</h6>
                        <span class="badge" style="background:{{ $enrollment->status_color }}">{{ $enrollment->status_label }}</span>
                        <span class="badge" style="background:{{ \App\Models\PartnerBlueprintAssignment::ROLE_COLORS[$enrollment->assignment?->role] ?? '#868e96' }}">{{ \App\Models\PartnerBlueprintAssignment::ROLES[$enrollment->assignment?->role] ?? '' }}</span>
                        <div class="mt-2">
                            @php $pct = $enrollment->completionPercentage(); @endphp
                            <div class="progress" style="height:8px"><div class="progress-bar bg-{{ $pct >= 80 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') }}" style="width:{{ $pct }}%"></div></div>
                            <small class="text-muted">{{ $pct }}% complete</small>
                        </div>
                        <a href="{{ route('portal.blueprints.show', $enrollment) }}" class="btn btn-sm btn-outline-primary mt-2">View Details</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Partner Assignments --}}
<div class="card portal-card">
    <div class="card-header"><strong>Partner Blueprint Assignments</strong></div>
    <div class="card-body">
        @forelse($assignments as $assignment)
        <div class="border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">{{ $assignment->blueprint?->name ?? 'Blueprint' }}</h6>
                    <span class="badge" style="background:{{ $assignment->role_color }}">{{ $assignment->role_label }}</span>
                    <span class="badge bg-secondary">{{ \App\Models\PartnerBlueprintAssignment::STATUSES[$assignment->status] ?? $assignment->status }}</span>
                    <small class="text-muted ms-2">{{ $assignment->enrollments->count() }} enrolled</small>
                </div>
                <div>
                    @php $aPct = $assignment->completionPercentage(); @endphp
                    <div class="progress" style="width:120px;height:8px"><div class="progress-bar" style="width:{{ $aPct }}%"></div></div>
                    <small class="text-muted">{{ $aPct }}%</small>
                </div>
            </div>
        </div>
        @empty
        <p class="text-muted mb-0">No blueprint assignments.</p>
        @endforelse
    </div>
</div>
@endsection
