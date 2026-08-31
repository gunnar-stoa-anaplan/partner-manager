@extends('portal.layout')

@section('title', 'Workshops')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-start justify-content-between mb-1">
        <h4 class="mb-0">Workshops</h4>
        <a href="{{ route('portal.workshops.rollup') }}" class="btn btn-sm btn-outline-secondary">History across all workshops →</a>
    </div>
    <p class="text-muted mb-4">Sessions your organization is part of, and upcoming sessions you can bring your people into.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <h6 class="mb-3">Our sessions</h6>
    @if($ourSessions->isEmpty())
        <p class="text-muted mb-4">Your organization hasn't been invited to any workshops yet.</p>
    @else
        <div class="row mb-2">
            @foreach($ourSessions as $session)
                <div class="col-md-6 mb-4">
                    <div class="card portal-card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-1">{{ $session->workshop->name }}</h5>
                            <p class="text-muted small mb-2">{{ $session->name }} &middot; {{ $session->start_date->format('M j, Y') }} - {{ $session->end_date->format('M j, Y') }}</p>
                            <p class="text-muted small mb-3">{{ $session->participants->count() }} of your people invited</p>
                            <a href="{{ route('portal.workshops.show', $session) }}" class="btn btn-sm btn-primary">Manage</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <h6 class="mb-3 mt-2">Open sessions</h6>
    <p class="text-muted small mb-3">Upcoming sessions no one from your organization is enrolled in yet.</p>
    @if($openSessions->isEmpty())
        <p class="text-muted">No open sessions right now.</p>
    @else
        <div class="row">
            @foreach($openSessions as $session)
                <div class="col-md-6 mb-4">
                    <div class="card portal-card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-1">{{ $session->workshop->name }}</h5>
                            <p class="text-muted small mb-2">{{ $session->name }} &middot; {{ $session->start_date->format('M j, Y') }} - {{ $session->end_date->format('M j, Y') }}</p>
                            <a href="{{ route('portal.workshops.show', $session) }}" class="btn btn-sm btn-outline-primary">View &amp; invite</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
