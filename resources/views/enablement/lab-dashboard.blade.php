@extends('layouts.vertical', ['pageTitle' => 'Lab Facilitator Dashboard'])

@section('css')
<style>
    .checkpoint-cell {
        width: 90px;
        min-width: 90px;
        text-align: center;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .checkpoint-cell:hover { opacity: 0.85; }
    .checkpoint-cell.status-passed { background-color: rgba(40, 167, 69, 0.15); }
    .checkpoint-cell.status-failed { background-color: rgba(220, 53, 69, 0.15); }
    .checkpoint-cell.status-pending { background-color: rgba(134, 142, 150, 0.08); }
    .checkpoint-cell .badge { font-size: 0.7rem; }
    .checkpoint-header { writing-mode: vertical-lr; text-orientation: mixed; font-size: 0.75rem; max-height: 120px; overflow: hidden; }
</style>
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Lab Dashboard', 'pageSubTitle1' => 'Enablement', 'pageSubTitle1Url' => route('enablement.calendar'), 'pageSubText' => $session->display_title . ' — ' . $session->start_at->format('M j, Y')])
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($labs->isEmpty())
        <div class="text-center text-muted py-5">
            <p>No published labs for this session.</p>
            <a href="{{ route('labs.index') }}" class="btn btn-outline-primary">Manage Labs</a>
        </div>
    @else
        @foreach($labs as $lab)
            @php $grid = $progressGrid[$lab->id] ?? []; @endphp
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        <a href="{{ route('labs.show', $lab) }}" class="text-decoration-none">{{ $lab->name }}</a>
                        @php $stats = $lab->completionStats(); @endphp
                        <small class="text-muted ms-2">{{ $stats['completed'] }}/{{ $stats['total'] }} completed</small>
                    </div>
                    <form action="{{ route('sessions.validate-all', $session) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('This will validate all pending checkpoints for all participants via the Anaplan API. This may take a moment.')">
                            <i class="fal fa-check-double me-1"></i> Validate All
                        </button>
                    </form>
                </div>
                <div class="card-body p-0">
                    @if(empty($grid))
                        <div class="p-4 text-muted text-center">No participants enrolled.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th style="min-width: 200px;">Participant</th>
                                        <th style="width: 80px;">Partner</th>
                                        <th style="width: 80px;">Progress</th>
                                        @foreach($lab->checkpoints as $checkpoint)
                                            <th class="checkpoint-cell p-1">
                                                <div class="checkpoint-header" title="{{ $checkpoint->title }}">{{ Str::limit($checkpoint->title, 25) }}</div>
                                                <div>
                                                    <span class="badge" style="background-color: {{ \App\Models\LabCheckpoint::TYPE_COLORS[$checkpoint->checkpoint_type] ?? '#868e96' }}">
                                                        <i class="fal fa-{{ \App\Models\LabCheckpoint::TYPE_ICONS[$checkpoint->checkpoint_type] ?? 'check' }}"></i>
                                                    </span>
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($grid as $row)
                                        @php $participant = $row['participant']; @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $participant->contact->full_name }}</strong>
                                                <br><small class="text-muted">{{ $participant->contact->email }}</small>
                                            </td>
                                            <td><small>{{ $participant->partner->name ?? '—' }}</small></td>
                                            <td class="text-center">
                                                @php $pct = $participant->completionPercentage(); @endphp
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $pct }}%"></div>
                                                </div>
                                                <small>{{ $pct }}%</small>
                                            </td>
                                            @foreach($lab->checkpoints as $checkpoint)
                                                @php $cellData = $row['checkpoints'][$checkpoint->id] ?? ['status' => 'pending', 'attempts' => 0, 'error' => null]; @endphp
                                                <td class="checkpoint-cell status-{{ $cellData['status'] }}"
                                                    title="{{ $cellData['error'] ?? '' }}"
                                                    onclick="validateSingle({{ $participant->id }}, {{ $checkpoint->id }}, this)">
                                                    @if($cellData['status'] === 'passed')
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    @elseif($cellData['status'] === 'failed')
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                    @else
                                                        <i class="fal fa-circle text-muted"></i>
                                                    @endif
                                                    @if($cellData['attempts'] > 0)
                                                        <br><small class="text-muted">{{ $cellData['attempts'] }}x</small>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    {{-- Legend --}}
    <div class="card shadow-0 mb-g">
        <div class="card-body py-2">
            <small class="text-muted me-3">Legend:</small>
            <span class="me-3"><i class="fas fa-check-circle text-success me-1"></i> Passed</span>
            <span class="me-3"><i class="fas fa-times-circle text-danger me-1"></i> Failed</span>
            <span class="me-3"><i class="fal fa-circle text-muted me-1"></i> Not validated</span>
            <span class="text-muted ms-3">Click a cell to validate a single checkpoint.</span>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function validateSingle(participantId, checkpointId, cell) {
        cell.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';

        fetch(`/lab-participants/${participantId}/checkpoints/${checkpointId}/validate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            cell.className = 'checkpoint-cell status-' + (data.passed ? 'passed' : 'failed');
            if (data.passed) {
                cell.innerHTML = '<i class="fas fa-check-circle text-success"></i><br><small class="text-muted">' + data.attempt_number + 'x</small>';
            } else {
                cell.innerHTML = '<i class="fas fa-times-circle text-danger"></i><br><small class="text-muted">' + data.attempt_number + 'x</small>';
                cell.title = data.error_message || '';
            }
        })
        .catch(() => {
            cell.innerHTML = '<i class="fal fa-exclamation-triangle text-warning"></i>';
            cell.title = 'Validation request failed';
        });
    }

    // Auto-refresh every 30 seconds
    setInterval(() => { location.reload(); }, 30000);
</script>
@endsection
