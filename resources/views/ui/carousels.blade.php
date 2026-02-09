@extends('layouts.vertical', ['pageTitle' => 'Carousels'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Carousels', 'pageSubTitle1' => 'Visual Feedback', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Bootstrap carousels are slideshow components that cycle through images or content with controls, indicators, and optional autoplay.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="info-container">
                <p class="mb-0">The carousel is a slideshow for cycling through a series of content, built with CSS 3D
                    transforms and a bit of JavaScript. It works with a series of images, text, or custom markup. It
                    also
                    includes support for previous/next controls and indicators.</p>
            </div>
        </div>
        <div class="col-xl-12">
            <!--Kitchen sink example-->
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
                            Carousels don't automatically normalize slide dimensions. As such, you may need to use
                            additional utilities or custom styles to appropriately size content. While carousels support
                            previous/next controls and indicators, they're not explicitly required. Add and customize as
                            you
                            see fit.
                            <br/>
                            <br/>
                            <strong>The <code>.active</code> class needs to be added to one of the slides</strong>
                            otherwise
                            the carousel will not be visible. Also be sure to set a unique id on the
                            <code>.carousel</code>
                            for optional controls, especially if you're using multiple carousels on a single page.
                            Control
                            and indicator elements must have a <code>data-target</code> attribute (or <code>href</code>
                            for
                            links) that matches the id of the <code>.carousel</code> element.
                        </div>
                        <div class="carousel slide" data-bs-ride="carousel" id="carouselExampleKitchenSink">
                            <ol class="carousel-indicators">
                                <li class="" data-bs-slide-to="0" data-bs-target="#carouselExampleKitchenSink"></li>
                                <li class="active" data-bs-slide-to="1"
                                    data-bs-target="#carouselExampleKitchenSink"></li>
                                <li class="" data-bs-slide-to="2" data-bs-target="#carouselExampleKitchenSink"></li>
                            </ol>
                            <div class="carousel-inner">
                                <div class="carousel-item">
                                    <img alt="First slide" class="d-block w-100" src="/img/demo/relax-full.jpg"/>
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5 class="color-white opacity-70">First slide label</h5>
                                        <p>Nulla vitae elit libero, a pharetra augue mollis interdum.</p>
                                    </div>
                                </div>
                                <div class="carousel-item active">
                                    <img alt="Second slide" class="d-block w-100" src="/img/demo/peace-full.jpg"/>
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5 class="color-white opacity-70">Second slide label</h5>
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Third slide" class="d-block w-100" src="/img/demo/sea-full.jpg"/>
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5 class="color-white opacity-70">Third slide label</h5>
                                        <p>Praesent commodo cursus magna, vel scelerisque nisl consectetur.</p>
                                    </div>
                                </div>
                            </div>
                            <a class="carousel-control-prev" data-bs-slide="prev" href="#carouselExampleKitchenSink"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-prev-icon"></span>
                                <span class="visually-hidden">Previous</span>
                            </a>
                            <a class="carousel-control-next" data-bs-slide="next" href="#carouselExampleKitchenSink"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-next-icon"></span>
                                <span class="visually-hidden">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <!--Slides only-->
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Slides <span class="fw-300"><i>only</i></span>
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
                            Notice presence of the <code>.d-block</code> and <code>.w-100</code> on carousel images
                        </div>
                        <div class="carousel slide" data-bs-interval="2000" data-bs-ride="carousel"
                             id="carouselExampleSlidesOnly">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img alt="First slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=51adf6&amp;fg=ffffff&amp;text=First slide"
                                         src="about:blank"/>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Second slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=39a1f4&amp;fg=ffffff&amp;text=Second slide"
                                         src="about:blank"/>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Third slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=2196F3&amp;fg=ffffff&amp;text=Third slide"
                                         src="about:blank"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Controls-->
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Control <span class="fw-300"><i>example</i></span>
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
                            Adding in previous and next controls
                        </div>
                        <div class="carousel slide" data-bs-ride="carousel" id="carouselExampleControls">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img alt="First slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=fe85be&amp;fg=ffffff&amp;text=First slide"
                                         src="about:blank"/>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Second slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=fe6bb0&amp;fg=ffffff&amp;text=Second slide"
                                         src="about:blank"/>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Third slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=fd52a3&amp;fg=ffffff&amp;text=Third slide"
                                         src="about:blank"/>
                                </div>
                            </div>
                            <a class="carousel-control-prev" data-bs-slide="prev" href="#carouselExampleControls"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-prev-icon"></span>
                                <span class="visually-hidden">Previous</span>
                            </a>
                            <a class="carousel-control-next" data-bs-slide="next" href="#carouselExampleControls"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-next-icon"></span>
                                <span class="visually-hidden">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!--Controls-->
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Interval <span class="fw-300"><i>example</i></span>
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
                            Add <code>data-interval=""</code> to a <code>.carousel-item</code> to change the amount of
                            time
                            to delay between automatically cycling to the next item. E.g The first image will change
                            after
                            10 seconds, the second one will change after 2 seconds, and the following will change as
                            default
                            settings.
                        </div>
                        <div class="carousel slide" data-bs-ride="carousel" id="carouselExampleInterval">
                            <div class="carousel-inner">
                                <div class="carousel-item active" data-bs-interval="10000">
                                    <img alt="First slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=ffd274&amp;fg=333333&amp;text=First slide"
                                         src="about:blank"/>
                                </div>
                                <div class="carousel-item" data-bs-interval="2000">
                                    <img alt="Second slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=ffca5b&amp;fg=000000&amp;text=Second slide"
                                         src="about:blank"/>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Third slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=ffc241&amp;fg=000000&amp;text=Third slide"
                                         src="about:blank"/>
                                </div>
                            </div>
                            <a class="carousel-control-prev" data-bs-slide="prev" href="#carouselExampleInterval"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-prev-icon"></span>
                                <span class="visually-hidden">Previous</span>
                            </a>
                            <a class="carousel-control-next" data-bs-slide="next" href="#carouselExampleInterval"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-next-icon"></span>
                                <span class="visually-hidden">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <!--Indicators-->
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Indicators <span class="fw-300"><i>example</i></span>
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
                            You can also add the indicators to the carousel, alongside the controls, too
                        </div>
                        <div class="carousel slide" data-bs-ride="carousel" id="carouselExampleIndicators">
                            <ol class="carousel-indicators">
                                <li class="active" data-bs-slide-to="0"
                                    data-bs-target="#carouselExampleIndicators"></li>
                                <li data-bs-slide-to="1" data-bs-target="#carouselExampleIndicators"></li>
                                <li data-bs-slide-to="2" data-bs-target="#carouselExampleIndicators"></li>
                            </ol>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img alt="First slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=37e2d0&amp;fg=ffffff&amp;text=First slide"
                                         src="about:blank"/>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Second slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=21dfcb&amp;fg=ffffff&amp;text=Second slide"
                                         src="about:blank"/>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Third slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=1dc9b7&amp;fg=ffffff&amp;text=Third slide"
                                         src="about:blank"/>
                                </div>
                            </div>
                            <a class="carousel-control-prev" data-bs-slide="prev" href="#carouselExampleIndicators"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-prev-icon"></span>
                                <span class="visually-hidden">Previous</span>
                            </a>
                            <a class="carousel-control-next" data-bs-slide="next" href="#carouselExampleIndicators"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-next-icon"></span>
                                <span class="visually-hidden">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!--Crossfade-->
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Crossfade <span class="fw-300"><i>example</i></span>
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
                            Add <code>.carousel-fade</code> to your carousel to animate slides with a fade transition
                            instead of a slide
                        </div>
                        <div class="carousel slide carousel-fade" data-bs-ride="carousel" id="carouselExampleFade">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img alt="First slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=a38cc6&amp;fg=ffffff&amp;text=First slide"
                                         src="about:blank"/>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Second slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=967bbd&amp;fg=ffffff&amp;text=Second slide"
                                         src="about:blank"/>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Third slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=886ab5&amp;fg=ffffff&amp;text=Third slide"
                                         src="about:blank"/>
                                </div>
                            </div>
                            <a class="carousel-control-prev" data-bs-slide="prev" href="#carouselExampleFade"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-prev-icon"></span>
                                <span class="visually-hidden">Previous</span>
                            </a>
                            <a class="carousel-control-next" data-bs-slide="next" href="#carouselExampleFade"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-next-icon"></span>
                                <span class="visually-hidden">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!--Add captions-->
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        Add <span class="fw-300"><i>captions</i></span>
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
                            Add captions to your slides easily with the <code>.carousel-caption</code> element within
                            any
                            <code>.carousel-item</code>. They can be easily hidden on smaller viewports, as shown below,
                            with optional <a href="#" target="_blank">display utilities</a>. We hide them initially with
                            <code>.d-none</code> and bring them back on medium-sized devices with
                            <code>.d-md-block</code>
                        </div>
                        <div class="carousel slide" data-bs-ride="carousel" id="carouselExampleCaptions">
                            <ol class="carousel-indicators">
                                <li class="active" data-bs-slide-to="0" data-bs-target="#carouselExampleCaptions"></li>
                                <li data-bs-slide-to="1" data-bs-target="#carouselExampleCaptions"></li>
                                <li data-bs-slide-to="2" data-bs-target="#carouselExampleCaptions"></li>
                            </ol>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img alt="First slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=5a636d&amp;fg=7f8995&amp;text=First slide"
                                         src="about:blank"/>
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5>First slide label</h5>
                                        <p>Nulla vitae elit libero, a pharetra augue mollis interdum.</p>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Second slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=5a636d&amp;fg=7f8995&amp;text=Second slide"
                                         src="about:blank"/>
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5>Second slide label</h5>
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Third slide" class="d-block w-100"
                                         data-src="holder.js/800x400?auto=yes&amp;bg=5a636d&amp;fg=7f8995&amp;text=Third slide"
                                         src="about:blank"/>
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5>Third slide label</h5>
                                        <p>Praesent commodo cursus magna, vel scelerisque nisl consectetur.</p>
                                    </div>
                                </div>
                            </div>
                            <a class="carousel-control-prev" data-bs-slide="prev" href="#carouselExampleCaptions"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-prev-icon"></span>
                                <span class="visually-hidden">Previous</span>
                            </a>
                            <a class="carousel-control-next" data-bs-slide="next" href="#carouselExampleCaptions"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-next-icon"></span>
                                <span class="visually-hidden">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Fullscreen Carousel -->
        <div aria-hidden="true" class="modal fade modal-fullscreen example-carousel-fullscreen" role="dialog"
             tabindex="-1">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content h-100 border-0 shadow-0 bg-fusion-800">
                    <button aria-label="Close"
                            class="close p-sm-2 p-md-4 text-white fs-xxl position-absolute pos-right me-sm-2 mt-sm-1 z-index-space"
                            data-dismiss="modal" type="button">
                        <span aria-hidden="true"><i class="sa sa-close"></i></span>
                    </button>
                    <div class="modal-body p-0">
                        <div class="carousel slide" data-bs-ride="carousel" id="example-carousel-fullscreen">
                            <ol class="carousel-indicators">
                                <li class="" data-bs-slide-to="0" data-bs-target="#example-carousel-fullscreen"></li>
                                <li class="active" data-bs-slide-to="1"
                                    data-bs-target="#example-carousel-fullscreen"></li>
                                <li class="" data-bs-slide-to="2" data-bs-target="#example-carousel-fullscreen"></li>
                            </ol>
                            <div class="carousel-inner">
                                <div class="carousel-item">
                                    <img alt="First slide" class="d-block w-100" src="/img/demo/relax-full.jpg"/>
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5 class="color-white opacity-70">First slide label</h5>
                                        <p>Nulla vitae elit libero, a pharetra augue mollis interdum.</p>
                                    </div>
                                </div>
                                <div class="carousel-item active">
                                    <img alt="Second slide" class="d-block w-100" src="/img/demo/peace-full.jpg"/>
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5 class="color-white opacity-70">Second slide label</h5>
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <img alt="Third slide" class="d-block w-100" src="/img/demo/sea-full.jpg"/>
                                    <div class="carousel-caption d-none d-md-block">
                                        <h5 class="color-white opacity-70">Third slide label</h5>
                                        <p>Praesent commodo cursus magna, vel scelerisque nisl consectetur.</p>
                                    </div>
                                </div>
                            </div>
                            <a class="carousel-control-prev" data-bs-slide="prev" href="#example-carousel-fullscreen"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-prev-icon"></span>
                                <span class="visually-hidden">Previous</span>
                            </a>
                            <a class="carousel-control-next" data-bs-slide="next" href="#example-carousel-fullscreen"
                               role="button">
                                <span aria-hidden="true" class="carousel-control-next-icon"></span>
                                <span class="visually-hidden">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.9.7/holder.min.js"></script>
@endsection
