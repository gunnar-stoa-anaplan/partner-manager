@extends('layouts.vertical', ['pageTitle' => $partner->name . ' — Marketplace Submission'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $partner->name, 'pageSubTitle1' => 'Marketplace Submissions', 'pageSubTitle1Url' => route('engage.marketplace-submissions.index')])
@endsection

@section('content')
@php
    $color = \App\Models\Partner::MARKETPLACE_STATUS_COLORS[$partner->marketplace_status] ?? 'secondary';
    $statusLabel = \App\Models\Partner::MARKETPLACE_STATUSES[$partner->marketplace_status] ?? $partner->marketplace_status;
@endphp

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row mt-3">
    {{-- Main Content --}}
    <div class="col-lg-8">
        {{-- Marketplace Profile --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">Marketplace Profile</div>
                <span class="badge bg-{{ $color }} fs-sm">{{ $statusLabel }}</span>
            </div>
            <div class="card-body">
                @if($partner->headline)
                    <h5 class="fw-semibold mb-2">{{ $partner->headline }}</h5>
                @else
                    <p class="text-muted mb-2">No headline provided.</p>
                @endif
                @if($partner->company_description)
                    <p class="mb-0">{{ $partner->company_description }}</p>
                @else
                    <p class="text-muted mb-0">No company description provided.</p>
                @endif
            </div>
        </div>

        {{-- Company Details --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Company Details</div></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        @if($partner->logo_url)
                            <div class="mb-3">
                                <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}" style="max-height: 48px; max-width: 180px;" class="rounded">
                            </div>
                        @endif
                        <dl class="row mb-0">
                            <dt class="col-sm-5 text-muted">Partner</dt>
                            <dd class="col-sm-7 fw-500">{{ $partner->name }}</dd>
                            <dt class="col-sm-5 text-muted">PSM</dt>
                            <dd class="col-sm-7">{{ $partner->user?->name ?? '—' }}</dd>
                            <dt class="col-sm-5 text-muted">Website</dt>
                            <dd class="col-sm-7">
                                @if($partner->website)
                                    <a href="{{ $partner->website }}" target="_blank" class="text-decoration-none">{{ $partner->website }}</a>
                                @else — @endif
                            </dd>
                            <dt class="col-sm-5 text-muted">Phone</dt>
                            <dd class="col-sm-7">{{ $partner->phone ?? '—' }}</dd>
                            <dt class="col-sm-5 text-muted">Territory</dt>
                            <dd class="col-sm-7">{{ $partner->sales_territory ?? '—' }}</dd>
                            <dt class="col-sm-5 text-muted">Industry</dt>
                            <dd class="col-sm-7">{{ $partner->industry ?? '—' }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Mailing Address</h6>
                        @if($partner->mailing_street || $partner->mailing_city)
                            <address class="mb-3">
                                @if($partner->mailing_street){{ $partner->mailing_street }}<br>@endif
                                @if($partner->mailing_suite){{ $partner->mailing_suite }}<br>@endif
                                @if($partner->mailing_city){{ $partner->mailing_city }}@endif
                                @if($partner->mailing_state), {{ $partner->mailing_state }}@endif
                                @if($partner->mailing_postal_code) {{ $partner->mailing_postal_code }}@endif
                                @if($partner->mailing_country)<br>{{ $partner->mailing_country }}@endif
                            </address>
                        @else
                            <p class="text-muted">No address provided.</p>
                        @endif

                        @if($partner->partner_locator_emails)
                            <h6 class="text-muted mb-2">Distribution Emails</h6>
                            <p class="text-break mb-0">{{ $partner->partner_locator_emails }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Marketplace Entries --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Marketplace Entries</div></div>
            <div class="card-body">
                @php $hasEntries = false; @endphp
                @foreach($categories as $key => $label)
                    @if(isset($entries[$key]) && $entries[$key]->count())
                        @php $hasEntries = true; @endphp
                        <h6 class="text-primary mt-3 mb-2">{{ $label }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-3">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30px;">#</th>
                                        <th>Title</th>
                                        <th>Link</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entries[$key] as $entry)
                                    <tr>
                                        <td class="text-muted">{{ $entry->sort_order }}</td>
                                        <td class="fw-medium">{{ $entry->title }}</td>
                                        <td><a href="{{ $entry->link }}" target="_blank" class="text-decoration-none">{{ Str::limit($entry->link, 40) }}</a></td>
                                        <td class="text-muted">{{ Str::limit($entry->description, 100) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endforeach

                @unless($hasEntries)
                    <p class="text-muted text-center py-4 mb-0">No marketplace entries submitted by this partner.</p>
                @endunless
            </div>
        </div>

        {{-- Rejection Reason --}}
        @if($partner->marketplace_status === 'rejected' && $partner->marketplace_rejection_reason)
        <div class="card shadow-0 mb-g border-left-danger">
            <div class="card-body">
                <h5 class="card-title text-danger">Rejection Reason</h5>
                <p class="mb-0">{{ $partner->marketplace_rejection_reason }}</p>
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
                        <small class="text-muted">
                            @if($partner->marketplace_status !== 'draft')
                                Profile submitted for review
                            @else
                                Not yet submitted
                            @endif
                        </small>
                    </div>
                </div>
                @if($partner->marketplace_reviewed_at)
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-{{ $partner->marketplace_status === 'approved' ? 'success' : 'danger' }} me-2">2</span>
                    <div>
                        <strong>{{ $partner->marketplace_status === 'approved' ? 'Approved' : 'Rejected' }}</strong><br>
                        <small class="text-muted">{{ $partner->marketplace_reviewed_at->format('M d, Y H:i') }}</small><br>
                        <small class="text-muted">By: {{ $partner->marketplaceReviewer?->name ?? 'Unknown' }}</small>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Review Actions --}}
        @if($partner->marketplace_status === 'submitted')
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Review Actions</div></div>
            <div class="card-body">
                <form action="{{ route('partners.marketplace-review', $partner) }}" method="POST" class="mb-2">
                    @csrf @method('PUT')
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn btn-success w-100 mb-2">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#check"></use></svg> Approve Profile
                    </button>
                </form>
                <form action="{{ route('partners.marketplace-review', $partner) }}" method="POST">
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

        {{-- Partner Link --}}
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center">
                <a href="{{ route('partners.show', $partner) }}" class="btn btn-outline-primary btn-sm w-100">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#external-link"></use></svg> View Partner Detail
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
