@extends('layouts.vertical', [
    'pageTitle' => $pageTitle ?? 'PSM Engagement',
    'pageSubTitle1' => 'Insights',
    'pageSubTitle2' => ($focus ?? null) ? 'PSM Engagement' : null,
    'pageSubTitle2Url' => ($focus ?? null) ? url('partner-success') : null,
    'pageSubText' => 'Audited, warehouse-backed engagement metrics — every number drills to its source; filters and scope apply across all PSM Engagement views.',
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

    {{-- Pinned, collapsible scope panel — toggle pills, applied instantly (no
         submit). Session-backed: one selection scopes every metric / chart /
         drill across all PSM Engagement tabs. --}}
    <style>
        .insight-pill { cursor: pointer; font-weight: 400; background: var(--bs-body-bg); color: var(--bs-body-color); border-color: var(--bs-border-color) !important; }
        .insight-pill:hover { border-color: var(--bs-primary) !important; }
        .insight-pill.is-active { background: var(--bs-primary); color: #fff; border-color: var(--bs-primary) !important; }
    </style>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header d-flex align-items-center justify-content-between" role="button"
             data-bs-toggle="collapse" data-bs-target="#ps-scope">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-600 me-1">
                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#filter"></use></svg>
                    Filters
                </span>
                <span id="ps-active-chips" class="d-flex align-items-center gap-1 flex-wrap"></span>
            </div>
            <svg class="sa-icon sa-icon-sm text-muted"><use href="/icons/sprite.svg#chevron-down"></use></svg>
        </div>

        <div id="ps-scope" class="collapse">
            <div class="card-body">
                @foreach($dimDefs as $key => $label)
                    @if(! empty($options[$key]))
                        <div class="mb-3">
                            <div class="fw-500 fs-sm text-muted mb-1">{{ $label }}</div>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($options[$key] as $opt)
                                    <button type="button"
                                            class="insight-pill badge rounded-pill border {{ in_array($opt, $active[$key] ?? []) ? 'is-active' : '' }}"
                                            data-dim="{{ $key }}" data-value="{{ $opt }}">{{ $opt }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
                <button type="button" id="ps-clear" class="btn btn-outline-secondary btn-sm mt-1">Clear all</button>
            </div>
        </div>
    </div>

    @unless($focus ?? false)
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
    @endunless

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
                            <x-insight.stat-card :title="$m['title']" :value="$primary" :sub="$sub" :metric="$m['name']" :unit="$m['unit'] ?? null" />
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
(function () {
    const CSRF = @js(csrf_token());
    const SET = @js(route('partner-success.filters.set'));
    const CLEAR = @js(route('partner-success.filters.clear'));
    const METRICS = @js(url('partner-success/api/metrics'));
    const DIM_LABELS = @js($dimDefs);

    function collect() {
        const dims = {};
        document.querySelectorAll('.insight-pill.is-active').forEach(p => {
            (dims[p.dataset.dim] = dims[p.dataset.dim] || []).push(p.dataset.value);
        });
        return dims;
    }

    function renderChips(dims) {
        const el = document.getElementById('ps-active-chips');
        if (!el) return;
        const parts = [];
        Object.keys(DIM_LABELS).forEach(k => (dims[k] || []).forEach(v => {
            const chip = document.createElement('span');
            chip.className = 'badge bg-body-secondary text-body border fw-normal';
            chip.textContent = DIM_LABELS[k] + ': ' + v;
            parts.push(chip);
        }));
        el.replaceChildren();
        if (parts.length) { parts.forEach(c => el.appendChild(c)); }
        else {
            const s = document.createElement('span');
            s.className = 'text-muted fs-sm fst-italic';
            s.textContent = 'Showing all your data — no filters applied';
            el.appendChild(s);
        }
    }

    async function refreshCards() {
        let metrics = [];
        try { metrics = await (await fetch(METRICS, { headers: { Accept: 'application/json' } })).json(); } catch (e) { return; }
        const by = {}; metrics.forEach(m => by[m.name] = m);
        document.querySelectorAll('.insight-stat-card[data-metric]').forEach(card => {
            const m = by[card.dataset.metric]; if (!m) return;
            const money = card.dataset.unit === 'currency';
            const val = money ? '$' + ((m.acv || 0) / 1e6).toFixed(1) + 'M' : Number(m.count).toLocaleString();
            const sub = money ? Number(m.count).toLocaleString() + ' opps' : (m.acv != null ? '$' + (m.acv / 1e6).toFixed(1) + 'M ACV' : '');
            const v = card.querySelector('.insight-stat-value'), s = card.querySelector('.insight-stat-sub');
            if (v) v.textContent = val; if (s) s.textContent = sub;
        });
    }

    async function persist(url, dims) {
        await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(dims),
        });
        window.dispatchEvent(new CustomEvent('insights:filters-changed')); // charts re-fetch
        refreshCards();
    }

    let timer;
    function schedule() {
        const dims = collect();
        renderChips(dims);
        clearTimeout(timer);
        timer = setTimeout(() => persist(SET, dims), 250);
    }

    document.querySelectorAll('.insight-pill').forEach(p =>
        p.addEventListener('click', () => { p.classList.toggle('is-active'); schedule(); }));

    const clearBtn = document.getElementById('ps-clear');
    if (clearBtn) clearBtn.addEventListener('click', () => {
        document.querySelectorAll('.insight-pill.is-active').forEach(p => p.classList.remove('is-active'));
        renderChips({});
        persist(CLEAR, {});
    });

    renderChips(collect());

    // ApexCharts sizes at 0 in hidden tab panes; redraw when a tab is shown.
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(btn =>
        btn.addEventListener('shown.bs.tab', () => window.dispatchEvent(new CustomEvent('insights:filters-changed'))));
})();
</script>
@endsection
