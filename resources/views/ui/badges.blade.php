@extends('layouts.vertical', ['pageTitle' => 'Badges'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle' => 'Badges', 'pageSubTitle1' => 'Design', 'pageSubTitle2' => 'UI Components', 'pageSubText' => 'Small and adaptive tag for adding context to just about any content.'])
@endsection

@section('content')
    <div class="info-container">
        <p>
            Using color to add meaning only provides a visual indication, which will not be conveyed to users of
            assistive
            technologies – such as screen readers. Ensure that information denoted by the color is either obvious from
            the
            content itself (e.g. the visible text), or is included through alternative means, such as additional text
            hidden
            with the <code>.visually-hidden</code> class.
        </p>
        <p class="mb-0">
            Learn more about this component on bootstrap's
            <a href="https://getbootstrap.com/docs/5.3/components/badge/" target="_blank">official
                documentation</a>
        </p>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Badge <span class="fw-300"><i>scaling</i></span>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Badges scale to match the size of the immediate parent element by using relative font sizing
                            and
                            <code>em</code> units
                        </div>
                        <h5 class="frame-heading">Badge Scaling Examples</h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <h1>Example heading <span class="badge bg-primary">New</span></h1>
                                <h2>Example heading <span class="badge bg-secondary">New</span></h2>
                                <h3>Example heading <span class="badge bg-success">New</span></h3>
                                <h4>Example heading <span class="badge bg-info">New</span></h4>
                                <h5>Example heading <span class="badge bg-warning">New</span></h5>
                                <h6>Example heading <span class="badge bg-danger">New</span></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Badge <span class="fw-300"><i>in buttons</i></span>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Badges scale to match the size of the immediate parent element by using relative font sizing
                            and
                            <code>em</code> units
                        </div>
                        <h5 class="frame-heading">
                            Regular buttons
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-lg btn-primary" type="button">Messages
                                    <span class="badge bg-primary-300 ms-1">4</span>
                                </button>
                                <button class="btn btn-lg btn-secondary" type="button">Messages
                                    <span class="badge bg-fusion-500 ms-1">4</span>
                                </button>
                                <button class="btn btn-lg btn-default" type="button">Messages
                                    <span class="badge bg-primary-500 ms-1">4</span>
                                </button>
                            </div>
                            <div class="demo">
                                <button class="btn btn-default" type="button">Messages
                                    <span class="badge bg-primary-500 ms-1">4</span>
                                </button>
                                <button class="btn btn-success" type="button">Messages
                                    <span class="badge bg-success-700 ms-1">4</span>
                                </button>
                                <button class="btn btn-warning" type="button">Messages
                                    <span class="badge bg-warning-200 ms-1">4</span>
                                </button>
                            </div>
                            <div class="demo">
                                <button class="btn btn-sm btn-info" type="button">Messages
                                    <span class="badge bg-info-700 ms-1">4</span>
                                </button>
                                <button class="btn btn-sm btn-danger" type="button">Messages
                                    <span class="badge bg-danger-800 ms-1">4</span>
                                </button>
                                <button class="btn btn-sm btn-dark" type="button">Messages
                                    <span class="badge bg-fusion-300 ms-1">4</span>
                                </button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Outline buttons
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-lg btn-outline-primary" type="button">Messages
                                    <span class="badge bg-primary-300 ms-1">4</span>
                                </button>
                                <button class="btn btn-lg btn-outline-secondary" type="button">Messages
                                    <span class="badge bg-fusion-500 ms-1">4</span>
                                </button>
                                <button class="btn btn-lg btn-outline-default" type="button">Messages
                                    <span class="badge bg-primary-500 ms-1">4</span>
                                </button>
                            </div>
                            <div class="demo">
                                <button class="btn btn-outline-default" type="button">Messages
                                    <span class="badge bg-primary-500 ms-1">4</span>
                                </button>
                                <button class="btn btn-outline-success" type="button">Messages
                                    <span class="badge bg-success-700 ms-1">4</span>
                                </button>
                                <button class="btn btn-outline-warning" type="button">Messages
                                    <span class="badge bg-warning-200 ms-1">4</span>
                                </button>
                            </div>
                            <div class="demo">
                                <button class="btn btn-sm btn-outline-info" type="button">Messages
                                    <span class="badge bg-info-700 ms-1">4</span>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" type="button">Messages
                                    <span class="badge bg-danger-800 ms-1">4</span>
                                </button>
                                <button class="btn btn-sm btn-outline-dark" type="button">Messages
                                    <span class="badge bg-fusion-300 ms-1">4</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Icon <span class="fw-300"><i>badges</i></span>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag"> Use utility classes with <code>.btn-icon</code> to place the badge</div>
                        <div class="demo demo-h-spacing mb-4">
                            <a class="btn btn-lg btn-primary btn-icon rounded-circle position-relative js-waves-off"
                               href="javascript:void(0);">
                                <i class="fal fa-print"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger mt-2">
                                9 <span class="visually-hidden">unread messages</span>
                            </span>
                            </a>
                            <a class="btn btn-secondary btn-lg btn-icon position-relative js-waves-off"
                               href="javascript:void(0);">
                                <i class="fal fa-volume-mute"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success mt-2">
                                18 <span class="visually-hidden">unread messages</span>
                            </span>
                            </a>
                            <a class="btn btn-default btn-lg btn-icon rounded-circle position-relative js-waves-off"
                               href="javascript:void(0);">
                                <i class="fal fa-envelope"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning-700 mt-2 p-1">
                                99 <span class="visually-hidden">unread messages</span>
                            </span>
                            </a>
                            <a class="btn btn-outline-success btn-lg btn-icon rounded-circle position-relative js-waves-off"
                               href="javascript:void(0);">
                                <i class="fal fa-download"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary mt-2">
                                100.. <span class="visually-hidden">unread messages</span>
                            </span>
                            </a>
                        </div>
                        <div class="demo demo-h-spacing mb-4">
                            <a class="btn btn-primary btn-icon rounded-circle position-relative js-waves-off"
                               href="javascript:void(0);">
                                <i class="fal fa-print"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger mt-1">
                                16 <span class="visually-hidden">unread messages</span>
                            </span>
                            </a>
                            <a class="btn btn-secondary btn-icon position-relative js-waves-off"
                               href="javascript:void(0);">
                                <i class="fal fa-volume-mute fs-md"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success mt-1">
                                4 <span class="visually-hidden">unread messages</span>
                            </span>
                            </a>
                            <a class="btn btn-default btn-icon rounded-circle position-relative js-waves-off"
                               href="javascript:void(0);">
                                <i class="fal fa-envelope"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning mt-1">
                                4 <span class="visually-hidden">unread messages</span>
                            </span>
                            </a>
                            <a class="btn btn-outline-success btn-icon rounded-circle position-relative js-waves-off"
                               href="javascript:void(0);">
                                <i class="fal fa-download"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary mt-1">
                                99+ <span class="visually-hidden">unread messages</span>
                            </span>
                            </a>
                        </div>
                        <div class="demo demo-h-spacing mb-4">
                            <a class="btn btn-sm btn-primary btn-icon rounded-circle position-relative js-waves-off"
                               href="javascript:void(0);">
                                <i class="fal fa-print"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                4 <span class="visually-hidden">unread messages</span>
                            </span>
                            </a>
                            <a class="btn btn-sm btn-secondary btn-icon position-relative js-waves-off"
                               href="javascript:void(0);">
                                <i class="fal fa-volume-mute fs-md"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                0 <span class="visually-hidden">unread messages</span>
                            </span>
                            </a>
                            <a class="btn btn-sm btn-default btn-icon rounded-circle position-relative js-waves-off"
                               href="javascript:void(0);">
                                <i class="fal fa-envelope"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                7 <span class="visually-hidden">unread messages</span>
                            </span>
                            </a>
                            <a class="btn btn-sm btn-outline-success btn-icon rounded-circle position-relative js-waves-off"
                               href="javascript:void(0);">
                                <i class="fal fa-download"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                4 <span class="visually-hidden">unread messages</span>
                            </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Status <span class="fw-300"><i>badges</i></span>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            These badges are unique and triggered by wrapping an element with <code>.status</code>
                            class.
                            Comes with three badge sizes <code>.status-sm</code>, <code>.status-xs</code> and the
                            default
                            <code>.status</code>. Comes in 3 colors, <code>.status-danger</code>,
                            <code>.status-warning</code>, and <code>.status-success</code>
                        </div>
                        <div class="demo">
                        <span class="status d-inline-block">
                            <img alt="..." class="profile-image rounded-circle" src="/img/demo/avatars/avatar-c.png"/>
                        </span>
                            <span class="status status-danger d-inline-block">
                            <img alt="..." class="profile-image rounded-circle" src="/img/demo/avatars/avatar-c.png"/>
                        </span>
                            <span class="status status-success d-inline-block">
                            <img alt="..." class="profile-image rounded-circle" src="/img/demo/avatars/avatar-c.png"/>
                        </span>
                            <span class="status status-warning d-inline-block">
                            <img alt="..." class="profile-image rounded-circle" src="/img/demo/avatars/avatar-c.png"/>
                        </span>
                        </div>
                        <div class="demo">
                        <span class="status status-sm d-inline-block">
                            <img alt="..." class="profile-image profile-image-md rounded-circle"
                                 src="/img/demo/avatars/avatar-c.png"/>
                        </span>
                            <span class="status status-sm status-danger d-inline-block">
                            <img alt="..." class="profile-image profile-image-md rounded-circle"
                                 src="/img/demo/avatars/avatar-c.png"/>
                        </span>
                            <span class="status status-sm status-success d-inline-block">
                            <img alt="..." class="profile-image profile-image-md rounded-circle"
                                 src="/img/demo/avatars/avatar-c.png"/>
                        </span>
                            <span class="status status-sm status-warning d-inline-block">
                            <img alt="..." class="profile-image profile-image-md rounded-circle"
                                 src="/img/demo/avatars/avatar-c.png"/>
                        </span>
                        </div>
                        <div class="demo">
                        <span class="status status-xs d-inline-block">
                            <img alt="..." class="profile-image profile-image-sm rounded-circle"
                                 src="/img/demo/avatars/avatar-c.png"/>
                        </span>
                            <span class="status status-xs status-danger d-inline-block">
                            <img alt="..." class="profile-image profile-image-sm rounded-circle"
                                 src="/img/demo/avatars/avatar-c.png"/>
                        </span>
                            <span class="status status-xs status-success d-inline-block">
                            <img alt="..." class="profile-image profile-image-sm rounded-circle"
                                 src="/img/demo/avatars/avatar-c.png"/>
                        </span>
                            <span class="status status-xs status-warning d-inline-block">
                            <img alt="..." class="profile-image profile-image-sm rounded-circle"
                                 src="/img/demo/avatars/avatar-c.png"/>
                        </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Contextual <span class="fw-300"><i>variations</i></span>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Add any of the below mentioned modifier classes to change the appearance of a badge.
                        </div>
                        <div class="frame-wrap">
                            <div class="demo">
                                <span class="badge bg-primary">Primary</span>
                                <span class="badge bg-secondary">Secondary</span>
                                <span class="badge bg-success">Success</span>
                                <span class="badge bg-danger">Danger</span>
                                <span class="badge bg-warning text-dark">Warning</span>
                                <span class="badge bg-info">Info</span>
                                <span class="badge bg-light text-dark">Light</span>
                                <span class="badge bg-dark">Dark</span>
                            </div>
                        </div>
                        <div class="frame-heading">
                            Border utility
                        </div>
                        <div class="frame-wrap">
                            <div class="demo">
                                <span class="badge border border-primary text-primary">Primary</span>
                                <span class="badge border border-secondary text-secondary">Secondary</span>
                                <span class="badge border border-success text-success">Success</span>
                                <span class="badge border border-danger text-danger">Danger</span>
                                <span class="badge border border-warning text-warning">Warning</span>
                                <span class="badge border border-info text-info">Info</span>
                                <span class="badge border border-light text-light">Light</span>
                                <span class="badge border border-dark text-dark">Dark</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Pill <span class="fw-300"><i>badges</i></span>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Use the <code>.badge-pill</code> modifier class to make badges more rounded (with a larger
                            border-radius and additional horizontal padding).
                        </div>
                        <div class="frame-wrap">
                            <div class="demo">
                                <span class="badge rounded-pill bg-primary">Primary</span>
                                <span class="badge rounded-pill bg-secondary">Secondary</span>
                                <span class="badge rounded-pill bg-success">Success</span>
                                <span class="badge rounded-pill bg-danger">Danger</span>
                                <span class="badge rounded-pill bg-warning">Warning</span>
                                <span class="badge rounded-pill bg-info">Info</span>
                                <span class="badge rounded-pill bg-light">Light</span>
                                <span class="badge rounded-pill bg-dark">Dark</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        Links <span class="fw-300"><i>example</i></span>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Add <code>.badge-*</code> class on a hyperlink element to convert it to actionable badges
                            with
                            hover and focus states
                        </div>
                        <div class="demo">
                            <a class="badge bg-primary" href="#">Link Badge - Primary</a>
                            <a class="badge bg-secondary" href="#">Link Badge - Secondary</a>
                            <a class="badge bg-success" href="#">Link Badge - Success</a>
                            <a class="badge bg-danger" href="#">Link Badge - Danger</a>
                            <a class="badge bg-warning" href="#">Link Badge - Warning</a>
                            <a class="badge bg-info" href="#">Link Badge - Info</a>
                            <a class="badge bg-light" href="#">Link Badge - Light</a>
                            <a class="badge bg-dark" href="#">Link Badge - Dark</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-8">
                <div class="panel-hdr">
                    <h2>
                        Nested <span class="fw-300"><i>badge</i></span>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Use the included <a href="{{ route('second', ['utilities', 'colorpalette']) }}" target="_blank">color utilities</a>
                            to
                            change background and text colors. Below are examples of nested badge with different
                            background
                            colors
                        </div>
                        <div class="frame-wrap">
                            <div class="demo">
                            <span class="badge bg-primary-100">
                                <span class="badge bg-primary-200">
                                    <span class="badge bg-primary-300">
                                        <span class="badge bg-primary-400">
                                            <span class="badge bg-primary-500">
                                                <span class="fs-xl fw-300">Nested Badge 1</span>
                                            </span>
                                        </span>
                                    </span>
                                </span>
                            </span>
                                <span class="badge bg-primary-400">
                                <span class="badge bg-info-500">
                                    <span class="badge bg-success-500">
                                        <span class="badge bg-warning-500">
                                            <span class="badge bg-danger-500">
                                                <span class="fs-xl fw-300">Nested Badge 2</span>
                                            </span>
                                        </span>
                                    </span>
                                </span>
                            </span>
                                <span class="badge bg-danger-400 badge-pill">
                                <span class="badge bg-warning-500 badge-pill">
                                    <span class="badge bg-info-500 badge-pill">
                                        <span class="badge bg-success-500 badge-pill">
                                            <span class="badge bg-primary-500 badge-pill">
                                                <span class="fs-xl fw-300">99+</span>
                                            </span>
                                        </span>
                                    </span>
                                </span>
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-9">
                <div class="panel-hdr">
                    <h2>
                        Badge <span class="fw-300"><i>header</i></span>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            We use <code>.badge-icon</code> with a combination of other utility classes to create a link
                            badge. This class will auto turncate overflow giving you a nice clean look without any
                            hassle
                        </div>
                        <div class="demo">
                            <a class="height-2 d-inline-flex align-items-center justify-content-center position-relative m-0 text-primary h2"
                               href="javascript:void(0);">
                                I am a large text
                                <span class="badge badge-icon position-relative ms-1">9999</span>
                            </a>
                            <br/>
                            <a class="width-auto height-2 d-inline-flex align-items-center justify-content-center position-relative m-0 h4 text-primary"
                               href="javascript:void(0);">
                                I am smaller text
                                <span class="badge badge-icon position-relative ms-1">9999</span>
                            </a>
                            <br/>
                            <a class="width-auto height-2 d-inline-flex align-items-center justify-content-center position-relative m-0 text-primary"
                               href="javascript:void(0);">
                                I am normal text
                                <span class="badge badge-icon position-relative ms-1">9999</span>
                            </a>
                        </div>
                        <div class="demo">
                            <a class="width-3 height-2 d-inline-flex align-items-center justify-content-center position-relative h1 text-primary"
                               href="javascript:void(0);">
                                <i class="fal fa-bell"></i>
                                <span class="badge badge-icon top-0 end-0">11</span>
                            </a>
                            <a class="width-3 height-2 d-inline-flex align-items-center justify-content-center position-relative h1 text-primary"
                               href="javascript:void(0);">
                                <i class="fal fa-envelope"></i>
                                <span class="badge badge-icon top-0 end-0">17</span>
                            </a>
                            <a class="width-3 height-2 d-inline-flex align-items-center justify-content-center position-relative h3 text-primary"
                               href="javascript:void(0);">
                                <i class="fal fa-bell"></i>
                                <span class="badge badge-icon top-0 end-0">11</span>
                            </a>
                            <a class="width-3 height-2 d-inline-flex align-items-center justify-content-center position-relative h3 text-primary"
                               href="javascript:void(0);">
                                <i class="fal fa-envelope"></i>
                                <span class="badge badge-icon top-0 end-0">17</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
