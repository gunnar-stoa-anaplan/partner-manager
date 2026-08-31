@extends('layouts.vertical', ['pageTitle' => 'Invitation Gaps'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Invitation Gaps', 'pageSubTitle1' => 'Workshops', 'pageSubTitle1Url' => route('workshops.dashboard'), 'pageSubText' => 'Partners with no workshop participation, or none in the last six months - candidates for the next session.'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item"><a class="nav-link" href="{{ route('workshops.analytics.workshops', request()->only('workshop_id')) }}">Workshops</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('workshops.analytics.sessions', request()->only('workshop_id')) }}">Sessions</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('workshops.analytics.participation', request()->only('workshop_id')) }}">Participation</a></li>
            <li class="nav-item"><a class="nav-link active" href="{{ route('workshops.analytics.gaps', request()->only('workshop_id')) }}">Gaps</a></li>
        </ul>
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fs-sm">Workshop</label>
                        <select class="form-select form-select-sm" name="workshop_id" onchange="this.form.submit()">
                            <option value="">All Workshops</option>
                            @foreach($workshops as $option)
                                <option value="{{ $option->id }}" @selected($selected?->id === $option->id)>{{ $option->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th class="text-end">Past participants</th>
                            <th>Last session</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($candidates as $row)
                            <tr>
                                <td class="fw-medium">{{ $row['partner']->name }}</td>
                                <td class="text-end">{{ $row['participants'] }}</td>
                                <td class="text-muted fs-sm">{{ $row['last_session_at']?->format('Y-m-d') ?? 'Never' }}</td>
                                <td>
                                    @if($selected)
                                        <a href="{{ route('workshops.show', $selected) }}" class="btn btn-sm btn-outline-primary">Invite to a session →</a>
                                    @else
                                        <span class="text-muted fs-sm">Filter to a workshop to invite</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No gaps - every partner has recent participation.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
