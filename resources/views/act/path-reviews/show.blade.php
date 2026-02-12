@extends('layouts.vertical', ['pageTitle' => 'Path Review: ' . $enrollment->partner->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Path Review', 'pageSubTitle1' => 'Act', 'pageSubTitle1Url' => route('act.path-reviews.index'), 'pageSubTitle2' => $enrollment->partner->name])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">This review page shows the full picture for a single partner's path enrollment — their current stage, how long they've been there, and exactly which gate requirements they've met or are still working toward. Use it to make informed decisions about stage advancement and to identify the specific areas where the partner needs support.</p>

{{-- Enrollment Summary --}}
<div class="card shadow-0 mb-g">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <small class="text-muted d-block">Partner</small>
                <strong>{{ $enrollment->partner->name }}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Path</small>
                <strong>{{ $enrollment->path->name }}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Current Stage</small>
                <span class="badge" style="background-color: {{ $enrollment->currentStage->color }}">{{ $enrollment->currentStage->name }}</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Next Stage</small>
                @if($nextStage)
                <span class="badge" style="background-color: {{ $nextStage->color }}">{{ $nextStage->name }}</span>
                @else
                <span class="text-muted">Final stage reached</span>
                @endif
            </div>
        </div>
    </div>
</div>

@if($nextStage)
{{-- Gate Progress Summary --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 {{ $metGates === $totalGates ? 'text-success' : 'text-warning' }}">{{ $metGates }} / {{ $totalGates }}</h2>
                <small class="text-muted">Gate Requirements Met</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0">{{ $totalGates > 0 ? round(($metGates / $totalGates) * 100) : 0 }}%</h2>
                <small class="text-muted">Gate Completion</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                @if($metGates === $totalGates)
                <span class="badge bg-success p-2 fs-sm">ELIGIBLE FOR ADVANCEMENT</span>
                @else
                <span class="badge bg-warning p-2 fs-sm">{{ $totalGates - $metGates }} requirement(s) remaining</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Gate Requirements Detail --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Gate Requirements: {{ $enrollment->currentStage->name }} &rarr; {{ $nextStage->name }}</div></div>
    <div class="card-body p-0">
        <table class="table m-0">
            <thead>
                <tr><th>Requirement</th><th class="text-center">Current</th><th class="text-center">Threshold</th><th class="text-center">Status</th></tr>
            </thead>
            <tbody>
                @foreach($gateResults as $gate)
                <tr>
                    <td>
                        <strong>{{ $gate['label'] }}</strong>
                        @if(!empty($gate['description']))
                        <small class="d-block text-muted">{{ $gate['description'] }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ is_numeric($gate['current_value']) ? number_format($gate['current_value'], $gate['unit'] === 'percentage' ? 1 : 0) : $gate['current_value'] }}</td>
                    <td class="text-center">{{ $gate['comparison'] }} {{ number_format($gate['threshold'], $gate['unit'] === 'percentage' ? 1 : 0) }}{{ $gate['unit'] === 'percentage' ? '%' : '' }}</td>
                    <td class="text-center">
                        @if($gate['met'])
                        <span class="badge bg-success">Met</span>
                        @else
                        <span class="badge bg-danger">Not Met</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Actions --}}
@if($metGates === $totalGates)
<div class="card shadow-0 mb-g border-success">
    <div class="card-body text-center">
        <h5 class="text-success">All gate requirements met!</h5>
        <p class="text-muted">{{ $enrollment->partner->name }} is eligible for advancement to {{ $nextStage->name }}.</p>
        <form action="{{ route('act.path-reviews.advance', $enrollment) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success" onclick="return confirm('Advance {{ $enrollment->partner->name }} to {{ $nextStage->name }}?')">
                Advance to {{ $nextStage->name }}
            </button>
        </form>
    </div>
</div>
@else
<div class="card shadow-0 mb-g">
    <div class="card-body text-center">
        <p class="text-muted">Partner does not yet meet all gate requirements. Create an action plan to address gaps, or place a hold.</p>
        <form action="{{ route('act.path-reviews.hold', $enrollment) }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="notes" value="Placed on hold during path review.">
            <button type="submit" class="btn btn-outline-warning">Place Hold</button>
        </form>
    </div>
</div>
@endif

@else
<div class="alert alert-info">This partner has reached the final stage in their path. No further advancement is available.</div>
@endif

@endsection
