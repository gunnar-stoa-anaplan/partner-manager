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

    {{-- Role Legend --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex gap-3 small">
                <span><i class="fas fa-check-circle text-success me-1"></i> Required</span>
                <span><i class="fas fa-check-circle text-muted me-1"></i> Optional</span>
                <span class="text-muted">|</span>
                <span style="color: #fd7e14;"><i class="fas fa-circle me-1 small"></i> GTM (Sales)</span>
                <span style="color: #886ab5;"><i class="fas fa-circle me-1 small"></i> Pre-Sales</span>
                <span style="color: #00BCD4;"><i class="fas fa-circle me-1 small"></i> Delivery</span>
            </div>
        </div>
    </div>

    {{-- Outcomes --}}
    @foreach($blueprint->outcomes as $outcome)
        <div class="row">
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
                                            <th class="text-center" style="width:60px; color:#fd7e14">GTM</th>
                                            <th class="text-center" style="width:60px; color:#886ab5">Pre-S</th>
                                            <th class="text-center" style="width:60px; color:#00BCD4">Del</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($outcome->contentItems as $item)
                                            <tr>
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
                                                <td class="text-center">
                                                    @if($item->gtm_applicable === 'required')
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($item->gtm_applicable === 'optional')
                                                        <i class="fas fa-check-circle text-muted"></i>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($item->pre_sales_applicable === 'required')
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($item->pre_sales_applicable === 'optional')
                                                        <i class="fas fa-check-circle text-muted"></i>
                                                    @endif
                                                </td>
                                                <td class="text-center">
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
                                        <tr>
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
@endsection
