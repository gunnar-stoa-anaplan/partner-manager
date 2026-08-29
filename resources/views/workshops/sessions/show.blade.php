@extends('layouts.vertical', ['pageTitle' => $session->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $session->name, 'pageSubTitle1' => $workshop->name, 'pageSubTitle1Url' => route('workshops.show', $workshop), 'pageSubText' => $session->start_date->format('M j, Y').' - '.$session->end_date->format('M j, Y')])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1"><i class="fal fa-users me-2"></i> Participants</div>
                <span class="badge bg-secondary">{{ \App\Models\WorkshopSession::STATUSES[$session->status] ?? $session->status }}</span>
                <a href="{{ route('workshops.sessions.edit', [$workshop, $session]) }}" class="btn btn-outline-secondary btn-sm ms-2">Edit Session</a>
            </div>
            <div class="card-body">
                <form action="{{ route('workshops.sessions.participants.invite', [$workshop, $session]) }}" method="POST" class="mb-4">
                    @csrf
                    <label class="form-label fs-sm">Invite by email <span class="text-muted">(one per line, or comma-separated - must already have a contact record)</span></label>
                    <div class="input-group">
                        <textarea class="form-control" name="emails" rows="2" placeholder="jane.doe@partner.com&#10;john.smith@partner.com" required></textarea>
                        <button type="submit" class="btn btn-primary">Invite</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Partner</th>
                                <th>Status</th>
                                <th>Enrolled</th>
                                <th>Credential</th>
                                <th>Completion</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($session->participants as $participant)
                                @php $credentialAssignment = $activeCredentialByContact->get($participant->contact_id); @endphp
                                <tr>
                                    <td>{{ $participant->contact->full_name }} <span class="text-muted">{{ $participant->contact->email }}</span></td>
                                    <td>{{ $participant->contact->partner?->name }}</td>
                                    <td><span class="badge bg-secondary">{{ \App\Models\WorkshopSessionParticipant::STATUSES[$participant->status] ?? $participant->status }}</span></td>
                                    <td>{{ $participant->enrolled_at?->format('M j, Y') }}</td>
                                    <td>
                                        @if($credentialAssignment)
                                            <div class="fs-sm">{{ $credentialAssignment->credential->username }}</div>
                                            <div class="text-nowrap">
                                                <form action="{{ route('workshops.credentials.assignments.extend', $credentialAssignment) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="date" name="hold_until" value="{{ $credentialAssignment->hold_until?->format('Y-m-d') }}" class="form-control form-control-sm d-inline-block" style="width: auto;">
                                                    <button type="submit" class="btn btn-sm btn-link p-0">Extend</button>
                                                </form>
                                                <form action="{{ route('workshops.credentials.assignments.release', $credentialAssignment) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">Release</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-muted fs-sm">None</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($participant->completed_at)
                                            <span class="badge bg-success">Completed {{ $participant->completed_at->format('M j') }}</span>
                                        @else
                                            <span class="badge bg-light text-dark">In progress</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if($participant->status !== 'removed')
                                        @if($participant->completed_at)
                                        <form action="{{ route('workshops.sessions.participants.uncomplete', [$workshop, $session, $participant]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Mark Incomplete</button>
                                        </form>
                                        @else
                                        <form action="{{ route('workshops.sessions.participants.complete', [$workshop, $session, $participant]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">Mark Complete</button>
                                        </form>
                                        @endif
                                        <form action="{{ route('workshops.sessions.participants.remove', [$workshop, $session, $participant]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove {{ $participant->contact->full_name }} from this session?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No participants invited yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title flex-grow-1"><i class="fal fa-comments me-2"></i> Discussion</div>
                <span class="badge bg-info me-2">{{ $activeViewers }} active</span>
                <a href="{{ route('workshops.sessions.feed', [$workshop, $session]) }}" class="btn btn-sm btn-outline-secondary me-1">Feed</a>
                <a href="{{ route('workshops.monitor.show', $session) }}" class="btn btn-sm btn-outline-secondary">Monitor View</a>
            </div>
            <div class="card-body">
                <form action="{{ route('workshops.sessions.threads.store', [$workshop, $session]) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                    @csrf
                    <textarea name="body" class="form-control form-control-sm mb-1" rows="2" placeholder="Post an announcement or comment..." required></textarea>
                    <div class="d-flex justify-content-between align-items-center">
                        <input type="file" name="attachment" class="form-control form-control-sm" style="max-width: 200px;">
                        <button type="submit" class="btn btn-sm btn-primary">Post</button>
                    </div>
                </form>
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
                            <form action="{{ route('workshops.sessions.threads.react', [$workshop, $session, $thread]) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-link p-0 {{ $thread->hasReactionFrom(auth()->id(), null) ? 'text-primary' : 'text-muted' }}">
                                    👍 {{ $thread->reactions->where('kind', 'me_too')->count() }}
                                </button>
                            </form>
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
                        @if($thread->status !== 'hidden')
                        <form action="{{ route('workshops.sessions.threads.hide', [$workshop, $session, $thread]) }}" method="POST" class="mt-1">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-link text-danger p-0">Hide</button>
                        </form>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">No questions posted yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title"><i class="fal fa-user-check me-2"></i> Facilitators / Monitors</div>
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
