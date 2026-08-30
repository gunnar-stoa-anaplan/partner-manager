@extends('layouts.vertical', ['pageTitle' => 'Surveys & Knowledge Check'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Surveys & Knowledge Check', 'pageSubTitle1' => $session->name, 'pageSubTitle1Url' => route('workshops.reports.index', [$workshop, $session])])
@endsection

@section('content')
<style>
    .stat-tile { border: 1px solid #e5e7eb; border-radius: 6px; padding: 0.9rem 1.1rem; }
    .stat-tile .stat-label { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: #6b7280; }
    .stat-tile .stat-value { font-size: 1.6rem; font-weight: 700; color: #1e293b; }
    .dist-bar-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.4rem; }
    .dist-bar-label { width: 220px; flex-shrink: 0; font-size: 0.85rem; text-transform: capitalize; }
    .dist-bar-track { flex-grow: 1; background: #eef2ff; border-radius: 3px; height: 0.9rem; overflow: hidden; }
    .dist-bar-fill { background: #2563eb; height: 100%; }
    .dist-bar-count { width: 6rem; text-align: right; font-size: 0.8rem; color: #6b7280; }
</style>
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Collection status</div></div>
            <div class="card-body">
                <p class="text-muted fs-sm">Responses are collected from the participant-facing survey form inside the workshop site. Manage question structure on the <a href="{{ route('workshops.surveys.index', $workshop) }}">workshop survey templates</a> page.</p>
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="stat-tile">
                            <div class="stat-label">Pre-Workshop Survey</div>
                            <div class="stat-value">{{ $templates['pre']['responses']->count() }} responses</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="stat-tile">
                            <div class="stat-label">Post-Workshop Survey</div>
                            <div class="stat-value">{{ $templates['post']['responses']->count() }} responses</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="stat-tile">
                            <div class="stat-label">Knowledge Check</div>
                            <div class="stat-value">{{ $templates['knowledge']['responses']->count() }} responses</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($prePostComparison))
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Pre → Post Likert comparison</div></div>
            <div class="card-body">
                <p class="text-muted fs-sm">Matched on identical question. 5 = Very comfortable, 1 = Very uncomfortable. Δ &gt; 0 = improvement.</p>
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <thead>
                            <tr class="text-muted fs-xs text-uppercase">
                                <th>Question</th>
                                <th class="text-end">Pre Avg</th>
                                <th class="text-end">Post Avg</th>
                                <th class="text-end">Δ</th>
                                <th class="text-end">N (Pre / Post)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prePostComparison as $row)
                                <tr class="border-top">
                                    <td>{{ $row['text'] }}</td>
                                    <td class="text-end">{{ $row['pre_avg'] ?? '—' }}</td>
                                    <td class="text-end">{{ $row['post_avg'] ?? '—' }}</td>
                                    <td class="text-end fw-medium {{ $row['delta'] !== null ? ($row['delta'] >= 0 ? 'text-success' : 'text-danger') : '' }}">
                                        {{ $row['delta'] !== null ? ($row['delta'] >= 0 ? '+'.$row['delta'] : $row['delta']) : '—' }}
                                    </td>
                                    <td class="text-end text-muted">{{ $row['n_pre'] }} / {{ $row['n_post'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @foreach(['pre' => 'Pre-workshop survey', 'post' => 'Post-workshop survey'] as $kind => $label)
            @if($templates[$kind]['template'] && $templates[$kind]['questionBreakdown']->isNotEmpty())
            <div class="card shadow-0 mb-g">
                <div class="card-header"><div class="card-title">{{ $label }} &middot; {{ $templates[$kind]['responses']->count() }} responses</div></div>
                <div class="card-body">
                    @foreach($templates[$kind]['questionBreakdown'] as $question)
                        <div class="mb-4 pb-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div><span class="fw-medium">{{ $question['question']['text'] }}</span> <span class="badge bg-light text-dark ms-1">likert</span></div>
                                <span class="text-muted fs-sm">n = {{ $question['n'] }}</span>
                            </div>
                            <p class="mb-2"><strong>Average: {{ $question['average'] ?? '—' }}</strong> / 5</p>
                            @foreach($question['distribution'] as $bucket)
                                <div class="dist-bar-row">
                                    <div class="dist-bar-label">{{ $bucket['label'] }}</div>
                                    <div class="dist-bar-track"><div class="dist-bar-fill" style="width: {{ $bucket['percent'] }}%"></div></div>
                                    <div class="dist-bar-count">{{ $bucket['count'] }} &middot; {{ $bucket['percent'] }}%</div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach

        @if($templates['knowledge']['template'] && $templates['knowledge']['responses']->isNotEmpty())
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Knowledge check &middot; {{ $templates['knowledge']['responses']->count() }} responses</div></div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="fw-medium">Result <span class="badge bg-light text-dark ms-1">pass / fail</span></span>
                </div>
                <div class="d-flex align-items-baseline gap-3">
                    <div class="fs-1 fw-bold text-success">{{ $knowledgePassRate }}%</div>
                    <div class="text-muted"><span class="text-success fw-medium">{{ $knowledgePassed }}</span> pass &middot; <span class="text-danger fw-medium">{{ $knowledgeFailed }}</span> fail</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
