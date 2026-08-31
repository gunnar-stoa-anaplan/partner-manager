@extends('portal.layout')

@section('title', 'Browse Workshops')

@section('content')
<div class="container py-4">
    <h4 class="mb-1">Browse Workshops</h4>
    <p class="text-muted mb-4">Upcoming sessions you're not enrolled in yet - register yourself directly.</p>

    @if($openSessions->isEmpty())
        <div class="text-center text-muted py-5">
            <p>No open sessions right now.</p>
        </div>
    @else
        <div class="row">
            @foreach($openSessions as $session)
                <div class="col-md-6 mb-4">
                    <div class="card portal-card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-1">{{ $session->workshop->name }}</h5>
                            <p class="text-muted small mb-3">{{ $session->name }} &middot; {{ $session->start_date->format('M j, Y') }} - {{ $session->end_date->format('M j, Y') }}</p>
                            <form action="{{ route('portal.my-workshops.self-register', $session) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary">Register</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <a href="{{ route('portal.my-workshops.index') }}" class="btn btn-outline-secondary btn-sm mt-2">&laquo; Back to My Workshops</a>
</div>
@endsection
