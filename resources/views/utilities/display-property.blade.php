@extends('layouts.vertical', ['pageTitle' => 'Display Property'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle' => 'Display Property', 'pageSubTitle1' => 'Design', 'pageSubTitle2' => 'System Utilities', 'pageSubText' => 'Control the display behavior of elements using our display utilities with support for common values as well as responsive breakpoints.'])
@endsection

@section('content')
    <div class="info-container">
        <p class="mb-0">Change the value of the display property with our responsive display utility classes. We support
            all common values while adding responsive variations for each breakpoint. You can also use the <a
                href="{{ route('second', ['utilities', 'visibility-generator']) }}">Visibility Generator Page</a> to automate the CSS generation.</p>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Displaying <span class="fw-300"><i>elements</i></span>
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
                            <p>For faster mobile-friendly development, use responsive display classes for showing and
                                hiding elements by device. Avoid creating entirely different versions of the same site,
                                instead hide elements responsively for each screen size.</p>
                            <p>To hide elements simply use the <code>.d-none</code> class or one of the <code>.d-{sm,md,lg,xl,xxl}-none</code>
                                classes for any responsive screen variation.</p>
                            <p>To show an element only on a given interval of screen sizes you can combine one <code>.d-*-none</code>
                                class
                                with a <code>.d-*-*</code> class, for example <code>.d-none .d-md-block .d-xl-none
                                    .d-xxl-none</code> will hide
                                the element for all screen sizes except on medium and large devices.</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-layout-fixed table-bordered mb-g">
                                <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Screen size</th>
                                    <th>Class</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Hidden on all</td>
                                    <td>*.*</td>
                                    <td><code>.d-none</code></td>
                                </tr>
                                <tr>
                                    <td>Hidden only on xs</td>
                                    <td>576px</td>
                                    <td><code>.d-none .d-sm-block</code></td>
                                </tr>
                                <tr>
                                    <td>Hidden only on sm</td>
                                    <td>576px</td>
                                    <td><code>.d-sm-none .d-md-block</code></td>
                                </tr>
                                <tr>
                                    <td>Hidden only on md</td>
                                    <td>768px</td>
                                    <td><code>.d-md-none .d-lg-block</code></td>
                                </tr>
                                <tr>
                                    <td>Hidden only on lg</td>
                                    <td>1024px</td>
                                    <td><code>.d-lg-none .d-xl-block</code></td>
                                </tr>
                                <tr>
                                    <td>Hidden only on xl</td>
                                    <td>1280px</td>
                                    <td><code>.d-xl-none .d-xxl-block</code></td>
                                </tr>
                                <tr>
                                    <td>Hidden only on xxl</td>
                                    <td>1400px</td>
                                    <td><code>.d-xxl-none</code></td>
                                </tr>
                                <tr>
                                    <td>Hidden only on xxxl</td>
                                    <td>1600px</td>
                                    <td><code>.d-xxxl-none</code></td>
                                </tr>
                                <tr>
                                    <td>Visible on all</td>
                                    <td>*.*</td>
                                    <td><code>.d-block</code></td>
                                </tr>
                                <tr>
                                    <td>Visible only on xs</td>
                                    <td>576px</td>
                                    <td><code>.d-block .d-sm-none</code></td>
                                </tr>
                                <tr>
                                    <td>Visible only on sm</td>
                                    <td>576px</td>
                                    <td><code>.d-none .d-sm-block .d-md-none</code></td>
                                </tr>
                                <tr>
                                    <td>Visible only on md</td>
                                    <td>768px</td>
                                    <td><code>.d-none .d-md-block .d-lg-none</code></td>
                                </tr>
                                <tr>
                                    <td>Visible only on lg</td>
                                    <td>1024px</td>
                                    <td><code>.d-none .d-lg-block .d-xl-none</code></td>
                                </tr>
                                <tr>
                                    <td>Visible only on xl</td>
                                    <td>1280px</td>
                                    <td><code>.d-none .d-xl-block .d-xxl-none</code></td>
                                </tr>
                                <tr>
                                    <td>Visible only on xxl</td>
                                    <td>1400px</td>
                                    <td><code>.d-none .d-xxl-block</code></td>
                                </tr>
                                <tr>
                                    <td>Visible only on xxxl</td>
                                    <td>1600px</td>
                                    <td><code>.d-none .d-xxxl-block</code></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="panel-tag">
                            <p>Change the <code>display</code> value of elements when printing with our print display
                                utility classes. Includes support for the same <code>display</code> values as our
                                responsive <code>.d-*</code> utilities.</p>
                        </div>
                        <ul>
                            <li><code>.d-print-none</code></li>
                            <li><code>.d-print-inline</code></li>
                            <li><code>.d-print-inline-block</code></li>
                            <li><code>.d-print-block</code></li>
                            <li><code>.d-print-grid</code></li>
                            <li><code>.d-print-table</code></li>
                            <li><code>.d-print-table-row</code></li>
                            <li><code>.d-print-table-cell</code></li>
                            <li><code>.d-print-flex</code></li>
                            <li><code>.d-print-inline-flex</code></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
