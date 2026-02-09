@extends('layouts.vertical', ['pageTitle' => 'Easy Pie Chart'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Easy Pie Chart', 'pageSubTitle1' => 'Data Visualization', 'pageSubTitle2'=> 'Data Bites', 'pageSubText' => 'Lightweight plugin to render simple, animated and retina optimized pie charts.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div class="panel" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Color <span class="fw-300"><i>Change</i></span>
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
                        <button class="btn btn-panel" data-action="panel-close" data-bs-offset="0,10"
                                data-bs-original-title="Close" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Colors are inherited from the parent font color, so you could literally change the any of
                            the
                            font colors available within this WebApp <code>.js-easy-pie-chart.color-danger-500</code>
                        </div>
                        <div class="demo">
                            <div class="js-easy-pie-chart text-danger d-inline-flex" data-percent="30" data-piesize="95"
                                 data-scalelength="3"></div>
                            <div class="js-easy-pie-chart text-success d-inline-flex" data-percent="60"
                                 data-piesize="95"
                                 data-scalelength="3"></div>
                            <div class="js-easy-pie-chart text-primary d-inline-flex" data-percent="90"
                                 data-piesize="95"
                                 data-scalelength="3"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Scale <span class="fw-300"><i>&amp; Color</i></span>
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
                        <button class="btn btn-panel" data-action="panel-close" data-bs-offset="0,10"
                                data-bs-original-title="Close" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Change the size of the chart using <code>data-piesize</code> and the line width by using
                            <code>data-linewidth</code>
                        </div>
                        <div class="demo">
                            <div class="js-easy-pie-chart text-danger d-inline-flex" data-linewidth="40"
                                 data-percent="30"
                                 data-piesize="95" data-scalelength="2"></div>
                            <div class="js-easy-pie-chart text-success d-inline-flex" data-linewidth="20"
                                 data-percent="60"
                                 data-piesize="80" data-scalelength="2"></div>
                            <div class="js-easy-pie-chart text-primary d-inline-flex" data-linewidth="5"
                                 data-percent="75" data-piesize="50" data-scalelength="2"></div>
                            <div class="js-easy-pie-chart text-info d-inline-flex" data-linewidth="3" data-percent="95"
                                 data-piesize="30" data-scalelength="2"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Data <span class="fw-300"><i>Percentage</i></span>
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
                        <button class="btn btn-panel" data-action="panel-close" data-bs-offset="0,10"
                                data-bs-original-title="Close" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Data percentage is displayed by using various class utilities and a container with the class
                            <code>.js-percent</code>
                        </div>
                        <div class="demo">
                            <div
                                class="js-easy-pie-chart text-primary position-relative d-inline-flex align-items-center justify-content-center"
                                data-linecap="round" data-linewidth="5" data-percent="86" data-piesize="95"
                                data-scalelength="7">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-xl">
                                    <span class="js-percent d-block text-dark"></span>
                                    <div class="d-block fs-xs text-dark opacity-70">
                                        <small>Memory</small>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="js-easy-pie-chart text-primary position-relative d-inline-flex align-items-center justify-content-center"
                                data-linecap="round" data-linewidth="5" data-percent="46" data-piesize="80"
                                data-scalelength="5">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-lg">
                                    <span class="js-percent d-block text-dark"></span>
                                    <div class="d-block fs-xs text-dark opacity-70">
                                        <small>CPU</small>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="js-easy-pie-chart text-primary position-relative d-inline-flex align-items-center justify-content-center"
                                data-linecap="round" data-linewidth="5" data-percent="56" data-piesize="70"
                                data-scalelength="4">
                                <div
                                    class="d-flex align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fs-md">
                                    <span class="js-percent d-block"></span>
                                </div>
                            </div>
                            <div
                                class="js-easy-pie-chart text-primary position-relative d-inline-flex align-items-center justify-content-center"
                                data-linecap="round" data-linewidth="5" data-percent="36" data-piesize="60"
                                data-scalelength="3">
                                <span
                                    class="js-percent d-flex align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-xs"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Avatar <span class="fw-300"><i>Usage</i></span>
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
                        <button class="btn btn-panel" data-action="panel-close" data-bs-offset="0,10"
                                data-bs-original-title="Close" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Here we nested the image using various class utilities to center it within the graph. The
                            graph
                            is also using <code>data-scalelength</code> to change the scales to various sizes
                        </div>
                        <div class="demo">
                            <div
                                class="js-easy-pie-chart text-danger position-relative d-inline-flex align-items-center justify-content-center"
                                data-linewidth="10" data-percent="75" data-piesize="95" data-scalelength="5">
                                <div
                                    class="d-flex align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom">
                                    <img alt="Dr. Codex Lantern" class="profile-image rounded-circle"
                                         src="/img/demo/avatars/avatar-admin.png"/>
                                </div>
                            </div>
                            <div
                                class="js-easy-pie-chart text-success position-relative d-inline-flex align-items-center justify-content-center"
                                data-linewidth="7" data-percent="86" data-piesize="80" data-scalelength="4">
                                <div
                                    class="d-flex align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom">
                                    <img alt="Dr. Codex Lantern" class="profile-image-md rounded-circle"
                                         src="/img/demo/avatars/avatar-admin.png"/>
                                </div>
                            </div>
                            <div
                                class="js-easy-pie-chart text-info position-relative d-inline-flex align-items-center justify-content-center"
                                data-linewidth="3" data-percent="65" data-piesize="50" data-scalecolor="#fff"
                                data-scalelength="1">
                                <div
                                    class="d-flex align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom">
                                    <img alt="Dr. Codex Lantern" class="profile-image-sm rounded-circle"
                                         src="/img/demo/avatars/avatar-admin.png"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Nested <span class="fw-300"><i>Charts</i></span>
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
                        <button class="btn btn-panel" data-action="panel-close" data-bs-offset="0,10"
                                data-bs-original-title="Close" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Just as the example above, we can create nested charts by using the
                            <code>position-absolute</code> utility
                        </div>
                        <div class="demo">
                            <div
                                class="js-easy-pie-chart text-danger position-relative d-inline-flex align-items-center justify-content-center"
                                data-linewidth="10" data-percent="35" data-piesize="95" data-scalelength="5">
                                <div
                                    class="js-easy-pie-chart text-info position-relative position-absolute pos-left pos-right pos-top pos-bottom d-flex align-items-center justify-content-center"
                                    data-linewidth="5" data-percent="65" data-piesize="60" data-scalecolor="#fff"
                                    data-scalelength="1">
                                    <div
                                        class="js-easy-pie-chart text-success position-relative position-absolute pos-left pos-right pos-top pos-bottom d-flex align-items-center justify-content-center"
                                        data-linewidth="3" data-percent="35" data-piesize="40" data-scalecolor="#fff"
                                        data-scalelength="1">
                                        <span
                                            class="js-percent d-flex align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-xs"></span>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="js-easy-pie-chart text-primary position-relative d-inline-flex align-items-center justify-content-center"
                                data-linewidth="10" data-percent="35" data-piesize="95" data-scalelength="5">
                                <div
                                    class="js-easy-pie-chart text-primary position-relative position-absolute pos-left pos-right pos-top pos-bottom d-flex align-items-center justify-content-center"
                                    data-linewidth="5" data-percent="65" data-piesize="60" data-scalecolor="#fff"
                                    data-scalelength="1">
                                    <div
                                        class="js-easy-pie-chart color-primary-400 position-relative position-absolute pos-left pos-right pos-top pos-bottom d-flex align-items-center justify-content-center"
                                        data-linewidth="3" data-percent="35" data-piesize="40" data-scalecolor="#fff"
                                        data-scalelength="1">
                                        <span
                                            class="js-percent d-flex align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-xs text-primary"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Dynamic <span class="fw-300"><i>Usage</i></span>
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
                        <button class="btn btn-panel" data-action="panel-close" data-bs-offset="0,10"
                                data-bs-original-title="Close" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="d-flex flex-column align-items-center" id="update-chart">
                            <div
                                class="js-easy-pie-chart text-primary position-relative d-flex align-items-center justify-content-center"
                                data-linewidth="20" data-percent="50" data-piesize="145" data-scalelength="8"
                                data-trackcolor="var(--bs-border-color)">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-xl">
                                    <span class="js-percent d-block text-dark"></span>
                                    <div class="d-block fs-xs text-dark opacity-70">
                                        <small>Random</small>
                                    </div>
                                </div>
                            </div>
                            <button class="js-update btn btn-outline-primary mt-3" type="button">Update Chart</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/easypiechart.js'])
@endsection
