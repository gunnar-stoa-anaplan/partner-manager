@extends('layouts.vertical', [
    'pageTitle' => 'Delivery',
    'pageSubTitle1' => 'Insights',
    'pageSubTitle2' => 'PSM Engagement',
    'pageSubTitle2Url' => url('partner-success'),
    'pageSubText' => 'Delivery across use cases, implementation health and support. Scoped to your partners and the filters above.',
])

@php
    $s = $data['stats'];
    $ragLabel = ['G' => 'Green', 'Y' => 'Yellow', 'R' => 'Red'];
    $ragBg = ['G' => 'bg-success', 'Y' => 'bg-warning', 'R' => 'bg-danger'];
@endphp

@section('content')
<div class="container-fluid py-3">

    @include('partner-success.partials.scope-filter')

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-usecases" type="button">Use cases</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-health" type="button">Implementation health</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-support" type="button">Support</button></li>
    </ul>

    <style>
        .dlv-mx { width: max-content; min-width: 100%; }
        .dlv-mx th, .dlv-mx td { white-space: nowrap; }
        .dlv-mx thead tr:nth-child(2) th { min-width: 46px; text-align: center; }
        .dlv-mx td.prodc { text-align: center; }
        .dlv-mx td.prodc.on { background: rgba(255,97,0,.10); color: var(--bs-primary); font-weight: 700; }
        .dlv-mx thead th:first-child, .dlv-mx tbody td:first-child {
            position: sticky; left: 0; z-index: 3; min-width: 210px; max-width: 250px;
            white-space: normal; background: var(--bs-card-bg); box-shadow: 1px 0 0 var(--bs-border-color);
        }
        .rag-sel.G { background: var(--bs-success-bg-subtle); }
        .rag-sel.Y { background: var(--bs-warning-bg-subtle); }
        .rag-sel.R { background: var(--bs-danger-bg-subtle); }
        .rag-dot { display:inline-block; width:.7rem; height:.7rem; border-radius:50%; }
    </style>

    <div class="tab-content">
        {{-- ============================== USE CASES ============================== --}}
        <div class="tab-pane fade show active" id="tab-usecases">
            <div class="card border-0 shadow-sm mb-3"><div class="card-body d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-600 text-uppercase fs-xs text-muted me-1">Delivery status</span>
                <span class="badge rounded-pill bg-dark">{{ number_format($s['live']) }} LIVE</span>
                <span class="badge rounded-pill bg-primary">{{ number_format($s['in_delivery']) }} IN DELIVERY</span>
                <span class="badge rounded-pill bg-body-secondary text-body border">{{ number_format($s['not_started']) }} NOT STARTED</span>
                <span class="text-muted fs-sm ms-1">{{ number_format($s['deals']) }} deals · live delivery comes from use cases (projects fall off IHS at go-live)</span>
            </div></div>

            <div class="fw-600 text-uppercase fs-xs text-muted mb-2">Delivery readiness</div>
            <div class="row g-3 mb-3">
                <div class="col-6 col-xl-3"><x-insight.stat-card title="Delivery Ready" :value="number_format($s['ready'])" sub="every product's role is certified" /></div>
                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 border-top border-danger border-3"><div class="card-body">
                        <div class="fs-xs text-uppercase text-muted">Not Ready</div>
                        <div class="fs-2 fw-700 text-danger">{{ number_format($s['not_ready']) }}</div>
                        <div class="fs-xs text-muted">a product's role has nobody certified</div>
                    </div></div>
                </div>
                <div class="col-6 col-xl-3"><x-insight.stat-card title="Led By Partner" :value="number_format($s['led_by_partner'])" sub="partner is the implementer" /></div>
                <div class="col-6 col-xl-3"><x-insight.stat-card title="Apps In Play" :value="number_format($s['apps_in_play'])" sub="distinct products across these deals" /></div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                <span class="fs-sm text-muted">Click a column to read it; use the controls to filter.</span>
                <div class="d-flex gap-2">
                    <input id="uc-search" type="search" class="form-control form-control-sm" style="max-width:240px" placeholder="Find account or deal…">
                    <button id="uc-hide-empty" type="button" class="btn btn-outline-secondary btn-sm">Hide empty columns</button>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle dlv-mx">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle">Account</th>
                                <th rowspan="2" class="align-middle">Opportunity</th>
                                <th rowspan="2" class="align-middle">Implementation partner</th>
                                <th rowspan="2" class="align-middle">Status</th>
                                <th rowspan="2" class="align-middle">Led by</th>
                                <th rowspan="2" class="align-middle">Go live</th>
                                <th rowspan="2" class="align-middle">Delivery readiness</th>
                                <th rowspan="2" class="align-middle">Support</th>
                                <th rowspan="2" class="align-middle">Polaris Customer</th>
                                <th rowspan="2" class="align-middle">IHS</th>
                                @foreach($data['matrix']['groups'] as $g)
                                    <th colspan="{{ count($g['products']) }}" class="text-center text-white" data-lob="{{ $g['lob'] }}"
                                        style="background: {{ ['Platform'=>'#1b3a5b','SCM'=>'#0f4c54','FIN'=>'#9b2c2c','RPM'=>'#8a6d1f','WFP'=>'#4c3d8f','AI'=>'#1f6f54'][$g['lob']] ?? '#334155' }}">{{ $g['lob'] }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($data['matrix']['groups'] as $g)
                                    @foreach($g['products'] as $p)
                                        <th class="fs-xs" data-prod="{{ $p['cap'] }}" title="{{ $p['cap'] }}">{{ $p['abbr'] }}</th>
                                    @endforeach
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['matrix']['rows'] as $r)
                                <tr data-hay="{{ strtolower(($r['account'] ?? '').' '.($r['opportunity'] ?? '').' '.($r['implementer'] ?? '')) }}">
                                    <td>
                                        <span class="fw-500">{{ $r['account'] ?: '—' }}</span>
                                        @if($r['sales_only'])<span class="badge bg-dark text-white ms-1" style="font-size:.65rem">SALES ONLY</span>@endif
                                    </td>
                                    <td class="text-truncate" style="max-width:240px" title="{{ $r['opportunity'] }}">{{ $r['opportunity'] ?: '—' }}</td>
                                    <td>{{ $r['implementer'] ?: '—' }}</td>
                                    <td><span class="badge {{ $r['status'] === 'In delivery' ? 'bg-primary' : 'bg-body-secondary text-body border' }}">{{ strtoupper($r['status']) }}</span></td>
                                    <td>@if($r['led_by'] !== '—')<span class="badge rounded-pill" style="background:#e7defb;color:#4c3d8f">PARTNER LED</span>@else <span class="text-muted">—</span>@endif</td>
                                    <td class="fs-sm">{{ $r['go_live'] ? \Illuminate\Support\Str::of($r['go_live'])->substr(0,10) : '—' }}</td>
                                    <td><span class="badge {{ $r['ready'] ? 'bg-dark' : 'bg-danger' }}">{{ $r['ready'] ? 'READY →' : 'NOT READY →' }}</span></td>
                                    <td class="text-muted">—</td>
                                    <td class="text-muted">—</td>
                                    <td class="text-muted">—</td>
                                    @foreach($data['matrix']['groups'] as $g)
                                        @foreach($g['products'] as $p)
                                            @php $on = isset($r['caps'][$p['cap']]); @endphp
                                            <td class="prodc {{ $on ? 'on' : '' }}" data-prod="{{ $p['cap'] }}">{{ $on ? '✓' : '' }}</td>
                                        @endforeach
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-muted text-center py-3">No deals in the current scope.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="fs-xs text-muted mt-1">Top {{ count($data['matrix']['rows']) }} deals by $ACV. Support, Polaris Customer and IHS stay blank until those extracts are linked in Sources.</div>
        </div>

        {{-- ========================= IMPLEMENTATION HEALTH ========================= --}}
        <div class="tab-pane fade" id="tab-health">
            <div class="fs-sm text-muted mb-2"><span class="text-success fw-600">Green</span> on track · <span class="text-warning fw-600">Yellow</span> one thing may be wrong · <span class="text-danger fw-600">Red</span> escalation likely</div>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Project</th><th>Project owner</th><th>Enablement</th><th>Use case</th>
                                <th>Support</th><th>PS / CS status</th><th>PS &amp; CS comments</th>
                                <th>Project phase</th><th>Scope</th><th>Timeline</th><th>Budget</th><th>Overall</th><th>Reason (yellow/red)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['projects'] as $p)
                                <tr>
                                    <td style="min-width:220px">
                                        <div class="fw-600">{{ $p['name'] ?: '—' }}</div>
                                        <div class="fs-xs text-muted">{{ $p['account'] }}@if($p['partner']) · {{ $p['partner'] }}@endif @if($p['go_live'])· Go-Live {{ \Illuminate\Support\Str::of($p['go_live'])->substr(0,10) }}@endif</div>
                                    </td>
                                    <td class="fs-sm">{{ $p['owner'] ?: '—' }}</td>
                                    <td class="text-muted">—</td>
                                    <td class="text-muted">—</td>
                                    <td class="text-muted">—</td>
                                    <td class="fs-sm">
                                        @if($p['health_ps'] || $p['health_cs'])
                                            {{ $p['health_ps'] ?: '—' }} / {{ $p['health_cs'] ?: '—' }}
                                        @else <span class="badge bg-body-secondary text-body border">Missing</span> @endif
                                    </td>
                                    <td class="text-muted">—</td>
                                    <td><input type="text" class="form-control form-control-sm" style="min-width:90px" placeholder="e.g. UAT"
                                               data-key="{{ $p['key'] }}" data-field="phase" value="{{ $p['phase'] }}"></td>
                                    @foreach(['scope','timeline','budget'] as $f)
                                        <td>
                                            <select class="form-select form-select-sm rag-sel {{ $p[$f] }}" style="min-width:76px" data-key="{{ $p['key'] }}" data-field="{{ $f }}">
                                                <option value="">—</option>
                                                <option value="G" @selected($p[$f]==='G')>Green</option>
                                                <option value="Y" @selected($p[$f]==='Y')>Yellow</option>
                                                <option value="R" @selected($p[$f]==='R')>Red</option>
                                            </select>
                                        </td>
                                    @endforeach
                                    <td class="overall">
                                        @if($p['overall'])<span class="rag-dot {{ $ragBg[$p['overall']] }}"></span> <span class="fs-sm">{{ $ragLabel[$p['overall']] }}</span>@else <span class="text-muted">—</span>@endif
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm" style="min-width:160px"
                                               data-key="{{ $p['key'] }}" data-field="reason" value="{{ $p['reason'] }}"></td>
                                </tr>
                            @empty
                                <tr><td colspan="13" class="text-muted text-center py-3">No delivery projects in the current scope.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="fs-xs text-muted mt-1">Scope / Timeline / Budget and the reason save as you edit. Overall is the worst of the three. Enablement, Use case, Support and PS &amp; CS comments populate once those extracts are linked.</div>
        </div>

        {{-- =============================== SUPPORT =============================== --}}
        <div class="tab-pane fade" id="tab-support">
            <h5 class="fw-600">Support <span class="fs-sm fw-normal text-muted">— partner tickets and tickets on the accounts they implement</span></h5>
            <div class="fs-sm text-muted mb-3">Cases matched to your partners will appear here. <strong>Opened dates and descriptions appear once the Support extract is linked in Sources.</strong></div>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead>
                            <tr><th>Case</th><th>Raised against</th><th>Account</th><th>Partner</th><th>Opened</th><th>Priority</th><th>State</th><th>Short description</th></tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="8" class="text-center text-muted py-4">No Support extract linked — connect a support feed in Sources to populate cases.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Use-cases: search + hide empty columns
    const ACTIVE = new Set(@json($data['activeCaps']));
    const search = document.getElementById('uc-search');
    const rows = Array.from(document.querySelectorAll('.dlv-mx tbody tr'));
    if (search) search.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        rows.forEach(tr => { tr.hidden = q && !(tr.dataset.hay || '').includes(q); });
    });
    const groups = @json($data['matrix']['groups']);
    const hideBtn = document.getElementById('uc-hide-empty');
    let hidden = false;
    if (hideBtn) hideBtn.addEventListener('click', () => {
        hidden = !hidden;
        hideBtn.textContent = hidden ? 'Show all columns' : 'Hide empty columns';
        document.querySelectorAll('.dlv-mx [data-prod]').forEach(el => { if (!ACTIVE.has(el.dataset.prod)) el.hidden = hidden; });
        document.querySelectorAll('.dlv-mx thead th[data-lob]').forEach(h => {
            const prods = groups.find(g => g.lob === h.dataset.lob)?.products || [];
            const vis = prods.filter(p => !hidden || ACTIVE.has(p.cap)).length;
            h.colSpan = Math.max(1, vis); h.hidden = vis === 0;
        });
    });

    // Implementation health: save edits, recolour selects, recompute Overall
    const CSRF = @json(csrf_token());
    const SAVE = @json(route('partner-success.project-health'));
    const RANK = { R: 3, Y: 2, G: 1 };
    const LBL = { G: 'Green', Y: 'Yellow', R: 'Red' };
    const DOT = { G: 'bg-success', Y: 'bg-warning', R: 'bg-danger' };
    function worst(vals) { let best = null, m = 0; vals.forEach(v => { if (v && RANK[v] > m) { m = RANK[v]; best = v; } }); return best; }
    document.querySelectorAll('#tab-health [data-field]').forEach(el => {
        el.addEventListener('change', () => {
            fetch(SAVE, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ project_key: el.dataset.key, field: el.dataset.field, value: el.value }) });
            if (el.classList.contains('rag-sel')) {
                el.classList.remove('G', 'Y', 'R'); if (el.value) el.classList.add(el.value);
                const tr = el.closest('tr');
                const vals = ['scope', 'timeline', 'budget'].map(f => tr.querySelector('[data-field="' + f + '"]').value);
                const o = worst(vals), cell = tr.querySelector('.overall');
                cell.innerHTML = o ? '<span class="rag-dot ' + DOT[o] + '"></span> <span class="fs-sm">' + LBL[o] + '</span>' : '<span class="text-muted">—</span>';
            }
        });
    });
})();
</script>
@endpush
