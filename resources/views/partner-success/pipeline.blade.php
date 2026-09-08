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

    {{-- Pipeline totals --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3"><x-insight.stat-card title="Total $ACV" :value="$m($total)" sub="all stages in scope" /></div>
        <div class="col-6 col-xl-3"><x-insight.stat-card title="Open $ACV" :value="$m($open)" sub="not yet won" /></div>
        <div class="col-6 col-xl-3"><x-insight.stat-card title="Closed Won $ACV" :value="$m($won)" sub="stage 5" /></div>
        <div class="col-6 col-xl-3"><x-insight.stat-card title="Products in play" :value="number_format(count($pipe['activeCaps']))" sub="distinct across these deals" /></div>
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
                                <span class="fw-500">{{ $r['account'] ?: '—' }}</span>
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

    const mk = (id, opt) => { const el = document.getElementById(id); if (el) new ApexCharts(el, opt).render(); };
    const donut = (id, labels, series, colors) => mk(id, {
        chart: { type: 'donut', height: 240 }, labels, series,
        colors: colors || labels.map((_, i) => PAL[i % PAL.length]),
        legend: { position: 'bottom', fontSize: '11px' }, dataLabels: { enabled: false },
        stroke: { width: 2 }, tooltip: { y: { formatter: money } },
        plotOptions: { pie: { donut: { size: '62%' } } },
    });

    // By sales stage (bar)
    mk('ch-stage', {
        chart: { type: 'bar', height: 240, toolbar: { show: false } },
        series: [{ name: '$ACV', data: PIPE.byStage.map(s => s.v) }],
        xaxis: { categories: PIPE.byStage.map(s => stageShort(s.k)), labels: { style: { fontSize: '10px' } } },
        yaxis: { labels: { formatter: money } },
        colors: [INK], plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
        dataLabels: { enabled: false }, tooltip: { y: { formatter: money } },
    });

    donut('ch-lob', PIPE.byLob.map(x => x.k), PIPE.byLob.map(x => x.v));
    donut('ch-region', PIPE.byGeo.map(x => x.k), PIPE.byGeo.map(x => x.v));

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
    });

    donut('ch-ai', ['AI-engaged', 'No AI feature', 'No product assigned'],
        [PIPE.ai.engaged, PIPE.ai.no_ai, PIPE.ai.unassigned], ['#8a7ff0', GREY, '#e9ecef']);
    donut('ch-est', ['Apps', 'Core platform only', 'No product assigned'],
        [PIPE.appsCore.apps, PIPE.appsCore.core, PIPE.appsCore.unassigned], [ORANGE, GREY, '#e9ecef']);

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
})();
</script>
@endpush
