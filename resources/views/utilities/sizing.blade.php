@extends('layouts.vertical', ['pageTitle' => 'Sizing'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Sizing', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'System Utilities', 'pageSubText' => 'Easily make an element as wide or as tall with our width and height utilities.'])
@endsection

@section('content')
    <div class="info-container">
        <p class="mb-0">
            You are able to reset the height and width of an object by adding class <code>.height-mobile-auto</code> and
            <code>.width-mobile-auto</code>. Expand full height and width of your mobile device by adding <code>.expand-full-height-on-mobile</code>
            and <code>.expand-full-width-on-mobile</code>. Reset max and minium width/height by using the following
            modifier: <code>.h-auto</code>, <code>.w-auto</code>, <code>.max-width-reset</code>,
            <code>.max-height-reset</code>, and <code>.min-width-0</code>
        </p>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Width <span class="fw-300"><i>presets</i></span>
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
                            Uniform widths to keep things aligned. See <code>helpers.scss</code> file for more details.
                            You can also set <code>.width-0</code> to remove any given width, or you can use <code>.w-auto</code>
                            to undo explicitly specified widths
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-1
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 1.5rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-2
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 2rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-3
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 2.5rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-3"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-4
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 2.75rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-4"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-5
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 3rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-5"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-6
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 3.25rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-6"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-7
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 3.5rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-7"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-8
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 3.75rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-8"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-9
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 4rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-9"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-10
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 4.25rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-10"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-xs
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change <u>min-width</u> to 5rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-xs"
                                             style="max-width:5rem"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-sm
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change <u>min-width</u> to 10rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-sm"
                                             style="max-width:10rem"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-lg
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change <u>min-width</u> to 15rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-lg"
                                             style="max-width:15rem"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .width-xl
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change <u>min-width</u> to 20rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 width-xl"
                                             style="max-width:20rem"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-5"/>
                        <div class="row">
                            <div class="col-md-6 col-lg-6 col-xl-3">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .w-25
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 25%</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 w-25"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-xl-3">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .w-50
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 50%</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 w-50"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-xl-3">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .w-75
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 75%</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 w-75"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-xl-3">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .w-100
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change width to 100%</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-danger-50 height-1 opacity-50 mt-2 w-100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Height <span class="fw-300"><i>presets</i></span>
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
                            Uniform heights to keep things aligned. See <code>variables.scss</code> file for more
                            details on <code>$p-*</code> values.
                        </div>
                        <div class="row" data-masonry='{"percentPosition": true }'>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-1
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 1.5rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-1 opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-2
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 2rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-2 opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-3
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 2.5rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-3 opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-4
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 2.75rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-4 opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-5
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 3rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-5 opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-6
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 3.25rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-6 opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-7
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 3.5rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-7 opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-8
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 3.75rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-8 opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-9
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 4rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-9 opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-10
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 4.25rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-10 opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-xs
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change <u>min-height</u> to 5rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-xs opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-sm
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change <u>min-height</u> to 10rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-sm opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-lg
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change <u>min-height</u> to 15rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-lg opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-4">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .height-xl
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change <u>min-height</u> to 20rem</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block bg-info-50 height-xl opacity-50 mt-2 width-1"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-5"/>
                        <div class="row">
                            <div class="col-md-6 col-lg-6 col-xl-3">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .h-25
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 25%</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block height-10 mt-2 width-1 border border-primary">
                                            <div class="d-block bg-info-500 h-25 opacity-50 width-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-xl-3">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .h-50
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 50%</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block height-10 mt-2 width-1 border border-primary">
                                            <div class="d-block bg-info-500 h-50 opacity-50 width-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-xl-3">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .h-75
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 75%</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block height-10 mt-2 width-1 border border-primary">
                                            <div class="d-block bg-info-500 h-75 opacity-50 width-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-xl-3">
                                <div class="card shadow-1 shadow-hover-5 mb-g">
                                    <div class="card-body p-3">
                                        <strong>Class</strong>
                                        <p>
                                            <code>
                                                .h-100
                                            </code>
                                        </p>
                                        <strong>
                                            Description
                                        </strong>
                                        <p class="text-muted">
                                            <i>change height to 100%</i>
                                        </p>
                                        <strong>
                                            Example
                                        </strong>
                                        <div class="d-block height-10 mt-2 width-1 border border-primary">
                                            <div class="d-block bg-info-500 h-100 opacity-50 width-1"></div>
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
@endsection

@section('scripts')

@endsection
