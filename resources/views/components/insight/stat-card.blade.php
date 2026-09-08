@props([
    'title',            // label, e.g. "Open Pipe $ACV"
    'value',            // formatted primary value, e.g. "$359.3M"
    'sub' => null,      // sub-label, e.g. "1,118 opps"
    'metric' => null,   // metric key — when set, the card is drillable + live-updatable
    'unit' => null,     // 'currency' | 'count' — how JS should format on live refresh
])

{{-- Insight Kit KPI tile. Drillable to audited source when `metric` is set, and
     its value/sub refresh live (data-metric + data-unit) when filters change. --}}
<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm h-100 insight-stat-card'.($metric ? ' insight-drillable' : '')]) }}
     @if($metric) data-metric="{{ $metric }}" data-unit="{{ $unit }}" role="button" tabindex="0" onclick="window.Insights && window.Insights.drill(@js($metric))" @endif>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <span class="text-uppercase fs-xs fw-500 text-muted">{{ $title }}</span>
            @if($metric)
                <svg class="sa-icon sa-icon-xs text-muted" aria-label="Drill to source"><use href="/icons/sprite.svg#search"></use></svg>
            @endif
        </div>
        <div class="fs-2 fw-700 mt-1 insight-stat-value">{{ $value }}</div>
        <div class="fs-sm text-muted insight-stat-sub">{{ $sub }}</div>
    </div>
</div>
