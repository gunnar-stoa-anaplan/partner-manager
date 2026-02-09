@extends('layouts.vertical', ['pageTitle' => 'Breadcrumbs'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Breadcrumbs', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Breadcrumbs are a way to navigate through a website.'])
@endsection

@section('content')
    <div class="info-container">
        <p>
            Breadcrumbs are a way to navigate through a website. They are a series of links that are used to navigate
            through a website. They are a series of links that are used to navigate through a website. They are a series
            of
            links that are used to navigate through a website.
        </p>
        <p class="mb-0">
            Learn more about this component on bootstrap's <a
                href="https://getbootstrap.com/docs/5.3/components/breadcrumb/" target="_blank">official
                documentation</a>.
        </p>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <!--Default-->
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Default <span class="fw-300"><i>Breadcrumb</i></span>
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
                        <div class="demo-v-spacing">
                            <ol class="breadcrumb bg-faded px-3 py-2 rounded">
                                <li class="breadcrumb-item active">Home</li>
                            </ol>
                            <ol class="breadcrumb bg-faded px-3 py-2 rounded">
                                <li class="breadcrumb-item">
                                    <a href="#">Home</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active">Data</li>
                            </ol>
                            <ol class="breadcrumb bg-faded px-3 py-2 rounded">
                                <li class="breadcrumb-item">
                                    <a href="#">This is a big text</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Hovering reveals the text</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Can be big as you like</a>
                                </li>
                                <li class="breadcrumb-item active">Cool right?</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!--Different sizes-->
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Different <span class="fw-300"><i>Sizes</i></span>
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
                        <div class="demo-v-spacing">
                            <ol class="breadcrumb bg-faded px-3 py-2 rounded fs-xs">
                                <li class="breadcrumb-item">
                                    <a href="#">Home</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active">Data</li>
                            </ol>
                            <ol class="breadcrumb bg-faded px-3 py-2 rounded fs-sm">
                                <li class="breadcrumb-item">
                                    <a href="#">Home</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active">Data</li>
                            </ol>
                            <ol class="breadcrumb bg-faded px-3 py-2 rounded fs-lg">
                                <li class="breadcrumb-item">
                                    <a href="#">Home</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active">Data</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!--Different Separators-->
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Different <span class="fw-300"><i>Separators</i></span>
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
                        <div class="demo-v-spacing">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item text-muted" data-breadcrumb-seperator="•">
                                    <a href="#">Home</a>
                                </li>
                                <li class="breadcrumb-item text-muted" data-breadcrumb-seperator="•">
                                    <a href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item text-muted" data-breadcrumb-seperator="•">
                                    <a href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active text-muted" data-breadcrumb-seperator="•">
                                    Seperators using data attributes
                                </li>
                            </ol>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item text-dark" data-breadcrumb-seperator="&gt;">
                                    <a class="text-primary" href="#">Home</a>
                                </li>
                                <li class="breadcrumb-item text-dark" data-breadcrumb-seperator="&gt;">
                                    <a class="text-primary" href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item text-dark" data-breadcrumb-seperator="&gt;">
                                    <a class="text-primary" href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active text-dark" data-breadcrumb-seperator="&gt;">Data</li>
                            </ol>
                            <ol class="breadcrumb breadcrumb-seperator-3 mb-0">
                                <li class="breadcrumb-item" data-breadcrumb-seperator="⇨">
                                    <a class="text-info" href="#">Home</a>
                                </li>
                                <li class="breadcrumb-item" data-breadcrumb-seperator="⇨">
                                    <a class="text-info" href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item" data-breadcrumb-seperator="⇨">
                                    <a class="text-info" href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active" data-breadcrumb-seperator="⇨">
                                    Data
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!--With dropdown-->
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        With <span class="fw-300"><i>Dropdown</i></span>
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
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item">
                                <a aria-expanded="false" aria-haspopup="true" data-bs-toggle="dropdown" href="#">Home
                                    <i class="sa sa-chevron-down"></i>
                                </a>
                                <div class="dropdown-menu"
                                     style="position: absolute; will-change: top, left; top: 114px; left: 40px;"
                                     x-placement="bottom-start">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </li>
                            <li class="breadcrumb-item">
                                <a aria-expanded="false" aria-haspopup="true" data-bs-toggle="dropdown" href="#">Library
                                    <i class="sa sa-chevron-down"></i>
                                </a>
                                <div class="dropdown-menu"
                                     style="position: absolute; will-change: top, left; top: 114px; left: 110px;"
                                     x-placement="bottom-start">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">Web</a>
                            </li>
                            <li class="breadcrumb-item active">Data</li>
                        </ol>
                    </div>
                </div>
            </div>
            <!--With Icons-->
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        With <span class="fw-300"><i>Icons</i></span>
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
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item">
                                <a href="#">
                                    <i class="sa sa-grid me-1 fs-md"></i>
                                    Application
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">
                                    <i class="sa sa-book-open me-1 fs-md"></i>
                                    Library
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">
                                    <i class="sa sa-globe me-1 fs-md"></i>
                                    Web
                                </a>
                            </li>
                            <li class="breadcrumb-item active">
                                <i class="sa sa-lock me-1 fs-md"></i>
                                Passwords
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <!--Backgrounds-->
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Backgrounds <span class="fw-300"><i>Outlines</i></span>
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
                        <div class="demo-v-spacing">
                            <ol class="breadcrumb bg-fusion-300 px-3 py-2 rounded">
                                <li class="breadcrumb-item text-white">
                                    <a class="text-white" href="#">
                                        <i class="sa sa-home me-1"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item text-white">
                                    <a class="text-white" href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item text-white">
                                    <a class="text-white" href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active text-white">Data</li>
                            </ol>
                            <ol class="breadcrumb bg-primary-300 px-3 py-2 rounded">
                                <li class="breadcrumb-item text-white">
                                    <a class="text-white" href="#">
                                        <i class="sa sa-home me-1"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item text-white">
                                    <a class="text-white" href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item text-white">
                                    <a class="text-white" href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active text-white">Data</li>
                            </ol>
                            <ol class="breadcrumb bg-info-400 px-3 py-2 rounded">
                                <li class="breadcrumb-item text-white">
                                    <a class="text-white" href="#">
                                        <i class="sa sa-home me-1"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item text-white">
                                    <a class="text-white" href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item text-white">
                                    <a class="text-white" href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active text-white">Data</li>
                            </ol>
                            <ol class="breadcrumb bg-danger-300 px-3 py-2 rounded">
                                <li class="breadcrumb-item text-white">
                                    <a class="text-white" href="#">
                                        <i class="sa sa-home me-1"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item text-white">
                                    <a class="text-white" href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item text-white">
                                    <a class="text-white" href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active text-white">Data</li>
                            </ol>
                            <ol class="breadcrumb bg-transparent border border-primary px-3 py-2 rounded">
                                <li class="breadcrumb-item text-primary">
                                    <a href="#">
                                        <i class="sa sa-home me-1"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item text-primary">
                                    <a href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item text-primary">
                                    <a href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active text-primary">Data</li>
                            </ol>
                            <ol class="breadcrumb bg-transparent border border-success px-3 py-2 rounded">
                                <li class="breadcrumb-item text-success">
                                    <a class="text-success" href="#">
                                        <i class="sa sa-home me-1"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item text-success">
                                    <a class="text-success" href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item text-success">
                                    <a class="text-success" href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active text-success">Data</li>
                            </ol>
                            <ol class="breadcrumb bg-transparent border border-info px-3 py-2 rounded">
                                <li class="breadcrumb-item text-info">
                                    <a class="text-info" href="#">
                                        <i class="sa sa-home me-1"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item text-info">
                                    <a class="text-info" href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item text-info">
                                    <a class="text-info" href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active text-info">Data</li>
                            </ol>
                            <ol class="breadcrumb bg-transparent border border-danger px-3 py-2 rounded mb-0">
                                <li class="breadcrumb-item text-danger">
                                    <a class="text-danger" href="#">
                                        <i class="sa sa-home me-1"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item text-danger">
                                    <a class="text-danger" href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item text-danger">
                                    <a class="text-danger" href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active text-danger">Data</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!--Clean-->
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        Clean <span class="fw-300"><i>Style</i></span>
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
                        <div class="demo-v-spacing">
                            <ol class="breadcrumb bg-transparent breadcrumb-sm pl-0 pr-0 fs-xs">
                                <li class="breadcrumb-item">
                                    <a href="#">
                                        <i class="sa sa-home me-1"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active">Data</li>
                            </ol>
                            <ol class="breadcrumb bg-transparent pl-0 pr-0">
                                <li class="breadcrumb-item">
                                    <a href="#">
                                        <i class="sa sa-home me-1"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active">Data</li>
                            </ol>
                            <ol class="breadcrumb bg-transparent breadcrumb-lg pl-0 pr-0 m-0 fs-xl">
                                <li class="breadcrumb-item">
                                    <a href="#">
                                        <i class="sa sa-home me-1"></i> Home
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Library</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="#">Web</a>
                                </li>
                                <li class="breadcrumb-item active">Data</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!--Other styles-->
            <div class="panel panel-icon" id="panel-8">
                <div class="panel-hdr">
                    <h2>
                        Other <span class="fw-300"><i>Styles</i></span>
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
                        <div class="demo-v-spacing">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item" data-breadcrumb-seperator="→">
                                    <a href="#">
                                        <span class="badge bg-primary">Application</span>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" data-breadcrumb-seperator="→">
                                    <a href="#">
                                        <span class="badge bg-primary">Projects</span>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" data-breadcrumb-seperator="→">
                                    <a href="#">
                                        <span class="badge bg-primary">Library</span>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" data-breadcrumb-seperator="→">
                                    <a href="#">
                                        <span class="badge bg-primary">DATA</span>
                                    </a>
                                </li>
                            </ol>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item" data-breadcrumb-seperator="›">
                                    <a class="btn btn-icon btn-sm btn-outline-default rounded-circle overflow-hidden"
                                       href="javascript:void(0);">
                                        <svg class="sa-icon sa-icon-dark sa-bold">
                                            <use href="/icons/sprite.svg#eye"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" data-breadcrumb-seperator="›">
                                    <a class="btn btn-icon btn-sm btn-outline-default rounded-circle overflow-hidden"
                                       href="javascript:void(0);">
                                        <svg class="sa-icon sa-icon-dark sa-bold">
                                            <use href="/icons/sprite.svg#heart"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="breadcrumb-item" data-breadcrumb-seperator="›">
                                    <a class="btn btn-icon btn-sm btn-outline-default rounded-circle overflow-hidden"
                                       href="javascript:void(0);">
                                        <svg class="sa-icon sa-icon-dark sa-bold">
                                            <use href="/icons/sprite.svg#layers"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" data-breadcrumb-seperator="›">
                                    <a class="btn btn-icon btn-sm btn-outline-default rounded-circle overflow-hidden"
                                       href="javascript:void(0);">
                                        <svg class="sa-icon sa-icon-dark sa-bold">
                                            <use href="/icons/sprite.svg#edit"></use>
                                        </svg>
                                    </a>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
