@extends('layouts.vertical', ['pageTitle' => 'Position'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Position', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'System Utilities', 'pageSubText' => 'Bootstrap position utilities allow you to control the positioning of elements using CSS properties like relative, absolute, fixed, sticky, and static to create flexible layouts.'])
@endsection

@section('content')
    <div class="info-container">
        <p class="mb-0">
            Positions can also be changed for mobile viewports by adding the following classes, <code>.position-on-mobile-absolute</code>,
            <code>.position-on-mobile-relative</code>, and <code>.position-on-mobile-static</code>
        </p>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Position <span class="fw-300"><i>Fixed</i></span>
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
                            An element with <code>.position-fixed</code>; is positioned relative to the viewport, which
                            means it always stays in the same place even if the page is scrolled. The <code>top</code>,
                            <code>right</code>, <code>bottom</code>, and <code>left</code> properties are used to
                            position the element
                        </div>
                        <button class="btn btn-outline-danger hidden-sm-down d-flex align-items-center"
                                data-bs-content='&lt;img src="/img/demo/position-fixed.gif" alt="" style="width:360px"&gt;'
                                data-bs-html="true" data-bs-placement="top"
                                data-bs-template='&lt;div class="popover max-width-reset" role="tooltip"&gt;&lt;div class="arrow"&gt;&lt;/div&gt;&lt;div class="popover-body p-3"&gt;&lt;/div&gt;&lt;/div&gt;'
                                data-bs-toggle="popover" data-bs-trigger="hover" title="" type="button"><i
                                class="sa sa-control-play me-2"></i>Live example
                        </button>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Position <span class="fw-300"><i>Relative</i></span>
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
                            In <code>.position-relative</code>, the element is positioned relative to itself. However,
                            an absolute positioned element is relative to its parent. An element with <code>position:
                                absolute</code> is removed from the normal document flow. ... If it doesn't have any
                            parent elements, then the initial document <code>html</code> will be its parent
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="demo-window rounded shadow-1 mb-4 mb-sm-0">
                                    <div class="demo-window-content">
                                        <div class="d-flex flex-row" style="height:15rem">
                                            <div
                                                class="flex-grow-1 w-100 bg-info-50 p-3 position-relative d-flex flex-column">
                                                <div class="border border-danger flex-grow-1 p-2 position-relative">
                                                    .position-relative
                                                    <p class="position-absolute pos-right pos-bottom p-2 bg-fusion-500 m-0">
                                                        .position-absolute</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="demo-window rounded shadow-1">
                                    <div class="demo-window-content">
                                        <div class="d-flex flex-row" style="height:15rem">
                                            <div
                                                class="w-100 flex-grow-1 bg-success-50 p-3 position-relative d-flex flex-column">
                                                <div
                                                    class="border border-danger flex-grow-1 p-5 position-relative d-flex flex-column">
                                                    <div class="position-absolute pos-top pos-left p-2">
                                                        .position-relative
                                                    </div>
                                                    <div class="border border-danger flex-grow-1 p-2 position-relative">
                                                        .position-relative
                                                        <p class="position-absolute pos-right pos-bottom p-2 bg-fusion-500 m-0">
                                                            .position-absolute</p>
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
            </div>
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Position <span class="fw-300"><i>Absolute</i></span>
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
                            An element with <code>.position-absolute</code>; is positioned relative to the nearest
                            positioned ancestor (instead of positioned relative to the viewport, like fixed). However;
                            if an absolute positioned element has no positioned ancestors, it uses the document body,
                            and moves along with page scrolling. You can use <code>.pos-top</code>,
                            <code>.pos-right</code>, <code>.pos-bottom</code>, and <code>.pos-left</code> to position
                            relavant to its closest relative positioned ancenstor
                        </div>
                        <div class="demo-window rounded mb-g shadow-1">
                            <div class="demo-window-content">
                                <div class="position-relative" style="height:15rem">
                                    <div class="bg-primary-50 p-2 position-absolute w-100 pos-top">
                                        .position-absolute .pos-top
                                    </div>
                                    <div class="bg-info-50 p-2 position-absolute w-100 pos-bottom">
                                        .position-absolute .pos-bottom
                                    </div>
                                    <div
                                        class="bg-danger-50 p-2 position-absolute h-100 pos-right d-flex align-items-center justify-content-center"
                                        style="width:120px;">
                                        <span class="rotate-270 text-nowrap">.position-absolute .pos-right</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-outline-danger hidden-sm-down d-flex align-items-center"
                                data-content='&lt;img src="/img/demo/position-absolute.gif" alt="" style="width:360px"&gt;'
                                data-html="true" data-placement="top"
                                data-template='&lt;div class="popover max-width-reset" role="tooltip"&gt;&lt;div class="arrow"&gt;&lt;/div&gt;&lt;div class="popover-body p-3"&gt;&lt;/div&gt;&lt;/div&gt;'
                                data-toggle="popover" data-trigger="hover" title="" type="button"><i
                                class="sa sa-control-play me-2"></i>Live example
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Position <span class="fw-300"><i>Sticky</i></span>
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
                            <p>Position an element at the top of the viewport, from edge to edge, but only after you
                                scroll past it. The <code>.sticky-top</code> utility uses CSS’s <code>position:
                                    sticky</code>, which isn’t fully supported in all browsers.</p>
                        </div>
                        <div class="demo-window rounded shadow-1 mb-g">
                            <div class="demo-window-content">
                                <div class="d-flex flex-row">
                                    <div class="position-relative overflow-auto p-3" style="height: 300px;">
                                        <h5 class="fw-500 opacity-70">
                                            Sticky Element: Scroll Down to See the Effect
                                            <small>
                                                Scroll down this page to see how sticky positioning works
                                            </small>
                                        </h5>
                                        <nav class="navbar navbar-expand-lg sticky-top bg-faded">
                                            <a class="navbar-brand d-flex align-items-center fw-500" href="#">
                                                SmartAdmin v5
                                            </a>
                                            <button aria-expanded="false" aria-label="Toggle navigation"
                                                    class="navbar-toggler" data-target="#navbarSticky"
                                                    data-toggle="collapse" type="button">
                                                <span class="navbar-toggler-icon"></span>
                                            </button>
                                            This will be sticky
                                        </nav>
                                        <br/>
                                        <p class="opacity-70">Some example text..</p>
                                        <h5 class="opacity-70">Scroll back up again to "remove" the sticky
                                            position.</h5>
                                        <p class="opacity-50">Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                            Phasellus imperdiet, nulla et dictum interdum, nisi lorem egestas odio,
                                            vitae scelerisque enim ligula venenatis dolor. Maecenas nisl est,
                                            ultrices</p>
                                        <p>1..</p>
                                        <p>...</p>
                                        <p>...</p>
                                        <p>...</p>
                                        <p>4..</p>
                                        <p>...</p>
                                        <p>...</p>
                                        <p>...</p>
                                        <p>8..</p>
                                        <p>...</p>
                                        <p>...</p>
                                        <p>...</p>
                                        <p>12.</p>
                                        <p>...</p>
                                        <p>...</p>
                                        <p>...</p>
                                        <p>16.</p>
                                        <p>...</p>
                                        <p>...</p>
                                        <p>...</p>
                                        <p>20.</p>
                                        <p>...</p>
                                        <p>...</p>
                                        <p>...</p>
                                        <p>24.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-outline-danger hidden-sm-down d-flex align-items-center"
                                data-content='&lt;img src="/img/demo/position-sticky.gif" alt="" style="width:360px"&gt;'
                                data-html="true" data-placement="top"
                                data-template='&lt;div class="popover max-width-reset" role="tooltip"&gt;&lt;div class="arrow"&gt;&lt;/div&gt;&lt;div class="popover-body p-3"&gt;&lt;/div&gt;&lt;/div&gt;'
                                data-toggle="popover" data-trigger="hover" title="" type="button"><i
                                class="sa sa-control-play me-2"></i>Live example
                        </button>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Position <span class="fw-300"><i>Static</i></span>
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
                            Every element has a static position by default, so the element will stick to the normal page
                            flow. So if there is a left/right/top/bottom/z-index set then there will be no effect on
                            that element. Adding <code>.position-static</code> will generally 'reset' these properties
                        </div>
                        <div class="demo-window rounded shadow-1 mb-g">
                            <div class="demo-window-content">
                                <div class="d-flex flex-row" style="height:15rem">
                                    <div
                                        class="w-100 flex-grow-1 bg-highlight p-3 position-relative d-flex flex-column">
                                        <div
                                            class="border border-danger flex-grow-1 p-5 position-relative d-flex flex-column">
                                            <div class="position-absolute pos-top pos-left p-2">.position-relative</div>
                                            <div class="border border-danger flex-grow-1 p-2" id="js-position-change">
                                                <strong class="text-danger"
                                                        id="js-position-text">.position-static</strong>
                                                <p class="position-absolute pos-right pos-bottom p-2 bg-fusion-500 m-0">
                                                    .position-absolute</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-outline-success" id="js-position-btn"
                                type="button">Change to RELATIVE
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite('resources/scripts/pages/position.js')
@endsection
