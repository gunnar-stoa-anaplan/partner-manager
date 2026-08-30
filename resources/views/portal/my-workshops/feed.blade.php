@extends('portal.layout')

@section('title', 'Activity Feed')

@section('content')
<div class="container py-4">
    <h4 class="mb-1">Activity Feed</h4>
    <p class="text-muted mb-4">{{ $session->workshop->name }} &middot; {{ $session->name }}</p>

    <div class="card portal-card">
        <div class="card-body">
            @forelse($items as $item)
                @if($item['type'] === 'thread')
                    <div class="d-flex mb-3">
                        <div class="me-3 text-muted small" style="min-width: 90px;">{{ $item['at']->format('M j, g:ia') }}</div>
                        <div>
                            <span class="fw-medium">{{ $item['model']->authorName() }}</span>
                            @if($item['model']->page)
                                <span class="badge bg-light text-dark">{{ $item['model']->page->title }}</span>
                            @endif
                            <div>{{ $item['model']->body }}</div>
                        </div>
                    </div>
                @else
                    <div class="d-flex mb-3 ms-4">
                        <div class="me-3 text-muted small" style="min-width: 90px;">{{ $item['at']->format('M j, g:ia') }}</div>
                        <div>
                            <span class="fw-medium">{{ $item['model']->authorName() }}</span>
                            @if($item['model']->is_monitor_reply)<span class="badge bg-primary">Facilitator</span>@endif
                            <div>{{ $item['model']->body }}</div>
                        </div>
                    </div>
                @endif
            @empty
                <p class="text-muted mb-0">No activity yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
