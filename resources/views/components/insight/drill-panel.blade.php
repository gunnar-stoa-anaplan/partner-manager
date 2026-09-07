{{-- Insight Kit drill panel. Include ONCE per page. window.Insights.drill(metric)
     fetches the audited source rows + SQL + provenance from the visibility-scoped
     /insights/{metric}/drill endpoint and renders them here. This is how any
     number, anywhere, becomes explainable in the same way. --}}
<div id="insight-drill" class="offcanvas offcanvas-end" tabindex="-1" style="width: min(920px, 96vw);" aria-labelledby="insight-drill-title">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="insight-drill-title">Source</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="insight-drill-provenance" class="mb-2 fs-sm text-muted"></div>
        <details class="mb-3">
            <summary class="fs-sm fw-500">Definition SQL</summary>
            <pre class="bg-body-tertiary p-2 rounded mt-2 fs-xs" id="insight-drill-sql" style="white-space:pre-wrap"></pre>
        </details>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead id="insight-drill-head"></thead>
                <tbody id="insight-drill-body"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    window.Insights = window.Insights || { query() { return ''; } };

    window.Insights.drill = async function (metric) {
        const base = @js(url('insights'));
        const q = window.Insights.query ? window.Insights.query() : '';
        const panelEl = document.getElementById('insight-drill');
        const panel = window.bootstrap ? window.bootstrap.Offcanvas.getOrCreateInstance(panelEl) : null;
        if (panel) panel.show();

        document.getElementById('insight-drill-body').innerHTML = '<tr><td class="text-muted">Loading…</td></tr>';
        let d = {};
        try {
            d = await (await fetch(base + '/' + encodeURIComponent(metric) + '/drill?' + q.replace(/^&/, ''), {headers: {'Accept': 'application/json'}})).json();
        } catch (e) {
            document.getElementById('insight-drill-body').innerHTML = '<tr><td class="text-danger">Could not load source.</td></tr>';
            return;
        }

        document.getElementById('insight-drill-title').textContent = 'Source — ' + (d.row_count ?? (d.rows || []).length) + ' rows';
        document.getElementById('insight-drill-sql').textContent = d.sql || '';
        const prov = (d.provenance || []).map(p => (p.source_file || '') + ' @ ' + (p.snapshot_date || '')).join(' · ');
        document.getElementById('insight-drill-provenance').textContent = prov ? ('Provenance: ' + prov) : '';

        const cols = d.columns || (d.rows && d.rows[0] ? Object.keys(d.rows[0]) : []);
        document.getElementById('insight-drill-head').innerHTML = '<tr>' + cols.map(c => '<th>' + c + '</th>').join('') + '</tr>';
        document.getElementById('insight-drill-body').innerHTML = (d.rows || []).slice(0, 500).map(r =>
            '<tr>' + cols.map(c => '<td>' + (r[c] ?? '') + '</td>').join('') + '</tr>'
        ).join('') || '<tr><td class="text-muted">No rows.</td></tr>';
    };
})();
</script>
