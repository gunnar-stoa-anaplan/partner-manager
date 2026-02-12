@extends('layouts.vertical', ['pageTitle' => 'Path Reviews'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Path Reviews', 'pageSubTitle1' => 'Act', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Path Reviews lists all active partner path enrollments so you can assess progression at a glance. See which partners are on track, which ones are stuck at a gate, and how long each has been in their current stage. Click into any enrollment to review gate requirement details and decide whether a partner is ready to advance.</p>

<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Active Path Enrollments</div></div>
    <div class="card-body p-0">
        <table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>Partner</th>
                    <th>Path</th>
                    <th>Current Stage</th>
                    <th>Days in Stage</th>
                    <th>Enrolled</th>
                    <th>Last Reviewed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enrollment)
                <tr>
                    <td>{{ $enrollment->partner->name ?? '—' }}</td>
                    <td>{{ $enrollment->path->name ?? '—' }}</td>
                    <td>
                        <span class="badge" style="background-color: {{ $enrollment->currentStage->color ?? '#6C757D' }}">
                            {{ $enrollment->currentStage->name ?? '—' }}
                        </span>
                    </td>
                    <td>
                        @php $days = $enrollment->stage_entered_at ? now()->diffInDays($enrollment->stage_entered_at) : 0; @endphp
                        <span class="{{ $days > 180 ? 'text-danger fw-500' : '' }}">{{ $days }} days</span>
                    </td>
                    <td>{{ $enrollment->enrolled_at?->format('M j, Y') ?? '—' }}</td>
                    <td>{{ $enrollment->last_reviewed_at?->format('M j, Y') ?? 'Never' }}</td>
                    <td>
                        <a href="{{ route('act.path-reviews.show', $enrollment) }}" class="btn btn-sm btn-outline-primary">
                            Review Gates
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        No active path enrollments. Enroll partners from the Partner Paths dashboard.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
