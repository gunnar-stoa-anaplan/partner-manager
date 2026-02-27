@extends('layouts.vertical', ['pageTitle' => 'Marketplace Submissions'])

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">Marketplace Submissions</h2>
        <p class="text-muted mb-0">Review and approve partner marketplace profiles before they appear on the directory.</p>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row mb-g">
    <div class="col-sm-6 col-xl-4">
        <div class="p-3 bg-warning-300 rounded overflow-hidden position-relative text-white">
            <div>
                <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ $stats['submitted'] }}</h3>
                <span class="m-0 l-h-n">Pending Review</span>
            </div>
            <i class="fal fa-clock position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="p-3 bg-success-300 rounded overflow-hidden position-relative text-white">
            <div>
                <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ $stats['approved'] }}</h3>
                <span class="m-0 l-h-n">Approved</span>
            </div>
            <i class="fal fa-check-circle position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="p-3 bg-danger-300 rounded overflow-hidden position-relative text-white">
            <div>
                <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ $stats['rejected'] }}</h3>
                <span class="m-0 l-h-n">Rejected</span>
            </div>
            <i class="fal fa-times-circle position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size:6rem"></i>
        </div>
    </div>
</div>

{{-- Status Filter --}}
<div class="d-flex gap-2 mb-3">
    @foreach(\App\Models\Partner::MARKETPLACE_STATUSES as $sKey => $sLabel)
    <a href="{{ route('engage.marketplace-submissions.index', ['status' => $sKey]) }}"
       class="btn btn-sm {{ request('status') === $sKey ? 'btn-'.\App\Models\Partner::MARKETPLACE_STATUS_COLORS[$sKey] : 'btn-outline-secondary' }}">
        {{ $sLabel }}
    </a>
    @endforeach
    <a href="{{ route('engage.marketplace-submissions.index') }}"
       class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">
        All
    </a>
</div>

{{-- Partners Table --}}
<div class="card shadow-0 mb-g">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="marketplace-submissions-table">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>Headline</th>
                        <th class="text-center">Entries</th>
                        <th>Status</th>
                        <th>Reviewed By</th>
                        <th>Reviewed At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($partners as $partner)
                    <tr>
                        <td>
                            <a href="{{ route('engage.marketplace-submissions.show', $partner) }}" class="fw-500 text-decoration-none">
                                {{ $partner->name }}
                            </a>
                            <br><small class="text-muted">{{ $partner->user?->name ?? '—' }}</small>
                        </td>
                        <td>
                            @if($partner->headline)
                                {{ Str::limit($partner->headline, 50) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary bg-opacity-25 text-primary">{{ $partner->marketplace_entries_count }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ \App\Models\Partner::MARKETPLACE_STATUS_COLORS[$partner->marketplace_status] ?? 'secondary' }}">
                                {{ \App\Models\Partner::MARKETPLACE_STATUSES[$partner->marketplace_status] ?? $partner->marketplace_status }}
                            </span>
                        </td>
                        <td>{{ $partner->marketplaceReviewer?->name ?? '—' }}</td>
                        <td>{{ $partner->marketplace_reviewed_at?->format('M j, Y') ?? '—' }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('engage.marketplace-submissions.show', $partner) }}" class="btn btn-xs btn-outline-info" title="Review">
                                <i class="fal fa-eye"></i> Review
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="mb-3"><i class="fal fa-store fa-3x text-muted opacity-50"></i></div>
                            <h5 class="text-muted">No marketplace submissions</h5>
                            <p class="text-muted mb-0">Partner marketplace profiles will appear here once submitted.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@vite(['resources/scripts/pages/table-init.js'])
@endsection
