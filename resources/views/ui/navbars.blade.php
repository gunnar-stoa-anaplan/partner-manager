@extends('layouts.vertical', ['pageTitle' => 'Navbars'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Navbars', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Documentation and examples for Bootstrap’s powerful, responsive navigation header, the navbar.'])
@endsection

@section('content')
    <div class="info-container">
        <p>Navbars can be styled using Bootstrap classes, such as <code>.navbar-light</code> and
            <code>.navbar-dark</code>, to match different themes. Additionally,
            Bootstrap allows <strong>toggling the navbar</strong> for mobile views, ensuring an optimal user experience.

            By utilizing built-in utilities like <code>.fixed-top</code> or <code>.sticky-top</code>, developers can
            create <strong>fixed</strong> or <strong>sticky</strong> navbars that remain visible while scrolling.</p>
        <p class="mb-0">
            Learn more about this component on bootstrap's <a
                href="https://getbootstrap.com/docs/5.3/components/navbar/" target="_blank">official documentation</a>.
        </p>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Supported <span class="fw-300"><i>content</i></span>
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
                            <p>Navbars come with built-in support for a handful of sub-components. Choose from the
                                following as needed:</p>
                            <ul class="d-grid gap-2">
                                <li><code>.navbar-brand</code> for your company, product, or project name.</li>
                                <li><code>.navbar-nav</code> for a full-height and lightweight navigation (including
                                    support for dropdowns).
                                </li>
                                <li><code>.navbar-toggler</code> for use with our collapse plugin and other <a
                                        href="#responsive-behaviors">navigation toggling</a> behaviors.
                                </li>
                                <li><code>.form-inline</code> for any form controls and actions.</li>
                                <li><code>.navbar-text</code> for adding vertically centered strings of text.</li>
                                <li><code>.collapse.navbar-collapse</code> for grouping and hiding navbar contents by a
                                    parent breakpoint.
                                </li>
                            </ul>
                        </div>
                        <nav class="navbar navbar-expand-lg navbar-light bg-light">
                            <div class="container-fluid">
                                <a class="navbar-brand" href="#">Navbar</a>
                                <button aria-controls="navbarSupportedContent" aria-expanded="false"
                                        aria-label="Toggle navigation" class="navbar-toggler"
                                        data-bs-target="#navbarSupportedContent" data-bs-toggle="collapse"
                                        type="button">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                        <li class="nav-item">
                                            <a aria-current="page" class="nav-link active" href="#">Home</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Link</a>
                                        </li>
                                        <li class="nav-item dropdown">
                                            <a aria-expanded="false" class="nav-link dropdown-toggle"
                                               data-bs-toggle="dropdown" href="#" id="navbarDropdown" role="button">
                                                Dropdown
                                            </a>
                                            <ul aria-labelledby="navbarDropdown" class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Action</a></li>
                                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                                <li>
                                                    <div class="dropdown-divider"></div>
                                                </li>
                                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                                            </ul>
                                        </li>
                                        <li class="nav-item">
                                            <a aria-disabled="true" class="nav-link disabled" href="#" tabindex="-1">Disabled</a>
                                        </li>
                                    </ul>
                                    <form class="d-flex">
                                        <input aria-label="Search" class="form-control me-2" placeholder="Search"
                                               type="search"/>
                                        <button
                                            class="btn btn-outline-success rounded-3 width-4 d-flex align-items-center justify-content-center px-1"
                                            type="submit"><i class="sa sa-magnifier"></i></button>
                                    </form>
                                </div>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Navbar <span class="fw-300"><i>Brand</i></span>
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
                            The <code>.navbar-brand</code> can be applied to most elements, but an anchor works best as
                            some elements might require utility classes or custom styles.
                        </div>
                        <nav class="navbar navbar-light bg-light">
                            <div class="container-fluid">
                                <a class="navbar-brand d-flex align-items-center fw-500" href="#">
                                    <img alt="logo" class="d-inline-block align-text-top me-2" src="/img/logo.png"/>
                                    SmartAdmin
                                </a>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Navbar <span class="fw-300"><i>Links</i></span>
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
                            <p>Navbar navigation links build on our <code>.nav</code> options with their own modifier
                                class and require the use of <a href="#toggler">toggler classes</a> for proper
                                responsive styling. <strong>Navigation in navbars will also grow to occupy as much
                                    horizontal space as possible</strong> to keep your navbar contents securely aligned.
                            </p>
                        </div>
                        <nav class="navbar navbar-expand-lg navbar-light bg-light">
                            <div class="container-fluid">
                                <a class="navbar-brand d-flex align-items-center fw-500" href="#">
                                    <img alt="logo" class="d-inline-block align-text-top me-2" src="/img/logo.png"/>
                                    SmartAdmin
                                </a>
                                <button aria-controls="navbarNavDropdown" aria-expanded="false"
                                        aria-label="Toggle navigation" class="navbar-toggler"
                                        data-bs-target="#navbarNavDropdown" data-bs-toggle="collapse" type="button">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarNavDropdown">
                                    <ul class="navbar-nav">
                                        <li class="nav-item">
                                            <a aria-current="page" class="nav-link active" href="#">Home</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Features</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Pricing</a>
                                        </li>
                                        <li class="nav-item dropdown">
                                            <a aria-expanded="false" class="nav-link dropdown-toggle"
                                               data-bs-toggle="dropdown" href="#" id="navbarDropdownMenuLink"
                                               role="button">
                                                Dropdown link
                                            </a>
                                            <ul aria-labelledby="navbarDropdownMenuLink" class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Action</a></li>
                                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Navbar <span class="fw-300"><i>Links using utilities</i></span>
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
                            Mix and match with other components and utilities as needed.
                        </div>
                        <nav class="navbar navbar-expand-lg navbar-light bg-light">
                            <div class="container-fluid">
                                <a class="navbar-brand d-flex align-items-center fw-500" href="#">
                                    <img alt="logo" class="d-inline-block align-text-top me-2" src="/img/logo.png"/>
                                    SmartAdmin
                                </a>
                                <button aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation"
                                        class="navbar-toggler" data-bs-target="#navbarText" data-bs-toggle="collapse"
                                        type="button">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarText">
                                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                        <li class="nav-item">
                                            <a aria-current="page" class="nav-link active" href="#">Home</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Features</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Pricing</a>
                                        </li>
                                    </ul>
                                    <span class="navbar-text">
                                                            Navbar text with an inline element
                                                        </span>
                                </div>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Color <span class="fw-300"><i>Variations</i></span>
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
                            Change navbar colors with these utilities. Note that the <code>.navbar-brand</code> and
                            <code>.navbar-toggler</code> will adapt to blend in with their container.
                        </div>
                        <!-- Dark navbar -->
                        <nav class="navbar navbar-expand-lg navbar-dark bg-fusion-500 mb-2">
                            <div class="container-fluid">
                                <a class="navbar-brand d-flex align-items-center fw-500" href="#">
                                    <img alt="logo" class="d-inline-block align-text-top me-2" src="/img/logo.png"/>
                                    SmartAdmin
                                </a>
                                <button aria-controls="navbarColor01" aria-expanded="false"
                                        aria-label="Toggle navigation" class="navbar-toggler"
                                        data-bs-target="#navbarColor01" data-bs-toggle="collapse" type="button">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarColor01">
                                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                        <li class="nav-item">
                                            <a aria-current="page" class="nav-link active" href="#">Home</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Features</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Pricing</a>
                                        </li>
                                    </ul>
                                    <button class="btn btn-primary btn-icon" data-bs-container="#navbarColor01"
                                            data-bs-content="&lt;form class='d-flex'&gt;&lt;input class='form-control me-2' type='search' placeholder='Search' aria-label='Search'&gt;&lt;/form&gt;"
                                            data-bs-html="true" data-bs-placement="top" data-bs-toggle="popover"
                                            type="button">
                                        <i class="sa sa-magnifier"></i>
                                    </button>
                                </div>
                            </div>
                        </nav>
                        <!-- Primary gradient navbar -->
                        <nav class="navbar navbar-expand-lg navbar-dark bg-primary bg-primary-gradient mb-2">
                            <div class="container-fluid">
                                <a class="navbar-brand d-flex align-items-center fw-500" href="#">
                                    <img alt="logo" class="d-inline-block align-text-top me-2" src="/img/logo.png"/>
                                    SmartAdmin
                                </a>
                                <button aria-controls="navbarColor02" aria-expanded="false"
                                        aria-label="Toggle navigation" class="navbar-toggler"
                                        data-bs-target="#navbarColor02" data-bs-toggle="collapse" type="button">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarColor02">
                                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                        <li class="nav-item">
                                            <a aria-current="page" class="nav-link active" href="#">Home</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Features</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Pricing</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </nav>
                        <!-- Light navbar -->
                        <nav class="navbar navbar-expand-lg navbar-light">
                            <div class="container-fluid">
                                <a class="navbar-brand d-flex align-items-center fw-500" href="#">
                                    <img alt="logo" class="d-inline-block align-text-top me-2" src="/img/logo.png"/>
                                    SmartAdmin
                                </a>
                                <button aria-controls="navbarColor03" aria-expanded="false"
                                        aria-label="Toggle navigation" class="navbar-toggler"
                                        data-bs-target="#navbarColor03" data-bs-toggle="collapse" type="button">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarColor03">
                                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                        <li class="nav-item">
                                            <a aria-current="page" class="nav-link active" href="#">Home</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Features</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Pricing</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Header <span class="fw-300"><i>position</i></span>
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
                            <p>Use <a href="#">position utilities</a> to place navbars in non-static positions. Choose
                                from fixed to the top, fixed to the bottom, or stickied to the top (scrolls with the
                                page until it reaches the top, then stays there). Fixed navbars use <code>position:
                                    fixed</code>, meaning they're pulled from the normal flow of the DOM and may require
                                custom CSS (e.g., <code>padding-top</code> on the <code>&lt;body&gt;</code>) to prevent
                                overlap with other elements.</p>
                            Also note that <strong><code>.sticky-top</code> uses <code>position: sticky</code>, which <a
                                    href="https://caniuse.com/#feat=css-sticky" target="_blank">isn't fully supported in
                                    every browser</a></strong>
                        </div>
                        <div class="position-relative overflow-auto" style="height: 300px;">
                            <h5 class="fw-500">
                                Sticky Element: Scroll Down to See the Effect
                                <small>
                                    Scroll down this page to see how sticky positioning works
                                </small>
                            </h5>
                            <nav class="navbar navbar-expand-lg navbar-light bg-warning-500 sticky-top">
                                <a class="navbar-brand d-flex align-items-center fw-500" href="#">
                                    <img alt="logo" class="d-inline-block align-top mx-2" src="/img/logo.png"/>
                                    SmartAdmin
                                </a>
                                <button aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation"
                                        class="navbar-toggler" data-target="#navbarSticky" data-toggle="collapse"
                                        type="button">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarSticky">
                                    <ul class="navbar-nav me-auto">
                                        <li class="nav-item active">
                                            <a class="nav-link" href="#">Home
                                                <span class="sr-only">(current)</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Features</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Pricing</a>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                            <br/>
                            <p class="lead">
                                SmartAdmin is a <strong>powerful, feature-rich admin template</strong> designed for
                                developers who want to build professional and scalable dashboards. Unlike traditional
                                admin panels, SmartAdmin provides a <strong>fully responsive</strong>, modern design
                                with a <strong>clean UI</strong>, making it a perfect choice for applications that
                                require a smooth user experience.
                            </p>
                            <p>
                                One of SmartAdmin’s biggest advantages is its <strong>jQuery-free</strong> structure in
                                the latest versions, making it future-proof and optimized for <strong>modern JavaScript
                                    frameworks</strong> like React, Vue, and Angular. This means developers can
                                integrate it easily with their preferred stack without dealing with legacy dependencies.
                                Additionally, it comes packed with <strong>ready-to-use components</strong>, such as
                                form elements, charts, widgets, and pre-built pages, reducing development time.
                            </p>
                            <p>
                                Another key feature of SmartAdmin is its <strong>modular architecture</strong>, allowing
                                developers to customize and extend functionalities effortlessly. Whether you're building
                                a simple dashboard or a complex enterprise solution, SmartAdmin's <strong>scalable
                                    design</strong> ensures that you can add new components without affecting
                                performance. The template also includes a <strong>dark mode</strong>, which improves
                                accessibility and enhances the user experience in different environments.
                            </p>
                            <p>
                                Security is another major concern in web development, and SmartAdmin is built with best
                                practices in mind. With <strong>role-based access control (RBAC)</strong>, developers
                                can define user permissions and restrict access to sensitive data. This is crucial for
                                applications requiring different levels of authentication, such as <strong>admin panels,
                                    financial dashboards, and analytics tools</strong>.
                            </p>
                            <p>
                                SmartAdmin is also well-documented and actively maintained, ensuring developers have
                                access to updates, bug fixes, and new features. The template includes a <strong>detailed
                                    documentation guide</strong>, helping developers quickly integrate and customize it
                                for their needs. Additionally, the SmartAdmin community is growing, offering support
                                through <strong>forums, GitHub discussions, and dedicated customer service</strong>.
                            </p>
                            <p class="mb-4">
                                Overall, SmartAdmin is an <strong>all-in-one solution</strong> for building modern,
                                responsive, and scalable admin dashboards. With its <strong>clean design, lightweight
                                    architecture, and rich feature set</strong>, it’s an excellent choice for startups,
                                enterprises, and developers looking for a robust UI framework. Whether you're working on
                                a SaaS product, internal tool, or data-driven application, SmartAdmin provides
                                everything you need to create a professional and efficient user interface.
                            </p>
                            <a class="btn btn-primary btn-lg"
                               href="https://wrapbootstrap.com/theme/smartadmin-responsive-webapp-WB0573SK0"
                               target="_blank">
                                Learn More About SmartAdmin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        Navbar <span class="fw-300"><i>Icons</i></span>
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
                            Adding profile pictures and dropdowns to nav bars
                        </div>
                        <nav class="navbar navbar-expand-lg navbar-light bg-light">
                            <button aria-controls="navbarTogglerDemo03" aria-expanded="false"
                                    aria-label="Toggle navigation" class="navbar-toggler"
                                    data-target="#navbarTogglerDemo03" data-toggle="collapse" type="button">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <a class="navbar-brand" href="#">Navbar</a>
                            <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
                                <ul class="navbar-nav me-auto mt-3 mt-lg-0">
                                    <li class="nav-item active">
                                        <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#">Link</a>
                                    </li>
                                    <li class="nav-item">
                                        <a aria-disabled="true" class="nav-link disabled" href="#" tabindex="-1">Disabled</a>
                                    </li>
                                </ul>
                                <a class="width-3 height-2 d-inline-flex align-items-center justify-content-center position-relative h1 me-2 mb-0 fs-xxl"
                                   href="javascript:void(0);">
                                    <i class="sa sa-bell"></i>
                                    <span class="badge badge-icon pos-top pos-right">99</span>
                                </a>
                                <a class="d-flex align-items-center justify-content-center ms-2"
                                   data-bs-toggle="dropdown" href="#" title="sunnya@getwebora.com">
                                    <img alt="sunnya@getwebora.com"
                                         class="profile-image profile-image-md rounded-circle"
                                         src="/img/demo/avatars/avatar-admin.png"/>
                                    <span
                                        class="ms-1 me-1 text-truncate text-truncate-header hidden-xs-down">Sunny A.</span>
                                    <i class="sa sa-chevron-down hidden-xs-down"></i>
                                </a>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
