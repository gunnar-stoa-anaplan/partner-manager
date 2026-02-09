@extends('layouts.vertical', ['pageTitle' => 'Borders'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Borders', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'System Utilities', 'pageSubText' => 'Bootstrap borders are utility classes that allow you to quickly add, remove, or modify borders on elements with predefined styles, colors, and widths.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <!--Colorful Borders-->
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Colorful <span class="fw-300"><i>borders</i></span>
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
                            Use color profiles to the following border colors <code>,border-primary</code>, <code>.border-secondary</code>,
                            <code>.border-success</code>, <code>.border-danger</code>, <code>.border-warning</code>,
                            <code>.border-info</code>, <code>.border-light</code>, <code>.border-dark</code>, <code>.border-white</code>,
                            <code>.border-transparent</code>, <code>.border-faded</code>
                        </div>
                        <div class="demo d-flex flex-wrap justify-content-evenly gap-1">
                            <div class="border border-primary width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-primary-subtle width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-secondary width-10 height-10 d-inline-block bg-faded"></div>
                            <div
                                class="border border-secondary-subtle width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-success width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-success-subtle width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-danger width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-danger-subtle width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-warning width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-warning-subtle width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-info width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-info-subtle width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-light width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-light-subtle width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-dark width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-dark-subtle width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-black width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-white width-10 height-10 d-inline-block bg-faded"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Add Borders-->
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Add <span class="fw-300"><i>Borders</i></span>
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
                            Add border using <code>.border</code>, <code>.border-top</code>, <code>.border-end</code>,
                            <code>.border-end</code>, <code>.border-bottom</code>, <code>.border-start</code>
                        </div>
                        <div class="demo d-flex flex-wrap justify-content-evenly justify-content-md-start gap-1">
                            <div class="border width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border-top width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border-end width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border-bottom width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border-start width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border rounded-circle width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border rounded width-10 height-10 d-inline-block bg-faded"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Border Opacity-->
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Border <span class="fw-300"><i>Opacity</i></span>
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
                            Add border using <code>.border-opacity-75</code>, <code>.border-opacity-50</code>,
                            <code>.border-opacity-25</code>, <code>.border-opacity-10</code>.
                        </div>
                        <div class="demo d-flex flex-wrap justify-content-evenly justify-content-md-start gap-1">
                            <div class="border border-primary width-10 height-10 d-inline-block bg-faded"></div>
                            <div
                                class="border border-primary border-opacity-75 width-10 height-10 d-inline-block bg-faded"></div>
                            <div
                                class="border border-primary border-opacity-50 width-10 height-10 d-inline-block bg-faded"></div>
                            <div
                                class="border border-primary border-opacity-25 width-10 height-10 d-inline-block bg-faded"></div>
                            <div
                                class="border border-primary border-opacity-10 width-10 height-10 d-inline-block bg-faded"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Border Thickness-->
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Border <span class="fw-300"><i>Thickness</i></span>
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
                            Border thickness using <code>border-1</code>, <code>border-2</code>,
                            <code>border-3</code>, <code>border-4</code>, <code>border-5</code>.
                        </div>
                        <div class="demo d-flex flex-wrap justify-content-evenly justify-content-md-start gap-1">
                            <div class="border border-1 width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-2 width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-3 width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-4 width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-5 width-10 height-10 d-inline-block bg-faded"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <!--Rounded-->
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Rounded <span class="fw-300"><i>example</i></span>
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
                            Round edges or control each corner using <code>.rounded</code>, <code>rounded-top</code>,
                            <code>rounded-end</code>, <code>rounded-bottom</code>, <code>rounded-start</code>, <code>rounded-circle</code>,
                            <code>rounded-0</code>, <code>.rounded-plus</code>, <code>.rounded-{sm,lg}</code>, <code>.rounded-pill</code>,
                            <code>border-{top,bottom}-{left,right}-radius-0</code>
                        </div>
                        <div class="demo d-flex flex-wrap justify-content-evenly justify-content-md-start gap-1">
                            <div class="rounded bg-success-50 width-10 height-10 d-inline-block"></div>
                            <div class="rounded-top bg-success-200 width-10 height-10 d-inline-block"></div>
                            <div class="rounded-end bg-success-200 width-10 height-10 d-inline-block"></div>
                            <div class="rounded-bottom bg-success-200 width-10 height-10 d-inline-block"></div>
                            <div class="rounded-start bg-success-200 width-10 height-10 d-inline-block"></div>
                            <div class="rounded-circle bg-danger-200 width-10 height-10 d-inline-block"></div>
                            <div class="rounded-plus bg-danger-50 width-10 height-10 d-inline-block"></div>
                            <div class="rounded-0 bg-primary-50 width-10 height-10 d-inline-block"></div>
                            <div class="rounded-pill bg-info-50 width-10 height-10 d-inline-block"></div>
                        </div>
                        <div class="panel-tag">
                            Control rounded depth using <code>rounded-1</code>, <code>rounded-2</code>,
                            <code>rounded-3</code>, <code>rounded-4</code>,
                            <code>rounded-5</code>
                        </div>
                        <div class="demo d-flex flex-wrap justify-content-evenly justify-content-md-start gap-1">
                            <div class="rounded-1 width-10 height-10 d-inline-block bg-fusion-50"></div>
                            <div class="rounded-2 width-10 height-10 d-inline-block bg-fusion-50"></div>
                            <div class="rounded-3 width-10 height-10 d-inline-block bg-fusion-50"></div>
                            <div class="rounded-4 width-10 height-10 d-inline-block bg-fusion-50"></div>
                            <div class="rounded-5 width-10 height-10 d-inline-block bg-fusion-50"></div>
                        </div>
                        <div class="panel-tag">
                            Mix and match rounded corners using <code>rounded-bottom-1</code>,
                            <code>rounded-start-2</code>,
                            <code>rounded-end-circle</code>, <code>rounded-start-pill</code>, <code>rounded-5
                                rounded-top-0</code>
                        </div>
                        <div class="demo d-flex flex-wrap justify-content-evenly justify-content-md-start gap-1">
                            <div class="rounded-bottom-1 width-10 height-10 d-inline-block bg-fusion-50"></div>
                            <div class="rounded-start-2 width-10 height-10 d-inline-block bg-fusion-50"></div>
                            <div class="rounded-end-circle width-10 height-10 d-inline-block bg-fusion-50"></div>
                            <div class="rounded-start-pill width-10 height-10 d-inline-block bg-fusion-50"></div>
                            <div class="rounded-5 rounded-top-0 width-10 height-10 d-inline-block bg-fusion-50"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Remove borders-->
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Remove <span class="fw-300"><i>borders</i></span>
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
                            Subtract borders with <code>border-0</code>, <code>border-top-0</code>,
                            <code>border-end-0</code>, <code>border-bottom-0</code>, <code>border-start-0</code>, <code>.border-transparent</code>
                        </div>
                        <div class="demo d-flex flex-wrap justify-content-evenly justify-content-md-start gap-1">
                            <div class="border-0 width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-top-0 width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-end-0 width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-bottom-0 width-10 height-10 d-inline-block bg-faded"></div>
                            <div class="border border-start-0 width-10 height-10 d-inline-block bg-faded"></div>
                            <div
                                class="border rounded-circle border-end-0 border-start-0 width-10 height-10 d-inline-block bg-faded"></div>
                            <div
                                class="border rounded width-10 border-end-0 border-start-0 height-10 d-inline-block bg-faded"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
