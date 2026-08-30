@extends('layouts.vertical', ['pageTitle' => $session->name.' - Leaderboard'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Leaderboard', 'pageSubTitle1' => $session->name, 'pageSubTitle1Url' => route('workshops.sessions.show', [$workshop, $session])])
@endsection

@section('content')
<style>
    .checkpoint-dot { display: inline-block; width: 0.8rem; height: 0.8rem; border-radius: 50%; }
    .checkpoint-dot.done { background: #15803d; }
    .checkpoint-dot.partial { background: #d97706; }
    .checkpoint-dot.todo { background: #cbd5e1; }
    .checkpoint-dot.failed { background: #dc2626; }
    .leaderboard-table th.checkpoint-col { writing-mode: vertical-rl; text-orientation: mixed; white-space: nowrap; font-size: 0.68rem; padding: 0.5rem 0.25rem; vertical-align: bottom; max-height: 140px; }
    .leaderboard-table td, .leaderboard-table th { white-space: nowrap; }
</style>
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="fs-sm text-muted">
                <span class="checkpoint-dot done me-1"></span> Done
                <span class="checkpoint-dot partial ms-3 me-1"></span> In progress
                <span class="checkpoint-dot todo ms-3 me-1"></span> Not started
                <span class="checkpoint-dot failed ms-3 me-1"></span> Failed
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted fs-sm" id="leaderboardRefreshNote">Auto-refreshes every 30 seconds.</span>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="location.reload()">↻ Refresh now</button>
            </div>
        </div>
        <div class="card shadow-0 mb-g">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 leaderboard-table">
                    <thead>
                        <tr>
                            <th>Participant</th>
                            <th>Partner</th>
                            @foreach($checkpoints as $checkpoint)
                                <th class="text-center checkpoint-col" title="{{ $checkpoint['label'] }}">{{ $checkpoint['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaderboard as $entry)
                            <tr>
                                <td>{{ $entry['contact']->full_name }}</td>
                                <td class="text-muted fs-sm">{{ $entry['contact']->partner?->name }}</td>
                                @foreach($checkpoints as $checkpoint)
                                    @php $status = $entry['statuses'][$checkpoint['key']] ?? 'todo'; @endphp
                                    <td class="text-center">
                                        <span class="checkpoint-dot {{ $status }}" title="{{ $checkpoint['label'] }}: {{ ['done' => 'Done', 'partial' => 'In progress', 'failed' => 'Failed', 'todo' => 'Not started'][$status] }}"></span>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ 2 + count($checkpoints) }}" class="text-center text-muted py-4">No participants yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
setTimeout(function () { location.reload(); }, 30000);
</script>
@endsection
