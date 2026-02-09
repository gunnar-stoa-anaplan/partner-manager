@extends('layouts.vertical', ['pageTitle' => 'Panels'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle' => 'Panels', 'pageSubTitle1' => 'Design', 'pageSubTitle2' => 'UI Components', 'pageSubText' => 'Panels can be used in almost any situation, helping wrap everything in a slick & lightweight container.'])
@endsection

@section('content')
        <div class="info-container">
            <p class="mb-0">
                Display data, tables, forms, or images effortlessly with our <strong>lightweight, clean, and highly
                    customizable panels</strong>.
                Expand to fullscreen, collapse, or close as needed. Equiped with many <strong>interactive features</strong>,
                a versatile design, these panels adapt to your workflow seamlessly.
                <strong>Auto-save keeps your layout intact</strong>, while <strong>SortableJS</strong> lets you drag and
                organize panels seamlessly.
                Need to print? Just switch to fullscreen for a perfect view. All panels can be reset to their default state
                by clicking the button below.
            </p>

        </div>
        <div class="row" id="sortable-grid">
            <div class="col-sm-12 col-md-6 col-xl-6">
                <!--Default panel-->
                <div class="panel panel-icon" id="panel-1">
                    <div class="panel-hdr">
                        <h2>
                            Blank <span class="fw-light"><i>Panel</i></span>
                        </h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                All panels needs to have an unique ID in order to use the panel sorting funtions.
                            </div>
                            <p class="mb-0">Panel content goes here...</p>
                        </div>
                    </div>
                </div>
                <!--Collapsed panel-->
                <div class="panel panel-icon panel-collapsed" id="panel-2">
                    <div class="panel-hdr">
                        <h2>
                            Collapsed <span class="fw-light"><i>Panel</i></span>
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
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                This panel is closed by default. To close a panel we add the class
                                <code>.panel-collapsed</code> to <code>.panel</code>
                            </div>
                            <p class="mb-0">
                                Default panel text.
                            </p>
                        </div>
                    </div>
                </div>
                <!--Collapsable panel-->
                <div class="panel panel-icon" id="panel-3">
                    <div class="panel-hdr">
                        <h2>
                            Collapsable <span class="fw-light"><i>Panel</i></span>
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
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                Panel is closed by using the attribute <code>data-action="panel-collapse"</code>, and can be
                                placed anywhere inside <code>.panel</code>
                            </div>
                            <p>
                                You can also place the collapse action inside the <code>.panel-content</code> as button or
                                link
                            </p>
                            <button class="btn btn-success me-2" data-action="panel-collapse" type="button">collapse
                                button
                            </button>
                            <a class="text-success" data-action="panel-collapse" href="javascript:void(0);">collapse
                                link</a>
                        </div>
                    </div>
                </div>
                <!--Fullscreen panel-->
                <div class="panel panel-icon" id="panel-4">
                    <div class="panel-hdr">
                        <h2>
                            Fullscreen <span class="fw-light"><i>Panel</i></span>
                        </h2>
                        <div class="panel-toolbar">
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
                            <div class="panel-tag">
                                Panel is closed by using the attribute <code>data-action="panel-fullscreen"</code>, and can
                                be placed anywhere inside <code>.panel</code>
                            </div>
                            <p>
                                You can also place the collapse action inside the <code>.panel-content</code> as button or
                                link
                            </p>
                            <button class="btn btn-warning me-2" data-action="panel-fullscreen" type="button">fullscreen
                                button
                            </button>
                            <a class="text-warning" data-action="panel-fullscreen" href="javascript:void(0);">fullscreen
                                link</a>
                        </div>
                    </div>
                </div>
                <!--Close panel-->
                <div class="panel panel-icon" id="panel-5">
                    <div class="panel-hdr">
                        <h2>
                            Close <span class="fw-light"><i>Panel</i></span>
                        </h2>
                        <div class="panel-toolbar">
                            <button class="btn btn-panel" data-action="panel-close" data-bs-offset="0,10"
                                    data-bs-original-title="Close" data-bs-placement="top" data-bs-toggle="tooltip"
                                    type="button">
                                <svg class="sa-icon">
                                    <use href="/icons/sprite.svg#x-circle"></use>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                Panel is closed by using the attribute <code>data-action="panel-close"</code>, and can be
                                placed anywhere inside <code>.panel</code>
                            </div>
                            <p>
                                You can also place the collapse action inside the <code>.panel-content</code> as button or
                                link
                            </p>
                            <button class="btn btn-danger me-2" data-action="panel-close" type="button">close button
                            </button>
                            <a class="text-danger" data-action="panel-close" href="javascript:void(0);">close link</a>
                        </div>
                    </div>
                </div>
                <!--Panel colors-->
                <div class="panel panel-icon" id="panel-6">
                    <div class="panel-hdr">
                        <h2>
                            Panel <span class="fw-light"><i>Colors</i></span>
                        </h2>
                        <div class="panel-toolbar">
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                You can mix and match any color styles, below is what we were found to be an interesting
                                match.
                            </div>
                            <div class="d-flex flex-wrap justify-content-evenly gap-2 mb-3">
                                <button
                                    class="btn d-inline-block bg-primary-700 bg-success-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-primary-700 bg-success-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-primary-500 bg-info-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-primary-500 bg-info-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-primary-600 bg-primary-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-primary-600 bg-primary-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-info-600 bg-primray-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-info-600 bg-primray-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-info-600 bg-info-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-info-600 bg-info-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-info-700 bg-success-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-info-700 bg-success-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-success-900 bg-info-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-success-900 bg-info-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-success-700 bg-primary-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-success-700 bg-primary-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-success-600 bg-success-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-success-600 bg-success-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-danger-900 bg-info-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-danger-900 bg-info-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-success-500 bg-danger-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-success-500 bg-danger-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-success-300 bg-warning-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-success-300 bg-warning-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-success-200 bg-fusion-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-success-200 bg-fusion-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-primary-800 bg-danger-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-primary-800 bg-danger-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-primary-400 bg-warning-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-primary-400 bg-warning-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-primary-300 bg-fusion-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-primary-300 bg-fusion-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-info-500 bg-danger-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-info-500 bg-danger-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-info-800 bg-warning-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-info-800 bg-warning-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-info-200 bg-success-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-info-200 bg-success-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-danger-700 bg-primary-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-danger-700 bg-primary-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-danger-500 bg-info-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-danger-500 bg-info-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-danger-300 bg-success-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-danger-300 bg-success-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-warning-700 bg-primary-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-warning-700 bg-primary-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-warning-500 bg-info-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-warning-500 bg-info-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-warning-300 bg-danger-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-warning-300 bg-danger-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-faded bg-primary-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-faded bg-primary-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-faded bg-info-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-faded bg-info-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-faded bg-danger-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-faded bg-danger-gradient"
                                    type="button"></button>
                                <button
                                    class="btn d-inline-block bg-fusion-400 bg-fusion-gradient width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                    data-action="panel-style" data-panel-style="bg-fusion-400 bg-fusion-gradient"
                                    type="button"></button>
                                <button class="btn d-inline-block bg-faded width-3 height-3 p-0 rounded-0 hover-effect-dot"
                                        data-action="panel-style" data-panel-style="bg-faded" type="button"></button>
                            </div>
                            <div class="d-flex pt-3">
                                <button class="btn btn-outline-secondary" data-action="panel-reset" type="button">
                                    Reset Styles
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Master button-->
                <div class="panel panel-icon" id="panel-7">
                    <div class="panel-hdr">
                        <h2>
                            Master <span class="fw-light"><i>Button</i></span>
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
                        </div>
                        <!-- master button needs to be the last child in .panel-hdr -->
                        <div class="panel-toolbar">
                            <button class="btn btn-toolbar-master px-0" data-bs-toggle="dropdown" type="button">
                                <i class="ellipsis-icon"></i>
                            </button>
                            <!-- included a wrapper here to make the dropdown menu animate and also respond to hitting edges -->
                            <div class="dropdown-menu-animated">
                                <div class="dropdown-menu dropdown-menu-end rounded-top rounded-bottom">
                                    <button class="dropdown-item" data-action="panel-refresh"
                                            data-refresh-callback="updatePanelContent" data-refresh-duration="2000"
                                            type="button"><span data-i18n="drpdwn.refreshpanel">Refresh Content</span>
                                    </button>
                                    <button class="dropdown-item" data-action="panel-toggle"
                                            data-panel-toggle="panel-locked" type="button">
                                        <span class="panel-locked-text" data-i18n="drpdwn.lockpanel">Unlock panel</span>
                                        <span class="panel-unlock-text" data-i18n="drpdwn.unlockpanel">Lock panel</span>
                                    </button>
                                    <div class="dropdown-multilevel dropdown-multilevel-left">
                                        <div class="dropdown-item d-flex justify-content-between align-items-center">
                                            <span data-i18n="drpdwn.panelcolor">Panel Style</span>
                                            <i class="sa sa-chevron-right"></i>
                                        </div>
                                        <div class="dropdown-menu">
                                            <div
                                                class="d-flex flex-wrap align-items-center justify-content-center px-2 py-1 width-sm gap-1">
                                                <button
                                                    class="btn d-inline-block bg-primary-700 bg-success-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-primary-700 bg-success-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-primary-500 bg-info-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-primary-500 bg-info-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-primary-600 bg-primary-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-primary-600 bg-primary-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-info-600 bg-primray-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-info-600 bg-primray-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-info-600 bg-info-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-info-600 bg-info-gradient" type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-info-700 bg-success-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-info-700 bg-success-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-success-900 bg-info-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-success-900 bg-info-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-success-700 bg-primary-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-success-700 bg-primary-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-success-600 bg-success-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-success-600 bg-success-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-danger-900 bg-info-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-danger-900 bg-info-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-fusion-400 bg-fusion-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-fusion-400 bg-fusion-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-faded width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style" data-panel-style="bg-faded"
                                                    type="button"></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dropdown-multilevel dropdown-multilevel-left">
                                        <div class="dropdown-item d-flex justify-content-between align-items-center">
                                            <span data-i18n="drpdwn.actions">Actions</span>
                                            <i class="sa sa-chevron-right"></i>
                                        </div>
                                        <div class="dropdown-menu">
                                            <button class="dropdown-item px-3" data-action="panel-collapse" type="button">
                                                <div class="d-flex align-items-center">
                                                    <svg class="sa-icon sa-icon-success me-2">
                                                        <use class="panel-collapsed-icon"
                                                             href="/img/sprite.svg#minus-circle"></use>
                                                        <use class="panel-expand-icon"
                                                             href="/img/sprite.svg#plus-circle"></use>
                                                    </svg>
                                                    Toggle
                                                </div>
                                            </button>
                                            <button class="dropdown-item px-3" data-action="panel-fullscreen" type="button">
                                                <div class="d-flex align-items-center">
                                                    <svg class="sa-icon sa-icon-warning me-2">
                                                        <use href="/icons/sprite.svg#stop-circle"></use>
                                                    </svg>
                                                    Fullscreen
                                                </div>
                                            </button>
                                            <button class="dropdown-item px-3 text-danger" data-action="panel-close"
                                                    type="button">
                                                <div class="d-flex align-items-center">
                                                    <svg class="sa-icon sa-icon-danger me-2">
                                                        <use href="/icons/sprite.svg#x-circle"></use>
                                                    </svg>
                                                    Close
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                    <button class="dropdown-item" type="button"><span data-i18n="drpdwn.custombutton">Custom Button</span>
                                    </button>
                                    <button class="dropdown-item" data-action="panel-toggle" data-panel-toggle="panel-icon"
                                            type="button">
                                        <span data-i18n="drpdwn.iconstyle">Icon Style</span>
                                    </button>
                                    <div class="dropdown-divider m-0"></div>
                                    <button class="dropdown-item fw-500 text-danger" data-action="panel-reset"
                                            type="button">
                                        <span data-i18n="drpdwn.resetpanel">Reset Panel</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                The <code>.btn-toolbar-master</code> class enhances <code>.panel-toolbar</code> by
                                integrating dropdown functionality and additional actions while optimizing space.
                                It supports icons, grouped actions, submenus, and input fields, making panels more
                                interactive and organized.
                            </div>
                            <div class="d-flex">
                                <div
                                    class="dropdown-menu dropdown-menu-end rounded-top rounded-bottom show position-relative border"
                                    style="box-shadow:none; width: 10rem;">
                                    <button class="dropdown-item" data-action="panel-refresh"
                                            data-refresh-callback="updatePanelContent" data-refresh-duration="2000"
                                            type="button"><span data-i18n="drpdwn.refreshpanel">Refresh Content</span>
                                    </button>
                                    <button class="dropdown-item" data-action="panel-toggle"
                                            data-panel-toggle="panel-locked" type="button">
                                        <span class="panel-locked-text" data-i18n="drpdwn.lockpanel">Unlock panel</span>
                                        <span class="panel-unlock-text" data-i18n="drpdwn.unlockpanel">Lock panel</span>
                                    </button>
                                    <div class="dropdown-multilevel dropdown-multilevel-right">
                                        <div class="dropdown-item d-flex justify-content-between align-items-center">
                                            <span data-i18n="drpdwn.panelcolor">Panel Style</span>
                                            <i class="sa sa-chevron-right"></i>
                                        </div>
                                        <div class="dropdown-menu">
                                            <div
                                                class="d-flex flex-wrap align-items-center justify-content-center px-2 py-1 width-sm gap-1">
                                                <button
                                                    class="btn d-inline-block bg-primary-700 bg-success-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-primary-700 bg-success-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-primary-500 bg-info-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-primary-500 bg-info-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-primary-600 bg-primary-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-primary-600 bg-primary-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-info-600 bg-primray-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-info-600 bg-primray-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-info-600 bg-info-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-info-600 bg-info-gradient" type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-info-700 bg-success-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-info-700 bg-success-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-success-900 bg-info-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-success-900 bg-info-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-success-700 bg-primary-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-success-700 bg-primary-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-success-600 bg-success-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-success-600 bg-success-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-danger-900 bg-info-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-danger-900 bg-info-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-fusion-400 bg-fusion-gradient width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style"
                                                    data-panel-style="bg-fusion-400 bg-fusion-gradient"
                                                    type="button"></button>
                                                <button
                                                    class="btn d-inline-block bg-faded width-2 height-2 p-0 rounded-0 hover-effect-dot"
                                                    data-action="panel-style" data-panel-style="bg-faded"
                                                    type="button"></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dropdown-multilevel dropdown-multilevel-right">
                                        <div class="dropdown-item d-flex justify-content-between align-items-center">
                                            <span data-i18n="drpdwn.actions">Actions</span>
                                            <i class="sa sa-chevron-right"></i>
                                        </div>
                                        <div class="dropdown-menu">
                                            <button class="dropdown-item px-3" data-action="panel-collapse" type="button">
                                                <div class="d-flex align-items-center">
                                                    <svg class="sa-icon sa-icon-success me-2">
                                                        <use class="panel-collapsed-icon"
                                                             href="/img/sprite.svg#minus-circle"></use>
                                                        <use class="panel-expand-icon"
                                                             href="/img/sprite.svg#plus-circle"></use>
                                                    </svg>
                                                    Toggle
                                                </div>
                                            </button>
                                            <button class="dropdown-item px-3" data-action="panel-fullscreen" type="button">
                                                <div class="d-flex align-items-center">
                                                    <svg class="sa-icon sa-icon-warning me-2">
                                                        <use href="/icons/sprite.svg#stop-circle"></use>
                                                    </svg>
                                                    Fullscreen
                                                </div>
                                            </button>
                                            <button class="dropdown-item px-3 text-danger" data-action="panel-close"
                                                    type="button">
                                                <div class="d-flex align-items-center">
                                                    <svg class="sa-icon sa-icon-danger me-2">
                                                        <use href="/icons/sprite.svg#x-circle"></use>
                                                    </svg>
                                                    Close
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                    <button class="dropdown-item" type="button"><span data-i18n="drpdwn.custombutton">Custom Button</span>
                                    </button>
                                    <button class="dropdown-item" data-action="panel-toggle" data-panel-toggle="panel-icon"
                                            type="button">
                                        <span data-i18n="drpdwn.iconstyle">Icon Style</span>
                                    </button>
                                    <div class="dropdown-divider m-0"></div>
                                    <button class="dropdown-item fw-500 text-danger" data-action="panel-reset"
                                            type="button">
                                        <span data-i18n="drpdwn.resetpanel">Reset Panel</span>
                                    </button>
                                </div>
                            </div>
                            <div class="js-refreshed-content badge bg-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-xl-6">
                <!--Panel label-->
                <div class="panel panel-icon" id="panel-8">
                    <div class="panel-hdr">
                        <h2>
                            Label <span class="fw-light"><i>Panel</i></span>
                            <sup>
    <span class="badge text-bg-warning ms-2">
                                                        sup
                                                    </span>
                            </sup>
                        </h2>
                        <div class="panel-toolbar">
                            <div class="panel-toolbar">
    <span class="badge text-bg-primary fw-400">
                                                        Panel Badge
                                                    </span>
                            </div>
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                You may place badge inside <code>.panel-toolbar</code>, <code>.panel-content</code> and
                                <code>.panel-hdr</code>.
                                Check out the <a href="{{ route('second', ['ui', 'badges']) }}" target="_blank">badges page</a> to learn more about
                                badges
                            </div>
                        </div>
                        <!-- panel footer with utility classes -->
                        <div
                            class="panel-content py-2 rounded-bottom border-faded border-left-0 border-right-0 border-bottom-0 text-muted d-flex"
                            style="gap: 5px;">
    <span class="badge text-bg-danger">
                                                    Footer badge
                                                </span>
                            <span class="badge rounded-pill text-bg-success">
                                                    Pill badge
                                                </span>
                            <span class="badge rounded-pill rounded-circle text-bg-secondary ms-auto">
                                                    1
                                                </span>
                            <span class="badge text-bg-warning px-1">
                                                    22
                                                </span>
                        </div>
                    </div>
                </div>
                <!--Form elements-->
                <div class="panel panel-icon" id="panel-9">
                    <div class="panel-hdr">
                        <h2>
                            Form <span class="fw-light"><i>elements</i></span>
                        </h2>
                        <div class="panel-toolbar">
                            <div class="form-check form-check-reverse form-switch mb-0">
                                <input class="form-check-input" id="flexSwitchCheckDefault" role="switch" type="checkbox"/>
                                <label class="form-check-label" for="flexSwitchCheckDefault">Panel switch</label>
                            </div>
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                You can add form elements to <code>.panel-hdr</code> and any other parts of the
                                <code>.panel</code>
                            </div>
                        </div>
                        <div
                            class="panel-content py-2 rounded-bottom border-faded border-end-0 border-start-0 border-bottom-0 text-muted d-flex">
                            <div class="form-check mb-0">
                                <input class="form-check-input" id="flexCheckDefault" type="checkbox" value=""/>
                                <label class="form-check-label" for="flexCheckDefault">
                                    Panel checkbox
                                    <label>
                                    </label></label></div>
                        </div>
                    </div>
                </div>
                <!--Form inputs-->
                <div class="panel panel-icon" id="panel-10">
                    <div class="panel-hdr">
                        <h2>
                            Form <span class="fw-light"><i>Inputs</i></span>
                        </h2>
                        <div class="panel-toolbar gap-2">
                            <select class="form-select form-select-sm">
                                <option selected="">Select</option>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                            <div class="d-flex position-relative ms-auto width-sm">
                                <svg class="sa-icon sa-icon-1x translate-middle position-absolute top-50 ms-3">
                                    <use href="/icons/sprite.svg#search"></use>
                                </svg>
                                <input class="form-control form-control-sm" placeholder="Search" style="padding-left:1.7rem"
                                       type="text"/>
                            </div>
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                You can add form inputs to <code>.panel-hdr</code> with moderation. Adding too many elements
                                may break the design on mobile viewport
                            </div>
                        </div>
                    </div>
                </div>
                <!--Panel buttons -->
                <div class="panel panel-icon" id="panel-11">
                    <div class="panel-hdr">
                        <h2>
                            Panel <span class="fw-light"><i>Buttons</i></span>
                        </h2>
                        <div class="panel-toolbar">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-secondary" type="button">Small split button</button>
                                <button aria-expanded="false"
                                        class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" type="button">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Action</a></li>
                                    <li><a class="dropdown-item" href="#">Another action</a></li>
                                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li><a class="dropdown-item" href="#">Separated link</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                Add panel buttons to <code>.panel-hdr</code> and footer area. Keep in mind of your panel
                                title size as distortion can occur with too many buttons overflowing.
                            </div>
                        </div>
                        <div
                            class="panel-content py-2 rounded-bottom bg-faded border-faded border-start-0 border-end-0 border-bottom-0 text-muted d-flex">
                            <ul class="pagination pagination-sm justify-content-start mb-0">
                                <li class="page-item disabled"><a class="page-link">Prev</a></li>
                                <li class="page-item"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!--Panel tabs-->
                <div class="panel panel-icon" id="panel-12">
                    <div class="panel-hdr border-bottom">
                        <h2>
                            Panel <span class="fw-light"><i>Tabs</i></span>
                        </h2>
                        <div class="panel-toolbar pr-3 align-self-end">
                            <ul class="nav nav-tabs border-bottom-0 js-demo-panel-tabs" id="demo_panel-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tab_default-1"
                                       role="tab">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab_default-2" role="tab">Set</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link disabled" data-bs-toggle="tab" href="#" role="tab">Disabled</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                <p>You can easily add <code>.nav-tabs</code> to <code>.panel-hdr</code>, consider adding
                                    dropdown panel button if you have a lot of panel tabs. Check out the Tabs &amp; pills
                                    page for more details</p>
                            </div>
                            <div aria-label="Basic radio toggle button group" class="btn-group" id="js_change_tab_direction"
                                 role="group">
                                <input autocomplete="off" checked="checked" class="btn-check" data-action="toggle-swap"
                                       data-target=".js-demo-panel-tabs" data-toggleclass="nav-tabs-clean" id="btnradio1"
                                       name="btnradio" type="radio" value="nav nav-tabs"/>
                                <label class="btn btn-sm btn-outline-primary" for="btnradio1">Default</label>
                                <input autocomplete="off" class="btn-check" data-action="toggle-swap"
                                       data-target=".js-demo-panel-tabs" data-toggleclass="nav-tabs-clean" id="btnradio2"
                                       name="btnradio" type="radio" value="nav nav-tabs justify-content-end"/>
                                <label class="btn btn-sm btn-outline-primary" for="btnradio2">Clean</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Panel pills-->
                <div class="panel panel-icon panel-locked js-example-panel-lock" id="panel-13">
                    <div class="panel-hdr">
                        <h2>
                            Panel <span class="fw-light"><i>Locked</i></span>
                        </h2>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag"> This panel is locked from drag and drop. We use the class <code>.panel-locked</code>
                                to lock the panel. Go ahead and try to drag it, it won't budge!
                            </div>
                            <button class="btn btn-outline-secondary me-2" data-action="panel-toggle"
                                    data-panel-toggle="panel-locked" type="button">
                                <span class="panel-locked-text"><i class="sa sa-lock me-1"></i> Unlock panel</span>
                                <span class="panel-unlock-text"><i class="sa sa-lock-open me-1"></i> Lock panel</span>
                            </button>
                            <a data-action="panel-reset" href="javascript:void(0);">Reset panel</a> will also unlock the
                            panel.
                        </div>
                    </div>
                </div>
                <!--Panel progress bars-->
                <div class="panel panel-icon" id="panel-14">
                    <div class="panel-hdr">
                        <h2>
                            <svg class="sa-icon panel-icon">
                                <use href="/icons/sprite.svg#package"></use>
                            </svg>
                            Insert <span class="fw-light"><i>Icon</i></span>
                        </h2>
                        <div class="panel-toolbar">
                            <div class="progress bg-warning-200 width-sm">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="75"
                                     class="progress-bar bg-info-700 progress-bar-striped progress-bar-animated fw-700"
                                     role="progressbar" style="width: 75%;">75%
                                </div>
                            </div>
                        </div>
                        <!-- <div class="panel-toolbar">
                                                    <button type="button" class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Toggle">
                                                        <svg class="sa-icon">
                                                            <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                                            <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="btn btn-panel" data-action="panel-fullscreen"data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Fullscreen">
                                                        <svg class="sa-icon">

                                                            <use href="/icons/sprite.svg#stop-circle"></use>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                                                        <svg class="sa-icon">
                                                            <use href="/icons/sprite.svg#x-circle"></use>
                                                        </svg>
                                                    </button>
                                                </div> -->
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                Insert sleek icons—with over <a href="{{ route('second', ['icons', 'system']) }}" target="_blank">2,500 options</a> to
                                choose from—and dynamic progress bars to bring your panel to life, making data clearer,
                                faster, and more engaging than ever!
                                You can also add stylish progress bars to enhance your design.
                            </div>
                        </div>
                        <div
                            class="panel-content py-2 rounded-bottom border-faded border-start-0 border-end-0 border-bottom-0 text-muted">
                            <div class="progress progress-sm w-100 shadow-inset-2">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="75"
                                     class="progress-bar bg-info-300 bg-info-gradient" role="progressbar"
                                     style="width: 75%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Icon panel-->
                <div class="panel" id="panel-15">
                    <div class="panel-hdr">
                        <h2>
                            Classic <span class="fw-light"><i>Icons</i></span>
                        </h2>
                        <div class="panel-toolbar">
                            <button class="btn btn-panel" data-action="panel-collapse" data-bs-offset="0,16"
                                    data-bs-original-title="Toggle" data-bs-placement="top" data-bs-toggle="tooltip"
                                    type="button">
                                <svg class="sa-icon">
                                    <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                    <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                                </svg>
                            </button>
                            <button class="btn btn-panel" data-action="panel-fullscreen" data-bs-offset="0,16"
                                    data-bs-original-title="Fullscreen" data-bs-placement="top" data-bs-toggle="tooltip"
                                    type="button">
                                <svg class="sa-icon">
                                    <use href="/icons/sprite.svg#stop-circle"></use>
                                </svg>
                            </button>
                            <button class="btn btn-panel" data-action="panel-close" data-bs-offset="0,16"
                                    data-bs-original-title="Close" data-bs-placement="top" data-bs-toggle="tooltip"
                                    type="button">
                                <svg class="sa-icon">
                                    <use href="/icons/sprite.svg#x-circle"></use>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                Looking for a less cluttered panel? Add <code>.panel-icon</code> class to your
                                <code>.panel</code> to remove panel buttons and keep only the icons.
                            </div>
                            <button class="btn btn-outline-secondary" data-action="panel-toggle"
                                    data-panel-toggle="panel-icon" type="button">
                                Switch Panel Icon Style
                            </button>
                        </div>
                    </div>
                </div>
                <!--Refresh panel-->
                <div class="panel" id="panel-16">
                    <div class="panel-hdr">
                        <h2>
                            Panel <span class="fw-light"><i>Refresh</i></span>
                        </h2>
                        <div class="panel-toolbar">
                            <button class="btn btn-xs btn-primary" data-action="panel-refresh"
                                    data-refresh-callback="loadRandomGraph" data-refresh-duration="2000" type="button">
                                Refresh Panel
                            </button>
                        </div>
                    </div>
                    <div class="panel-container">
                        <div class="panel-content">
                            <div class="panel-tag">
                                Refresh panels with a simple button. Use the <code>data-action="panel-refresh"</code> to
                                activate the refresh feature. The <code>data-refresh-duration</code> attribute sets the
                                refresh interval and the <code>data-refresh-callback="functionName"</code> attribute sets
                                the callback function.
                            </div>
                            <div class="d-flex align-items-center justify-content-center">
                                <img alt="Random Colorful Bar Graph" id="randomGraph" src=""
                                     style="width:350px; height: 200px;"/>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <i class="js-last-refresh fs-xs text-muted"></i>
                                <button class="btn btn-outline-secondary" data-action="panel-refresh"
                                        data-refresh-callback="loadRandomGraph" data-refresh-duration="2000" type="button">
                                    Refresh Panel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@section('scripts')
    @vite('resources/scripts/pages/refresh-panel.js')
@endsection
