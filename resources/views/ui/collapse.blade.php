@extends('layouts.vertical', ['pageTitle' => 'Collapse'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Collapse', 'pageSubTitle1' => 'Interactive Features', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Toggle the visibility of content across your project with a few classes.'])
@endsection

@section('content')
    <div class="info-container">
        <p>
            The collapse JavaScript plugin is used to show and hide content. Buttons or anchors are used as
            triggers that are mapped to specific elements you toggle. Collapsing an element will animate the
            height from its current value to 0. Given how CSS handles animations, you cannot use padding on
            a <code>.collapse</code> element. Instead, use the class as an independent wrapping element.
        </p>
        <p class="mb-0">

            Learn more about this component on bootstrap's <a
                href="https://getbootstrap.com/docs/5.3/components/collapse/"
                target="_blank">official documentation</a>.
        </p>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Basic <span class="fw-300"><i>example</i></span>
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
                        <p>Click the buttons below to show or hide elements using class changes:</p>
                        <ul class="list-spaced mt-3">
                            <li><code>.collapse</code> hides content</li>
                            <li><code>.collapsing</code> is applied during transitions</li>
                            <li><code>.collapse.show</code> reveals content</li>
                        </ul>
                        <p>You can use a link with the <code>href</code> attribute or a button with the
                            <code>data-bs-target</code> attribute. In both cases, <code>data-bs-toggle="collapse"</code>
                            is
                            required.</p>
                        <div class="frame-wrap mt-3 mb-0 w-100">
                            <p class="demo">
                                <a aria-controls="collapseExample" aria-expanded="false" class="btn btn-primary"
                                   data-bs-toggle="collapse" href="#collapseExample" role="button">
                                    Link with href
                                </a>
                                <button aria-controls="collapseExample" aria-expanded="false" class="btn btn-primary"
                                        data-bs-target="#collapseExample" data-bs-toggle="collapse" type="button">
                                    Button with data-target
                                </button>
                            </p>
                            <div class="collapse" id="collapseExample">
                                <div class="card card-body">
                                    Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry
                                    richardson ad
                                    squid. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt
                                    sapiente ea proident.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Multiple <span class="fw-300"><i>Targets</i></span>
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
                            <p>A <code>&lt;button&gt;</code> or <code>&lt;a&gt;</code> can toggle multiple elements by
                                referencing them with a selector in its <code>href</code> or <code>data-bs-target</code>
                                attribute.
                                Multiple <code>&lt;button&gt;</code> or <code>&lt;a&gt;</code> elements can control the
                                same
                                element if
                                they each reference it with <code>href</code> or <code>data-bs-target</code>.</p>
                        </div>
                        <div class="frame-wrap mb-0 w-100">
                            <p class="demo">
                                <a aria-controls="multiCollapseExample1" aria-expanded="false" class="btn btn-primary"
                                   data-bs-toggle="collapse" href="#multiCollapseExample1" role="button">Toggle first
                                    element</a>
                                <button aria-controls="multiCollapseExample2" aria-expanded="false"
                                        class="btn btn-primary"
                                        data-bs-target="#multiCollapseExample2" data-bs-toggle="collapse" type="button">
                                    Toggle
                                    second element
                                </button>
                                <button aria-controls="multiCollapseExample1 multiCollapseExample2"
                                        aria-expanded="false"
                                        class="btn btn-primary" data-bs-target=".multi-collapse"
                                        data-bs-toggle="collapse"
                                        type="button">Toggle both elements
                                </button>
                            </p>
                            <div class="row">
                                <div class="col">
                                    <div class="collapse multi-collapse" id="multiCollapseExample1">
                                        <div class="card card-body">
                                            Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry
                                            richardson ad squid. Nihil anim keffiyeh helvetica, craft beer labore wes
                                            anderson cred nesciunt sapiente ea proident.
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="collapse multi-collapse" id="multiCollapseExample2">
                                        <div class="card card-body">
                                            Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry
                                            richardson ad squid. Nihil anim keffiyeh helvetica, craft beer labore wes
                                            anderson cred nesciunt sapiente ea proident.
                                        </div>
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
                        Accordion <span class="fw-300"><i>example</i></span>
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
                            <p>Add navigation to a card’s header (or body) using Bootstrap's built-in nav components.
                                You
                                can show or hide elements when a <code>.collapse</code> state changes using the
                                <code>.collapse-reveal</code> and <code>.collapse-hidden</code> classes.</p>
                            <p><strong>Ensure that the <code>.collapse-*</code> classes are placed inside the element
                                    with
                                    <code>data-bs-toggle="collapse"</code> or adjacent to it for proper
                                    functionality.</strong></p>
                        </div>
                        <div class="frame-wrap w-100">
                            <div class="accordion" id="accordionExample">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button aria-controls="collapseOne" aria-expanded="true"
                                                class="accordion-button"
                                                data-bs-target="#collapseOne" data-bs-toggle="collapse" type="button">
                                            Collapsible Group Item #1
                                            <span class="ms-auto">
                                            <span class="collapsed-reveal">
                                                <i class="sa sa-minus-circle text-danger"></i>
                                            </span>
                                            <span class="collapsed-hidden">
                                                <i class="sa sa-plus-circle text-success"></i>
                                            </span>
                                        </span>
                                        </button>
                                    </h2>
                                    <div aria-labelledby="headingOne" class="accordion-collapse collapse show"
                                         data-bs-parent="#accordionExample" id="collapseOne">
                                        <div class="accordion-body">
                                            Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry
                                            richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard
                                            dolor
                                            brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon
                                            tempor,
                                            sunt aliqua put a bird on it squid single-origin coffee nulla assumenda
                                            shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson
                                            cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo.
                                            Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth
                                            nesciunt
                                            you probably haven't heard of them accusamus labore sustainable VHS.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button aria-controls="collapseTwo" aria-expanded="false"
                                                class="accordion-button collapsed" data-bs-target="#collapseTwo"
                                                data-bs-toggle="collapse" type="button">
                                            Collapsible Group Item #2
                                            <span class="ms-auto">
                                            <span class="collapsed-reveal">
                                                <i class="sa sa-minus-circle text-danger"></i>
                                            </span>
                                            <span class="collapsed-hidden">
                                                <i class="sa sa-plus-circle text-success"></i>
                                            </span>
                                        </span>
                                        </button>
                                    </h2>
                                    <div aria-labelledby="headingTwo" class="accordion-collapse collapse"
                                         data-bs-parent="#accordionExample" id="collapseTwo">
                                        <div class="accordion-body">
                                            Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry
                                            richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard
                                            dolor
                                            brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon
                                            tempor,
                                            sunt aliqua put a bird on it squid single-origin coffee nulla assumenda
                                            shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson
                                            cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo.
                                            Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth
                                            nesciunt
                                            you probably haven't heard of them accusamus labore sustainable VHS.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button aria-controls="collapseThree" aria-expanded="false"
                                                class="accordion-button collapsed" data-bs-target="#collapseThree"
                                                data-bs-toggle="collapse" type="button">
                                            Collapsible Group Item #3
                                            <span class="ms-auto">
                                            <span class="collapsed-reveal">
                                                <i class="sa sa-minus-circle text-danger"></i>
                                            </span>
                                            <span class="collapsed-hidden">
                                                <i class="sa sa-plus-circle text-success"></i>
                                            </span>
                                        </span>
                                        </button>
                                    </h2>
                                    <div aria-labelledby="headingThree" class="accordion-collapse collapse"
                                         data-bs-parent="#accordionExample" id="collapseThree">
                                        <div class="accordion-body">
                                            Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry
                                            richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard
                                            dolor
                                            brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon
                                            tempor,
                                            sunt aliqua put a bird on it squid single-origin coffee nulla assumenda
                                            shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson
                                            cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo.
                                            Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth
                                            nesciunt
                                            you probably haven't heard of them accusamus labore sustainable VHS.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFour">
                                        <button aria-controls="collapseFour" aria-expanded="false"
                                                class="accordion-button collapsed" data-bs-target="#collapseFour"
                                                data-bs-toggle="collapse" type="button">
                                            Collapsible Group Item #4
                                            <span class="ms-auto">
                                            <span class="collapsed-reveal">
                                                <i class="sa sa-minus-circle text-danger"></i>
                                            </span>
                                            <span class="collapsed-hidden">
                                                <i class="sa sa-plus-circle text-success"></i>
                                            </span>
                                        </span>
                                        </button>
                                    </h2>
                                    <div aria-labelledby="headingFour" class="accordion-collapse collapse"
                                         data-bs-parent="#accordionExample" id="collapseFour">
                                        <div class="accordion-body">
                                            Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry
                                            richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard
                                            dolor
                                            brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon
                                            tempor,
                                            sunt aliqua put a bird on it squid single-origin coffee nulla assumenda
                                            shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson
                                            cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo.
                                            Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth
                                            nesciunt
                                            you probably haven't heard of them accusamus labore sustainable VHS.
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
