@extends('layouts.vertical', ['pageTitle' => 'Participants — ' . $journey->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Participants',
        'pageSubTitle1' => 'Journeys',
        'pageSubTitle1Url' => route('journeys.index'),
        'pageSubTitle2' => $journey->name,
        'pageSubTitle2Url' => route('journeys.show', $journey),
        'pageSubText' => 'Full participant list for ' . $journey->name,
    ])
@endsection

@section('content')

    <div class="mb-3">
        <a href="{{ route('journeys.show', $journey) }}" class="btn btn-xs btn-outline-secondary">
            ← Back to Dashboard
        </a>
    </div>

    <div class="card shadow-0">
        <div class="card-body p-0">
            @if($participants->isEmpty())
                <div class="text-center text-muted py-5">
                    <svg class="sa-icon sa-icon-2x mb-3"><use href="/icons/sprite.svg#users"></use></svg>
                    <p>No participants found in the journey database.</p>
                </div>
            @else
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Organization</th>
                            <th>Stage</th>
                            <th>Tasks</th>
                            <th class="text-center">Days in Stage</th>
                            <th>Last Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participants as $participant)
                            @php
                                $stageColors = [1 => 'secondary', 2 => 'info', 3 => 'primary', 4 => 'warning', 5 => 'success'];
                                $stageNum = (int) ($participant->current_stage ?? 0);
                                $stageColor = $stageColors[$stageNum] ?? 'light';
                                $tasksDone = (int) ($participant->tasks_completed ?? 0);
                                $tasksTotal = $participant->stage_tasks ?: 1;
                                $taskPct = min(100, round(($tasksDone / $tasksTotal) * 100));
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $participant->name ?? '—' }}</strong>
                                    @if($participant->is_stalled)
                                        <span class="badge bg-danger ms-1" title="Stalled: no activity in over 14 days">Stalled</span>
                                    @endif
                                </td>
                                <td>{{ $participant->email ?? '—' }}</td>
                                <td>{{ $participant->organization ?? $participant->org ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $stageColor }}">{{ $participant->stage_name }}</span>
                                </td>
                                <td style="min-width: 120px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $taskPct }}%"></div>
                                        </div>
                                        <span class="small text-muted text-nowrap">{{ $tasksDone }}/{{ $tasksTotal }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($participant->days_in_stage !== null)
                                        <span class="{{ $participant->is_stalled ? 'text-danger fw-bold' : 'text-muted' }}">
                                            {{ $participant->days_in_stage }}d
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ isset($participant->last_active_at) ? \Carbon\Carbon::parse($participant->last_active_at)->format('M j, Y') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

@endsection
