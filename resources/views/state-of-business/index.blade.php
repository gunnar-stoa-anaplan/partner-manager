@use('App\Models\PsmObjective')
@use('App\Models\WeeklyFocusItem')
@php
    function shortMoney(float $value, int $decimals = 1): string {
        $abs = abs($value);
        $sign = $value < 0 ? '-' : '';
        if ($abs >= 1_000_000) {
            return $sign . '$' . number_format($abs / 1_000_000, $decimals) . 'M';
        }
        if ($abs >= 1_000) {
            return $sign . '$' . number_format($abs / 1_000, 0) . 'K';
        }
        return $sign . '$' . number_format($abs, 0);
    }
@endphp
@extends('layouts.vertical', ['pageTitle' => 'State of The Business'])

@section('css')
<style>
    #sob-draggable-sessions .fc-event {
        cursor: grab;
        padding: 5px 8px;
        margin-bottom: 5px;
        border-radius: 4px;
        font-size: 0.8rem;
        color: #fff;
        border: none;
    }
    #sob-draggable-sessions .fc-event:active { cursor: grabbing; }
    #sob-draggable-sessions .fc-event .event-meta {
        font-size: 0.7rem;
        opacity: 0.85;
    }
</style>
@endsection

@section('page-title')
    <div class="d-flex align-items-end mb-4">
        <div>
            @include('layouts.partials/app-pagetitle', ['pageTitle' => 'State of The Business', 'pageSubTitle1' => 'Insights', 'pageSubTitle2' => 'Dashboards'])
        </div>

        <div class="ms-auto d-none d-sm-flex align-items-center">
            {{-- Consolidated scope / partner / filter dropdown --}}
            @php
                $currentLabel = 'All Partners';
                if ($activeFilter ?? null) {
                    $currentLabel = $activeFilter->name;
                } elseif ($selectedPartner ?? null) {
                    $currentLabel = $selectedPartner->name;
                } elseif (($scope ?? 'all') === 'mine') {
                    $currentLabel = 'My Partners';
                }
            @endphp
            <div class="me-3 pe-3 border border-top-0 border-bottom-0 border-start-0">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 200px; text-align: left;">
                        <i class="fal fa-filter me-1"></i> {{ $currentLabel }}
                    </button>
                    <div class="dropdown-menu shadow" style="min-width: 240px;">
                        {{-- All Partners --}}
                        @if(auth()->user()->isAdmin())
                            <a class="dropdown-item {{ ($scope ?? 'all') === 'all' && !$selectedPartnerId && !($activeFilter ?? null) ? 'active' : '' }}"
                               href="{{ route('state-of-business', ['scope' => 'all']) }}">
                                All Partners
                            </a>
                        @endif

                        {{-- My Partners → multilevel submenu --}}
                        <div class="dropdown-multilevel">
                            <div class="dropdown-item d-flex align-items-center justify-content-between {{ ($scope ?? '') === 'mine' || $selectedPartnerId ? 'fw-500' : '' }}">
                                My Partners
                                <i class="sa sa-chevron-right"></i>
                            </div>
                            <div class="dropdown-menu" style="max-height: 360px; overflow-y: auto;">
                                <a class="dropdown-item {{ ($scope ?? '') === 'mine' && !$selectedPartnerId && !($activeFilter ?? null) ? 'active' : '' }}"
                                   href="{{ route('state-of-business', ['scope' => 'mine']) }}">
                                    All My Partners
                                </a>
                                <div class="dropdown-divider"></div>
                                @foreach($myPartners as $p)
                                    <a class="dropdown-item {{ ($selectedPartnerId ?? '') == $p->id ? 'active' : '' }}"
                                       href="{{ route('state-of-business', ['partner' => $p->id]) }}">
                                        {{ $p->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Custom Filters → multilevel submenu --}}
                        <div class="dropdown-multilevel">
                            <div class="dropdown-item d-flex align-items-center justify-content-between {{ ($activeFilter ?? null) ? 'fw-500' : '' }}">
                                Custom Filters
                                <i class="sa sa-chevron-right"></i>
                            </div>
                            <div class="dropdown-menu" style="min-width: 220px;">
                                @foreach($savedFilters ?? [] as $sf)
                                    <div class="d-flex align-items-center">
                                        <a class="dropdown-item flex-grow-1 {{ ($activeFilter ?? null) && $activeFilter->id === $sf->id ? 'active' : '' }}"
                                           href="{{ route('state-of-business', ['filter' => $sf->id]) }}">
                                            {{ $sf->name }}
                                        </a>
                                        <button class="btn btn-sm btn-link text-danger p-0 me-2 delete-filter-btn" data-id="{{ $sf->id }}" title="Delete">
                                            <i class="fal fa-trash-alt" style="font-size: 0.75rem"></i>
                                        </button>
                                    </div>
                                @endforeach
                                @if(($savedFilters ?? collect())->isNotEmpty())
                                    <div class="dropdown-divider"></div>
                                @endif
                                <a class="dropdown-item text-primary" href="#" data-bs-toggle="modal" data-bs-target="#newFilterModal">
                                    <i class="fal fa-plus me-1"></i> Create Filter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <div class="d-inline-flex flex-column justify-content-center me-2">
                    <span class="fw-500 fs-xs d-block">
                        <small>PIPELINE NNACV</small>
                    </span>
                    <span class="fw-500 fs-xl d-block text-primary">{{ shortMoney($current['total_nnacv'] ?? 0) }}</span>
                </div>
                @if(($trends ?? collect())->count() > 1)
                <div class="d-none d-md-inline-flex">
                    <span class="peity-bar d-none" data-peity='{ "fill": ["var(--primary-500)"], "height": 34, "width": "85" }'>{{ ($trends ?? collect())->pluck('nnacv')->map(fn($v) => round($v / 1000000, 1))->implode(', ') }}</span>
                </div>
                @endif
            </div>
            <div class="d-flex align-items-center border border-top-0 border-bottom-0 border-end-0 ms-3 ps-3">
                <div class="d-inline-flex flex-column justify-content-center me-2">
                    <span class="fw-500 fs-xs d-block">
                        <small>PIPELINE OPPS</small>
                    </span>
                    <span class="fw-500 fs-xl d-block text-success">{{ $current['opp_count'] ?? 0 }}</span>
                </div>
                @if(($trends ?? collect())->count() > 1)
                <div class="d-none d-md-inline-flex">
                    <span class="peity-bar d-none" data-peity='{ "fill": ["var(--success-500)"], "height": 34, "width": "85" }'>{{ ($trends ?? collect())->pluck('opps')->implode(', ') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('content')

<div class="row">
    <div class="col-lg-12">
        <!-- Historical Trends -->
        <div id="panel-1" class="panel panel-icon">
            <div class="panel-hdr">
                <h2> Historical <span class="fw-light"><i>Trends</i></span></h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Toggle" type="button">
                        <svg class="sa-icon">
                            <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                            <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                        </svg>
                    </button>
                    <button class="btn btn-panel" data-action="panel-fullscreen" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Fullscreen" type="button">
                        <svg class="sa-icon">
                            <use href="/icons/sprite.svg#stop-circle"></use>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content p-0">
                    <div class="row row-grid g-0">
                        <div class="col-sm-4 p-3">
                            @php
                                $cur = $current ?? [];
                                $trendData = $trends ?? collect();
                                $latestTrend = $trendData->last() ?? [];
                                $firstTrend = $trendData->first() ?? [];
                                // NNACV Growth % (first snapshot to latest — full historical)
                                $nnacvGrowth = ($firstTrend['nnacv'] ?? 0) > 0
                                    ? round((($latestTrend['nnacv'] ?? 0) - $firstTrend['nnacv']) / $firstTrend['nnacv'] * 100)
                                    : 0;
                                // Resource Growth %
                                $resGrowth = ($firstTrend['resources'] ?? 0) > 0
                                    ? round((($latestTrend['resources'] ?? 0) - $firstTrend['resources']) / $firstTrend['resources'] * 100)
                                    : 0;
                                // Partner Sourced %
                                $sourcedPct = round($latestTrend['sourced_pct'] ?? 0);
                                // Win Rate %
                                $winRate = round($latestTrend['win_rate'] ?? 0);
                                // CS Health Red %
                                $redPct = round($latestTrend['red_pct'] ?? 0);
                            @endphp

                            <div class="d-flex mt-3">
                                <span class="h6 m-0 fw-300">NNACV Growth</span>
                                <span class="h6 d-inline-block ms-auto {{ $nnacvGrowth >= 0 ? 'text-success' : 'text-danger' }}">{{ $nnacvGrowth > 0 ? '+' : '' }}{{ $nnacvGrowth }}%</span>
                            </div>
                            <div class="progress progress mb-4 border shadow-inset-3">
                                <div class="progress-bar {{ $nnacvGrowth >= 0 ? 'bg-primary-700' : 'bg-danger-500' }} progress-bar-striped" role="progressbar" style="width: {{ min(abs($nnacvGrowth), 100) }}%;"></div>
                            </div>
                            <div class="d-flex">
                                <span class="h6 m-0 fw-300">Delivery Resources Growth</span>
                                <span class="h6 d-inline-block ms-auto {{ $resGrowth >= 0 ? 'text-success' : 'text-danger' }}">{{ $resGrowth > 0 ? '+' : '' }}{{ $resGrowth }}%</span>
                            </div>
                            <div class="progress progress mb-4 border shadow-inset-3">
                                <div class="progress-bar {{ $resGrowth >= 0 ? 'bg-primary-700' : 'bg-danger-500' }} progress-bar-striped" role="progressbar" style="width: {{ min(abs($resGrowth), 100) }}%;"></div>
                            </div>
                            <div class="d-flex">
                                <span class="h6 m-0 fw-300">Partner Sourced</span>
                                <span class="h6 d-inline-block ms-auto">{{ $sourcedPct }}%</span>
                            </div>
                            <div class="progress progress mb-4 border shadow-inset-3">
                                <div class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar" style="width: {{ $sourcedPct }}%;"></div>
                            </div>
                            <div class="d-flex">
                                <span class="h6 m-0 fw-300">Win Rate</span>
                                <span class="h6 d-inline-block ms-auto">{{ $winRate }}%</span>
                            </div>
                            <div class="progress progress mb-4 border shadow-inset-3">
                                <div class="progress-bar bg-primary-700 progress-bar-striped" role="progressbar" style="width: {{ $winRate }}%;"></div>
                            </div>
                            <div class="d-flex">
                                <span class="h6 m-0 fw-300">Red Implementations</span>
                                <span class="h6 d-inline-block ms-auto {{ $redPct > 20 ? 'text-danger' : '' }}">{{ $redPct }}%</span>
                            </div>
                            <div class="progress progress mb-4 border shadow-inset-3">
                                <div class="progress-bar bg-danger-500 progress-bar-striped" role="progressbar" style="width: {{ $redPct }}%;"></div>
                            </div>
                        </div>
                        <div class="col-sm-8 p-3">
                            <div id="subscription-chart"></div>
                        </div>
                    </div>
                </div>
                <div class="panel-content p-0">
                    <div class="row row-grid g-0">
                        {{-- TOTAL NNACV --}}
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-3">
                            <div class="p-3 d-flex align-items-center">
                                @php $nnacvPie = ($cur['total_nnacv'] ?? 0) > 0 ? min(round(($cur['total_nnacv'] / max($cur['total_nnacv'], 1)) * 100), 100) : 0; @endphp
                                <div class="js-easy-pie-chart position-relative d-inline-flex align-items-center justify-content-center" style="color: var(--primary-300);" data-percent="{{ $winRate }}" data-piesize="50" data-linewidth="5" data-linecap="butt" data-scalelength="0">
                                    <div class="d-flex flex-column align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-lg">
                                        <span class="js-percent d-block text-muted"></span>
                                    </div>
                                </div>
                                <span class="d-inline-block ms-2 text-muted">
                                    WIN RATE
                                    @if(($deltas['win_rate'] ?? 0) >= 0)
                                        <i class="fal fa-caret-up text-success ms-1"></i>
                                    @else
                                        <i class="fal fa-caret-down text-danger ms-1"></i>
                                    @endif
                                </span>
                                <div class="ms-auto d-inline-flex align-items-center">
                                    @if(($trends ?? collect())->count() > 1)
                                    <span class="peity-line d-none" data-peity='{ "fill": false, "stroke": "var(--primary-500)", "height": 30, "width": "70", "strokeWidth": 1 }'>{{ ($trends ?? collect())->pluck('nnacv')->map(fn($v) => round($v / 1000))->implode(',') }}</span>
                                    @endif
                                    <div class="d-inline-flex flex-column small ms-2">
                                        <span class="d-inline-block badge bg-success opacity-75 text-center p-1 width-6">{{ shortMoney($cur['total_nnacv'] ?? 0) }}</span>
                                        <span class="d-inline-block badge bg-fusion-300 opacity-75 text-center p-1 width-6 mt-1">{{ $cur['opp_count'] ?? 0 }} opps</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- RESOURCES --}}
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-3">
                            <div class="p-3 d-flex align-items-center">
                                @php $resPie = min(($cur['resource_count'] ?? 0) > 0 ? round(($cur['resource_count'] / max($cur['opp_count'] ?? 1, 1)) * 100) : 0, 100); @endphp
                                <div class="js-easy-pie-chart text-success position-relative d-inline-flex align-items-center justify-content-center" data-percent="{{ $resPie }}" data-piesize="50" data-linewidth="5" data-linecap="butt">
                                    <div class="d-flex flex-column align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-lg">
                                        <span class="js-percent d-block text-muted"></span>
                                    </div>
                                </div>
                                <span class="d-inline-block ms-2 text-muted">
                                    RESOURCES
                                    @if(($deltas['resources'] ?? 0) >= 0)
                                        <i class="fal fa-caret-up text-success ms-1"></i>
                                    @else
                                        <i class="fal fa-caret-down text-danger ms-1"></i>
                                    @endif
                                </span>
                                <div class="ms-auto d-inline-flex align-items-center">
                                    @if(($trends ?? collect())->count() > 1)
                                    <span class="peity-line d-none" data-peity='{ "fill": false, "stroke": "var(--success-500)", "height": 30, "width": "70", "strokeWidth": 1 }'>{{ ($trends ?? collect())->pluck('resources')->implode(',') }}</span>
                                    @endif
                                    <div class="d-inline-flex flex-column small ms-2">
                                        <span class="d-inline-block badge bg-info opacity-75 text-center p-1 width-6">{{ $cur['resource_count'] ?? 0 }}</span>
                                        <span class="d-inline-block badge bg-fusion-300 opacity-75 text-center p-1 width-6 mt-1">{{ $resPie }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- PARTNER SOURCED --}}
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-3">
                            <div class="p-3 d-flex align-items-center">
                                <div class="js-easy-pie-chart text-info position-relative d-inline-flex align-items-center justify-content-center" data-percent="{{ $sourcedPct }}" data-piesize="50" data-linewidth="5" data-linecap="butt">
                                    <div class="d-flex flex-column align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-lg">
                                        <span class="js-percent d-block text-muted"></span>
                                    </div>
                                </div>
                                <span class="d-inline-block ms-2 text-muted">
                                    SOURCED
                                    @if(($deltas['sourced_count'] ?? 0) >= 0)
                                        <i class="fal fa-caret-up text-success ms-1"></i>
                                    @else
                                        <i class="fal fa-caret-down text-danger ms-1"></i>
                                    @endif
                                </span>
                                <div class="ms-auto d-inline-flex align-items-center">
                                    @if(($trends ?? collect())->count() > 1)
                                    <span class="peity-line d-none" data-peity='{ "fill": false, "stroke": "var(--info-500)", "height": 30, "width": "70", "strokeWidth": 1 }'>{{ ($trends ?? collect())->pluck('sourced_count')->implode(',') }}</span>
                                    @endif
                                    <div class="d-inline-flex flex-column small ms-2">
                                        <span class="d-inline-block badge bg-info opacity-75 text-center p-1 width-6">{{ $cur['sourced_count'] ?? 0 }}</span>
                                        <span class="d-inline-block badge bg-fusion-300 opacity-75 text-center p-1 width-6 mt-1">{{ shortMoney($cur['sourced_nnacv'] ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- CS HEALTH --}}
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-3">
                            <div class="p-3 d-flex align-items-center">
                                @php
                                    $csTotal = array_sum($cur['cs_health'] ?? []);
                                    $greenPct = $csTotal > 0 ? round(($cur['cs_health']['green'] ?? 0) / $csTotal * 100) : 0;
                                @endphp
                                <div class="js-easy-pie-chart position-relative d-inline-flex align-items-center justify-content-center" style="color: {{ $greenPct >= 60 ? 'var(--success-500)' : ($greenPct >= 40 ? 'var(--warning-500)' : 'var(--danger-500)') }};" data-percent="{{ $greenPct }}" data-piesize="50" data-linewidth="5" data-linecap="butt">
                                    <div class="d-flex flex-column align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-lg">
                                        <span class="js-percent d-block text-muted"></span>
                                    </div>
                                </div>
                                <span class="d-inline-block ms-2 text-muted">
                                    CS HEALTH
                                </span>
                                <div class="ms-auto d-inline-flex align-items-center">
                                    <div class="d-inline-flex flex-column small ms-2">
                                        <span class="d-inline-block badge bg-success opacity-75 text-center p-1 width-6">{{ $cur['cs_health']['green'] ?? 0 }}</span>
                                        <span class="d-inline-block badge bg-warning opacity-75 text-center p-1 width-6 mt-1">{{ $cur['cs_health']['yellow'] ?? 0 }}</span>
                                        <span class="d-inline-block badge bg-danger opacity-75 text-center p-1 width-6 mt-1">{{ $cur['cs_health']['red'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <!-- Weekly Changes -->
        <div id="panel-2" class="panel panel-icon panel-collapsed">
            <div class="panel-hdr">
                <h2> Weekly <span class="fw-light"><i>Changes</i></span></h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Toggle" type="button">
                        <svg class="sa-icon">
                            <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                            <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                        </svg>
                    </button>
                    <button class="btn btn-panel" data-action="panel-fullscreen" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Fullscreen" type="button">
                        <svg class="sa-icon">
                            <use href="/icons/sprite.svg#stop-circle"></use>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="panel-container">
                <div class="panel-content py-2 px-3">
                    @if($previousSnapshot && $latestSnapshot)
                        <small class="text-muted">Comparing {{ \Carbon\Carbon::parse($latestSnapshot)->format('n/j/Y') }} vs {{ \Carbon\Carbon::parse($previousSnapshot)->format('n/j/Y') }}</small>
                    @endif
                </div>
                <div class="panel-content p-0">
                    @php
                        $pipelineNnacv = $current['total_nnacv'] ?? 0;
                        $pipelineOppCount = $current['opp_count'] ?? 0;
                        $pipelineAsp = $pipelineOppCount > 0 ? $pipelineNnacv / $pipelineOppCount : 0;
                        $prevPipelineAsp = ($previous['opp_count'] ?? 0) > 0 ? ($previous['total_nnacv'] ?? 0) / $previous['opp_count'] : 0;

                        $cwCount = $current['closed_won_count'] ?? 0;
                        $cwNnacv = $current['closed_won_nnacv'] ?? 0;
                        $cwCountDelta = $deltas['closed_won_count'] ?? 0;
                        $cwNnacvDelta = $deltas['closed_won_nnacv'] ?? 0;
                    @endphp
                    @php
                        $nnacvDelta = $deltas['nnacv'] ?? 0;
                        $nnacvPositive = $nnacvDelta > 0;
                        $nnacvNegative = $nnacvDelta < 0;
                        $cwVelocity = $current['closed_won_velocity'] ?? ['avg_days' => 0, 'min_days' => 0, 'max_days' => 0];
                        $oppDelta = $deltas['opps'] ?? 0;
                        $aspDelta = $pipelineAsp - $prevPipelineAsp;
                        $sourcedCount = $current['sourced_count'] ?? 0;
                        $sourcedNnacv = $current['sourced_nnacv'] ?? 0;
                        $sourcedCountDelta = ($current['sourced_count'] ?? 0) - ($previous['sourced_count'] ?? 0);
                        $sourcedNnacvDelta = ($current['sourced_nnacv'] ?? 0) - ($previous['sourced_nnacv'] ?? 0);
                        $sourcedPctOfPipeline = ($pipelineOppCount > 0) ? round($sourcedCount / $pipelineOppCount * 100) : 0;
                    @endphp
                    <div class="px-3 pt-3 pb-1">
                        <span class="fw-500 text-muted small text-uppercase">Business Health</span>
                    </div>
                    <div class="d-flex gap-3 px-3 pb-3">
                        {{-- Pipeline Health --}}
                        @php
                            $quota = 30000000;
                            $pipelineMultiple = $quota > 0 ? round($pipelineNnacv / $quota, 2) : 0;
                            $pipelineMultipleColor = match(true) {
                                $pipelineMultiple >= 3 => 'success',
                                $pipelineMultiple >= 2 => 'primary',
                                $pipelineMultiple >= 1 => 'warning',
                                default => 'danger',
                            };
                            $pipelineShortfall = max(0, $quota - $pipelineNnacv);
                            $shortfallOpps = $pipelineAsp > 0 ? (int) ceil($pipelineShortfall / $pipelineAsp) : 0;
                        @endphp
                        <div style="flex: 1;">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase mt-0">Pipeline <small class="text-muted fw-normal">Stages 1–4</small></h6>
                                    <div class="d-flex gap-4 flex-wrap">
                                        <div>
                                            <small class="text-muted">NNACV</small>
                                            <h2 class="text-primary mb-0">{{ shortMoney($pipelineNnacv) }}</h2>
                                            <span class="badge {{ $nnacvPositive ? 'bg-success' : ($nnacvNegative ? 'bg-danger' : 'bg-secondary') }}">{{ $nnacvPositive ? '▲' : ($nnacvNegative ? '▼' : '') }} {{ $nnacvNegative ? '-' : '+' }}{{ shortMoney(abs($nnacvDelta)) }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted">Opps</small>
                                            <h2 class="text-success mb-0">{{ number_format($pipelineOppCount) }}</h2>
                                            <span class="badge {{ $oppDelta > 0 ? 'bg-success' : ($oppDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $oppDelta > 0 ? '▲' : ($oppDelta < 0 ? '▼' : '') }} {{ ($oppDelta >= 0 ? '+' : '') . $oppDelta }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted">ASP</small>
                                            <h2 class="text-info mb-0">{{ shortMoney($pipelineAsp) }}</h2>
                                            <span class="badge {{ $aspDelta > 0 ? 'bg-success' : ($aspDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $aspDelta >= 0 ? '+' : '-' }}{{ shortMoney(abs($aspDelta)) }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted">Pipeline Multiple</small>
                                            <h2 class="text-{{ $pipelineMultipleColor }} mb-0">{{ $pipelineMultiple }}x</h2>
                                            <span class="text-muted small">vs {{ shortMoney($quota, 0) }} quota</span>
                                        </div>
                                        <div>
                                            <small class="text-muted">Shortfall</small>
                                            @if($pipelineShortfall > 0)
                                                <h2 class="text-danger mb-0">{{ shortMoney($pipelineShortfall) }}</h2>
                                                <span class="text-muted small">~{{ number_format($shortfallOpps) }} opps at ASP</span>
                                            @else
                                                <h2 class="text-success mb-0">On Track</h2>
                                                <span class="text-muted small">Pipeline exceeds quota</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-g">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase mt-0">Closed Won</h6>
                                    <div class="d-flex gap-4">
                                        <div>
                                            <small class="text-muted">NNACV</small>
                                            <h2 class="text-success mb-0">{{ shortMoney($cwNnacv) }}</h2>
                                            <span class="badge {{ $cwNnacvDelta > 0 ? 'bg-success' : ($cwNnacvDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $cwNnacvDelta >= 0 ? '+' : '-' }}{{ shortMoney(abs($cwNnacvDelta)) }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted">Deals</small>
                                            <h2 class="text-success mb-0">{{ $cwCount }}</h2>
                                            <span class="badge {{ $cwCountDelta > 0 ? 'bg-success' : ($cwCountDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $cwCountDelta > 0 ? '▲' : ($cwCountDelta < 0 ? '▼' : '') }} {{ ($cwCountDelta >= 0 ? '+' : '') . $cwCountDelta }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted">Avg Velocity</small>
                                            <h2 class="text-info mb-0">{{ $cwVelocity['avg_days'] }}d</h2>
                                            <span class="text-muted small">{{ $cwVelocity['min_days'] }}d – {{ $cwVelocity['max_days'] }}d</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Partner Sourced --}}
                        <div style="flex: 1;">
                            <div class="card mb-g">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase mt-0">Partner Sourced</h6>
                                    <div class="d-flex gap-4">
                                        <div>
                                            <small class="text-muted">Opps</small>
                                            <h2 class="text-success mb-0">{{ number_format($sourcedCount) }}</h2>
                                            <span class="badge {{ $sourcedCountDelta > 0 ? 'bg-success' : ($sourcedCountDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $sourcedCountDelta > 0 ? '▲' : ($sourcedCountDelta < 0 ? '▼' : '') }} {{ ($sourcedCountDelta >= 0 ? '+' : '') . $sourcedCountDelta }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted">NNACV</small>
                                            <h2 class="text-success mb-0">{{ shortMoney($sourcedNnacv) }}</h2>
                                            <span class="badge {{ $sourcedNnacvDelta > 0 ? 'bg-success' : ($sourcedNnacvDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $sourcedNnacvDelta >= 0 ? '+' : '-' }}{{ shortMoney(abs($sourcedNnacvDelta)) }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted">% of Pipeline</small>
                                            <h2 class="text-info mb-0">{{ $sourcedPctOfPipeline }}%</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- LOB Breakdown Charts --}}
                    <div class="row px-3 pb-3">
                        <div class="col-6">
                            <div class="card mb-g">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase mt-0">Pipeline by LOB</h6>
                                    <div id="pipeline-lob-chart" style="min-height: 200px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card mb-g">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase mt-0">Closed Won by LOB</h6>
                                    <div id="closed-won-lob-chart" style="min-height: 200px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Portfolio Metrics --}}
                    @php
                        $csHealth = $current['cs_health'] ?? ['green' => 0, 'yellow' => 0, 'red' => 0];
                        $prevCsHealth = $previous['cs_health'] ?? ['green' => 0, 'yellow' => 0, 'red' => 0];
                        $csTotal = $csHealth['green'] + $csHealth['yellow'] + $csHealth['red'];
                        $prevCsTotal = $prevCsHealth['green'] + $prevCsHealth['yellow'] + $prevCsHealth['red'];

                        $gDelta = $csHealth['green'] - $prevCsHealth['green'];
                        $yDelta = $csHealth['yellow'] - $prevCsHealth['yellow'];
                        $rDelta = $csHealth['red'] - $prevCsHealth['red'];

                        $sourcedAttainmentPct = $quota > 0 ? round(($cwNnacv / $quota) * 100, 1) : 0;
                        $prevCwNnacv = $previous['closed_won_nnacv'] ?? 0;
                        $prevSourcedAttainmentPct = $quota > 0 ? round(($prevCwNnacv / $quota) * 100, 1) : 0;
                        $sourcedAttainmentDelta = round($sourcedAttainmentPct - $prevSourcedAttainmentPct, 1);

                        $deliveryCards = [
                            [
                                'label' => 'Sourced Attainment',
                                'value' => $sourcedAttainmentPct,
                                'delta' => $sourcedAttainmentDelta,
                                'format' => 'pct',
                                'color' => $sourcedAttainmentPct >= 80 ? 'success' : ($sourcedAttainmentPct >= 50 ? 'warning' : 'danger'),
                                'type' => 'sourced_attainment',
                            ],
                            [
                                'label' => 'Resources',
                                'type' => 'resources_breakdown',
                                'sales' => $current['resources_by_role']['sales'] ?? 0,
                                'pre_sales' => $current['resources_by_role']['pre_sales'] ?? 0,
                                'delivery' => $current['resources_by_role']['delivery'] ?? 0,
                                'prev_sales' => $previous['resources_by_role']['sales'] ?? 0,
                                'prev_pre_sales' => $previous['resources_by_role']['pre_sales'] ?? 0,
                                'prev_delivery' => $previous['resources_by_role']['delivery'] ?? 0,
                            ],
                            [
                                'label' => 'App vs Platform',
                                'type' => 'app_vs_platform',
                                'app_count' => $current['app_count'] ?? 0,
                                'app_nnacv' => $current['app_nnacv'] ?? 0,
                                'app_count_delta' => ($current['app_count'] ?? 0) - ($previous['app_count'] ?? 0),
                                'app_nnacv_delta' => ($current['app_nnacv'] ?? 0) - ($previous['app_nnacv'] ?? 0),
                                'platform_count' => $current['platform_count'] ?? 0,
                                'platform_nnacv' => $current['platform_nnacv'] ?? 0,
                                'platform_count_delta' => ($current['platform_count'] ?? 0) - ($previous['platform_count'] ?? 0),
                                'platform_nnacv_delta' => ($current['platform_nnacv'] ?? 0) - ($previous['platform_nnacv'] ?? 0),
                            ],
                        ];

                    @endphp
                    {{-- Delivery Readiness --}}
                    <div class="px-3 pb-1">
                        <span class="fw-500 text-muted small text-uppercase">Delivery Readiness</span>
                    </div>
                    <div class="row sortable-off px-3 pb-3">
                        @foreach($deliveryCards as $dCard)
                            @if(($dCard['type'] ?? 'standard') === 'resources_breakdown')
                                @php
                                    $sDelta = $dCard['sales'] - $dCard['prev_sales'];
                                    $psDelta = $dCard['pre_sales'] - $dCard['prev_pre_sales'];
                                    $dlDelta = $dCard['delivery'] - $dCard['prev_delivery'];
                                @endphp
                                <div class="col-6 col-xl-3">
                                    <div class="card mb-g">
                                        <div class="card-body">
                                            <h6 class="text-muted text-uppercase mt-0">{{ $dCard['label'] }}</h6>
                                            <div class="d-flex align-items-end gap-4">
                                                <div class="text-center">
                                                    <h2 class="text-warning mb-0">{{ $dCard['sales'] }}</h2>
                                                    <small class="text-muted">Sales</small><br>
                                                    <span class="badge {{ $sDelta > 0 ? 'bg-success' : ($sDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $sDelta > 0 ? '▲' : ($sDelta < 0 ? '▼' : '') }} {{ ($sDelta >= 0 ? '+' : '') . $sDelta }}</span>
                                                </div>
                                                <div class="text-center">
                                                    <h2 class="text-info mb-0">{{ $dCard['pre_sales'] }}</h2>
                                                    <small class="text-muted">Pre-Sales</small><br>
                                                    <span class="badge {{ $psDelta > 0 ? 'bg-success' : ($psDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $psDelta > 0 ? '▲' : ($psDelta < 0 ? '▼' : '') }} {{ ($psDelta >= 0 ? '+' : '') . $psDelta }}</span>
                                                </div>
                                                <div class="text-center">
                                                    <h2 class="text-primary mb-0">{{ $dCard['delivery'] }}</h2>
                                                    <small class="text-muted">Delivery</small><br>
                                                    <span class="badge {{ $dlDelta > 0 ? 'bg-success' : ($dlDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $dlDelta > 0 ? '▲' : ($dlDelta < 0 ? '▼' : '') }} {{ ($dlDelta >= 0 ? '+' : '') . $dlDelta }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif(($dCard['type'] ?? 'standard') === 'app_vs_platform')
                                @php
                                    $acDelta = $dCard['app_count_delta'];
                                    $anDelta = $dCard['app_nnacv_delta'];
                                    $pcDelta = $dCard['platform_count_delta'];
                                    $pnDelta = $dCard['platform_nnacv_delta'];
                                @endphp
                                <div class="col-6 col-xl-3">
                                    <div class="card mb-g">
                                        <div class="card-body">
                                            <h6 class="text-muted text-uppercase mt-0">{{ $dCard['label'] }}</h6>
                                            <div class="d-flex gap-4">
                                                <div>
                                                    <small class="text-muted">Applications</small>
                                                    <h2 class="text-info mb-0">{{ number_format($dCard['app_count']) }} <small class="badge {{ $acDelta > 0 ? 'bg-success' : ($acDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $acDelta > 0 ? '▲' : ($acDelta < 0 ? '▼' : '') }} {{ ($acDelta >= 0 ? '+' : '') . $acDelta }}</small></h2>
                                                    <span class="text-success">{{ shortMoney($dCard['app_nnacv']) }}</span>
                                                    <span class="badge {{ $anDelta > 0 ? 'bg-success' : ($anDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $anDelta >= 0 ? '+' : '-' }}{{ shortMoney(abs($anDelta)) }}</span>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Platform</small>
                                                    <h2 class="text-warning mb-0">{{ number_format($dCard['platform_count']) }} <small class="badge {{ $pcDelta > 0 ? 'bg-success' : ($pcDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $pcDelta > 0 ? '▲' : ($pcDelta < 0 ? '▼' : '') }} {{ ($pcDelta >= 0 ? '+' : '') . $pcDelta }}</small></h2>
                                                    <span class="text-success">{{ shortMoney($dCard['platform_nnacv']) }}</span>
                                                    <span class="badge {{ $pnDelta > 0 ? 'bg-success' : ($pnDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $pnDelta >= 0 ? '+' : '-' }}{{ shortMoney(abs($pnDelta)) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif(($dCard['type'] ?? 'standard') === 'sourced_attainment')
                                <div class="col-6 col-xl-3">
                                    <div class="card mb-g">
                                        <div class="card-body">
                                            <h6 class="text-muted text-uppercase mt-0">{{ $dCard['label'] }}</h6>
                                            <h2 class="text-{{ $dCard['color'] }} mb-1">{{ $dCard['value'] }}%</h2>
                                            <div class="progress mb-2" style="height: 8px;">
                                                <div class="progress-bar bg-{{ $dCard['color'] }}" style="width: {{ min($dCard['value'], 100) }}%;"></div>
                                            </div>
                                            <div class="d-flex justify-content-between small text-muted">
                                                <span>{{ shortMoney($cwNnacv) }} YTD</span>
                                                <span>{{ shortMoney($quota, 0) }} quota</span>
                                            </div>
                                            @if($dCard['delta'] != 0)
                                                <span class="badge {{ $dCard['delta'] > 0 ? 'bg-success' : 'bg-danger' }} mt-1">{{ $dCard['delta'] > 0 ? '▲' : '▼' }} {{ ($dCard['delta'] >= 0 ? '+' : '') . $dCard['delta'] }}pp</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                @php
                                    $dDelta = $dCard['delta'];
                                    $dPositive = $dDelta > 0;
                                    $dNegative = $dDelta < 0;
                                    $dBadgeBg = $dPositive ? 'bg-success' : ($dNegative ? 'bg-danger' : 'bg-secondary');
                                    $dDeltaIcon = $dPositive ? '▲' : ($dNegative ? '▼' : '');

                                    if ($dCard['format'] === 'currency') {
                                        $dMainDisplay = shortMoney($dCard['value']);
                                        $dDeltaDisplay = ($dNegative ? '-' : '+') . shortMoney(abs($dDelta));
                                    } elseif ($dCard['format'] === 'pct') {
                                        $dMainDisplay = $dCard['value'] . '%';
                                        $dDeltaDisplay = ($dNegative ? '' : '+') . round($dDelta) . 'pp';
                                    } else {
                                        $dMainDisplay = number_format($dCard['value']);
                                        $dDeltaDisplay = ($dNegative ? '' : '+') . number_format($dDelta);
                                    }
                                @endphp
                                <div class="col-6 col-xl-3">
                                    <div class="card mb-g">
                                        <div class="card-body">
                                            <h6 class="text-muted text-uppercase mt-0">{{ $dCard['label'] }}</h6>
                                            <h2 class="text-{{ $dCard['color'] }}">{{ $dMainDisplay }}</h2>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge {{ $dBadgeBg }}">{{ $dDeltaIcon }} {{ $dDeltaDisplay }}</span>
                                                <span class="text-muted">vs prior week</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        {{-- CS Health Score --}}
                        <div class="col-6 col-xl-3">
                            <div class="card mb-g">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase mt-0">CS Health Score</h6>
                                    <div class="d-flex align-items-end gap-4">
                                        <div class="text-center">
                                            <h2 class="text-success mb-0">{{ $csHealth['green'] }}</h2>
                                            <small class="text-muted">Green</small><br>
                                            <span class="badge {{ $gDelta > 0 ? 'bg-success' : ($gDelta < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $gDelta > 0 ? '▲' : ($gDelta < 0 ? '▼' : '') }} {{ ($gDelta >= 0 ? '+' : '') . $gDelta }}</span>
                                        </div>
                                        <div class="text-center">
                                            <h2 class="text-warning mb-0">{{ $csHealth['yellow'] }}</h2>
                                            <small class="text-muted">Yellow</small><br>
                                            <span class="badge {{ $yDelta > 0 ? 'bg-danger' : ($yDelta < 0 ? 'bg-success' : 'bg-secondary') }}">{{ $yDelta > 0 ? '▲' : ($yDelta < 0 ? '▼' : '') }} {{ ($yDelta >= 0 ? '+' : '') . $yDelta }}</span>
                                        </div>
                                        <div class="text-center">
                                            <h2 class="text-danger mb-0">{{ $csHealth['red'] }}</h2>
                                            <small class="text-muted">Red</small><br>
                                            <span class="badge {{ $rDelta > 0 ? 'bg-danger' : ($rDelta < 0 ? 'bg-success' : 'bg-secondary') }}">{{ $rDelta > 0 ? '▲' : ($rDelta < 0 ? '▼' : '') }} {{ ($rDelta >= 0 ? '+' : '') . $rDelta }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <!-- Potential Risks -->
        <div id="panel-4" class="panel panel-icon panel-collapsed">
            <div class="panel-hdr">
                <h2> Potential <span class="fw-light"><i>Risks</i></span></h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Toggle" type="button">
                        <svg class="sa-icon">
                            <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                            <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                        </svg>
                    </button>
                    <button class="btn btn-panel" data-action="panel-fullscreen" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Fullscreen" type="button">
                        <svg class="sa-icon">
                            <use href="/icons/sprite.svg#stop-circle"></use>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="panel-container">
                <div class="panel-content p-0">
                    {{-- Sales Velocity --}}
                    <div class="px-3 pt-3 pb-1">
                        <span class="fw-500 text-muted small text-uppercase">Sales Velocity</span>
                    </div>
                    <div class="d-flex flex-nowrap overflow-auto gap-2 px-3" id="stage-cards">
                        @foreach(($aging ?? collect()) as $stage => $data)
                            @php
                                $stageSlug = Str::slug($stage);
                                $isClosedWon = str_starts_with($stage, '5.');
                                $agingColor = $isClosedWon ? 'success' : match(true) {
                                    $data['avg_days'] > 90 => 'danger',
                                    $data['avg_days'] > 60 => 'warning',
                                    $data['avg_days'] > 30 => 'info',
                                    default => 'success',
                                };
                            @endphp
                            <div class="card mb-2 border-top border-{{ $agingColor }} js-detail-card" data-panel="stage-panel-{{ $stageSlug }}" role="button" style="min-width: 160px; flex: 1 0 0; cursor: pointer; transition: box-shadow .15s;">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <h6 class="text-{{ $agingColor }} fw-500 mb-1 small">{{ $stage }}</h6>
                                        <span class="small text-muted"><i class="fal fa-chevron-down js-chevron" style="transition: transform .2s;"></i></span>
                                    </div>
                                    <span class="text-muted small">{{ $data['count'] }} {{ Str::plural('opportunity', $data['count']) }}</span>
                                    <div class="d-flex align-items-end gap-3 mt-2">
                                        @if($isClosedWon)
                                        <div>
                                            <h3 class="text-{{ $agingColor }} mb-0 fw-700">{{ shortMoney($data['total_nnacv'] ?? 0) }}</h3>
                                            <small class="text-muted">NNACV</small>
                                        </div>
                                        @else
                                        <div>
                                            <h3 class="text-{{ $agingColor }} mb-0 fw-700">{{ round($data['avg_days']) }}d</h3>
                                            <small class="text-muted">avg in stage</small>
                                        </div>
                                        <div>
                                            <span class="text-muted fs-lg">{{ $data['max_days'] }}d</span><br>
                                            <small class="text-muted">max</small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{-- Shared detail panels for Sales Velocity --}}
                    <div class="px-3 pb-3">
                        @foreach(($aging ?? collect()) as $stage => $data)
                            @php $stageSlug = Str::slug($stage); @endphp
                            <div class="card border shadow-0 mb-0 js-detail-panel" id="stage-panel-{{ $stageSlug }}" style="display: none;">
                                <div class="card-header py-2 px-3 bg-light d-flex align-items-center justify-content-between">
                                    <span class="fw-500 small">{{ $stage }} — {{ $data['count'] }} {{ Str::plural('opportunity', $data['count']) }}</span>
                                    <button class="btn btn-sm btn-link text-muted p-0 js-panel-close"><i class="fal fa-times"></i></button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                        <table class="table table-sm table-hover mb-0 small">
                                            <thead class="sticky-top bg-white">
                                                <tr class="text-muted">
                                                    <th class="ps-3">Opportunity</th>
                                                    <th>Partner</th>
                                                    <th class="text-end">NNACV</th>
                                                    <th>LOB</th>
                                                    <th>Quarter</th>
                                                    <th class="text-end pe-3">Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['opportunities'] ?? [] as $opp)
                                                    <tr>
                                                        <td class="ps-3 fw-500">{{ $opp['name'] }}</td>
                                                        <td>{{ $opp['partner'] }}</td>
                                                        <td class="text-end">{{ shortMoney($opp['amount']) }}</td>
                                                        <td>{{ $opp['product_area'] ?? '—' }}</td>
                                                        <td>{{ $opp['fiscal_quarter'] ?? '—' }}</td>
                                                        <td class="text-end pe-3"><span class="badge bg-{{ ($opp['revenue_type'] ?? '') === 'sourced' ? 'primary' : 'info' }} bg-opacity-25 text-{{ ($opp['revenue_type'] ?? '') === 'sourced' ? 'primary' : 'info' }}">{{ ucfirst($opp['revenue_type'] ?? '—') }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Delivery Risks --}}
                    <div class="px-3 pb-1">
                        <span class="fw-500 text-muted small text-uppercase">Delivery Risks by LOB</span>
                    </div>
                    <div class="d-flex flex-nowrap overflow-auto gap-2 px-3" id="lob-cards">
                        @foreach(($deliveryRisk ?? collect()) as $lob => $dr)
                            @php
                                $lobSlug = Str::slug($lob);
                                $ratio = $dr['opps'] > 0 ? round($dr['resources'] / $dr['opps'], 2) : 0;
                                $isStaffed = $ratio >= 1;
                                $severity = match(true) {
                                    $ratio == 0 => ['label' => 'CRITICAL', 'color' => 'danger'],
                                    $ratio < 0.25 => ['label' => 'HIGH', 'color' => 'danger'],
                                    $ratio < 0.5 => ['label' => 'MEDIUM', 'color' => 'warning'],
                                    $ratio < 1 => ['label' => 'MEDIUM', 'color' => 'warning'],
                                    default => ['label' => 'LOW', 'color' => 'success'],
                                };
                            @endphp
                            <div class="card mb-2 border-start border-{{ $severity['color'] }} border-3 js-detail-card" data-panel="lob-panel-{{ $lobSlug }}" role="button" style="min-width: 200px; flex: 1 0 0; cursor: pointer; transition: box-shadow .15s;">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <h6 class="text-{{ $severity['color'] }} fw-700 mb-1">{{ $lob }}</h6>
                                        <span class="small text-muted"><i class="fal fa-chevron-down js-chevron" style="transition: transform .2s;"></i></span>
                                    </div>
                                    <span class="text-muted small">{{ $dr['opps'] }} {{ Str::plural('opportunity', $dr['opps']) }} {!! '&bull;' !!} {{ $dr['resources'] }} resources</span>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <h2 class="text-{{ $severity['color'] }} mb-0 fw-700">{{ number_format($ratio, 2) }}</h2>
                                        <span class="badge bg-{{ $severity['color'] }}">{{ $severity['label'] }}</span>
                                    </div>
                                    <span class="text-muted small">Resource:Opp Ratio</span><br>
                                    <span class="small">{{ $dr['resources'] }}:{{ $dr['opps'] }} ({!! $isStaffed ? '&#10003; Staffed' : '&#9888; Understaffed' !!})</span>
                                    @if($dr['nnacv_at_risk'] > 0)
                                        <div class="mt-2">
                                            <span class="text-{{ $severity['color'] }} fw-500">{{ shortMoney($dr['nnacv_at_risk']) }} at risk</span>
                                        </div>
                                    @endif
                                    <div class="mt-2 pt-2 border-top">
                                        <button type="button" class="btn btn-xs btn-outline-primary js-take-action"
                                            onclick="event.stopPropagation();"
                                            data-lob="{{ $lob }}"
                                            data-opps="{{ $dr['opps'] }}"
                                            data-resources="{{ $dr['resources'] }}"
                                            data-nnacv="{{ $dr['nnacv_at_risk'] }}"
                                            data-severity="{{ $severity['label'] }}"
                                            data-staffed="{{ $isStaffed ? '1' : '0' }}">
                                            <i class="fal fa-crosshairs me-1"></i> Take Action
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{-- Shared detail panels for Delivery Risks --}}
                    <div class="px-3 pb-3">
                        @foreach(($deliveryRisk ?? collect()) as $lob => $dr)
                            @php $lobSlug = Str::slug($lob); @endphp
                            <div class="card border shadow-0 mb-0 js-detail-panel" id="lob-panel-{{ $lobSlug }}" style="display: none;">
                                <div class="card-header py-2 px-3 bg-light d-flex align-items-center justify-content-between">
                                    <span class="fw-500 small">{{ $lob }} — {{ $dr['opps'] }} {{ Str::plural('opportunity', $dr['opps']) }}</span>
                                    <button class="btn btn-sm btn-link text-muted p-0 js-panel-close"><i class="fal fa-times"></i></button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                        <table class="table table-sm table-hover mb-0 small">
                                            <thead class="sticky-top bg-white">
                                                <tr class="text-muted">
                                                    <th class="ps-3">Opportunity</th>
                                                    <th>Partner</th>
                                                    <th class="text-end">NNACV</th>
                                                    <th>Stage</th>
                                                    <th class="text-end">Resources</th>
                                                    <th class="text-end pe-3">Risk</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dr['opportunities'] ?? [] as $opp)
                                                    @php
                                                        $riskColor = match($opp['risk_level']) {
                                                            'Very High Risk' => 'danger',
                                                            'High Risk' => 'warning',
                                                            default => 'success',
                                                        };
                                                    @endphp
                                                    <tr>
                                                        <td class="ps-3 fw-500">{{ $opp['name'] }}</td>
                                                        <td>{{ $opp['partner'] }}</td>
                                                        <td class="text-end">{{ shortMoney($opp['amount']) }}</td>
                                                        <td>{{ $opp['stage'] }}</td>
                                                        <td class="text-end">{{ $opp['resource_count'] }}</td>
                                                        <td class="text-end pe-3"><span class="badge bg-{{ $riskColor }} bg-opacity-25 text-{{ $riskColor }}">{{ $opp['risk_level'] }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Recommendations --}}
                    <div class="px-3 pb-1">
                        <span class="fw-500 text-muted small text-uppercase">Recommendations</span>
                    </div>
                    <div class="px-3 pb-3">
                        @foreach(($lobRecs ?? collect()) as $rec)
                            @php
                                $recColor = match($rec['severity']) {
                                    'critical' => 'danger',
                                    'warning' => 'warning',
                                    default => 'success',
                                };
                                $recLabel = match($rec['severity']) {
                                    'critical' => 'CRITICAL',
                                    'warning' => 'HIGH',
                                    default => 'CROSS-TRAIN',
                                };
                                $recIcon = match($rec['type']) {
                                    'staffing_gap' => 'alert-triangle',
                                    'cross_train' => 'refresh-cw',
                                    default => 'info',
                                };
                            @endphp
                            @php $collapseId = 'rec-partners-' . $rec['rank']; @endphp
                            <div class="card shadow-0 mb-2 border-start border-{{ $recColor }} border-3">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex align-items-start">
                                        <svg class="sa-icon text-{{ $recColor }} me-2 flex-shrink-0 mt-1" style="width:20px;height:20px"><use href="/icons/sprite.svg#{{ $recIcon }}"></use></svg>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="fw-700 text-{{ $recColor }}">{{ $rec['rank'] }}. {{ $recLabel }}</span>
                                                <span class="fw-700">{{ $rec['title'] }}</span>
                                                @if(count($rec['partners'] ?? []) > 0)
                                                    <a class="ms-auto small text-muted" data-bs-toggle="collapse" href="#{{ $collapseId }}" role="button" aria-expanded="false">
                                                        <i class="fal fa-chevron-down"></i> {{ count($rec['partners']) }} partners
                                                    </a>
                                                @endif
                                            </div>
                                            <p class="mb-0 small"><span class="fw-500">Why:</span> {{ $rec['why'] }}</p>
                                            <p class="mb-0 small"><span class="fw-500">Impact:</span> {{ $rec['impact'] }}</p>
                                            <p class="mb-0 small text-{{ $recColor }}"><span class="fw-500">Action:</span> {{ $rec['action'] }}</p>
                                            @if(!$selectedPartnerId && count($rec['partners'] ?? []) > 0)
                                                @php
                                                    $topThree = collect($rec['partners'])->sortByDesc('nnacv')->take(3);
                                                @endphp
                                                <p class="mb-0 small"><span class="fw-500">Prioritize:</span> {{ $topThree->pluck('partner_name')->implode(', ') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if(count($rec['partners'] ?? []) > 0)
                                    <div class="collapse" id="{{ $collapseId }}">
                                        <div class="card-body p-0 border-top">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0 small">
                                                    <thead>
                                                        <tr class="text-muted">
                                                            <th class="ps-3">Partner</th>
                                                            <th class="text-end">Opps</th>
                                                            <th class="text-end">NNACV</th>
                                                            <th class="text-end">Resources</th>
                                                            <th class="text-end pe-3">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($rec['partners'] as $p)
                                                            @php
                                                                $pStatus = match(true) {
                                                                    $p['resource_count'] === 0 => ['label' => 'No Resources', 'color' => 'danger'],
                                                                    $p['resource_count'] < $p['opp_count'] => ['label' => 'Understaffed', 'color' => 'warning'],
                                                                    default => ['label' => 'On Track', 'color' => 'success'],
                                                                };
                                                            @endphp
                                                            <tr>
                                                                <td class="ps-3 fw-500">{{ $p['partner_name'] }}</td>
                                                                <td class="text-end">{{ $p['opp_count'] }}</td>
                                                                <td class="text-end">{{ shortMoney($p['nnacv']) }}</td>
                                                                <td class="text-end">{{ $p['resource_count'] }}</td>
                                                                <td class="text-end pe-3"><span class="badge bg-{{ $pStatus['color'] }} bg-opacity-25 text-{{ $pStatus['color'] }}">{{ $pStatus['label'] }}</span></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Priorities Section — always visible --}}
    <div class="col-xl-12">
        <div id="panel-priorities" class="panel panel-icon panel-collapsed">
            <div class="panel-hdr">
                <h2><i class="fal fa-bullseye-arrow me-2"></i> Key Objectives</h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel waves-effect waves-themed" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Toggle">
                        <svg class="sa-icon"><use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use><use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use></svg>
                    </button>
                    <button class="btn btn-panel" data-action="panel-fullscreen" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Fullscreen" type="button">
                        <svg class="sa-icon">
                            <use href="/icons/sprite.svg#stop-circle"></use>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    @if(($sobObjectives ?? collect())->count() === 0 && ($sobActionItems ?? collect())->count() === 0 && ($teamObjectives ?? collect())->count() === 0)
                        <div class="text-center py-4 text-muted">
                            <i class="fal fa-bullseye-arrow fa-2x mb-2 d-block opacity-50"></i>
                            <p class="mb-1">No objectives or action items yet.</p>
                            <p class="small">Use the <strong>Take Action</strong> buttons on delivery risk cards above to create objectives.</p>
                        </div>
                    @else
                    <div class="row">
                        {{-- Objectives --}}
                        <div class="col-12 mb-3">
                            @php
                                $allObjs = $sobObjectives ?? collect();
                                $objsByCategory = $allObjs->groupBy('category');
                            @endphp
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-3" id="objective-filters">
                                <small class="text-muted fw-700 text-uppercase me-1">Filter:</small>
                                <button type="button" class="btn btn-xs btn-outline-secondary obj-filter-btn active" data-filter="all">
                                    All <span class="badge bg-secondary bg-opacity-25 text-secondary ms-1">{{ $allObjs->count() }}</span>
                                </button>
                                <span class="text-muted opacity-50">|</span>
                                @foreach($objsByCategory as $cat => $group)
                                    <button type="button" class="btn btn-xs btn-outline-{{ \App\Models\PsmObjective::CATEGORY_COLORS[$cat] ?? 'secondary' }} obj-filter-btn" data-filter="category" data-value="{{ $cat }}">
                                        {{ ucfirst($cat) }} <span class="badge bg-{{ \App\Models\PsmObjective::CATEGORY_COLORS[$cat] ?? 'secondary' }} bg-opacity-25 ms-1">{{ $group->count() }}</span>
                                    </button>
                                @endforeach
                                <span class="ms-auto"></span>
                                <button type="button" class="btn btn-xs btn-outline-primary" id="priorities-new-objective-btn">
                                    <i class="fal fa-plus me-1"></i> New Objective
                                </button>
                            </div>

                            <div id="objectives-grouped-list">
                            @php
                                $rawGrouped = $allObjs->groupBy(fn ($o) => $o->priority > 0 && $o->priority <= 3 ? $o->priority : 0);
                                $groupedObjs = collect([1 => collect(), 2 => collect(), 3 => collect(), 0 => collect()])
                                    ->map(fn ($default, $key) => $rawGrouped->get($key, $default));
                            @endphp
                            @foreach($groupedObjs as $tier => $tierObjs)
                                <div class="objective-priority-group mb-3" data-priority-tier="{{ $tier }}">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        @if($tier > 0)
                                            @php $tierColor = PsmObjective::PRIORITY_COLORS[$tier] ?? 'secondary'; @endphp
                                            <span class="badge bg-{{ $tierColor }} text-white px-2">{{ PsmObjective::PRIORITIES[$tier] ?? 'Unknown' }}</span>
                                            <small class="text-muted fw-500">{{ $tierObjs->count() }} {{ Str::plural('objective', $tierObjs->count()) }}</small>
                                        @else
                                            <span class="badge bg-secondary text-white px-2">Unprioritized</span>
                                            <small class="text-muted fw-500">{{ $tierObjs->count() }} {{ Str::plural('objective', $tierObjs->count()) }}</small>
                                        @endif
                                    </div>
                                    <div class="objective-drop-zone" data-priority="{{ $tier }}" style="min-height: 20px;">
                                    @foreach($tierObjs as $obj)
                                        @php
                                            $progress = $obj->progressPercentage();
                                            $progressColor = match(true) {
                                                $progress >= 75 => 'success',
                                                $progress >= 40 => 'primary',
                                                $progress >= 15 => 'warning',
                                                default => 'danger',
                                            };
                                            $isOverdue = $obj->isOverdue();
                                            $categoryColor = PsmObjective::CATEGORY_COLORS[$obj->category] ?? 'secondary';
                                        @endphp
                                        <div class="card border shadow-0 mb-2 objective-item" data-objective-id="{{ $obj->id }}" data-category="{{ $obj->category }}" data-priority="{{ $obj->priority }}">
                                            <div class="card-body py-2 px-3">
                                                {{-- Row 1: drag handle, title line, actions --}}
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <div class="d-flex align-items-center flex-grow-1 me-2">
                                                        <div class="me-2 objective-drag-handle" style="cursor: grab; opacity: 0.4;" title="Drag to reorder or change priority">
                                                            <i class="fal fa-grip-vertical"></i>
                                                        </div>
                                                        <span class="badge bg-{{ $categoryColor }} text-white me-2">{{ ucfirst($obj->category) }}</span>
                                                        <span class="fw-500">{{ $obj->title }}</span>
                                                        @if($obj->partner)
                                                            <span class="text-muted small ms-1">· {{ $obj->partner->name }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                        <small class="text-muted me-2">Due {{ $obj->target_date->format('M j') }}</small>
                                                        @if($isOverdue)
                                                            <span class="badge bg-danger bg-opacity-15 text-danger me-1">Overdue</span>
                                                        @endif
                                                        <form method="POST" action="{{ route('priorities.objectives.update', $obj) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" class="btn btn-xs btn-outline-success" title="Mark completed"><i class="fal fa-check me-1"></i>Done</button>
                                                        </form>
                                                        <button type="button" class="btn btn-xs btn-outline-primary edit-objective-btn" title="Edit"
                                                            data-obj-id="{{ $obj->id }}"
                                                            data-obj-title="{{ $obj->title }}"
                                                            data-obj-description="{{ $obj->description }}"
                                                            data-obj-category="{{ $obj->category }}"
                                                            data-obj-metric-type="{{ $obj->metric_type }}"
                                                            data-obj-target-value="{{ (int) $obj->target_value }}"
                                                            data-obj-target-date="{{ $obj->target_date->format('Y-m-d') }}"
                                                            data-obj-partner-id="{{ $obj->partner_id }}"
                                                            data-obj-status="{{ $obj->status }}"
                                                            data-obj-priority="{{ $obj->priority }}">
                                                            <i class="fal fa-pencil"></i>
                                                        </button>
                                                        <form method="POST" action="{{ route('priorities.objectives.destroy', $obj) }}" class="d-inline delete-objective-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete"><i class="fal fa-trash"></i></button>
                                                        </form>
                                                    </div>
                                                </div>
                                                {{-- Row 2: description — kept clear of the right-side buttons --}}
                                                @if($obj->description)
                                                    <p class="mb-1 small text-muted ps-4" style="max-width: calc(100% - 280px);">{{ $obj->description }}</p>
                                                @endif
                                                {{-- Row 3: progress bar + inline update --}}
                                                <div class="d-flex align-items-center gap-2 ps-4" style="max-width: calc(100% - 280px);">
                                                    <div class="progress flex-grow-1" style="height: 6px; max-width: 300px;">
                                                        <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ max(0, $progress) }}%"></div>
                                                    </div>
                                                    <span class="small fw-500 text-{{ $progressColor }}">{{ $progress }}%</span>
                                                    <span class="small text-muted">{{ (int) $obj->current_value }}/{{ (int) $obj->target_value }} {{ PsmObjective::METRIC_TYPES[$obj->metric_type] ?? '' }}</span>
                                                    <form method="POST" action="{{ route('priorities.objectives.update', $obj) }}" class="d-inline-flex align-items-center gap-1 ms-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="number" name="current_value" class="form-control form-control-sm" style="width: 70px; height: 24px; font-size: 11px;" value="{{ (int) $obj->current_value }}" min="0" step="1">
                                                        <button type="submit" class="btn btn-xs btn-outline-primary" style="height: 24px; padding: 0 6px;" title="Update progress"><i class="fal fa-save" style="font-size: 10px;"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        </div>

                        {{-- Action items moved to separate panel below --}}
                    </div>

                    {{-- Manager View: Direct Reports' Objectives --}}
                    @if(($teamObjectives ?? collect())->count() > 0)
                        <hr class="mt-3 mb-3">
                        <h6 class="fw-700 text-uppercase fs-xs text-muted mb-3">
                            <i class="fal fa-users me-1"></i> Team Objectives
                            <span class="badge bg-info bg-opacity-15 text-info ms-1">{{ $teamObjectives->count() }}</span>
                        </h6>
                        @foreach($teamObjectives->groupBy(fn ($o) => $o->user->name) as $memberName => $memberObjs)
                            <div class="mb-3">
                                <span class="fw-500 small text-muted"><i class="fal fa-user me-1"></i> {{ $memberName }}</span>
                                @foreach($memberObjs as $obj)
                                    @php
                                        $progress = $obj->progressPercentage();
                                        $progressColor = match(true) {
                                            $obj->status === 'completed' => 'success',
                                            $obj->status === 'cancelled' => 'secondary',
                                            $obj->status === 'missed' => 'danger',
                                            $progress >= 75 => 'success',
                                            $progress >= 40 => 'primary',
                                            $progress >= 15 => 'warning',
                                            default => 'danger',
                                        };
                                        $categoryColor = PsmObjective::CATEGORY_COLORS[$obj->category] ?? 'secondary';
                                        $statusColor = PsmObjective::STATUS_COLORS[$obj->status] ?? 'secondary';
                                    @endphp
                                    <div class="card border shadow-0 mb-2 {{ $obj->status !== 'active' ? 'opacity-75' : '' }}">
                                        <div class="card-body py-2 px-3">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div class="flex-grow-1 me-3">
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <span class="badge bg-{{ $statusColor }} text-white">{{ ucfirst($obj->status) }}</span>
                                                        <span class="badge bg-{{ $categoryColor }} text-white">{{ ucfirst($obj->category) }}</span>
                                                        <span class="fw-500">{{ $obj->title }}</span>
                                                        @if($obj->partner)
                                                            <span class="text-muted small">· {{ $obj->partner->name }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height: 6px; max-width: 200px;">
                                                            <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $progress }}%"></div>
                                                        </div>
                                                        <span class="small fw-500 text-{{ $progressColor }}">{{ $progress }}%</span>
                                                        <span class="small text-muted">{{ (int) $obj->current_value }}/{{ (int) $obj->target_value }} {{ PsmObjective::METRIC_TYPES[$obj->metric_type] ?? '' }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-end flex-shrink-0">
                                                    <small class="text-muted d-block">Created {{ $obj->created_at->format('M j') }}</small>
                                                    <small class="text-muted d-block">Due {{ $obj->target_date->format('M j') }}</small>
                                                    @if($obj->isOverdue())
                                                        <span class="badge bg-danger bg-opacity-15 text-danger">Overdue</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Action Items — separate panel --}}
    @if(($sobActionItems ?? collect())->count() > 0)
    <div class="col-xl-12">
        <div id="panel-action-items" class="panel panel-icon panel-collapsed">
            <div class="panel-hdr">
                <h2><i class="fal fa-tasks me-2"></i> Action Items <span class="fw-light"><i>({{ $sobActionItems->count() }})</i></span></h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel waves-effect waves-themed" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Toggle">
                        <svg class="sa-icon"><use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use><use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use></svg>
                    </button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    @foreach($sobActionItems as $item)
                        @php
                            $statusColor = WeeklyFocusItem::STATUS_COLORS[$item->status] ?? 'secondary';
                            $isDone = in_array($item->status, ['completed', 'dropped']);
                        @endphp
                        <div class="d-flex align-items-start gap-2 mb-2 {{ $isDone ? 'opacity-50' : '' }}">
                            @if(!$isDone)
                                <form method="POST" action="{{ route('priorities.items.update', $item) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $item->status === 'planned' ? 'in_progress' : 'completed' }}">
                                    <button type="submit" class="btn btn-xs btn-outline-{{ $statusColor }} rounded-circle p-0" style="width:22px;height:22px;" title="Mark {{ $item->status === 'planned' ? 'in progress' : 'completed' }}">
                                        @if($item->status === 'in_progress')
                                            <i class="fal fa-spinner-third fa-spin" style="font-size:10px;"></i>
                                        @else
                                            <i class="fal fa-circle" style="font-size:8px;"></i>
                                        @endif
                                    </button>
                                </form>
                            @else
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle border border-{{ $statusColor }}" style="width:22px;height:22px;">
                                    <i class="fal fa-{{ $item->status === 'completed' ? 'check' : 'times' }} text-{{ $statusColor }}" style="font-size:10px;"></i>
                                </span>
                            @endif
                            <div>
                                <span class="small {{ $isDone ? 'text-decoration-line-through' : '' }}">{{ $item->description }}</span>
                                @if($item->partner)
                                    <span class="text-muted small">· {{ $item->partner->name }}</span>
                                @endif
                                <span class="text-muted small d-block">Added {{ $item->created_at->format('M j') }} · <span class="badge bg-{{ $statusColor }} bg-opacity-15 text-{{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $item->status)) }}</span></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Enablement Calendar (single-partner view only) --}}
    @if($selectedPartnerId ?? false)
    @php $calPartner = $calendarPartner ?? null; @endphp
    <div class="col-12 mb-g">
        <div id="panel-calendar" class="panel panel-icon panel-collapsed">
            <div class="panel-hdr">
                <h2><i class="fal fa-award me-2"></i> Enablement <span class="fw-light"><i>Calendar</i></span></h2>
                <div class="panel-toolbar">
                    <a href="{{ route('partners.enablement', $selectedPartnerId) }}" class="btn btn-xs btn-outline-primary me-2">
                        <i class="fal fa-external-link me-1"></i> Full Calendar
                    </a>
                    <button class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Toggle">
                        <svg class="sa-icon"><use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use><use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use></svg>
                    </button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    <div class="row">
                        {{-- Calendar --}}
                        <div class="col-lg-9">
                            <div id="sob-enablement-calendar"></div>
                        </div>
                        {{-- Sidebar --}}
                        <div class="col-lg-3">
                            {{-- Type Filter --}}
                            <div class="card shadow-0 mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Filter by Type</h5>
                                    <button class="btn btn-xs btn-link p-0" id="sobToggleAllTypes">All</button>
                                </div>
                                <div class="card-body">
                                    <div id="sob-type-filters">
                                        @foreach(\App\Models\EnablementEvent::TYPES as $typeKey => $typeLabel)
                                            <div class="form-check">
                                                <input class="form-check-input sob-type-filter" type="checkbox"
                                                       value="{{ $typeKey }}" id="sob-type-{{ $typeKey }}" checked>
                                                <label class="form-check-label" for="sob-type-{{ $typeKey }}">
                                                    <span class="badge me-1" style="background-color: {{ \App\Models\EnablementEvent::TYPE_COLORS[$typeKey] ?? '#868e96' }}; width: 10px; height: 10px; display: inline-block; border-radius: 50%; padding: 0;"></span>
                                                    {{ $typeLabel }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Available Sessions --}}
                            <div class="card shadow-0">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Available Sessions</h5>
                                </div>
                                <div class="card-body p-2">
                                    <div class="form-text mb-2 px-1">Drag a session onto the calendar to register this partner.</div>
                                    <div id="sob-draggable-sessions" style="max-height: 400px; overflow-y: auto;">
                                        <div class="text-center text-muted py-2">
                                            <small>Loading sessions...</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contact Assignment Modal --}}
    <div class="modal fade" id="sobContactAssignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign {{ $calPartner?->name ?? 'Partner' }} Contacts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="sobAssignSessionId">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h6 class="text-muted mb-1">Session</h6>
                            <h5 id="sobAssignSessionTitle" class="mb-0"></h5>
                        </div>
                        <div class="col-md-4 text-end">
                            <span id="sobAssignTypeBadge" class="badge"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><small class="text-muted d-block">Date</small><strong id="sobAssignSessionDate"></strong></div>
                        <div class="col-md-4"><small class="text-muted d-block">Location</small><strong id="sobAssignSessionLocation"></strong></div>
                        <div class="col-md-4"><small class="text-muted d-block">Instructor</small><strong id="sobAssignSessionInstructor"></strong></div>
                    </div>
                    <hr>
                    <h6 class="text-muted mb-2"><i class="fal fa-users me-1"></i> Assigned Contacts</h6>
                    <div id="sobAssignedContactsList" class="mb-3"><p class="text-muted">Loading...</p></div>
                    <hr>
                    <h6 class="text-muted mb-2"><i class="fal fa-user-plus me-1"></i> Add Contacts</h6>
                    <div id="sobAvailableContacts">
                        @foreach($calPartner?->contacts ?? [] as $contact)
                            <div class="form-check" id="sob-contact-row-{{ $contact->id }}">
                                <input class="form-check-input sob-assign-contact-cb" type="checkbox"
                                       value="{{ $contact->id }}" id="sob-assign-contact-{{ $contact->id }}">
                                <label class="form-check-label" for="sob-assign-contact-{{ $contact->id }}">
                                    {{ $contact->first_name }} {{ $contact->last_name }}
                                    @if($contact->email) <small class="text-muted">({{ $contact->email }})</small> @endif
                                </label>
                            </div>
                        @endforeach
                        @if(($calPartner?->contacts ?? collect())->isEmpty())
                            <p class="text-muted mb-0">No contacts for this partner.</p>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger me-auto" id="sobRemovePartnerBtn">
                        <i class="fal fa-times me-1"></i> Remove from Calendar
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="sobAssignContactsBtn" disabled>
                        <i class="fal fa-user-plus me-1"></i> Assign Selected
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100; pointer-events: none;" id="toast-container"></div>
    @endif

    {{-- Enablement Goals --}}
    @if(!empty($enablementGoals ?? []))
    @php
        $isMultiPartner = count($enablementGoals) > 1;

        // Build chart data per group
        $goalChartData = [];
        foreach ($enablementGoalGroups ?? [] as $gk => $gc) {
            if ($isMultiPartner) {
                // Aggregate across all partners — single bar pair
                $totalTarget = 0;
                $totalActual = 0;
                foreach ($enablementGoals as $pid => $pg) {
                    $groupLines = $pg[$gk] ?? [];
                    $totalTarget += array_sum(array_column($groupLines, 'target'));
                    $totalActual += array_sum(array_column($groupLines, 'actual'));
                }
                $goalChartData[$gk] = [
                    'label' => $gc['label'],
                    'partners' => [$gc['label']],
                    'targets' => [$totalTarget],
                    'actuals' => [$totalActual],
                ];
            } else {
                // Single partner — one bar pair
                $pid = array_key_first($enablementGoals);
                $groupLines = $enablementGoals[$pid][$gk] ?? [];
                $goalChartData[$gk] = [
                    'label' => $gc['label'],
                    'partners' => [($goalPartners[$pid] ?? null)?->name ?? 'Partner'],
                    'targets' => [array_sum(array_column($groupLines, 'target'))],
                    'actuals' => [array_sum(array_column($groupLines, 'actual'))],
                ];
            }
        }

        // For multi-partner: aggregate lines across all partners
        if ($isMultiPartner) {
            $aggregatedGroups = [];
            foreach ($enablementGoalGroups ?? [] as $gk => $gc) {
                $mergedLines = [];
                foreach ($enablementGoals as $pid => $pg) {
                    foreach ($pg[$gk] ?? [] as $lineKey => $line) {
                        if (!isset($mergedLines[$lineKey])) {
                            $mergedLines[$lineKey] = ['label' => $line['label'], 'target' => 0, 'actual' => 0];
                        }
                        $mergedLines[$lineKey]['target'] += $line['target'];
                        $mergedLines[$lineKey]['actual'] += $line['actual'];
                    }
                }
                // Compute variance
                foreach ($mergedLines as &$ml) {
                    $ml['variance'] = $ml['target'] > 0 ? $ml['actual'] - $ml['target'] : 0;
                }
                unset($ml);
                $aggregatedGroups[$gk] = $mergedLines;
            }
        }
    @endphp
    <div class="col-12 mb-g">
        <div id="panel-enablement-goals" class="panel panel-icon panel-collapsed">
            <div class="panel-hdr">
                <h2><i class="fal fa-graduation-cap me-2"></i> Enablement <span class="fw-light"><i>Goals</i></span></h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Toggle">
                        <svg class="sa-icon"><use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use><use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use></svg>
                    </button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    {{-- Goal charts row --}}
                    <div class="row mb-4">
                        @foreach($enablementGoalGroups ?? [] as $groupKey => $groupConfig)
                        <div class="col-xl-6 col-lg-6 mb-3">
                            <h6 class="fw-700 text-uppercase fs-xs text-muted mb-1">
                                <i class="fal fa-layer-group me-1"></i> {{ $groupConfig['label'] }}
                            </h6>
                            <div id="goal-chart-{{ $groupKey }}" style="min-height: 200px;"></div>
                        </div>
                        @endforeach
                    </div>

                    @if($isMultiPartner)
                        {{-- Aggregated tables for multi-partner --}}
                        <div class="row">
                            @foreach($enablementGoalGroups ?? [] as $groupKey => $groupConfig)
                            @php $lines = $aggregatedGroups[$groupKey] ?? []; @endphp
                            <div class="col-xl-6 col-lg-6 mb-4">
                                <h6 class="fw-700 text-uppercase fs-xs text-muted mb-2">
                                    <i class="fal fa-layer-group me-1"></i> {{ $groupConfig['label'] }}
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-bordered mb-0">
                                        <thead class="bg-fusion-50">
                                            <tr>
                                                <th></th>
                                                <th class="text-center" style="width: 80px;">Target</th>
                                                <th class="text-center" style="width: 70px;">Actual</th>
                                                <th class="text-center" style="width: 80px;">Variance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $groupTotals = ['target' => 0, 'actual' => 0]; @endphp
                                            @foreach($lines as $lineKey => $line)
                                            @php
                                                $groupTotals['target'] += $line['target'];
                                                $groupTotals['actual'] += $line['actual'];
                                            @endphp
                                            <tr>
                                                <td class="fw-500">{{ $line['label'] }}</td>
                                                <td class="text-center">{{ $line['target'] }}</td>
                                                <td class="text-center fw-500">{{ $line['actual'] }}</td>
                                                <td class="text-center">
                                                    @if($line['target'] > 0)
                                                        <span class="badge bg-{{ $line['variance'] >= 0 ? 'success' : 'danger' }}-200 text-{{ $line['variance'] >= 0 ? 'success' : 'danger' }}-700">
                                                            {{ $line['variance'] >= 0 ? '+' : '' }}{{ $line['variance'] }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        @if(count($lines) > 1)
                                        <tfoot class="fw-700 bg-fusion-25">
                                            @php $totalVar = $groupTotals['target'] > 0 ? $groupTotals['actual'] - $groupTotals['target'] : 0; @endphp
                                            <tr>
                                                <td>Total</td>
                                                <td class="text-center">{{ $groupTotals['target'] }}</td>
                                                <td class="text-center">{{ $groupTotals['actual'] }}</td>
                                                <td class="text-center">
                                                    @if($groupTotals['target'] > 0)
                                                        <span class="badge bg-{{ $totalVar >= 0 ? 'success' : 'danger' }}-200 text-{{ $totalVar >= 0 ? 'success' : 'danger' }}-700">
                                                            {{ $totalVar >= 0 ? '+' : '' }}{{ $totalVar }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Single partner: detailed tables with trends and editable targets --}}
                        @foreach($enablementGoals as $partnerId => $partnerGroups)
                        <div class="row">
                            @foreach($enablementGoalGroups ?? [] as $groupKey => $groupConfig)
                            @php $lines = $partnerGroups[$groupKey] ?? []; @endphp
                            <div class="col-xl-6 col-lg-6 mb-4">
                                <h6 class="fw-700 text-uppercase fs-xs text-muted mb-2">
                                    <i class="fal fa-layer-group me-1"></i> {{ $groupConfig['label'] }}
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-bordered mb-0">
                                        <thead class="bg-fusion-50">
                                            <tr>
                                                <th></th>
                                                <th class="text-center" style="width: 80px;">Target</th>
                                                <th class="text-center" style="width: 70px;">Actual</th>
                                                <th class="text-center" style="width: 80px;">Trend</th>
                                                <th class="text-center" style="width: 80px;">Variance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $groupTotals = ['target' => 0, 'actual' => 0]; @endphp
                                            @foreach($lines as $lineKey => $line)
                                            @php
                                                $groupTotals['target'] += $line['target'];
                                                $groupTotals['actual'] += $line['actual'];
                                                $trendData = $enablementTrends[$partnerId]['series'][$lineKey] ?? [];
                                            @endphp
                                            <tr data-partner-id="{{ $partnerId }}" data-category="{{ $lineKey }}">
                                                <td class="fw-500">{{ $line['label'] }}</td>
                                                <td class="text-center">
                                                    <span class="editable-goal-field" data-field="target" data-goal-id="{{ $line['goal_id'] ?? '' }}" data-partner-id="{{ $partnerId }}" data-category="{{ $lineKey }}">
                                                        <span class="goal-display" role="button" title="Click to edit">{{ $line['target'] }}</span>
                                                        <input type="number" class="form-control form-control-sm text-center d-none goal-input" style="width: 70px; margin: 0 auto;" value="{{ $line['target'] }}" min="0">
                                                    </span>
                                                </td>
                                                <td class="text-center fw-500">{{ $line['actual'] }}</td>
                                                <td class="text-center">
                                                    @if(count($trendData) > 1 && array_sum($trendData) > 0)
                                                        <span class="peity-line d-none" data-peity='{"fill": false, "stroke": "var(--primary-500)", "height": 22, "width": 60, "strokeWidth": 1.5}'>{{ implode(',', $trendData) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($line['target'] > 0)
                                                        <span class="badge bg-{{ $line['variance'] >= 0 ? 'success' : 'danger' }}-200 text-{{ $line['variance'] >= 0 ? 'success' : 'danger' }}-700">
                                                            {{ $line['variance'] >= 0 ? '+' : '' }}{{ $line['variance'] }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        @if(count($lines) > 1)
                                        <tfoot class="fw-700 bg-fusion-25">
                                            @php
                                                $totalVar = $groupTotals['target'] > 0 ? $groupTotals['actual'] - $groupTotals['target'] : 0;
                                                $groupTrend = [];
                                                foreach ($lines as $lk => $l) {
                                                    foreach (($enablementTrends[$partnerId]['series'][$lk] ?? []) as $i => $v) {
                                                        $groupTrend[$i] = ($groupTrend[$i] ?? 0) + $v;
                                                    }
                                                }
                                            @endphp
                                            <tr>
                                                <td>Total</td>
                                                <td class="text-center">{{ $groupTotals['target'] }}</td>
                                                <td class="text-center">{{ $groupTotals['actual'] }}</td>
                                                <td class="text-center">
                                                    @if(count($groupTrend) > 1 && array_sum($groupTrend) > 0)
                                                        <span class="peity-line d-none" data-peity='{"fill": false, "stroke": "var(--primary-700)", "height": 22, "width": 60, "strokeWidth": 2}'>{{ implode(',', $groupTrend) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($groupTotals['target'] > 0)
                                                        <span class="badge bg-{{ $totalVar >= 0 ? 'success' : 'danger' }}-200 text-{{ $totalVar >= 0 ? 'success' : 'danger' }}-700">
                                                            {{ $totalVar >= 0 ? '+' : '' }}{{ $totalVar }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Enablement Waterfall Chart --}}
    @if(!empty($enablementWaterfall['values'] ?? []))
    <div class="col-12 mb-g">
        <div id="panel-enablement-trends" class="panel panel-icon panel-collapsed">
            <div class="panel-hdr">
                <h2><i class="fal fa-chart-bar me-2"></i> Enablement <span class="fw-light"><i>Trends</i></span></h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Toggle">
                        <svg class="sa-icon"><use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use><use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use></svg>
                    </button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    <div id="enablement-waterfall-chart" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

{{-- Filter Modal (kept for nav integration) --}}
<div class="modal fade" id="newFilterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Custom Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-500">Filter Name</label>
                    <input type="text" id="filter-name" class="form-control" placeholder="My custom filter">
                </div>
                <div class="row">
                    @foreach(['geo' => 'Geography', 'region' => 'Region', 'lob' => 'LOB', 'stage' => 'Sales Stage', 'quarter' => 'Fiscal Quarter', 'approach' => 'Impl. Approach'] as $key => $label)
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-500">{{ $label }}</label>
                        <select class="form-select form-select-sm filter-select" id="filter-{{ $key }}" multiple>
                            @php
                                $optKey = match($key) {
                                    'geo' => 'geos', 'region' => 'regions', 'lob' => 'lobs',
                                    'stage' => 'stages', 'quarter' => 'quarters', 'approach' => 'approaches',
                                };
                            @endphp
                            @foreach($filterOptions[$optKey] ?? [] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-500">PSM</label>
                        <select class="form-select form-select-sm" id="filter-psm" multiple>
                            @foreach($psms ?? [] as $psm)
                            <option value="{{ $psm->id }}">{{ $psm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-500">Partner</label>
                        <select class="form-select form-select-sm" id="filter-partner" multiple>
                            @foreach($myPartners as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-filter-btn">Save Filter</button>
            </div>
        </div>
    </div>
</div>

{{-- Take Action Modal — Create Objective from Delivery Risk --}}
<div class="modal fade" id="takeActionModal" tabindex="-1" aria-labelledby="takeActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="takeActionModalLabel">
                    <i class="fal fa-crosshairs me-1 text-primary"></i> Take Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Tab nav --}}
            <ul class="nav nav-tabs px-3 pt-2" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-objective" type="button">Create Objective</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-action-item" type="button">Quick Action Item</button>
                </li>
            </ul>

            <div class="tab-content">
                {{-- Objective Tab --}}
                <div class="tab-pane fade show active" id="tab-objective">
                    <form method="POST" action="{{ route('priorities.objectives.store') }}" id="objective-form">
                        @csrf
                        <input type="hidden" name="_redirect" value="{{ request()->fullUrl() }}">
                        <div class="modal-body">
                            <div class="alert alert-info d-flex align-items-start mb-3" id="action-context-alert">
                                <i class="fal fa-info-circle me-2 mt-1"></i>
                                <small id="action-context-text"></small>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-500">Partner <span class="text-danger">*</span></label>
                                    <select name="partner_id" class="form-select" id="action-partner" required>
                                        <option value="">Select partner…</option>
                                        @foreach($myPartners as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-500">Target Date <span class="text-danger">*</span></label>
                                    <input type="date" name="target_date" class="form-control" id="action-target-date" required
                                        min="{{ now()->addDay()->format('Y-m-d') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-500">Objective Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" id="action-title" required maxlength="255">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-500">Description</label>
                                    <textarea name="description" class="form-control" id="action-description" rows="2" maxlength="1000"></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-500">Category</label>
                                    <select name="category" class="form-select" id="action-category">
                                        <option value="delivery">Delivery</option>
                                        <option value="pipeline">Pipeline</option>
                                        <option value="enablement">Enablement</option>
                                        <option value="certification">Certification</option>
                                        <option value="general">General</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-500">Metric Type</label>
                                    <select name="metric_type" class="form-select" id="action-metric-type">
                                        @foreach(\App\Models\PsmObjective::METRIC_TYPES as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-500">Target Value <span class="text-danger">*</span></label>
                                    <input type="number" name="target_value" class="form-control" id="action-target-value" required min="1" step="1">
                                </div>
                            </div>
                            <input type="hidden" name="source_signal" id="action-source-signal" value="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fal fa-bullseye-arrow me-1"></i> Create Objective</button>
                        </div>
                    </form>
                </div>

                {{-- Action Item Tab --}}
                <div class="tab-pane fade" id="tab-action-item">
                    <form method="POST" action="{{ route('priorities.items.store') }}" id="action-item-form">
                        @csrf
                        <input type="hidden" name="_redirect" value="{{ request()->fullUrl() }}">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-500">Partner <span class="text-danger">*</span></label>
                                    <select name="partner_id" class="form-select" id="action-item-partner" required>
                                        <option value="">Select partner…</option>
                                        @foreach($myPartners as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">&nbsp;</div>
                                <div class="col-12">
                                    <label class="form-label fw-500">Description <span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control" id="action-item-description" rows="3" required maxlength="500"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fal fa-plus me-1"></i> Add Action Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Objective Modal --}}
<div class="modal fade" id="editObjectiveModal" tabindex="-1" aria-labelledby="editObjectiveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editObjectiveModalLabel">
                    <i class="fal fa-pencil me-1 text-primary"></i> Edit Objective
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="edit-objective-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_redirect" value="{{ request()->fullUrl() }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Partner <span class="text-danger">*</span></label>
                            <select name="partner_id" class="form-select" id="edit-obj-partner" required>
                                <option value="">Select partner…</option>
                                @foreach($myPartners as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Target Date <span class="text-danger">*</span></label>
                            <input type="date" name="target_date" class="form-control" id="edit-obj-target-date" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Objective Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" id="edit-obj-title" required maxlength="255">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" class="form-control" id="edit-obj-description" rows="2" maxlength="1000"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Status</label>
                            <select name="status" class="form-select" id="edit-obj-status">
                                @foreach(\App\Models\PsmObjective::STATUSES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Category</label>
                            <select name="category" class="form-select" id="edit-obj-category">
                                @foreach(\App\Models\PsmObjective::CATEGORIES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Metric Type</label>
                            <select name="metric_type" class="form-select" id="edit-obj-metric-type">
                                @foreach(\App\Models\PsmObjective::METRIC_TYPES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Priority</label>
                            <select name="priority" class="form-select" id="edit-obj-priority">
                                <option value="0">— None —</option>
                                @foreach(\App\Models\PsmObjective::PRIORITIES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Target Value <span class="text-danger">*</span></label>
                            <input type="number" name="target_value" class="form-control" id="edit-obj-target-value" required min="1" step="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fal fa-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Close main content row --}}
</div>

{{-- Clear stale panel state BEFORE loadPanelState runs (deferred module).
     loadPanelState removes all panels from DOM and only re-inserts saved ones.
     If new panels (panel-priorities, panel-calendar) aren't in saved state, they vanish. --}}
<script>
(function() {
    var pageId = window.location.pathname.split('/').pop() || 'index.html';
    var allStates = JSON.parse(localStorage.getItem('allPanelStates') || '{}');
    var saved = allStates[pageId];
    if (!saved) return;
    var pagePanelIds = Array.from(document.querySelectorAll('.panel[id]')).map(function(p) { return p.id; });
    var savedIds = saved.map(function(s) { return s.id; });
    var hasMissing = pagePanelIds.some(function(id) { return savedIds.indexOf(id) === -1; });
    if (hasMissing) {
        delete allStates[pageId];
        localStorage.setItem('allPanelStates', JSON.stringify(allStates));
    }
})();
</script>

@endsection

@section('scripts')
    @vite(['resources/scripts/pages/peitycharts.js', 'resources/scripts/pages/easypiechart.js'])
    @if($selectedPartnerId ?? false)
    <script>
        window.enablementCalendarConfig = {
            mode: 'partner',
            partnerId: {{ (int) $selectedPartnerId }},
            partnerName: @json($calendarPartner?->name ?? ''),
            contacts: @json(($calendarPartner?->contacts ?? collect())->map(fn($c) => ['id' => $c->id, 'name' => $c->first_name . ' ' . $c->last_name, 'email' => $c->email])),
            eventTypes: @json(\App\Models\EnablementEvent::TYPES),
            typeColors: @json(\App\Models\EnablementEvent::TYPE_COLORS),
            apiBaseUrl: '{{ url("/api") }}',
            csrfToken: '{{ csrf_token() }}',
            // SoB-embedded mode: use sob- prefixed element IDs
            embedded: true,
            calendarEl: 'sob-enablement-calendar',
            draggableEl: 'sob-draggable-sessions',
            typeFilterClass: 'sob-type-filter',
            toggleAllId: 'sobToggleAllTypes',
            modalId: 'sobContactAssignModal',
            assignSessionIdEl: 'sobAssignSessionId',
            assignSessionTitleEl: 'sobAssignSessionTitle',
            assignTypeBadgeEl: 'sobAssignTypeBadge',
            assignSessionDateEl: 'sobAssignSessionDate',
            assignSessionLocationEl: 'sobAssignSessionLocation',
            assignSessionInstructorEl: 'sobAssignSessionInstructor',
            assignedContactsListEl: 'sobAssignedContactsList',
            assignContactCbClass: 'sob-assign-contact-cb',
            assignContactsBtnEl: 'sobAssignContactsBtn',
            removePartnerBtnEl: 'sobRemovePartnerBtn',
            contactRowPrefix: 'sob-contact-row-',
        };
    </script>
    @vite(['resources/scripts/pages/enablement-calendar.js'])
    @endif
<script>
function initSobCharts() {
    // Historical Trends chart (subscription-chart style)
    var subEl = document.getElementById('subscription-chart');
    if (subEl && window.colorMap && window.ApexCharts) {
        var trends = @json($trends ?? []);
        var categories = trends.map(function(t) {
            var d = new Date(t.date + 'T00:00:00');
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        });
        var nnacvData = trends.map(function(t) { return Math.round(t.nnacv); });
        var oppData = trends.map(function(t) { return t.opps; });
        var resData = trends.map(function(t) { return t.resources; });
        var winRateData = trends.map(function(t) { return t.win_rate || 0; });
        var warningColor = window.colorMap.warning ? window.colorMap.warning[500].hex : '#ffc107';
        new window.ApexCharts(subEl, {
            series: [
                { name: 'NNACV', type: 'area', data: nnacvData },
                { name: 'Opportunities', type: 'line', data: oppData },
                { name: 'Resources', type: 'line', data: resData },
                { name: 'Win Rate', type: 'line', data: winRateData }
            ],
            chart: { height: 335, type: 'line', zoom: { enabled: false }, stacked: false, toolbar: { show: false }, fontFamily: 'inherit', parentHeightOffset: 0 },
            colors: [window.colorMap.bootstrapVars.bodyColor.rgba(0.1), window.colorMap.primary[400].hex, window.colorMap.success ? window.colorMap.success[400].hex : '#198754', warningColor],
            stroke: { width: [1, 2, 2, 2], curve: 'smooth', colors: [window.colorMap.bootstrapVars.bodyColor.rgba(0.8), window.colorMap.primary[400].hex, window.colorMap.success ? window.colorMap.success[400].hex : '#198754', warningColor], dashArray: [4, 0, 0, 5] },
            fill: { type: ['solid', 'solid', 'solid', 'solid'], opacity: [0.15, 1, 1, 1] },
            markers: { size: [3, 3, 3, 3], strokeWidth: 2, hover: { sizeOffset: 2 } },
            xaxis: { categories: categories, labels: { style: { colors: window.colorMap.bootstrapVars.bodyColor.hex, fontSize: '12px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: [
                { seriesName: 'NNACV', labels: { style: { colors: window.colorMap.bootstrapVars.bodyColor.hex, fontSize: '12px' }, formatter: function(v) { if (v >= 1000000) return '$' + (v/1000000).toFixed(1) + 'M'; if (v >= 1000) return '$' + Math.round(v/1000) + 'K'; return '$' + v; } } },
                { seriesName: 'Opportunities', opposite: true, labels: { style: { colors: window.colorMap.bootstrapVars.bodyColor.hex, fontSize: '12px' }, formatter: function(v) { return Math.round(v).toLocaleString(); } } },
                { seriesName: 'Resources', show: false },
                { seriesName: 'Win Rate', opposite: true, show: false, min: 0, max: 100 }
            ],
            legend: { show: true, position: 'top', horizontalAlign: 'center', fontSize: '14px', fontFamily: 'inherit', labels: { colors: window.colorMap.bootstrapVars.bodyColor.hex }, markers: { width: 18, height: 6, radius: 2 }, itemMargin: { horizontal: 12, vertical: 0 } },
            grid: { borderColor: window.colorMap.bootstrapVars.bodyColor.rgba(0.1), strokeDashArray: 3, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } },
            tooltip: { shared: true, intersect: false, theme: 'dark', y: [{ formatter: function(v) { return '$' + v.toLocaleString(); } }, { formatter: function(v) { return v.toLocaleString() + ' opps'; } }, { formatter: function(v) { return v.toLocaleString() + ' resources'; } }, { formatter: function(v) { return v + '%'; } }] }
        }).render();
    }

    // Pipeline by LOB – horizontal bar chart
    var pipelineLobEl = document.getElementById('pipeline-lob-chart');
    if (pipelineLobEl && window.ApexCharts) {
        var pipelineLob = @json($current['pipeline_by_lob'] ?? []);
        var lobColors = {
            'Finance': window.colorMap.primary[500].hex,
            'Supply Chain': window.colorMap.success ? window.colorMap.success[500].hex : '#198754',
            'Sales': window.colorMap.info ? window.colorMap.info[500].hex : '#0dcaf0',
            'Workforce': window.colorMap.warning ? window.colorMap.warning[500].hex : '#ffc107',
        };
        var defaultColors = ['#6f42c1', '#d63384', '#fd7e14', '#20c997', '#0d6efd'];
        var pLobLabels = pipelineLob.map(function(l) { return l.lob; });
        var pLobNnacv = pipelineLob.map(function(l) { return Math.round(l.nnacv); });
        var pLobCounts = pipelineLob.map(function(l) { return l.count; });
        var pLobColors = pLobLabels.map(function(l, i) { return lobColors[l] || defaultColors[i % defaultColors.length]; });
        new window.ApexCharts(pipelineLobEl, {
            series: [{ name: 'NNACV', data: pLobNnacv }],
            chart: { type: 'bar', height: 200, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 3, distributed: true } },
            colors: pLobColors,
            dataLabels: {
                enabled: true,
                formatter: function(v, opt) { return pLobCounts[opt.dataPointIndex] + ' opps · $' + (v / 1000000).toFixed(1) + 'M'; },
                style: { fontSize: '11px', fontWeight: 500 },
                offsetX: 5
            },
            xaxis: { categories: pLobLabels, labels: { formatter: function(v) { return '$' + (v / 1000000).toFixed(1) + 'M'; }, style: { colors: window.colorMap.bootstrapVars.bodyColor.hex, fontSize: '11px' } } },
            yaxis: { labels: { style: { colors: window.colorMap.bootstrapVars.bodyColor.hex, fontSize: '12px' } } },
            grid: { borderColor: window.colorMap.bootstrapVars.bodyColor.rgba(0.08), xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
            tooltip: { y: { formatter: function(v) { return '$' + v.toLocaleString(); } } },
            legend: { show: false }
        }).render();
    }

    // Closed Won by LOB – donut chart
    var cwLobEl = document.getElementById('closed-won-lob-chart');
    if (cwLobEl && window.ApexCharts) {
        var cwLob = @json($current['closed_won_by_lob'] ?? []);
        if (cwLob.length > 0) {
            var cwLabels = cwLob.map(function(l) { return l.lob; });
            var cwValues = cwLob.map(function(l) { return Math.round(l.nnacv); });
            var cwColors = cwLabels.map(function(l, i) {
                var lobColors = {
                    'Finance': window.colorMap.primary[500].hex,
                    'Supply Chain': window.colorMap.success ? window.colorMap.success[500].hex : '#198754',
                    'Sales': window.colorMap.info ? window.colorMap.info[500].hex : '#0dcaf0',
                    'Workforce': window.colorMap.warning ? window.colorMap.warning[500].hex : '#ffc107',
                };
                var defaults = ['#6f42c1', '#d63384', '#fd7e14', '#20c997', '#0d6efd'];
                return lobColors[l] || defaults[i % defaults.length];
            });
            new window.ApexCharts(cwLobEl, {
                series: cwValues,
                chart: { type: 'donut', height: 200, fontFamily: 'inherit' },
                labels: cwLabels,
                colors: cwColors,
                dataLabels: { enabled: true, formatter: function(val) { return Math.round(val) + '%'; }, style: { fontSize: '12px' } },
                tooltip: { y: { formatter: function(v) { return '$' + v.toLocaleString(); } } },
                legend: { position: 'bottom', fontSize: '12px', labels: { colors: window.colorMap.bootstrapVars.bodyColor.hex } },
                plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total', formatter: function(w) { return '$' + (w.globals.seriesTotals.reduce(function(a,b){return a+b;}, 0) / 1000000).toFixed(1) + 'M'; } } } } } }
            }).render();
        } else {
            cwLobEl.innerHTML = '<div class="text-center text-muted py-5">No closed won deals in FY2027</div>';
        }
    }

    // Enablement Goal Charts — Target vs Actual per group
    var goalChartData = @json($goalChartData ?? []);
    Object.keys(goalChartData).forEach(function(groupKey) {
        var el = document.getElementById('goal-chart-' + groupKey);
        if (!el || !window.ApexCharts) return;

        var gd = goalChartData[groupKey];
        var hasAnyTarget = gd.targets.some(function(v) { return v > 0; });
        var hasAnyActual = gd.actuals.some(function(v) { return v > 0; });
        if (!hasAnyTarget && !hasAnyActual) {
            el.innerHTML = '<div class="text-center text-muted py-4 fs-sm">No targets set</div>';
            return;
        }

        new window.ApexCharts(el, {
            series: [
                { name: 'Target', data: gd.targets },
                { name: 'Actual', data: gd.actuals }
            ],
            chart: { type: 'bar', height: 200, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { columnWidth: gd.partners.length > 1 ? '60%' : '35%', borderRadius: 2 } },
            colors: ['#6c757d', '#e8803a'],
            dataLabels: {
                enabled: true,
                style: { fontSize: '11px', fontWeight: 600, colors: ['#333'] },
                offsetY: -12,
                formatter: function(val) { return val > 0 ? val : ''; }
            },
            xaxis: {
                categories: gd.partners,
                labels: { show: gd.partners.length > 1, style: { fontSize: '11px', fontWeight: 500 }, trim: true, maxHeight: 60 },
                axisBorder: { show: true, color: '#ccc' }
            },
            yaxis: { labels: { style: { fontSize: '11px' } }, forceNiceScale: true },
            grid: { borderColor: '#e8e8e8', strokeDashArray: 0, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } },
            legend: { position: 'top', fontSize: '12px', markers: { width: 10, height: 10, radius: 2 } },
            tooltip: { shared: true, intersect: false }
        }).render();
    });

    // Enablement Waterfall Chart
    var waterfallData = @json($enablementWaterfall ?? []);
    var waterfallEl = document.getElementById('enablement-waterfall-chart');
    if (waterfallEl && window.ApexCharts && waterfallData.values && waterfallData.values.length) {
        var labels = waterfallData.labels;
        var values = waterfallData.values;
        var colors = waterfallData.colors;
        var lastIdx = values.length - 1;

        // Build cumulative base (invisible) and visible series for waterfall effect
        var baseSeries = [];
        var visibleSeries = [];
        var cumulative = 0;
        for (var i = 0; i < values.length; i++) {
            if (i === lastIdx) {
                // Total bar starts from zero
                baseSeries.push(0);
                visibleSeries.push(values[i]);
            } else {
                baseSeries.push(cumulative);
                visibleSeries.push(values[i]);
                cumulative += values[i];
            }
        }

        new window.ApexCharts(waterfallEl, {
            series: [
                { name: 'Base', data: baseSeries },
                { name: 'Value', data: visibleSeries }
            ],
            chart: {
                type: 'bar',
                height: 320,
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    columnWidth: '50%',
                    borderRadius: 2
                }
            },
            colors: ['transparent', function(opts) { return colors[opts.dataPointIndex] || '#1b2a4a'; }],
            dataLabels: {
                enabled: true,
                enabledOnSeries: [1],
                formatter: function(val) { return val; },
                offsetY: -8,
                style: { fontSize: '13px', fontWeight: 700, colors: ['#333'] }
            },
            xaxis: {
                categories: labels,
                labels: {
                    style: { fontSize: '11px', fontWeight: 600 },
                    trim: false
                },
                axisBorder: { show: true, color: '#ccc' }
            },
            yaxis: {
                labels: { style: { fontSize: '11px' } },
                forceNiceScale: true
            },
            grid: {
                borderColor: '#e8e8e8',
                strokeDashArray: 0,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } }
            },
            legend: { show: false },
            tooltip: {
                enabled: true,
                shared: false,
                intersect: true,
                custom: function(opts) {
                    if (opts.seriesIndex === 0) return '';
                    var label = labels[opts.dataPointIndex].replace(/\n/g, ' ');
                    var val = visibleSeries[opts.dataPointIndex];
                    return '<div class="px-3 py-2"><strong>' + label + '</strong>: ' + val + '</div>';
                }
            },
            states: {
                hover: { filter: { type: 'darken', value: 0.9 } }
            }
        }).render();
    }

    var saveBtn = document.getElementById('save-filter-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            var name = document.getElementById('filter-name').value.trim();
            if (!name) { alert('Please enter a filter name'); return; }
            var criteria = {};
            ['geo','region','lob','stage','quarter','approach','psm','partner'].forEach(function(key) {
                var el = document.getElementById('filter-' + key);
                if (el) {
                    var vals = Array.from(el.selectedOptions).map(function(o) { return o.value; });
                    if (vals.length) { criteria[key] = vals; }
                }
            });
            fetch('{{ route("state-of-business.filters.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name: name, criteria: criteria })
            })
            .then(function(r) { return r.json(); })
            .then(function() { location.reload(); });
        });
    }

    // Delete saved filter buttons
    document.querySelectorAll('.delete-filter-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!confirm('Delete this filter?')) { return; }
            var id = this.dataset.id;
            fetch('/state-of-business/filters/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(function() { location.reload(); });
        });
    });
}
// Force Weekly Changes & Potential Risks collapsed on every page load.
// Must run AFTER loadPanelState (which is deferred via Vite module), so we use requestAnimationFrame.
function forceCollapsePanels() {
    ['panel-2', 'panel-4'].forEach(function(id) {
        var panel = document.getElementById(id);
        if (!panel) return;
        panel.className = 'panel panel-icon panel-collapsed';
        var container = panel.querySelector('.panel-container');
        if (container) {
            container.classList.remove('show');
            container.style.height = '0';
            container.style.overflow = 'hidden';
        }
    });
}
// (stale panel state is cleared via inline script in the content section)
// Run after all deferred scripts and DOM mutations settle
requestAnimationFrame(function() { setTimeout(forceCollapsePanels, 100); });

// Vite modules (type=module) are deferred — colorMap/ApexCharts may not be ready yet.
// Retry until dependencies are available.
(function waitForDeps() {
    if (window.ApexCharts && window.colorMap) {
        initSobCharts();
        // Force charts to recalculate width after layout settles
        setTimeout(function() { window.dispatchEvent(new Event('resize')); }, 250);
    } else {
        setTimeout(waitForDeps, 50);
    }
})();

// Detail card → panel toggle (Sales Velocity & Delivery Risks)
document.querySelectorAll('.js-detail-card').forEach(function(card) {
    card.addEventListener('click', function() {
        var panelId = this.dataset.panel;
        var panel = document.getElementById(panelId);
        if (!panel) return;

        var wasVisible = panel.style.display !== 'none';

        // Hide all panels and deselect all cards
        document.querySelectorAll('.js-detail-panel').forEach(function(p) { p.style.display = 'none'; });
        document.querySelectorAll('.js-detail-card').forEach(function(c) {
            c.style.boxShadow = '';
            var chevron = c.querySelector('.js-chevron');
            if (chevron) chevron.style.transform = '';
        });

        // Toggle: if it was already open, leave everything closed
        if (!wasVisible) {
            panel.style.display = '';
            this.style.boxShadow = '0 0 0 2px rgba(var(--bs-primary-rgb), .35)';
            var chevron = this.querySelector('.js-chevron');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        }
    });
});
document.querySelectorAll('.js-panel-close').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var panel = this.closest('.js-detail-panel');
        if (panel) panel.style.display = 'none';
        document.querySelectorAll('.js-detail-card').forEach(function(c) {
            c.style.boxShadow = '';
            var chevron = c.querySelector('.js-chevron');
            if (chevron) chevron.style.transform = '';
        });
    });
});

// Take Action — wire buttons to modal
document.querySelectorAll('.js-take-action').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var lob = this.dataset.lob;
        var opps = parseInt(this.dataset.opps);
        var resources = parseInt(this.dataset.resources);
        var nnacv = parseFloat(this.dataset.nnacv || 0);
        var severity = this.dataset.severity;
        var isStaffed = this.dataset.staffed === '1';

        // Auto-select partner if viewing a single partner
        var selectedPartnerId = '{{ $selectedPartnerId ?? '' }}';
        var selectedPartnerName = '{{ addslashes(($selectedPartner->name ?? '')) }}';
        if (selectedPartnerId) {
            document.getElementById('action-partner').value = selectedPartnerId;
            document.getElementById('action-item-partner').value = selectedPartnerId;
        }

        // Default target date to 30 days from now
        var targetDate = new Date();
        targetDate.setDate(targetDate.getDate() + 30);
        document.getElementById('action-target-date').value = targetDate.toISOString().split('T')[0];

        var partnerCtx = selectedPartnerName ? ' for <strong>' + selectedPartnerName + '</strong>' : '';

        if (!isStaffed) {
            // Understaffed: suggest resource recruitment
            var gap = Math.max(1, Math.ceil(opps - resources));
            document.getElementById('action-title').value = 'Recruit ' + gap + ' delivery resource' + (gap > 1 ? 's' : '') + ' for ' + lob;
            document.getElementById('action-description').value = lob + ' has ' + opps + ' pipeline opportunities but only ' + resources + ' delivery resources (' + severity + ' severity). $' + Number(nnacv).toLocaleString() + ' NNACV at risk.';
            document.getElementById('action-category').value = 'delivery';
            document.getElementById('action-metric-type').value = 'resources_registered';
            document.getElementById('action-target-value').value = gap;
            document.getElementById('action-source-signal').value = 'sob_delivery_risk:' + lob;
            document.getElementById('action-context-text').innerHTML =
                '<strong>' + lob + '</strong>' + partnerCtx + ' — ' + severity + ' delivery risk. ' +
                opps + ' pipeline opps, ' + resources + ' resources, $' + Number(nnacv).toLocaleString() + ' NNACV at risk.';
            document.getElementById('action-item-description').value = 'Follow up on ' + lob + ' delivery resource gap — ' + opps + ' opps but only ' + resources + ' resources.';
        } else {
            // Staffed: suggest pipeline or enablement action
            document.getElementById('action-title').value = 'Grow ' + lob + ' pipeline';
            document.getElementById('action-description').value = lob + ' has ' + opps + ' pipeline opportunities with ' + resources + ' delivery resources (' + severity + ' risk). Well-staffed — opportunity to grow.';
            document.getElementById('action-category').value = 'pipeline';
            document.getElementById('action-metric-type').value = 'opportunity_count';
            document.getElementById('action-target-value').value = Math.max(1, Math.ceil(opps * 1.2));
            document.getElementById('action-source-signal').value = 'sob_lob_growth:' + lob;
            document.getElementById('action-context-text').innerHTML =
                '<strong>' + lob + '</strong>' + partnerCtx + ' — ' + severity + ' risk, well-staffed. ' +
                opps + ' pipeline opps, ' + resources + ' resources.';
            document.getElementById('action-item-description').value = 'Explore growth opportunities for ' + lob + ' — currently well-staffed with ' + resources + ' resources for ' + opps + ' opps.';
        }

        // Show modal
        var modal = new bootstrap.Modal(document.getElementById('takeActionModal'));
        modal.show();
    });
});

