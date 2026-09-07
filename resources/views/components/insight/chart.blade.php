@props([
    'heading' => null,
    'metric',            // metric key to fetch a breakdown for
    'dimension' => 'stage',
    'type' => 'bar',     // bar | donut | line | area
    'height' => 300,
])

@php $cid = 'insight-chart-'.\Illuminate\Support\Str::random(6); @endphp

{{-- Insight Kit chart. One ApexCharts wrapper (window.ApexCharts is global) that
     pulls a breakdown from the visibility-scoped /insights endpoint, so every
     chart in the app is drawn the same way and respects row-level scope. --}}
<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm h-100']) }}>
    <div class="card-body">
        @if($heading)<h6 class="fw-600 mb-2">{{ $heading }}</h6>@endif
        <div id="{{ $cid }}" style="min-height: {{ $height }}px"></div>
    </div>
</div>

<script>
(function () {
    const qs = () => (window.Insights ? window.Insights.query() : '');

    async function draw() {
        const el = document.getElementById(@js($cid));
        if (!el || !window.ApexCharts) return;
        const url = @js(url('insights')) + '/' + encodeURIComponent(@js($metric)) + '/breakdown?dimension=' + encodeURIComponent(@js($dimension)) + qs();
        let rows = [];
        try { rows = await (await fetch(url, {headers: {'Accept': 'application/json'}})).json(); } catch (e) { return; }
        const labels = rows.map(r => r.key);
        const data = rows.map(r => (r.acv ?? r.count ?? 0));
        const type = @js($type);
        const opts = {
            chart: { type: type === 'donut' ? 'donut' : type, height: {{ $height }}, toolbar: { show: false }, fontFamily: 'inherit' },
            colors: ['#e8600f', '#12263a', '#3b7ddd', '#6c757d', '#adb5bd', '#dee2e6'],
            dataLabels: { enabled: false },
            legend: { position: 'bottom' },
        };
        if (type === 'donut') {
            opts.series = data; opts.labels = labels;
        } else {
            opts.series = [{ name: 'ACV', data }];
            opts.xaxis = { categories: labels };
            opts.plotOptions = { bar: { borderRadius: 3, columnWidth: '55%' } };
        }
        el.innerHTML = '';
        new window.ApexCharts(el, opts).render();
    }

    // ApexCharts loads as a deferred bundle — wait for it before first draw.
    function ready(tries) {
        if (!window.ApexCharts) {
            if ((tries || 0) < 50) return setTimeout(() => ready((tries || 0) + 1), 100);
            return;
        }
        draw();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => ready(0));
    } else {
        ready(0);
    }
    window.addEventListener('insights:filters-changed', draw);
})();
</script>
