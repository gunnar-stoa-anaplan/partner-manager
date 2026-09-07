@extends('layouts.vertical', ['pageTitle' => 'PSM Engagement (Insight Kit)'])

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex align-items-baseline justify-content-between mb-2">
        <h4 class="fw-700 mb-0">PSM Engagement</h4>
        <span class="fs-sm text-muted">Insight Kit reference — every number drills to its audited source; filters and scope re-run everything.</span>
    </div>

    {{-- Filter bar: publishes geo/partner to window.Insights, scoped to visible partners --}}
    <x-insight.filter-bar :partnersUrl="url('partner-success/api/partners')" />

    {{-- KPI cards (server-rendered from the visibility-scoped registry; drillable) --}}
    <div class="row g-3 mb-3">
        @foreach($pipeline as $m)
            @php
                $meta = $m['meta']; $v = $m['value'];
                $isMoney = $meta->unit === 'currency';
                $primary = $isMoney
                    ? '$'.number_format(($v->sum ?? 0) / 1_000_000, 1).'M'
                    : number_format($v->count);
                $sub = $isMoney
                    ? number_format($v->count).' opps'
                    : ($v->sum !== null ? '$'.number_format($v->sum / 1_000_000, 1).'M ACV' : null);
            @endphp
            <div class="col-6 col-md-4 col-xl-2">
                <x-insight.stat-card :title="$meta->title" :value="$primary" :sub="$sub" :metric="$meta->key" />
            </div>
        @endforeach
    </div>

    {{-- Charts (ApexCharts via the shared component; fetch visibility-scoped breakdowns) --}}
    <div class="row g-3">
        <div class="col-lg-6">
            <x-insight.chart heading="Open pipeline ACV by line of business" metric="open_pipe" dimension="lob" type="bar" />
        </div>
        <div class="col-lg-6">
            <x-insight.chart heading="Open pipeline ACV by region" metric="open_pipe" dimension="geo" type="donut" />
        </div>
    </div>
</div>

{{-- Single shared drill panel for the whole page --}}
<x-insight.drill-panel />
@endsection
