@extends('layouts.vertical', ['pageTitle' => $session->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $session->name, 'pageSubTitle1' => $workshop->name, 'pageSubTitle1Url' => route('workshops.show', $workshop), 'pageSubText' => $session->start_date->format('M j, Y').' - '.$session->end_date->format('M j, Y')])
@endsection

@section('content')
<style>
    .info-tile .info-label { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: #6b7280; }
    .info-tile .info-value { font-size: 1.05rem; font-weight: 500; }
</style>
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="info-tile">
                        <div class="info-label">Slug</div>
                        <div class="info-value"><code>{{ $session->slug }}</code></div>
                    </div>
                    <div>
                        <a href="{{ route('workshops.sessions.edit', [$workshop, $session]) }}" class="btn btn-outline-secondary btn-sm">Edit Session</a>
                        @if($session->status !== 'archived')
                            <form action="{{ route('workshops.sessions.archive', [$workshop, $session]) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this session? Discussion will become read-only.')">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning btn-sm">Archive session</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-6 col-md-2 info-tile">
                        <div class="info-label">Start</div>
                        <div class="info-value">{{ $session->start_date->format('Y-m-d') }}</div>
                    </div>
                    <div class="col-6 col-md-2 info-tile">
                        <div class="info-label">End</div>
                        <div class="info-value">{{ $session->end_date->format('Y-m-d') }}</div>
                    </div>
                    <div class="col-6 col-md-2 info-tile">
                        <div class="info-label">Status</div>
                        <div class="info-value">{{ \App\Models\WorkshopSession::STATUSES[$session->status] ?? $session->status }}</div>
                    </div>
                    <div class="col-6 col-md-3 info-tile">
                        <div class="info-label">Created By</div>
                        <div class="info-value">{{ $session->creator?->name ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3 info-tile">
                        <div class="info-label">Created</div>
                        <div class="info-value fs-sm">{{ $session->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-g">
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h6 class="mb-0">Participants</h6>
                            <div class="text-nowrap">
                                <a href="{{ route('workshops.reports.leaderboard', [$workshop, $session]) }}" class="btn btn-outline-secondary btn-sm">Leaderboard ↗</a>
                                <a href="{{ route('workshops.sessions.participants.index', [$workshop, $session]) }}" class="btn btn-primary btn-sm">Manage →</a>
                            </div>
                        </div>
                        <div class="fs-2 fw-bold mb-1">{{ $session->active_participants_count }}</div>
                        <p class="text-muted fs-sm mb-0">Enrolled participants for this session.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h6 class="mb-0">Surveys &amp; Knowledge Check</h6>
                            <a href="{{ route('workshops.surveys.index', $workshop) }}" class="btn btn-primary btn-sm">Open →</a>
                        </div>
                        <p class="text-muted fs-sm mb-0">Pre-survey, post-survey, and knowledge check - the same templates used across every session in this workshop.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h6 class="mb-0">Reports</h6>
                            <a href="{{ route('workshops.reports.index', [$workshop, $session]) }}" class="btn btn-primary btn-sm">Open →</a>
                        </div>
                        <p class="text-muted fs-sm mb-0">Executive summary, Slack celebration post, per-partner sponsor reports, JSON export - all rendered from this session's participants and survey responses.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h6 class="mb-0">Discussion moderators</h6>
                            <div class="text-nowrap">
                                <a href="{{ route('workshops.sessions.feed', [$workshop, $session]) }}" class="btn btn-outline-secondary btn-sm">Feed ↗</a>
                                <a href="#moderators" class="btn btn-primary btn-sm">Manage →</a>
                            </div>
                        </div>
                        <div class="fs-2 fw-bold mb-1">{{ $session->monitors->whereNull('removed_at')->count() }}</div>
                        <p class="text-muted fs-sm mb-0">Active moderators - responding to questions and posting celebrations across this session's workshop pages.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-0 mb-g">
                    <div class="card-header">
                        <div class="card-title flex-grow-1"><i class="fal fa-comments me-2"></i> Discussion</div>
                        <span class="badge bg-info me-2">{{ $activeViewers }} active</span>
                    </div>
                    <div class="card-body">
                        @if($session->isLocked())
                            <div class="alert alert-secondary py-2 fs-sm mb-0"><i class="fal fa-lock me-1"></i> This session is read-only. Archived sessions, and sessions more than {{ \App\Models\WorkshopSession::LOCK_GRACE_DAYS }} days past their end date, no longer accept new discussion.</div>
                        @else
                        <form action="{{ route('workshops.sessions.threads.store', [$workshop, $session]) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                            @csrf
                            <textarea name="body" class="form-control form-control-sm mb-1" rows="2" placeholder="Post an announcement or comment..." required></textarea>
                            <div class="d-flex justify-content-between align-items-center">
                                <input type="file" name="attachment" class="form-control form-control-sm" style="max-width: 200px;">
                                <button type="submit" class="btn btn-sm btn-primary">Post</button>
                            </div>
                        </form>
                        @endif
                    </div>
                    <div class="card-body border-top" style="max-height: 500px; overflow-y: auto;">
                        @forelse($session->threads as $thread)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="fw-medium">{{ $thread->authorName() }}</div>
                                <div class="text-muted fs-sm mb-2">{{ $thread->created_at->diffForHumans() }} &middot; <span class="badge bg-{{ $thread->status === 'answered' ? 'success' : 'secondary' }}">{{ ucfirst($thread->status) }}</span></div>
                                <div>{{ $thread->body }}</div>

                                @foreach($thread->uploads as $upload)
                                    <div class="fs-sm"><a href="{{ route('workshops.uploads.download', $upload) }}"><i class="fal fa-paperclip me-1"></i>{{ $upload->filename }}</a></div>
                                @endforeach

                                <div class="mt-1">
                                    @if($session->isLocked())
                                        <span class="text-muted">👍 {{ $thread->reactions->where('kind', 'me_too')->count() }}</span>
                                    @else
                                    <form action="{{ route('workshops.sessions.threads.react', [$workshop, $session, $thread]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link p-0 {{ $thread->hasReactionFrom(auth()->id(), null) ? 'text-primary' : 'text-muted' }}">
                                            👍 {{ $thread->reactions->where('kind', 'me_too')->count() }}
                                        </button>
                                    </form>
                                    @endif
                                </div>

                                @foreach($thread->replies as $reply)
                                    <div class="ms-3 mt-2 ps-2 border-start">
                                        <div class="fw-medium fs-sm">{{ $reply->authorName() }} @if($reply->is_monitor_reply)<span class="badge bg-primary">Facilitator</span>@endif</div>
                                        <div class="fs-sm">{{ $reply->body }}</div>
                                        @foreach($reply->uploads as $upload)
                                            <div class="fs-sm"><a href="{{ route('workshops.uploads.download', $upload) }}"><i class="fal fa-paperclip me-1"></i>{{ $upload->filename }}</a></div>
                                        @endforeach
                                    </div>
                                @endforeach

                                @unless($session->isLocked())
                                <form action="{{ route('workshops.sessions.threads.reply', [$workshop, $session, $thread]) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                    @csrf
                                    <div class="d-flex gap-1 mb-1">
                                        <input type="text" name="body" class="form-control form-control-sm suggest-reply-input" placeholder="Reply..." required>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Send</button>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <input type="file" name="attachment" class="form-control form-control-sm" style="max-width: 160px;">
                                        <button type="button" class="btn btn-sm btn-outline-info suggest-reply-btn" data-url="{{ route('workshops.sessions.threads.suggest-reply', [$workshop, $session, $thread]) }}">✨ Suggest Reply</button>
                                    </div>
                                </form>
                                @endunless
                                <div class="mt-1">
                                    @if($thread->status === 'open' && ! $session->isLocked())
                                    <form action="{{ route('workshops.sessions.threads.answer', [$workshop, $session, $thread]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link text-success p-0 me-2">Mark Answered</button>
                                    </form>
                                    @endif
                                    @if($thread->status !== 'hidden')
                                    <form action="{{ route('workshops.sessions.threads.hide', [$workshop, $session, $thread]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0">Hide</button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No questions posted yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-0 mb-g" id="moderators">
                    <div class="card-header">
                        <div class="card-title"><i class="fal fa-user-check me-2"></i> Facilitators / Moderators</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('workshops.sessions.monitors.store', [$workshop, $session]) }}" method="POST" class="input-group mb-3">
                            @csrf
                            <select class="form-select form-select-sm" name="user_id" required>
                                <option value="">Assign facilitator...</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Add</button>
                        </form>

                        <ul class="list-group list-group-flush">
                            @forelse($session->monitors as $monitor)
                                @if(!$monitor->removed_at)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    {{ $monitor->user->name }}
                                    <form action="{{ route('workshops.sessions.monitors.destroy', [$workshop, $session, $monitor->user]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0">Remove</button>
                                    </form>
                                </li>
                                @endif
                            @empty
                                <li class="list-group-item px-0 text-muted">No facilitators assigned.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('click', function (event) {
    var btn = event.target.closest('.suggest-reply-btn');
    if (!btn) return;
    var input = btn.closest('form').querySelector('.suggest-reply-input');
    btn.disabled = true;
    btn.textContent = 'Thinking...';
    fetch(btn.dataset.url, { headers: { 'Accept': 'application/json' } })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.suggestion) { input.value = data.suggestion; }
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = '✨ Suggest Reply';
        });
});
</script>
@endpush
