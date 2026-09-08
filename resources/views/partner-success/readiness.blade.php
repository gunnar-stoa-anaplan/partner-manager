@extends('layouts.vertical', [
    'pageTitle' => 'Readiness Plan',
    'pageSubTitle1' => 'Insights',
    'pageSubTitle2' => 'PSM Engagement',
    'pageSubTitle2Url' => url('partner-success'),
    'pageSubText' => 'One value per path for every partner, mapped to delivery demand. Toggle the lens between Baseline, Target and Gap; switch role to re-map the app paths.',
])

@section('content')
<div class="container-fluid py-3">

    @include('partner-success.partials.scope-filter')

    <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
        <h5 class="fw-600 mb-0">Readiness plan <span class="fs-sm fw-normal text-muted">— Partner × path</span></h5>
        <div class="d-flex align-items-center gap-2 ms-auto">
            <label class="fs-sm text-muted mb-0">Role</label>
            <select id="rp-role" class="form-select form-select-sm" style="width:auto">
                <option value="2" selected>Delivery</option>
                <option value="0">GTM</option>
                <option value="1">PreSales</option>
            </select>
            <label class="fs-sm text-muted mb-0">Show</label>
            <select id="rp-metric" class="form-select form-select-sm" style="width:auto">
                <option value="req" selected>Target</option>
                <option value="base">Baseline</option>
                <option value="gap">Gap</option>
            </select>
        </div>
    </div>
    <div id="rp-note" class="fs-sm text-muted mb-2"></div>

    <style>
        .rp-tbl { width: max-content; min-width: 100%; }
        .rp-tbl th, .rp-tbl td { white-space: nowrap; text-align: center; }
        .rp-tbl td.num { min-width: 64px; }
        .rp-tbl thead th:first-child,
        .rp-tbl tbody td:first-child,
        .rp-tbl tfoot td:first-child {
            position: sticky; left: 0; z-index: 3; min-width: 230px; max-width: 260px;
            text-align: left; white-space: normal;
            background: var(--bs-card-bg); box-shadow: 1px 0 0 var(--bs-border-color);
        }
        .rp-tbl tfoot td:first-child { background: var(--bs-dark); }
        .rp-pill { display: inline-block; min-width: 30px; padding: .15rem .45rem; border-radius: 999px;
                   font-size: .8rem; font-weight: 600; }
        .rp-pill.on { background: #9b2c2c; color: #fff; }
        .rp-pill.off { color: var(--bs-secondary-color); }
    </style>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle rp-tbl">
                <thead>
                    <tr>
                        <th>Partner</th>
                        @foreach($plan['paths'] as $i => $p)
                            <th class="num fs-xs" data-path="{{ $i }}" data-found="{{ $p['foundational'] ? 1 : 0 }}"
                                title="{{ $p['label'] }}">{{ $p['abbr'] }}<span class="rp-suffix"></span></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="rp-body"></tbody>
                <tfoot><tr id="rp-total"></tr></tfoot>
            </table>
        </div>
    </div>
    <div class="fs-xs text-muted mt-1">Baseline = people certified now. Target = coverage-per-deal × demand deals ({{ $plan['planNeed'][2] }} per delivery, {{ $plan['planNeed'][0] }} per GTM/PreSales deal). Gap = Target − Baseline. Foundational paths (ADO, Polaris, TAWA) carry no role suffix.</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const PLAN = @json($plan);
    const roleSel = document.getElementById('rp-role');
    const metSel = document.getElementById('rp-metric');
    const body = document.getElementById('rp-body');
    const totalRow = document.getElementById('rp-total');
    const note = document.getElementById('rp-note');

    const value = (cell, r, met) =>
        met === 'base' ? cell.base[r]
        : met === 'req' ? cell.req[r]
        : Math.max(0, cell.req[r] - cell.base[r]);

    function render() {
        const r = +roleSel.value, met = metSel.value, roleWord = PLAN.roles[r];
        // header suffix (role word for non-foundational paths)
        document.querySelectorAll('.rp-tbl thead th[data-path]').forEach(th => {
            const suf = th.querySelector('.rp-suffix');
            suf.textContent = th.dataset.found === '1' ? '' : ' ' + roleWord.slice(0, 4).toUpperCase();
        });
        // note
        const explain = met === 'base' ? 'people certified now'
            : met === 'req' ? 'people the plan requires (baseline + gap)'
            : 'people still needed vs plan (target − baseline)';
        note.innerHTML = explain + ' &middot; <strong>' + roleWord + '</strong> scope';

        const totals = PLAN.paths.map(() => 0);
        body.replaceChildren();
        PLAN.rows.forEach(row => {
            const tr = document.createElement('tr');
            let cells = '<td title="' + row.partner.replace(/"/g, '&quot;') + '">' + row.partner + '</td>';
            row.cells.forEach((c, ci) => {
                const v = value(c, r, met);
                totals[ci] += v;
                const on = met === 'gap' ? v > 0 : (met === 'req' ? c.req[r] > c.base[r] : v > 0);
                const show = v || (met === 'gap' ? '0' : '—');
                cells += '<td class="num"><span class="rp-pill ' + (v > 0 && on ? 'on' : 'off') + '">' + show + '</span></td>';
            });
            tr.innerHTML = cells;
            body.appendChild(tr);
        });
        totalRow.innerHTML = '<td>TOTAL (' + PLAN.rows.length + ')</td>'
            + totals.map(t => '<td class="num fw-700">' + (t || '') + '</td>').join('');
    }

    roleSel.addEventListener('change', render);
    metSel.addEventListener('change', render);
    render();
})();
</script>
@endpush
