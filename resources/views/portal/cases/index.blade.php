@extends('portal.layout', ['pageTitle' => 'Cases'])

@section('content')
<h4 class="mb-4">Support Cases — {{ $partner->name }}</h4>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card portal-card text-center py-3">
            <h3 class="text-warning mb-0">{{ $openCount }}</h3>
            <small class="text-muted">Open Cases</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card portal-card text-center py-3">
            <h3 class="text-danger mb-0">{{ $escalatedCount }}</h3>
            <small class="text-muted">Escalated</small>
        </div>
    </div>
</div>

{{-- Log New Case --}}
<div class="card portal-card mb-4">
    <div class="card-header">
        <a class="text-decoration-none" data-bs-toggle="collapse" href="#newCaseForm"><strong>Log New Case</strong></a>
    </div>
    <div class="collapse" id="newCaseForm">
        <div class="card-body">
            <form action="{{ route('portal.cases.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Severity <span class="text-danger">*</span></label>
                        <select name="severity" class="form-select" required>
                            @foreach(\App\Models\SupportCase::SEVERITIES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Project</label>
                        <select name="project_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="{{ old('category') }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Log Case</button>
            </form>
        </div>
    </div>
</div>

<div class="card portal-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Case #</th><th>Subject</th><th>Project</th><th>Severity</th><th>Status</th><th>Opened</th></tr></thead>
                <tbody>
                @forelse($cases as $case)
                    <tr>
                        <td>{{ $case->case_number ?? '—' }}</td>
                        <td>{{ $case->subject }}</td>
                        <td>{{ $case->project?->name ?? '—' }}</td>
                        <td><span class="badge bg-{{ $case->severity === 'critical' ? 'danger' : ($case->severity === 'high' ? 'warning' : 'secondary') }}">{{ ucfirst($case->severity) }}</span></td>
                        <td>
                            @php $caseStatusColors = ['open'=>'warning','in_progress'=>'info','escalated'=>'danger','resolved'=>'success','closed'=>'secondary']; @endphp
                            <span class="badge bg-{{ $caseStatusColors[$case->status] ?? 'secondary' }}">{{ \App\Models\SupportCase::STATUSES[$case->status] ?? $case->status }}</span>
                        </td>
                        <td>{{ $case->opened_date?->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-3">No cases found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($cases->hasPages())
    <div class="card-footer">{{ $cases->links() }}</div>
    @endif
</div>
@endsection
