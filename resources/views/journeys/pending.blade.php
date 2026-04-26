@extends('layouts.vertical', ['pageTitle' => 'Pending Actions — ' . $journey->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Pending Actions',
        'pageSubTitle1' => 'Journeys',
        'pageSubTitle1Url' => route('journeys.index'),
        'pageSubTitle2' => $journey->name,
        'pageSubTitle2Url' => route('journeys.show', $journey),
        'pageSubText' => 'Agent approval queue for ' . $journey->name,
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
            @if($pendingActions->isEmpty())
                <div class="text-center text-muted py-5">
                    <svg class="sa-icon sa-icon-2x text-success mb-3"><use href="/icons/sprite.svg#check-circle"></use></svg>
                    <h5 class="text-success">All clear!</h5>
                    <p>No pending actions in the queue.</p>
                </div>
            @else
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Action Type</th>
                            <th>Participant</th>
                            <th class="text-center">Confidence</th>
                            <th>Reasoning</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingActions as $action)
                            <tr>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $action->action_type ?? '—' }}</span>
                                </td>
                                <td>{{ $action->participant ?? '—' }}</td>
                                <td class="text-center">
                                    @if(isset($action->confidence))
                                        @php $confidence = round((float) $action->confidence * 100); @endphp
                                        <span class="badge {{ $confidence >= 80 ? 'bg-success' : ($confidence >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ $confidence }}%
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $action->reasoning ?? '—' }}</td>
                                <td class="text-muted small text-nowrap">
                                    {{ isset($action->created_at) ? \Carbon\Carbon::parse($action->created_at)->format('M j, Y H:i') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

@endsection
