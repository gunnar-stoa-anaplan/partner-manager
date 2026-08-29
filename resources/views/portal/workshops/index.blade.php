@extends('portal.layout')

@section('title', 'Workshops')

@section('content')
<div class="container py-4">
    <h4 class="mb-1">Workshops</h4>
    <p class="text-muted mb-4">Workshop sessions your organization has been invited to.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($sessions->isEmpty())
        <div class="text-center text-muted py-5">
            <p>Your organization hasn't been invited to any workshops yet.</p>
        </div>
    @else
        <div class="row">
            @foreach($sessions as $session)
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
</div>
@endsection