// New Objective button in Priorities panel header
document.getElementById('priorities-new-objective-btn')?.addEventListener('click', function() {
    var selectedPartnerId = '{{ $selectedPartnerId ?? '' }}';
    if (selectedPartnerId) {
        document.getElementById('action-partner').value = selectedPartnerId;
        document.getElementById('action-item-partner').value = selectedPartnerId;
    }
    var targetDate = new Date();
    targetDate.setDate(targetDate.getDate() + 30);
    document.getElementById('action-target-date').value = targetDate.toISOString().split('T')[0];
    document.getElementById('action-title').value = '';
    document.getElementById('action-description').value = '';
    document.getElementById('action-category').value = 'general';
    document.getElementById('action-metric-type').value = 'manual';
    document.getElementById('action-target-value').value = '';
    document.getElementById('action-source-signal').value = '';
    document.getElementById('action-context-alert').classList.add('d-none');
    document.getElementById('action-item-description').value = '';
    var modal = new bootstrap.Modal(document.getElementById('takeActionModal'));
    modal.show();
});

// Edit Objective
document.querySelectorAll('.edit-objective-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var form = document.getElementById('edit-objective-form');
        form.action = '{{ url("priorities/objectives") }}/' + this.dataset.objId;
        document.getElementById('edit-obj-title').value = this.dataset.objTitle;
        document.getElementById('edit-obj-description').value = this.dataset.objDescription || '';
        document.getElementById('edit-obj-category').value = this.dataset.objCategory;
        document.getElementById('edit-obj-metric-type').value = this.dataset.objMetricType;
        document.getElementById('edit-obj-target-value').value = parseInt(this.dataset.objTargetValue) || 0;
        document.getElementById('edit-obj-target-date').value = this.dataset.objTargetDate;
        document.getElementById('edit-obj-partner').value = this.dataset.objPartnerId;
        document.getElementById('edit-obj-status').value = this.dataset.objStatus;
        document.getElementById('edit-obj-priority').value = this.dataset.objPriority || 0;
        var modal = new bootstrap.Modal(document.getElementById('editObjectiveModal'));
        modal.show();
    });
});

