@extends('portal.layout', ['pageTitle' => $project->name])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $project->name }}</h4>
    <a href="{{ route('portal.projects.index') }}" class="btn btn-outline-secondary btn-sm">Back to Projects</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card portal-card">
            <div class="card-header"><strong>Project Details</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Account</small>
                        <strong>{{ $project->account_name ?? '—' }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'primary' : 'warning') }}">{{ ucfirst($project->status ?? 'unknown') }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Phase</small>
                        <strong>{{ $project->phase ?? '—' }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Completion</small>
                        @php $pct = $project->completion_percentage ?? 0; @endphp
                        <div class="progress mt-1" style="height:10px"><div class="progress-bar" style="width:{{ $pct }}%"></div></div>
                        <small>{{ $pct }}%</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Start Date</small>
                        <strong>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M j, Y') : '—' }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Go-Live Date</small>
                        <strong>{{ $project->go_live_date ? \Carbon\Carbon::parse($project->go_live_date)->format('M j, Y') : '—' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card portal-card">
            <div class="card-header"><strong>Team</strong></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($team as $member)
                        <li class="list-group-item">{{ $member->full_name }} <small class="text-muted">{{ $member->title ?? '' }}</small></li>
                    @empty
                        <li class="list-group-item text-muted">No team members assigned</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card portal-card">
    <div class="card-header"><strong>Support Cases</strong></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Case #</th><th>Subject</th><th>Severity</th><th>Status</th><th>Opened</th></tr></thead>
                <tbody>
                @forelse($cases as $case)
                    <tr>
                        <td>{{ $case->case_number ?? '—' }}</td>
                        <td>{{ $case->subject }}</td>
                        <td><span class="badge bg-{{ $case->severity === 'critical' ? 'danger' : ($case->severity === 'high' ? 'warning' : 'secondary') }}">{{ ucfirst($case->severity) }}</span></td>
                        <td>{{ \App\Models\SupportCase::STATUSES[$case->status] ?? $case->status }}</td>
                        <td>{{ $case->opened_date?->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-3">No cases for this project.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
