@extends('layouts.vertical', ['pageTitle' => 'Marketing Dashboard'])

@section('page-title')
    <div class="d-flex align-items-end mb-4">
        <div>
            @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Marketing Dashboard', 'pageSubTitle1' => 'Insights', 'pageSubTitle2'=> 'Dashboards'])
        </div>
        <div class="ms-auto d-none d-sm-flex align-items-center ">
            <div class="d-flex align-items-center">
                <div class="d-none d-md-inline-flex">
                    <span class="peity-donut d-none"
                          data-peity='{ "fill": ["var(--success-300)"], "height": 34, "width": "34" }'>7, 3</span>
                </div>
                <div class="d-inline-flex flex-column justify-content-center ms-2">
                                    <span class="fw-500 fs-xs d-block">
                                        <small>Click Rate</small>
                                    </span>
                    <span class="fw-500 fs-xl d-flex align-items-center text-success"> 70% <svg
                            class="sa-icon sa-bold sa-icon-success ms-1">
                                            <use href="/img/sprite.svg#trending-up"></use>
                                        </svg>
                                    </span>
                </div>
            </div>
            <div
                class="d-flex align-items-center border-faded border-dashed border-top-0 border-bottom-0 border-end-0 ms-3 ps-3">
                <div class="d-none d-md-inline-flex">
                    <span class="peity-donut d-none"
                          data-peity='{ "fill": ["var(--danger-500)"], "height": 34, "width": "34" }'>1, 9</span>
                </div>
                <div class="d-inline-flex flex-column justify-content-center ms-2">
                                    <span class="fw-500 fs-xs d-block">
                                        <small>Bounce Rate</small>
                                    </span>
                    <span class="fw-500 fs-xl d-flex align-items-center text-danger"> 10% <svg
                            class="sa-icon sa-bold ms-1 sa-icon-danger">
                                            <use href="/img/sprite.svg#trending-down"></use>
                                        </svg>
                                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row sortable-off">
        <div class="col-md-12 col-lg-6 col-xl-3">
            <div class="card mb-g">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mt-0">Orders</h6>
                    <h2>38,786</h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary">+115% </span>
                        <span class="text-muted">From previous period</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-lg-6 col-xl-3">
            <div class="card mb-g">
                <div class="card-body">
                    <h6 class="text-muted">Product Sold</h6>
                    <h2>24,986</h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary">+55% </span>
                        <span class="text-muted">Last year</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-lg-6 col-xl-3">
            <div class="card mb-g">
                <div class="card-body">
                    <h6 class="text-muted">Total Revenue</h6>
                    <h2 class="text-success">$15.4 M</h2>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <span class="badge bg-primary">+77% </span>
                        <span class="text-muted">Last year</span>
                        <div class="ms-auto d-flex gap-2 align-items-center">
                            <svg class="sa-icon sa-bold sa-icon-success ms-1">
                                <use href="/icons/sprite.svg#trending-up"></use>
                            </svg>
                            7%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-lg-6 col-xl-3">
            <div class="card mb-g">
                <div class="card-body">
                    <h6 class="text-muted mt-0">Income status</h6>
                    <h2 class="text-info">$1.2 M</h2>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <span class="text-muted">New income</span>
                        <div class="ms-auto d-flex gap-2 align-items-center">
                            <svg class="sa-icon sa-bold sa-icon-success ms-1">
                                <use href="/icons/sprite.svg#trending-up"></use>
                            </svg>
                            10%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <!-- Live Feeds -->
            <div class="panel panel-icon panel-locked" id="panel-1">
                <div class="panel-hdr">
                    <h2> Live <span class="fw-light"><i>Feeds</i></span>
                    </h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="marketing-profits-chart"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2> Returning <span class="fw-light"><i>Target</i></span></h2>
                    <div class="panel-toolbar">
                        <button class="btn btn-panel" data-action="panel-collapse" data-bs-offset="0,10"
                                data-bs-original-title="Toggle" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button class="btn btn-panel" data-action="panel-fullscreen" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container">
                    <div class="panel-content position-relative">
                        <div
                            class="p-1 position-absolute end-0 top-0 mt-5 me-3 z-1 d-flex align-items-center justify-content-center">
                            <div
                                class="border-faded border-top-0 border-start-0 border-bottom-0 py-2 pe-4 me-3 hidden-sm-down">
                                <div class="text-end fw-500 l-h-n d-flex flex-column">
                                    <div class="h5 m-0 d-flex align-items-center justify-content-end">
                                        $44.34 GE
                                        <svg class="sa-icon sa-bold sa-icon-success ms-1">
                                            <use href="/icons/sprite.svg#trending-up"></use>
                                        </svg>
                                    </div>
                                    <span class="m-0 fs-xs text-muted">Redux margins and estimates</span>
                                </div>
                            </div>
                            <div
                                class="js-easy-pie-chart position-relative d-inline-flex align-items-center justify-content-center"
                                data-linewidth="10" data-percent="35" data-piesize="95" data-scalelength="5"
                                style="color: var(--primary-300);">
                                <div
                                    class="js-easy-pie-chart color-success-400 position-relative position-absolute translate-middle top-50 start-50 d-flex align-items-center justify-content-center"
                                    data-linewidth="5" data-percent="65" data-piesize="60" data-scalecolor="#fff"
                                    data-scalelength="1" style="color: var(--success-300);">
                                    <div
                                        class="position-absolute translate-middle top-50 start-50 d-flex align-items-center justify-content-center fw-500 fs-xl">
                                        78%
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="returning-target-chart"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2> Efficiency <span class="fw-light"><i>Metrics</i></span></h2>
                    <div class="panel-toolbar">
                        <button class="btn btn-xs btn-outline-default" data-action="panel-refresh"
                                data-refresh-callback="loadRandomGraph" data-refresh-duration="2000"
                                type="button">Refresh
                        </button>
                    </div>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="p-3">
                            <div class="row">
                                <div class="col-6 col-xl-6 col-xxl-3 d-sm-flex align-items-center mb-3">
                                    <div class="p-2 me-2 me-xl-3 me-xxl-3 bg-warning-300 rounded">
                                        <span class="peity-bar"
                                              data-peity='{ "fill": ["#fff"], "width": 27, "height": 27 }'>3,4,5,8,2</span>
                                    </div>
                                    <div class="d-flex flex-column align-items-start justify-content-center">
                                        <label class="fs-xs mb-0">Bounce Rate</label>
                                        <h5 class="fw-bold mb-0">37.56%</h5>
                                    </div>
                                </div>
                                <div class="col-6 col-xl-6 col-xxl-3 d-sm-flex align-items-center mb-3">
                                    <div class="p-2 me-2 me-xxl-3 bg-info-300 rounded">
                                        <span class="peity-bar"
                                              data-peity='{ "fill": ["#fff"], "width": 27, "height": 27 }'>5,3,1,7,9</span>
                                    </div>
                                    <div class="d-flex flex-column align-items-start justify-content-center">
                                        <label class="fs-xs mb-0">Actual Sessions</label>
                                        <h5 class="fw-bold mb-0">56.34%</h5>
                                    </div>
                                </div>
                                <div class="col-6 col-xl-6 col-xxl-3 d-sm-flex align-items-center mb-3">
                                    <div class="p-2 me-2 me-xxl-3 bg-primary-300 rounded">
                                        <span class="peity-bar"
                                              data-peity='{ "fill": ["#fff"], "width": 27, "height": 27 }'>3,4,3,5,5</span>
                                    </div>
                                    <div class="d-flex flex-column align-items-start justify-content-center">
                                        <label class="fs-xs mb-0">New Sessions</label>
                                        <h5 class="fw-bold mb-0">12.17%</h5>
                                    </div>
                                </div>
                                <div class="col-6 col-xl-6 col-xxl-3 d-sm-flex align-items-center mb-3">
                                    <div class="p-2 me-2 me-xxl-3 bg-success-300 rounded">
                                        <span class="peity-bar"
                                              data-peity='{ "fill": ["#fff"], "width": 27, "height": 27 }'>6,4,7,5,6</span>
                                    </div>
                                    <div class="d-flex flex-column align-items-start justify-content-center">
                                        <label class="fs-xs mb-0">Clickthrough</label>
                                        <h5 class="fw-bold mb-0">19.77%</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="efficiency-metrics-chart"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-12">
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2> Sales <span class="fw-light"><i>Performance</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button class="btn btn-panel" data-action="panel-collapse" data-bs-offset="0,10"
                                data-bs-original-title="Toggle" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button class="btn btn-panel" data-action="panel-fullscreen" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <table class="table table-bordered table-sm" id="sales-performance-table">
                            <thead></thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/easypiechart.js', 'resources/scripts/pages/marketingdashboard.js'])
@endsection
