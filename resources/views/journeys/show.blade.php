@extends('layouts.vertical', ['pageTitle' => $journey->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $journey->name,
        'pageSubTitle1' => 'Journeys',
        'pageSubTitle1Url' => route('journeys.index'),
        'pageSubText' => $journey->product_area . ' · ' . $journey->persona,
    ])
@endsection

@section('content')

    {{-- Metric Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card shadow-0 text-center">
                <div class="card-body">
                    <div class="display-6 fw-bold text-primary">{{ $totalParticipants }}</div>
                    <div class="text-muted small mt-1">Total Participants</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-0 text-center">
                <div class="card-body">
                    <div class="display-6 fw-bold text-success">{{ $activeCount }}</div>
                    <div class="text-muted small mt-1">Active</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-0 text-center">
                <div class="card-body">
                    <div class="display-6 fw-bold text-warning">{{ $stalledCount }}</div>
                    <div class="text-muted small mt-1">Stalled (&gt;14 days)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card shadow-0 text-center">
                <div class="card-body">
                    <div class="display-6 fw-bold text-danger">{{ $pendingCount }}</div>
                    <div class="text-muted small mt-1">Pending Actions</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stage Pipeline --}}
    <div class="card shadow-0 mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Stage Pipeline</h5>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @php
                    $pipelineColors = [1 => 'secondary', 2 => 'info', 3 => 'primary', 4 => 'warning', 5 => 'success'];
                @endphp
                @foreach($stages as $stage)
                    <div class="col">
                        <div class="card border text-center h-100">
                            <div class="card-body py-3 px-2">
                                <div class="h4 fw-bold text-{{ $pipelineColors[$stage['num']] ?? 'secondary' }}">
                                    {{ $stage['count'] }}
                                </div>
                                <div class="small text-muted">{{ $stage['name'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="card shadow-0 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Activity</h5>
            <a href="{{ route('journeys.activity', $journey) }}" class="btn btn-xs btn-outline-secondary">View All</a>
        </div>
        <div class="card-body p-0">
            @if($recentActivity->isEmpty())
                <div class="text-center text-muted py-4">No activity recorded yet.</div>
            @else
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Event</th>
                            <th>Participant</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivity as $entry)
                            <tr>
                                <td class="text-muted small text-nowrap">
                                    {{ isset($entry->created_at) ? \Carbon\Carbon::parse($entry->created_at)->format('M j, H:i') : '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $entry->event_type ?? '—' }}</span>
                                </td>
                                <td>{{ $entry->participant ?? '—' }}</td>
                                <td class="text-muted small">{{ $entry->details ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Sub-page Links --}}
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('journeys.participants', $journey) }}" class="btn btn-outline-primary btn-sm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#users"></use></svg>
            All Participants
        </a>
        <a href="{{ route('journeys.pending', $journey) }}" class="btn btn-outline-warning btn-sm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#clock"></use></svg>
            Pending Actions
            @if($pendingCount > 0)
                <span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('journeys.activity', $journey) }}" class="btn btn-outline-secondary btn-sm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#activity"></use></svg>
            Activity Log
        </a>
        <a href="{{ route('journeys.builder', $journey) }}" class="btn btn-outline-success btn-sm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#layers"></use></svg>
            Builder
        </a>
    </div>

@endsection
