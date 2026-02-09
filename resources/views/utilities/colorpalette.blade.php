@extends('layouts.vertical', ['pageTitle' => 'Color Palette'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Color Palette', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'System Utilities', 'pageSubText' => 'Comes with 60 unique color pallets, that uses one prime to generate all pallets.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="panel" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Impressionistic <span class="fw-300"><i>colors</i></span>
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
                        <div class="row">
                            <div class="col col-xl-12">
                                <div class="table-responsive">
                                    <table class="table table-hover table-clean table-layout-fixed">
                                        <thead>
                                        <tr>
                                            <th style="width: 150px">State</th>
                                            <th>Preview</th>
                                            <th>Postfix</th>
                                            <th>Button</th>
                                            <th>Text</th>
                                            <th>Background</th>
                                            <th>Alerts</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>Success</td>
                                            <td>
                                                <span class="width-1 height-1 rounded-circle d-block bg-success"></span>
                                            </td>
                                            <td><code>*-success</code></td>
                                            <td><code>btn-success</code></td>
                                            <td><code>color-success</code></td>
                                            <td><code>bg-success</code></td>
                                            <td><code>alert-success</code></td>
                                        </tr>
                                        <tr>
                                            <td>Warning</td>
                                            <td>
                                                <span class="width-1 height-1 rounded-circle d-block bg-warning"></span>
                                            </td>
                                            <td><code>*-warning</code></td>
                                            <td><code>btn-warning</code></td>
                                            <td><code>color-warning</code></td>
                                            <td><code>bg-warning</code></td>
                                            <td><code>alert-warning</code></td>
                                        </tr>
                                        <tr>
                                            <td>Danger</td>
                                            <td>
                                                <span class="width-1 height-1 rounded-circle d-block bg-danger"></span>
                                            </td>
                                            <td><code>*-danger</code></td>
                                            <td><code>btn-danger</code></td>
                                            <td><code>color-danger</code></td>
                                            <td><code>bg-danger</code></td>
                                            <td><code>alert-danger</code></td>
                                        </tr>
                                        <tr>
                                            <td>Info</td>
                                            <td>
                                                <span class="width-1 height-1 rounded-circle d-block bg-info"></span>
                                            </td>
                                            <td><code>*-info</code></td>
                                            <td><code>btn-info</code></td>
                                            <td><code>color-info</code></td>
                                            <td><code>bg-info</code></td>
                                            <td><code>alert-info</code></td>
                                        </tr>
                                        <tr>
                                            <td>Primary</td>
                                            <td>
                                                <span class="width-1 height-1 rounded-circle d-block bg-primary"></span>
                                            </td>
                                            <td><code>*-primary</code></td>
                                            <td><code>btn-primary</code></td>
                                            <td><code>color-primary</code></td>
                                            <td><code>bg-primary</code></td>
                                            <td><code>alert-primary</code></td>
                                        </tr>
                                        <tr>
                                            <td>Secondary</td>
                                            <td>
                                                <span
                                                    class="width-1 height-1 rounded-circle d-block bg-secondary"></span>
                                            </td>
                                            <td><code>*-secondary</code></td>
                                            <td><code>btn-secondary</code></td>
                                            <td><code>color-secondary</code></td>
                                            <td><code>bg-secondary</code></td>
                                            <td><code>alert-secondary</code></td>
                                        </tr>
                                        <tr>
                                            <td>Fusion</td>
                                            <td>
                                                <span
                                                    class="width-1 height-1 rounded-circle d-block bg-fusion-500"></span>
                                            </td>
                                            <td><code>*-fusion</code></td>
                                            <td><code>btn-fusion</code></td>
                                            <td><code>color-fusion</code></td>
                                            <td><code>bg-fusion</code></td>
                                            <td><code>alert-fusion</code></td>
                                        </tr>
                                        <tr>
                                            <td>Dark</td>
                                            <td>
                                                <span class="width-1 height-1 rounded-circle d-block bg-dark"></span>
                                            </td>
                                            <td><code>*-dark</code></td>
                                            <td><code>btn-dark</code></td>
                                            <td><code>color-dark</code></td>
                                            <td><code>bg-dark</code></td>
                                            <td><code>alert-dark</code></td>
                                        </tr>
                                        <tr>
                                            <td>Light</td>
                                            <td>
                                                <span
                                                    class="width-1 height-1 rounded-circle d-block bg-light border"></span>
                                            </td>
                                            <td><code>*-light</code></td>
                                            <td><code>btn-light</code></td>
                                            <td><code>color-white</code></td>
                                            <td><code>bg-light</code></td>
                                            <td><code>alert-light</code></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-xl-6">
            <div class="panel" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Success <span class="fw-300"><i>pallet</i></span>
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Success color pallet will be used for positive feedback and user assurance. We have selected
                            teal with a hint of green to stand out and cast the shadow of quality, healing, freshness
                            and peace
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-clean table-layout-fixed">
                                <thead>
                                <tr>
                                    <th>Background</th>
                                    <th>Postfix</th>
                                    <th>Example usage</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-success-50 width-2 height-2"></span></td>
                                    <td><code>*-success-50</code></td>
                                    <td><code>bg-success-50</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-success-100 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-success-100</code></td>
                                    <td><code>bg-success-100</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-success-200 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-success-200</code></td>
                                    <td><code>bg-success-200</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-success-300 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-success-300</code></td>
                                    <td><code>bg-success-300</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-success-400 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-success-400</code></td>
                                    <td><code>bg-success-400</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-success-500 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-success-500</code></td>
                                    <td><code>bg-success-500</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-success-600 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-success-600</code></td>
                                    <td><code>bg-success-600</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-success-700 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-success-700</code></td>
                                    <td><code>bg-success-700</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-success-800 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-success-800</code></td>
                                    <td><code>bg-success-800</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-success-900 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-success-900</code></td>
                                    <td><code>bg-success-900</code></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Warning <span class="fw-300"><i>pallet</i></span>
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Warning color pallet lets users know to be catious and careful. It can envoke the feeling of
                            curiosity, confidence, sociability, and bravery
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-clean table-layout-fixed">
                                <thead>
                                <tr>
                                    <th>Background</th>
                                    <th>Postfix</th>
                                    <th>Example usage</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-warning-50 width-2 height-2"></span></td>
                                    <td><code>*-warning-50</code></td>
                                    <td><code>bg-warning-50</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-warning-100 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-warning-100</code></td>
                                    <td><code>bg-warning-100</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-warning-200 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-warning-200</code></td>
                                    <td><code>bg-warning-200</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-warning-300 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-warning-300</code></td>
                                    <td><code>bg-warning-300</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-warning-400 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-warning-400</code></td>
                                    <td><code>bg-warning-400</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-warning-500 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-warning-500</code></td>
                                    <td><code>bg-warning-500</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-warning-600 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-warning-600</code></td>
                                    <td><code>bg-warning-600</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-warning-700 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-warning-700</code></td>
                                    <td><code>bg-warning-700</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-warning-800 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-warning-800</code></td>
                                    <td><code>bg-warning-800</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-warning-900 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-warning-900</code></td>
                                    <td><code>bg-warning-900</code></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Danger <span class="fw-300"><i>pallet</i></span>
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            This color as an attention getter. Use this color as less as possible to have maximum
                            impact. This color is connected to ecitement, love, strength, and energy
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-clean table-layout-fixed">
                                <thead>
                                <tr>
                                    <th>Background</th>
                                    <th>Postfix</th>
                                    <th>Example usage</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-danger-50 width-2 height-2"></span></td>
                                    <td><code>*-danger-50</code></td>
                                    <td><code>bg-danger-50</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-danger-100 width-2 height-2"></span></td>
                                    <td><code>*-danger-100</code></td>
                                    <td><code>bg-danger-100</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-danger-200 width-2 height-2"></span></td>
                                    <td><code>*-danger-200</code></td>
                                    <td><code>bg-danger-200</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-danger-300 width-2 height-2"></span></td>
                                    <td><code>*-danger-300</code></td>
                                    <td><code>bg-danger-300</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-danger-400 width-2 height-2"></span></td>
                                    <td><code>*-danger-400</code></td>
                                    <td><code>bg-danger-400</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-danger-500 width-2 height-2"></span></td>
                                    <td><code>*-danger-500</code></td>
                                    <td><code>bg-danger-500</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-danger-600 width-2 height-2"></span></td>
                                    <td><code>*-danger-600</code></td>
                                    <td><code>bg-danger-600</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-danger-700 width-2 height-2"></span></td>
                                    <td><code>*-danger-700</code></td>
                                    <td><code>bg-danger-700</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-danger-800 width-2 height-2"></span></td>
                                    <td><code>*-danger-800</code></td>
                                    <td><code>bg-danger-800</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-danger-900 width-2 height-2"></span></td>
                                    <td><code>*-danger-900</code></td>
                                    <td><code>bg-danger-900</code></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Secondary <span class="fw-300"><i>pallet</i></span>
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Bootstrap uses this color for secondary elements. It is a neutral color that is used to
                            balance the primary colors.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-clean table-layout-fixed">
                                <thead>
                                <tr>
                                    <th>Background</th>
                                    <th>Postfix</th>
                                    <th>Example usage</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-secondary-50 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-secondary-50</code></td>
                                    <td><code>bg-secondary-50</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-secondary-100 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-secondary-100</code></td>
                                    <td><code>bg-secondary-100</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-secondary-200 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-secondary-200</code></td>
                                    <td><code>bg-secondary-200</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-secondary-300 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-secondary-300</code></td>
                                    <td><code>bg-secondary-300</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-secondary-400 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-secondary-400</code></td>
                                    <td><code>bg-secondary-400</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-secondary-500 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-secondary-500</code></td>
                                    <td><code>bg-secondary-500</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-secondary-600 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-secondary-600</code></td>
                                    <td><code>bg-secondary-600</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-secondary-700 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-secondary-700</code></td>
                                    <td><code>bg-secondary-700</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-secondary-800 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-secondary-800</code></td>
                                    <td><code>bg-secondary-800</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-secondary-900 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-secondary-900</code></td>
                                    <td><code>bg-secondary-900</code></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-xl-6">
            <div class="panel" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Info <span class="fw-300"><i>pallet</i></span>
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            This particular pallet helps you build trust, it has the ability to project competence,
                            peace, loyalty and strength to your users
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-clean table-layout-fixed">
                                <thead>
                                <tr>
                                    <th>Background</th>
                                    <th>Postfix</th>
                                    <th>Example usage</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-info-50 width-2 height-2"></span></td>
                                    <td><code>*-info-50</code></td>
                                    <td><code>bg-info-50</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-info-100 width-2 height-2"></span></td>
                                    <td><code>*-info-100</code></td>
                                    <td><code>bg-info-100</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-info-200 width-2 height-2"></span></td>
                                    <td><code>*-info-200</code></td>
                                    <td><code>bg-info-200</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-info-300 width-2 height-2"></span></td>
                                    <td><code>*-info-300</code></td>
                                    <td><code>bg-info-300</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-info-400 width-2 height-2"></span></td>
                                    <td><code>*-info-400</code></td>
                                    <td><code>bg-info-400</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-info-500 width-2 height-2"></span></td>
                                    <td><code>*-info-500</code></td>
                                    <td><code>bg-info-500</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-info-600 width-2 height-2"></span></td>
                                    <td><code>*-info-600</code></td>
                                    <td><code>bg-info-600</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-info-700 width-2 height-2"></span></td>
                                    <td><code>*-info-700</code></td>
                                    <td><code>bg-info-700</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-info-800 width-2 height-2"></span></td>
                                    <td><code>*-info-800</code></td>
                                    <td><code>bg-info-800</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-info-900 width-2 height-2"></span></td>
                                    <td><code>*-info-900</code></td>
                                    <td><code>bg-info-900</code></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        Primary <span class="fw-300"><i>pallet</i></span>
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            This is the primary color of the app, and is widely used in almost all of the elements. The
                            color shows ambition, sprituality, royalty and luxury
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-clean table-layout-fixed">
                                <thead>
                                <tr>
                                    <th>Background</th>
                                    <th>Postfix</th>
                                    <th>Example usage</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-primary-50 width-2 height-2"></span></td>
                                    <td><code>*-primary-50</code></td>
                                    <td><code>bg-primary-50</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-primary-100 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-primary-100</code></td>
                                    <td><code>bg-primary-100</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-primary-200 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-primary-200</code></td>
                                    <td><code>bg-primary-200</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-primary-300 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-primary-300</code></td>
                                    <td><code>bg-primary-300</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-primary-400 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-primary-400</code></td>
                                    <td><code>bg-primary-400</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-primary-500 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-primary-500</code></td>
                                    <td><code>bg-primary-500</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-primary-600 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-primary-600</code></td>
                                    <td><code>bg-primary-600</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-primary-700 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-primary-700</code></td>
                                    <td><code>bg-primary-700</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-primary-800 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-primary-800</code></td>
                                    <td><code>bg-primary-800</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-primary-900 width-2 height-2"></span>
                                    </td>
                                    <td><code>*-primary-900</code></td>
                                    <td><code>bg-primary-900</code></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="panel-8">
                <div class="panel-hdr">
                    <h2>
                        Fusion <span class="fw-300"><i>pallet</i></span>
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Fusion is a alternation of primary pallet. It is dramatic and bold. It has class and a sense
                            of formality combined with security.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-clean table-layout-fixed">
                                <thead>
                                <tr>
                                    <th>Background</th>
                                    <th>Postfix</th>
                                    <th>Example usage</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-fusion-50 width-2 height-2"></span></td>
                                    <td><code>*-fusion-50</code></td>
                                    <td><code>bg-fusion-50</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-fusion-100 width-2 height-2"></span></td>
                                    <td><code>*-fusion-100</code></td>
                                    <td><code>bg-fusion-100</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-fusion-200 width-2 height-2"></span></td>
                                    <td><code>*-fusion-200</code></td>
                                    <td><code>bg-fusion-200</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-fusion-300 width-2 height-2"></span></td>
                                    <td><code>*-fusion-300</code></td>
                                    <td><code>bg-fusion-300</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-fusion-400 width-2 height-2"></span></td>
                                    <td><code>*-fusion-400</code></td>
                                    <td><code>bg-fusion-400</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-fusion-500 width-2 height-2"></span></td>
                                    <td><code>*-fusion-500</code></td>
                                    <td><code>bg-fusion-500</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-fusion-600 width-2 height-2"></span></td>
                                    <td><code>*-fusion-600</code></td>
                                    <td><code>bg-fusion-600</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-fusion-700 width-2 height-2"></span></td>
                                    <td><code>*-fusion-700</code></td>
                                    <td><code>bg-fusion-700</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-fusion-800 width-2 height-2"></span></td>
                                    <td><code>*-fusion-800</code></td>
                                    <td><code>bg-fusion-800</code></td>
                                </tr>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-fusion-900 width-2 height-2"></span></td>
                                    <td><code>*-fusion-900</code></td>
                                    <td><code>bg-fusion-900</code></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" id="panel-9">
                <div class="panel-hdr">
                    <h2>
                        Light <span class="fw-300"><i>pallet</i></span>
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Bootstrap uses this color for light elements. It is a light color that is used to balance
                            the primary colors.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-clean table-layout-fixed">
                                <thead>
                                <tr>
                                    <th>Background</th>
                                    <th>Postfix</th>
                                    <th>Example usage</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><span class="rounded-circle d-block bg-light-50 width-2 height-2 border"></span>
                                    </td>
                                    <td><code>*-light-50</code></td>
                                    <td><code>bg-light-50</code></td>
                                </tr>
                                <tr>
                                    <td><span
                                            class="rounded-circle d-block bg-light-100 width-2 height-2 border"></span>
                                    </td>
                                    <td><code>*-light-100</code></td>
                                    <td><code>bg-light-100</code></td>
                                </tr>
                                <tr>
                                    <td><span
                                            class="rounded-circle d-block bg-light-200 width-2 height-2 border"></span>
                                    </td>
                                    <td><code>*-light-200</code></td>
                                    <td><code>bg-light-200</code></td>
                                </tr>
                                <tr>
                                    <td><span
                                            class="rounded-circle d-block bg-light-300 width-2 height-2 border"></span>
                                    </td>
                                    <td><code>*-light-300</code></td>
                                    <td><code>bg-light-300</code></td>
                                </tr>
                                <tr>
                                    <td><span
                                            class="rounded-circle d-block bg-light-400 width-2 height-2 border"></span>
                                    </td>
                                    <td><code>*-light-400</code></td>
                                    <td><code>bg-light-400</code></td>
                                </tr>
                                <tr>
                                    <td><span
                                            class="rounded-circle d-block bg-light-500 width-2 height-2 border"></span>
                                    </td>
                                    <td><code>*-light-500</code></td>
                                    <td><code>bg-light-500</code></td>
                                </tr>
                                <tr>
                                    <td><span
                                            class="rounded-circle d-block bg-light-600 width-2 height-2 border"></span>
                                    </td>
                                    <td><code>*-light-600</code></td>
                                    <td><code>bg-light-600</code></td>
                                </tr>
                                <tr>
                                    <td><span
                                            class="rounded-circle d-block bg-light-700 width-2 height-2 border"></span>
                                    </td>
                                    <td><code>*-light-700</code></td>
                                    <td><code>bg-light-700</code></td>
                                </tr>
                                <tr>
                                    <td><span
                                            class="rounded-circle d-block bg-light-800 width-2 height-2 border"></span>
                                    </td>
                                    <td><code>*-light-800</code></td>
                                    <td><code>bg-light-800</code></td>
                                </tr>
                                <tr>
                                    <td><span
                                            class="rounded-circle d-block bg-light-900 width-2 height-2 border"></span>
                                    </td>
                                    <td><code>*-light-900</code></td>
                                    <td><code>bg-light-900</code></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
