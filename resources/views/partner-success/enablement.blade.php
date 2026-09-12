@extends('layouts.vertical', [
    'pageTitle' => 'Enablement',
    'pageSubTitle1' => 'Insights',
    'pageSubTitle2' => 'PSM Engagement',
    'pageSubTitle2Url' => url('partner-success'),
    'pageSubText' => 'Enablement across people and product coverage. Scoped to your partners and the filters above.',
])

@section('content')

{{-- Embedded screen agent (renders only when config('agent.enabled')). --}}
<x-agent.panel screen="enablement" />
<div class="container-fluid py-3">

    @include('partner-success.partials.scope-filter')

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-indiv" type="button">By individual</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-cov" type="button">By product &amp; role</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-board" type="button">By sales stage</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-opps" type="button">By opportunity</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-map" type="button">Product mapping</button></li>
    </ul>

    @php
        $bst = [
            'ok' => ['Ready', '#0A2F46'], 'part' => ['Assigned', '#C25000'],
            'gap' => ['Not ready', '#B33014'], 'untagged' => ['Product not tagged', '#8FA6B4'],
        ];
    @endphp

    <div class="tab-content">
        {{-- ========================== BY INDIVIDUAL ========================== --}}
        <div class="tab-pane fade show active" id="tab-indiv">
            <div class="d-flex justify-content-end mb-2">
                <input id="en-search" type="search" class="form-control form-control-sm" style="max-width:260px" placeholder="Find person, partner or cert…">
            </div>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead><tr><th>Person</th><th>Partner</th><th>Roles</th><th>Certified</th><th>In progress</th></tr></thead>
                        <tbody>
                            @forelse($en['people'] as $p)
                                <tr data-hay="{{ strtolower($p['name'].' '.$p['partner'].' '.implode(' ', $p['certified']).' '.implode(' ', $p['in_progress'])) }}">
                                    <td class="fw-500">{{ $p['name'] ?: '—' }}</td>
                                    <td class="fs-sm">{{ $p['partner'] ?: '—' }}</td>
                                    <td>@foreach($p['roles'] as $r)<span class="badge rounded-pill bg-body-secondary text-body border me-1">{{ $r }}</span>@endforeach</td>
                                    <td>@forelse($p['certified'] as $c)<span class="badge rounded-pill me-1" style="background:#e7f0e9;color:#1f6f54">{{ $c }}</span>@empty<span class="text-muted">—</span>@endforelse</td>
                                    <td>@forelse($p['in_progress'] as $c)<span class="badge rounded-pill me-1" style="background:#eef;color:#4c3d8f">{{ $c }}</span>@empty<span class="text-muted">—</span>@endforelse</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted text-center py-3">No people in the current scope.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="fs-xs text-muted mt-1">{{ count($en['people']) }} people in scope.</div>
        </div>

        {{-- ========================= BY PRODUCT & ROLE ========================= --}}
        <div class="tab-pane fade" id="tab-cov">
            <div class="d-flex align-items-center gap-2 mb-2">
                <label class="fs-sm text-muted mb-0">Role</label>
                <select id="en-role" class="form-select form-select-sm" style="width:auto">
                    <option value="2" selected>Delivery</option>
                    <option value="0">GTM</option>
                    <option value="1">PreSales</option>
                </select>
                <span class="fs-sm text-muted">Certified vs the plan's target ({{ $en['planNeed'][2] }} per delivery deal); Gap is what's still needed.</span>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead><tr><th>Path</th><th class="text-end">Certified</th><th class="text-end">In progress</th><th class="text-end">Target</th><th class="text-end">Gap</th><th style="width:30%">Coverage</th></tr></thead>
                        <tbody id="en-cov-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ========================== BY SALES STAGE ========================== --}}
        <div class="tab-pane fade" id="tab-board">
            <div class="row g-2 mb-3">
                @foreach($bst as $k => $b)
                    <div class="col-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100"><div class="card-body py-2">
                            <div class="fs-xs text-uppercase text-muted">{{ $b[0] }}</div>
                            <div class="fs-3 fw-700" style="color: {{ $b[1] }}">{{ number_format($en['counts'][$k]) }}</div>
                        </div></div>
                    </div>
                @endforeach
            </div>
            <div id="en-board" class="d-flex gap-3 overflow-auto pb-2"></div>
            <div class="fs-xs text-muted mt-1">Readiness is the delivering partner's Delivery bench for each product on the deal.</div>
        </div>

        {{-- ========================== BY OPPORTUNITY ========================== --}}
        <div class="tab-pane fade" id="tab-opps">
            <div class="row g-2 mb-3" id="en-opp-tiles">
                @foreach($bst as $k => $b)
                    <div class="col-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100 en-opp-tile" data-status="{{ $k }}" role="button" style="cursor:pointer">
                            <div class="card-body py-2">
                                <div class="fs-xs text-uppercase text-muted">{{ $b[0] }}</div>
                                <div class="fs-3 fw-700" style="color: {{ $b[1] }}">{{ number_format($en['counts'][$k]) }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-end mb-2"><input id="en-opp-search" type="search" class="form-control form-control-sm" style="max-width:260px" placeholder="Find account or deal…"></div>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead><tr><th>Account</th><th>Opportunity</th><th>Stage</th><th>Products</th><th>Status</th></tr></thead>
                        <tbody id="en-opp-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ========================== PRODUCT MAPPING ========================== --}}
        <div class="tab-pane fade" id="tab-map">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <h5 class="fw-600 mb-0">Academy <span class="fs-sm fw-normal text-muted">↔ Product mapping</span></h5>
                <select id="en-map-role" class="form-select form-select-sm ms-auto" style="width:auto">
                    <option value="">All roles</option>
                    <option value="Delivery">Delivery</option>
                    <option value="PreSales">PreSales</option>
                    <option value="GTM">GTM</option>
                    <option value="Blueprint">Blueprint</option>
                </select>
                <input id="en-map-search" type="search" class="form-control form-control-sm" style="max-width:220px" placeholder="Find a path…">
            </div>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead><tr><th>Learning path</th><th>Role</th><th class="text-end">Courses</th><th>Outcome</th></tr></thead>
                        <tbody id="en-map-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="fs-xs text-muted mt-1">{{ count($en['paths']) }} academy paths from the current warehouse extract.</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // By individual: search
    const search = document.getElementById('en-search');
    const rows = Array.from(document.querySelectorAll('#tab-indiv tbody tr'));
    if (search) search.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        rows.forEach(tr => { tr.hidden = q && !(tr.dataset.hay || '').includes(q); });
    });

    // By product & role
    const COV = @json($en['coverage']);
    const roleSel = document.getElementById('en-role');
    const body = document.getElementById('en-cov-body');
    function render() {
        const r = +roleSel.value;
        body.replaceChildren();
        COV.forEach(c => {
            const cert = c.certified[r], tgt = c.target[r], gap = c.gap[r];
            const pct = tgt > 0 ? Math.min(100, Math.round(cert / tgt * 100)) : (cert > 0 ? 100 : 0);
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td class="fw-500">' + c.abbr + ' <span class="text-muted fs-xs">' + c.label + '</span></td>'
                + '<td class="text-end">' + (cert || '—') + '</td>'
                + '<td class="text-end">' + (c.in_progress || '—') + '</td>'
                + '<td class="text-end">' + (tgt || '—') + '</td>'
                + '<td class="text-end ' + (gap > 0 ? 'text-danger fw-600' : '') + '">' + (tgt ? gap : '—') + '</td>'
                + '<td><div class="progress" style="height:8px"><div class="progress-bar ' + (gap > 0 ? 'bg-warning' : 'bg-success') + '" style="width:' + pct + '%"></div></div></td>';
            body.appendChild(tr);
        });
    }
    roleSel.addEventListener('change', render);
    render();

    // Shared deal data
    const DEALS = @json($en['deals']);
    const STAGES = @json($en['stageOrder']);
    const BST = @json($bst);
    const caps = arr => arr.length
        ? arr.slice(0, 6).map(c => '<span class="badge rounded-pill bg-body-secondary text-body border me-1">' + c + '</span>').join('') + (arr.length > 6 ? '<span class="text-muted fs-xs">+' + (arr.length - 6) + '</span>' : '')
        : '<span class="text-muted fs-xs">no product</span>';
    const pill = st => '<span class="badge rounded-pill" style="background:' + BST[st][1] + ';color:#fff">' + BST[st][0] + '</span>';

    // By sales stage — board
    const board = document.getElementById('en-board');
    if (board) {
        STAGES.forEach(stage => {
            const inStage = DEALS.filter(d => d.stage_label === stage);
            const col = document.createElement('div');
            col.style.minWidth = '260px'; col.style.flex = '0 0 260px';
            col.innerHTML = '<div class="fw-600 fs-sm mb-2">' + stage + ' <span class="text-muted">(' + inStage.length + ')</span></div>'
                + inStage.slice(0, 40).map(d =>
                    '<div class="card border-0 shadow-sm mb-2"><div class="card-body p-2">'
                    + '<div class="d-flex justify-content-between align-items-start gap-1"><span class="fw-500 fs-sm text-truncate" style="max-width:150px" title="' + (d.account || '') + '">' + (d.account || '—') + '</span>' + pill(d.status) + '</div>'
                    + '<div class="text-muted fs-xs text-truncate" title="' + (d.opportunity || '') + '">' + (d.opportunity || '') + '</div>'
                    + '<div class="mt-1">' + caps(d.caps) + '</div></div></div>').join('')
                + (inStage.length > 40 ? '<div class="text-muted fs-xs">+' + (inStage.length - 40) + ' more</div>' : '');
            board.appendChild(col);
        });
    }

    // By opportunity — table + status tiles + search
    const oppBody = document.getElementById('en-opp-body');
    let oppFilter = null, oppQuery = '';
    function renderOpps() {
        oppBody.replaceChildren();
        DEALS.filter(d => (!oppFilter || d.status === oppFilter)
                && (!oppQuery || ((d.account || '') + ' ' + (d.opportunity || '')).toLowerCase().includes(oppQuery)))
            .forEach(d => {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td class="fw-500">' + (d.account || '—') + '</td>'
                    + '<td class="fs-sm text-truncate" style="max-width:240px" title="' + (d.opportunity || '') + '">' + (d.opportunity || '—') + '</td>'
                    + '<td class="fs-sm">' + (d.stage_label || '—') + '</td>'
                    + '<td>' + caps(d.caps) + '</td><td>' + pill(d.status) + '</td>';
                oppBody.appendChild(tr);
            });
    }
    document.querySelectorAll('.en-opp-tile').forEach(t => t.addEventListener('click', () => {
        const s = t.dataset.status; oppFilter = oppFilter === s ? null : s;
        document.querySelectorAll('.en-opp-tile').forEach(x => x.classList.toggle('border', x === t && oppFilter));
        document.querySelectorAll('.en-opp-tile').forEach(x => x.style.outline = (x === t && oppFilter) ? '2px solid var(--bs-primary)' : '');
        renderOpps();
    }));
    const oppSearch = document.getElementById('en-opp-search');
    if (oppSearch) oppSearch.addEventListener('input', () => { oppQuery = oppSearch.value.trim().toLowerCase(); renderOpps(); });
    renderOpps();

    // Product mapping
    const PATHS = @json($en['paths']);
    const mapBody = document.getElementById('en-map-body');
    const mapRole = document.getElementById('en-map-role');
    const mapSearch = document.getElementById('en-map-search');
    function renderMap() {
        const r = mapRole.value, q = (mapSearch.value || '').trim().toLowerCase();
        mapBody.replaceChildren();
        PATHS.filter(p => (!r || p.role === r) && (!q || (p.id + ' ' + (p.outcome || '')).toLowerCase().includes(q)))
            .forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td class="fs-sm" style="font-family:monospace">' + p.id + '</td>'
                    + '<td><span class="badge rounded-pill bg-body-secondary text-body border">' + p.role + '</span></td>'
                    + '<td class="text-end">' + (p.courses || '—') + '</td>'
                    + '<td class="fs-sm text-muted">' + (p.outcome || '—') + '</td>';
                mapBody.appendChild(tr);
            });
    }
    mapRole.addEventListener('change', renderMap);
    mapSearch.addEventListener('input', renderMap);
    renderMap();
})();
</script>
@endpush
