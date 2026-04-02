@extends('layouts.vertical', ['pageTitle' => 'Resource Paths'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Resource Paths', 'pageSubTitle1' => 'Plan', 'pageSubTitle1Url' => route('welcome'), 'pageSubText' => 'Track individual resource progression across partner organizations'])
@endsection

@section('content')

{{-- KPI Row --}}
<div class="row mb-g">
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Total Enrolled</p>
                    <span class="h3 mb-0 fw-500">{{ $totalEnrolled }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-users fa-lg text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Active Paths</p>
                    <span class="h3 mb-0 fw-500">{{ $paths->count() }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-map-signs fa-lg text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Avg Days in Stage</p>
                    <span class="h3 mb-0 fw-500">{{ $avgDaysInStage }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-clock fa-lg text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">By Path</p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($byPathCounts as $pathName => $count)
                        <span class="badge bg-light text-dark">{{ $pathName }}: {{ $count }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="card shadow-0 mb-g">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('plan.resource-paths.index') }}" class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                @if($canSeeAll && $psmUsers->isNotEmpty())
                <label class="form-label mb-0 fw-500 text-muted">PSM:</label>
                <select name="psm_id" class="form-select form-select-sm" style="width: 180px;" onchange="this.form.submit()">
                    <option value="">All PSMs</option>
                    @foreach($psmUsers as $psm)
                        <option value="{{ $psm->id }}" {{ $selectedPsmId == $psm->id ? 'selected' : '' }}>{{ $psm->name }}</option>
                    @endforeach
                </select>
                @endif

                <label class="form-label mb-0 fw-500 text-muted">Partner:</label>
                <select name="partner_id" class="form-select form-select-sm" style="width: 220px;" onchange="this.form.submit()">
                    <option value="">All Partners</option>
                    @foreach($partnersForDropdown as $p)
                        <option value="{{ $p->id }}" {{ $selectedPartnerId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>

                <label class="form-label mb-0 fw-500 text-muted">Path:</label>
                <select name="path_id" class="form-select form-select-sm" style="width: 200px;" onchange="this.form.submit()">
                    <option value="">All Paths</option>
                    @foreach($paths as $p)
                        <option value="{{ $p->id }}" {{ $selectedPathId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>

                @if($selectedPartnerId || $selectedPsmId || $selectedPathId)
                    <a href="{{ route('plan.resource-paths.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fal fa-times me-1"></i>Clear
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Enrollments by Path --}}
@forelse($enrollmentsByPath as $pathId => $pathEnrollments)
@php
    $path = $paths->firstWhere('id', $pathId);
    $pathStages = $path ? $path->stages->sortBy('sort_order') : collect();
@endphp
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title">
            {{ $path->name ?? 'Unknown Path' }}
            <span class="badge bg-primary ms-2">{{ $pathEnrollments->count() }} enrolled</span>
        </div>
        <div class="d-flex align-items-center gap-1">
            @foreach($pathStages as $stage)
                @php $stageCount = $pathEnrollments->where('current_stage_id', $stage->id)->count(); @endphp
                <span class="badge" style="background-color: {{ $stage->color }}" title="{{ $stage->name }}">{{ $stage->name }}: {{ $stageCount }}</span>
            @endforeach
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>Resource</th>
                    <th>Partner</th>
                    <th>Current Stage</th>
                    <th class="text-center">Days in Stage</th>
                    <th>Enrolled</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($pathEnrollments->sortBy(fn($e) => $e->currentStage->stage_number) as $enrollment)
                <tr>
                    <td>
                        <div class="fw-500">{{ $enrollment->contact->full_name }}</div>
                        <small class="text-muted">{{ $enrollment->contact->resource_role ? \App\Models\Contact::RESOURCE_ROLES[$enrollment->contact->resource_role] ?? $enrollment->contact->resource_role : $enrollment->contact->contact_type }}</small>
                    </td>
                    <td>{{ $enrollment->partner->name }}</td>
                    <td>
                        <span class="badge" style="background-color: {{ $enrollment->currentStage->color }}">
                            <i class="{{ $enrollment->currentStage->icon ?? 'fal fa-circle' }} me-1"></i>{{ $enrollment->currentStage->name }}
                        </span>
                    </td>
                    <td class="text-center">
                        @php $daysInStage = (int) $enrollment->stage_entered_at->diffInDays(now()); @endphp
                        <span class="{{ $daysInStage > 90 ? 'text-danger fw-bold' : ($daysInStage > 60 ? 'text-warning' : '') }}">{{ $daysInStage }}d</span>
                    </td>
                    <td><small class="text-muted">{{ $enrollment->enrolled_at->format('M j, Y') }}</small></td>
                    <td>
                        <span class="badge bg-{{ $enrollment->status === 'active' ? 'success' : ($enrollment->status === 'paused' ? 'warning' : 'secondary') }}">{{ ucfirst($enrollment->status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('plan.resource-paths.show', $enrollment) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fal fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@empty
<div class="card shadow-0">
    <div class="card-body text-center py-5">
        <i class="fal fa-users fa-3x text-muted mb-3"></i>
        <h5>No Resource Enrollments Found</h5>
        <p class="text-muted">No resources match the current filters. Adjust filters or enroll resources in a path from the partner detail page.</p>
    </div>
</div>
@endforelse

@endsection
