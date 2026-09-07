@props([
    'title',            // label, e.g. "Open Pipe $ACV"
    'value',            // formatted primary value, e.g. "$359.3M"
    'sub' => null,      // sub-label, e.g. "1,118 opps"
    'metric' => null,   // metric key — when set, the card is drillable to source
])

{{-- Insight Kit KPI tile. Consistent across every view; when `metric` is set the
     card drills to its audited source rows via the shared drill panel. --}}
<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm h-100 insight-stat-card'.($metric ? ' insight-drillable' : '')]) }}
     @if($metric) role="button" tabindex="0" onclick="window.Insights && window.Insights.drill(@js($metric))" @endif>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <span class="text-uppercase fs-xs fw-500 text-muted">{{ $title }}</span>
            @if($metric)
                <svg class="sa-icon sa-icon-xs text-muted" aria-label="Drill to source"><use href="/icons/sprite.svg#search"></use></svg>
            @endif
        </div>
        <div class="fs-2 fw-700 mt-1">{{ $value }}</div>
        @if($sub)
            <div class="fs-sm text-muted">{{ $sub }}</div>
        @endif
    </div>
</div>
