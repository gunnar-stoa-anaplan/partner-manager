@extends('layouts.vertical', ['pageTitle' => 'Popovers'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Popovers', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Bootstrap popovers are interactive, dismissible overlays that provide additional content when users click or hover over an element.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Basic <span class="fw-300"><i>Popover</i></span>
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
                            Minimalistic popover with no content - just title
                        </div>
                        <button class="btn btn-secondary" data-bs-title="Popover title" data-bs-toggle="popover"
                                type="button">
                            Click to toggle popover
                        </button>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Popover <span class="fw-300"><i>Content</i></span>
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
                            <p>Default content value if <code>data-content={string|element|function}</code> attribute
                                isn't present.</p>
                            <p>If a function is given, it will be called with its <code>this</code> reference set to the
                                element that the popover is attached to.</p>
                        </div>
                        <button class="btn btn-secondary btn-lg"
                                data-bs-content="And here's some amazing content. It's very engaging. Right?"
                                data-bs-title="Popover title" data-bs-toggle="popover" type="button">
                            Popover with content
                        </button>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Popover <span class="fw-300"><i>Animation</i></span>
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
                            Disable the CSS fade transition to the popover using attribute <code>data-bs-animation="false"</code>
                        </div>
                        <button class="btn btn-secondary" data-bs-animation="false"
                                data-bs-content="And here's some amazing content. It's very engaging. Right?"
                                data-bs-placement="top" data-bs-title="Popover title" data-bs-toggle="popover"
                                type="button">
                            Not Animated
                        </button>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Popover <span class="fw-300"><i>Title</i></span>
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
                            <p>Default title value if <code>title</code> attribute isn't present.</p>
                            <p>If a function is given, it will be called with its <code>this</code> reference set to the
                                element that the popover is attached to.</p>
                        </div>
                        <button class="btn btn-secondary"
                                data-bs-content="And here's some amazing content. It's very engaging. Right?"
                                data-bs-original-title="Original Title" data-bs-toggle="popover" type="button">
                            Display popover with original title
                        </button>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Popover <span class="fw-300"><i>Container</i></span>
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
                            Appends the popover to a specific element. Example: <code>container: 'body'</code>. This
                            option is particularly useful in that it allows you to position the popover in the flow of
                            the document near the triggering element - which will prevent the popover from floating away
                            from the triggering element during a window resize
                        </div>
                        <div class="d-flex align-items-end">
                            <button class="btn btn-secondary" data-bs-container=".js-test-container"
                                    data-bs-content="And here's some amazing content. It's very engaging. Right?"
                                    data-bs-title="Popover title" data-bs-toggle="popover" type="button">
                                Display popover
                            </button>
                            <a class="btn btn-success btn-xs ms-auto" data-action="toggle-swap"
                               data-target=".js-test-container" data-toggleclass="d-none" href="javascript:void(0);">
                                hide container
                            </a>
                        </div>
                        <div class="p-3 rounded bg-warning-100 mt-3 js-test-container">
                            The popover is nested inside this container but displayed on the element above. Once this
                            container is hidden the popover will not be visible. Try hiding this container by pressing
                            the "hide" button above
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Delayed <span class="fw-300"><i>Popover</i></span>
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
                            <p>Delay showing and hiding the popover (ms) - does not apply to manual trigger type. If a
                                number is supplied, delay is applied to both hide/show</p>
                            <p>Object structure is: <code>delay: { "show": 500, "hide": 100 }</code></p>
                        </div>
                        <button class="btn btn-secondary btn-lg"
                                data-bs-content="And here's some amazing content. It's very engaging. Right?"
                                data-bs-delay='{ "show": 500, "hide": 500 }' data-bs-placement="auto"
                                data-bs-title="I was 1/2 sec late!" data-bs-toggle="popover" type="button">
                            Delayed toggle
                        </button>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        Popover <span class="fw-300"><i>Placement</i></span>
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
                            <p>Four options are available: top, right, bottom, and left. Directions are mirrored when
                                using Bootstrap in RTL. Set <code>data-bs-placement</code> to change the direction.</p>
                        </div>
                        <div class="demo">
                            <button class="btn btn-secondary" data-bs-container="body"
                                    data-bs-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus."
                                    data-bs-placement="auto" data-bs-toggle="popover" type="button">
                                Auto
                            </button>
                            <button class="btn btn-secondary" data-bs-container="body"
                                    data-bs-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus."
                                    data-bs-placement="top" data-bs-toggle="popover" type="button">
                                Top
                            </button>
                            <button class="btn btn-secondary" data-bs-container="body"
                                    data-bs-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus."
                                    data-bs-placement="right" data-bs-toggle="popover" type="button">
                                Right
                            </button>
                            <button class="btn btn-secondary" data-bs-container="body" data-bs-content="Vivamus
                                sagittis lacus vel augue laoreet rutrum faucibus." data-bs-placement="bottom"
                                    data-bs-toggle="popover" type="button">
                                Down
                            </button>
                            <button class="btn btn-secondary" data-bs-container="body"
                                    data-bs-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus."
                                    data-bs-placement="left" data-bs-toggle="popover" type="button">
                                Left
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-8">
                <div class="panel-hdr">
                    <h2>
                        Popover <span class="fw-300"><i>Triggers</i></span>
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
                            How popover is triggered - click | hover | focus | manual. You may pass multiple triggers;
                            separate them with a space. <code>manual</code> cannot be combined with any other trigger.
                        </div>
                        <div class="demo">
                            <button class="btn btn-outline-success btn-lg" data-bs-content="Display on hover"
                                    data-bs-placement="top" data-bs-toggle="popover" data-bs-trigger="hover"
                                    tabindex="0" type="button">
                                Hover
                            </button>
                            <button class="btn btn-outline-info btn-lg" data-bs-content="Display on focus"
                                    data-bs-placement="top" data-bs-toggle="popover" data-bs-trigger="focus"
                                    tabindex="0" type="button">
                                Focus
                            </button>
                            <button class="btn btn-outline-warning btn-lg" data-bs-content="Display on click"
                                    data-bs-placement="top" data-bs-toggle="popover" data-bs-trigger="click"
                                    tabindex="0" type="button">
                                Click
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-9">
                <div class="panel-hdr">
                    <h2>
                        Popover <span class="fw-300"><i>Template</i></span>
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
                            <p>Base HTML to use when creating a popover in Bootstrap 5. The popover's <code>title</code>
                                is injected into an element with the <code>.popover-header</code> class, and the <code>content</code>
                                is injected into an element with the <code>.popover-body</code> class. The popover
                                includes an <code>.arrow</code> element for the pointer. The outermost wrapper of the
                                popover must have the <code>.popover</code> class, and it is automatically generated by
                                Bootstrap's JavaScript when initializing a popover.</p>
                        </div>
                        <div class="demo">
                            <button class="btn btn-primary" data-bs-content="This popover is themed via CSS variables."
                                    data-bs-custom-class="primary-popover" data-bs-placement="top"
                                    data-bs-title="Custom popover" data-bs-toggle="popover" type="button">
                                Primary
                            </button>
                            <button class="btn btn-secondary"
                                    data-bs-content="This popover is themed via CSS variables."
                                    data-bs-custom-class="secondary-popover" data-bs-placement="top"
                                    data-bs-title="Custom popover" data-bs-toggle="popover" type="button">
                                Secondary
                            </button>
                            <button class="btn btn-success" data-bs-content="This popover is themed via CSS variables."
                                    data-bs-custom-class="success-popover" data-bs-placement="top"
                                    data-bs-title="Custom popover" data-bs-toggle="popover" type="button">
                                Success
                            </button>
                            <button class="btn btn-info" data-bs-content="This popover is themed via CSS variables."
                                    data-bs-custom-class="info-popover" data-bs-placement="top"
                                    data-bs-title="Custom popover" data-bs-toggle="popover" type="button">
                                Info
                            </button>
                            <button class="btn btn-warning" data-bs-content="This popover is themed via CSS variables."
                                    data-bs-custom-class="warning-popover" data-bs-placement="top"
                                    data-bs-title="Custom popover" data-bs-toggle="popover" type="button">
                                Warning
                            </button>
                            <button class="btn btn-danger" data-bs-content="This popover is themed via CSS variables."
                                    data-bs-custom-class="danger-popover" data-bs-placement="top"
                                    data-bs-title="Custom popover" data-bs-toggle="popover" type="button">
                                Danger
                            </button>
                            <button class="btn btn-dark" data-bs-content="This popover is themed via CSS variables."
                                    data-bs-custom-class="dark-popover" data-bs-placement="top"
                                    data-bs-title="Custom popover" data-bs-toggle="popover" type="button">
                                Dark
                            </button>
                            <button class="btn btn-light" data-bs-content="This popover is themed via CSS variables."
                                    data-bs-custom-class="light-popover" data-bs-placement="top"
                                    data-bs-title="Custom popover" data-bs-toggle="popover" type="button">
                                Light
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-10">
                <div class="panel-hdr">
                    <h2>
                        Dismissable <span class="fw-300"><i>Popover</i></span>
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
                            Use the focus trigger to dismiss popovers on the user’s next click of an element other than
                            the toggle element.
                            <strong>Dismissing on next click requires specific HTML for proper cross-browser and
                                cross-platform behavior.</strong>
                        </div>
                        <div class="demo">
                            <a class="btn btn-lg btn-outline-danger"
                               data-bs-content="And here's some amazing content. It's very engaging. Right?"
                               data-bs-custom-class="dismissable-popover" data-bs-placement="top"
                               data-bs-title="Dismissible popover" data-bs-toggle="popover" data-bs-trigger="focus"
                               role="button" tabindex="0">Dismissible popover</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
