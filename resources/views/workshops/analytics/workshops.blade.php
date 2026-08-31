@extends('layouts.vertical', ['pageTitle' => 'Workshop Analytics'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Workshop Analytics', 'pageSubTitle1' => 'Workshops', 'pageSubTitle1Url' => route('workshops.dashboard'), 'pageSubText' => 'Cross-session progression for one workshop, or all of them together'])
@endsection

@section('content')
<style>
    .stat-tile { border: 1px solid #e5e7eb; border-radius: 6px; padding: 0.9rem 1.1rem; }
    .stat-tile .stat-label { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: #6b7280; }
    .stat-tile .stat-value { font-size: 1.6rem; font-weight: 700; color: #1e293b; }
    .partner-bar-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
    .partner-bar-label { width: 220px; flex-shrink: 0; font-size: 0.85rem; }
    .partner-bar-track { flex-grow: 1; background: #eef2ff; border-radius: 3px; height: 0.9rem; overflow: hidden; }
    .partner-bar-fill { background: #2563eb; height: 100%; }
    .partner-bar-count { width: 3rem; text-align: right; font-size: 0.85rem; }
</style>
<div class="row">
    <div class="col-lg-12">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item"><a class="nav-link active" href="{{ route('workshops.analytics.workshops', request()->only('workshop_id')) }}">Workshops</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('workshops.analytics.sessions', request()->only('workshop_id')) }}">Sessions</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('workshops.analytics.participation', request()->only('workshop_id')) }}">Participation</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('workshops.analytics.gaps', request()->only('workshop_id')) }}">Gaps</a></li>
        </ul>
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end mb-3">
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

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-2">
                        <div class="stat-tile">
                            <div class="stat-label">Workshops</div>
                            <div class="stat-value">{{ $workshops_count }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="stat-tile">
                            <div class="stat-label">Sessions</div>
                            <div class="stat-value">{{ $sessions_count }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="stat-tile">
                            <div class="stat-label">Participants</div>
                            <div class="stat-value">{{ $participants_count }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="stat-tile">
                            <div class="stat-label">Partner Firms</div>
                            <div class="stat-value">{{ $partner_firms_count }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="stat-tile">
                            <div class="stat-label">Avg Pre → Post Δ</div>
                            <div class="stat-value {{ $avg_delta !== null && $avg_delta < 0 ? 'text-danger' : 'text-success' }}">{{ $avg_delta !== null ? ($avg_delta >= 0 ? '+' : '').$avg_delta : '—' }}</div>
                        </div>
                    </div>
                </div>

                @if($per_session->isNotEmpty())
                <h6 class="mb-2">Per-session progression</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-borderless mb-0">
                        <thead>
                            <tr class="text-muted fs-xs text-uppercase">
                                <th>Workshop</th>
                                <th>Session</th>
                                <th class="text-end">Participants</th>
                                <th class="text-end">Pre Avg</th>
                                <th class="text-end">Post Avg</th>
                                <th class="text-end">Δ</th>
                                <th class="text-end">KC Pass</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($per_session as $row)
                                <tr class="border-top">
                                    <td><a href="{{ route('workshops.show', $row['workshop']) }}">{{ $row['workshop']->name }}</a></td>
                                    <td><a href="{{ route('workshops.sessions.show', [$row['workshop'], $row['session']]) }}">{{ $row['session']->name }}</a></td>
                                    <td class="text-end">{{ $row['participants'] }}</td>
                                    <td class="text-end">{{ $row['pre_avg'] ?? '—' }}</td>
                                    <td class="text-end">{{ $row['post_avg'] ?? '—' }}</td>
                                    <td class="text-end fw-medium {{ $row['delta'] !== null ? ($row['delta'] >= 0 ? 'text-success' : 'text-danger') : '' }}">
                                        {{ $row['delta'] !== null ? ($row['delta'] >= 0 ? '+'.$row['delta'] : $row['delta']) : '—' }}
                                    </td>
                                    <td class="text-end">{{ $row['kc_pass_rate'] !== null ? $row['kc_pass_rate'].'%' : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if($top_partners->isNotEmpty())
                <h6 class="mb-2">Top partners by participant volume</h6>
                @php $maxCount = $top_partners->max('count'); @endphp
                @foreach($top_partners->take(10) as $partner)
                    <div class="partner-bar-row">
                        <div class="partner-bar-label">{{ $partner['name'] }}</div>
                        <div class="partner-bar-track"><div class="partner-bar-fill" style="width: {{ $maxCount > 0 ? round(($partner['count'] / $maxCount) * 100) : 0 }}%"></div></div>
                        <div class="partner-bar-count">{{ $partner['count'] }}</div>
                    </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
