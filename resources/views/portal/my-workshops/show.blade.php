@extends('portal.layout')

@section('title', $session->workshop->name)

@section('content')
<div class="container py-4">
    <h4 class="mb-1">{{ $session->workshop->name }}</h4>
    <p class="text-muted mb-4">{{ $session->name }} &middot; {{ $session->start_date->format('M j, Y') }} - {{ $session->end_date->format('M j, Y') }}</p>

    <div class="card portal-card">
        <div class="card-body">
            <p class="mb-0">{{ $session->workshop->short_description }}</p>
            <p class="text-muted small mt-3 mb-0">Workshop content and lab completion tracking is coming soon.</p>
        </div>
    </div>
</div>
@endsection
