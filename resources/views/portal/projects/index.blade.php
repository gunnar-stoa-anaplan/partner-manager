@extends('portal.layout', ['pageTitle' => 'Projects'])

@section('content')
<h4 class="mb-4">Projects — {{ $partner->name }}</h4>

<div class="card portal-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Project</th><th>Account</th><th>Status</th><th>Phase</th><th style="width:20%">Completion</th><th>Start</th></tr></thead>
                <tbody>
                @forelse($projects as $project)
                    @php $pct = $project->completion_percentage ?? 0; @endphp
                    <tr>
                        <td><a href="{{ route('portal.projects.show', $project) }}">{{ $project->name }}</a></td>
                        <td>{{ $project->account_name ?? '—' }}</td>
                        <td><span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'primary' : 'warning') }}">{{ ucfirst($project->status ?? 'unknown') }}</span></td>
                        <td>{{ $project->phase ?? '—' }}</td>
                        <td>
                            <div class="progress" style="height:8px"><div class="progress-bar" style="width:{{ $pct }}%"></div></div>
                            <small class="text-muted">{{ $pct }}%</small>
                        </td>
                        <td>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M j, Y') : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-3">No projects found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
