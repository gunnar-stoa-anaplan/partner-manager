@php
    $dimDefs = [
        'geos' => 'GEO',
        'partners' => 'Partners',
        'lobs' => 'Line of Business',
        'stages' => 'Sales Stage',
        'quarters' => 'Close Quarter',
        'capGroups' => 'Capability',
    ];
@endphp

{{-- Pinned, collapsible scope panel (toggle pills, applied instantly). Session-
     backed and shared with the Dashboard, so the selection persists across all
     PSM Engagement views. On this page a change reloads to recompute. --}}
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
                <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#filter"></use></svg> Filters
            </span>
            <span id="ps-active-chips" class="d-flex align-items-center gap-1 flex-wrap"></span>
        </div>
        <svg class="sa-icon sa-icon-sm text-muted"><use href="/icons/sprite.svg#chevron-down"></use></svg>
    </div>
    {{-- Always start collapsed/minimized; the active-filter chips in the header
         show what's applied without expanding the panel. --}}
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

<script>
(function () {
    const CSRF = @js(csrf_token());
    const SET = @js(route('partner-success.filters.set'));
    const CLEAR = @js(route('partner-success.filters.clear'));
    const DIM_LABELS = @js($dimDefs);

    function collect() {
        const dims = {};
        document.querySelectorAll('.insight-pill.is-active').forEach(p =>
            (dims[p.dataset.dim] = dims[p.dataset.dim] || []).push(p.dataset.value));
        return dims;
    }
    function renderChips(dims) {
        const el = document.getElementById('ps-active-chips'); if (!el) return;
        el.replaceChildren();
        const parts = [];
        Object.keys(DIM_LABELS).forEach(k => (dims[k] || []).forEach(v => {
            const c = document.createElement('span');
            c.className = 'badge bg-body-secondary text-body border fw-normal';
            c.textContent = DIM_LABELS[k] + ': ' + v; parts.push(c);
        }));
        if (parts.length) parts.forEach(c => el.appendChild(c));
        else { const s = document.createElement('span'); s.className = 'text-muted fs-sm fst-italic'; s.textContent = 'Showing all your data — no filters applied'; el.appendChild(s); }
    }
    async function persistAndReload(url, dims) {
        renderChips(dims);
        await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(dims) });
        window.location.reload();
    }
    let timer;
    document.querySelectorAll('.insight-pill').forEach(p => p.addEventListener('click', () => {
        p.classList.toggle('is-active');
        clearTimeout(timer);
        timer = setTimeout(() => persistAndReload(SET, collect()), 300);
    }));
    const clearBtn = document.getElementById('ps-clear');
    if (clearBtn) clearBtn.addEventListener('click', () => persistAndReload(CLEAR, {}));
    renderChips(collect());
})();
</script>
