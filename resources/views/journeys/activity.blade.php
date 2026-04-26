@extends('layouts.vertical', ['pageTitle' => 'Activity Log — ' . $journey->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Activity Log',
        'pageSubTitle1' => 'Journeys',
        'pageSubTitle1Url' => route('journeys.index'),
        'pageSubTitle2' => $journey->name,
        'pageSubTitle2Url' => route('journeys.show', $journey),
        'pageSubText' => 'Agent activity log for ' . $journey->name,
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
            @if($paginator->isEmpty())
                <div class="text-center text-muted py-5">
                    <svg class="sa-icon sa-icon-2x mb-3"><use href="/icons/sprite.svg#activity"></use></svg>
                    <p>No activity recorded yet.</p>
                </div>
            @else
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Event Type</th>
                            <th>Participant</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginator as $entry)
                            <tr>
                                <td class="text-muted small text-nowrap">
                                    {{ isset($entry->created_at) ? \Carbon\Carbon::parse($entry->created_at)->format('M j, Y H:i') : '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $entry->event_type ?? '—' }}</span>
                                </td>
                                <td>{{ $entry->participant ?? '—' }}</td>
                                <td class="text-muted small">{{ $entry->details ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($paginator->hasPages())
                    <div class="d-flex justify-content-center py-3">
                        {{ $paginator->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

@endsection
