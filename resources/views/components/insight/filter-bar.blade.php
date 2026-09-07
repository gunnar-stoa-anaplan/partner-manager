@props([
    'geos' => ['All', 'AMER', 'APAC', 'EMEA'],
    'partnersUrl' => null,   // endpoint returning a JSON array of partner names
])

{{-- Insight Kit filter bar. Publishes the current geo/partner filter to
     window.Insights and fires `insights:filters-changed` so every chart/card
     on the page re-runs against the same scope. --}}
<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm mb-3']) }}>
    <div class="card-body d-flex flex-wrap align-items-center gap-2">
        <div class="btn-group btn-group-sm" role="group" aria-label="GEO filter" id="insight-geo">
            @foreach($geos as $g)
                <button type="button" class="btn btn-outline-secondary{{ $loop->first ? ' active' : '' }}"
                        data-geo="{{ $g === 'All' ? '' : $g }}">{{ $g }}</button>
            @endforeach
        </div>
        <select id="insight-partner" class="form-select form-select-sm w-auto">
            <option value="">All partners</option>
        </select>
    </div>
</div>

<script>
(function () {
    window.Insights = window.Insights || {
        _geo: '', _partner: '',
        query() {
            const p = [];
            if (this._geo) p.push('geo=' + encodeURIComponent(this._geo));
            if (this._partner) p.push('partner=' + encodeURIComponent(this._partner));
            return p.length ? '&' + p.join('&') : '';
        },
        _changed() { window.dispatchEvent(new CustomEvent('insights:filters-changed')); },
    };

    document.querySelectorAll('#insight-geo [data-geo]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#insight-geo [data-geo]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            window.Insights._geo = btn.getAttribute('data-geo') || '';
            window.Insights._changed();
        });
    });

    const sel = document.getElementById('insight-partner');
    if (sel) {
        sel.addEventListener('change', () => { window.Insights._partner = sel.value; window.Insights._changed(); });
        @if($partnersUrl)
        fetch(@js($partnersUrl), {headers: {'Accept': 'application/json'}})
            .then(r => r.json())
            .then(list => (list || []).forEach(name => {
                const o = document.createElement('option'); o.value = name; o.textContent = name; sel.appendChild(o);
            }))
            .catch(() => {});
        @endif
    }
})();
</script>
