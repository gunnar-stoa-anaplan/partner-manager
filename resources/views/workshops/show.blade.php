@extends('layouts.vertical', ['pageTitle' => $workshop->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $workshop->name, 'pageSubTitle1' => 'Workshops', 'pageSubTitle1Url' => route('workshops.index'), 'pageSubText' => $workshop->short_description])
@endsection

@section('content')
<style>
    .stat-tile { border: 1px solid #e5e7eb; border-radius: 6px; padding: 0.9rem 1.1rem; }
    .stat-tile .stat-label { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: #6b7280; }
    .stat-tile .stat-value { font-size: 1.6rem; font-weight: 700; color: #1e293b; }
    .partner-bar-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
    .partner-bar-label { width: 160px; flex-shrink: 0; font-size: 0.85rem; }
    .partner-bar-track { flex-grow: 1; background: #eef2ff; border-radius: 3px; height: 0.9rem; overflow: hidden; }
    .partner-bar-fill { background: #2563eb; height: 100%; }
    .partner-bar-count { width: 3rem; text-align: right; font-size: 0.85rem; }
</style>
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Workshop summary --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">Workshop summary</div>
                <span class="badge bg-secondary me-2">{{ \App\Models\Workshop::STATUSES[$workshop->status] ?? $workshop->status }}</span>
                <a href="{{ route('workshops.rollup', $workshop) }}" class="btn btn-outline-secondary btn-sm">All-sessions rollup report →</a>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    @if($summary['date_range'])
                        Rolled up across the {{ $summary['sessions_count'] }} non-archived session{{ $summary['sessions_count'] === 1 ? '' : 's' }} from {{ $summary['date_range']['start']->format('Y-m-d') }} to {{ $summary['date_range']['end']->format('Y-m-d') }}.
                    @else
                        No non-archived sessions to roll up yet.
                    @endif
                </p>

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="stat-tile">
                            <div class="stat-label">Sessions</div>
                            <div class="stat-value">{{ $summary['sessions_count'] }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-tile">
                            <div class="stat-label">Counted Participants</div>
                            <div class="stat-value">{{ $summary['participants_count'] }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-tile">
                            <div class="stat-label">Partner Firms</div>
                            <div class="stat-value">{{ $summary['partner_firms_count'] }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-tile">
                            <div class="stat-label">Pre / Post Avg</div>
                            @if($summary['pre_avg'] !== null || $summary['post_avg'] !== null)
                                <div class="stat-value fs-4">{{ $summary['pre_avg'] ?? '—' }} → {{ $summary['post_avg'] ?? '—' }}</div>
                                @if($summary['pre_avg'] !== null && $summary['post_avg'] !== null)
                                    @php $delta = round($summary['post_avg'] - $summary['pre_avg'], 2); @endphp
                                    <div class="fs-sm fw-medium {{ $delta >= 0 ? 'text-success' : 'text-danger' }}">{{ $delta >= 0 ? '+' : '' }}{{ $delta }} Δ</div>
                                @endif
                            @else
                                <div class="stat-value fs-4">—</div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($summary['per_session']->isNotEmpty())
                <h6 class="mb-2">Per-session progression</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-borderless mb-0">
                        <thead>
                            <tr class="text-muted fs-xs text-uppercase">
                                <th>Session</th>
                                <th>Dates</th>
                                <th class="text-end">Participants</th>
                                <th class="text-end">Pre Avg</th>
                                <th class="text-end">Post Avg</th>
                                <th class="text-end">Δ</th>
                                <th class="text-end">KC Pass</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary['per_session'] as $row)
                                <tr class="border-top">
                                    <td><a href="{{ route('workshops.sessions.show', [$workshop, $row['session']]) }}">{{ $row['session']->name }}</a></td>
                                    <td class="text-muted fs-sm">{{ $row['session']->start_date->format('Y-m-d') }} → {{ $row['session']->end_date->format('Y-m-d') }}</td>
                                    <td class="text-end">{{ $row['participants'] }}</td>
                                    <td class="text-end">{{ $row['pre_avg'] ?? '—' }}</td>
                                    <td class="text-end">{{ $row['post_avg'] ?? '—' }}</td>
                                    <td class="text-end {{ $row['delta'] !== null ? ($row['delta'] >= 0 ? 'text-success' : 'text-danger') : '' }} fw-medium">
                                        {{ $row['delta'] !== null ? ($row['delta'] >= 0 ? '+'.$row['delta'] : $row['delta']) : '—' }}
                                    </td>
                                    <td class="text-end">{{ $row['kc_pass_rate'] !== null ? $row['kc_pass_rate'].'%' : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if($summary['top_partners']->isNotEmpty())
                <h6 class="mb-2">Top partners by participant volume</h6>
                @php $maxCount = $summary['top_partners']->max('count'); @endphp
                @foreach($summary['top_partners']->take(10) as $partner)
                    <div class="partner-bar-row">
                        <div class="partner-bar-label">{{ $partner['name'] }}</div>
                        <div class="partner-bar-track"><div class="partner-bar-fill" style="width: {{ $maxCount > 0 ? round(($partner['count'] / $maxCount) * 100) : 0 }}%"></div></div>
                        <div class="partner-bar-count">{{ $partner['count'] }}</div>
                    </div>
                @endforeach
                @endif
            </div>
        </div>

        {{-- Sessions --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">Sessions</div>
                <a href="{{ route('workshops.edit', $workshop) }}" class="btn btn-outline-secondary btn-sm me-2">Edit Workshop</a>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#newSessionForm">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                    New session
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Each session is one running of <strong>{{ $workshop->name }}</strong> with its own dates, participants, and reports.</p>

                <div class="collapse mb-4" id="newSessionForm">
                    <form action="{{ route('workshops.sessions.store', $workshop) }}" method="POST" class="row g-2 align-items-end border rounded p-3 bg-light">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label fs-sm">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="name" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-sm">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="slug" placeholder="q3-2026-cohort" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-sm">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="start_date" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-sm">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="end_date" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-sm">Status <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="status" required>
                                @foreach(\App\Models\WorkshopSession::STATUSES as $value => $label)
                                    <option value="{{ $value }}" @selected($value === 'upcoming')>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm">Add</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100">
                        <thead>
                            <tr>
                                <th>Slug</th>
                                <th>Name</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($workshop->sessions as $session)
                                <tr>
                                    <td><code>{{ $session->slug }}</code></td>
                                    <td><a href="{{ route('workshops.sessions.show', [$workshop, $session]) }}">{{ $session->name }}</a></td>
                                    <td>{{ $session->start_date->format('Y-m-d') }}</td>
                                    <td>{{ $session->end_date->format('Y-m-d') }}</td>
                                    <td><span class="badge bg-secondary">{{ \App\Models\WorkshopSession::STATUSES[$session->status] ?? $session->status }}</span></td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('workshops.sessions.show', [$workshop, $session]) }}" class="btn btn-sm btn-outline-secondary">Open</a>
                                        @if($session->status !== 'archived')
                                            <form action="{{ route('workshops.sessions.archive', [$workshop, $session]) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this session? Discussion will become read-only.')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning">Archive</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No sessions scheduled yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Administer this workshop --}}
        <div class="row g-3 mb-g">
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h6 class="mb-0">Content</h6>
                            <a href="{{ route('workshops.pages.index', $workshop) }}" class="btn btn-outline-primary btn-sm">Manage →</a>
                        </div>
                        <p class="text-muted fs-sm mb-0">
                            {{ $workshop->participantPages->count() }} participant page{{ $workshop->participantPages->count() === 1 ? '' : 's' }}
                            @if($workshop->facilitatorPages->isNotEmpty())
                                &middot; {{ $workshop->facilitatorPages->count() }} facilitator-only
                            @endif
                            . View or edit the rendered content of every page in this workshop.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h6 class="mb-0">Survey templates</h6>
                            <a href="{{ route('workshops.surveys.index', $workshop) }}" class="btn btn-outline-primary btn-sm">Manage →</a>
                        </div>
                        <p class="text-muted fs-sm mb-0">Define the pre-workshop, post-workshop, and knowledge-check surveys participants take from inside the workshop site. Same template applies across every session.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h6 class="mb-0">Promotion toolkit</h6>
                            <a href="{{ route('workshops.toolkit.index', $workshop) }}" class="btn btn-outline-primary btn-sm">Open →</a>
                        </div>
                        <p class="text-muted fs-sm mb-0">{{ $workshop->toolkitAssets->count() }} copy-pasteable asset{{ $workshop->toolkitAssets->count() === 1 ? '' : 's' }} for promoting this workshop.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h6 class="mb-0">Tenant pool</h6>
                            <a href="{{ route('workshops.credentials.index', $workshop) }}" class="btn btn-outline-primary btn-sm">Manage →</a>
                        </div>
                        <p class="text-muted fs-sm mb-0">Pre-provisioned Anaplan credentials handed to participants for the week of their session. Import in bulk via CSV, or add one at a time.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h6 class="mb-0">Reports</h6>
                        </div>
                        <p class="text-muted fs-sm mb-0">Exec summary, partner sponsor reports, Slack celebration post, leaderboard. Run per-session from that session's page; the rollup report above covers every session together.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h6 class="mb-0">Review Queue @if($pendingReviewCount > 0)<span class="badge bg-warning text-dark ms-1">{{ $pendingReviewCount }}</span>@endif</h6>
                            <a href="{{ route('workshops.review-queue', $workshop) }}" class="btn btn-outline-primary btn-sm">Review →</a>
                        </div>
                        <p class="text-muted fs-sm mb-0">Draft page and survey revisions awaiting review before they go live - the PR queue for this workshop's content.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h6 class="mb-0">Content Flags @if($openContentFlagsCount > 0)<span class="badge bg-danger ms-1">{{ $openContentFlagsCount }}</span>@endif</h6>
                            <a href="{{ route('workshops.content-flags', $workshop) }}" class="btn btn-outline-primary btn-sm">View →</a>
                        </div>
                        <p class="text-muted fs-sm mb-0">Inaccuracies participants have flagged on this workshop's content, across every session.</p>
                    </div>
                </div>
            </div>
        </div>

        @if($workshop->facilitatorPages->isNotEmpty())
        <div class="card shadow-0 mb-g border-warning">
            <div class="card-header bg-warning bg-opacity-10">
                <div class="card-title"><i class="fal fa-clipboard-list me-2"></i> Facilitator Guide <span class="badge bg-warning text-dark ms-1">Internal Only</span></div>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($workshop->facilitatorPages as $page)
                    <li class="list-group-item">
                        <a href="{{ route('workshops.pages.show', [$workshop, $page]) }}">{{ $page->title }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
