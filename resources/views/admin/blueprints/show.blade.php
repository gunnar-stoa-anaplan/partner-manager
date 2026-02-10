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

    {{-- Blueprint Info Card --}}
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
                        <a href="{{ route('blueprints.edit', $blueprint) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fal fa-pencil me-1"></i> Edit
                        </a>
                        <a href="{{ route('blueprints.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fal fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <small class="text-muted d-block">Category</small>
                            <span class="badge bg-{{ $blueprint->category === 'application' ? 'info' : 'warning' }}">
                                {{ \App\Models\Blueprint::CATEGORIES[$blueprint->category] ?? $blueprint->category }}
                            </span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Product Area</small>
                            <strong>{{ $blueprint->product_area ?? '-' }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Status</small>
                            @php $statusColors = ['draft' => 'secondary', 'published' => 'success', 'archived' => 'warning']; @endphp
                            <span class="badge bg-{{ $statusColors[$blueprint->status] ?? 'secondary' }}">{{ ucfirst($blueprint->status) }}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Outcomes</small>
                            <strong>{{ $blueprint->outcomes->count() }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Content Items</small>
                            <strong>{{ $blueprint->outcomes->sum(fn($o) => $o->contentItems->count()) }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Assignments</small>
                            <strong>{{ $blueprint->assignments->count() }}</strong>
                        </div>
                    </div>
                    @if($blueprint->description)
                        <div class="mt-3">
                            <small class="text-muted d-block">Description</small>
                            <p class="mb-0">{{ $blueprint->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Outcomes with Content Items --}}
    <div class="row">
        <div class="col-lg-12">
            @foreach($blueprint->outcomes as $outcome)
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
                                            <th class="text-center" style="width:70px">
                                                <span class="d-inline-block" style="color: #fd7e14;" title="GTM (Sales)">GTM</span>
                                            </th>
                                            <th class="text-center" style="width:70px">
                                                <span class="d-inline-block" style="color: #886ab5;" title="Pre-Sales">Pre-S</span>
                                            </th>
                                            <th class="text-center" style="width:70px">
                                                <span class="d-inline-block" style="color: #00BCD4;" title="Delivery">Del</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($outcome->contentItems as $item)
                                            <tr>
                                                <td>
                                                    @if($item->url)
                                                        <a href="{{ $item->url }}" target="_blank" class="text-primary">
                                                            {{ $item->title }}
                                                            <i class="fal fa-external-link ms-1 small"></i>
                                                        </a>
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
                                                        <i class="fas fa-check-circle text-muted" title="Optional"></i>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($item->pre_sales_applicable === 'required')
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($item->pre_sales_applicable === 'optional')
                                                        <i class="fas fa-check-circle text-muted" title="Optional"></i>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($item->delivery_applicable === 'required')
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($item->delivery_applicable === 'optional')
                                                        <i class="fas fa-check-circle text-muted" title="Optional"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-3 text-muted text-center">No content items for this outcome.</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Assignments --}}
    @if($blueprint->assignments->count())
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-0 mb-g">
                    <div class="card-header">
                        <div class="card-title">Partner Assignments ({{ $blueprint->assignments->count() }})</div>
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
                                        <th>Assigned</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($blueprint->assignments as $assignment)
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
                                            <td>{{ $assignment->created_at->format('M j, Y') }}</td>
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
@endsection

@section('scripts')
@endsection
