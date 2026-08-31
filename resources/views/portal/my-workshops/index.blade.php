@extends('portal.layout')

@section('title', 'My Workshops')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-start justify-content-between mb-1">
        <h4 class="mb-0">My Workshops</h4>
        <a href="{{ route('portal.my-workshops.browse') }}" class="btn btn-sm btn-outline-primary">Browse open sessions →</a>
    </div>
    <p class="text-muted mb-4">Workshops you've been personally invited to, or registered for yourself.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($sessions->isEmpty())
        <div class="text-center text-muted py-5">
            <p>You haven't been invited to any workshops yet.</p>
        </div>
    @else
        <div class="row">
            @foreach($sessions as $session)
                @php
                    $statuses = $outcomesBySessionId->get($session->id, []);
                    $doneCount = collect($statuses)->filter(fn ($s) => $s === 'done')->count();
                    $totalCount = count($statuses);
                    $hasFailed = in_array('failed', $statuses, true);
                @endphp
                <div class="col-md-6 mb-4">
                    <div class="card portal-card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-1">{{ $session->workshop->name }}</h5>
                            <p class="text-muted small mb-2">{{ $session->name }} &middot; {{ $session->start_date->format('M j, Y') }} - {{ $session->end_date->format('M j, Y') }}</p>
                            <span class="badge bg-secondary mb-2">{{ \App\Models\WorkshopSessionParticipant::STATUSES[$session->pivot->status] ?? $session->pivot->status }}</span>
                            @if($totalCount > 0)
                                <p class="small mb-3 {{ $hasFailed ? 'text-danger' : 'text-muted' }}">{{ $doneCount }}/{{ $totalCount }} checkpoints complete{{ $hasFailed ? ' - some failed' : '' }}</p>
                            @endif
                            <div class="d-flex gap-2">
                                <a href="{{ route('portal.my-workshops.show', $session) }}" class="btn btn-sm btn-primary">Open</a>
                                @if($session->pivot->status === 'invited')
                                    <form action="{{ route('portal.my-workshops.register', $session) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">Register</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
