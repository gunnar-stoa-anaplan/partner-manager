@extends('layouts.vertical', ['pageTitle' => $workshop->name.' - Progress Report'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Progress Report', 'pageSubTitle1' => $workshop->name, 'pageSubTitle1Url' => route('workshops.show', $workshop), 'pageSubText' => 'Completion across all sessions and partners'])
@endsection

@section('content')
<div class="row mb-g">
    <div class="col-md-4">
        <div class="card shadow-0 text-center">
            <div class="card-body">
                <div class="fs-32 fw-bold">{{ $totalParticipants }}</div>
                <div class="text-muted">Total Invited</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 text-center">
            <div class="card-body">
                <div class="fs-32 fw-bold text-success">{{ $totalCompleted }}</div>
                <div class="text-muted">Completed</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 text-center">
            <div class="card-body">
                <div class="fs-32 fw-bold">{{ $totalParticipants > 0 ? round(($totalCompleted / $totalParticipants) * 100) : 0 }}%</div>
                <div class="text-muted">Completion Rate</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title"><i class="fal fa-calendar me-2"></i> By Session</div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Session</th>
                            <th>Invited</th>
                            <th>Completed</th>
                            <th>Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessionStats as $stat)
                            <tr>
                                <td><a href="{{ route('workshops.sessions.show', [$workshop, $stat['session']]) }}">{{ $stat['session']->name }}</a></td>
                                <td>{{ $stat['total'] }}</td>
                                <td>{{ $stat['completed'] }}</td>
                                <td>{{ $stat['completion_rate'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No sessions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title"><i class="fal fa-building me-2"></i> By Partner</div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th>Invited</th>
                            <th>Completed</th>
                            <th>Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partnerStats as $stat)
                            <tr>
                                <td>{{ $stat['partner'] }}</td>
                                <td>{{ $stat['total'] }}</td>
                                <td>{{ $stat['completed'] }}</td>
                                <td>{{ $stat['completion_rate'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No participants yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mb-g">
    <div class="col-md-3">
        <div class="card shadow-0 text-center"><div class="card-body"><div class="fs-32 fw-bold">{{ $discussionMetrics['total_threads'] }}</div><div class="text-muted">Total Questions</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 text-center"><div class="card-body"><div class="fs-32 fw-bold text-warning">{{ $discussionMetrics['open_threads'] }}</div><div class="text-muted">Open</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 text-center"><div class="card-body"><div class="fs-32 fw-bold text-success">{{ $discussionMetrics['answered_threads'] }}</div><div class="text-muted">Answered</div></div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 text-center"><div class="card-body"><div class="fs-32 fw-bold">{{ $discussionMetrics['avg_response_minutes'] !== null ? $discussionMetrics['avg_response_minutes'].' min' : '—' }}</div><div class="text-muted">Avg. Response Time</div></div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title"><i class="fal fa-trophy me-2"></i> Leaderboard</div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Participant</th>
                            <th>Partner</th>
                            <th>Sessions Completed</th>
                            <th>Avg. Survey Score</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaderboard as $index => $entry)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $entry['contact']->full_name }}</td>
                                <td>{{ $entry['contact']->partner?->name }}</td>
                                <td>{{ $entry['sessions_completed'] }}</td>
                                <td>{{ $entry['average_survey_score'] !== null ? $entry['average_survey_score'].'%' : '—' }}</td>
                                <td class="fw-bold">{{ $entry['points'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No participants yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
