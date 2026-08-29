@extends('layouts.vertical', ['pageTitle' => 'Survey Results'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Survey Results', 'pageSubTitle1' => 'Survey Templates', 'pageSubTitle1Url' => route('workshops.surveys.index'), 'pageSubText' => $template->workshop->name.' - '.(\App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind)])
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row mb-g">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title"><i class="fal fa-upload me-2"></i> Import Results</div></div>
            <div class="card-body">
                <form action="{{ route('workshops.surveys.upload', $template) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label fs-sm">Session</label>
                        <select class="form-select form-select-sm" name="workshop_session_id" required>
                            @foreach($template->workshop->sessions as $session)
                                <option value="{{ $session->id }}">{{ $session->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-sm">CSV/XLSX File</label>
                        <input type="file" class="form-control form-control-sm" name="file" accept=".csv,.xlsx,.xls" required>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_anonymous" value="1" id="is_anonymous">
                            <label class="form-check-label fs-sm" for="is_anonymous">Anonymous</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm">Import</button>
                    </div>
                </form>
                <div class="form-text mt-2">File needs an "email" column plus one column per question id ({{ collect($template->questions_json)->pluck('id')->implode(', ') }}).</div>

                @if($importBatches->isNotEmpty())
                    <hr>
                    <p class="fw-medium mb-2">Imported batches</p>
                    @foreach($importBatches as $batch)
                        <form action="{{ route('workshops.surveys.destroy-batch', $template) }}" method="POST" class="d-inline-flex align-items-center gap-2 mb-1 me-3" onsubmit="return confirm('Remove all responses from this import?')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="import_batch" value="{{ $batch }}">
                            <code class="fs-sm">{{ $batch }}</code>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

@if(!empty($comparison))
<div class="row mb-g">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title"><i class="fal fa-chart-line me-2"></i> Pre vs Post Comparison</div></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>Question</th><th>Pre %</th><th>Post %</th><th>Change</th></tr>
                    </thead>
                    <tbody>
                        @foreach($comparison as $row)
                            <tr>
                                <td>{{ $row['text'] }}</td>
                                <td>{{ $row['pre_avg'] ?? '—' }}{{ $row['pre_avg'] !== null ? '%' : '' }}</td>
                                <td>{{ $row['post_avg'] ?? '—' }}{{ $row['post_avg'] !== null ? '%' : '' }}</td>
                                <td>
                                    @if($row['pre_avg'] !== null && $row['post_avg'] !== null)
                                        <span class="{{ $row['post_avg'] >= $row['pre_avg'] ? 'text-success' : 'text-danger' }}">
                                            {{ $row['post_avg'] - $row['pre_avg'] >= 0 ? '+' : '' }}{{ $row['post_avg'] - $row['pre_avg'] }}%
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row mb-g">
    <div class="col-md-4">
        <div class="card shadow-0 text-center">
            <div class="card-body">
                <div class="fs-32 fw-bold">{{ $total }}</div>
                <div class="text-muted">Responses</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 text-center">
            <div class="card-body">
                <div class="fs-32 fw-bold text-success">{{ $passed }}</div>
                <div class="text-muted">Passed</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 text-center">
            <div class="card-body">
                <div class="fs-32 fw-bold">{{ $averageScore }}%</div>
                <div class="text-muted">Average Score</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Contact</th>
                            <th>Partner</th>
                            <th>Session</th>
                            <th>Source</th>
                            <th>Attempt</th>
                            <th>Score</th>
                            <th>Result</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($template->responses as $response)
                            <tr>
                                <td>
                                    @if($response->is_anonymous || !$response->contact)
                                        <span class="text-muted">Anonymous{{ $response->raw_email ? " ({$response->raw_email})" : '' }}</span>
                                    @else
                                        {{ $response->contact->full_name }}
                                    @endif
                                </td>
                                <td>{{ $response->contact?->partner?->name }}</td>
                                <td>{{ $response->session->name }}</td>
                                <td><span class="badge bg-light text-dark">{{ ucfirst($response->source) }}</span></td>
                                <td>{{ $response->attempt_number }}</td>
                                <td>{{ $response->score }}%</td>
                                <td><span class="badge {{ $response->passed ? 'bg-success' : 'bg-danger' }}">{{ $response->passed ? 'Passed' : 'Failed' }}</span></td>
                                <td>{{ $response->submitted_at->format('M j, Y g:ia') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No responses yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