// Objective Filters (additive — multiple categories can be active at once)
(function() {
    var allBtn = document.querySelector('.obj-filter-btn[data-filter="all"]');
    var catBtns = document.querySelectorAll('.obj-filter-btn[data-filter="category"]');
    var items = document.querySelectorAll('.objective-item');
    var groups = document.querySelectorAll('.objective-priority-group');
    if (!items.length) return;

    function applyFilters() {
        var activeCats = [];
        catBtns.forEach(function(b) {
            if (b.classList.contains('active')) activeCats.push(b.dataset.value);
        });
        var showAll = allBtn && allBtn.classList.contains('active');

        items.forEach(function(item) {
            if (showAll || activeCats.length === 0) {
                item.style.display = '';
            } else {
                item.style.display = activeCats.indexOf(item.dataset.category) !== -1 ? '' : 'none';
            }
        });

        groups.forEach(function(group) {
            var visibleItems = group.querySelectorAll('.objective-item:not([style*="display: none"])');
            group.style.display = visibleItems.length > 0 ? '' : 'none';
        });
    }

    if (allBtn) {
        allBtn.addEventListener('click', function() {
            catBtns.forEach(function(b) { b.classList.remove('active'); });
            allBtn.classList.add('active');
            applyFilters();
        });
    }

    catBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (allBtn) allBtn.classList.remove('active');
            this.classList.toggle('active');

            // If nothing selected, revert to All
            var anyActive = false;
            catBtns.forEach(function(b) { if (b.classList.contains('active')) anyActive = true; });
            if (!anyActive && allBtn) allBtn.classList.add('active');

            applyFilters();
        });
    });
})();

