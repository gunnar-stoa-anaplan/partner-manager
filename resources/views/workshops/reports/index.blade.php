@extends('layouts.vertical', ['pageTitle' => 'Reports'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Reports', 'pageSubTitle1' => $session->name, 'pageSubTitle1Url' => route('workshops.sessions.show', [$workshop, $session])])
@endsection

@section('content')
<style>
    .stat-tile { border: 1px solid #e5e7eb; border-radius: 6px; padding: 0.9rem 1.1rem; }
    .stat-tile .stat-label { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: #6b7280; }
    .stat-tile .stat-value { font-size: 1.6rem; font-weight: 700; color: #1e293b; }
</style>
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Session snapshot</div></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="stat-tile">
                            <div class="stat-label">Counted</div>
                            <div class="stat-value">{{ $total }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-tile">
                            <div class="stat-label">Partners</div>
                            <div class="stat-value">{{ $partnersCount }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-tile">
                            <div class="stat-label">All Three Done</div>
                            <div class="stat-value text-success">{{ $allThreeDoneRate }}%</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-tile">
                            <div class="stat-label">Pre → Post Δ</div>
                            <div class="stat-value {{ $prePostDelta !== null && $prePostDelta < 0 ? 'text-danger' : 'text-success' }}">{{ $prePostDelta !== null ? ($prePostDelta >= 0 ? '+' : '').$prePostDelta : '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <h6 class="mb-2">Executive Summary</h6>
                        <p class="text-muted fs-sm">Polished HTML one-pager for internal partner alliances + product leadership.</p>
                        <a href="{{ route('workshops.reports.exec-summary', [$workshop, $session]) }}" class="btn btn-primary btn-sm">Open →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <h6 class="mb-2">Slack Celebration Post</h6>
                        <p class="text-muted fs-sm">Pre-filled Slack-ready post with this session's numbers.</p>
                        <a href="{{ route('workshops.reports.slack', [$workshop, $session]) }}" class="btn btn-primary btn-sm">Open →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <h6 class="mb-2">Partner Sponsor Reports</h6>
                        <p class="text-muted fs-sm">One report per partner firm with next-step recommendations. Copy + send to the sponsor.</p>
                        <a href="{{ route('workshops.reports.partners', [$workshop, $session]) }}" class="btn btn-primary btn-sm">Open →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <h6 class="mb-2">Export Session JSON</h6>
                        <p class="text-muted fs-sm">Full data dump for archival or follow-on analysis.</p>
                        <a href="{{ route('workshops.reports.export-json', [$workshop, $session]) }}" class="btn btn-primary btn-sm">Download ↓</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <h6 class="mb-2">Leaderboard</h6>
                        <p class="text-muted fs-sm">Per-participant progression across every lab check, survey, and knowledge check.</p>
                        <a href="{{ route('workshops.reports.leaderboard', [$workshop, $session]) }}" class="btn btn-primary btn-sm">Open →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
