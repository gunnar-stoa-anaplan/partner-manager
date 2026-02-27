{{-- Signals Tab: Cohort Comparison + Active Signals --}}

{{-- Cohort Comparison Card --}}
@if($cohortBenchmarks && $partnerMetrics)
@php
    $metrics = $cohortBenchmarks['metrics'] ?? [];
    $cohortSize = $cohortBenchmarks['cohort_size'] ?? 0;
    $computedDate = $cohortBenchmarks['computed_date'] ?? null;

    $comparisons = [
        [
            'label' => 'Health Score',
            'key' => 'avg_health_score',
            'partner' => $partnerMetrics['health_score'],
            'portfolio' => $portfolioAvgs['health_score'] ?? null,
            'format' => fn($v) => number_format($v, 1),
            'icon' => 'fa-heartbeat',
            'higher_is_better' => true,
        ],
        [
            'label' => 'Pipeline Coverage',
            'key' => 'avg_pipeline_coverage',
            'partner' => $partnerMetrics['pipeline_coverage'],
            'portfolio' => $portfolioAvgs['pipeline_coverage'] ?? null,
            'format' => fn($v) => number_format($v, 1) . 'x',
            'icon' => 'fa-chart-line',
            'higher_is_better' => true,
        ],
        [
            'label' => 'Win Rate',
            'key' => 'avg_win_rate',
            'partner' => $partnerMetrics['win_rate'],
            'portfolio' => $portfolioAvgs['win_rate'] ?? null,
            'format' => fn($v) => number_format($v, 1) . '%',
            'icon' => 'fa-trophy',
            'higher_is_better' => true,
        ],
        [
            'label' => 'Active Certifications',
            'key' => 'certification_density',
            'partner' => $partnerMetrics['certification_density'],
            'portfolio' => $portfolioAvgs['certification_density'] ?? null,
            'format' => fn($v) => number_format($v, is_int($v) || $v == (int)$v ? 0 : 1),
            'icon' => 'fa-award',
            'higher_is_better' => true,
        ],
    ];
@endphp

