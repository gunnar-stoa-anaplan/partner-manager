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

    // Pinned filter panel dimensions (session key => label).
    $dimDefs = [
        'geos' => 'GEO',
        'partners' => 'Partners',
        'lobs' => 'Line of Business',
        'stages' => 'Sales Stage',
        'quarters' => 'Close Quarter',
        'capGroups' => 'Capability',
    ];
    $activeCount = collect($active)->flatten()->count();
@endphp

@section('content')
<div class="container-fluid py-3">

    {{-- Pinned, collapsible scope panel. Session-backed: one selection applies
         to every metric / chart / drill on all PSM Engagement tabs. --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header d-flex align-items-center justify-content-between" role="button"
             data-bs-toggle="collapse" data-bs-target="#ps-scope" aria-expanded="{{ $activeCount ? 'false' : 'true' }}">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-600 me-1">
                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#filter"></use></svg>
                    Filters
                </span>
                @if($activeCount)
                    @foreach($dimDefs as $key => $label)
                        @foreach($active[$key] ?? [] as $v)
                            <span class="badge bg-body-secondary text-body border fw-normal">{{ $label }}: {{ $v }}</span>
                        @endforeach
                    @endforeach
                @else
                    <span class="text-muted fs-sm fst-italic">Showing all your data — no filters applied</span>
                @endif
            </div>
            <svg class="sa-icon sa-icon-sm text-muted"><use href="/icons/sprite.svg#chevron-down"></use></svg>
        </div>

        <div id="ps-scope" class="collapse @if(! $activeCount) show @endif">
            <div class="card-body">
                <form method="POST" action="{{ route('partner-success.filters.set') }}">
                    @csrf
                    <div class="row g-3">
                        @foreach($dimDefs as $key => $label)
                            <div class="col-md-4">
                                <label class="form-label fw-500 fs-sm">{{ $label }}</label>
                                <select name="{{ $key }}[]" class="form-select form-select-sm" multiple size="5">
                                    @foreach($options[$key] ?? [] as $opt)
                                        <option value="{{ $opt }}" @selected(in_array($opt, $active[$key] ?? []))>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Apply filters</button>
                        <button type="submit" class="btn btn-outline-secondary btn-sm"
                                formaction="{{ route('partner-success.filters.clear') }}">Clear all</button>
                        <span class="text-muted fs-xs align-self-center ms-2">Hold Ctrl/Cmd to select more than one.</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
