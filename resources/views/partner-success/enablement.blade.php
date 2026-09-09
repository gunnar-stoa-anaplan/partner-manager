@extends('layouts.vertical', [
    'pageTitle' => 'Enablement',
    'pageSubTitle1' => 'Insights',
    'pageSubTitle2' => 'PSM Engagement',
    'pageSubTitle2Url' => url('partner-success'),
    'pageSubText' => 'Enablement across people and product coverage. Scoped to your partners and the filters above.',
])

@section('content')
<div class="container-fluid py-3">

    @include('partner-success.partials.scope-filter')

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-indiv" type="button">By individual</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-cov" type="button">By product &amp; role</button></li>
    </ul>

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
})();
</script>
@endpush
