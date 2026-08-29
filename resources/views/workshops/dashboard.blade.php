@extends('layouts.vertical', ['pageTitle' => 'Workshops Dashboard'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Workshops', 'pageSubText' => 'Partner delivery workshop overview'])
@endsection

@section('content')
<div class="row mb-g">
    <div class="col-md-3">
        <div class="card shadow-0 text-center">
            <div class="card-body">
                <div class="fs-32 fw-bold">{{ $totalWorkshops }}</div>
                <div class="text-muted">Workshops</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 text-center">
            <div class="card-body">
                <div class="fs-32 fw-bold">{{ $totalSessions }}</div>
                <div class="text-muted">Sessions</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 text-center">
            <div class="card-body">
                <div class="fs-32 fw-bold">{{ $totalParticipants }}</div>
                <div class="text-muted">Participants</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 text-center">
            <div class="card-body">
                <div class="fs-32 fw-bold text-success">{{ $completionRate }}%</div>
                <div class="text-muted">Completion Rate</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1"><i class="fal fa-check-square me-2"></i> Workshops</div>
                <a href="{{ route('workshops.create') }}" class="btn btn-primary btn-sm">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                    New Workshop
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Workshop</th>
                            <th>Sessions</th>
                            <th>Participants</th>
                            <th>Completed</th>
                            <th>Rate</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workshopSummaries as $summary)
                            <tr>
                                <td><a href="{{ route('workshops.show', $summary['workshop']) }}">{{ $summary['workshop']->name }}</a></td>
                                <td>{{ $summary['sessions'] }}</td>
                                <td>{{ $summary['participants'] }}</td>
                                <td>{{ $summary['completed'] }}</td>
                                <td>{{ $summary['completion_rate'] }}%</td>
                                <td><a href="{{ route('workshops.rollup', $summary['workshop']) }}" class="btn btn-sm btn-outline-primary">Report</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No workshops yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title"><i class="fal fa-calendar me-2"></i> Upcoming Sessions</div>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($upcomingSessions as $session)
                    <li class="list-group-item">
                        <a href="{{ route('workshops.sessions.show', [$session->workshop, $session]) }}">{{ $session->workshop->name }}</a>
                        <div class="text-muted fs-sm">{{ $session->name }} &middot; {{ $session->start_date->format('M j, Y') }} &middot; {{ $session->active_participants_count }} invited</div>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No upcoming sessions.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
