{{-- Marketplace Status --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-2">
        <h6 class="mb-0">Marketplace Status</h6>
        <span class="badge bg-{{ \App\Models\Partner::MARKETPLACE_STATUS_COLORS[$partner->marketplace_status] ?? 'secondary' }}">
            {{ \App\Models\Partner::MARKETPLACE_STATUSES[$partner->marketplace_status] ?? 'Draft' }}
        </span>
        @if($partner->marketplace_reviewed_at && $partner->marketplaceReviewer)
            <small class="text-muted ms-2">
                Reviewed by {{ $partner->marketplaceReviewer->name }} on {{ $partner->marketplace_reviewed_at->format('M j, Y') }}
            </small>
        @endif
    </div>

    @if($partner->marketplace_status !== 'draft')
        <a href="{{ route('engage.marketplace-submissions.show', $partner) }}" class="btn btn-sm btn-outline-info">
            View Submission
        </a>
    @endif
</div>

@if($partner->marketplace_status === 'rejected' && $partner->marketplace_rejection_reason)
    <div class="alert alert-danger mb-4">
        <strong>Rejection Reason:</strong> {{ $partner->marketplace_rejection_reason }}
    </div>
@endif

{{-- Company Profile Summary --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Company Information</div></div>
            <div class="card-body">
                @if($partner->logo_url)
                    <div class="mb-3">
                        <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}" style="max-height: 48px; max-width: 180px;" class="rounded">
                    </div>
                @endif
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted">Website</dt>
                    <dd class="col-sm-7">{{ $partner->website ?? '—' }}</dd>
                    <dt class="col-sm-5 text-muted">Phone</dt>
                    <dd class="col-sm-7">{{ $partner->phone ?? '—' }}</dd>
                    <dt class="col-sm-5 text-muted">Territory</dt>
                    <dd class="col-sm-7">{{ $partner->sales_territory ?? '—' }}</dd>
                    <dt class="col-sm-5 text-muted">Industry</dt>
                    <dd class="col-sm-7">{{ $partner->industry ?? '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Mailing Address</div></div>
            <div class="card-body">
                @if($partner->mailing_street || $partner->mailing_city)
                    <address class="mb-0">
                        @if($partner->mailing_street){{ $partner->mailing_street }}<br>@endif
                        @if($partner->mailing_suite){{ $partner->mailing_suite }}<br>@endif
                        @if($partner->mailing_city){{ $partner->mailing_city }}@endif
                        @if($partner->mailing_state), {{ $partner->mailing_state }}@endif
                        @if($partner->mailing_postal_code) {{ $partner->mailing_postal_code }}@endif
                        @if($partner->mailing_country)<br>{{ $partner->mailing_country }}@endif
                    </address>
                @else
                    <span class="text-muted">No address provided.</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Marketplace Profile</div></div>
            <div class="card-body">
                @if($partner->headline)
                    <p class="fw-semibold mb-1">{{ $partner->headline }}</p>
                @endif
                @if($partner->company_description)
                    <p class="text-muted small mb-2">{{ Str::limit($partner->company_description, 200) }}</p>
                @endif
                @if($partner->partner_locator_emails)
                    <div class="mt-2">
                        <small class="text-muted d-block mb-1">Distribution List</small>
                        <small class="text-break">{{ $partner->partner_locator_emails }}</small>
                    </div>
                @endif
                @if(!$partner->headline && !$partner->company_description && !$partner->partner_locator_emails)
                    <span class="text-muted">No marketplace profile submitted.</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Marketplace Entries --}}
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title">Marketplace Entries</div>
    </div>
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