// Delete Objective — confirm before submitting
document.querySelectorAll('.delete-objective-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to delete this objective?')) {
            e.preventDefault();
        }
    });
});

// Objective drag-and-drop: reorder within groups + drag between priority tiers
(function() {
    var container = document.getElementById('objectives-grouped-list');
    if (!container) return;

    var dropZones = container.querySelectorAll('.objective-drop-zone');
    var dragItem = null;

    // Style for drop zone hover
    var hoverClass = 'border-primary';
    var hoverStyle = 'border: 2px dashed var(--bs-primary); border-radius: 6px; background: rgba(var(--bs-primary-rgb), 0.03);';

    function initDraggable(item) {
        item.setAttribute('draggable', 'true');

        item.addEventListener('dragstart', function(e) {
            dragItem = this;
            this.style.opacity = '0.4';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.dataset.objectiveId);
        });

        item.addEventListener('dragend', function() {
            this.style.opacity = '1';
            dragItem = null;
            // Clean up all highlights
            dropZones.forEach(function(z) { z.removeAttribute('style'); z.style.minHeight = '20px'; });
            container.querySelectorAll('.objective-item').forEach(function(el) {
                el.classList.remove(hoverClass);
            });
        });

        // Item-level drag over (for reordering within a zone)
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            this.classList.add(hoverClass);
        });

        item.addEventListener('dragleave', function() {
            this.classList.remove(hoverClass);
        });

        item.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove(hoverClass);
            if (!dragItem || dragItem === this) return;

            // Insert before or after based on mouse position
            var rect = this.getBoundingClientRect();
            var midY = rect.top + rect.height / 2;
            if (e.clientY < midY) {
                this.parentNode.insertBefore(dragItem, this);
            } else {
                this.parentNode.insertBefore(dragItem, this.nextSibling);
            }

            saveOrder();
        });
    }

    // Init all existing items
    container.querySelectorAll('.objective-item').forEach(initDraggable);

    // Drop zone level: allows dropping into empty groups
    dropZones.forEach(function(zone) {
        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            this.setAttribute('style', hoverStyle + ' min-height: 40px;');
        });

        zone.addEventListener('dragleave', function(e) {
            // Only if leaving the zone itself, not entering a child
            if (!this.contains(e.relatedTarget)) {
                this.removeAttribute('style');
                this.style.minHeight = '20px';
            }
        });

        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.removeAttribute('style');
            this.style.minHeight = '20px';
            if (!dragItem) return;

            // If dropped on the zone (not on an item), append to end
            if (e.target === this || e.target.classList.contains('objective-drop-zone')) {
                this.appendChild(dragItem);
                saveOrder();
            }
        });
    });

    function saveOrder() {
        var groups = {};
        dropZones.forEach(function(zone) {
            var priority = zone.dataset.priority;
            var ids = [];
            zone.querySelectorAll('.objective-item').forEach(function(item) {
                ids.push(parseInt(item.dataset.objectiveId));
                // Update the item's data attribute
                item.dataset.priority = priority;
            });
            groups[priority] = ids;

            // Update group count in header
            var groupEl = zone.closest('.objective-priority-group');
            if (groupEl) {
                var countEl = groupEl.querySelector('small.text-muted');
                if (countEl) {
                    var n = ids.length;
                    countEl.textContent = n + ' objective' + (n !== 1 ? 's' : '');
                }
            }
        });

        fetch('{{ route("priorities.objectives.reorder") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ groups: groups })
        });
    }
})();

// Enablement Goals — inline editing
(function() {
    var csrfToken = '{{ csrf_token() }}';
    var upsertUrl = '{{ route("state-of-business.goals.upsert") }}';

    document.querySelectorAll('.editable-goal-field .goal-display').forEach(function(span) {
        span.style.cursor = 'pointer';
        span.title = 'Click to edit';
        span.addEventListener('click', function() {
            var wrapper = this.closest('.editable-goal-field');
            var input = wrapper.querySelector('.goal-input');
            this.classList.add('d-none');
            input.classList.remove('d-none');
            input.focus();
            input.select();
        });
    });

    document.querySelectorAll('.editable-goal-field .goal-input').forEach(function(input) {
        function save() {
            var wrapper = input.closest('.editable-goal-field');
            var display = wrapper.querySelector('.goal-display');
            var partnerId = wrapper.dataset.partnerId;
            var category = wrapper.dataset.category;
            var row = input.closest('tr');
            var newVal = parseInt(input.value) || 0;

            var payload = {
                partner_id: parseInt(partnerId),
                category: category,
                target: newVal,
                baseline: 0
            };

            display.textContent = newVal;
            display.classList.remove('d-none');
            input.classList.add('d-none');

            fetch(upsertUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload)
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                // Update variance cell
                var varianceCell = row.querySelector('td:last-child');
                if (data.target > 0) {
                    var cls = data.variance >= 0 ? 'success' : 'danger';
                    varianceCell.innerHTML = '<span class="badge bg-' + cls + '-200 text-' + cls + '-700">' + (data.variance >= 0 ? '+' : '') + data.variance + '</span>';
                } else {
                    varianceCell.innerHTML = '<span class="text-muted">—</span>';
                }
                wrapper.dataset.goalId = data.goal_id;
            })
            .catch(function(err) { console.error('Failed to save goal:', err); });
        }

        input.addEventListener('blur', save);
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); save(); }
            if (e.key === 'Escape') {
                var wrapper = input.closest('.editable-goal-field');
                var display = wrapper.querySelector('.goal-display');
                input.value = display.textContent;
                display.classList.remove('d-none');
                input.classList.add('d-none');
            }
        });
    });
})();
</script>
@endsection
