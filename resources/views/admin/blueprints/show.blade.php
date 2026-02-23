@extends('layouts.vertical', ['pageTitle' => $blueprint->name])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $blueprint->name, 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubTitle2' => 'Enablement Blueprints', 'pageSubTitle2Url' => route('blueprints.index'), 'pageSubText' => \App\Models\Blueprint::CATEGORIES[$blueprint->category] ?? ''])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">This is the blueprint detail view, showing the full structure of outcomes and content items. Use it to review the learning path, assign the blueprint to partners, and track how contacts are progressing through each outcome. From here you can also edit the blueprint or manage its content items.</p>

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
                        <a href="{{ route('blueprints.export', $blueprint) }}" class="btn btn-sm btn-outline-success">
                            <i class="fal fa-file-powerpoint me-1"></i> Export PPTX
                        </a>
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
                            @php $catColors = ['application' => 'info', 'product' => 'warning', 'delivery' => 'primary']; @endphp
                            <span class="badge bg-{{ $catColors[$blueprint->category] ?? 'secondary' }}">
                                {{ \App\Models\Blueprint::CATEGORIES[$blueprint->category] ?? $blueprint->category }}
                            </span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Product Area</small>
                            <strong>{{ $blueprint->product_area ?? '-' }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Status</small>
                            @php $statusColors = ['draft' => 'secondary', 'submitted' => 'info', 'published' => 'success', 'archived' => 'warning']; @endphp
                            <span class="badge bg-{{ $statusColors[$blueprint->status] ?? 'secondary' }}">{{ ucfirst($blueprint->status) }}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Owner</small>
                            <strong>{{ $blueprint->owner->name ?? '-' }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Date Published</small>
                            <strong>{{ $blueprint->published_at ? $blueprint->published_at->format('M j, Y') : '-' }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Content Items</small>
                            <strong>{{ $blueprint->outcomes->sum(fn($o) => $o->contentItems->count()) }}</strong>
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
            <div class="accordion" id="outcomesAccordion">
                @foreach($blueprint->outcomes as $outcome)
                    <div class="accordion-item shadow-0 mb-g border rounded">
                        <h2 class="accordion-header" id="outcomeHead{{ $outcome->id }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#outcomeBody{{ $outcome->id }}" aria-expanded="false" aria-controls="outcomeBody{{ $outcome->id }}">
                                <span class="badge bg-primary me-2">{{ $outcome->sort_order }}</span>
                                {{ $outcome->title }}
                                <small class="text-muted ms-2">({{ $outcome->contentItems->count() }} items)</small>
                            </button>
                        </h2>
                        <div id="outcomeBody{{ $outcome->id }}" class="accordion-collapse collapse" aria-labelledby="outcomeHead{{ $outcome->id }}" data-bs-parent="#outcomesAccordion">
                            <div class="accordion-body p-0">
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
                    </div>
                @endforeach
            </div>
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
                            <table id="blueprint-assignments-table" class="table table-bordered table-hover mb-0">
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
    @vite(['resources/scripts/pages/table-init.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.querySelector('#blueprint-assignments-table');
            if (window.SmartTables && el && el.querySelector('tbody tr td:not([colspan])')) {
                new window.SmartTables('blueprint-assignments-table', { perPage: 10, sortable: true, searchable: true });
            }
        });
    </script>
@endsection
