@extends('layouts.vertical', ['pageTitle' => 'Goals & Targets'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Goals & Targets', 'pageSubTitle1' => 'Plan', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Goals & Targets helps you plan and track revenue outcomes for your partners. The revenue calculator uses your historical ASP and win rate to work backward from a target — showing how many deals, how much pipeline, and how many new opportunities per quarter are needed to hit the number. The goals table below tracks each partner's sourced and influenced NNACV targets against actuals so you can monitor attainment throughout the fiscal year.</p>

{{-- Revenue Planning Calculator --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Revenue Planning Calculator — {{ $fy }}</div></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label fw-500">Historical ASP</label>
                <input type="text" class="form-control" id="calc-asp" value="{{ number_format($historicalAsp, 0) }}" readonly>
                <small class="text-muted">Avg license sales price (trailing 24m)</small>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-500">Win Rate</label>
                <input type="text" class="form-control" id="calc-winrate" value="{{ number_format($historicalWinRate * 100, 1) }}%" readonly>
                <small class="text-muted">Won / (Won + Lost) trailing 12m</small>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-500">Pipeline Multiple</label>
                <input type="text" class="form-control" id="calc-multiple" value="{{ $historicalWinRate > 0 ? number_format(1/$historicalWinRate, 1) : '—' }}x" readonly>
                <small class="text-muted">1 / Win Rate</small>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-500">Target NNACV</label>
                <input type="number" class="form-control" id="calc-target" placeholder="e.g. 2000000">
                <small class="text-muted">Enter target to calculate</small>
            </div>
        </div>
        <div class="row mt-2" id="calc-results" style="display:none;">
            <div class="col-md-3"><div class="p-3 bg-light rounded text-center"><h4 class="m-0" id="res-deals">—</h4><small>Required Closed Deals</small></div></div>
            <div class="col-md-3"><div class="p-3 bg-light rounded text-center"><h4 class="m-0" id="res-pipeline">—</h4><small>Required Pipeline</small></div></div>
            <div class="col-md-3"><div class="p-3 bg-light rounded text-center"><h4 class="m-0" id="res-opps">—</h4><small>New Opps / Quarter</small></div></div>
            <div class="col-md-3"><div class="p-3 bg-light rounded text-center"><h4 class="m-0" id="res-gap">—</h4><small>Pipeline Gap</small></div></div>
        </div>
    </div>
</div>

{{-- Partner Goals Table --}}
<div class="card shadow-0 mb-g">
    <div class="card-header"><div class="card-title">Partner Goals — {{ $fy }}</div></div>
    <div class="card-body">
        <table class="table table-hover m-0" id="goals-table">
            <thead><tr><th>Partner</th><th>Sourced Target</th><th>Sourced Actual</th><th>Influenced Target</th><th>Influenced Actual</th><th>Pipeline</th><th>Attainment</th></tr></thead>
            <tbody>
            @foreach($partners as $partner)
                @php
                    $goal = $goals->get($partner->id);
                    $won = $wonData->get($partner->id);
                    $pipeline = $pipelineData->get($partner->id, 0);
                    $sTarget = $goal ? $goal->sourced_nnacv_target : 0;
                    $iTarget = $goal ? $goal->influenced_nnacv_target : 0;
                    $sActual = $won ? $won->won : 0;
                    $totalTarget = $sTarget + $iTarget;
                    $attainment = $totalTarget > 0 ? round(($sActual / $totalTarget) * 100) : 0;
                @endphp
                <tr>
                    <td>{{ $partner->name }}</td>
                    <td>${{ number_format($sTarget) }}</td>
                    <td>${{ number_format($sActual) }}</td>
                    <td>${{ number_format($iTarget) }}</td>
                    <td>—</td>
                    <td>${{ number_format($pipeline) }}</td>
                    <td><span class="badge bg-{{ $attainment >= 80 ? 'success' : ($attainment >= 50 ? 'warning' : 'danger') }}">{{ $attainment }}%</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    new SmartTable('#goals-table', { sortable: true, perPage: 15 });
    var asp = {{ $historicalAsp ?: 200000 }};
    var winRate = {{ $historicalWinRate ?: 0.25 }};
    $("#calc-target").on("input", function() {
        var multiple = winRate > 0 ? 1/winRate : 4;
        var deals = Math.ceil(target / asp);
        var pipeline = target * multiple;
        var oppsPerQ = Math.ceil((deals / winRate) / 4);
        var gap = pipeline - {{ collect($pipelineData)->sum() }};
        $("#res-deals").text(deals);
        $("#res-pipeline").text(String.fromCharCode(36) + Math.round(pipeline).toLocaleString());
        $("#res-opps").text(oppsPerQ);
        $("#res-gap").text(String.fromCharCode(36) + Math.round(gap).toLocaleString()).toggleClass("text-danger", gap > 0);
        $("#calc-results").show();
    });
});
</script>
@endsection
