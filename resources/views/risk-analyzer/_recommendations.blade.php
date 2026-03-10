{{-- Automated Recommendations --}}
@if($recommendations->isEmpty())
<div class="alert alert-success">No critical recommendations at this time. Portfolio is in good shape!</div>
@else
<div class="row mb-3">
    <div class="col-auto">
        <span class="badge bg-danger bg-opacity-25 text-danger">{{ $recommendations->where('severity', 'critical')->count() }} Critical</span>
        <span class="badge bg-warning bg-opacity-25 text-warning ms-1">{{ $recommendations->where('severity', 'warning')->count() }} Warning</span>
    </div>
</div>

@foreach($recommendations as $rec)
@php
    $borderColor = match($rec['severity']) {
        'critical' => 'danger',
        'warning' => 'warning',
        default => 'info',
    };
    $iconMap = [
        'staffing_gap' => 'alert-triangle',
        'cross_training' => 'refresh-cw',
        'stage_stale' => 'clock',
    ];
    $icon = $iconMap[$rec['type']] ?? 'info';
@endphp
<div class="card shadow-0 mb-2 border-start border-{{ $borderColor }} border-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-start">
            <svg class="sa-icon text-{{ $borderColor }} me-2 flex-shrink-0 mt-1" style="width:20px;height:20px"><use href="/icons/sprite.svg#{{ $icon }}"></use></svg>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fw-500">{{ $rec['partner'] }}</span>
                    <span class="badge bg-{{ $borderColor }} bg-opacity-25 text-{{ $borderColor }} text-uppercase" style="font-size:0.65rem">{{ str_replace('_', ' ', $rec['type']) }}</span>
                </div>
                <p class="mb-0 small">{{ $rec['message'] }}</p>
                <p class="mb-0 text-muted small">{{ $rec['detail'] }}</p>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif
