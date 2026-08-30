@extends('layouts.vertical', ['pageTitle' => $session->name.' - Leaderboard'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Session Leaderboard', 'pageSubTitle1' => $session->name, 'pageSubTitle1Url' => route('workshops.sessions.show', [$workshop, $session])])
@endsection

@section('content')
<style>
    .checkpoint-dot { display: inline-block; width: 0.85rem; height: 0.85rem; border-radius: 50%; }
    .checkpoint-dot.done { background: #15803d; }
    .checkpoint-dot.partial { background: #d97706; }
    .checkpoint-dot.todo { background: #dc2626; opacity: 0.35; }
</style>
<div class="row">
    <div class="col-lg-12">
        @if(!empty($checkpoints))
        <div class="mb-3 fs-sm text-muted">
            <span class="checkpoint-dot done me-1"></span> Done
            <span class="checkpoint-dot partial ms-3 me-1"></span> In Progress
            <span class="checkpoint-dot todo ms-3 me-1"></span> Not Started
        </div>
        @endif
        <div class="card shadow-0 mb-g">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Participant</th>
                            <th>Partner</th>
                            <th>Status</th>
                            @foreach($checkpoints as $checkpoint)
                                <th class="text-center" title="{{ $checkpoint['label'] }}">{{ $checkpoint['label'] }}</th>
                            @endforeach
                            <th>Survey Score</th>
                            <th>Points</th>
                        </tr>
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
                                @foreach($checkpoints as $checkpoint)
                                    <td class="text-center">
                                        <span class="checkpoint-dot {{ $entry['statuses'][$checkpoint['key']] ?? 'todo' }}" title="{{ $checkpoint['label'] }}: {{ ucfirst(str_replace('todo', 'not started', $entry['statuses'][$checkpoint['key']] ?? 'todo')) }}"></span>
                                    </td>
                                @endforeach
                                <td>{{ $entry['average_survey_score'] !== null ? $entry['average_survey_score'].'%' : '—' }}</td>
                                <td class="fw-bold">{{ $entry['points'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ 6 + count($checkpoints) }}" class="text-center text-muted py-4">No participants yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
