@extends('layouts.vertical', ['pageTitle' => $dealRegistration->deal_name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $dealRegistration->deal_name, 'pageSubTitle1' => 'Deal Registrations', 'pageSubTitle1Url' => route('engage.deal-registrations.index')])
@endsection

@section('content')
@php
    $color = \App\Models\DealRegistration::STATUS_COLORS[$dealRegistration->status] ?? 'secondary';
@endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row mt-3">
    {{-- Main Details --}}
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">Deal Details</div>
                <span class="badge bg-{{ $color }} fs-sm">{{ \App\Models\DealRegistration::STATUSES[$dealRegistration->status] ?? $dealRegistration->status }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-0">Partner</label>
                        <p class="fw-500">{{ $dealRegistration->partner->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-0">Submitted By</label>
                        <p class="fw-500">{{ $dealRegistration->contact?->full_name ?? 'PSM / Admin' }}</p>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-0">Customer</label>
                        <p class="fw-500">{{ $dealRegistration->customer_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-0">Customer Email</label>
                        <p>{{ $dealRegistration->customer_contact_email ?? '—' }}</p>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-0">Estimated Value</label>
                        <p class="fw-500">${{ number_format($dealRegistration->estimated_value) }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-0">Expected Close</label>
                        <p>{{ $dealRegistration->expected_close_date?->format('M d, Y') ?? '—' }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-0">Product Area</label>
                        <p>{{ $dealRegistration->product_area ?? '—' }}</p>
                    </div>
                </div>
                @if($dealRegistration->description)
                <div class="mb-3">
                    <label class="form-label text-muted mb-0">Description</label>
                    <p>{{ $dealRegistration->description }}</p>
                </div>
                @endif
            </div>
        </div>

        @if($dealRegistration->opportunity)
        <div class="card shadow-0 mb-g border-left-success">
            <div class="card-body">
                <h5 class="card-title text-success">Linked Opportunity</h5>
                <p class="mb-1"><strong>{{ $dealRegistration->opportunity->opportunity_name }}</strong></p>
                <p class="text-muted mb-0">Stage: {{ $dealRegistration->opportunity->stage }} | Amount: ${{ number_format($dealRegistration->opportunity->amount) }}</p>
            </div>
        </div>
        @endif

        @if($dealRegistration->rejection_reason)
        <div class="card shadow-0 mb-g border-left-danger">
            <div class="card-body">
                <h5 class="card-title text-danger">Rejection Reason</h5>
                <p class="mb-0">{{ $dealRegistration->rejection_reason }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Timeline --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Timeline</div></div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-secondary me-2">1</span>
                    <div>
                        <strong>Submitted</strong><br>
                        <small class="text-muted">{{ $dealRegistration->submitted_at?->format('M d, Y H:i') ?? '—' }}</small>
                    </div>
                </div>
                @if($dealRegistration->reviewed_at)
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-{{ $dealRegistration->status === 'approved' ? 'success' : 'danger' }} me-2">2</span>
                    <div>
                        <strong>{{ $dealRegistration->status === 'approved' ? 'Approved' : 'Rejected' }}</strong><br>
                        <small class="text-muted">{{ $dealRegistration->reviewed_at->format('M d, Y H:i') }}</small><br>
                        <small class="text-muted">By: {{ $dealRegistration->reviewer?->name ?? 'Unknown' }}</small>
                    </div>
                </div>
                @endif
                @if($dealRegistration->expires_at && !$dealRegistration->reviewed_at)
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-warning me-2">!</span>
                    <div>
                        <strong>Expires</strong><br>
                        <small class="{{ $dealRegistration->days_until_expiry <= 14 ? 'text-danger' : 'text-muted' }}">
                            {{ $dealRegistration->expires_at->format('M d, Y') }}
                            ({{ $dealRegistration->days_until_expiry }} days)
                        </small>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Review Actions --}}
        @if(in_array($dealRegistration->status, ['submitted', 'under_review']))
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Review Actions</div></div>
            <div class="card-body">
                <form action="{{ route('engage.deal-registrations.review', $dealRegistration) }}" method="POST" class="mb-2">
                    @csrf @method('PUT')
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn btn-success w-100 mb-2">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#check"></use></svg> Approve & Create Opportunity
                    </button>
                </form>
                <form action="{{ route('engage.deal-registrations.review', $dealRegistration) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="action" value="reject">
                    <div class="mb-2">
                        <textarea name="rejection_reason" class="form-control form-control-sm" rows="2" placeholder="Reason for rejection..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#x"></use></svg> Reject
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
