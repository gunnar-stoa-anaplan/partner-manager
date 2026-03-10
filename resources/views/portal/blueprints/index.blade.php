@extends('portal.layout', ['pageTitle' => 'Blueprints'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Blueprints — {{ $partner->name }}</h4>
    <div class="btn-group" id="portalRoleFilter">
        <button type="button" class="btn btn-sm btn-outline-primary active" data-filter="all">All Roles</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="gtm" style="border-color:#fd7e14;color:#fd7e14">GTM (Sales)</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="pre_sales" style="border-color:#886ab5;color:#886ab5">Pre-Sales</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="delivery" style="border-color:#00BCD4;color:#00BCD4">Delivery</button>
    </div>
</div>

{{-- My Enrollments --}}
@if($myEnrollments->isNotEmpty())
<div class="card portal-card mb-4">
    <div class="card-header"><strong>My Enrollments</strong></div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($myEnrollments as $enrollment)
            <div class="col-md-4 portal-role-item" data-role="{{ $enrollment->assignment?->role }}">
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
        <a href="{{ route('portal.blueprints.assignment', $assignment) }}" class="text-decoration-none text-body d-block border-bottom py-3 portal-role-item" data-role="{{ $assignment->role }}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">{{ $assignment->blueprint?->name ?? 'Blueprint' }}</h6>
                    <span class="badge" style="background:{{ $assignment->role_color }}">{{ $assignment->role_label }}</span>
                    <span class="badge bg-secondary">{{ \App\Models\PartnerBlueprintAssignment::STATUSES[$assignment->status] ?? $assignment->status }}</span>
                    <small class="text-muted ms-2">{{ $assignment->enrollments->count() }} enrolled</small>
                </div>
                <div class="text-end">
                    @php $aPct = $assignment->completionPercentage(); @endphp
                    <div class="progress" style="width:120px;height:8px"><div class="progress-bar bg-{{ $aPct >= 80 ? 'success' : ($aPct >= 40 ? 'warning' : 'danger') }}" style="width:{{ $aPct }}%"></div></div>
                    <small class="text-muted">{{ $aPct }}%</small>
                </div>
            </div>
        </a>
        @empty
        <p class="text-muted mb-0">No blueprint assignments.</p>
        @endforelse
    </div>
</div>

<script>
    (function() {
        var filterBtns = document.querySelectorAll('#portalRoleFilter button');
        var items = document.querySelectorAll('.portal-role-item');

        filterBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var filter = this.dataset.filter;

                filterBtns.forEach(function(b) {
                    b.classList.remove('active');
                    b.style.backgroundColor = '';
                    b.style.color = b.dataset.filter === 'all' ? '' : b.style.borderColor;
                });
                this.classList.add('active');
                if (filter !== 'all') {
                    this.style.backgroundColor = this.style.borderColor;
                    this.style.color = '#fff';
                }

                items.forEach(function(item) {
                    if (filter === 'all' || item.dataset.role === filter) {
                        item.classList.remove('d-none');
                    } else {
                        item.classList.add('d-none');
                    }
                });
            });
        });
    })();
</script>
@endsection
