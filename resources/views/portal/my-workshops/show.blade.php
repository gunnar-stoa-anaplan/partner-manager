@extends('portal.layout')

@section('title', $session->workshop->name)

@section('content')
<div class="container py-4">
    <h4 class="mb-1">{{ $session->workshop->name }}</h4>
    <p class="text-muted mb-4">{{ $session->name }} &middot; {{ $session->start_date->format('M j, Y') }} - {{ $session->end_date->format('M j, Y') }}</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card portal-card mb-4">
        <div class="card-body">
            <p class="mb-2">{{ $session->workshop->short_description }}</p>
            <div class="d-flex gap-2">
                @if($session->workshop->pages->isNotEmpty())
                    <a href="{{ route('portal.my-workshops.pages.show', [$session, $session->workshop->pages->first()]) }}" class="btn btn-sm btn-primary">Start Content</a>
                    <a href="{{ route('portal.my-workshops.progress', $session) }}" class="btn btn-sm btn-outline-primary">My Progress</a>
                @endif
                @if($session->workshop->toolkitAssets->isNotEmpty())
                    <a href="#toolkit" class="btn btn-sm btn-outline-secondary">Toolkit</a>
                @endif
            </div>
        </div>
    </div>

    @if($session->workshop->toolkitAssets->isNotEmpty())
    <div class="card portal-card mb-4" id="toolkit">
        <div class="card-header"><h6 class="mb-0">Toolkit</h6></div>
        <ul class="list-group list-group-flush">
            @foreach($session->workshop->toolkitAssets as $asset)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>{{ $asset->title }}</span>
                    <a href="{{ route('portal.my-workshops.toolkit.download', [$session, $asset]) }}" class="btn btn-sm btn-outline-primary">Download</a>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($session->workshop->surveyTemplates->isNotEmpty())
    <div class="card portal-card mb-4">
        <div class="card-header"><h6 class="mb-0">Surveys</h6></div>
        <div class="card-body">
            @foreach($session->workshop->surveyTemplates as $template)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>{{ \App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind }}</span>
                    @if($myCompletedSurveyKinds->contains($template->id) && ! $template->max_attempts)
                        <span class="badge bg-success">Submitted</span>
                    @else
                        <a href="{{ route('portal.my-workshops.surveys.show', [$session, $template]) }}" class="btn btn-sm btn-primary">Take Survey</a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="card portal-card">
        <div class="card-header"><h6 class="mb-0">Discussion</h6></div>
        <div class="card-body">
            <form action="{{ route('portal.my-workshops.threads.store', $session) }}" method="POST" class="mb-4">
                @csrf
                <textarea name="body" class="form-control mb-2" rows="2" placeholder="Ask a question..." required></textarea>
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-check-label small">
                        <input type="checkbox" name="anonymous" value="1" class="form-check-input"> Post anonymously
                    </label>
                    <button type="submit" class="btn btn-sm btn-primary">Post</button>
                </div>
            </form>

            @forelse($session->threads as $thread)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="fw-medium">{{ $thread->authorName() }}</div>
                    <div class="text-muted small mb-2">{{ $thread->created_at->diffForHumans() }} &middot; <span class="badge bg-{{ $thread->status === 'answered' ? 'success' : 'secondary' }}">{{ ucfirst($thread->status) }}</span></div>
                    <div>{{ $thread->body }}</div>

                    @foreach($thread->replies as $reply)
                        <div class="ms-3 mt-2 ps-2 border-start">
                            <div class="fw-medium small">{{ $reply->authorName() }} @if($reply->is_monitor_reply)<span class="badge bg-primary">Facilitator</span>@endif</div>
                            <div class="small">{{ $reply->body }}</div>
                        </div>
                    @endforeach

                    <form action="{{ route('portal.my-workshops.threads.reply', [$session, $thread]) }}" method="POST" class="mt-2 d-flex gap-1">
                        @csrf
                        <input type="text" name="body" class="form-control form-control-sm" placeholder="Reply..." required>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Send</button>
                    </form>
                </div>
            @empty
                <p class="text-muted mb-0">No questions posted yet - be the first to ask.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
