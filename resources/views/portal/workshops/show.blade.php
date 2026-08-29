@extends('portal.layout')

@section('title', $session->workshop->name)

@section('content')
<div class="container py-4">
    <h4 class="mb-1">{{ $session->workshop->name }}</h4>
    <p class="text-muted mb-4">{{ $session->name }} &middot; {{ $session->start_date->format('M j, Y') }} - {{ $session->end_date->format('M j, Y') }}</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card portal-card mb-4">
        <div class="card-body">
            <h6 class="mb-3">Invite your people</h6>
            <form action="{{ route('portal.workshops.invite', $session) }}" method="POST" class="mb-2">
                @csrf
                <label class="form-label small">Email addresses <span class="text-muted">(one per line, or comma-separated - they need a contact record with your organization already)</span></label>
                <div class="input-group">
                    <textarea class="form-control" name="emails" rows="2" required></textarea>
                    <button type="submit" class="btn btn-primary">Invite</button>
                </div>
            </form>
            @if($availableContacts->isNotEmpty())
                <p class="text-muted small mb-0">Available: {{ $availableContacts->pluck('email')->filter()->implode(', ') }}</p>
            @endif
        </div>
    </div>

    <div class="card portal-card">
        <div class="card-header">
            <h6 class="mb-0">Your team's progress</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Enrolled</th>
                        <th>Completion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($session->participants as $participant)
                        <tr>
                            <td>{{ $participant->contact->full_name }} <span class="text-muted">{{ $participant->contact->email }}</span></td>
                            <td><span class="badge bg-secondary">{{ \App\Models\WorkshopSessionParticipant::STATUSES[$participant->status] ?? $participant->status }}</span></td>
                            <td>{{ $participant->enrolled_at?->format('M j, Y') }}</td>
                            <td>
                                @if($participant->completed_at)
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-light text-dark">In progress</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No one from your organization is enrolled yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
