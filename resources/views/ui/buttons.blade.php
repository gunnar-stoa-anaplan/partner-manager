@extends('layouts.vertical', ['pageTitle' => 'Buttons'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Buttons', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Use custom button styles for actions in forms, dialogs, and more. Includes support for a handful of contextual variations, sizes, states, and more.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 col-xl-6">
            <!--Button colors-->
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Button <span class="fw-300"><i>Colors</i></span>
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
                            SmartAdmin includes nine predefined button styles, each serving its own semantic purpose.
                        </div>
                        <h5 class="frame-heading">
                            Default
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-primary" type="button">Primary</button>
                                <button class="btn btn-secondary" type="button">Secondary</button>
                                <button class="btn btn-success" type="button">Success</button>
                                <button class="btn btn-info" type="button">Info</button>
                                <button class="btn btn-warning" type="button">Warning</button>
                                <button class="btn btn-danger" type="button">Danger</button>
                                <button class="btn btn-dark" type="button">Dark</button>
                                <button class="btn btn-light" type="button">Light</button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Rounded
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-primary btn-pills" type="button">Primary</button>
                                <button class="btn btn-secondary btn-pills" type="button">Secondary</button>
                                <button class="btn btn-success btn-pills" type="button">Success</button>
                                <button class="btn btn-info btn-pills" type="button">Info</button>
                                <button class="btn btn-warning btn-pills" type="button">Warning</button>
                                <button class="btn btn-danger btn-pills" type="button">Danger</button>
                                <button class="btn btn-dark btn-pills" type="button">Dark</button>
                                <button class="btn btn-light btn-pills" type="button">Light</button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Unique style
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-default" type="button">Default</button>
                                <button class="btn btn-default btn-pills" type="button">Default</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Block-->
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Block <span class="fw-300"><i>Buttons</i></span>
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
                            Create block level buttons—those that span the full width of a parent—by adding
                            <code>.btn-block</code>
                        </div>
                        <div class="frame-wrap text-center mb-0">
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="demo-v-spacing">
                                        <button class="btn btn-primary btn-lg btn-block" type="button">Block
                                            <span>large</span>
                                        </button>
                                        <button class="btn btn-primary btn-block" type="button">Block
                                            <span>default</span>
                                        </button>
                                        <button class="btn btn-primary btn-sm btn-block" type="button">Block
                                            <span>small</span>
                                        </button>
                                        <button class="btn btn-primary btn-xs btn-block" type="button">Block
                                            <span>extra small</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="demo-v-spacing mt-3 mt-xl-0">
                                        <button class="btn btn-success btn-pills btn-lg btn-block" type="button">Block
                                            <span>large</span>
                                        </button>
                                        <button class="btn btn-success btn-pills btn-block" type="button">Block
                                            <span>default</span>
                                        </button>
                                        <button class="btn btn-success btn-pills btn-sm btn-block" type="button">Block
                                            <span>small</span>
                                        </button>
                                        <button class="btn btn-success btn-pills btn-xs btn-block" type="button">Block
                                            <span>extra small</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Sizes-->
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Option <span class="fw-300"><i>Sizes</i></span>
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
                            Alter button sizes by using the classes <code>.btn-lg</code>, <code>.btn-sm</code>,
                            <code>.btn-xs</code>
                        </div>
                        <h5 class="frame-heading">
                            Largest size
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-lg btn-primary" type="button">Primary</button>
                                <button class="btn btn-lg btn-secondary" type="button">Secondary</button>
                                <button class="btn btn-lg btn-default" type="button">Default</button>
                                <button class="btn btn-lg btn-success" type="button">Success</button>
                                <button class="btn btn-lg btn-warning" type="button">Warning</button>
                                <button class="btn btn-lg btn-info" type="button">Info</button>
                                <button class="btn btn-lg btn-danger" type="button">Danger</button>
                                <button class="btn btn-lg btn-dark" type="button">Dark</button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Default size
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-primary" type="button">Primary</button>
                                <button class="btn btn-secondary" type="button">Secondary</button>
                                <button class="btn btn-default" type="button">Default</button>
                                <button class="btn btn-success" type="button">Success</button>
                                <button class="btn btn-warning" type="button">Warning</button>
                                <button class="btn btn-info" type="button">Info</button>
                                <button class="btn btn-danger" type="button">Danger</button>
                                <button class="btn btn-dark" type="button">Dark</button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Small size
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-sm btn-primary" type="button">Primary</button>
                                <button class="btn btn-sm btn-secondary" type="button">Secondary</button>
                                <button class="btn btn-sm btn-default" type="button">Default</button>
                                <button class="btn btn-sm btn-success" type="button">Success</button>
                                <button class="btn btn-sm btn-warning" type="button">Warning</button>
                                <button class="btn btn-sm btn-info" type="button">Info</button>
                                <button class="btn btn-sm btn-danger" type="button">Danger</button>
                                <button class="btn btn-sm btn-dark" type="button">Dark</button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Smallest size
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-xs btn-primary" type="button">Primary</button>
                                <button class="btn btn-xs btn-secondary" type="button">Secondary</button>
                                <button class="btn btn-xs btn-default" type="button">Default</button>
                                <button class="btn btn-xs btn-success" type="button">Success</button>
                                <button class="btn btn-xs btn-warning" type="button">Warning</button>
                                <button class="btn btn-xs btn-info" type="button">Info</button>
                                <button class="btn btn-xs btn-danger" type="button">Danger</button>
                                <button class="btn btn-xs btn-dark" type="button">Dark</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--With icons-->
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Button <span class="fw-300"><i>with Icons</i></span>
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
                            We use the icon tags to insert icons into buttons
                        </div>
                        <div class="demo">
                            <button class="btn btn-lg btn-primary" type="button">
                                <span class="fal fa-print me-1"></span>
                                Print
                            </button>
                            <button class="btn btn-lg btn-secondary" type="button">
                                <span class="fal fa-volume-mute me-1"></span>
                                Mute
                            </button>
                            <button class="btn btn-lg btn-default" type="button">
                                <span class="fal fa-check me-1"></span>
                                Submit
                            </button>
                            <button class="btn btn-lg btn-success" type="button">
                                <span class="fal fa-download me-1"></span>
                                Download
                            </button>
                            <button class="btn btn-lg btn-warning" type="button">
                                <span class="fal fa-exclamation-triangle me-1"></span>
                                Scan Device
                            </button>
                            <button class="btn btn-lg btn-info" type="button">
                                <span class="fal fa-bug me-1"></span>
                                Report Bug
                            </button>
                            <button class="btn btn-lg btn-danger" type="button">
                                <span class="fal fa-times me-1"></span>
                                Delete
                            </button>
                            <button class="btn btn-lg btn-dark" type="button">
                                <span class="fal fa-eject me-1"></span>
                                Eject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!--Icon buttons-->
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Icons <span class="fw-300"><i>example</i></span>
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
                            Convert button to an icon button by adding the class <code>.btn-icon</code>
                        </div>
                        <h5 class="frame-heading">
                            Various sizes
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <a class="btn btn-primary btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-secondary btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute fs-md"></i>
                                </a>
                                <a class="btn btn-default btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-success btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-warning btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-info btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-danger btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-dark btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </div>
                            <div class="demo">
                                <a class="btn btn-primary btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-secondary btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute fs-md"></i>
                                </a>
                                <a class="btn btn-default btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-success btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-warning btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-info btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-danger btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-dark btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </div>
                            <div class="demo">
                                <a class="btn btn-primary btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-secondary btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute"></i>
                                </a>
                                <a class="btn btn-default btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-success btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-warning btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-info btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-danger btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-dark btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </div>
                            <div class="demo">
                                <a class="btn btn-primary btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-secondary btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute"></i>
                                </a>
                                <a class="btn btn-default btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-success btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-warning btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-info btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-danger btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-dark btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Rounded
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <a class="btn btn-primary btn-lg btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-secondary btn-lg btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute fs-md"></i>
                                </a>
                                <a class="btn btn-default btn-lg btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-success btn-lg btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-warning btn-lg btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-info btn-lg btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-danger btn-lg btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-dark btn-lg btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </div>
                            <div class="demo">
                                <a class="btn btn-primary btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-secondary btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute fs-md"></i>
                                </a>
                                <a class="btn btn-default btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-success btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-warning btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-info btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-danger btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-dark btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </div>
                            <div class="demo">
                                <a class="btn btn-primary btn-sm btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-secondary btn-sm btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute"></i>
                                </a>
                                <a class="btn btn-default btn-sm btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-success btn-sm btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-warning btn-sm btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-info btn-sm btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-danger btn-sm btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-dark btn-sm btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </div>
                            <div class="demo">
                                <a class="btn btn-primary btn-xs btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-secondary btn-xs btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute"></i>
                                </a>
                                <a class="btn btn-default btn-xs btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-success btn-xs btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-warning btn-xs btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-info btn-xs btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-danger btn-xs btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-dark btn-xs btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Hover dot effect
                        </h5>
                        <div class="frame-wrap">
                            <p class="demo">
                                <a class="btn btn-primary btn-lg btn-icon rounded-circle hover-effect-dot"
                                   href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-secondary btn-lg btn-icon rounded-circle hover-effect-dot"
                                   href="javascript:void(0);">
                                    <i class="fal fa-volume-mute fs-md"></i>
                                </a>
                                <a class="btn btn-success btn-lg btn-icon hover-effect-dot" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-xl-6">
            <!--Button colors (outline)-->
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Button <span class="fw-300"><i>Outlines</i></span>
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
                            Replace the default modifier classes with<code>.btn-outline-*</code>
                        </div>
                        <h5 class="frame-heading">
                            Outline
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-outline-primary" type="button">Primary</button>
                                <button class="btn btn-outline-secondary" type="button">Secondary</button>
                                <button class="btn btn-outline-success" type="button">Success</button>
                                <button class="btn btn-outline-info" type="button">Info</button>
                                <button class="btn btn-outline-warning" type="button">Warning</button>
                                <button class="btn btn-outline-danger" type="button">Danger</button>
                                <button class="btn btn-outline-dark" type="button">Dark</button>
                                <button class="btn btn-outline-light" type="button">Light</button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Rounded outline
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-outline-primary btn-pills" type="button">Primary</button>
                                <button class="btn btn-outline-secondary btn-pills" type="button">Secondary</button>
                                <button class="btn btn-outline-success btn-pills" type="button">Success</button>
                                <button class="btn btn-outline-info btn-pills" type="button">Info</button>
                                <button class="btn btn-outline-warning btn-pills" type="button">Warning</button>
                                <button class="btn btn-outline-danger btn-pills" type="button">Danger</button>
                                <button class="btn btn-outline-dark btn-pills" type="button">Dark</button>
                                <button class="btn btn-outline-light btn-pills" type="button">Light</button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Unique style
                        </h5>
                        <div class="frame-wrap mb-0">
                            <div class="demo">
                                <button class="btn btn-outline-default" type="button">Default</button>
                                <button class="btn btn-outline-default btn-pills" type="button">Default</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Block (outline)-->
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        Block <span class="fw-300"><i>Outlines</i></span>
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
                            Block buttons inherits outline button properties <code>.btn-outline-*</code>
                        </div>
                        <div class="frame-wrap text-center mb-0">
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="demo-v-spacing">
                                        <button class="btn btn-outline-primary btn-lg btn-block" type="button">Block
                                            <span>large</span>
                                        </button>
                                        <button class="btn btn-outline-primary btn-block" type="button">Block
                                            <span>default</span>
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm btn-block" type="button">Block
                                            <span>small</span>
                                        </button>
                                        <button class="btn btn-outline-primary btn-xs btn-block" type="button">Block
                                            <span>extra small</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="demo-v-spacing mt-3 mt-xl-0">
                                        <button class="btn btn-outline-success btn-pills btn-lg btn-block"
                                                type="button">Block
                                            <span>large</span>
                                        </button>
                                        <button class="btn btn-outline-success btn-pills btn-block" type="button">Block
                                            <span>default</span>
                                        </button>
                                        <button class="btn btn-outline-success btn-pills btn-sm btn-block"
                                                type="button">Block
                                            <span>small</span>
                                        </button>
                                        <button class="btn btn-outline-success btn-pills btn-xs btn-block"
                                                type="button">Block
                                            <span>extra small</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Sizes (outline)-->
            <div class="panel panel-icon" id="panel-8">
                <div class="panel-hdr">
                    <h2>
                        Option <span class="fw-300"><i>Sizes</i></span>
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
                            Alter button sizes by using the classes <code>.btn-lg</code>, <code>.btn-sm</code>,
                            <code>.btn-xs</code>
                        </div>
                        <h5 class="frame-heading">
                            Largest size
                        </h5>
                        <div class="frame-wrap">
                            <p class="demo">
                                <button class="btn btn-lg btn-outline-primary" type="button">Primary</button>
                                <button class="btn btn-lg btn-outline-secondary" type="button">Secondary</button>
                                <button class="btn btn-lg btn-outline-default" type="button">Default</button>
                                <button class="btn btn-lg btn-outline-success" type="button">Success</button>
                                <button class="btn btn-lg btn-outline-warning" type="button">Warning</button>
                                <button class="btn btn-lg btn-outline-info" type="button">Info</button>
                                <button class="btn btn-lg btn-outline-danger" type="button">Danger</button>
                                <button class="btn btn-lg btn-outline-dark" type="button">Dark</button>
                            </p>
                        </div>
                        <h5 class="frame-heading">
                            Default size
                        </h5>
                        <div class="frame-wrap">
                            <p class="demo">
                                <button class="btn btn-outline-primary" type="button">Primary</button>
                                <button class="btn btn-outline-secondary" type="button">Secondary</button>
                                <button class="btn btn-outline-default" type="button">Default</button>
                                <button class="btn btn-outline-success" type="button">Success</button>
                                <button class="btn btn-outline-warning" type="button">Warning</button>
                                <button class="btn btn-outline-info" type="button">Info</button>
                                <button class="btn btn-outline-danger" type="button">Danger</button>
                                <button class="btn btn-outline-dark" type="button">Dark</button>
                            </p>
                        </div>
                        <h5 class="frame-heading">
                            Small size
                        </h5>
                        <div class="frame-wrap">
                            <p class="demo">
                                <button class="btn btn-sm btn-outline-primary" type="button">Primary</button>
                                <button class="btn btn-sm btn-outline-secondary" type="button">Secondary</button>
                                <button class="btn btn-sm btn-outline-default" type="button">Default</button>
                                <button class="btn btn-sm btn-outline-success" type="button">Success</button>
                                <button class="btn btn-sm btn-outline-warning" type="button">Warning</button>
                                <button class="btn btn-sm btn-outline-info" type="button">Info</button>
                                <button class="btn btn-sm btn-outline-danger" type="button">Danger</button>
                                <button class="btn btn-sm btn-outline-dark" type="button">Dark</button>
                            </p>
                        </div>
                        <h5 class="frame-heading">
                            Smallest size
                        </h5>
                        <div class="frame-wrap">
                            <p class="demo">
                                <button class="btn btn-xs btn-outline-primary" type="button">Primary</button>
                                <button class="btn btn-xs btn-outline-secondary" type="button">Secondary</button>
                                <button class="btn btn-xs btn-outline-default" type="button">Default</button>
                                <button class="btn btn-xs btn-outline-success" type="button">Success</button>
                                <button class="btn btn-xs btn-outline-warning" type="button">Warning</button>
                                <button class="btn btn-xs btn-outline-info" type="button">Info</button>
                                <button class="btn btn-xs btn-outline-danger" type="button">Danger</button>
                                <button class="btn btn-xs btn-outline-dark" type="button">Dark</button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!--With icons (outline)-->
            <div class="panel panel-icon" id="panel-9">
                <div class="panel-hdr">
                    <h2>
                        Button <span class="fw-300"><i>with Icons</i></span>
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
                            We use the icon tags to insert icons into buttons
                        </div>
                        <div class="demo">
                            <button class="btn btn-lg btn-outline-primary" type="button">
                                <span class="fal fa-print me-1"></span>
                                Print
                            </button>
                            <button class="btn btn-lg btn-outline-secondary" type="button">
                                <span class="fal fa-volume-mute me-1"></span>
                                Mute
                            </button>
                            <button class="btn btn-lg btn-outline-default" type="button">
                                <span class="fal fa-check me-1"></span>
                                Submit
                            </button>
                            <button class="btn btn-lg btn-outline-success" type="button">
                                <span class="fal fa-download me-1"></span>
                                Download
                            </button>
                            <button class="btn btn-lg btn-outline-warning" type="button">
                                <span class="fal fa-exclamation-triangle me-1"></span>
                                Scan Device
                            </button>
                            <button class="btn btn-lg btn-outline-info" type="button">
                                <span class="fal fa-bug me-1"></span>
                                Report Bug
                            </button>
                            <button class="btn btn-lg btn-outline-danger" type="button">
                                <span class="fal fa-times me-1"></span>
                                Delete
                            </button>
                            <button class="btn btn-lg btn-outline-dark" type="button">
                                <span class="fal fa-eject me-1"></span>
                                Eject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!--Icon buttons (outline)-->
            <div class="panel panel-icon" id="panel-10">
                <div class="panel-hdr">
                    <h2>
                        Icon <span class="fw-300"><i>Outlined</i></span>
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
                            Convert button to an icon button by adding the class <code>.btn-icon</code>
                        </div>
                        <h5 class="frame-heading">
                            Various sizes
                        </h5>
                        <div class="frame-wrap">
                            <p class="demo">
                                <a class="btn btn-outline-primary btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-outline-secondary btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute fs-md"></i>
                                </a>
                                <a class="btn btn-outline-default btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-outline-success btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-outline-warning btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-outline-info btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-outline-danger btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-outline-dark btn-lg btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </p>
                            <p class="demo">
                                <a class="btn btn-outline-primary btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-outline-secondary btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute fs-md"></i>
                                </a>
                                <a class="btn btn-outline-default btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-outline-success btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-outline-warning btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-outline-info btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-outline-danger btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-outline-dark btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </p>
                            <p class="demo">
                                <a class="btn btn-outline-primary btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-outline-secondary btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute"></i>
                                </a>
                                <a class="btn btn-outline-default btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-outline-success btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-outline-warning btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-outline-info btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-outline-danger btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-outline-dark btn-sm btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </p>
                            <p class="demo">
                                <a class="btn btn-outline-primary btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-outline-secondary btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute"></i>
                                </a>
                                <a class="btn btn-outline-default btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-outline-success btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-outline-warning btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-outline-info btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-outline-danger btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-outline-dark btn-xs btn-icon" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </p>
                        </div>
                        <h5 class="frame-heading">
                            Rounded outline
                        </h5>
                        <div class="frame-wrap">
                            <p class="demo">
                                <a class="btn btn-outline-primary btn-lg btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-outline-secondary btn-lg btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-volume-mute fs-md"></i>
                                </a>
                                <a class="btn btn-outline-default btn-lg btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-outline-success btn-lg btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-outline-warning btn-lg btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-outline-info btn-lg btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-outline-danger btn-lg btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-outline-dark btn-lg btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </p>
                            <p class="demo">
                                <a class="btn btn-outline-primary btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-outline-secondary btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-volume-mute fs-md"></i>
                                </a>
                                <a class="btn btn-outline-default btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-outline-success btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-outline-warning btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-outline-info btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-outline-danger btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-outline-dark btn-icon rounded-circle" href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </p>
                            <p class="demo">
                                <a class="btn btn-outline-primary btn-sm btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-outline-secondary btn-sm btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-volume-mute"></i>
                                </a>
                                <a class="btn btn-outline-default btn-sm btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-outline-success btn-sm btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-outline-warning btn-sm btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-outline-info btn-sm btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-outline-danger btn-sm btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-outline-dark btn-sm btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </p>
                            <p class="demo">
                                <a class="btn btn-outline-primary btn-xs btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-outline-secondary btn-xs btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-volume-mute"></i>
                                </a>
                                <a class="btn btn-outline-default btn-xs btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-check"></i>
                                </a>
                                <a class="btn btn-outline-success btn-xs btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                                <a class="btn btn-outline-warning btn-xs btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-exclamation-triangle"></i>
                                </a>
                                <a class="btn btn-outline-info btn-xs btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-bug"></i>
                                </a>
                                <a class="btn btn-outline-danger btn-xs btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-times"></i>
                                </a>
                                <a class="btn btn-outline-dark btn-xs btn-icon rounded-circle"
                                   href="javascript:void(0);">
                                    <i class="fal fa-eject"></i>
                                </a>
                            </p>
                        </div>
                        <h5 class="frame-heading">
                            Hover dot effect
                        </h5>
                        <div class="frame-wrap">
                            <p class="demo">
                                <a class="btn btn-outline-primary btn-lg btn-icon rounded-circle hover-effect-dot"
                                   href="javascript:void(0);">
                                    <i class="fal fa-print"></i>
                                </a>
                                <a class="btn btn-outline-secondary btn-lg btn-icon rounded-circle hover-effect-dot"
                                   href="javascript:void(0);">
                                    <i class="fal fa-volume-mute fs-md"></i>
                                </a>
                                <a class="btn btn-outline-success btn-lg btn-icon hover-effect-dot"
                                   href="javascript:void(0);">
                                    <i class="fal fa-download"></i>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
