@extends('layouts.vertical', ['pageTitle' => 'Peity Charts'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Peity Charts', 'pageSubTitle1' => 'Data Visualization', 'pageSubTitle2'=> 'Data Bites', 'pageSubText' => 'Lightweight plugin to render simple, animated and retina optimized pie charts.'])
@endsection

@section('content')
    <div class="info-container">
        Peity turns element text like <code>"5,3,9,6,5,9,7"</code> into simple SVG mini graphs. These lightweight charts
        can
        be used inline or as small data visualization elements.
    </div>
    <div class="row">
        <div class="col-xl-6">
            <!-- Line Chart -->
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Line <span class="fw-300"><i>Chart</i></span>
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
                            You can specify the width of the strokes using <code>strokeWidth</code> inside
                            <code>data-peity</code> attribute

                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-line"
                                      data-peity='{ "fill": false, "stroke": "var(--success-500)", "height": 40, "width": "110", "strokeWidth": 2 }'>0,-3,-6,-4,-5,-4,-7,-3,-5,-2</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-line"
                                      data-peity='{ "fill": false, "stroke": "var(--danger-500)", "height": 40, "width": "110", "strokeWidth": 2 }'>0,-3,-6,-4,-5,-4,-7,-3,-5,-2</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-line"
                                      data-peity='{ "fill": false, "stroke": "var(--warning-500)", "height": 40, "width": "110", "strokeWidth": 3 }'>0,1,7,6,4,2,3,5,3,4</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-line"
                                      data-peity='{ "fill": false, "stroke": "var(--info-500)", "height": 40, "width": "110", "strokeWidth": 4 }'>5,3,9,6,5,3,1,7,6,4</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Bar Chart -->
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Bar <span class="fw-300"><i>Chart</i></span>
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
                            Bar charts can have positive or negative integers. You can change the bar colors via the
                            following <code>
                                data-peity='{ "fill":
                                ["var(--info-200)","var(--info-500)","var(--info-700)","var(--info-900)"] }'</code>
                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-bar"
                                      data-peity='{ "fill": ["var(--info-200)","var(--info-500)","var(--info-700)","var(--info-900)"], "height": 40, "width": "110" }'>5,3,9,6,5,9,7,3,5,2</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-bar"
                                      data-peity='{ "fill": ["var(--success-200)","var(--success-500)","var(--success-700)","var(--success-900)"], "height": 40, "width": "110" }'>4,-3,-6,-4,-5,-4,7,-3,-5,2</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-bar"
                                      data-peity='{ "fill": ["var(--info-500)","var(--success-500)","var(--info-600)","var(--success-300)"], "height": 40, "width": "110" }'>0,1,7,6,4,2,3,5,3,4</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-bar"
                                      data-peity='{ "fill": ["var(--success-200)","var(--success-300)","var(--success-400)","var(--success-500)"], "height": 40, "width": "110" }'>0,-1,-7,-6,-4,-2,-3,-5,-3,-4</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Donut Chart -->
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Donut <span class="fw-300"><i>Chart</i></span>
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
                            Donut charts can have any values. You can change the colors via CSS variables:
                            <code>data-peity='{ "fill":
                                ["var(--primary-500)","var(--success-500)","var(--info-500)","var(--warning-500)"]
                                }'</code>
                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-donut"
                                      data-peity='{ "fill": ["var(--info-500)","var(--warning-500)"], "height": 40, "width": "50" }'>1/3</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-donut"
                                      data-peity='{ "fill": ["var(--info-500)","var(--warning-500)","var(--success-200)","var(--primary-100)"], "height": 40, "width": "50" }'>7,6,3,4</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-donut"
                                      data-peity='{ "fill": ["var(--success-300)","var(--danger-500)"], "height": 40, "width": "50" }'>5/10</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-donut"
                                      data-peity='{ "fill": ["var(--warning-300)","var(--success-500)","var(--danger-300)","var(--info-500)","var(--primary-500)","var(--danger-300)","var(--info-100)","var(--danger-700)"], "height": 40, "width": "50" }'>2,3,4,7,9,10,5,6</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Dynamic Colors -->
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        Dynamic <span class="fw-300"><i>Colors</i></span>
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
                            Colors, sizes and different settings of the chart can be generated dynamically based on data
                            values. The examples below use the <code>.bar-negative</code> class to show
                            positive/negative
                            values in different colors and <code>.bar-transition</code> class to create a gradient
                            effect
                            across bars.
                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="bar-negative"
                                      data-peity='{ "height": 40, "width": "110" }'>5,3,2,-1,-3,-2,2,3,5,2</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="bar-transition"
                                      data-peity='{ "height": 40, "width": "110" }'>5,3,9,6,5,9,7,3,5,2</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <!-- Area Chart -->
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Area <span class="fw-300"><i>Chart</i></span>
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
                            Example of line with filling. You can add fill by using inserting
                            <code>data-peity='{ "fill": "#1dc9b7" }'</code>
                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-line"
                                      data-peity='{ "fill": "var(--success-200)", "stroke": "var(--success-500)", "height": 40, "width": "110" }'>5,3,2,-1,-3,-2,2,3,5,2</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-line"
                                      data-peity='{ "fill": "var(--danger-200)", "stroke": "var(--danger-500)", "height": 40, "width": "110" }'>1,4,4,7,5,9,10,4,4,7,5,9,10</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-line"
                                      data-peity='{ "fill": "var(--warning-200)", "stroke": "var(--warning-500)", "height": 40, "width": "110" }'>5,3,9,6,5,3,1,7,6,4,4,1,5</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-line"
                                      data-peity='{ "fill": "var(--info-200)", "stroke": "var(--info-500)", "height": 40, "width": "110" }'>0,1,7,6,4,2,3,5,3,4,3,1,4,6</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Pie Chart -->
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Pie <span class="fw-300"><i>Chart</i></span>
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
                            Pie charts can have any values. You can change the colors using CSS variables:
                            <code>data-peity='{ "fill":
                                ["var(--primary-500)","var(--success-500)","var(--info-500)","var(--warning-500)"]
                                }'</code>
                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-pie"
                                      data-peity='{ "fill": ["var(--warning-400)","var(--info-500)"], "height": 40, "width": "50" }'>1/4</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-pie"
                                      data-peity='{ "fill": ["var(--info-500)","var(--warning-500)","var(--success-200)","var(--primary-100)"], "height": 40, "width": "50" }'>7,6,3,4</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-pie"
                                      data-peity='{ "fill": ["var(--success-300)","var(--danger-500)"], "height": 40, "width": "50" }'>5/10</span>
                            </div>
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="peity-pie"
                                      data-peity='{ "fill": ["var(--warning-300)","var(--success-500)","var(--danger-300)","var(--info-500)","var(--primary-500)","var(--danger-300)","var(--info-100)","var(--danger-700)"], "height": 40, "width": "50" }'>2,3,4,7,9,10,5,6</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Updating Chart -->
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Updating <span class="fw-300"><i>Chart</i></span>
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
                            This chart is automatically updated every half second using vanilla JavaScript. A new random
                            value is generated, added to the chart data, and the oldest value is removed to create a
                            live
                            data visualization effect.
                        </div>
                        <div class="demo d-flex justify-content-center flex-wrap d-sm-block">
                            <div class="p-3 w-auto text-center d-inline-flex border-faded">
                                <span class="updating-chart">5,3,9,6,5,9,7,3,5,2,5,3,9,6,5,9,7,3,5,2</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Chart Sizes -->
            <div class="panel panel-icon" id="panel-8">
                <div class="panel-hdr">
                    <h2>
                        Chart <span class="fw-300"><i>Sizes</i></span>
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
                            Since the chart is just a generated SVG file, you can potentially generate the chart to any
                            sizes using the values <code>innerRadius</code> and <code>radius</code> for pie and donut
                            charts, and <code>width</code> and <code>height</code> for other charts
                        </div>
                        <div class="demo demo-h-spacing d-flex justify-content-center flex-wrap d-sm-block">
                            <span class="peity-donut"
                                  data-peity='{ "fill": ["#fd3995"], "innerRadius": 13, "radius": 37 }'>1/7</span>
                            <span class="peity-donut"
                                  data-peity='{ "fill": ["#ffc241", "var(--bs-border-color)"], "innerRadius": 10, "radius": 30 }'>2/7</span>
                            <span class="peity-donut"
                                  data-peity='{ "fill": ["#ffdb8e", "var(--bs-border-color)"], "innerRadius": 20, "radius": 28 }'>3/7</span>
                            <span class="peity-donut"
                                  data-peity='{ "fill": ["#1dc9b7", "var(--bs-border-color)"], "innerRadius": 20, "radius": 25 }'>4/7</span>
                            <span class="peity-donut"
                                  data-peity='{ "fill": ["#2196F3", "var(--bs-border-color)"], "innerRadius": 17, "radius": 21 }'>5/7</span>
                            <span class="peity-donut"
                                  data-peity='{ "fill": ["#886ab5", "var(--bs-border-color)"], "innerRadius": 15, "radius": 18 }'>6/7</span>
                            <span class="peity-donut"
                                  data-peity='{ "fill": ["#5d5d5d", "var(--bs-border-color)"], "innerRadius": 13, "radius": 15 }'>7/7</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/peitycharts.js'])
@endsection
