@extends('layouts.vertical', ['pageTitle' => 'Progressbars'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Progressbars', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Bootstrap progress bars visually represent the completion status of a task, with customizable colors, labels, and animated stripes.'])
@endsection

@section('content')
    <div class="info-container">
        <p class="mb-0">Progress components are built with two HTML elements, some CSS to set the width, and a few
            attributes.
            We don’t use the HTML5 <a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Element/progress"
                                      target="_blank">
                <code>&lt;progress&gt;</code></a> element, ensuring you can stack progress bars, animate them, and place
            text
            labels over them.</p>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <!--Default-->
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Default <span class="fw-300"><i>Progressbar</i></span>
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
                            The <code>.progress</code> is a wrapper to indicate the max value of the progress bar. We
                            use the inner <code>.progress-bar</code> to indicate the progress so far. The <code>.progress-bar</code>
                            requires an inline style, utility class, or custom CSS to set their width. You can add some
                            role and aria attributes to make it accessible.
                        </div>
                        <div class="progress">
                            <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="65" class="progress-bar"
                                 role="progressbar" style="width: 65%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Various height-->
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Various <span class="fw-300"><i>height</i></span>
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
                            Control the height by adding style <code>height:"value"</code> to <code>.progress</code>
                            element or you could use the prebuilt classes for a more uniform structure, <code>.progress-xs</code>,
                            <code>.progress-sm</code>, <code>.progress-md</code>, <code>.progress-lg</code>, <code>.progress-xl</code>
                        </div>
                        <div class="demo-v-spacing">
                            <div class="progress progress-xs">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="75" class="progress-bar"
                                     role="progressbar" style="width: 75%;"></div>
                            </div>
                            <div class="progress progress-sm">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="55" class="progress-bar"
                                     role="progressbar" style="width: 55%;"></div>
                            </div>
                            <div class="progress progress-md">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="35" class="progress-bar"
                                     role="progressbar" style="width: 35%;"></div>
                            </div>
                            <div class="progress progress-lg">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="25" class="progress-bar"
                                     role="progressbar" style="width: 25%;"></div>
                            </div>
                            <div class="progress progress-xl">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="15" class="progress-bar"
                                     role="progressbar" style="width: 15%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Label display-->
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Label <span class="fw-300"><i>display</i></span>
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
                            Add labels to your progress bars by placing text within the <code>.progress-bar</code>
                        </div>
                        <div class="progress">
                            <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="25" class="progress-bar"
                                 role="progressbar" style="width: 25%;">25%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Solid backgrounds-->
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Solid <span class="fw-300"><i>backgrounds</i></span>
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
                            Control the background by adding avaialble <a href="{{ route('second', ['utilities', 'colorpalette']) }}"
                                                                          target="_blank">color pallets</a> to <code>.progress</code>
                            class
                        </div>
                        <div class="demo-v-spacing">
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="20"
                                     class="progress-bar bg-secondary" role="progressbar" style="width: 20%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="30"
                                     class="progress-bar bg-primary" role="progressbar" style="width: 30%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="40"
                                     class="progress-bar bg-success" role="progressbar" style="width: 40%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="50"
                                     class="progress-bar bg-info" role="progressbar" style="width: 50%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="60"
                                     class="progress-bar bg-warning" role="progressbar" style="width: 60%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="80"
                                     class="progress-bar bg-danger" role="progressbar" style="width: 80%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <!--Multiple bars-->
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Multiple <span class="fw-300"><i>bars</i></span>
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
                            Include multiple progress bars in a progress component if you need
                        </div>
                        <div class="progress">
                            <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="15" class="progress-bar"
                                 role="progressbar" style="width: 10%"></div>
                            <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="20"
                                 class="progress-bar bg-success" role="progressbar" style="width: 20%"></div>
                            <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="5" class="progress-bar bg-info"
                                 role="progressbar" style="width: 15%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Gradient backgrounds-->
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Gradient <span class="fw-300"><i>backgrounds</i></span>
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
                            Control the background by adding avaialble <a href="{{ route('second', ['utilities', 'colorpalette']) }}"
                                                                          target="_blank">color pallets</a> to <code>.progress</code>
                            class
                        </div>
                        <div class="demo-v-spacing">
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="20"
                                     class="progress-bar bg-primary-700 bg-success-gradient" role="progressbar"
                                     style="width: 20%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="30"
                                     class="progress-bar bg-primary-300 bg-primary-gradient" role="progressbar"
                                     style="width: 30%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="40"
                                     class="progress-bar bg-success-700 bg-primary-gradient" role="progressbar"
                                     style="width: 40%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="50"
                                     class="progress-bar bg-info bg-info-gradient" role="progressbar"
                                     style="width: 50%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="60"
                                     class="progress-bar bg-danger-300 bg-warning-gradient" role="progressbar"
                                     style="width: 60%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="80"
                                     class="progress-bar bg-success-500 bg-danger-gradient" role="progressbar"
                                     style="width: 80%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Striped backrounds-->
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        Striped <span class="fw-300"><i>backrounds</i></span>
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
                            Add <code>.progress-bar-striped</code> to any <code>.progress-bar</code> to apply a stripe
                            via CSS gradient over the progress bar’s background color
                        </div>
                        <div class="demo-v-spacing">
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="20"
                                     class="progress-bar progress-bar-striped bg-secondary" role="progressbar"
                                     style="width: 20%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="30"
                                     class="progress-bar progress-bar-striped bg-primary" role="progressbar"
                                     style="width: 30%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="40"
                                     class="progress-bar progress-bar-striped bg-success" role="progressbar"
                                     style="width: 40%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="50"
                                     class="progress-bar progress-bar-striped bg-info" role="progressbar"
                                     style="width: 50%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="60"
                                     class="progress-bar progress-bar-striped bg-warning" role="progressbar"
                                     style="width: 60%"></div>
                            </div>
                            <div class="progress">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="80"
                                     class="progress-bar progress-bar-striped bg-danger" role="progressbar"
                                     style="width: 80%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Animated-->
            <div class="panel panel-icon" id="panel-8">
                <div class="panel-hdr">
                    <h2>
                        Animated <span class="fw-300"><i>bars</i></span>
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
                            The striped gradient can also be animated. Add <code>.progress-bar-animated</code> to <code>.progress-bar</code>
                            to animate the stripes right to left via CSS3 animations.
                        </div>
                        <div class="demo js-progress-animated">
                            <div class="progress progress-xl">
                                <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="60"
                                     class="progress-bar progress-bar-striped bg-primary-500" role="progressbar"
                                     style="width: 60%"></div>
                            </div>
                        </div>
                        <button aria-pressed="false" class="btn btn-secondary" data-action="toggle-swap"
                                data-target=".js-progress-animated .progress-bar" data-toggle="button"
                                data-toggleclass="progress-bar-animated" type="button">
                            Toggle animation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
