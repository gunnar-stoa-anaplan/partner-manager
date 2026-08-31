@extends('layouts.vertical', ['pageTitle' => 'Session Analytics'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Session Analytics', 'pageSubTitle1' => 'Workshops', 'pageSubTitle1Url' => route('workshops.dashboard'), 'pageSubText' => 'Every session side by side, for comparing progression across cohorts'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item"><a class="nav-link" href="{{ route('workshops.analytics.workshops', request()->only('workshop_id')) }}">Workshops</a></li>
            <li class="nav-item"><a class="nav-link active" href="{{ route('workshops.analytics.sessions', request()->only('workshop_id')) }}">Sessions</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('workshops.analytics.participation', request()->only('workshop_id')) }}">Participation</a></li>
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
                            <th>Workshop</th>
                            <th>Session</th>
                            <th>Dates</th>
                            <th>Status</th>
                            <th class="text-end">Participants</th>
                            <th class="text-end">Pre Avg</th>
                            <th class="text-end">Post Avg</th>
                            <th class="text-end">Δ</th>
                            <th class="text-end">KC Pass</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($per_session as $row)
                            <tr>
                                <td><a href="{{ route('workshops.show', $row['workshop']) }}">{{ $row['workshop']->name }}</a></td>
                                <td><a href="{{ route('workshops.sessions.show', [$row['workshop'], $row['session']]) }}">{{ $row['session']->name }}</a></td>
                                <td class="fs-sm">{{ $row['session']->start_date->format('Y-m-d') }} → {{ $row['session']->end_date->format('Y-m-d') }}</td>
                                <td><span class="badge bg-secondary">{{ \App\Models\WorkshopSession::STATUSES[$row['session']->status] ?? $row['session']->status }}</span></td>
                                <td class="text-end">{{ $row['participants'] }}</td>
                                <td class="text-end">{{ $row['pre_avg'] ?? '—' }}</td>
                                <td class="text-end">{{ $row['post_avg'] ?? '—' }}</td>
                                <td class="text-end fw-medium {{ $row['delta'] !== null ? ($row['delta'] >= 0 ? 'text-success' : 'text-danger') : '' }}">
                                    {{ $row['delta'] !== null ? ($row['delta'] >= 0 ? '+'.$row['delta'] : $row['delta']) : '—' }}
                                </td>
                                <td class="text-end">{{ $row['kc_pass_rate'] !== null ? $row['kc_pass_rate'].'%' : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No sessions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
