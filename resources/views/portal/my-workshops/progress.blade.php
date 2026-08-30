@extends('portal.layout')

@section('title', 'Progress')

@section('content')
<div class="container py-4">
    <h4 class="mb-1">Your Progress</h4>
    <p class="text-muted mb-4">{{ $session->workshop->name }} &middot; {{ $session->name }}</p>

    @if($credentialAssignment)
        <div class="card portal-card mb-4 border-primary">
            <div class="card-header"><h6 class="mb-0">Your Workshop Sandbox</h6></div>
            <div class="card-body">
                <p class="text-muted small mb-2">Use these credentials to log in to your Anaplan practice tenant for this workshop.</p>
                <dl class="row mb-0">
                    <dt class="col-sm-3">Username</dt>
                    <dd class="col-sm-9">{{ $credentialAssignment->credential->username }}</dd>
                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $credentialAssignment->credential->email }}</dd>
                    <dt class="col-sm-3">Password</dt>
                    <dd class="col-sm-9"><code>{{ $credentialAssignment->credential->password_enc }}</code></dd>
                    @if($credentialAssignment->hold_until)
                        <dt class="col-sm-3">Available Until</dt>
                        <dd class="col-sm-9">{{ $credentialAssignment->hold_until->format('M j, Y') }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card portal-card text-center">
                <div class="card-body">
                    <div class="fs-32 fw-bold">{{ $passedChecks }}/{{ $totalChecks }}</div>
                    <div class="text-muted">Checks Passed</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card portal-card text-center">
                <div class="card-body">
                    <div class="fs-32 fw-bold">{{ $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100) : 0 }}%</div>
                    <div class="text-muted">Complete</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card portal-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Checks</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pageProgress as $entry)
                        <tr>
                            <td><a href="{{ route('portal.my-workshops.pages.show', [$session, $entry['page']]) }}">{{ $entry['page']->title }}</a></td>
                            <td>{{ $entry['passed'] }}/{{ $entry['total'] }}</td>
                            <td>
                                @if($entry['complete'])
                                    <span class="badge bg-success">Complete</span>
                                @else
                                    <span class="badge bg-light text-dark">In progress</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No content pages yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
