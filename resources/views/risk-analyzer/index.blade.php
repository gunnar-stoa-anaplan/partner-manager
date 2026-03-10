@extends('layouts.vertical', ['pageTitle' => 'Risk Analyzer'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Implementation Risk Analyzer', 'pageSubTitle1' => 'Measure', 'pageSubTitle1Url' => route('measure.index')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-3">Analyze delivery risk across your partner portfolio by correlating opportunity pipeline data with certified resource coverage. Identify staffing gaps, track resource movements, and surface actionable recommendations.</p>

{{-- Snapshot Selector --}}
@if($snapshotDates->isNotEmpty() && !in_array($tab, ['trends', 'heatmap', 'coverage']))
<div class="d-flex align-items-center mb-3">
    <label class="form-label mb-0 me-2 text-muted small">Snapshot:</label>
    <select class="form-select form-select-sm" style="width:auto" onchange="window.location.href='{{ route('measure.risk-analyzer.index') }}?tab={{ $tab }}&snapshot=' + this.value">
        @foreach($snapshotDates as $date)
        <option value="{{ $date }}" {{ $currentSnapshot === $date ? 'selected' : '' }}>{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</option>
        @endforeach
    </select>
</div>
@endif

{{-- Tab Navigation --}}
<ul class="nav nav-tabs mb-3" role="tablist">
    @foreach($tabs as $key => $label)
    <li class="nav-item">
        <a class="nav-link {{ $tab === $key ? 'active' : '' }}" href="{{ route('measure.risk-analyzer.index', ['tab' => $key, 'snapshot' => $currentSnapshot]) }}">
            {{ $label }}
        </a>
    </li>
    @endforeach
</ul>

{{-- Tab Content --}}
@include("risk-analyzer._$tab")

@endsection
