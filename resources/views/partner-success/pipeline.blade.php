@extends('layouts.vertical', [
    'pageTitle' => 'Pipeline',
    'pageSubTitle1' => 'Insights',
    'pageSubTitle2' => 'PSM Engagement',
    'pageSubTitle2Url' => url('partner-success'),
    'pageSubText' => 'Pipeline distribution and the account × product matrix. Scoped to your partners and the filters above.',
])

@php
    $m = fn ($v) => '$'.number_format(($v ?? 0) / 1_000_000, 1).'M';
    $total = array_sum(array_column($pipe['byStage'], 'v'));
    $won = collect($pipe['byStage'])->firstWhere('k', 'like', '%Closed Won%')['v'] ?? 0;
    if (! $won) {
        foreach ($pipe['byStage'] as $s) { if (str_contains(strtolower($s['k'] ?? ''), 'won')) { $won = $s['v']; break; } }
    }
    $open = max(0, $total - $won);
    $lobColor = [
        'Platform' => '#1b3a5b', 'SCM' => '#0f4c54', 'FIN' => '#9b2c2c',
        'RPM' => '#8a6d1f', 'WFP' => '#4c3d8f', 'AI' => '#1f6f54', '—' => '#334155',
    ];
@endphp

@section('content')
<div class="container-fluid py-3">

    @include('partner-success.partials.scope-filter')
    <x-insight.drill-panel />
    <style>
        .pipe-drill { cursor: pointer; }
        .pipe-drill:hover .card { outline: 2px solid var(--bs-primary); }
        .pipe-drill-link { cursor: pointer; text-decoration: underline dotted; text-underline-offset: 2px; }
        #ch-stage, #ch-lob, #ch-region, #ch-app, #ch-ai, #ch-est { cursor: pointer; }
    </style>

    {{-- Pipeline totals --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3 pipe-drill" role="button" data-dim="all" data-title="Total $ACV"><x-insight.stat-card title="Total $ACV" :value="$m($total)" sub="all stages in scope" /></div>
        <div class="col-6 col-xl-3 pipe-drill" role="button" data-dim="open" data-title="Open $ACV"><x-insight.stat-card title="Open $ACV" :value="$m($open)" sub="not yet won" /></div>
        <div class="col-6 col-xl-3 pipe-drill" role="button" data-dim="won" data-title="Closed Won $ACV"><x-insight.stat-card title="Closed Won $ACV" :value="$m($won)" sub="stage 5" /></div>
        <div class="col-6 col-xl-3 pipe-drill" role="button" data-dim="hasproduct" data-title="Products in play"><x-insight.stat-card title="Products in play" :value="number_format(count($pipe['activeCaps']))" sub="distinct across these deals" /></div>
    </div>

    {{-- Distribution charts --}}
    <div class="row g-3">
        <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="fw-600 mb-2">By sales stage</div><div id="ch-stage" style="min-height:240px"></div></div></div></div>
        <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="fw-600 mb-2">By line of business</div><div id="ch-lob" style="min-height:240px"></div></div></div></div>
        <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="fw-600 mb-2">By region</div><div id="ch-region" style="min-height:240px"></div></div></div></div>
        <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="fw-600">Top products</div><div class="fs-xs text-muted mb-2">opportunities counted against every product tagged on them</div><div id="ch-app" style="min-height:260px"></div></div></div></div>
        <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="fw-600 mb-2">AI engagement</div><div id="ch-ai" style="min-height:240px"></div></div></div></div>
        <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="fw-600">% Apps vs Core</div><div class="fs-xs text-muted mb-2">each deal counted once — grey is Core with no app</div><div id="ch-est" style="min-height:240px"></div></div></div></div>
    </div>

    {{-- Pipeline by account by product --}}
    <div class="d-flex align-items-center justify-content-between mt-4 mb-2 flex-wrap gap-2">
        <h5 class="fw-600 mb-0">Pipeline by Account by Product</h5>
        <div class="d-flex align-items-center gap-2">
            <input id="mx-search" type="search" class="form-control form-control-sm" style="max-width:240px" placeholder="Find account or deal…">
            <button id="mx-hide-empty" type="button" class="btn btn-outline-secondary btn-sm">Hide empty columns</button>
        </div>
    </div>
    <style>
        .pipe-mx { width: max-content; min-width: 100%; }
        .pipe-mx th, .pipe-mx td { white-space: nowrap; }
        .pipe-mx thead tr:nth-child(2) th { min-width: 46px; text-align: center; }
        .pipe-mx td.prodc { text-align: center; }
        .pipe-mx td.prodc.on { background: rgba(255,97,0,.10); color: var(--bs-primary); font-weight: 700; }
        .pipe-mx thead th:first-child,
        .pipe-mx tbody td:first-child {
            position: sticky; left: 0; z-index: 3; min-width: 220px; max-width: 260px;
            white-space: normal; background: var(--bs-card-bg); box-shadow: 1px 0 0 var(--bs-border-color);
        }
        .pipe-mx .salesonly { font-size: .65rem; }
    </style>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle pipe-mx">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle">Account</th>
                        <th rowspan="2" class="align-middle">Opportunity</th>
                        <th rowspan="2" class="align-middle">Partner</th>
                        <th rowspan="2" class="align-middle">Stage</th>
                        <th rowspan="2" class="align-middle text-end">$ACV</th>
                        @foreach($pipe['matrix']['groups'] as $g)
                            <th colspan="{{ count($g['products']) }}" class="text-center text-white" data-lob="{{ $g['lob'] }}"
                                style="background: {{ $lobColor[$g['lob']] ?? '#334155' }}">{{ $g['lob'] }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($pipe['matrix']['groups'] as $g)
                            @foreach($g['products'] as $p)
                                <th class="fs-xs" data-prod="{{ $p['cap'] }}" title="{{ $p['cap'] }}">{{ $p['abbr'] }}</th>
                            @endforeach
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($pipe['matrix']['rows'] as $r)
                        @php
                            $salesOnly = empty($r['implementer']) && ! empty($r['partner']);
                            $hay = strtolower(($r['account'] ?? '').' '.($r['opportunity'] ?? '').' '.($r['partner'] ?? '').' '.($r['implementer'] ?? ''));
                        @endphp
                        <tr data-hay="{{ $hay }}">
                            <td>
                                @if($r['account'])
                                    <span class="fw-500 pipe-drill-link" role="button" data-dim="account" data-value="{{ $r['account'] }}" data-title="{{ $r['account'] }}">{{ $r['account'] }}</span>
                                @else <span class="fw-500">—</span> @endif
                                @if($salesOnly)<span class="badge bg-dark text-white salesonly ms-1">SALES ONLY</span>@endif
                            </td>
                            <td class="text-truncate" style="max-width:260px" title="{{ $r['opportunity'] }}">{{ $r['opportunity'] ?: '—' }}</td>
                            <td>
                                {{ $r['partner'] ?: '—' }}
                                @if(! empty($r['implementer']) && $r['implementer'] !== $r['partner'])
                                    <span class="text-muted">▸ {{ $r['implementer'] }}</span>
                                @endif
                            </td>
                            <td class="fs-sm">{{ $r['stage'] ?: '—' }}</td>
                            <td class="text-end">{{ $m($r['acv']) }}</td>
                            @foreach($pipe['matrix']['groups'] as $g)
                                @foreach($g['products'] as $p)
                                    @php $on = isset($r['caps'][$p['cap']]); @endphp
                                    <td class="prodc {{ $on ? 'on' : '' }}" data-prod="{{ $p['cap'] }}">{{ $on ? '✓' : '' }}</td>
                                @endforeach
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted text-center py-3">No opportunities in the current scope.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="fs-xs text-muted mt-1">Top {{ count($pipe['matrix']['rows']) }} deals by $ACV in scope. A ✓ marks each product tagged on the deal; “SALES ONLY” means no implementation partner is named yet.</div>
</div>

{{-- Embedded screen agent (renders only when config('agent.enabled')). --}}
<x-agent.panel screen="pipeline" />
@endsection

@push('scripts')
<script>
(function initPipeline(tries) {
    if (!window.ApexCharts || document.readyState === 'loading') {
        if ((tries || 0) < 100) return void setTimeout(() => initPipeline((tries || 0) + 1), 50);
        return;
    }
    const PIPE = @json($pipe);
    const LOBCOL = @json($lobColor);
    const GREY = '#C4C2BC', ORANGE = '#FF6100', INK = '#1b3a5b';
    const PAL = ['#1b3a5b', '#FF6100', '#4c3d8f', '#9b2c2c', '#0f4c54', '#8a6d1f', '#1f6f54', '#7a8ca3'];
    const money = v => { v = +v || 0; if (Math.abs(v) >= 1e6) return '$' + (v/1e6).toFixed(1) + 'M'; if (Math.abs(v) >= 1e3) return '$' + Math.round(v/1e3) + 'K'; return '$' + v; };
    const stageShort = s => (s || '').replace(/^\s*\d+\.?\s*/, '').replace('Qualification and Discovery','Qual & Disc').replace('Solution Development','Sol. Dev').replace('Solution Validation','Sol. Valid').replace('Negotiate and Close','Neg & Close').replace('Closed Won','Closed Won');

    // ── Click-to-drill audit drawer (reuses the Insight Kit drill-panel markup) ──
    const DRILL_BASE = @js(url('partner-success/api/pipeline-drill'));
    async function openDrill(dim, value, value2, title) {
        const panelEl = document.getElementById('insight-drill');
        if (!panelEl) return;
        if (window.bootstrap) window.bootstrap.Offcanvas.getOrCreateInstance(panelEl).show();
        const body = document.getElementById('insight-drill-body');
        body.replaceChildren();
        const loading = document.createElement('tr'); const lc = document.createElement('td');
        lc.className = 'text-muted'; lc.textContent = 'Loading…'; loading.appendChild(lc); body.appendChild(loading);
        const qs = new URLSearchParams({ value: value ?? '' });
        if (value2 != null) qs.set('value2', value2);
        let d = {};
        try {
            d = await (await fetch(DRILL_BASE + '/' + encodeURIComponent(dim) + '?' + qs.toString(), { headers: { Accept: 'application/json' } })).json();
        } catch (e) {
            body.replaceChildren(); const tr = document.createElement('tr'), td = document.createElement('td');
            td.className = 'text-danger'; td.textContent = 'Could not load source.'; tr.appendChild(td); body.appendChild(tr); return;
        }
        document.getElementById('insight-drill-title').textContent = (title || 'Source') + ' — ' + (d.row_count ?? (d.rows || []).length) + ' rows';
        document.getElementById('insight-drill-sql').textContent = d.sql || '';
        const prov = (d.provenance || []).map(p => (p.source_file || '') + ' @ ' + (p.snapshot_date || '')).join(' · ');
        document.getElementById('insight-drill-provenance').textContent = prov ? ('Provenance: ' + prov) : '';
        const cols = d.columns || [];
        const head = document.getElementById('insight-drill-head');
        head.replaceChildren(); body.replaceChildren();
        const htr = document.createElement('tr');
        cols.forEach(c => { const th = document.createElement('th'); th.textContent = c; htr.appendChild(th); });
        head.appendChild(htr);
        const rws = (d.rows || []).slice(0, 500);
        if (!rws.length) {
            const tr = document.createElement('tr'), td = document.createElement('td');
            td.className = 'text-muted'; td.colSpan = cols.length || 1; td.textContent = 'No rows.'; tr.appendChild(td); body.appendChild(tr); return;
        }
        rws.forEach(r => {
            const tr = document.createElement('tr');
            cols.forEach(c => { const td = document.createElement('td'); const v = r[c]; td.textContent = (v === null || v === undefined) ? '' : String(v); tr.appendChild(td); });
            body.appendChild(tr);
        });
    }

    const mk = (id, opt, onPoint) => {
        const el = document.getElementById(id); if (!el) return;
        if (onPoint) {
            opt.chart = opt.chart || {};
            opt.chart.events = Object.assign({}, opt.chart.events, {
                dataPointSelection: (e, ctx, cfg) => { const d = onPoint(cfg.dataPointIndex); if (d) openDrill(d.dim, d.value, d.value2, d.title); },
            });
        }
        new ApexCharts(el, opt).render();
    };
    const donut = (id, labels, series, colors, onPoint) => mk(id, {
        chart: { type: 'donut', height: 240 }, labels, series,
        colors: colors || labels.map((_, i) => PAL[i % PAL.length]),
        legend: { position: 'bottom', fontSize: '11px' }, dataLabels: { enabled: false },
        stroke: { width: 2 }, tooltip: { y: { formatter: money } },
        plotOptions: { pie: { donut: { size: '62%' } } },
    }, onPoint);

    // By sales stage (bar)
    mk('ch-stage', {
        chart: { type: 'bar', height: 240, toolbar: { show: false } },
        series: [{ name: '$ACV', data: PIPE.byStage.map(s => s.v) }],
        xaxis: { categories: PIPE.byStage.map(s => stageShort(s.k)), labels: { style: { fontSize: '10px' } } },
        yaxis: { labels: { formatter: money } },
        colors: [INK], plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
        dataLabels: { enabled: false }, tooltip: { y: { formatter: money } },
    }, i => ({ dim: 'stage', value: PIPE.byStage[i].k, title: 'Stage · ' + PIPE.byStage[i].k }));

    donut('ch-lob', PIPE.byLob.map(x => x.k), PIPE.byLob.map(x => x.v), null,
        i => ({ dim: 'lob', value: PIPE.byLob[i].k, title: 'LOB · ' + PIPE.byLob[i].k }));
    donut('ch-region', PIPE.byGeo.map(x => x.k), PIPE.byGeo.map(x => x.v), null,
        i => ({ dim: 'geo', value: PIPE.byGeo[i].k, title: 'Region · ' + PIPE.byGeo[i].k }));

    // Top products (horizontal bar), coloured by LOB; platform + untagged grey
    const platGrey = { 'Core Platform': 1, 'Polaris': 1, 'Anaplan Data Orchestrator': 1 };
    mk('ch-app', {
        chart: { type: 'bar', height: 260, toolbar: { show: false } },
        series: [{ name: 'Opportunities', data: PIPE.topProducts.map(p => p.v) }],
        xaxis: { categories: PIPE.topProducts.map(p => p.k) },
        colors: PIPE.topProducts.map(p => p.k === 'No product' ? '#e9ecef' : (platGrey[p.k] ? GREY : (LOBCOL[p.lob] || INK))),
        plotOptions: { bar: { horizontal: true, borderRadius: 3, distributed: true, barHeight: '70%' } },
        dataLabels: { enabled: false }, legend: { show: false },
        tooltip: { y: { formatter: v => Math.round(v) + ' opps' } },
    }, i => { const p = PIPE.topProducts[i]; return p.k === 'No product'
        ? { dim: 'noproduct', title: 'No product' }
        : { dim: 'product', value: p.k, title: 'Product · ' + p.k }; });

    donut('ch-ai', ['AI-engaged', 'No AI feature', 'No product assigned'],
        [PIPE.ai.engaged, PIPE.ai.no_ai, PIPE.ai.unassigned], ['#8a7ff0', GREY, '#e9ecef'],
        i => ({ dim: 'ai', value: ['engaged', 'no_ai', 'unassigned'][i], title: 'AI · ' + ['AI-engaged', 'No AI feature', 'No product assigned'][i] }));
    donut('ch-est', ['Apps', 'Core platform only', 'No product assigned'],
        [PIPE.appsCore.apps, PIPE.appsCore.core, PIPE.appsCore.unassigned], [ORANGE, GREY, '#e9ecef'],
        i => ({ dim: 'appscore', value: ['apps', 'core', 'unassigned'][i], title: ['Apps', 'Core platform only', 'No product assigned'][i] }));

    // Matrix: search filter
    const search = document.getElementById('mx-search');
    const rows = Array.from(document.querySelectorAll('.pipe-mx tbody tr'));
    if (search) search.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        rows.forEach(tr => { tr.hidden = q && !(tr.dataset.hay || '').includes(q); });
    });

    // Matrix: hide empty product columns (empty = not present anywhere in scope)
    const ACTIVE = new Set(PIPE.activeCaps);
    const btn = document.getElementById('mx-hide-empty');
    let hidden = false;
    if (btn) btn.addEventListener('click', () => {
        hidden = !hidden;
        btn.textContent = hidden ? 'Show all columns' : 'Hide empty columns';
        document.querySelectorAll('.pipe-mx [data-prod]').forEach(el => {
            if (!ACTIVE.has(el.dataset.prod)) el.hidden = hidden;
        });
        // recompute LOB group-header colspans to match visible product columns
        document.querySelectorAll('.pipe-mx thead th[data-lob]').forEach(h => {
            const lob = h.dataset.lob;
            const prods = PIPE.matrix.groups.find(g => g.lob === lob)?.products || [];
            const vis = prods.filter(p => !hidden || ACTIVE.has(p.cap)).length;
            h.colSpan = Math.max(1, vis); h.hidden = vis === 0;
        });
    });

    // KPI tiles + matrix account cells → open the audit drawer.
    document.addEventListener('click', (e) => {
        const t = e.target.closest('.pipe-drill, .pipe-drill-link');
        if (!t) return;
        openDrill(t.dataset.dim, t.dataset.value || '', t.dataset.value2 || null, t.dataset.title || '');
    });
})();
</script>
@endpush
