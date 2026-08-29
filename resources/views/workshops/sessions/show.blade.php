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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($session->participants as $participant)
                                <tr>
                                    <td>{{ $participant->contact->full_name }} <span class="text-muted">{{ $participant->contact->email }}</span></td>
                                    <td>{{ $participant->contact->partner?->name }}</td>
                                    <td><span class="badge bg-secondary">{{ \App\Models\WorkshopSessionParticipant::STATUSES[$participant->status] ?? $participant->status }}</span></td>
                                    <td>{{ $participant->enrolled_at?->format('M j, Y') }}</td>
                                    <td>
                                        @if($participant->status !== 'removed')
                                        <form action="{{ route('workshops.sessions.participants.remove', [$workshop, $session, $participant]) }}" method="POST" onsubmit="return confirm('Remove {{ $participant->contact->full_name }} from this session?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No participants invited yet.</td>
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
