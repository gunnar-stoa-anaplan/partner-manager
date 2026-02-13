@extends('layouts.vertical', ['pageTitle' => 'Deal Registrations'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Deal Registrations', 'pageSubTitle1' => 'Engage', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Deal Registration enables partners to register new sales opportunities for review, approval, and co-sell support. PSMs manage the approval workflow and can track registered deals through the sales lifecycle.</p>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Stats --}}
<div class="row mb-g">
    <div class="col-md-4">
        <div class="card shadow-0 mb-g border-top border-3 border-primary">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0">{{ $stats['submitted'] }}</h2>
                <small class="text-muted">Pending Review</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 mb-g border-top border-3 border-success">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0">{{ $stats['approved'] }}</h2>
                <small class="text-muted">Approved</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 mb-g border-top border-3 border-info">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0">${{ number_format($stats['total_value']) }}</h2>
                <small class="text-muted">Approved Value</small>
            </div>
        </div>
    </div>
</div>

{{-- Actions & Filter --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
        @foreach(\App\Models\DealRegistration::STATUSES as $sKey => $sLabel)
        <a href="{{ route('engage.deal-registrations.index', ['status' => $sKey]) }}"
           class="btn btn-sm {{ request('status') === $sKey ? 'btn-'.\App\Models\DealRegistration::STATUS_COLORS[$sKey] : 'btn-outline-secondary' }}">
            {{ $sLabel }}
        </a>
        @endforeach
        <a href="{{ route('engage.deal-registrations.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
    </div>
    <a href="{{ route('engage.deal-registrations.create') }}" class="btn btn-primary btn-sm">
        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> Register Deal
    </a>
</div>

{{-- Deals Table --}}
<div class="card shadow-0 mb-g">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Deal Name</th>
                        <th>Partner</th>
                        <th>Customer</th>
                        <th class="text-end">Est. Value</th>
                        <th>Product Area</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Expires</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($deals as $deal)
                    <tr>
                        <td>
                            <a href="{{ route('engage.deal-registrations.show', $deal) }}" class="fw-500 text-decoration-none">
                                {{ $deal->deal_name }}
                            </a>
                        </td>
                        <td>{{ $deal->partner->name }}</td>
                        <td>{{ $deal->customer_name }}</td>
                        <td class="text-end">${{ number_format($deal->estimated_value) }}</td>
                        <td>{{ $deal->product_area ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ \App\Models\DealRegistration::STATUS_COLORS[$deal->status] ?? 'secondary' }}">
                                {{ \App\Models\DealRegistration::STATUSES[$deal->status] ?? $deal->status }}
                            </span>
                        </td>
                        <td>{{ $deal->submitted_at?->format('M d, Y') ?? '—' }}</td>
                        <td>
                            @if($deal->expires_at && $deal->status !== 'expired' && $deal->status !== 'approved')
                                @if($deal->days_until_expiry <= 14)
                                    <span class="text-danger">{{ $deal->expires_at->format('M d') }}</span>
                                @else
                                    {{ $deal->expires_at->format('M d') }}
                                @endif
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No deal registrations found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{ $deals->links() }}
@endsection
