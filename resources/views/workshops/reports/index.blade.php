@extends('layouts.vertical', ['pageTitle' => 'Reports'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Reports', 'pageSubTitle1' => $session->name, 'pageSubTitle1Url' => route('workshops.sessions.show', [$workshop, $session])])
@endsection

@section('content')
<div class="row mb-g">
    <div class="col-md-3">
        <div class="card shadow-0 text-center"><div class="card-body"><div class="fs-32 fw-bold">{{ $total }}</div><div class="text-muted">Invited</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 text-center"><div class="card-body"><div class="fs-32 fw-bold text-success">{{ $completed }}</div><div class="text-muted">Completed</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 text-center"><div class="card-body"><div class="fs-32 fw-bold">{{ $completionRate }}%</div><div class="text-muted">Completion Rate</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 text-center"><div class="card-body"><div class="fs-32 fw-bold">{{ $surveyAvgScore }}%</div><div class="text-muted">Avg Survey Score</div></div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Session Reports</div></div>
            <div class="list-group list-group-flush">
                <a href="{{ route('workshops.reports.exec-summary', [$workshop, $session]) }}" class="list-group-item list-group-item-action">Executive Summary</a>
                <a href="{{ route('workshops.reports.leaderboard', [$workshop, $session]) }}" class="list-group-item list-group-item-action">Leaderboard</a>
                <a href="{{ route('workshops.reports.slack', [$workshop, $session]) }}" class="list-group-item list-group-item-action">Slack Post</a>
                <a href="{{ route('workshops.reports.export-json', [$workshop, $session]) }}" class="list-group-item list-group-item-action">Export JSON</a>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">Partner Sponsor Reports</div>
                @if($partners->isNotEmpty())
                    <a href="{{ route('workshops.reports.partners-zip', [$workshop, $session]) }}" class="btn btn-sm btn-outline-primary">Download All (ZIP)</a>
                @endif
            </div>
            <div class="list-group list-group-flush">
                @forelse($partners as $partner)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $partner->name }}</span>
                        <span>
                            <a href="{{ route('workshops.reports.partner', [$workshop, $session, $partner]) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            <a href="{{ route('workshops.reports.partner-zip', [$workshop, $session, $partner]) }}" class="btn btn-sm btn-outline-secondary">ZIP</a>
                        </span>
                    </div>
                @empty
                    <div class="list-group-item text-muted">No partners with participants in this session yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
