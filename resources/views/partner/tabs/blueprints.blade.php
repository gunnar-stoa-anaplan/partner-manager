{{-- Blueprints Tab: Full blueprint assignment detail with enrollment progress --}}

@php
    $enrolledContactIds = $assignments->flatMap(fn($a) => $a->enrollments->pluck('contact_id'))->unique();
    $assignmentsWithEnrollments = $assignments->filter(fn($a) => $a->enrollments->count() > 0);
    $avgCompletion = $assignmentsWithEnrollments->count() > 0
        ? round($assignmentsWithEnrollments->avg(fn($a) => $a->completionPercentage()), 1)
        : 0;
    $completedAssignments = $assignmentsWithEnrollments->filter(fn($a) => $a->completionPercentage() >= 100)->count();
@endphp

{{-- Summary KPI Cards --}}
<div class="row mb-g">
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Total Assignments</p>
                    <span class="h3 mb-0 fw-500">{{ $assignments->count() }}</span>
                </div>
                <div class="width-5 height-5 bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-layers fa-lg text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Enrolled Contacts</p>
                    <span class="h3 mb-0 fw-500">{{ $enrolledContactIds->count() }}</span>
                </div>
                <div class="width-5 height-5 bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-users fa-lg text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Avg Completion</p>
                    <span class="h3 mb-0 fw-500">{{ $avgCompletion }}%</span>
                </div>
                <div class="width-5 height-5 bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-chart-pie fa-lg text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Completed</p>
                    <span class="h3 mb-0 fw-500">{{ $completedAssignments }} <small class="text-muted fw-400">/ {{ $assignments->count() }}</small></span>
                </div>
                <div class="width-5 height-5 bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fal fa-check-circle fa-lg text-success"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Role Filter + Quick Link --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group" id="blueprintRoleFilter">
        <button type="button" class="btn btn-sm btn-outline-primary active" data-filter="all">All Roles</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="gtm" style="border-color:#fd7e14;color:#fd7e14">GTM (Sales)</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="pre_sales" style="border-color:#886ab5;color:#886ab5">Pre-Sales</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-filter="delivery" style="border-color:#00BCD4;color:#00BCD4">Delivery</button>
    </div>
    <a href="{{ route('partners.blueprints', $partner->id) }}" class="btn btn-outline-warning btn-sm">
        <i class="fal fa-external-link me-1"></i> Open Full Blueprint Progress
    </a>
</div>

