@extends('layouts.vertical', ['pageTitle' => 'Dropdowns'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Dropdowns', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Toggle contextual overlays for displaying lists of links and more with the Bootstrap dropdown plugin.'])
@endsection

@section('content')
    <div class="info-container">
        <p>
            Dropdowns are toggleable, contextual overlays for displaying lists of links and more. They're made
            interactive
            with the included Bootstrap dropdown JavaScript plugin. They're toggled by clicking, not by hovering; this
            is an
            intentional design decision. Dropdowns are built on a third party library, Popper.js, which provides dynamic
            positioning and viewport detection.
        </p>
        <p class="mb-0">
            Find more facts and uses at the bootstrap <a href="https://getbootstrap.com/docs/5.3/components/dropdowns/"
                                                         target="_blank">official documentation</a>.
        </p>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <!-- Kitchen Sink -->
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Kitchen <span class="fw-300"><i>Sink example</i></span>
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
                            A composition of all working elements
                        </div>
                        <div class="dropdown-menu d-block position-relative float-none"
                             style="width: 14rem; z-index: 2">
                            <a class="dropdown-item" href="javascript:void(0)">
                                Action<span class="badge bg-success float-end width-2">17</span></a>
                            <a class="dropdown-item" href="javascript:void(0)">
                                Another action<span class="badge badge-pill bg-primary float-end width-2">29</span></a>
                            <a class="dropdown-item disabled" href="javascript:void(0)">Disabled link</a>
                            <div class="dropdown-divider"></div>
                            <h6 class="dropdown-header">Dropdown header</h6>
                            <a class="dropdown-item active" href="javascript:void(0)">
                                <i class="sa sa-big-plus"></i>  Has icon</a>
                            <div class="dropdown-multilevel">
                                <div class="dropdown-item d-flex align-items-center justify-content-between">
                                    Multilevel
                                    <i class="sa sa-chevron-right"></i>
                                </div>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);">Menu Item</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Another Item</a>
                                    <a class="dropdown-item disabled" href="javascript:void(0);">Disabled</a>
                                    <div class="dropdown-multilevel">
                                        <div class="dropdown-item d-flex align-items-center justify-content-between">
                                            Second Level
                                            <i class="sa sa-chevron-right"></i>
                                        </div>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:void(0);">Menu Item</a>
                                            <a class="dropdown-item" href="javascript:void(0);">Another Item</a>
                                            <div class="dropdown-multilevel dropdown-multilevel-left">
                                                <div
                                                    class="dropdown-item d-flex align-items-center justify-content-between">
                                                    Third Level
                                                    <i class="sa sa-chevron-right"></i>
                                                </div>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="javascript:void(0);">Menu Item</a>
                                                    <a class="dropdown-item disabled"
                                                       href="javascript:void(0);">Disabled</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a class="dropdown-item" href="javascript:void(0)">Another action</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Links and buttons -->
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Links <span class="fw-300"><i>&amp; buttons</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button class="btn btn-panel" data-action="panel-collapse" data-bs-offset="0,10"
                                data-bs-original-title="Collapse" data-bs-placement="top" data-bs-toggle="tooltip"
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
                            Dropdown menus can be triggered from <code>&lt;a&gt;</code> or <code>&lt;button&gt;</code>
                            elements to better fit your potential needs.
                        </div>
                        <h5 class="frame-heading">
                            Button
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo demo-h-spacing">
                                <div class="btn-group">
                                    <button aria-expanded="false" class="btn btn-outline-secondary dropdown-toggle"
                                            data-bs-toggle="dropdown" type="button">
                                        Dropdown button
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button aria-expanded="false" class="btn btn-info rounded-circle btn-icon"
                                            data-bs-toggle="dropdown" type="button">
                                        <svg class="sa-icon sa-bold sa-icon-light">
                                            <use href="/icons/sprite.svg#plus-circle"></use>
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0)">Action</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Another action</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="javascript:void(0)">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button aria-expanded="false" class="btn btn-danger rounded-circle btn-icon"
                                            data-bs-toggle="dropdown" type="button">
                                        <svg class="sa-icon sa-bold sa-icon-light">
                                            <use href="/icons/sprite.svg#plus-circle"></use>
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0)">Action</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Another action</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="javascript:void(0)">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button aria-expanded="false" class="btn btn-success btn-icon rounded"
                                            data-bs-toggle="dropdown" type="button">
                                        <svg class="sa-icon sa-bold sa-icon-light">
                                            <use href="/icons/sprite.svg#plus-circle"></use>
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0)">Action</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Another action</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="javascript:void(0)">Separated link</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Link
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo demo-h-spacing d-flex">
                                <div class="dropdown">
                                    <a aria-expanded="false" class="btn btn-secondary dropdown-toggle"
                                       data-bs-toggle="dropdown" href="#" role="button">
                                        Dropdown link
                                    </a>
                                    <div aria-labelledby="dropdownMenuLink" class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <a aria-expanded="false" class="btn btn-warning rounded-circle btn-icon"
                                       data-bs-toggle="dropdown" href="#">
                                        <svg class="sa-icon sa-bold sa-icon-light">
                                            <use href="/icons/sprite.svg#plus-circle"></use>
                                        </svg>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0)">Action</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Another action</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="javascript:void(0)">Separated link</a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <a aria-expanded="false" class="btn btn-primary rounded-circle btn-icon"
                                       data-bs-toggle="dropdown" href="#">
                                        <svg class="sa-icon sa-bold sa-icon-light">
                                            <use href="/icons/sprite.svg#plus-circle"></use>
                                        </svg>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0)">Action</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Another action</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="javascript:void(0)">Separated link</a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <a aria-expanded="false" class="btn btn-default btn-icon" data-bs-toggle="dropdown"
                                       href="#">
                                        <svg class="sa-icon sa-bold sa-icon-dark">
                                            <use href="/icons/sprite.svg#plus-circle"></use>
                                        </svg>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0)">Action</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Another action</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="javascript:void(0)">Separated link</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Dropdown buttons -->
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Dropdown <span class="fw-300"><i>buttons</i></span>
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
                        <h5 class="frame-heading">
                            Variations
                        </h5>
                        <div class="frame-wrap w-100">
                            <div class="demo">
                                <div class="btn-group">
                                    <button aria-expanded="false" class="btn btn-primary dropdown-toggle"
                                            data-bs-toggle="dropdown" type="button">Primary
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button aria-expanded="false" class="btn btn-success dropdown-toggle"
                                            data-bs-toggle="dropdown" type="button">Success
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button aria-expanded="false" class="btn btn-info dropdown-toggle"
                                            data-bs-toggle="dropdown" type="button">Info
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button aria-expanded="false" class="btn btn-warning dropdown-toggle"
                                            data-bs-toggle="dropdown" type="button">Warning
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button aria-expanded="false" class="btn btn-danger dropdown-toggle"
                                            data-bs-toggle="dropdown" type="button">Danger
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button aria-expanded="false" class="btn btn-dark dropdown-toggle"
                                            data-bs-toggle="dropdown" type="button">Dark
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Split button
                        </h5>
                        <div class="frame-wrap w-100 mb-0">
                            <div class="demo">
                                <div class="btn-group">
                                    <button class="btn btn-primary" type="button">Primary</button>
                                    <button aria-expanded="false"
                                            class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                                            data-bs-toggle="dropdown" type="button">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-success" type="button">Success</button>
                                    <button aria-expanded="false"
                                            class="btn btn-success dropdown-toggle dropdown-toggle-split"
                                            data-bs-toggle="dropdown" type="button">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-info" type="button">Info</button>
                                    <button aria-expanded="false"
                                            class="btn btn-info dropdown-toggle dropdown-toggle-split"
                                            data-bs-toggle="dropdown" type="button">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-warning" type="button">Warning</button>
                                    <button aria-expanded="false"
                                            class="btn btn-warning dropdown-toggle dropdown-toggle-split"
                                            data-bs-toggle="dropdown" type="button">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-danger" type="button">Danger</button>
                                    <button aria-expanded="false"
                                            class="btn btn-danger dropdown-toggle dropdown-toggle-split"
                                            data-bs-toggle="dropdown" type="button">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-dark" type="button">Dark</button>
                                    <button aria-expanded="false"
                                            class="btn btn-dark dropdown-toggle dropdown-toggle-split"
                                            data-bs-toggle="dropdown" type="button">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Multilevel -->
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Multilevel <span class="fw-300"><i>dropdown</i></span>
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
                            Add multilevel menu by wrapping the child node selectors with
                            <code>.dropdown-multilevel</code>,
                            change direction of by adding <code>.dropdown-multilevel-left</code> (right by default)
                        </div>
                        <div class="btn-group">
                            <button aria-expanded="false" class="btn btn-danger dropdown-toggle"
                                    data-bs-toggle="dropdown"
                                    type="button">
                                <i class="sa sa-energy me-1"></i> Multilevel default
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">Action</a>
                                <div class="dropdown-multilevel">
                                    <div class="dropdown-item d-flex align-items-center justify-content-between">
                                        Multilevel
                                        <i class="sa sa-chevron-right"></i>
                                    </div>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0);">First level</a>
                                        <div class="dropdown-multilevel">
                                            <div
                                                class="dropdown-item d-flex align-items-center justify-content-between">
                                                Second Level
                                                <i class="sa sa-chevron-right"></i>
                                            </div>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="javascript:void(0);">Level two</a>
                                                <a class="dropdown-item disabled" href="javascript:void(0);">Disabled
                                                    menu</a>
                                                <div
                                                    class="dropdown-multilevel dropdown-multilevel-left d-flex align-items-center justify-content-between">
                                                    <div class="dropdown-item">
                                                        Third Level (left)
                                                        <i class="sa sa-chevron-right"></i>
                                                    </div>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="javascript:void(0);">Menu
                                                            Item</a>
                                                        <a class="dropdown-item disabled"
                                                           href="javascript:void(0);">Disabled</a>
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
            <!-- Content -->
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Content <span class="fw-300"><i>types</i></span>
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
                        <div class="demo demo-no-mb">
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-secondary dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    Text
                                </button>
                                <div class="dropdown-menu p-4 text-muted"
                                     style="max-width: 200px; background-image: url(img/backgrounds/bg-4.png);">
                                    <p>
                                        Some example text that's free-flowing within the dropdown menu.
                                    </p>
                                    <p class="mb-0">
                                        And this is more example text.
                                    </p>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-secondary dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    Forms
                                </button>
                                <div class="dropdown-menu dropdown-xl"
                                     style="background-image: url(img/backgrounds/bg-1.png);">
                                    <form class="p-4 needs-validation" novalidate="">
                                        <div class="mb-3">
                                            <label class="form-label" for="username">Username</label>
                                            <input class="form-control" id="username" placeholder="your id or email"
                                                   required="" type="email" value="drlantern@getwebora.com"/>
                                            <div class="invalid-feedback">No, you missed this one.</div>
                                            <div class="form-text">Your unique username to app</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="password">Password</label>
                                            <input class="form-control" id="password" placeholder="password" required=""
                                                   type="password" value="password123"/>
                                            <div class="invalid-feedback">Sorry, you missed this one.</div>
                                            <div class="form-text">Your password</div>
                                        </div>
                                        <div class="mb-3 form-check">
                                            <input class="form-check-input" id="rememberme" type="checkbox"/>
                                            <label class="form-check-label" for="rememberme">Remember me for the next 30
                                                days</label>
                                        </div>
                                        <button class="btn btn-info w-100 waves-effect waves-themed" type="submit">Sign
                                            in
                                        </button>
                                    </form>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">New around here? Sign up</a>
                                    <a class="dropdown-item" href="#">Forgot password?</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sizing -->
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Sizing <span class="fw-300"><i>dropdowns</i></span>
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
                            Button dropdowns work with buttons of all sizes, including default and split dropdown
                            buttons
                        </div>
                        <div class="demo mb-2">
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-secondary btn-lg dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    Large button
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-lg btn-secondary" type="button">Large split button</button>
                                <button aria-expanded="false"
                                        class="btn btn-lg btn-secondary dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" type="button">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </div>
                        </div>
                        <div class="demo mb-2">
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-secondary dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    Default size
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-secondary" type="button">Split button</button>
                                <button aria-expanded="false"
                                        class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" type="button">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </div>
                        </div>
                        <div class="demo mb-2">
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-secondary btn-sm dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    Small button
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-secondary" type="button">Small split button</button>
                                <button aria-expanded="false"
                                        class="btn btn-sm btn-secondary dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" type="button">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </div>
                        </div>
                        <div class="demo mb-0">
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-secondary btn-xs dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    Extra small
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-xs btn-secondary" type="button">Extra small</button>
                                <button aria-expanded="false"
                                        class="btn btn-xs btn-secondary dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" type="button">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <!-- No arrow -->
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        No <span class="fw-300"><i>arrow</i></span>
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
                            Remove directional arrow from dropdown button by adding <code>.no-arrow</code> to
                            <code>.dropdown-toggle</code>
                        </div>
                        <div class="btn-group">
                            <button aria-expanded="false" class="btn btn-info dropdown-toggle no-arrow"
                                    data-bs-toggle="dropdown" type="button">
                                No arrow
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" type="button">Action</a>
                                <a class="dropdown-item" href="#" type="button">Another action</a>
                                <a class="dropdown-item" href="#" type="button">Something else here</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Animated -->
            <div class="panel panel-icon" id="panel-8">
                <div class="panel-hdr">
                    <h2>
                        Animated <span class="fw-300"><i>dropdowns</i></span>
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
                            Add animations to dropdowns by adding <code>.dropdown-menu-animated</code>,
                            <code>.fadeup</code>, <code>.fadedown</code>, <code>.faderight</code>,
                            <code>.fadeleft</code> to
                            <code>.dropdown-menu</code>
                        </div>
                        <div class="demo">
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-primary dropdown-toggle bg-trans-gradient"
                                        data-bs-animation="false" data-bs-toggle="dropdown" type="button">
                                    Custom
                                </button>
                                <div class="dropdown-menu dropdown-menu-animated">
                                    <a class="dropdown-item" href="#" type="button">Action</a>
                                    <a class="dropdown-item" href="#" type="button">Another action</a>
                                    <a class="dropdown-item" href="#" type="button">Something else here</a>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button aria-expanded="true" class="btn btn-primary dropdown-toggle bg-brand-gradient"
                                        data-bs-animation="false" data-bs-toggle="dropdown" type="button">
                                    Fadeinup
                                </button>
                                <div class="dropdown-menu fadeup">
                                    <h6 class="dropdown-header">Fade in from bottom</h6>
                                    <a class="dropdown-item" href="#" type="button">Action</a>
                                    <a class="dropdown-item" href="#" type="button">Another action</a>
                                    <a class="dropdown-item" href="#" type="button">Something else here</a>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button aria-expanded="true" class="btn btn-primary dropdown-toggle bg-brand-gradient"
                                        data-bs-animation="false" data-bs-toggle="dropdown" type="button">
                                    Fadeindown
                                </button>
                                <div class="dropdown-menu fadedown">
                                    <h6 class="dropdown-header">Fade in from top</h6>
                                    <a class="dropdown-item" href="#" type="button">Action</a>
                                    <a class="dropdown-item" href="#" type="button">Another action</a>
                                    <a class="dropdown-item" href="#" type="button">Something else here</a>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button aria-expanded="true" class="btn btn-primary dropdown-toggle bg-brand-gradient"
                                        data-bs-animation="false" data-bs-toggle="dropdown" type="button">
                                    Fadeinright
                                </button>
                                <div class="dropdown-menu fadeinright">
                                    <h6 class="dropdown-header">Fade in from right</h6>
                                    <a class="dropdown-item" href="#" type="button">Action</a>
                                    <a class="dropdown-item" href="#" type="button">Another action</a>
                                    <a class="dropdown-item" href="#" type="button">Something else here</a>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button aria-expanded="true" class="btn btn-primary dropdown-toggle bg-brand-gradient"
                                        data-bs-animation="false" data-bs-toggle="dropdown" type="button">
                                    Fadeinleft
                                </button>
                                <div class="dropdown-menu fadeleft">
                                    <h6 class="dropdown-header">Fade in from left</h6>
                                    <a class="dropdown-item" href="#" type="button">Action</a>
                                    <a class="dropdown-item" href="#" type="button">Another action</a>
                                    <a class="dropdown-item" href="#" type="button">Something else here</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Directions -->
            <div class="panel panel-icon" id="panel-9">
                <div class="panel-hdr">
                    <h2>
                        Directional <span class="fw-300"><i>Arrows</i></span>
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
                            Trigger dropdown menus above elements by adding <code>.dropup</code>,
                            <code>.dropright</code>,
                            and <code>.dropleft</code> to the parent element.
                        </div>
                        <h5 class="frame-heading">
                            Dropup
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo demo-no-mb">
                                <div class="btn-group dropup">
                                    <button aria-expanded="false" class="btn btn-secondary dropdown-toggle"
                                            data-bs-toggle="dropdown" type="button">
                                        Dropup
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group dropup">
                                    <button class="btn btn-secondary" type="button">
                                        Split dropup
                                    </button>
                                    <button aria-expanded="false"
                                            class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                                            data-bs-toggle="dropdown" type="button">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Dropend
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo demo-no-mb">
                                <div class="btn-group dropend">
                                    <button aria-expanded="false" class="btn btn-secondary dropdown-toggle"
                                            data-bs-toggle="dropdown" type="button">
                                        Dropend
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group dropend">
                                    <button class="btn btn-secondary" type="button">
                                        Split dropend
                                    </button>
                                    <button aria-expanded="false"
                                            class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                                            data-bs-toggle="dropdown" type="button">
                                        <span class="visually-hidden">Toggle Dropend</span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Dropstart
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo demo-no-mb">
                                <div class="btn-group dropstart">
                                    <button aria-expanded="false" class="btn btn-secondary dropdown-toggle"
                                            data-bs-toggle="dropdown" type="button">
                                        Dropstart
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <div class="btn-group dropstart" role="group">
                                        <button aria-expanded="false"
                                                class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                                                data-bs-toggle="dropdown" type="button">
                                            <span class="visually-hidden">Toggle Dropstart</span>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Separated link</a>
                                        </div>
                                    </div>
                                    <button class="btn btn-secondary" type="button">
                                        Split dropstart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container -->
            <div class="panel panel-icon" id="panel-10">
                <div class="panel-hdr">
                    <h2>
                        Container <span class="fw-300"><i>examples</i></span>
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
                            You can add any content to dropdown containers and change its width by adding the class
                            <code>.dropdown-sm</code>, <code>.dropdown-md</code>, <code>.dropdown-lg</code>, and
                            <code>.dropdown-xl</code>
                        </div>
                        <div class="demo">
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-primary dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    dropdown-sm
                                </button>
                                <div class="dropdown-menu dropdown-sm">
                                    <a class="dropdown-item" href="#" type="button">Action</a>
                                    <a class="dropdown-item" href="#" type="button">Another action</a>
                                    <a class="dropdown-item" href="#" type="button">Something else</a>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-primary dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    dropdown-md
                                </button>
                                <div class="dropdown-menu dropdown-md">
                                    <a class="dropdown-item" href="#" type="button">Action</a>
                                    <a class="dropdown-item" href="#" type="button">Another action</a>
                                    <a class="dropdown-item" href="#" type="button">Something else here</a>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-primary dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    dropdown-lg
                                </button>
                                <div class="dropdown-menu dropdown-lg">
                                    <a class="dropdown-item" href="#" type="button">Action</a>
                                    <a class="dropdown-item" href="#" type="button">Another action</a>
                                    <a class="dropdown-item" href="#" type="button">Something else here</a>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-primary dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    dropdown-xl
                                </button>
                                <div class="dropdown-menu dropdown-xl">
                                    <a class="dropdown-item" href="#" type="button">Action</a>
                                    <a class="dropdown-item" href="#" type="button">Another action</a>
                                    <a class="dropdown-item" href="#" type="button">Something else here</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Headers -->
            <div class="panel panel-icon" id="panel-11">
                <div class="panel-hdr">
                    <h2>
                        Headers <span class="fw-300"><i>examples</i></span>
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
                            Customize <code>.dropdown-header</code> using utility classes. Change <a href="#"
                                                                                                     target="_blank">backgrounds</a>,
                            <a href="#" target="_blank">colors</a>, and paddings using
                            various <a href="#" target="_blank">helpers</a>
                        </div>
                        <div class="demo">
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-outline-primary dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    Custom header 1
                                </button>
                                <div class="dropdown-menu p-0">
                                    <div
                                        class="dropdown-header bg-trans-gradient d-flex flex-row px-4 py-3 rounded-top text-white width-lg">
                                        <div class="d-flex flex-row align-items-center mt-1 mb-1 color-white">
                                        <span class="me-2">
                                            <img alt="Dr. Codex Lantern" class="rounded-circle profile-image"
                                                 src="/img/demo/avatars/avatar-admin.png"/>
                                        </span>
                                            <div class="info-card-text">
                                                <div class="fs-lg text-truncate text-truncate-lg">Sunny A.</div>
                                                <span
                                                    class="text-truncate text-truncate-md opacity-80">admin@smartadmin.ai</span>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="dropdown-item" href="#" type="button">Action</a>
                                    <a class="dropdown-item" href="#" type="button">Another action</a>
                                    <a class="dropdown-item" href="#" type="button">Something else</a>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-outline-primary dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    Custom header 2
                                </button>
                                <div class="dropdown-menu p-0">
                                    <div
                                        class="dropdown-header bg-fusion-500 d-flex flex-row px-4 py-3 rounded-top text-white">
                                        <div class="d-flex flex-row align-items-center mt-1 mb-1 color-white">
                                        <span class="me-2">
                                            <img alt="Dr. Codex Lantern" class="rounded-circle profile-image"
                                                 src="/img/demo/avatars/avatar-admin.png"/>
                                        </span>
                                            <div class="info-card-text">
                                                <div class="fs-lg text-truncate text-truncate-lg">Sunny A.</div>
                                                <span
                                                    class="text-truncate text-truncate-md opacity-80">admin@smartadmin.ai</span>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="dropdown-item" type="button">Action</button>
                                    <button class="dropdown-item" type="button">Another action</button>
                                    <button class="dropdown-item" type="button">Something else</button>
                                </div>
                            </div>
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-outline-primary dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    Custom header 3
                                </button>
                                <div class="dropdown-menu p-0">
                                    <div
                                        class="dropdown-header bg-danger-900 bg-info-gradient d-flex flex-row px-4 py-3 rounded-top text-white">
                                        <div class="d-flex flex-row align-items-center mt-1 mb-1 color-white">
                                        <span class="me-2">
                                            <img alt="Dr. Codex Lantern" class="rounded-circle profile-image"
                                                 src="/img/demo/avatars/avatar-admin.png"/>
                                        </span>
                                            <div class="info-card-text">
                                                <div class="fs-lg text-truncate text-truncate-lg">Sunny A.</div>
                                                <span
                                                    class="text-truncate text-truncate-md opacity-80">admin@smartadmin.ai</span>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="dropdown-item" href="#" type="button">Action</a>
                                    <a class="dropdown-item" href="#" type="button">Another action</a>
                                    <a class="dropdown-item" href="#" type="button">Something else</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Responsive alignment -->
            <div class="panel panel-icon" id="panel-12">
                <div class="panel-hdr">
                    <h2>
                        Responsive <span class="fw-300"><i>alignment</i></span>
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
                            If you want to use responsive alignment, disable dynamic positioning by adding the
                            <code>data-display="static"</code> attribute and use the responsive variation classes. To
                            align
                            left/right the dropdown menu with the given breakpoint or larger, add
                            <code>.dropdown-menu{-sm|-md|-lg|-xl}-right</code> or
                            <code>.dropdown-menu{-sm|-md|-lg|-xl}-left</code>
                        </div>
                        <h5 class="frame-heading">
                            Left-aligned but right aligned when large screen
                        </h5>
                        <div class="frame-wrap">
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-secondary dropdown-toggle"
                                        data-bs-display="static" data-bs-toggle="dropdown" type="button">
                                    Left aligned large screens
                                </button>
                                <div class="dropdown-menu dropdown-menu-lg-right">
                                    <button class="dropdown-item" type="button">Action</button>
                                    <button class="dropdown-item" type="button">Another action</button>
                                    <button class="dropdown-item" type="button">Something else here</button>
                                </div>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Right-aligned but left aligned when large screen
                        </h5>
                        <div class="frame-wrap mb-0">
                            <div class="btn-group">
                                <button aria-expanded="false" class="btn btn-secondary dropdown-toggle"
                                        data-bs-display="static" data-bs-toggle="dropdown" type="button">
                                    Right aligned large screens
                                </button>
                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left">
                                    <a class="dropdown-item" href="#" type="button">Action</a>
                                    <a class="dropdown-item" href="#" type="button">Another action</a>
                                    <a class="dropdown-item" href="#" type="button">Something else here</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
