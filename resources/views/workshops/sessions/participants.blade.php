@extends('layouts.vertical', ['pageTitle' => $session->name.' - Participants'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Manage Participants', 'pageSubTitle1' => $session->name, 'pageSubTitle1Url' => route('workshops.sessions.show', [$workshop, $session])])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        @if($totalCredentials > 0)
        <div class="card shadow-0 mb-g border-start border-4 {{ $freeCredentials > 0 ? 'border-warning' : 'border-danger' }}">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="fs-xs text-muted text-uppercase fw-medium">Tenant Pool</div>
                    <div class="fs-5">{{ $freeCredentials }} of {{ $totalCredentials }} free for this workshop</div>
                </div>
                <a href="{{ route('workshops.credentials.index') }}" class="btn btn-outline-secondary btn-sm">Manage pool →</a>
            </div>
        </div>
        @endif

        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Add participants</div></div>
            <div class="card-body">
                <p class="text-muted fs-sm">Paste one email per line. Accepts plain <code>user@domain</code>, <code>Name &lt;user@domain&gt;</code>, or <code>"Name" &lt;user@domain&gt;</code>. The email must already belong to a contact record in this app.</p>
                <form action="{{ route('workshops.sessions.participants.invite', [$workshop, $session]) }}" method="POST">
                    @csrf
                    <textarea class="form-control mb-2" name="emails" rows="4" placeholder="first.last@partner.com&#10;Jane Doe &lt;jane@partner.com&gt;" required></textarea>
                    <button type="submit" class="btn btn-primary">Enroll participants</button>
                </form>
            </div>
        </div>

        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Enrolled &middot; {{ $session->participants->count() }} total</div></div>
            <div class="card-body">
                @forelse($participantsByPartner->sortKeys() as $partnerName => $participants)
                    <h6 class="mt-3 mb-2">{{ $partnerName }} <span class="text-muted fw-normal">&middot; {{ $participants->count() }}</span></h6>
                    <div class="table-responsive mb-2">
                        <table class="table table-sm table-bordered table-hover w-100">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Enrolled</th>
                                    <th>Last Login</th>
                                    <th>Tenant</th>
                                    <th>Completion</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($participants as $participant)
                                    @php $credentialAssignment = $activeCredentialByContact->get($participant->contact_id); @endphp
                                    <tr>
                                        <td>{{ $participant->contact->email }} <span class="text-muted fs-sm">{{ $participant->contact->full_name }}</span></td>
                                        <td><span class="badge bg-secondary">{{ \App\Models\WorkshopSessionParticipant::STATUSES[$participant->status] ?? $participant->status }}</span></td>
                                        <td class="fs-sm">{{ $participant->enrolled_at?->format('Y-m-d H:i:s') }}</td>
                                        <td class="fs-sm">{{ $participant->contact->last_login_at?->format('Y-m-d H:i:s') ?? '—' }}</td>
                                        <td class="fs-sm">
                                            @if($credentialAssignment)
                                                {{ $credentialAssignment->credential->username }}
                                                <form action="{{ route('workshops.credentials.assignments.release', $credentialAssignment) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0 ms-1">Release</button>
                                                </form>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($participant->completed_at)
                                                <span class="badge bg-success">Done</span>
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
                                                @if($participant->contact->portal_access)
                                                <form action="{{ route('workshops.sessions.participants.reset-password', [$workshop, $session, $participant->contact]) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset the password for {{ $participant->contact->full_name }}?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Reset password</button>
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <p class="text-muted text-center py-4 mb-0">No participants invited yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
