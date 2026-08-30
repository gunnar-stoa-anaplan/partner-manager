@extends('layouts.vertical', ['pageTitle' => 'Monitor Dashboard'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Monitor Dashboard', 'pageSubText' => $session->workshop->name.' - '.$session->name])
@endsection

@section('content')
<div class="row mb-g">
    <div class="col-md-3">
        <div class="card shadow-0 text-center"><div class="card-body"><div class="fs-32 fw-bold">{{ $totalParticipants }}</div><div class="text-muted">Participants</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 text-center"><div class="card-body"><div class="fs-32 fw-bold text-success">{{ $completedParticipants }}</div><div class="text-muted">Completed</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 text-center"><div class="card-body"><div class="fs-32 fw-bold text-warning">{{ $openThreadsCount }}</div><div class="text-muted">Open Questions</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 text-center"><div class="card-body"><div class="fs-32 fw-bold">{{ $activeNow }}</div><div class="text-muted">Active Now</div></div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title"><i class="fal fa-comments me-2"></i> Open Questions</div></div>
            <div class="card-body">
                @forelse($session->threads->where('status', 'open') as $thread)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="fw-medium">{{ $thread->authorName() }}</div>
                        <div>{{ $thread->body }}</div>
                        <form action="{{ route('workshops.monitor.threads.answer', [$session, $thread]) }}" method="POST" class="mt-1">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success">Mark Answered</button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted mb-0">No open questions right now.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        @if($facilitatorPages->isNotEmpty())
        <div class="card shadow-0 mb-g border-warning">
            <div class="card-header bg-warning bg-opacity-10"><div class="card-title"><i class="fal fa-clipboard-list me-2"></i> Facilitator Guide</div></div>
            <ul class="list-group list-group-flush">
                @foreach($facilitatorPages as $page)
                    <li class="list-group-item">
                        <a href="{{ route('workshops.monitor.pages.show', [$session, $page]) }}">{{ $page->title }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title"><i class="fal fa-users me-2"></i> Participants</div></div>
            <ul class="list-group list-group-flush">
                @foreach($session->participants as $participant)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $participant->contact->full_name }}</span>
                        @if($participant->completed_at)
                            <span class="badge bg-success">Done</span>
                        @else
                            <span class="badge bg-light text-dark">In progress</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