{{-- Blueprint Assignments — Accordion --}}
@if($assignments->count())
    <div class="accordion" id="blueprintAccordion">
        @foreach($assignments as $assignment)
            @php
                $pct = $assignment->completionPercentage();
                $progressColor = $pct >= 100 ? 'success' : ($pct >= 50 ? 'info' : ($pct > 0 ? 'warning' : 'secondary'));
                $applicableCount = $assignment->applicableContentItemCount();
                $catColors = ['application' => 'info', 'product' => 'warning', 'delivery' => 'primary'];
            @endphp
            <div class="accordion-item shadow-0 mb-g border rounded blueprint-accordion-item" data-role="{{ $assignment->role }}">
                <h2 class="accordion-header" id="bpHead{{ $assignment->id }}">
                    <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#bpBody{{ $assignment->id }}" aria-expanded="false" aria-controls="bpBody{{ $assignment->id }}">
                        <div class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap me-2">
                            <span class="fw-500">{{ $assignment->blueprint->name }}</span>
                            <span class="badge bg-{{ $catColors[$assignment->blueprint->category] ?? 'secondary' }} opacity-75 fw-400">
                                {{ \App\Models\Blueprint::CATEGORIES[$assignment->blueprint->category] ?? $assignment->blueprint->category }}
                            </span>
                            <span class="badge" style="background-color: {{ \App\Models\PartnerBlueprintAssignment::ROLE_COLORS[$assignment->role] ?? '#868e96' }}">
                                {{ \App\Models\PartnerBlueprintAssignment::ROLES[$assignment->role] ?? $assignment->role }}
                            </span>
                            <span class="badge bg-{{ $assignment->status === 'active' ? 'success' : ($assignment->status === 'paused' ? 'warning' : 'info') }}">
                                {{ ucfirst($assignment->status) }}
                            </span>
                            <div class="d-flex align-items-center gap-1" style="min-width: 140px;">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <small class="text-muted text-nowrap">{{ $pct }}%</small>
                            </div>
                            <small class="text-muted text-nowrap"><i class="fal fa-users me-1"></i>{{ $assignment->enrollments->count() }}</small>
                        </div>
                    </button>
                </h2>
                <div id="bpBody{{ $assignment->id }}" class="accordion-collapse collapse" aria-labelledby="bpHead{{ $assignment->id }}" data-bs-parent="#blueprintAccordion">
                    <div class="accordion-body">
                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-end gap-2 mb-3">
                            <a href="{{ route('blueprint-assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fal fa-chart-bar me-1"></i> Details
                            </a>
                            <a href="{{ route('blueprints.detail', $assignment->blueprint) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fal fa-eye me-1"></i> Blueprint
                            </a>
                        </div>

                        {{-- Assignment Meta Row --}}
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <small class="text-muted d-block">Role</small>
                                <span class="badge" style="background-color: {{ \App\Models\PartnerBlueprintAssignment::ROLE_COLORS[$assignment->role] ?? '#868e96' }}">
                                    {{ \App\Models\PartnerBlueprintAssignment::ROLES[$assignment->role] ?? $assignment->role }}
                                </span>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Status</small>
                                <span class="badge bg-{{ $assignment->status === 'active' ? 'success' : ($assignment->status === 'paused' ? 'warning' : 'info') }}">
                                    {{ ucfirst($assignment->status) }}
                                </span>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Content Items</small>
                                <strong>{{ $applicableCount }}</strong>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Enrolled</small>
                                <strong>{{ $assignment->enrollments->count() }}</strong> contacts
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Assigned By</small>
                                <span>{{ $assignment->assignedBy->name }}</span>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Since</small>
                                <span>{{ $assignment->created_at->format('M j, Y') }}</span>
                            </div>
                        </div>

                        {{-- Overall Progress Bar --}}
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <small class="text-muted text-nowrap fw-500">Overall:</small>
                            <div class="progress flex-grow-1" style="height: 10px;">
                                <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $pct }}%"></div>
                            </div>
                            <small class="text-muted text-nowrap fw-500">{{ $pct }}%</small>
                        </div>

                        {{-- Enrolled Contacts --}}
                        @if($assignment->enrollments->count())
                            <div class="border-top pt-3">
                                <small class="text-muted fw-500 d-block mb-2">Enrolled Contacts</small>
                                @foreach($assignment->enrollments as $enrollment)
                                    @php
                                        $ePct = $enrollment->completionPercentage();
                                        $eCompleted = $enrollment->completedItemsCount();
                                        $eTotal = $enrollment->totalApplicableItems();
                                        $eColor = $ePct >= 100 ? 'success' : ($ePct >= 50 ? 'info' : ($ePct > 0 ? 'warning' : 'secondary'));
                                    @endphp
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div style="min-width: 160px;">
                                            <a href="{{ route('blueprint-enrollments.show', $enrollment) }}" class="text-dark">
                                                {{ $enrollment->contact->first_name }} {{ $enrollment->contact->last_name }}
                                            </a>
                                        </div>
                                        <span class="badge" style="background-color: {{ \App\Models\BlueprintEnrollment::STATUS_COLORS[$enrollment->status] ?? '#868e96' }}; min-width: 80px;">
                                            {{ \App\Models\BlueprintEnrollment::STATUSES[$enrollment->status] ?? $enrollment->status }}
                                        </span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $eColor }}" style="width: {{ $ePct }}%"></div>
                                        </div>
                                        <small class="text-muted text-nowrap">{{ $eCompleted }}/{{ $eTotal }} ({{ $ePct }}%)</small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3 text-muted border-top">
                                <i class="fal fa-user-plus me-1"></i> No contacts enrolled yet.
                                <a href="{{ route('blueprint-assignments.show', $assignment) }}">Enroll contacts</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        (function() {
            var filterBtns = document.querySelectorAll('#blueprintRoleFilter button');
            var items = document.querySelectorAll('.blueprint-accordion-item');

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
@else
    <div class="row">
        <div class="col-12">
            <div class="card shadow-0">
                <div class="card-body text-center py-5">
                    <div class="mb-3"><i class="fal fa-layer-group fa-3x text-muted opacity-50"></i></div>
                    <h5 class="text-muted">No blueprints assigned yet</h5>
                    <p class="text-muted mb-3">Assign enablement blueprints to {{ $partner->name }} to track their readiness progression.</p>
                    <a href="{{ route('blueprints.catalog') }}" class="btn btn-primary btn-sm">
                        <i class="fal fa-layers me-1"></i> Browse Blueprints
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
