@extends('layouts.vertical', ['pageTitle' => $journey->name.' — Standup'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $journey->name.' — Standup',
        'pageSubTitle1' => 'Journeys',
        'pageSubTitle1Url' => route('journeys.index'),
        'pageSubTitle2' => $journey->name,
        'pageSubTitle2Url' => route('journeys.show', $journey),
        'pageSubText' => 'Asset workflow status + trend + open blockers',
    ])
@endsection

@php
    $statusMeta = [
        'missing' => ['label' => 'Missing', 'class' => 'bg-danger', 'text' => 'text-danger', 'hex' => '#dc3545'],
        'in_review' => ['label' => 'In Review', 'class' => 'bg-info', 'text' => 'text-info', 'hex' => '#0dcaf0'],
        'needs_revision' => ['label' => 'Needs Revision', 'class' => 'bg-warning', 'text' => 'text-warning', 'hex' => '#ffc107'],
        'approved' => ['label' => 'Approved', 'class' => 'bg-success', 'text' => 'text-success', 'hex' => '#198754'],
    ];
@endphp

@section('content')
    {{-- Status pills (today) --}}
    <div class="row g-3 mb-3">
        @foreach ($statusMeta as $key => $meta)
            <div class="col-6 col-md-3">
                <div class="card shadow-0 text-center">
                    <div class="card-body py-3">
                        <div class="display-6 fw-bold {{ $meta['text'] }}">{{ $statusCounts[$key] ?? 0 }}</div>
                        <div class="text-muted small">{{ $meta['label'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Trend chart --}}
    <div class="card shadow-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Trend</h5>
                <div class="btn-group btn-group-sm" role="group" aria-label="Resolution">
                    <a class="btn btn-outline-secondary {{ $resolution === 'daily' ? 'active' : '' }}" href="{{ route('journeys.standup', [$journey, 'resolution' => 'daily']) }}">Daily</a>
                    <a class="btn btn-outline-secondary {{ $resolution === 'weekly' ? 'active' : '' }}" href="{{ route('journeys.standup', [$journey, 'resolution' => 'weekly']) }}">Weekly</a>
                    <a class="btn btn-outline-secondary {{ $resolution === 'monthly' ? 'active' : '' }}" href="{{ route('journeys.standup', [$journey, 'resolution' => 'monthly']) }}">Monthly</a>
                </div>
            </div>

            @php
                $maxTotal = max(1, $series->max(fn ($p) => array_sum(array_intersect_key($p['counts'], $statusMeta))));
                $bucketCount = max(1, $series->count());
                $columnWidthPct = 100 / $bucketCount;
            @endphp

            <div class="d-flex align-items-end" style="height: 220px; gap: 2px;">
                @foreach ($series as $point)
                    @php $totalForPoint = array_sum(array_intersect_key($point['counts'], $statusMeta)); @endphp
                    <div class="d-flex flex-column align-items-center" style="width: {{ $columnWidthPct }}%; height: 100%;">
                        <div class="d-flex flex-column-reverse w-100" style="flex: 1;">
                            @foreach ($statusMeta as $key => $meta)
                                @php
                                    $n = $point['counts'][$key] ?? 0;
                                    $pct = $maxTotal > 0 ? round(($n / $maxTotal) * 100, 2) : 0;
                                @endphp
                                @if ($n > 0)
                                    <div class="{{ $meta['class'] }}" style="height: {{ $pct }}%;" title="{{ $meta['label'] }}: {{ $n }} ({{ $point['label'] }})"></div>
                                @endif
                            @endforeach
                        </div>
                        <div class="text-muted" style="font-size: 9px; transform: rotate(-30deg); transform-origin: left top; height: 18px; white-space: nowrap; margin-top: 6px;">
                            {{ $point['label'] }}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-end gap-3 small mt-3">
                @foreach ($statusMeta as $key => $meta)
                    <span><span class="d-inline-block rounded" style="width: 12px; height: 12px; background: {{ $meta['hex'] }};"></span> {{ $meta['label'] }}</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Open blockers --}}
    <div class="card shadow-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Open blockers ({{ $blockers->count() }})</h5>
                <a href="{{ route('journeys.blockers.index', $journey) }}" class="btn btn-sm btn-outline-primary">Manage blockers</a>
            </div>
            @if ($blockers->isEmpty())
                <div class="text-muted">No open blockers. Surface new blockers from the Blockers page or while reviewing assets.</div>
            @else
                <table class="table table-sm align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Title</th>
                            <th>Asset</th>
                            <th>Assigned To</th>
                            <th>Target Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($blockers as $blocker)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $blocker->title }}</div>
                                @if ($blocker->description)
                                    <div class="text-muted small">{{ \Illuminate\Support\Str::limit($blocker->description, 100) }}</div>
                                @endif
                            </td>
                            <td>{{ $blocker->journeyAsset?->asset_name ?? '—' }}</td>
                            <td>{{ $blocker->assignedToUser?->name ?? $blocker->assigned_to_external ?? '—' }}</td>
                            <td>
                                @if ($blocker->target_resolution_date)
                                    <span class="@if ($blocker->target_resolution_date->isPast()) text-danger fw-bold @endif">
                                        {{ $blocker->target_resolution_date->format('M j') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge @if ($blocker->status === 'open') bg-danger @elseif ($blocker->status === 'in_progress') bg-warning @else bg-secondary @endif">
                                    {{ str_replace('_', ' ', $blocker->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
