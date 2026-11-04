@extends('layouts.vertical', ['pageTitle' => 'My Journeys'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'My Journeys',
        'pageSubTitle1' => 'Connected Enablement',
        'pageSubText' => 'Owner dashboard — asset workflow across all assigned journeys',
    ])
@endsection

@php
    $statusMeta = [
        'missing' => ['label' => 'Missing', 'class' => 'bg-danger', 'text' => 'text-danger'],
        'in_review' => ['label' => 'In Review', 'class' => 'bg-info', 'text' => 'text-info'],
        'needs_revision' => ['label' => 'Needs Revision', 'class' => 'bg-warning', 'text' => 'text-warning'],
        'approved' => ['label' => 'Approved', 'class' => 'bg-success', 'text' => 'text-success'],
    ];

    $progressBar = function (array $counts) use ($statusMeta) {
        $total = max(1, array_sum(array_intersect_key($counts, $statusMeta)));
        $segments = '';
        foreach ($statusMeta as $key => $meta) {
            $n = $counts[$key] ?? 0;
            if ($n > 0) {
                $pct = round(($n / $total) * 100, 1);
                $segments .= '<div class="progress-bar '.$meta['class'].'" role="progressbar" style="width: '.$pct.'%" title="'.$meta['label'].': '.$n.'"></div>';
            }
        }
        return $segments;
    };
@endphp

@section('content')
    @if (session('flash.success'))
        <div class="alert alert-success">{{ session('flash.success') }}</div>
    @endif

    {{-- Three Business Pillars --}}
    <div class="card shadow-0 mb-4 border-start border-4 border-primary">
        <div class="card-body">
            <h5 class="card-title mb-3">Business outcomes — what these journeys exist to drive</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="text-uppercase text-muted small fw-bold mb-1">Pipeline</div>
                        <div class="display-6 fw-bold text-primary">
                            @if ($businessPillars['pipeline_value'] !== null)
                                ${{ number_format($businessPillars['pipeline_value'] / 1000, 0) }}K
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            Linked pipeline across {{ $businessPillars['pipeline_journey_count'] }} of {{ $journeys->count() }} journey(s)
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="text-uppercase text-muted small fw-bold mb-1">Win rate</div>
                        <div class="display-6 fw-bold text-success">
                            @if ($businessPillars['win_rate_avg'] !== null)
                                {{ $businessPillars['win_rate_avg'] }}%
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            Avg close rate, journey-attributed opportunities
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="text-uppercase text-muted small fw-bold mb-1">Delivery excellence</div>
                        <div class="display-6 fw-bold text-info">
                            @if ($businessPillars['delivery_excellence_avg'] !== null)
                                {{ $businessPillars['delivery_excellence_avg'] }}%
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            Zero-trouble deployment rate, post-launch
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-muted small mt-3">
                Manual entry for v0; auto-mapping from Salesforce + delivery health pipelines is a follow-up build (see <code>journey-opportunity-mapping-design.md</code>).
            </div>
        </div>
    </div>

    {{-- Aggregate Across All My Journeys --}}
    <div class="card shadow-0 mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">All my journeys — aggregate</h5>
            <div class="row g-3 align-items-center">
                @foreach ($statusMeta as $key => $meta)
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="display-6 fw-bold {{ $meta['text'] }}">{{ $aggregate[$key] ?? 0 }}</span>
                            <span class="text-muted small">{{ $meta['label'] }}</span>
                        </div>
                        <div class="text-muted small">
                            @php $tot = max(1, $aggregate['total']); @endphp
                            {{ round((($aggregate[$key] ?? 0) / $tot) * 100, 1) }}%
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="progress mt-3" style="height: 18px;">
                {!! $progressBar($aggregate) !!}
            </div>
            <div class="text-muted small mt-2">{{ $aggregate['total'] }} total assets tracked across {{ $journeys->count() }} journey(s)</div>
        </div>
    </div>

    {{-- Per-journey list --}}
    <div class="card shadow-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Journey</th>
                        <th class="text-center">Assets</th>
                        <th style="width: 35%;">Progress</th>
                        <th class="text-center">Approved</th>
                        <th class="text-center">Open Blockers</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($journeys as $journey)
                    @php
                        $perJ = $statusByJourney[$journey->id] ?? [];
                        $perJ['total'] = array_sum(array_intersect_key($perJ, $statusMeta));
                        $approvedPct = $perJ['total'] > 0 ? round((($perJ['approved'] ?? 0) / $perJ['total']) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('journeys.show', $journey) }}" class="fw-semibold">{{ $journey->name }}</a>
                            <div class="text-muted small">
                                {{ ucfirst($journey->persona) }} · {{ $journey->product_area }} · {{ ucfirst($journey->status) }}
                            </div>
                        </td>
                        <td class="text-center">{{ $perJ['total'] ?? 0 }}</td>
                        <td>
                            <div class="progress" style="height: 14px;">
                                {!! $progressBar($perJ) !!}
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-1">
                                @foreach ($statusMeta as $key => $meta)
                                    <span class="{{ $meta['text'] }}">{{ $meta['label'] }}: {{ $perJ[$key] ?? 0 }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-success">{{ $approvedPct }}%</span>
                        </td>
                        <td class="text-center">
                            @if ($journey->open_blockers_count > 0)
                                <a href="{{ route('journeys.blockers.index', $journey) }}" class="badge bg-warning text-decoration-none">{{ $journey->open_blockers_count }}</a>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('journeys.assets.index', $journey) }}" class="btn btn-sm btn-outline-primary">Assets</a>
                            <a href="{{ route('journeys.standup', $journey) }}" class="btn btn-sm btn-outline-secondary">Standup</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No journeys assigned to you yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Status Changes --}}
    @if ($recentChanges->count() > 0)
        <div class="card shadow-0 mt-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Recent status changes</h5>
                <ul class="list-unstyled mb-0">
                    @foreach ($recentChanges as $change)
                        <li class="border-bottom py-2">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="fw-semibold">{{ $change->journeyAsset?->asset_name }}</span>
                                    <span class="text-muted">·</span>
                                    <span class="small text-muted">{{ $change->journeyAsset?->journey?->name }}</span>
                                </div>
                                <div class="text-muted small">{{ $change->created_at?->diffForHumans() }}</div>
                            </div>
                            <div class="small">
                                <span class="text-muted">{{ $change->old_status ?: 'created' }}</span>
                                <span class="text-muted">→</span>
                                <span class="{{ $statusMeta[$change->new_status]['text'] ?? '' }} fw-semibold">{{ $statusMeta[$change->new_status]['label'] ?? $change->new_status }}</span>
                                @if ($change->changedByUser)
                                    <span class="text-muted">by {{ $change->changedByUser->name }}</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endsection
