@extends('layouts.vertical', ['pageTitle' => 'Survey Results'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Survey Results', 'pageSubTitle1' => 'Survey Templates', 'pageSubTitle1Url' => route('workshops.surveys.index'), 'pageSubText' => $template->workshop->name.' - '.(\App\Models\WorkshopSurveyTemplate::KINDS[$template->kind] ?? $template->kind)])
@endsection

@section('content')
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
                            <th>Attempt</th>
                            <th>Score</th>
                            <th>Result</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($template->responses as $response)
                            <tr>
                                <td>{{ $response->contact->full_name }}</td>
                                <td>{{ $response->contact->partner?->name }}</td>
                                <td>{{ $response->session->name }}</td>
                                <td>{{ $response->attempt_number }}</td>
                                <td>{{ $response->score }}%</td>
                                <td><span class="badge {{ $response->passed ? 'bg-success' : 'bg-danger' }}">{{ $response->passed ? 'Passed' : 'Failed' }}</span></td>
                                <td>{{ $response->submitted_at->format('M j, Y g:ia') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No responses yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
