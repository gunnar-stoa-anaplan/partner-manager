@extends('layouts.vertical', ['pageTitle' => 'Toasts'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Toasts', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Push notifications to your visitors with a toast, a lightweight and easily customizable alert message.'])
@endsection

@section('content')
    <div class="info-container">
        <p>Toasts are lightweight notifications designed to mimic the push notifications that have been popularized by
            mobile and desktop operating systems. They're built with flexbox, so they're easy to align and position.</p>
        <p class="mb-0">
            Learn more about this component on bootstrap's <a
                href="https://getbootstrap.com/docs/5.3/components/toasts/" target="_blank">official documentation</a>.
        </p>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Basic <span class="fw-300"><i>Toasts</i></span>
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
                            Toasts are as flexible as you need and have very little required markup. At a minimum, we
                            require a single element to contain your "toasted" content and strongly encourage a dismiss
                            button.
                        </div>
                        <div aria-atomic="true" aria-live="assertive" class="toast fade show" role="alert">
                            <div class="toast-header">
                                <img alt="brand-logo" class="me-2" height="16" src="/img/logo.png"/>
                                <strong class="me-auto">SmartAdmin</strong>
                                <small>11 mins ago</small>
                                <button aria-label="Close" class="btn-close" data-bs-dismiss="toast"
                                        type="button"></button>
                            </div>
                            <div class="toast-body">
                                Hello, world! This is a toast message.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Stacking <span class="fw-300"><i>Toasts</i></span>
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
                            When you have multiple toasts, we default to vertically stacking them in a readable manner.
                        </div>
                        <div aria-atomic="true" aria-live="polite"
                             class="position-relative height-lg bg-primary pattern-1">
                            <div class="position-absolute top-0 end-0 mt-3 me-3">
                                <div aria-atomic="true" aria-live="assertive" class="toast fade show" role="alert">
                                    <div class="toast-header">
                                        <img alt="brand-logo" class="me-2" height="16" src="/img/logo.png"/>
                                        <strong class="me-auto">SmartAdmin</strong>
                                        <small class="text-muted">just now</small>
                                        <button aria-label="Close" class="btn-close" data-bs-dismiss="toast"
                                                type="button"></button>
                                    </div>
                                    <div class="toast-body">
                                        See? Just like this.
                                    </div>
                                </div>
                                <div aria-atomic="true" aria-live="assertive" class="toast fade show" role="alert">
                                    <div class="toast-header">
                                        <img alt="brand-logo" class="me-2" height="16" src="/img/logo.png"/>
                                        <strong class="me-auto">SmartAdmin</strong>
                                        <small class="text-muted">2 seconds ago</small>
                                        <button aria-label="Close" class="btn-close" data-bs-dismiss="toast"
                                                type="button"></button>
                                    </div>
                                    <div class="toast-body">
                                        Heads up, toasts will stack automatically
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Translucent <span class="fw-300"><i>Toasts</i></span>
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
                            Toasts are slightly translucent, too, so they blend over whatever they might appear over.
                            For browsers that support the backdrop-filter CSS property, we'll also attempt to blur the
                            elements under a toast.
                        </div>
                        <div class="p-3 bg-secondary">
                            <div aria-atomic="true" aria-live="assertive" class="toast fade show" data-toggle="toast"
                                 role="alert">
                                <div class="toast-header">
                                    <img alt="brand-logo" class="me-2" height="16" src="/img/logo.png"/>
                                    <strong class="me-auto">SmartAdmin</strong>
                                    <small>11 mins ago</small>
                                    <button aria-label="Close" class="btn-close" data-bs-dismiss="toast"
                                            type="button"></button>
                                </div>
                                <div class="toast-body">
                                    Hello, world! This is a toast message.
                                </div>
                            </div> <!--end toast-->
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Placement <span class="fw-300"><i>Toasts</i></span>
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
                            Place toasts with custom CSS as you need them. The top right is often used for
                            notifications, as is the top middle. If you're only ever going to show one toast at a time,
                            put the positioning styles right on the <code>.toast</code>
                        </div>
                        <div aria-atomic="true" aria-live="polite"
                             class="d-flex justify-content-center align-items-center height-lg bg-info pattern-1">
                            <!-- Then put toasts within -->
                            <div aria-atomic="true" aria-live="assertive" class="toast fade show" data-toggle="toast"
                                 role="alert">
                                <div class="toast-header">
                                    <img alt="brand-logo" class="me-2" height="16" src="/img/logo.png"/>
                                    <strong class="me-auto">SmartAdmin</strong>
                                    <small>11 mins ago</small>
                                    <button aria-label="Close" class="btn-close" data-bs-dismiss="toast"
                                            type="button"></button>
                                </div>
                                <div class="toast-body">
                                    Hello, world! This is a toast message.
                                </div>
                            </div>
                            <!--end toast-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
