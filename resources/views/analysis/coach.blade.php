@extends('layouts.vertical', ['pageTitle' => 'Partner Coach'])

@section('css')
<style>
    .coach-greeting {
        background: linear-gradient(135deg, var(--bs-primary, #667eea) 0%, #764ba2 100%);
        color: #fff;
        border-radius: 0.5rem;
    }
    .coach-greeting .opacity-80 { opacity: 0.8; }

    /* Partner risk cards */
    .partner-card { border-left: 4px solid transparent; transition: all 0.2s; }
    .partner-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .partner-card.risk-critical { border-left-color: #dc3545; }
    .partner-card.risk-high { border-left-color: #fd7e14; }
    .partner-card.risk-medium { border-left-color: #ffc107; }
    .partner-card.risk-low { border-left-color: #28a745; }

    .risk-icon {
        width: 48px; height: 48px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .risk-icon.critical { background: rgba(220,53,69,0.12); color: #dc3545; }
    .risk-icon.high { background: rgba(253,126,20,0.12); color: #fd7e14; }
    .risk-icon.medium { background: rgba(255,193,7,0.12); color: #856404; }
    .risk-icon.low { background: rgba(40,167,69,0.12); color: #28a745; }

    .insight-list { list-style: none; padding: 0; margin: 0; }
    .insight-list li { padding: 0.35rem 0; font-size: 0.85rem; }
    .insight-list li i { width: 20px; text-align: center; }

    .metric-mini { text-align: center; padding: 0.5rem; }
    .metric-mini .value { font-size: 1.1rem; font-weight: 700; line-height: 1.2; }
    .metric-mini .label { font-size: 0.7rem; color: var(--bs-secondary-color); }

    .health-bar { height: 6px; }
    .hover-lift { transition: all 0.2s; }
    .hover-lift:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

    .quick-win-number {
        width: 28px; height: 28px; line-height: 28px;
        font-size: 12px; text-align: center;
        border-radius: 50%;
    }
    .cursor-pointer { cursor: pointer; }
    .qw-chevron { transition: transform 0.2s ease; }
    [aria-expanded="true"] .qw-chevron { transform: rotate(180deg); }

    .focus-card .panel-hdr { color: #fff; }
    .focus-card-close .panel-hdr { background-color: #28a745 !important; }
    .focus-card-enable .panel-hdr { background-color: #e69500 !important; }
    .focus-card-pipeline .panel-hdr { background-color: #138496 !important; }
</style>
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Partner Coach', 'pageSubTitle1' => 'Recommendations', 'pageSubTitle2'=> 'Analysis'])
@endsection

@section('content')

@php
    $userName = Auth::user()->name;
    $partnerCount = $partners->count();
    $weekOf = now()->startOfWeek()->format('M j');
    $weekEnd = now()->endOfWeek()->format('M j, Y');

    $totalOpps = $partners->sum(fn($p) => $p->opportunities->count());
    $totalPipeline = $partners->sum(fn($p) => $p->opportunities->sum('amount'));
    $totalResources = $partners->sum(fn($p) => $p->resources->count());
    $certifiedResources = $partners->sum(fn($p) => $p->resources->where('status', 'Completed')->count());
    $certPct = $totalResources > 0 ? round(($certifiedResources / $totalResources) * 100) : 0;

    // ─── Per-Partner Analysis ──────────────────────────────────────
    $partnerInsights = $partners->map(function($p) {
        $opps = $p->opportunities;
        $res = $p->resources;
        $projs = $p->projects;

        // Scores
        $pipeline = $opps->sum('amount');
        $pipelineScore = min(100, ($pipeline / 1000000) * 100);
        $certCount = $res->where('status', 'Completed')->count();
        $resourceScore = $res->count() > 0 ? ($certCount / $res->count()) * 100 : 0;
        $avgCompletion = $projs->count() > 0 ? $projs->avg('percent_complete') ?? 0 : 0;
        $composite = ($pipelineScore * 0.4) + ($resourceScore * 0.35) + ($avgCompletion * 0.25);
        $health = $composite >= 70 ? 'low' : ($composite >= 45 ? 'medium' : ($composite >= 20 ? 'high' : 'critical'));

        // Build all risks for this partner, ranked by severity
        $risks = collect();

        // Risk: Deals closing soon with no certified resources
        $closingSoon = $opps->filter(fn($o) => $o->close_date && now()->diffInDays($o->close_date, false) >= 0 && now()->diffInDays($o->close_date, false) <= 30);
        if ($closingSoon->count() > 0 && $certCount === 0) {
            $risks->push([
                'severity' => 'critical', 'weight' => 100,
                'icon' => 'exclamation-triangle',
                'title' => $closingSoon->count() . ' deal(s) worth $' . number_format($closingSoon->sum('amount')) . ' closing within 30 days with zero certified resources',
                'action' => 'Urgent: fast-track certification or assign co-delivery resources immediately.',
            ]);
        }

        // Risk: No delivery resources at all
        $deliveryCert = $res->where('role', 'Delivery')->where('status', 'Completed')->count();
        if ($deliveryCert === 0 && $projs->count() > 0) {
            $risks->push([
                'severity' => 'critical', 'weight' => 90,
                'icon' => 'user-hard-hat',
                'title' => 'Active projects but zero certified delivery resources',
                'action' => 'Review delivery staffing plan. Partner cannot execute independently.',
            ]);
        }

        // Risk: Empty pipeline
        if ($opps->count() === 0) {
            $risks->push([
                'severity' => 'high', 'weight' => 75,
                'icon' => 'funnel-dollar',
                'title' => 'No active opportunities in pipeline',
                'action' => 'Schedule a joint prospecting session to build pipeline.',
            ]);
        }

        // Risk: Low certification rate
        if ($res->count() > 0 && $resourceScore < 30) {
            $risks->push([
                'severity' => 'high', 'weight' => 70,
                'icon' => 'graduation-cap',
                'title' => 'Certification rate is only ' . round($resourceScore) . '% (' . $certCount . '/' . $res->count() . ' resources)',
                'action' => 'Review certification roadmap and schedule targeted training.',
            ]);
        } elseif ($res->count() === 0) {
            $risks->push([
                'severity' => 'high', 'weight' => 65,
                'icon' => 'users-slash',
                'title' => 'No resources registered for this partner',
                'action' => 'Initiate partner enablement — add resources and begin certification tracking.',
            ]);
        }

        // Risk: Projects behind schedule
        $behindProjects = $projs->filter(function($pr) {
            if (!$pr->total_sprints || !$pr->current_sprint) return false;
            $expectedPct = ($pr->current_sprint / $pr->total_sprints) * 100;
            return ($pr->percent_complete ?? 0) < $expectedPct - 20;
        });
        if ($behindProjects->count() > 0) {
            $risks->push([
                'severity' => 'medium', 'weight' => 55,
                'icon' => 'project-diagram',
                'title' => $behindProjects->count() . ' project(s) running behind schedule',
                'action' => 'Check sprint velocity and remove blockers. Consider resource augmentation.',
            ]);
        }

        // Risk: Pipeline concentrated in few deals
        if ($opps->count() > 0 && $opps->count() <= 2 && $pipeline > 200000) {
            $risks->push([
                'severity' => 'medium', 'weight' => 50,
                'icon' => 'balance-scale',
                'title' => '$' . number_format($pipeline) . ' pipeline concentrated in just ' . $opps->count() . ' deal(s)',
                'action' => 'Diversify pipeline — high revenue risk if any deal slips.',
            ]);
        }

        // Risk: Stale deals (close date passed)
        $staleDeals = $opps->filter(fn($o) => $o->close_date && now()->diffInDays($o->close_date, false) < 0 && $o->stage !== 'Closed Won' && $o->stage !== 'Closed Lost');
        if ($staleDeals->count() > 0) {
            $risks->push([
                'severity' => 'medium', 'weight' => 45,
                'icon' => 'clock',
                'title' => $staleDeals->count() . ' opportunity(ies) past close date but still open',
                'action' => 'Update close dates or qualify out stale deals to keep pipeline accurate.',
            ]);
        }

        // If no risks found, it's healthy
        if ($risks->isEmpty()) {
            $risks->push([
                'severity' => 'low', 'weight' => 0,
                'icon' => 'check-circle',
                'title' => 'No significant risks identified',
                'action' => 'Maintain momentum. Look for upsell and expansion opportunities.',
            ]);
        }

        // Sort risks by weight descending
        $risks = $risks->sortByDesc('weight')->values();

        // Build positive insights
        $insights = collect();
        $closedWon = $opps->where('stage', 'Closed Won');
        if ($closedWon->count() > 0) {
            $insights->push(['icon' => 'trophy', 'color' => 'success', 'text' => $closedWon->count() . ' won deal(s) worth $' . number_format($closedWon->sum('amount'))]);
        }
        $highProb = $opps->filter(fn($o) => $o->probability >= 70 && $o->stage !== 'Closed Won' && $o->stage !== 'Closed Lost');
        if ($highProb->count() > 0) {
            $insights->push(['icon' => 'bullseye-arrow', 'color' => 'info', 'text' => $highProb->count() . ' high-probability deal(s) ($' . number_format($highProb->sum('amount')) . ')']);
        }
        if ($certCount > 0) {
            $insights->push(['icon' => 'user-check', 'color' => 'success', 'text' => $certCount . ' certified resource(s) across ' . $res->pluck('program')->unique()->count() . ' program(s)']);
        }
        if ($projs->count() > 0) {
            $avgPct = round($projs->avg('percent_complete') ?? 0);
            $insights->push(['icon' => 'tasks', 'color' => 'primary', 'text' => $projs->count() . ' active project(s), avg ' . $avgPct . '% complete']);
        }

        return [
            'partner' => $p,
            'composite' => round($composite),
            'health' => $health,
            'pipeline' => $pipeline,
            'oppCount' => $opps->count(),
            'resourceCount' => $res->count(),
            'certCount' => $certCount,
            'projectCount' => $projs->count(),
            'pipelineScore' => round($pipelineScore),
            'resourceScore' => round($resourceScore),
            'projectScore' => round($avgCompletion),
            'topRisk' => $risks->first(),
            'allRisks' => $risks,
            'insights' => $insights,
        ];
    })->sortBy('composite')->values(); // Worst health first

    // Portfolio-level counts
    $criticalCount = $partnerInsights->where('health', 'critical')->count();
    $highCount = $partnerInsights->where('health', 'high')->count();
    $mediumCount = $partnerInsights->where('health', 'medium')->count();
    $lowCount = $partnerInsights->where('health', 'low')->count();

    // Quick Wins (with expandable detail data)
    $quickWins = collect();

    $highProbOpps = $partners->flatMap(fn($p) => $p->opportunities
        ->filter(fn($o) => $o->probability >= 70 && $o->stage !== 'Closed Won' && $o->stage !== 'Closed Lost')
        ->map(fn($o) => ['opp' => $o, 'partner' => $p])
    )->sortByDesc(fn($item) => $item['opp']->amount)->values();
    if ($highProbOpps->count() > 0) {
        $quickWins->push([
            'text' => 'Follow up on ' . $highProbOpps->count() . ' high-probability opportunities (70%+ likely to close).',
            'icon' => 'bullseye-arrow',
            'items' => $highProbOpps,
            'type' => 'opportunities',
        ]);
    }

    $noContacts = $partners->filter(fn($p) => $p->contacts->count() === 0)->values();
    if ($noContacts->count() > 0) {
        $quickWins->push([
            'text' => 'Add key contacts for ' . $noContacts->count() . ' partner(s) with no contacts on file.',
            'icon' => 'address-book',
            'items' => $noContacts,
            'type' => 'partners_no_contacts',
        ]);
    }

    $resourcesNoOpps = $partners->filter(fn($p) => $p->resources->count() > 0 && $p->opportunities->count() === 0)->values();
    if ($resourcesNoOpps->count() > 0) {
        $quickWins->push([
            'text' => 'Activate ' . $resourcesNoOpps->count() . ' partner(s) that have resources but no pipeline.',
            'icon' => 'rocket',
            'items' => $resourcesNoOpps,
            'type' => 'partners_no_pipeline',
        ]);
    }

    $staleDeals = $partners->flatMap(fn($p) => $p->opportunities
        ->filter(fn($o) => $o->close_date && now()->gt($o->close_date) && $o->stage !== 'Closed Won' && $o->stage !== 'Closed Lost')
        ->map(fn($o) => ['opp' => $o, 'partner' => $p])
    )->sortBy(fn($item) => $item['opp']->close_date)->values();
    if ($staleDeals->count() > 0) {
        $quickWins->push([
            'text' => 'Update or qualify out ' . $staleDeals->count() . ' opportunity(ies) past their close date.',
            'icon' => 'clock',
            'items' => $staleDeals,
            'type' => 'stale_opportunities',
        ]);
    }

    $quickWins->push([
        'text' => 'Run a data import if your Salesforce data is more than 1 week old.',
        'icon' => 'sync',
        'items' => collect(),
        'type' => 'reminder',
    ]);
@endphp

<!-- Greeting Card -->
<div class="coach-greeting p-4 mb-g">
    <div class="d-flex align-items-center">
        <div class="me-3">
            <i class="fal fa-hand-sparkles fa-2x"></i>
        </div>
        <div>
            <h4 class="mb-1 text-white">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', $userName)[0] }}!</h4>
            <p class="mb-0 opacity-80">
                Week of {{ $weekOf }} &ndash; {{ $weekEnd }} &bull;
                You're managing <strong>{{ $partnerCount }}</strong> partner{{ $partnerCount !== 1 ? 's' : '' }} with
                <strong>${{ number_format($totalPipeline) }}</strong> in total pipeline.
            </p>
        </div>
    </div>
</div>

<!-- Portfolio Risk Summary -->
<div class="row mb-g">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-700 text-danger">{{ $criticalCount }}</div>
                <div class="fs-nano text-muted text-uppercase">Critical</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-700" style="color: #fd7e14;">{{ $highCount }}</div>
                <div class="fs-nano text-muted text-uppercase">High Risk</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-700 text-warning">{{ $mediumCount }}</div>
                <div class="fs-nano text-muted text-uppercase">Watch</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-700 text-success">{{ $lowCount }}</div>
                <div class="fs-nano text-muted text-uppercase">Healthy</div>
            </div>
        </div>
    </div>
</div>

<!-- Per-Partner Insights & Risks -->
<div class="panel panel-icon" id="panel-partner-insights">
    <div class="panel-hdr">
        <h2><i class="fal fa-user-tie me-2"></i> This Week's <span class="fw-light"><i>Partner-by-Partner Assessment</i></span></h2>
    </div>
    <div class="panel-container">
        <div class="panel-content">
            @if($partnerInsights->count() > 0)
                @foreach($partnerInsights as $pi)
                <div class="partner-card risk-{{ $pi['topRisk']['severity'] }} card mb-3 border">
                    <div class="card-body">
                        <div class="row">
                            {{-- Left: Partner name + top risk --}}
                            <div class="col-xl-7">
                                <div class="d-flex align-items-start">
                                    <div class="risk-icon {{ $pi['topRisk']['severity'] }} me-3">
                                        <i class="fal fa-{{ $pi['topRisk']['icon'] }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <a href="{{ route('partners.show', $pi['partner']->id) }}" class="h5 mb-0 text-primary fw-700 me-2">{{ $pi['partner']->name }}</a>
                                            @if($pi['health'] === 'critical')
                                                <span class="badge bg-danger">Critical</span>
                                            @elseif($pi['health'] === 'high')
                                                <span class="badge bg-warning text-dark">High Risk</span>
                                            @elseif($pi['health'] === 'medium')
                                                <span class="badge bg-warning text-dark">Watch</span>
                                            @else
                                                <span class="badge bg-success">Healthy</span>
                                            @endif
                                            <span class="ms-2 fs-nano text-muted">Score: {{ $pi['composite'] }}</span>
                                        </div>

                                        {{-- Top risk --}}
                                        <div class="mb-2">
                                            <span class="fw-600 text-{{ $pi['topRisk']['severity'] === 'critical' ? 'danger' : ($pi['topRisk']['severity'] === 'high' ? 'warning' : ($pi['topRisk']['severity'] === 'low' ? 'success' : 'dark')) }}">
                                                <i class="fal fa-flag me-1"></i>Biggest risk:
                                            </span>
                                            <span>{{ $pi['topRisk']['title'] }}</span>
                                        </div>

                                        {{-- Recommended action --}}
                                        <div class="alert alert-light border-0 py-2 px-3 mb-2 fs-sm">
                                            <i class="fal fa-lightbulb text-warning me-1"></i>
                                            <strong>Action:</strong> {{ $pi['topRisk']['action'] }}
                                        </div>

                                        {{-- Additional risks (collapsed) --}}
                                        @if($pi['allRisks']->count() > 1)
                                        <div class="collapse" id="more-risks-{{ $pi['partner']->id }}">
                                            @foreach($pi['allRisks']->skip(1) as $risk)
                                            <div class="d-flex align-items-start mb-1 fs-sm">
                                                <i class="fal fa-{{ $risk['icon'] }} me-2 mt-1 text-{{ $risk['severity'] === 'critical' ? 'danger' : ($risk['severity'] === 'high' ? 'warning' : ($risk['severity'] === 'low' ? 'success' : 'secondary')) }}"></i>
                                                <span>{{ $risk['title'] }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                        <a class="fs-nano text-primary" data-bs-toggle="collapse" href="#more-risks-{{ $pi['partner']->id }}" role="button">
                                            <i class="fal fa-plus-circle me-1"></i>{{ $pi['allRisks']->count() - 1 }} more risk(s)
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Mini metrics + positive insights --}}
                            <div class="col-xl-5">
                                <div class="row mb-2">
                                    <div class="col-3 metric-mini">
                                        <div class="value text-primary">${{ $pi['pipeline'] >= 1000000 ? number_format($pi['pipeline']/1000000, 1) . 'M' : number_format($pi['pipeline']/1000, 0) . 'K' }}</div>
                                        <div class="label">Pipeline</div>
                                    </div>
                                    <div class="col-3 metric-mini">
                                        <div class="value text-info">{{ $pi['oppCount'] }}</div>
                                        <div class="label">Opps</div>
                                    </div>
                                    <div class="col-3 metric-mini">
                                        <div class="value text-success">{{ $pi['certCount'] }}/{{ $pi['resourceCount'] }}</div>
                                        <div class="label">Resources</div>
                                    </div>
                                    <div class="col-3 metric-mini">
                                        <div class="value text-warning">{{ $pi['projectCount'] }}</div>
                                        <div class="label">Projects</div>
                                    </div>
                                </div>

                                {{-- Score bars --}}
                                <div class="d-flex align-items-center mb-1 fs-nano">
                                    <span class="text-muted me-2" style="width: 65px;">Pipeline</span>
                                    <div class="progress health-bar flex-grow-1 me-2">
                                        <div class="progress-bar bg-{{ $pi['pipelineScore'] >= 60 ? 'success' : ($pi['pipelineScore'] >= 30 ? 'warning' : 'danger') }}" style="width: {{ min(100, $pi['pipelineScore']) }}%"></div>
                                    </div>
                                    <span class="text-muted" style="width: 30px;">{{ $pi['pipelineScore'] }}%</span>
                                </div>
                                <div class="d-flex align-items-center mb-1 fs-nano">
                                    <span class="text-muted me-2" style="width: 65px;">Resources</span>
                                    <div class="progress health-bar flex-grow-1 me-2">
                                        <div class="progress-bar bg-{{ $pi['resourceScore'] >= 60 ? 'success' : ($pi['resourceScore'] >= 30 ? 'warning' : 'danger') }}" style="width: {{ min(100, $pi['resourceScore']) }}%"></div>
                                    </div>
                                    <span class="text-muted" style="width: 30px;">{{ $pi['resourceScore'] }}%</span>
                                </div>
                                <div class="d-flex align-items-center mb-2 fs-nano">
                                    <span class="text-muted me-2" style="width: 65px;">Projects</span>
                                    <div class="progress health-bar flex-grow-1 me-2">
                                        <div class="progress-bar bg-{{ $pi['projectScore'] >= 60 ? 'success' : ($pi['projectScore'] >= 30 ? 'warning' : 'danger') }}" style="width: {{ min(100, $pi['projectScore']) }}%"></div>
                                    </div>
                                    <span class="text-muted" style="width: 30px;">{{ $pi['projectScore'] }}%</span>
                                </div>

                                {{-- Positive insights --}}
                                @if($pi['insights']->count() > 0)
                                <ul class="insight-list">
                                    @foreach($pi['insights']->take(3) as $ins)
                                    <li><i class="fal fa-{{ $ins['icon'] }} text-{{ $ins['color'] }} me-1"></i> {{ $ins['text'] }}</li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
            <div class="text-center text-muted py-5">
                <i class="fal fa-inbox fa-3x mb-2 d-block"></i>
                No partners assigned. Contact your administrator.
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Performance Pulse -->
<div class="row">
    <div class="col-xl-4">
        <div class="panel panel-icon" id="panel-spark-pipeline">
            <div class="panel-hdr">
                <h2><i class="fal fa-dollar-sign me-2"></i> Pipeline <span class="fw-light"><i>Trajectory</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content" style="height: 180px;">
                    <div id="spark-pipeline" style="height: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="panel panel-icon" id="panel-spark-closerate">
            <div class="panel-hdr">
                <h2><i class="fal fa-trophy me-2"></i> Close Rate <span class="fw-light"><i>Trend</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content" style="height: 180px;">
                    <div id="spark-closerate" style="height: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="panel panel-icon" id="panel-spark-resources">
            <div class="panel-hdr">
                <h2><i class="fal fa-users me-2"></i> Resource <span class="fw-light"><i>Growth</i></span></h2>
            </div>
            <div class="panel-container">
                <div class="panel-content" style="height: 180px;">
                    <div id="spark-resources" style="height: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Wins -->
<div class="panel panel-icon" id="panel-quickwins">
    <div class="panel-hdr">
        <h2><i class="fal fa-bolt me-2"></i> Quick <span class="fw-light"><i>Wins</i></span></h2>
        <div class="panel-toolbar">
            <span class="fs-sm text-muted">Low effort, high impact actions &mdash; click to expand</span>
        </div>
    </div>
    <div class="panel-container">
        <div class="panel-content">
            @foreach($quickWins as $i => $win)
            <div class="{{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                <div class="d-flex align-items-center {{ $win['items']->count() > 0 ? 'cursor-pointer' : '' }}"
                     @if($win['items']->count() > 0) data-bs-toggle="collapse" data-bs-target="#qw-detail-{{ $i }}" role="button" aria-expanded="false" @endif>
                    <div class="me-3">
                        <span class="badge bg-primary rounded-pill quick-win-number">{{ $i + 1 }}</span>
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0"><i class="fal fa-{{ $win['icon'] }} me-1 text-muted"></i> {{ $win['text'] }}</p>
                    </div>
                    @if($win['items']->count() > 0)
                    <div class="ms-2">
                        <i class="fal fa-chevron-down text-muted qw-chevron fs-sm"></i>
                    </div>
                    @endif
                </div>

                @if($win['items']->count() > 0)
                <div class="collapse" id="qw-detail-{{ $i }}">
                    <div class="mt-2 ms-5">

                        @if($win['type'] === 'opportunities' || $win['type'] === 'stale_opportunities')
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Partner</th>
                                        <th>Opportunity</th>
                                        <th class="text-end">Amount</th>
                                        @if($win['type'] === 'opportunities')
                                        <th class="text-center">Prob.</th>
                                        @endif
                                        <th>Close Date</th>
                                        <th class="text-center">Stage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($win['items'] as $item)
                                    <tr>
                                        <td><a href="{{ route('partners.show', $item['partner']->id) }}">{{ $item['partner']->name }}</a></td>
                                        <td>{{ $item['opp']->opportunity_name }}</td>
                                        <td class="text-end">${{ number_format($item['opp']->amount) }}</td>
                                        @if($win['type'] === 'opportunities')
                                        <td class="text-center"><span class="badge bg-info-200 text-info-800">{{ $item['opp']->probability }}%</span></td>
                                        @endif
                                        <td>{{ $item['opp']->close_date ? \Carbon\Carbon::parse($item['opp']->close_date)->format('M j, Y') : '—' }}</td>
                                        <td class="text-center"><span class="badge bg-secondary-200 text-secondary-800">{{ $item['opp']->stage }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @elseif($win['type'] === 'partners_no_contacts')
                        <ul class="list-unstyled mb-0">
                            @foreach($win['items'] as $partner)
                            <li class="py-1 d-flex align-items-center">
                                <i class="fal fa-building me-2 text-muted"></i>
                                <a href="{{ route('partners.show', $partner->id) }}">{{ $partner->name }}</a>
                                <span class="ms-2 fs-nano text-muted">{{ $partner->geography ?? '' }}</span>
                            </li>
                            @endforeach
                        </ul>

                        @elseif($win['type'] === 'partners_no_pipeline')
                        <ul class="list-unstyled mb-0">
                            @foreach($win['items'] as $partner)
                            <li class="py-1 d-flex align-items-center">
                                <i class="fal fa-building me-2 text-muted"></i>
                                <a href="{{ route('partners.show', $partner->id) }}">{{ $partner->name }}</a>
                                <span class="ms-2 badge bg-success-200 text-success-800">{{ $partner->resources->count() }} resource(s)</span>
                                <span class="ms-1 fs-nano text-muted">— {{ $partner->resources->where('status', 'Completed')->count() }} certified</span>
                            </li>
                            @endforeach
                        </ul>

                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const oppHistory = @json($opportunityHistory);
    const resHistory = @json($resourceHistory);

    const sparkOpts = {
        chart: { sparkline: { enabled: true }, height: '100%' },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
        tooltip: { fixed: { enabled: false } },
        dataLabels: { enabled: false }
    };

    if (oppHistory.length > 0) {
        new ApexCharts(document.querySelector('#spark-pipeline'), Object.assign({}, sparkOpts, {
            chart: Object.assign({}, sparkOpts.chart, { type: 'area' }),
            series: [{ name: 'Pipeline', data: oppHistory.map(h => parseFloat(h.total_amount || 0)) }],
            colors: ['#667eea'],
            xaxis: { categories: oppHistory.map(h => h.snapshot_date) },
            tooltip: { y: { formatter: v => '$' + v.toLocaleString() } }
        })).render();
    } else {
        document.querySelector('#spark-pipeline').innerHTML = '<div class="text-center text-muted py-4"><i class="fal fa-chart-area fa-2x mb-1 d-block"></i><span class="fs-nano">No historical data</span></div>';
    }

    if (oppHistory.length > 0) {
        const rates = oppHistory.map(h => {
            const total = parseInt(h.opportunity_count || 0);
            const won = parseInt(h.closed_won_count || 0);
            return total > 0 ? Math.round((won / total) * 100) : 0;
        });
        new ApexCharts(document.querySelector('#spark-closerate'), Object.assign({}, sparkOpts, {
            chart: Object.assign({}, sparkOpts.chart, { type: 'area' }),
            series: [{ name: 'Close Rate', data: rates }],
            colors: ['#28a745'],
            xaxis: { categories: oppHistory.map(h => h.snapshot_date) },
            tooltip: { y: { formatter: v => v + '%' } }
        })).render();
    } else {
        document.querySelector('#spark-closerate').innerHTML = '<div class="text-center text-muted py-4"><i class="fal fa-trophy fa-2x mb-1 d-block"></i><span class="fs-nano">No historical data</span></div>';
    }

    if (resHistory.length > 0) {
        new ApexCharts(document.querySelector('#spark-resources'), Object.assign({}, sparkOpts, {
            chart: Object.assign({}, sparkOpts.chart, { type: 'area' }),
            series: [{ name: 'Certified', data: resHistory.map(h => parseInt(h.certified_resources || 0)) }],
            colors: ['#ffc107'],
            xaxis: { categories: resHistory.map(h => h.snapshot_date) },
            tooltip: { y: { formatter: v => v + ' certified' } }
        })).render();
    } else {
        document.querySelector('#spark-resources').innerHTML = '<div class="text-center text-muted py-4"><i class="fal fa-users fa-2x mb-1 d-block"></i><span class="fs-nano">No historical data</span></div>';
    }
});
</script>
@endsection
