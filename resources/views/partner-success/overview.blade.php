@extends('layouts.vertical', [
    'pageTitle' => 'PSM Engagement',
    'pageSubTitle1' => 'Insights',
    'pageSubText' => 'Audited, warehouse-backed engagement metrics — every number drills to its source; GEO and partner filters re-run everything, scoped to what you can see.',
])

@php
    // Per-section breakdown charts (dimension → chart type + heading).
    $chartsBySection = [
        'Pipeline'   => [['lob', 'bar', 'Open pipeline ACV by line of business'], ['geo', 'donut', 'Open pipeline ACV by region']],
        'Delivery'   => [['dhealth', 'bar', 'Projects by health'], ['dgeo', 'donut', 'Projects by region']],
        'Capability' => [['capgrp', 'bar', 'ACV by capability group']],
        'Enablement' => [['role', 'bar', 'Partner people by role']],
        'Catalog'    => [['csource', 'bar', 'Courses by source']],
    ];
@endphp

@section('content')
<div class="container-fluid py-3">

    {{-- Filter bar (geo + partner), scoped to the partners the actor can see --}}
    <x-insight.filter-bar :partnersUrl="url('partner-success/api/partners')" />

    <ul class="nav nav-tabs" role="tablist">
        @foreach($sections as $section => $metrics)
            <li class="nav-item" role="presentation">
                <button class="nav-link @if($loop->first) active @endif" data-bs-toggle="tab"
                        data-bs-target="#sec-{{ \Illuminate\Support\Str::slug($section) }}" type="button" role="tab">
                    {{ $section }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content pt-3">
        @foreach($sections as $section => $metrics)
            <div class="tab-pane fade @if($loop->first) show active @endif"
                 id="sec-{{ \Illuminate\Support\Str::slug($section) }}" role="tabpanel">

                {{-- KPI cards for the section (drillable to audited source) --}}
                <div class="row g-3 mb-3">
                    @foreach($metrics as $m)
                        @php
                            $isMoney = ($m['unit'] ?? '') === 'currency';
                            $primary = $isMoney
                                ? '$'.number_format(($m['acv'] ?? 0) / 1_000_000, 1).'M'
                                : number_format($m['count']);
                            $sub = $isMoney
                                ? number_format($m['count']).' opps'
                                : (($m['acv'] ?? null) !== null ? '$'.number_format($m['acv'] / 1_000_000, 1).'M ACV' : null);
                        @endphp
                        <div class="col-6 col-md-4 col-xl-3">
                            <x-insight.stat-card :title="$m['title']" :value="$primary" :sub="$sub" :metric="$m['name']" />
                        </div>
                    @endforeach
                </div>

                {{-- Section breakdown charts --}}
                @if(!empty($chartsBySection[$section]) && count($metrics))
                    <div class="row g-3">
                        @foreach($chartsBySection[$section] as [$dim, $type, $heading])
                            <div class="col-lg-6">
                                <x-insight.chart :heading="$heading" :metric="$metrics[0]['name']" :dimension="$dim" :type="$type" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- Shared drill panel for the whole page --}}
<x-insight.drill-panel />

<script>
    // ApexCharts renders at 0 width inside hidden tab panes; when a tab is shown,
    // trigger the shared redraw so its charts size correctly.
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', () => window.dispatchEvent(new CustomEvent('insights:filters-changed')));
    });
</script>
@endsection
