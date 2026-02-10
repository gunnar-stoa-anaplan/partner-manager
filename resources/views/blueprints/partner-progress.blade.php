@extends('layouts.vertical', ['pageTitle' => 'Blueprint Progress'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Blueprint Progress', 'pageSubTitle1' => $assignment->partner->name, 'pageSubText' => $assignment->blueprint->name . ' - ' . \App\Models\PartnerBlueprintAssignment::ROLES[$assignment->role]])
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Assignment Summary --}}
    <div class="row mb-g">
        <div class="col-lg-12">
            <div class="card shadow-0">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        {{ $assignment->partner->name }} &mdash; {{ $assignment->blueprint->name }}
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#enrollContactModal">
                            <i class="fal fa-user-plus me-1"></i> Enroll Contacts
                        </button>
                        <a href="{{ route('blueprints.detail', $assignment->blueprint) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fal fa-arrow-left me-1"></i> Back to Blueprint
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <small class="text-muted d-block">Role</small>
                            <span class="badge" style="background-color: {{ \App\Models\PartnerBlueprintAssignment::ROLE_COLORS[$assignment->role] ?? '#868e96' }}">
                                {{ \App\Models\PartnerBlueprintAssignment::ROLES[$assignment->role] ?? $assignment->role }}
                            </span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Applicable Items</small>
                            <strong>{{ $applicableItems->count() }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Enrolled Contacts</small>
                            <strong>{{ $assignment->enrollments->count() }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge bg-{{ $assignment->status === 'active' ? 'success' : ($assignment->status === 'paused' ? 'warning' : 'info') }}">
                                {{ ucfirst($assignment->status) }}
                            </span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Assigned By</small>
                            <span>{{ $assignment->assignedBy->name }}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Assigned On</small>
                            <span>{{ $assignment->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Enrolled Contacts with Progress --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Enrolled Contacts</div>
                </div>
                <div class="card-body">
                    @forelse($assignment->enrollments as $enrollment)
                        @php
                            $pct = $enrollment->completionPercentage();
                            $completedCount = $enrollment->completedItemsCount();
                            $totalCount = $enrollment->totalApplicableItems();
                            $progressColor = $pct >= 100 ? 'success' : ($pct >= 50 ? 'info' : ($pct > 0 ? 'warning' : 'secondary'));
                        @endphp
                        <div class="border rounded mb-3 p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="flex-grow-1">
                                    <strong>{{ $enrollment->contact->first_name }} {{ $enrollment->contact->last_name }}</strong>
                                    @if($enrollment->contact->title)
                                        <small class="text-muted ms-2">{{ $enrollment->contact->title }}</small>
                                    @endif
                                    <span class="badge ms-2" style="background-color: {{ \App\Models\BlueprintEnrollment::STATUS_COLORS[$enrollment->status] ?? '#868e96' }}">
                                        {{ \App\Models\BlueprintEnrollment::STATUSES[$enrollment->status] ?? $enrollment->status }}
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('blueprint-enrollments.show', $enrollment) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fal fa-tasks me-1"></i> Track Progress
                                    </a>
                                    <form action="{{ route('blueprint-enrollments.destroy', $enrollment) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this enrollment?')">
                                            <i class="fal fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <small class="text-muted text-nowrap">{{ $completedCount }}/{{ $totalCount }} ({{ $pct }}%)</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <div class="mb-3"><i class="fal fa-users fa-3x text-muted opacity-50"></i></div>
                            <h5 class="text-muted">No contacts enrolled yet</h5>
                            <p class="text-muted mb-3">Enroll contacts from {{ $assignment->partner->name }} to track their progress.</p>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#enrollContactModal">
                                <i class="fal fa-user-plus me-1"></i> Enroll Contacts
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Enroll Contact Modal --}}
    <div class="modal fade" id="enrollContactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('blueprint-assignments.enroll', $assignment) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Enroll Contacts from {{ $assignment->partner->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @php
                            $enrolledIds = $assignment->enrollments->pluck('contact_id')->toArray();
                            $availableContacts = $assignment->partner->contacts->whereNotIn('id', $enrolledIds);
                        @endphp
                        @if($availableContacts->count())
                            <p class="text-muted small">Select contacts to enroll in this blueprint:</p>
                            @foreach($availableContacts as $contact)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="contact_ids[]" value="{{ $contact->id }}" id="contact_{{ $contact->id }}">
                                    <label class="form-check-label" for="contact_{{ $contact->id }}">
                                        {{ $contact->first_name }} {{ $contact->last_name }}
                                        @if($contact->title)
                                            <small class="text-muted">- {{ $contact->title }}</small>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info mb-0">
                                All contacts from {{ $assignment->partner->name }} are already enrolled.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        @if($availableContacts->count())
                            <button type="submit" class="btn btn-primary">Enroll Selected</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
