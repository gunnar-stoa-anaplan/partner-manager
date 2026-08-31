@extends('portal.layout')

@section('title', 'Workshops - History')

@section('content')
<div class="container py-4">
    <h4 class="mb-1">Your Workshop History</h4>
    <p class="text-muted mb-4">Your organization's participation across every workshop, not just the current one.</p>

    <div class="card portal-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Workshop</th>
                        <th class="text-end">Sessions</th>
                        <th class="text-end">Participants</th>
                        <th class="text-end">Completed</th>
                        <th class="text-end">Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($byWorkshop as $row)
                        <tr>
                            <td class="fw-medium">{{ $row['workshop'] }}</td>
                            <td class="text-end">{{ $row['sessions'] }}</td>
                            <td class="text-end">{{ $row['participants'] }}</td>
                            <td class="text-end">{{ $row['completed'] }}</td>
                            <td class="text-end fw-medium {{ $row['completion_rate'] >= 75 ? 'text-success' : ($row['completion_rate'] > 0 ? 'text-warning' : 'text-danger') }}">{{ $row['completion_rate'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No workshop history yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('portal.workshops.index') }}" class="btn btn-outline-secondary btn-sm mt-3">&laquo; Back to Workshops</a>
</div>
@endsection