<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">
            <i class="fal fa-analytics me-1"></i>
            Benchmark Comparison
        </h6>
        <div class="d-flex align-items-center gap-2">
            @if($computedDate)
            <small class="text-muted">Cohort data from {{ \Carbon\Carbon::parse($computedDate)->format('M j, Y') }}</small>
            @endif
            <span class="badge bg-secondary">{{ $cohortSize }} peer organizations</span>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted fs-sm mb-3">How this partner compares against your portfolio average and the anonymized industry cohort of {{ $cohortSize }} similar organizations.</p>

        <div class="row">
            @foreach($comparisons as $comp)
            @php
                $cohort = $metrics[$comp['key']] ?? null;
                $partnerVal = $comp['partner'];
                $portfolioVal = $comp['portfolio'];
                $formatFn = $comp['format'];

                $partnerVsCohort = null;
                $partnerVsPortfolio = null;

                if ($partnerVal !== null && $cohort) {
                    if ($partnerVal < $cohort['p25']) {
                        $partnerVsCohort = ['label' => 'Bottom quartile', 'color' => 'danger', 'icon' => 'fa-arrow-down'];
                    } elseif ($partnerVal < $cohort['median']) {
                        $partnerVsCohort = ['label' => 'Below median', 'color' => 'warning', 'icon' => 'fa-arrow-down'];
                    } elseif ($partnerVal >= $cohort['p75']) {
                        $partnerVsCohort = ['label' => 'Top quartile', 'color' => 'success', 'icon' => 'fa-arrow-up'];
                    } else {
                        $partnerVsCohort = ['label' => 'Above median', 'color' => 'info', 'icon' => 'fa-arrow-up'];
                    }
                }
            @endphp
            <div class="col-md-6 col-xl-3 mb-3">
                <div class="border rounded p-3 h-100">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fal {{ $comp['icon'] }} text-primary me-2"></i>
                        <span class="fw-500 fs-sm">{{ $comp['label'] }}</span>
                    </div>

                    @if($partnerVal !== null)
                    <div class="h4 mb-2">{{ $formatFn($partnerVal) }}</div>
                    @else
                    <div class="h4 mb-2 text-muted">N/A</div>
                    @endif

                    @if($partnerVsCohort)
                    <span class="badge bg-{{ $partnerVsCohort['color'] }} bg-opacity-10 text-{{ $partnerVsCohort['color'] }} mb-2">
                        <i class="fal {{ $partnerVsCohort['icon'] }} me-1"></i>{{ $partnerVsCohort['label'] }}
                    </span>
                    @endif

                    <div class="fs-xs text-muted">
                        @if($portfolioVal !== null)
                        <div class="d-flex justify-content-between">
                            <span>Portfolio avg:</span>
                            <span class="fw-500">{{ $formatFn($portfolioVal) }}</span>
                        </div>
                        @endif
                        @if($cohort)
                        <div class="d-flex justify-content-between">
                            <span>Cohort p25:</span>
                            <span class="fw-500">{{ $formatFn($cohort['p25']) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Cohort median:</span>
                            <span class="fw-500">{{ $formatFn($cohort['median']) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Cohort p75:</span>
                            <span class="fw-500">{{ $formatFn($cohort['p75']) }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Visual position indicator --}}
                    @if($partnerVal !== null && $cohort)
                    @php
                        $min = min($cohort['p25'] * 0.7, $partnerVal * 0.9);
                        $max = max($cohort['p75'] * 1.3, $partnerVal * 1.1);
                        $range = $max - $min;
                        $p25Pos = $range > 0 ? (($cohort['p25'] - $min) / $range) * 100 : 25;
                        $medianPos = $range > 0 ? (($cohort['median'] - $min) / $range) * 100 : 50;
                        $p75Pos = $range > 0 ? (($cohort['p75'] - $min) / $range) * 100 : 75;
                        $partnerPos = $range > 0 ? (($partnerVal - $min) / $range) * 100 : 50;
                        $partnerPos = max(0, min(100, $partnerPos));
                    @endphp
                    <div class="position-relative mt-3" style="height: 24px;">
                        {{-- Cohort range bar --}}
                        <div class="position-absolute bg-secondary bg-opacity-10 rounded"
                             style="left: {{ $p25Pos }}%; right: {{ 100 - $p75Pos }}%; top: 8px; height: 8px;"></div>
                        {{-- Median marker --}}
                        <div class="position-absolute bg-secondary rounded"
                             style="left: {{ $medianPos }}%; top: 6px; width: 2px; height: 12px; transform: translateX(-1px);"></div>
                        {{-- Partner marker --}}
                        <div class="position-absolute rounded-circle border border-2 border-{{ $partnerVsCohort['color'] }} bg-white"
                             style="left: {{ $partnerPos }}%; top: 4px; width: 16px; height: 16px; transform: translateX(-8px);"></div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@elseif(!$cohortBenchmarks)
<div class="card shadow-0 mb-g">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <i class="fal fa-info-circle text-muted me-2 fs-4"></i>
            <div>
                <p class="mb-1 fw-500">Cohort benchmarks not available</p>
                <p class="mb-0 text-muted fs-sm">Enable Signal Network sharing in <a href="{{ route('configuration.index') }}">Configuration</a> to receive anonymized industry benchmarks from similar organizations.</p>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Active Signals --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">
            <i class="fal fa-radar me-1"></i>
            Signals
            @if($signals->where('status', 'active')->count() > 0)
            <span class="badge bg-warning ms-1">{{ $signals->where('status', 'active')->count() }} active</span>
            @endif
        </h6>
        <a href="{{ route('act.signals.index', ['partner_id' => $partner->id]) }}" class="btn btn-xs btn-outline-primary">
            View in Signal Engine
        </a>
    </div>

    @if($signals->isEmpty())
    <div class="card-body">
        <p class="text-muted mb-0">No signals have been generated for this partner yet.</p>
    </div>
    @else
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 40px;"></th>
                        <th>Signal</th>
                        <th style="width: 100px;">Channel</th>
                        <th style="width: 90px;">Severity</th>
                        <th style="width: 90px;">Status</th>
                        <th style="width: 80px;">Confidence</th>
                        <th style="width: 110px;">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($signals as $signal)
                    <tr>
                        <td class="text-center">
                            <span class="badge bg-{{ \App\Models\Signal::CHANNEL_COLORS[$signal->channel] ?? 'secondary' }} rounded-circle p-1" style="width:24px;height:24px;">
                                <svg class="sa-icon text-white" style="width:12px;height:12px">
                                    <use href="/icons/sprite.svg#{{ \App\Models\Signal::CHANNEL_ICONS[$signal->channel] ?? 'activity' }}"></use>
                                </svg>
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('act.signals.show', $signal) }}" class="fw-500 text-decoration-none">
                                {{ Str::limit($signal->title, 80) }}
                            </a>
                            @if($signal->source_processor === 'cohort_benchmark')
                            <span class="badge bg-info bg-opacity-10 text-info ms-1 fs-xs">Cohort</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ \App\Models\Signal::CHANNEL_COLORS[$signal->channel] ?? 'secondary' }} bg-opacity-10 text-{{ \App\Models\Signal::CHANNEL_COLORS[$signal->channel] ?? 'secondary' }}">
                                {{ \App\Models\Signal::CHANNELS[$signal->channel] ?? $signal->channel }}
                            </span>
                        </td>
                        <td>
                            @php $sevColors = ['critical' => 'danger', 'warning' => 'warning', 'info' => 'info', 'positive' => 'success']; @endphp
                            <span class="badge bg-{{ $sevColors[$signal->severity] ?? 'secondary' }} bg-opacity-10 text-{{ $sevColors[$signal->severity] ?? 'secondary' }}">
                                {{ ucfirst($signal->severity) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ \App\Models\Signal::STATUS_COLORS[$signal->status] ?? 'secondary' }} bg-opacity-10 text-{{ \App\Models\Signal::STATUS_COLORS[$signal->status] ?? 'secondary' }}">
                                {{ ucfirst($signal->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1" style="height: 4px;">
                                    <div class="progress-bar" style="width: {{ $signal->confidence_score * 100 }}%"></div>
                                </div>
                                <small class="ms-1 text-muted">{{ number_format($signal->confidence_score * 100, 0) }}%</small>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">{{ $signal->created_at->format('M j') }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
