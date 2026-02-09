@extends('layouts.vertical', ['pageTitle' => 'Accordions'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Accordions', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Boost user engagement and streamline content with a sleek, collapsible component that organizes information seamlessly with smooth animations and minimal code.'])
@endsection

@section('content')
    <div class="info-container">
        <p>
            Use the <code>.accordion</code> component to provide a way to toggle the visibility of content.
            Be sure to add <code>aria-expanded</code> to the control element. This attribute explicitly conveys the
            current state of the collapsible element tied to the control to screen readers and similar assistive
            technologies.
        </p>
        <p>
            If the collapsible element is closed by default, the attribute on the control element should have a value
            of <code>aria-expanded="false"</code>. If you've set the collapsible element to be open by default using
            the <code>show</code> class, set <code>aria-expanded="true"</code> on the control instead.
        </p>
        <p class="mb-0">
            Learn more about this plugin on Bootstrap's <a
                    href="https://getbootstrap.com/docs/5.3/components/collapse/#accordion-example" target="_blank">official
                documentation</a>.
        </p>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Default <span class="fw-light"><i>Accordion</i></span>
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
                            Just add <code>data-bs-toggle="collapse"</code> and a <code>data-bs-target</code> to the
                            element
                            to automatically assign control of one or more collapsible elements. The
                            <code>data-bs-target</code> attribute accepts a CSS selector to apply the collapse to. Be
                            sure
                            to add the class <code>collapse</code> to the collapsible element. If you'd like it to
                            default
                            open, add the additional class <code>show</code>.
                            <br/>
                            <br/>
                            To add accordion-like group management to a collapsible area, add the data attribute
                            <code>data-parent="#selector"</code>. Refer to the demo to see this in action.
                        </div>
                        <div class="accordion" id="bs5_demo_accordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="true" class="accordion-button" data-bs-target="#collapseOne"
                                            data-bs-toggle="collapse" type="button">
                                        Collapsible Group Item #1
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse show" data-bs-parent="#bs5_demo_accordion"
                                     id="collapseOne">
                                    <div class="accordion-body">
                                        <p>Accordions work well inside <strong>forms and modals</strong>, helping break
                                            down
                                            complex inputs into manageable steps.</p>
                                        <p class="mb-0">Instead of overwhelming users with long forms, collapsible
                                            sections
                                            allow for a more <strong>progressive disclosure approach.</strong></p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="false" class="accordion-button collapsed"
                                            data-bs-target="#collapseTwo" data-bs-toggle="collapse" type="button">
                                        Collapsible Group Item #2
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse" data-bs-parent="#bs5_demo_accordion"
                                     id="collapseTwo">
                                    <div class="accordion-body">
                                        For example, sections like <strong>Billing Details</strong>, <strong>Shipping
                                            Address</strong>, and <strong>Payment Method</strong> can be toggled
                                        individually.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="false" class="accordion-button collapsed"
                                            data-bs-target="#collapseThree" data-bs-toggle="collapse" type="button">
                                        Collapsible Group Item #3
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse" data-bs-parent="#bs5_demo_accordion"
                                     id="collapseThree">
                                    <div class="accordion-body">
                                        This improves usability, reduces scrolling, and keeps the <strong>UI clean and
                                            focused.</strong>
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
                        Accordion <span class="fw-light"><i>Outline</i></span>
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
                            Change the style of your accordion by adding class <code>.accordion-outline</code> to
                            <code>.accordion</code>
                        </div>
                        <div class="accordion accordion-outline" id="js_demo_accordion-3">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="true" class="accordion-button custom-collapse"
                                            data-bs-target="#js_demo_accordion-3a" data-bs-toggle="collapse"
                                            type="button">
                                        <i class="sa sa-book-open width-2 fs-xl"></i>

                                        Medical Records
                                        <span class="ms-auto">
                                        <span class="collapsed-reveal">
                                            <svg class="sa-icon">
                                                <use href="/icons/sprite.svg#minus-square"></use>
                                            </svg>
                                        </span>
                                        <span class="collapsed-hidden">
                                            <svg class="sa-icon">
                                                <use href="/icons/sprite.svg#plus-square"></use>
                                            </svg>
                                        </span>
                                    </span>
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse show" data-bs-parent="#js_demo_accordion-3"
                                     id="js_demo_accordion-3a">
                                    <div class="accordion-body">
                                        <p>
                                            The Bootstrap Accordion component allows users to toggle between sections of
                                            content in a collapsible manner.
                                        </p>
                                        <p class="mb-0">
                                            Each accordion item contains a header and a body. The header triggers the
                                            collapse behavior, while the body contains the actual content.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="false" class="accordion-button custom-collapse collapsed"
                                            data-bs-target="#js_demo_accordion-3b" data-bs-toggle="collapse"
                                            type="button">
                                        <i class="sa sa-camcorder width-2 fs-xl"></i>
                                        Video logs
                                        <span class="ms-auto">
                                        <span class="collapsed-reveal">
                                            <svg class="sa-icon">
                                                <use href="/icons/sprite.svg#minus-square"></use>
                                            </svg>
                                        </span>
                                        <span class="collapsed-hidden">
                                            <svg class="sa-icon">
                                                <use href="/icons/sprite.svg#plus-square"></use>
                                            </svg>
                                        </span>
                                    </span>
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse" data-bs-parent="#js_demo_accordion-3"
                                     id="js_demo_accordion-3b">
                                    <div class="accordion-body">
                                        Developers can use Bootstrap’s JavaScript API to control accordion behavior
                                        dynamically. Accessibility is also considered, with proper ARIA attributes
                                        ensuring
                                        screen reader compatibility.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="false" class="accordion-button custom-collapse collapsed"
                                            data-bs-target="#js_demo_accordion-3c" data-bs-toggle="collapse"
                                            type="button">
                                        <i class="sa sa-briefcase width-2 fs-xl"></i>
                                        Medical history
                                        <span class="ms-auto">
                                        <span class="collapsed-reveal">
                                            <svg class="sa-icon">
                                                <use href="/icons/sprite.svg#minus-square"></use>
                                            </svg>
                                        </span>
                                        <span class="collapsed-hidden">
                                            <svg class="sa-icon">
                                                <use href="/icons/sprite.svg#plus-square"></use>
                                            </svg>
                                        </span>
                                    </span>
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse" data-bs-parent="#js_demo_accordion-3"
                                     id="js_demo_accordion-3c">
                                    <div class="accordion-body">
                                        Each accordion consists of a parent container with multiple accordion items.
                                        Each
                                        item has a header that users can click to expand or collapse the content inside.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Accordion <span class="fw-light"><i>Clean layout</i></span>
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
                            Give your accordion a clean look by adding class <code>.accordion-clean</code> to
                            <code>.accordion</code>
                        </div>
                        <div class="accordion accordion-clean" id="js_demo_accordion-1">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="true" class="accordion-button custom-collapse"
                                            data-bs-target="#js_demo_accordion-1a" data-bs-toggle="collapse"
                                            type="button">
                                    <span class="me-2">
                                        <span class="collapsed-reveal">
                                            <i class="sa sa-arrow-up text-danger fs-xxl me-2"></i>
                                        </span>
                                        <span class="collapsed-hidden">
                                            <i class="sa sa-arrow-down text-success fs-xxl me-2"></i>
                                        </span>
                                    </span>
                                        Application settings
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse show" data-bs-parent="#js_demo_accordion-1"
                                     id="js_demo_accordion-1a">
                                    <div class="accordion-body">
                                        By default, Bootstrap uses the <code>collapse.js</code> plugin to manage this
                                        behavior, ensuring smooth transitions and accessibility. The accordion structure
                                        supports <strong>custom styles</strong>, such as different background colors,
                                        border-radius adjustments, and shadow effects.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="false" class="accordion-button custom-collapse collapsed"
                                            data-bs-target="#js_demo_accordion-1b" data-bs-toggle="collapse"
                                            type="button">
                                    <span class="me-2">
                                        <span class="collapsed-reveal">
                                            <i class="sa sa-arrow-up text-danger fs-xxl me-2"></i>
                                        </span>
                                        <span class="collapsed-hidden">
                                            <i class="sa sa-arrow-down text-success fs-xxl me-2"></i>
                                        </span>
                                    </span>
                                        User management
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse" data-bs-parent="#js_demo_accordion-1"
                                     id="js_demo_accordion-1b">
                                    <div class="accordion-body">
                                        You can also integrate <strong>strong icons or animations</strong> to indicate
                                        when
                                        a section is opened or closed, improving user experience.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="false" class="accordion-button custom-collapse collapsed"
                                            data-bs-target="#js_demo_accordion-1c" data-bs-toggle="collapse"
                                            type="button">
                                    <span class="me-2">
                                        <span class="collapsed-reveal">
                                            <i class="sa sa-arrow-up text-danger fs-xxl me-2"></i>
                                        </span>
                                        <span class="collapsed-hidden">
                                            <i class="sa sa-arrow-down text-success fs-xxl me-2"></i>
                                        </span>
                                    </span>
                                        User groups
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse" data-bs-parent="#js_demo_accordion-1"
                                     id="js_demo_accordion-1c">
                                    <div class="accordion-body">
                                        However, developers can tweak the settings to allow multiple sections to stay
                                        open
                                        simultaneously using <code>data-bs-parent="false"</code>.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Accordion <span class="fw-light"><i>active state icons</i></span>
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
                            Add some navigation to a card's header (or block) with built-in nav components. You can
                            reveal
                            or hide elements during <code>.collapse</code> state using the classes
                            <code>.collapsed-reveal</code> and <code>.collapsed-hidden</code>
                            <br/><br/>
                            <strong>Make sure the <code>.collapsed-*</code> classes are inside the
                                <code>data-toggle="collapse"</code> element or adjacent to it</strong>
                        </div>
                        <div class="frame-wrap w-100">
                            <div class="accordion" id="js_demo_accordion-4">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button aria-expanded="true" class="accordion-button custom-collapse"
                                                data-bs-target="#js_demo_accordion-4a" data-bs-toggle="collapse"
                                                type="button">
                                            Email marketing
                                            <span class="ms-auto">
                                            <span class="collapsed-reveal">
                                                <i class="sa sa-envelope-open text-danger fs-xl"></i>
                                            </span>
                                            <span class="collapsed-hidden">
                                                <i class="sa sa-envelope text-success fs-xl"></i>
                                            </span>
                                        </span>
                                        </button>
                                    </h2>
                                    <div class="accordion-collapse collapse show" data-bs-parent="#js_demo_accordion-4"
                                         id="js_demo_accordion-4a">
                                        <div class="accordion-body">
                                            In addition to its built-in styling, the accordion can be controlled
                                            programmatically using Bootstrap’s <strong>JavaScript API</strong>.
                                            Functions like <code>.collapse('toggle')</code> allow developers to open,
                                            close,
                                            or update accordion elements dynamically via JavaScript.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button aria-expanded="false" class="accordion-button custom-collapse collapsed"
                                                data-bs-target="#js_demo_accordion-4b" data-bs-toggle="collapse"
                                                type="button">
                                            Online compaign
                                            <span class="ms-auto">
                                            <span class="collapsed-reveal">
                                                <i class="sa sa-envelope-open text-danger fs-xl"></i>
                                            </span>
                                            <span class="collapsed-hidden">
                                                <i class="sa sa-envelope text-success fs-xl"></i>
                                            </span>
                                        </span>
                                        </button>
                                    </h2>
                                    <div class="accordion-collapse collapse" data-bs-parent="#js_demo_accordion-4"
                                         id="js_demo_accordion-4b">
                                        <div class="accordion-body">
                                            For accessibility, Bootstrap includes <strong>strong ARIA
                                                attributes</strong>
                                            such as <code>aria-expanded</code> and <code>aria-controls</code>, ensuring
                                            screen readers can navigate the accordion efficiently.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button aria-expanded="false" class="accordion-button custom-collapse collapsed"
                                                data-bs-target="#js_demo_accordion-4c" data-bs-toggle="collapse"
                                                type="button">
                                            Electronic Data Management
                                            <span class="ms-auto">
                                            <span class="collapsed-reveal">
                                                <i class="sa sa-envelope-open text-danger fs-xl"></i>
                                            </span>
                                            <span class="collapsed-hidden">
                                                <i class="sa sa-envelope text-success fs-xl"></i>
                                            </span>
                                        </span>
                                        </button>
                                    </h2>
                                    <div class="accordion-collapse collapse" data-bs-parent="#js_demo_accordion-4"
                                         id="js_demo_accordion-4c">
                                        <div class="accordion-body">
                                            This makes it an excellent choice for creating responsive, <strong>
                                                mobile-friendly interfaces</strong> with a clean and structured layout.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Accordion <span class="fw-light"><i>hover</i></span>
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
                            Adding a hover effect adds nice element to your accordion. Achieve this by adding class
                            <code>.accordion-hover</code> to <code>.accordion</code>
                        </div>
                        <div class="accordion accordion-hover" id="js_demo_accordion-5">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="true" class="accordion-button"
                                            data-bs-target="#js_demo_accordion-5a" data-bs-toggle="collapse"
                                            type="button">
                                        <i class="sa sa-calendar-fine width-2 me-2 fs-xxl"></i>
                                        Report settings
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse show" data-bs-parent="#js_demo_accordion-5"
                                     id="js_demo_accordion-5a">
                                    <div class="accordion-body">
                                        <p>Bootstrap Accordion allows extensive customization using CSS variables and
                                            utility classes. You can modify backgrounds, borders, typography, and
                                            spacing to
                                            match your website’s design system.</p>
                                        <p class="mb-0">For example, applying <code>bg-primary text-white</code> to an
                                            accordion header gives it a bold, standout appearance.
                                            Additionally, you can tweak <code>data-bs-theme="dark"</code> to create a
                                            sleek,
                                            modern dark mode version.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="false" class="accordion-button collapsed"
                                            data-bs-target="#js_demo_accordion-5b" data-bs-toggle="collapse"
                                            type="button">
                                        <i class="sa sa-layers width-2 fs-xxl me-2"></i>
                                        Merge reports
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse" data-bs-parent="#js_demo_accordion-5"
                                     id="js_demo_accordion-5b">
                                    <div class="accordion-body">
                                        <p>Icons can be added to accordion headers to <strong>indicate expansion and
                                                collapse actions</strong>. Bootstrap provides built-in icons from
                                            Bootstrap
                                            Icons, Font Awesome, or custom SVGs for this purpose.</p>
                                        <p class="mb-0">For instance, using <code>bi-chevron-down</code> and toggling
                                            classes dynamically can enhance user interaction. These visual indicators
                                            improve clarity and provide a <strong>better UX for touch-based
                                                navigation</strong>.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="false" class="accordion-button collapsed"
                                            data-bs-target="#js_demo_accordion-5c" data-bs-toggle="collapse"
                                            type="button">
                                        <i class="sa sa-cloud-upload width-2 fs-xxl me-2"></i>
                                        Backup reports
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse" data-bs-parent="#js_demo_accordion-5"
                                     id="js_demo_accordion-5c">
                                    <div class="accordion-body">
                                        <p>Placing an accordion inside a Bootstrap Card can enhance the structure and
                                            readability of content. This combination is often used for pricing tables,
                                            user
                                            profiles, or expandable content sections.</p>
                                        <p class="mb-0">Cards provide additional padding, shadows, and borders, making
                                            the
                                            accordion feel more polished and refined. Applying <code>card-header</code>
                                            styles to the accordion title further integrates the two seamlessly.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Nested <span class="fw-light"><i>accordions</i></span>
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
                            <strong>Caution!</strong> Nested accordions will inherit the properties of its parent. In
                            the
                            example below we use the default accordion for parent and a different style for children.
                        </div>
                        <div class="accordion" id="js_demo_accordion-6">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="true" class="accordion-button"
                                            data-bs-target="#js_demo_accordion-6a" data-bs-toggle="collapse"
                                            type="button">
                                        <div class="d-flex flex-row align-items-center">
                                        <span class="me-2">
                                            <img alt="Sunn A." class="rounded-circle profile-image"
                                                 src="/img/demo/avatars/avatar-admin.png"/>
                                        </span>
                                            <div class="info-card-text">
                                                <div class="text-truncate text-truncate-lg">Sunny A.</div>
                                                <span
                                                        class="text-truncate text-truncate-md fw-300">sunnya@getwebora.com</span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse show" data-bs-parent="#js_demo_accordion-6"
                                     id="js_demo_accordion-6a">
                                    <div class="accordion-body">
                                        <!-- nested -->
                                        <div class="accordion" id="js_demo_accordion-7">
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button aria-expanded="true"
                                                            class="accordion-button custom-collapse px-3 py-2 bg-success-600 text-white"
                                                            data-bs-target="#js_demo_accordion-7a"
                                                            data-bs-toggle="collapse"
                                                            type="button">
                                                        Bloodworks
                                                        <span class="ms-auto">
                                                        <span class="collapsed-reveal">
                                                            <i class="sa sa-arrow-up fs-xl"></i>
                                                        </span>
                                                        <span class="collapsed-hidden">
                                                            <i class="sa sa-arrow-down fs-xl"></i>
                                                        </span>
                                                    </span>
                                                    </button>
                                                </h2>
                                                <div class="accordion-collapse collapse show"
                                                     data-bs-parent="#js_demo_accordion-7" id="js_demo_accordion-7a">
                                                    <div class="accordion-body bg-success-50 p-3">
                                                        Anim pariatur cliche reprehenderit, enim eiusmod high life
                                                        accusamus
                                                        terry richardson ad squid. 3 wolf moon officia aute, non
                                                        cupidatat
                                                        skateboard dolor brunch. Food truck quinoa nesciunt laborum
                                                        eiusmod.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button aria-expanded="false"
                                                            class="accordion-button custom-collapse px-3 py-2 collapsed bg-warning-500 text-dark"
                                                            data-bs-target="#js_demo_accordion-7b"
                                                            data-bs-toggle="collapse"
                                                            type="button">
                                                        Xray reports
                                                        <span class="ms-auto">
                                                        <span class="collapsed-reveal">
                                                            <i class="sa sa-arrow-up fs-xl"></i>
                                                        </span>
                                                        <span class="collapsed-hidden">
                                                            <i class="sa sa-arrow-down fs-xl"></i>
                                                        </span>
                                                    </span>
                                                    </button>
                                                </h2>
                                                <div class="accordion-collapse collapse"
                                                     data-bs-parent="#js_demo_accordion-7" id="js_demo_accordion-7b">
                                                    <div class="accordion-body bg-warning-50 p-3">
                                                        Anim pariatur cliche reprehenderit, enim eiusmod high life
                                                        accusamus
                                                        terry richardson ad squid. 3 wolf moon officia aute, non
                                                        cupidatat
                                                        skateboard dolor brunch. Food truck quinoa nesciunt laborum
                                                        eiusmod.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button aria-expanded="false"
                                                            class="accordion-button custom-collapse px-3 py-2 collapsed bg-info-700 text-white"
                                                            data-bs-target="#js_demo_accordion-7c"
                                                            data-bs-toggle="collapse"
                                                            type="button">
                                                        ECG
                                                        <span class="ms-auto">
                                                        <span class="collapsed-reveal">
                                                            <i class="sa sa-arrow-up fs-xl"></i>
                                                        </span>
                                                        <span class="collapsed-hidden">
                                                            <i class="sa sa-arrow-down fs-xl"></i>
                                                        </span>
                                                    </span>
                                                    </button>
                                                </h2>
                                                <div class="accordion-collapse collapse"
                                                     data-bs-parent="#js_demo_accordion-7" id="js_demo_accordion-7c">
                                                    <div class="accordion-body bg-info-50 p-3">
                                                        Anim pariatur cliche reprehenderit, enim eiusmod high life
                                                        accusamus
                                                        terry richardson ad squid. 3 wolf moon officia aute, non
                                                        cupidatat
                                                        skateboard dolor brunch. Food truck quinoa nesciunt laborum
                                                        eiusmod.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button aria-expanded="false" class="accordion-button collapsed"
                                            data-bs-target="#js_demo_accordion-6b" data-bs-toggle="collapse"
                                            type="button">
                                        <div class="d-flex flex-row align-items-center">
                                        <span class="me-2">
                                            <img alt="Jessica Milano" class="rounded-circle profile-image"
                                                 src="/img/demo/avatars/avatar-j.png"/>
                                        </span>
                                            <div class="info-card-text">
                                                <div class="text-truncate text-truncate-lg">Jessica Milano</div>
                                                <span
                                                        class="text-truncate text-truncate-md">jmilano@getwebora.com</span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse" data-bs-parent="#js_demo_accordion-6"
                                     id="js_demo_accordion-6b">
                                    <div class="accordion-body">
                                        <div class="alert alert-danger mb-0">
                                            <strong>
                                                Oops!
                                            </strong>
                                            Looks like there are no reports available for this user
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
