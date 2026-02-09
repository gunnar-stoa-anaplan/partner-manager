@extends('layouts.vertical', ['pageTitle' => 'Cards'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle' => 'Cards', 'pageSubTitle1' => 'Design', 'pageSubTitle2' => 'UI Components', 'pageSubText' => 'Cards provide a flexible and extensible content container with multiple variants and options.'])
@endsection

@section('content')
    <div class="info-container">
        <div class="d-flex flex-start w-100">
            <div class="d-flex flex-fill">
                <div class="flex-fill">
                    <p class="mb-0">Cards are built with as little markup and styles as possible, but still manage to
                        deliver a ton of control and customization. Built with flexbox, they offer easy alignment and
                        mix
                        well with other Bootstrap components. They have no <code>margin</code> by default, so use
                        spacing
                        utilities as needed. Although cards are a lightweight solution for 'widget' or 'panel', we
                        recommend
                        you check out SmartAdmin's <a href="{{ route('second', ['ui', 'panels']) }}" target="_blank" title="Components Panels">panels
                            page</a> for further flexible and an alternative option.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <!-- Kitchen sink example -->
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Kitchen <span class="fw-300"><i>sink example</i></span>
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
                            The most basic example of cards with enforced width
                        </div>
                        <div class="card m-auto m-lg-0" style="max-width: 18rem;">
                            <img alt="..." class="card-img-top" src="/img/cardcovers/cover-1-lg.png"/>
                            <div class="card-body">
                                <h5 class="card-title">Card title</h5>
                                <p class="card-text">Some quick example text to build on the card title and make up the
                                    bulk
                                    of the card's content.</p>
                                <a class="btn btn-primary" href="#">Go somewhere</a>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Cras justo odio</li>
                                <li class="list-group-item">Dapibus ac facilisis in</li>
                                <li class="list-group-item">Vestibulum at eros</li>
                            </ul>
                            <div class="card-body">
                                <a class="card-link" href="#">Card link</a>
                                <a class="card-link" href="#">Another link</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- List group -->
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        List <span class="fw-300"><i>group</i></span>
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
                            Create lists of content in a card with a flush list group.
                        </div>
                        <div class="card m-auto m-lg-0" style="width: 18rem;">
                            <div class="card-header">
                                Featured
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Cras justo odio</li>
                                <li class="list-group-item">Dapibus ac facilisis in</li>
                                <li class="list-group-item">Vestibulum at eros</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Quote -->
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Quote <span class="fw-300"><i>example</i></span>
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
                            Here we adjust <code>card-header</code> padding using provided <a href="#"
                                                                                              title="utility classes">utility
                                classes</a>
                        </div>
                        <div class="card m-auto border">
                            <div class="card-header py-2">
                                <div class="card-title">
                                    Quote
                                </div>
                            </div>
                            <div class="card-body">
                                <blockquote class="blockquote mb-0">
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a
                                        ante.
                                    </p>
                                    <footer class="blockquote-footer">Someone famous in <cite title="Source Title">Source
                                            Title</cite></footer>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Header and footer -->
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Header <span class="fw-300"><i>and footer</i></span>
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
                            Add an optional header and/or footer within a card.
                        </div>
                        <div class="card m-auto border">
                            <div class="card-header">
                                Featured
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">Special title treatment</h5>
                                <p class="card-text">With supporting text below as a natural lead-in to additional
                                    content.
                                </p>
                                <a class="btn btn-primary" href="#">Go somewhere</a>
                            </div>
                            <div class="card-footer text-muted py-2">
                                2 days ago
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Background & borders -->
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Background <span class="fw-300"><i>&amp; Borders</i></span>
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
                            <code>.card-header</code>, <code>.card-body</code>, and <code>.card-footer</code> background
                            cam
                            be changed using <a href="{{ route('second', ['utilities', 'colorpalette']) }}" target="_blank"
                                                title="BG Color Utility">color</a> and <a href="{{ route('second', ['utilities', 'borders']) }}"
                                                                                          target="_blank"
                                                                                          title="border utilities">border</a>
                            utilities
                        </div>
                        <!-- demo controls -->
                        <div class="mb-4">
                            <div class="row">
                                <div class="col-sm-6 col-xl-4">
                                    <div class="mb-3">
                                        <label class="form-label">Select target</label>
                                        <select class="js-bg-target form-select">
                                            <option value="#cp-2 .card-header">card-header</option>
                                            <option value="#cp-2 .card-body">card-body</option>
                                            <option value="#cp-2 .card-footer">card-footer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl-4">
                                    <div class="mb-3">
                                        <label class="form-label">Select background</label>
                                        <select class="js-bg-color form-select"></select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-xl-4">
                                    <div class="mb-3">
                                        <label class="form-label">Card border</label>
                                        <select class="js-border-color form-select">
                                            <option value="border-primary">border-primary</option>
                                            <option value="border-secondary">border-secondary</option>
                                            <option value="border-info">border-info</option>
                                            <option value="border-danger">border-danger</option>
                                            <option value="border-warning">border-warning</option>
                                            <option value="border-success">border-success</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card border" id="cp-2">
                            <div class="card-header bg-info-600">
                                Different colors
                            </div>
                            <div class="card-body p-0 bg-info-300">
                                <div class="" style="height: 160px; overflow-y: auto; overflow-x: hidden;">
                                    <div class="px-4 py-3">
                                        <div class="fs-xl fw-500 mb-3">
                                            He system office. Of to wonder, windshield to seven. Whenever
                                            <div class="fs-nano fw-300">SATURDAY, 27TH JANUARY 2018 15 minutes ago.
                                            </div>
                                        </div>
                                        <div class="fs-lg mb-3">Of totally to issues for to the decelerate city, the at
                                            how
                                            discharge than like…. It history; Their letters, away, the and stupid
                                            employees,
                                            divine them his a only live lobby, little regretting conduct, know out
                                            testimony
                                            the latest even and systematic be service, name of every accept bits turned.
                                        </div>
                                        In the a soon in facilitate reflections, he more had from preceding think the
                                        lay
                                        low the where initial feedback dropped at be its to in little instruments, of
                                        himself found phase they poverty disappointment is her and to thought, that the
                                        by
                                        is really chime in the of we sovereignty. On which, office. The that he each
                                        been
                                        ago she from these is name much phase. Counter-productive towards far and
                                        transmitting offers in of the with between was and over since failing. Slowly
                                        cheating far with gone in, irregular refute. No success six way differences
                                        authentic destined pass pouring.
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-info-800 py-2">
                                2 days ago
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <!-- Collapse -->
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Collapse <span class="fw-300"><i>example</i></span>
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
                            Utilizing bootstrap's collapse component with <code>.card</code>
                        </div>
                        <div class="card border" id="cp-1">
                            <div class="card-header">
                                <button aria-expanded="true" class="btn btn-sm btn-danger"
                                        data-bs-target="#cp-1 &gt; .card-body" data-bs-toggle="collapse" type="button">
                                    <span class="collapsed-hidden">Expand</span>
                                    <span class="collapsed-reveal">Collapse</span>
                                </button>
                            </div>
                            <div class="card-body p-0 show">
                                <!-- notice we place the padding inside another div and remove the padding from the card body -->
                                <div class="p-4">
                                    <p class="card-text">With supporting text below as a natural lead-in to additional
                                        content.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Navigation tabs -->
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        Navigation <span class="fw-300"><i>tabs</i></span>
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
                            Add some navigation to a card's header (or block) with built-in nav components.
                        </div>
                        <div class="card border">
                            <div class="card-header">
                                <ul class="nav nav-tabs card-header-tabs">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="#">Active</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#">Link</a>
                                    </li>
                                    <li class="nav-item">
                                        <a aria-disabled="true" class="nav-link disabled" href="#"
                                           tabindex="-1">Disabled</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <p class="card-text">With supporting text below as a natural lead-in to additional
                                    content.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Navigation pills -->
            <div class="panel panel-icon" id="panel-8">
                <div class="panel-hdr">
                    <h2>
                        Navigation <span class="fw-300"><i>pills</i></span>
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
                            Add some navigation to a card's header (or block) with built-in nav components.
                        </div>
                        <div class="card border">
                            <div class="card-header">
                                <ul class="nav nav-pills card-header-pills">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="#">Active</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#">Link</a>
                                    </li>
                                    <li class="nav-item">
                                        <a aria-disabled="true" class="nav-link disabled" href="#"
                                           tabindex="-1">Disabled</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <p class="card-text">With supporting text below as a natural lead-in to additional
                                    content.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Header options -->
            <div class="panel panel-icon" id="panel-9">
                <div class="panel-hdr">
                    <h2>
                        Header <span class="fw-300"><i>Options</i></span>
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
                            Display of some example optional "stuff" you can add to <code>.card-header</code>
                        </div>
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div
                                        class="card-header bg-fusion-500 d-flex align-items-center justify-content-between">
                                        <div class="card-title">Dropdowns</div>
                                        <button class="btn btn-icon btn-danger btn-sm" data-bs-toggle="dropdown"
                                                type="button">
                                            <i class="fal fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">Option 1</a>
                                            <a class="dropdown-item" href="#">Option 2</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Refresh</a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">With supporting text below as a natural lead-in to
                                            additional
                                            content.</p>
                                    </div>
                                </div>
                                <div class="card mb-4">
                                    <div
                                        class="card-header d-flex align-items-center justify-content-between bg-trans-gradient py-2 pe-2">
                                        <div class="card-title text-white">Input</div>
                                        <div class="d-flex position-relative" style="max-width: 10rem;">
                                            <i class="fal fa-search position-absolute"
                                               style="left: 10px; top: 50%; transform: translateY(-50%);"></i>
                                            <input class="form-control bg-white" placeholder="Search"
                                                   style="padding-left: 2rem;" type="text"/>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">With supporting text below as a natural lead-in to
                                            additional
                                            content.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div
                                        class="card-header d-flex align-items-center justify-content-between bg-warning py-2 pe-2">
                                        <div class="card-title text-white">Buttons</div>
                                        <div>
                                            <button class="btn btn-info btn-sm" type="button">Add new</button>
                                            <button class="btn btn-success btn-sm" type="button">
                                                <i class="fal fa-arrow-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">With supporting text below as a natural lead-in to
                                            additional
                                            content.</p>
                                    </div>
                                </div>
                                <div class="card">
                                    <div
                                        class="card-header d-flex align-items-center justify-content-between bg-success">
                                        <div class="card-title text-white">Checkbox</div>
                                        <div class="form-check form-switch form-check-reverse">
                                            <input checked="" class="form-check-input" id="demo-switch"
                                                   type="checkbox"/>
                                            <label class="form-check-label" for="demo-switch"></label>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">With supporting text below as a natural lead-in to
                                            additional
                                            content.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card group -->
            <div class="panel panel-icon" id="panel-10">
                <div class="panel-hdr">
                    <h2>
                        Card <span class="fw-300"><i>Group</i></span>
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
                            Use card groups to render cards as a single, attached element with equal width and height
                            columns. Card groups use <code>display: flex;</code> to achieve their uniform sizing.
                        </div>
                        <div class="card-group">
                            <div class="card">
                                <div class="w-100 bg-fusion-50 rounded-top border-top-right-radius-0"
                                     style="padding:40px 0 40px;"></div>
                                <div class="card-body">
                                    <h5 class="card-title">Card title</h5>
                                    <p class="card-text">This card has supporting text below as a natural lead-in to
                                        additional content.</p>
                                    <small class="text-muted">Last updated 3 mins ago</small>
                                </div>
                            </div>
                            <div class="card">
                                <div class="w-100 bg-fusion-50" style="padding:40px 0 40px;"></div>
                                <div class="card-body">
                                    <h5 class="card-title">Card title</h5>
                                    <p class="card-text">This card has supporting text below as a natural lead-in to
                                        additional content.</p>
                                    <small class="text-muted">Last updated 3 mins ago</small>
                                </div>
                            </div>
                            <div class="card">
                                <div class="w-100 bg-fusion-50 rounded-top border-top-left-radius-0"
                                     style="padding:40px 0 40px;"></div>
                                <div class="card-body">
                                    <h5 class="card-title">Card title</h5>
                                    <p class="card-text">This card has supporting text below as a natural lead-in to
                                        additional content.</p>
                                    <small class="text-muted">Last updated 3 mins ago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card decks -->
            <div class="panel panel-icon" id="panel-11">
                <div class="panel-hdr">
                    <h2>
                        Card <span class="fw-300"><i>Decks</i></span>
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
                            Need a set of equal width and height cards that aren't attached to one another? Use card
                            decks.
                        </div>
                        <div class="row row-cols-1 row-cols-md-3 g-4">
                            <div class="col">
                                <div class="card h-100">
                                    <div class="w-100 bg-fusion-50 rounded-top" style="padding:40px 0 40px;"></div>
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">This is a wider card with supporting text below as a
                                            natural
                                            lead-in to additional content.</p>
                                    </div>
                                    <div class="card-footer">
                                        <small class="text-muted">Last updated 3 mins ago</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100">
                                    <div class="w-100 bg-fusion-50 rounded-top" style="padding:40px 0 40px;"></div>
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">This card has supporting text below as a natural lead-in to
                                            additional content.</p>
                                    </div>
                                    <div class="card-footer">
                                        <small class="text-muted">Last updated 3 mins ago</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100">
                                    <div class="w-100 bg-fusion-50 rounded-top" style="padding:40px 0 40px;"></div>
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">This is a wider card with supporting text below as a
                                            natural
                                            lead-in to additional content.</p>
                                    </div>
                                    <div class="card-footer">
                                        <small class="text-muted">Last updated 3 mins ago</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="panel panel-icon" id="panel-12">
                <div class="panel-hdr">
                    <h2>Card <span class="fw-300"><i>Columns</i></span></h2>
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
                            Cards can be organized into <a href="https://masonry.desandro.com/"
                                                           target="_blank">Masonry</a>-like columns with just CSS by
                            wrapping them in
                            <code>.card-columns</code>. Cards are built with CSS <code>column</code> properties instead
                            of
                            flexbox for easier alignment. Cards are ordered from top to bottom and left to right.
                        </div>
                        <div class="alert alert-warning">
                            <strong>Heads up!</strong> Your mileage with card columns may vary. To prevent cards
                            breaking
                            across columns, we must set them to <code>display: inline-block</code> as
                            <code>column-break-inside: avoid</code> isn't a bulletproof solution yet.
                        </div>
                        <div class="row" data-masonry='{"percentPosition": true }'>
                            <div class="col-sm-6 col-lg-4 mb-4">
                                <div class="card">
                                    <div class="w-100 bg-fusion-300" style="padding:40px 0 40px;"></div>
                                    <div class="card-body">
                                        <h5 class="card-title">Card title that wraps to a new line</h5>
                                        <p class="card-text">This is a longer card with supporting text below as a
                                            natural
                                            lead-in to additional content. This content is a little bit longer.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-4">
                                <div class="card p-3">
                                    <blockquote class="blockquote mb-0">
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat
                                            a
                                            ante.</p>
                                        <footer class="blockquote-footer">
                                            <small class="text-muted">Someone famous in <cite title="Source Title">Source
                                                    Title</cite></small>
                                        </footer>
                                    </blockquote>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-4">
                                <div class="card">
                                    <div class="w-100 bg-fusion-300" style="padding:40px 0 40px;"></div>
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">This card has supporting text below as a natural lead-in to
                                            additional content.</p>
                                        <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-4">
                                <div class="card bg-primary text-white text-center p-3">
                                    <blockquote class="blockquote mb-0">
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere
                                            erat.
                                        </p>
                                        <footer class="blockquote-footer text-white">
                                            <small>Someone famous in <cite title="Source Title">Source
                                                    Title</cite></small>
                                        </footer>
                                    </blockquote>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">This card has a regular title and short paragraphy of text
                                            below it.</p>
                                        <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-4">
                                <div class="card">
                                    <div class="w-100 bg-fusion-300" style="padding:40px 0 40px;"></div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-4">
                                <div class="card p-3 text-end">
                                    <blockquote class="blockquote mb-0">
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat
                                            a
                                            ante.</p>
                                        <footer class="blockquote-footer">
                                            <small class="text-muted">Someone famous in <cite title="Source Title">Source
                                                    Title</cite></small>
                                        </footer>
                                    </blockquote>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">This is another card with title and supporting text below.
                                            This
                                            card has some additional content to make it slightly taller overall.</p>
                                        <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small>
                                        </p>
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

    <script>
        /* Inject additional scripts dynamically */
        // Menu Slider function
        function MenuSlider(options) {
            this.element = options.element;
            this.wrapperId = options.wrapperId;
            this.init();
        }

        // Background colors handling
        var ng_bgColors = ["bg-danger-50", "bg-danger-100", "bg-danger-200", "bg-danger-300", "bg-danger-400",
            "bg-danger-500", "bg-danger-600", "bg-danger-700", "bg-danger-800", "bg-danger-900", "bg-fusion-50",
            "bg-fusion-100", "bg-fusion-200", "bg-fusion-300", "bg-fusion-400", "bg-fusion-500", "bg-fusion-600",
            "bg-fusion-700", "bg-fusion-800", "bg-fusion-900", "bg-gray-50", "bg-gray-100", "bg-gray-200",
            "bg-gray-300", "bg-gray-400", "bg-gray-500", "bg-gray-600", "bg-gray-700", "bg-gray-800", "bg-gray-900",
            "bg-info-50", "bg-info-100", "bg-info-200", "bg-info-300", "bg-info-400", "bg-info-500", "bg-info-600",
            "bg-info-700", "bg-info-800", "bg-info-900", "bg-primary-50", "bg-primary-100", "bg-primary-200",
            "bg-primary-300", "bg-primary-400", "bg-primary-500", "bg-primary-600", "bg-primary-700", "bg-primary-800",
            "bg-primary-900", "bg-success-50", "bg-success-100", "bg-success-200", "bg-success-300", "bg-success-400",
            "bg-success-500", "bg-success-600", "bg-success-700", "bg-success-800", "bg-success-900", "bg-warning-50",
            "bg-warning-100", "bg-warning-200", "bg-warning-300", "bg-warning-400", "bg-warning-500", "bg-warning-600",
            "bg-warning-700", "bg-warning-800", "bg-warning-900"
        ];
        var formatBgColors = [];
        // Initialize background colors immediately
        (function handleBgColors() {
            if (ng_bgColors) {
                // Create default option
                var defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'select background';
                formatBgColors.push(defaultOption);
                // Create options for each color
                ng_bgColors.forEach(function (item) {
                    var option = document.createElement('option');
                    option.value = item;
                    option.className = item;
                    option.textContent = item;
                    formatBgColors.push(option);
                });
                // Update select elements
                var bgColorSelects = document.querySelectorAll('select.js-bg-color');
                bgColorSelects.forEach(function (select) {
                    select.innerHTML = '';
                    formatBgColors.forEach(function (option) {
                        select.appendChild(option.cloneNode(true));
                    });
                });
            } else {
                console.log("something went wrong!");
            }
        })();

        // Remove class prefix helper function
        function removeClassByPrefix(element, prefix) {
            var classes = element.className.split(" ").filter(function (c) {
                return c.lastIndexOf(prefix, 0) !== 0;
            });
            element.className = classes.join(" ").trim();
        }

        // Change background handler
        document.addEventListener('change', function (e) {
            if (e.target.matches('.js-bg-color')) {
                var bgColorSelect = document.querySelector('select.js-bg-color');
                var targetSelect = document.querySelector('select.js-bg-target');
                var setBgColor = bgColorSelect.value;
                var setValue = targetSelect.value;
                removeClassByPrefix(bgColorSelect, 'bg-');
                bgColorSelect.classList.add(setBgColor);
                var targetElement = document.querySelector(setValue);
                if (targetElement) {
                    removeClassByPrefix(targetElement, 'bg-');
                    targetElement.classList.add(setBgColor);
                }
            }
        });
    </script>
@endsection
