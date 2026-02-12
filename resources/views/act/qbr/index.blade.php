@extends('layouts.vertical', ['pageTitle' => 'QBR Workbench'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'QBR Workbench', 'pageSubTitle1' => 'Act', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The QBR Workbench helps you plan, run, and track Quarterly Business Reviews with your partners. Schedule new sessions, prepare talking points and agenda items, and record outcomes and commitments. Use the session history to maintain continuity across quarters and ensure every QBR drives measurable follow-through.</p>

{{-- Create New QBR --}}
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title">Schedule New QBR</div>
    </div>
    <div class="card-body">
        <form action="{{ route('act.qbr.store') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label fs-sm">Partner</label>
                <select name="partner_id" class="form-select form-select-sm" required>
                    <option value="">Select partner...</option>
                    @foreach($partners as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fs-sm">Fiscal Quarter</label>
                <input type="text" name="fiscal_quarter" class="form-control form-control-sm" placeholder="e.g., Q1 FY27" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fs-sm">Scheduled Date</label>
                <input type="date" name="scheduled_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Create QBR</button>
            </div>
        </form>
    </div>
</div>

{{-- QBR Sessions List --}}
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title">QBR Sessions</div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover m-0">
            <thead>
                <tr>
                    <th>Partner</th>
                    <th>Quarter</th>
                    <th>Status</th>
                    <th>Scheduled</th>
                    <th>Rating</th>
                    <th>Prepared By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td>{{ $session->partner->name ?? '—' }}</td>
                    <td>{{ $session->fiscal_quarter }}</td>
                    <td>
                        @php
                            $statusColors = ['planning' => 'secondary', 'scheduled' => 'info', 'in_progress' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$session->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</span>
                    </td>
                    <td>{{ $session->scheduled_date?->format('M j, Y') ?? '—' }}</td>
                    <td>
                        @if($session->overall_rating)
                        <span class="badge bg-{{ $session->overall_rating === 'strong' ? 'success' : ($session->overall_rating === 'at_risk' ? 'danger' : 'warning') }}">
                            {{ ucfirst(str_replace('_', ' ', $session->overall_rating)) }}
                        </span>
                        @else —
                        @endif
                    </td>
                    <td>{{ $session->preparedByUser->name ?? '—' }}</td>
                    <td><a href="{{ route('act.qbr.show', $session) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">No QBR sessions yet. Create one above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
