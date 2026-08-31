@extends('layouts.vertical', ['pageTitle' => 'Participation Analytics'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Participation Analytics', 'pageSubTitle1' => 'Workshops', 'pageSubTitle1Url' => route('workshops.dashboard'), 'pageSubText' => 'Which partner firms are sending people, and completing what they start'])
@endsection

@section('content')
<style>
    .partner-bar-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
    .partner-bar-label { width: 220px; flex-shrink: 0; font-size: 0.85rem; }
    .partner-bar-track { flex-grow: 1; background: #eef2ff; border-radius: 3px; height: 0.9rem; overflow: hidden; }
    .partner-bar-fill { background: #2563eb; height: 100%; }
    .partner-bar-count { width: 3rem; text-align: right; font-size: 0.85rem; }
</style>
<div class="row">
    <div class="col-lg-12">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item"><a class="nav-link" href="{{ route('workshops.analytics.workshops', request()->only('workshop_id')) }}">Workshops</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('workshops.analytics.sessions', request()->only('workshop_id')) }}">Sessions</a></li>
            <li class="nav-item"><a class="nav-link active" href="{{ route('workshops.analytics.participation', request()->only('workshop_id')) }}">Participation</a></li>
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
            <div class="card-body">
                @if($rows->isNotEmpty())
                    @php $maxParticipants = $rows->max('participants'); @endphp
                    @foreach($rows->take(15) as $row)
                        <div class="partner-bar-row">
                            <div class="partner-bar-label">{{ $row['partner'] }}</div>
                            <div class="partner-bar-track"><div class="partner-bar-fill" style="width: {{ $maxParticipants > 0 ? round(($row['participants'] / $maxParticipants) * 100) : 0 }}%"></div></div>
                            <div class="partner-bar-count">{{ $row['participants'] }}</div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th class="text-end">Sessions</th>
                            <th class="text-end">Participants</th>
                            <th class="text-end">Completed</th>
                            <th class="text-end">Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td class="fw-medium">{{ $row['partner'] }}</td>
                                <td class="text-end">{{ $row['sessions'] }}</td>
                                <td class="text-end">{{ $row['participants'] }}</td>
                                <td class="text-end">{{ $row['completed'] }}</td>
                                <td class="text-end fw-medium {{ $row['completion_rate'] >= 75 ? 'text-success' : ($row['completion_rate'] > 0 ? 'text-warning' : 'text-danger') }}">{{ $row['completion_rate'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No participants yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
