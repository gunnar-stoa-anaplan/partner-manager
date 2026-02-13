@extends('portal.layout', ['pageTitle' => 'Enablement'])

@section('content')
<h4 class="mb-4">Enablement — {{ $partner->name }}</h4>

{{-- My Registrations --}}
<div class="card portal-card mb-4">
    <div class="card-header"><strong>My Registrations</strong></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Event</th><th>Session Date</th><th>Type</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($myRegistrations as $reg)
                    <tr>
                        <td>{{ $reg->session?->event?->title ?? '—' }}</td>
                        <td>{{ $reg->session?->start_at?->format('M j, Y g:ia') ?? '—' }}</td>
                        <td>{{ $reg->session?->event?->type ?? '—' }}</td>
                        <td>
                            @php $regColors = ['registered'=>'info','confirmed'=>'primary','attended'=>'success','no_show'=>'danger','cancelled'=>'secondary']; @endphp
                            <span class="badge bg-{{ $regColors[$reg->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $reg->status)) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">No registrations yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Upcoming Sessions --}}
<div class="card portal-card">
    <div class="card-header"><strong>Upcoming Sessions</strong></div>
    <div class="card-body">
        @forelse($upcomingSessions as $session)
        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div>
                <strong>{{ $session->event?->title }}</strong>
                <span class="badge bg-secondary ms-1">{{ $session->event?->category ?? '' }}</span>
                <br><small class="text-muted">{{ $session->start_at?->format('M j, Y \\a\\t g:ia') }} &middot; {{ $session->location ?? $session->event?->type }}</small>
            </div>
        </div>
        @empty
        <p class="text-muted mb-0">No upcoming sessions.</p>
        @endforelse
    </div>
</div>
@endsection
