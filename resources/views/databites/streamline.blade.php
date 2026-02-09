@extends('layouts.vertical', ['pageTitle' => 'Streamline'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Streamline', 'pageSubTitle1' => 'Data Visualization', 'pageSubTitle2'=> 'Data Bites', 'pageSubText' => 'A lightweight, dependency-free JavaScript library for generating sparkline charts.'])
@endsection

@section('content')
    <div class="info-container">
        Streamline is a lightweight, dependency-free JavaScript library for generating mini graphs. This in-house plugin
        is designed to be flexible and customizable to your needs. If you'd like to request additional features, please
        don’t hesitate to <a href="https://wrapbootstrap.com/user/MyOrange/message" target="_blank">contact us</a>.
    </div>
    <div class="row">
        <div class="col-xl-6">
            <!-- Line Chart -->
            <div id="panel-1" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>Line <span class="fw-300"><i>Chart</i></span></h2>
                    <div class="panel-toolbar">
                        <button type="button" class="btn btn-panel" data-action="panel-collapse"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Toggle">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-fullscreen"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Line charts display data points connected by straight line segments. You can customize the
                            line color and width using <code>data-line-color</code> and <code>data-line-width</code>
                            attributes.
                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="line" data-line-color="var(--success-500)"
                                      data-fill-color="transparent" data-height="40" data-width="110"
                                      data-line-width="2">5,3,9,6,5,9,7,3,5,2</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="line" data-line-color="var(--danger-500)"
                                      data-fill-color="transparent" data-height="40" data-width="110"
                                      data-line-width="2">0,-3,-6,-4,-5,-4,-7,-3,-5,-2</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="line" data-line-color="var(--warning-500)"
                                      data-fill-color="transparent" data-height="40" data-width="110"
                                      data-line-width="3">0,1,7,6,4,2,3,5,3,4</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="line" data-line-color="var(--info-500)"
                                      data-fill-color="transparent" data-height="40" data-width="110"
                                      data-line-width="4">5,3,9,6,5,3,1,7,6,4</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bar Chart -->
            <div id="panel-2" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>Bar <span class="fw-300"><i>Chart</i></span></h2>
                    <div class="panel-toolbar">
                        <button type="button" class="btn btn-panel" data-action="panel-collapse"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Toggle">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-fullscreen"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Bar charts display data as vertical bars. You can customize bar colors, width, and spacing
                            using data attributes.
                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="bar" data-bar-color="var(--primary-500)"
                                      data-height="40" data-width="110" data-bar-width="4" data-bar-spacing="1">5,3,9,6,5,9,7,3,5,2</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="bar" data-bar-color="var(--success-500)"
                                      data-neg-bar-color="var(--danger-500)" data-height="40" data-width="110"
                                      data-bar-width="4" data-bar-spacing="1">4,-3,-6,-4,-5,-4,7,-3,-5,2</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="bar" data-bar-color="var(--info-500)"
                                      data-height="40" data-width="110" data-bar-width="8" data-bar-spacing="2">0,1,7,6,4,2,3,5,3,4</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="bar" data-bar-color="var(--warning-500)"
                                      data-neg-bar-color="var(--danger-500)" data-height="40" data-width="110"
                                      data-bar-width="8" data-bar-spacing="2">0,-1,-7,-6,-4,-2,-3,-5,-3,-4</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tristate Chart -->
            <div id="panel-3" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>Tristate <span class="fw-300"><i>Chart</i></span></h2>
                    <div class="panel-toolbar">
                        <button type="button" class="btn btn-panel" data-action="panel-collapse"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Toggle">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-fullscreen"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Tristate charts represent data in three states: positive (1), neutral (0), and negative
                            (-1). Each state can be represented with different colors.
                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="tristate" data-height="80" data-width="110"
                                      data-bar-width="8" data-bar-spacing="2" data-pos-bar-color="var(--success-500)"
                                      data-neg-bar-color="var(--danger-500)" data-zero-bar-color="var(--secondary)">1,1,0,1,-1,-1,1,-1,0,0,1,1</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="tristate" data-height="80" data-width="110"
                                      data-bar-width="8" data-bar-spacing="2" data-pos-bar-color="var(--primary-500)"
                                      data-neg-bar-color="var(--info-500)" data-zero-bar-color="var(--warning-500)">1,-1,0,1,-1,0,1,0,-1,1,0,-1</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <!-- Area Chart -->
            <div id="panel-4" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>Area <span class="fw-300"><i>Chart</i></span></h2>
                    <div class="panel-toolbar">
                        <button type="button" class="btn btn-panel" data-action="panel-collapse"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Toggle">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-fullscreen"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Area charts are line charts with the area below the line filled with a color. Customize both
                            line and fill colors using data attributes.
                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="line" data-fill-color="var(--success-200)"
                                      data-line-color="var(--success-500)" data-height="40" data-width="110">5,3,2,-1,-3,-2,2,3,5,2</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="line" data-fill-color="var(--danger-200)"
                                      data-line-color="var(--danger-500)" data-height="40" data-width="110">1,4,4,7,5,9,10,4,4,7,5,9,10</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="line" data-fill-color="var(--warning-200)"
                                      data-line-color="var(--warning-500)" data-height="40" data-width="110">5,3,9,6,5,3,1,7,6,4,4,1,5</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="line" data-fill-color="var(--info-200)"
                                      data-line-color="var(--info-500)" data-height="40" data-width="110">0,1,7,6,4,2,3,5,3,4,3,1,4,6</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Discrete Chart -->
            <div id="panel-5" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>Discrete <span class="fw-300"><i>Chart</i></span></h2>
                    <div class="panel-toolbar">
                        <button type="button" class="btn btn-panel" data-action="panel-collapse"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Toggle">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-fullscreen"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Discrete charts display data as individual vertical lines. Set a threshold value to change
                            the color of lines based on their values.
                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="discrete" data-line-color="var(--primary-500)"
                                      data-threshold-color="var(--danger-500)" data-threshold-value="5" data-height="40"
                                      data-width="110" data-line-width="2">5,9,7,6,5,3,2,5,8,9,7,5</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="discrete" data-line-color="var(--success-500)"
                                      data-threshold-color="var(--warning-500)" data-threshold-value="6"
                                      data-height="40" data-width="110"
                                      data-line-width="3">8,4,6,9,5,3,7,5,7,8,4,9</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie Chart -->
            <div id="panel-6" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>Pie <span class="fw-300"><i>Chart</i></span></h2>
                    <div class="panel-toolbar">
                        <button type="button" class="btn btn-panel" data-action="panel-collapse"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Toggle">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-fullscreen"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Pie charts show data as proportional slices of a whole. You can customize slice colors using
                            the <code>data-slice-colors</code> attribute with an array of colors.
                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="pie" data-width="80"
                                      data-height="80">30,20,45,5</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="streamline" data-type="pie" data-width="80" data-height="80"
                                      data-slice-colors="var(--info-500),var(--warning-500),var(--success-200)">7,6,3</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/streamline.js'])
@endsection
