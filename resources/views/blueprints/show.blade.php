@extends('layouts.vertical', ['pageTitle' => $blueprint->name])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $blueprint->name, 'pageSubTitle1' => 'Enablement Blueprints', 'pageSubText' => \App\Models\Blueprint::CATEGORIES[$blueprint->category] ?? ''])
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Blueprint Info --}}
    <div class="row mb-g">
        <div class="col-lg-12">
            <div class="card shadow-0">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        {{ $blueprint->name }}
                        @if($blueprint->version)
                            <small class="text-muted ms-2">{{ $blueprint->version }}</small>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignPartnerModal">
                            <i class="fal fa-user-plus me-1"></i> Assign Partner
                        </button>
                        <a href="{{ route('blueprints.catalog') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fal fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Category</small>
                            @php $catColors = ['application' => 'info', 'product' => 'warning', 'delivery' => 'primary']; @endphp
                            <span class="badge bg-{{ $catColors[$blueprint->category] ?? 'secondary' }}">
                                {{ \App\Models\Blueprint::CATEGORIES[$blueprint->category] ?? $blueprint->category }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Product Area</small>
                            <strong>{{ $blueprint->product_area ?? '-' }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Outcomes</small>
                            <strong>{{ $blueprint->outcomes->count() }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Content Items</small>
                            <strong>{{ $blueprint->outcomes->sum(fn($o) => $o->contentItems->count()) }}</strong>
                        </div>
                    </div>
                    @if($blueprint->description)
                        <p class="text-muted mb-0">{{ $blueprint->description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Role Legend & Filter --}}
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div class="d-flex gap-3 small">
                <span><i class="fas fa-check-circle text-success me-1"></i> Required</span>
                <span><i class="fas fa-check-circle text-muted me-1"></i> Optional</span>
                <span class="text-muted">|</span>
                <span style="color: #fd7e14;"><i class="fas fa-circle me-1 small"></i> GTM (Sales)</span>
                <span style="color: #886ab5;"><i class="fas fa-circle me-1 small"></i> Pre-Sales</span>
                <span style="color: #00BCD4;"><i class="fas fa-circle me-1 small"></i> Delivery</span>
            </div>
            <div class="btn-group" id="roleFilter">
                <button type="button" class="btn btn-sm btn-outline-primary active" data-filter="all">All Roles</button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-filter="gtm" style="border-color:#fd7e14;color:#fd7e14">GTM (Sales)</button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-filter="pre_sales" style="border-color:#886ab5;color:#886ab5">Pre-Sales</button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-filter="delivery" style="border-color:#00BCD4;color:#00BCD4">Delivery</button>
            </div>
        </div>
    </div>

    {{-- Outcomes --}}
    @foreach($blueprint->outcomes as $outcome)
        <div class="row outcome-card">
            <div class="col-lg-12">
                <div class="card shadow-0 mb-g">
                    <div class="card-header">
                        <div class="card-title">
                            <span class="badge bg-primary me-2">{{ $outcome->sort_order }}</span>
                            {{ $outcome->title }}
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($outcome->contentItems->count())
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 40%">Content</th>
                                            <th>Time</th>
                                            <th>Source</th>
                                            <th>Prerequisites</th>
                                            <th class="text-center role-col role-col-gtm" style="width:60px; color:#fd7e14">GTM</th>
                                            <th class="text-center role-col role-col-pre_sales" style="width:60px; color:#886ab5">Pre-S</th>
                                            <th class="text-center role-col role-col-delivery" style="width:60px; color:#00BCD4">Del</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($outcome->contentItems as $item)
                                            <tr class="content-row" data-gtm="{{ $item->gtm_applicable }}" data-pre-sales="{{ $item->pre_sales_applicable }}" data-delivery="{{ $item->delivery_applicable }}">
                                                <td>
                                                    @if($item->url)
                                                        <a href="{{ $item->url }}" target="_blank">{{ $item->title }} <i class="fal fa-external-link ms-1 small"></i></a>
                                                    @else
                                                        {{ $item->title }}
                                                    @endif
                                                </td>
                                                <td class="text-nowrap">{{ $item->duration_display ?? '-' }}</td>
                                                <td>{{ $item->source ?? '-' }}</td>
                                                <td><small class="text-muted">{{ $item->prerequisites ?? '-' }}</small></td>
                                                <td class="text-center role-col role-col-gtm">
                                                    @if($item->gtm_applicable === 'required')
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($item->gtm_applicable === 'optional')
                                                        <i class="fas fa-check-circle text-muted"></i>
                                                    @endif
                                                </td>
                                                <td class="text-center role-col role-col-pre_sales">
                                                    @if($item->pre_sales_applicable === 'required')
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($item->pre_sales_applicable === 'optional')
                                                        <i class="fas fa-check-circle text-muted"></i>
                                                    @endif
                                                </td>
                                                <td class="text-center role-col role-col-delivery">
                                                    @if($item->delivery_applicable === 'required')
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($item->delivery_applicable === 'optional')
                                                        <i class="fas fa-check-circle text-muted"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- My Assignments for this Blueprint --}}
    @if($myAssignments->count())
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-0 mb-g">
                    <div class="card-header">
                        <div class="card-title">My Partner Assignments</div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Partner</th>
                                        <th>Role</th>
                                        <th>Enrolled Contacts</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myAssignments as $assignment)
                                        <tr class="assignment-row" data-role="{{ $assignment->role }}">
                                            <td>{{ $assignment->partner->name }}</td>
                                            <td>
                                                <span class="badge" style="background-color: {{ \App\Models\PartnerBlueprintAssignment::ROLE_COLORS[$assignment->role] ?? '#868e96' }}">
                                                    {{ \App\Models\PartnerBlueprintAssignment::ROLES[$assignment->role] ?? $assignment->role }}
                                                </span>
                                            </td>
                                            <td>{{ $assignment->enrollments->count() }}</td>
                                            <td>
                                                <span class="badge bg-{{ $assignment->status === 'active' ? 'success' : ($assignment->status === 'paused' ? 'warning' : 'info') }}">
                                                    {{ ucfirst($assignment->status) }}
                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                <a href="{{ route('blueprint-assignments.show', $assignment) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="fal fa-chart-bar me-1"></i> Progress
                                                </a>
                                                <form action="{{ route('blueprint-assignments.destroy', $assignment) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this assignment?')">
                                                        <i class="fal fa-times"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Assign Partner Modal --}}
    <div class="modal fade" id="assignPartnerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('blueprints.assign', $blueprint) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Partner to {{ $blueprint->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Partner <span class="text-danger">*</span></label>
                            <select class="form-select" name="partner_id" required>
                                <option value="">Select Partner...</option>
                                @foreach($myPartners as $partner)
                                    <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" required>
                                <option value="">Select Role...</option>
                                @foreach(\App\Models\PartnerBlueprintAssignment::ROLES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var filterBtns = document.querySelectorAll('#roleFilter button');
    var contentRows = document.querySelectorAll('.content-row');
    var assignmentRows = document.querySelectorAll('.assignment-row');
    var outcomeCards = document.querySelectorAll('.outcome-card');
    var roleCols = document.querySelectorAll('.role-col');
    var roles = ['gtm', 'pre_sales', 'delivery'];
    var attrMap = { gtm: 'data-gtm', pre_sales: 'data-pre-sales', delivery: 'data-delivery' };

    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var filter = this.dataset.filter;

            // Update active button styling
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

            // Show/hide role columns — when a role is selected, hide the other two columns
            roles.forEach(function(role) {
                var cols = document.querySelectorAll('.role-col-' + role);
                cols.forEach(function(col) {
                    col.classList.toggle('d-none', filter !== 'all' && filter !== role);
                });
            });

            // Filter content item rows — hide rows where selected role is 'na'
            contentRows.forEach(function(row) {
                if (filter === 'all') {
                    row.classList.remove('d-none');
                } else {
                    row.classList.toggle('d-none', row.getAttribute(attrMap[filter]) === 'na');
                }
            });

            // Hide outcome cards where all content rows are hidden (no applicable items)
            outcomeCards.forEach(function(card) {
                var rows = card.querySelectorAll('.content-row');
                if (filter === 'all' || rows.length === 0) {
                    card.classList.remove('d-none');
                } else {
                    var anyVisible = Array.from(rows).some(function(r) { return !r.classList.contains('d-none'); });
                    card.classList.toggle('d-none', !anyVisible);
                }
            });

            // Filter assignment rows
            assignmentRows.forEach(function(row) {
                if (filter === 'all') {
                    row.classList.remove('d-none');
                } else {
                    row.classList.toggle('d-none', row.getAttribute('data-role') !== filter);
                }
            });
        });
    });
});
</script>
@endsection
