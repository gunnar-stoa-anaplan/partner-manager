@extends('layouts.vertical', ['pageTitle' => 'Exec Summary'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Executive Summary', 'pageSubTitle1' => $session->name, 'pageSubTitle1Url' => route('workshops.sessions.show', [$workshop, $session])])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">{{ $workshop->name }}</div>
                <a href="{{ route('workshops.reports.slack', [$workshop, $session]) }}" class="btn btn-sm btn-outline-secondary me-1">Slack Post</a>
                <a href="{{ route('workshops.reports.export-json', [$workshop, $session]) }}" class="btn btn-sm btn-outline-secondary">Export JSON</a>
            </div>
            <div class="card-body">
                <h5>{{ $session->name }}</h5>
                <p class="text-muted">{{ $session->start_date->format('F j') }} - {{ $session->end_date->format('F j, Y') }}</p>

                <p>
                    This session had <strong>{{ $total }}</strong> invited participant{{ $total === 1 ? '' : 's' }},
                    of whom <strong>{{ $completed }}</strong> ({{ $completionRate }}%) completed the workshop.
                    @if($surveyTotal > 0)
                        Of the {{ $surveyTotal }} post-workshop survey response{{ $surveyTotal === 1 ? '' : 's' }} collected,
                        {{ $surveyPassed }} passed, for an average score of {{ $surveyAvgScore }}%.
                    @endif
                    The session generated {{ $openThreads + $answeredThreads }} discussion thread{{ ($openThreads + $answeredThreads) === 1 ? '' : 's' }},
                    with {{ $openThreads }} still open and {{ $answeredThreads }} answered.
                </p>

                @if($partnerBreakdown->isNotEmpty())
                    <h6 class="mt-4">By Partner</h6>
                    <table class="table table-sm">
                        <thead><tr><th>Partner</th><th>Invited</th><th>Completed</th></tr></thead>
                        <tbody>
                            @foreach($partnerBreakdown as $row)
                                <tr><td>{{ $row['partner'] }}</td><td>{{ $row['total'] }}</td><td>{{ $row['completed'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>

                    <h6 class="mt-4">Per-Partner Sponsor Reports</h6>
                    <ul>
                        @foreach($session->participants->pluck('contact.partner')->filter()->unique('id') as $partner)
                            <li>
                                {{ $partner->name }} -
                                <a href="{{ route('workshops.reports.partner', [$workshop, $session, $partner]) }}">View</a> |
                                <a href="{{ route('workshops.reports.partner-zip', [$workshop, $session, $partner]) }}">Download ZIP</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
