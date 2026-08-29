@extends('layouts.vertical', ['pageTitle' => $session->name.' - Leaderboard'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Session Leaderboard', 'pageSubTitle1' => $session->name, 'pageSubTitle1Url' => route('workshops.sessions.show', [$workshop, $session])])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>#</th><th>Participant</th><th>Partner</th><th>Status</th><th>Survey Score</th><th>Points</th></tr>
                    </thead>
                    <tbody>
                        @forelse($leaderboard as $index => $entry)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $entry['contact']->full_name }}</td>
                                <td>{{ $entry['contact']->partner?->name }}</td>
                                <td>
                                    @if($entry['completed'])
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-light text-dark">In progress</span>
                                    @endif
                                </td>
                                <td>{{ $entry['average_survey_score'] !== null ? $entry['average_survey_score'].'%' : '—' }}</td>
                                <td class="fw-bold">{{ $entry['points'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No participants yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
