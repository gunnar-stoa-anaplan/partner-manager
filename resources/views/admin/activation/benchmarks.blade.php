@extends('layouts.vertical', ['pageTitle' => 'Activation Benchmarks'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Activation Benchmarks',
        'pageSubTitle1' => 'Feature Activation',
        'pageSubTitle1Url' => route('admin.activation.paths'),
        'pageSubTitle2' => 'Activation Paths',
        'pageSubTitle2Url' => route('admin.activation.paths'),
        'pageSubText' => 'Compare your activation performance against peer cohort benchmarks',
    ])
@endsection

@section('content')

{{-- Summary Cards --}}
<div class="row mb-g">
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Overall Completion</h6>
                <h2 class="mb-0">{{ number_format(($aggregate['summary']['overall_completion_rate'] ?? 0) * 100, 1) }}%</h2>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Avg Velocity</h6>
                <h2 class="mb-0">{{ $aggregate['summary']['avg_velocity_days'] ?? '—' }} <small class="text-muted">days</small></h2>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Users in Training</h6>
                <h2 class="mb-0">{{ $aggregate['summary']['users_in_training'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-0 h-100">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Features Activated</h6>
                <h2 class="mb-0">{{ $aggregate['summary']['features_fully_activated'] ?? 0 }} <small class="text-muted">/ {{ $aggregate['summary']['total_paths'] ?? 0 }}</small></h2>
            </div>
        </div>
    </div>
</div>

{{-- Cohort Benchmark Comparison --}}
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title mb-0">Cohort Benchmark Comparison</div>
    </div>
    <div class="card-body">
        @if($hasBenchmarks)
            @php
                $metrics = $benchmarks['metrics'] ?? [];
                $cohortSize = $benchmarks['cohort_size'] ?? 0;
                $computedDate = $benchmarks['computed_date'] ?? null;
            @endphp
            <p class="text-muted mb-3">
                Benchmarks computed from <strong>{{ $cohortSize }}</strong> peer instances
                @if($computedDate) on {{ \Carbon\Carbon::parse($computedDate)->format('M d, Y') }}@endif
            </p>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th class="text-center">Your Value</th>
                            <th class="text-center">p25</th>
                            <th class="text-center">Median</th>
                            <th class="text-center">p75</th>
                            <th class="text-center">Mean</th>
                            <th class="text-center">Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $summary = $aggregate['summary'] ?? [];
                            $phaseDays = $summary['avg_phase_days'] ?? [];
                            $ownMetrics = [
                                'overall_completion_rate' => ['label' => 'Completion Rate', 'value' => ($summary['overall_completion_rate'] ?? 0) * 100, 'suffix' => '%', 'higher_better' => true],
                                'avg_velocity_days' => ['label' => 'Avg Velocity', 'value' => $summary['avg_velocity_days'] ?? null, 'suffix' => ' days', 'higher_better' => false],
                                'features_fully_activated' => ['label' => 'Features Activated', 'value' => $summary['features_fully_activated'] ?? 0, 'suffix' => '', 'higher_better' => true],
                                'tell_avg_days' => ['label' => 'Tell Phase Avg', 'value' => $phaseDays['tell'] ?? null, 'suffix' => ' days', 'higher_better' => false],
                                'show_avg_days' => ['label' => 'Show Phase Avg', 'value' => $phaseDays['show'] ?? null, 'suffix' => ' days', 'higher_better' => false],
                                'do_avg_days' => ['label' => 'Do Phase Avg', 'value' => $phaseDays['do'] ?? null, 'suffix' => ' days', 'higher_better' => false],
                            ];
                        @endphp
                        @foreach($ownMetrics as $key => $meta)
                            @php
                                $benchmark = $metrics[$key] ?? null;
                                $own = $meta['value'];
                                $position = '—';
                                $positionClass = '';
                                if ($benchmark && $own !== null) {
                                    $median = $benchmark['median'] ?? 0;
                                    $p25 = $benchmark['p25'] ?? 0;
                                    $p75 = $benchmark['p75'] ?? 0;
                                    if ($meta['higher_better']) {
                                        if ($own >= $p75) { $position = 'Top 25%'; $positionClass = 'text-success'; }
                                        elseif ($own >= $median) { $position = 'Above Median'; $positionClass = 'text-success'; }
                                        elseif ($own >= $p25) { $position = 'Below Median'; $positionClass = 'text-warning'; }
                                        else { $position = 'Bottom 25%'; $positionClass = 'text-danger'; }
                                    } else {
                                        if ($own <= $p25) { $position = 'Top 25%'; $positionClass = 'text-success'; }
                                        elseif ($own <= $median) { $position = 'Above Median'; $positionClass = 'text-success'; }
                                        elseif ($own <= $p75) { $position = 'Below Median'; $positionClass = 'text-warning'; }
                                        else { $position = 'Bottom 25%'; $positionClass = 'text-danger'; }
                                    }
                                }
                            @endphp
                            <tr>
                                <td>{{ $meta['label'] }}</td>
                                <td class="text-center fw-bold">{{ $own !== null ? number_format($own, 1) . $meta['suffix'] : '—' }}</td>
                                <td class="text-center text-muted">{{ isset($benchmark['p25']) ? number_format($benchmark['p25'], 1) . $meta['suffix'] : '—' }}</td>
                                <td class="text-center text-muted">{{ isset($benchmark['median']) ? number_format($benchmark['median'], 1) . $meta['suffix'] : '—' }}</td>
                                <td class="text-center text-muted">{{ isset($benchmark['p75']) ? number_format($benchmark['p75'], 1) . $meta['suffix'] : '—' }}</td>
                                <td class="text-center text-muted">{{ isset($benchmark['mean']) ? number_format($benchmark['mean'], 1) . $meta['suffix'] : '—' }}</td>
                                <td class="text-center"><span class="{{ $positionClass }} fw-bold">{{ $position }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center text-muted py-4">
                <i data-feather="bar-chart-2" style="width:48px;height:48px;" class="mb-3"></i>
                <p>Cohort benchmarks are not yet available.<br>Benchmarks will appear once enough peer instances report activation data.</p>
            </div>
        @endif
    </div>
</div>

{{-- Per-Path Metrics --}}
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title mb-0">Path Performance</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Feature Path</th>
                        <th class="text-center">Assigned</th>
                        <th class="text-center">Completed</th>
                        <th class="text-center">Completion Rate</th>
                        <th class="text-center">Avg Velocity</th>
                        <th class="text-center">Last 30d</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aggregate['paths'] as $path)
                        @php
                            $rate = ($path['completion_rate'] ?? 0) * 100;
                            $rateClass = $rate >= 75 ? 'text-success' : ($rate >= 25 ? 'text-warning' : 'text-danger');
                        @endphp
                        <tr>
                            <td>{{ $path['feature'] }}</td>
                            <td class="text-center">{{ $path['assigned_count'] }}</td>
                            <td class="text-center">{{ $path['completed_count'] }}</td>
                            <td class="text-center {{ $rateClass }} fw-bold">{{ number_format($rate, 1) }}%</td>
                            <td class="text-center">{{ $path['avg_days_to_complete'] !== null ? number_format($path['avg_days_to_complete'], 1) . 'd' : '—' }}</td>
                            <td class="text-center">{{ $path['velocity']['completions_last_30d'] ?? 0 }}</td>
                            <td>
                                @if($path['instance_active'])
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Bottleneck Steps --}}
@if(!empty($aggregate['summary']['bottleneck_steps']))
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title mb-0">Bottleneck Steps</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Path</th>
                        <th>Step</th>
                        <th>Phase</th>
                        <th class="text-center">Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aggregate['summary']['bottleneck_steps'] as $bottleneck)
                        <tr>
                            <td>{{ $bottleneck['path'] }}</td>
                            <td>{{ $bottleneck['step'] }}</td>
                            <td><span class="badge bg-info">{{ ucfirst($bottleneck['phase'] ?? 'n/a') }}</span></td>
                            <td class="text-center text-danger fw-bold">{{ number_format(($bottleneck['completion_rate'] ?? 0) * 100, 1) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection
