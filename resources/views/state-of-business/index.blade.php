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
                <h2><i class="fal fa-bullseye-arrow me-2"></i> Priorities <span class="fw-light"><i>& Actions</i></span></h2>
                <div class="panel-toolbar">
                    <a href="{{ route('priorities.weekly') }}" class="btn btn-xs btn-outline-primary me-2">
                        <i class="fal fa-external-link me-1"></i> View All in Priorities
                    </a>
                    <button class="btn btn-panel waves-effect waves-themed" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Toggle">
                        <svg class="sa-icon"><use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use><use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use></svg>
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
                        <div class="{{ ($sobActionItems ?? collect())->count() > 0 ? 'col-xl-7' : 'col-12' }} mb-3 mb-xl-0">
                            <h6 class="fw-700 text-uppercase fs-xs text-muted mb-3">
                                <i class="fal fa-bullseye-arrow me-1"></i> My Objectives
                                <span class="badge bg-primary bg-opacity-15 text-primary ms-1">{{ ($sobObjectives ?? collect())->count() }}</span>
                            </h6>
                            @forelse($sobObjectives ?? collect() as $obj)
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
                                    $isOverdue = $obj->isOverdue();
                                    $categoryColor = PsmObjective::CATEGORY_COLORS[$obj->category] ?? 'secondary';
                                    $statusColor = PsmObjective::STATUS_COLORS[$obj->status] ?? 'secondary';
                                    $isActive = $obj->status === 'active';
                                @endphp
                                <div class="card border shadow-0 mb-2 {{ !$isActive ? 'opacity-75' : '' }}">
                                    <div class="card-body py-2 px-3">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="flex-grow-1 me-3">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <span class="badge bg-{{ $statusColor }} bg-opacity-15 text-{{ $statusColor }}">{{ ucfirst($obj->status) }}</span>
                                                    <span class="badge bg-{{ $categoryColor }} bg-opacity-15 text-{{ $categoryColor }}">{{ ucfirst($obj->category) }}</span>
                                                    <span class="fw-500">{{ $obj->title }}</span>
                                                    @if($obj->partner)
                                                        <span class="text-muted small">· {{ $obj->partner->name }}</span>
                                                    @endif
                                                </div>
                                                @if($obj->description)
                                                    <p class="mb-1 small text-muted">{{ Str::limit($obj->description, 120) }}</p>
                                                @endif
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <div class="progress flex-grow-1" style="height: 6px; max-width: 200px;">
                                                        <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $progress }}%"></div>
                                                    </div>
                                                    <span class="small fw-500 text-{{ $progressColor }}">{{ $progress }}%</span>
                                                    <span class="small text-muted">{{ $obj->current_value }}/{{ $obj->target_value }} {{ PsmObjective::METRIC_TYPES[$obj->metric_type] ?? '' }}</span>
                                                    @if($isActive)
                                                        {{-- Inline progress update --}}
                                                        <form method="POST" action="{{ route('priorities.objectives.update', $obj) }}" class="d-inline-flex align-items-center gap-1 ms-2">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="number" name="current_value" class="form-control form-control-sm" style="width: 70px; height: 24px; font-size: 11px;" value="{{ $obj->current_value }}" min="0" step="0.01">
                                                            <button type="submit" class="btn btn-xs btn-outline-primary" style="height: 24px; padding: 0 6px;" title="Update progress"><i class="fal fa-save" style="font-size: 10px;"></i></button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-end flex-shrink-0">
                                                <small class="text-muted d-block">Created {{ $obj->created_at->format('M j') }}</small>
                                                <small class="text-muted d-block">Due {{ $obj->target_date->format('M j') }}</small>
                                                @if($isOverdue)
                                                    <span class="badge bg-danger bg-opacity-15 text-danger">Overdue</span>
                                                @endif
                                                @if($isActive)
                                                    <form method="POST" action="{{ route('priorities.objectives.update', $obj) }}" class="d-inline mt-1">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="btn btn-xs btn-outline-success" title="Mark completed"><i class="fal fa-check me-1"></i>Done</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small">No objectives yet.</p>
                            @endforelse
                        </div>

                        {{-- Action Items --}}
                        @if(($sobActionItems ?? collect())->count() > 0)
                        <div class="col-xl-5">
                            <h6 class="fw-700 text-uppercase fs-xs text-muted mb-3">
                                <i class="fal fa-tasks me-1"></i> Action Items
                                <span class="badge bg-primary bg-opacity-15 text-primary ms-1">{{ $sobActionItems->count() }}</span>
                            </h6>
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
                        @endif
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
                                                        <span class="badge bg-{{ $statusColor }} bg-opacity-15 text-{{ $statusColor }}">{{ ucfirst($obj->status) }}</span>
                                                        <span class="badge bg-{{ $categoryColor }} bg-opacity-15 text-{{ $categoryColor }}">{{ ucfirst($obj->category) }}</span>
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
                                                        <span class="small text-muted">{{ $obj->current_value }}/{{ $obj->target_value }} {{ PsmObjective::METRIC_TYPES[$obj->metric_type] ?? '' }}</span>
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
                    @foreach($enablementGoals as $partnerId => $partnerGroups)
                    @php $partnerName = ($goalPartners[$partnerId] ?? null)?->name ?? 'Partner #' . $partnerId; @endphp

                    @if(count($enablementGoals) > 1)
                    <h5 class="fw-700 mb-3 {{ !$loop->first ? 'mt-4 pt-3 border-top' : '' }}">
                        <a href="{{ route('state-of-business', ['partner' => $partnerId]) }}" class="text-decoration-none">{{ $partnerName }}</a>
                    </h5>
                    @endif

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
                                            <th>Product</th>
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
                                            // Sum line trends element-wise for group total sparkline
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
                </div>
            </div>
        </div>
    </div>

    {{-- Enablement Trends Panel --}}
    <div class="col-12 mb-g">
        <div id="panel-enablement-trends" class="panel panel-icon panel-collapsed">
            <div class="panel-hdr">
                <h2><i class="fal fa-chart-line me-2"></i> Enablement <span class="fw-light"><i>Trends</i></span></h2>
                <div class="panel-toolbar">
                    <button class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Toggle">
                        <svg class="sa-icon"><use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use><use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use></svg>
                    </button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    <div class="row">
                        @foreach($enablementGoalGroups as $gKey => $gConfig)
                        <div class="col-xl-6 col-lg-6 mb-4">
                            <h6 class="fw-700 text-uppercase fs-xs text-muted mb-2">
                                <i class="fal fa-layer-group me-1"></i> {{ $gConfig['label'] }}
                            </h6>
                            <div id="enablement-trend-{{ $gKey }}" style="min-height: 250px;"></div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                                        <option value="resources_registered">Resources Registered</option>
                                        <option value="certifications">Certifications</option>
                                        <option value="pipeline_value">Pipeline Value</option>
                                        <option value="opportunity_count">Opportunity Count</option>
                                        <option value="projects_delivered">Projects Delivered</option>
                                        <option value="manual">Manual</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-500">Target Value <span class="text-danger">*</span></label>
                                    <input type="number" name="target_value" class="form-control" id="action-target-value" required min="0.01" step="0.01">
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

    // Enablement Trends — ApexCharts per group
    var enablementTrends = @json($enablementTrends ?? []);
    var enablementGroups = @json($enablementGoalGroups ?? []);
    var enablementGoalsData = @json($enablementGoals ?? []);
    var groupColors = ['#0d6efd', '#198754', '#6f42c1', '#d63384', '#fd7e14', '#20c997', '#0dcaf0'];

    Object.keys(enablementGroups).forEach(function(groupKey) {
        var el = document.getElementById('enablement-trend-' + groupKey);
        if (!el || !window.ApexCharts) return;

        var group = enablementGroups[groupKey];
        var series = [];
        var annotations = [];
        var xCategories = [];

        // Use first partner's dates for x-axis
        var partnerIds = Object.keys(enablementTrends);
        if (!partnerIds.length) return;
        var firstPartner = enablementTrends[partnerIds[0]];
        xCategories = (firstPartner.dates || []).map(function(d) {
            return new Date(d + 'T00:00:00').toLocaleDateString('en-US', {month:'short', day:'numeric'});
        });
        if (xCategories.length < 2) {
            el.innerHTML = '<div class="text-center text-muted py-5 fs-sm">Need 2+ snapshots for trend data</div>';
            return;
        }

        if (group.aggregate) {
            // Aggregate group: one series per partner (or single series in single-partner view)
            partnerIds.forEach(function(pid) {
                var data = enablementTrends[pid];
                var seriesData = data.series[groupKey] || [];
                if (seriesData.length) {
                    series.push({ name: partnerIds.length > 1 ? 'Partner ' + pid : group.label, data: seriesData });
                }
                // Target reference line
                var goalData = (enablementGoalsData[pid] || {})[groupKey] || {};
                var target = (goalData[groupKey] || {}).target || 0;
                if (target > 0) {
                    annotations.push({ y: target, borderColor: '#FF4560', strokeDashArray: 4,
                        label: { text: 'Target: ' + target, style: { background: '#FF4560', color: '#fff', fontSize: '11px', padding: { left: 6, right: 6, top: 2, bottom: 2 } } } });
                }
            });
        } else {
            // Line-level: one series per product line (single-partner) or summed across partners
            var lines = group.lines || {};
            var ci = 0;
            Object.keys(lines).forEach(function(lineKey) {
                // Sum across partners for multi-partner view
                var combined = [];
                partnerIds.forEach(function(pid) {
                    var lineData = (enablementTrends[pid].series || {})[lineKey] || [];
                    lineData.forEach(function(v, i) { combined[i] = (combined[i] || 0) + v; });
                });
                if (combined.some(function(v) { return v > 0; })) {
                    series.push({ name: lines[lineKey].label, data: combined });
                }
                // Target reference lines for single-partner
                if (partnerIds.length === 1) {
                    var goalData = (enablementGoalsData[partnerIds[0]] || {})[groupKey] || {};
                    var target = (goalData[lineKey] || {}).target || 0;
                    if (target > 0) {
                        annotations.push({ y: target, borderColor: groupColors[ci % groupColors.length], strokeDashArray: 4, opacity: 0.5,
                            label: { text: lines[lineKey].label + ': ' + target, style: { background: groupColors[ci % groupColors.length], color: '#fff', fontSize: '10px', padding: { left: 4, right: 4, top: 1, bottom: 1 } } } });
                    }
                }
                ci++;
            });
        }

        if (!series.length) {
            el.innerHTML = '<div class="text-center text-muted py-5 fs-sm">No data yet</div>';
            return;
        }

        new window.ApexCharts(el, {
            series: series,
            chart: { type: 'line', height: 240, toolbar: { show: false }, fontFamily: 'inherit', zoom: { enabled: false } },
            stroke: { width: 2, curve: 'smooth' },
            markers: { size: 3, strokeWidth: 2, hover: { sizeOffset: 2 } },
            xaxis: { categories: xCategories, labels: { style: { fontSize: '11px' } } },
            yaxis: { min: 0, forceNiceScale: true, labels: { style: { fontSize: '11px' } } },
            annotations: { yaxis: annotations },
            legend: { position: 'top', fontSize: '12px', labels: { colors: window.colorMap ? window.colorMap.bootstrapVars.bodyColor.hex : '#333' } },
            grid: { borderColor: window.colorMap ? window.colorMap.bootstrapVars.bodyColor.rgba(0.08) : 'rgba(0,0,0,0.08)', strokeDashArray: 3 },
            tooltip: { shared: true, intersect: false }
        }).render();
    });

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
