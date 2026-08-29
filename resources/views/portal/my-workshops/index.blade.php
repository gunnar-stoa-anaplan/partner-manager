@extends('portal.layout')

@section('title', 'My Workshops')

@section('content')
<div class="container py-4">
    <h4 class="mb-1">My Workshops</h4>
    <p class="text-muted mb-4">Workshops you've been personally invited to.</p>

    @if($sessions->isEmpty())
        <div class="text-center text-muted py-5">
            <p>You haven't been invited to any workshops yet.</p>
        </div>
    @else
        <div class="row">
            @foreach($sessions as $session)
                <div class="col-md-6 mb-4">
                    <div class="card portal-card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-1">{{ $session->workshop->name }}</h5>
                            <p class="text-muted small mb-2">{{ $session->name }} &middot; {{ $session->start_date->format('M j, Y') }} - {{ $session->end_date->format('M j, Y') }}</p>
                            <span class="badge bg-secondary mb-3">{{ \App\Models\WorkshopSessionParticipant::STATUSES[$session->pivot->status] ?? $session->pivot->status }}</span>
                            <div>
                                <a href="{{ route('portal.my-workshops.show', $session) }}" class="btn btn-sm btn-primary">Open</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
