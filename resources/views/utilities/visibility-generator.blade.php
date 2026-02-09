@extends('layouts.vertical', ['pageTitle' => 'Visibility Generator'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Visibility Generator', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'System Utilities', 'pageSubText' => 'Create responsive visibility classes for elements across screen sizes.'])
@endsection

@section('content')
    <div class="alert alert-info">
        <h5 class="alert-heading d-flex align-items-center"><i class="sa sa-info me-2"></i>About Visibility Classes</h5>
        <p class="mb-0">Bootstrap provides utility classes to control the visibility and display properties of elements
            based on screen size.
            This tool helps you generate the classes you need for responsive design. Combine display types with
            breakpoints to create
            elements that show or hide at specific screen sizes.</p>
    </div>
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="option-card">
                <div class="option-title">Visibility Mode</div>
                <div class="form-section">
                    <div class="form-check form-check-inline">
                        <input checked="" class="form-check-input" id="visibilityHidden" name="visibilityMode"
                               type="radio" value="hidden"/>
                        <label class="form-check-label" for="visibilityHidden">Hidden</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" id="visibilityVisible" name="visibilityMode" type="radio"
                               value="visible"/>
                        <label class="form-check-label" for="visibilityVisible">Visible</label>
                    </div>
                </div>
            </div>
            <div class="option-card" id="displayTypeSection">
                <div class="option-title">Display Type</div>
                <div class="form-section">
                    <div class="form-check form-check-inline">
                        <input checked="" class="form-check-input" id="block" name="displayType" type="radio"
                               value="block"/>
                        <label class="form-check-label" for="block">Block</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" id="flex" name="displayType" type="radio" value="flex"/>
                        <label class="form-check-label" for="flex">Flex</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" id="inline" name="displayType" type="radio" value="inline"/>
                        <label class="form-check-label" for="inline">Inline</label>
                    </div>
                </div>
            </div>
            <div class="option-card">
                <div class="option-title">
                    <span>Breakpoints</span>
                    <small class="float-end text-muted">Current: <span id="currentBreakpoint">...</span></small>
                </div>
                <div class="mb-2">
                    <div class="form-check">
                        <input class="form-check-input" id="all" type="checkbox" value="all"/>
                        <label class="form-check-label fw-bold" for="all">All Breakpoints</label>
                    </div>
                </div>
                <div class="bp-grid">
                    <div class="form-check">
                        <input class="form-check-input breakpoint-checkbox" id="xs" type="checkbox" value="xs"/>
                        <label class="form-check-label" for="xs">XS (≤576px)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input breakpoint-checkbox" id="sm" type="checkbox" value="sm"/>
                        <label class="form-check-label" for="sm">SM (≥576px)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input breakpoint-checkbox" id="md" type="checkbox" value="md"/>
                        <label class="form-check-label" for="md">MD (≥768px)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input breakpoint-checkbox" id="lg" type="checkbox" value="lg"/>
                        <label class="form-check-label" for="lg">LG (≥992px)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input breakpoint-checkbox" id="xl" type="checkbox" value="xl"/>
                        <label class="form-check-label" for="xl">XL (≥1200px)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input breakpoint-checkbox" id="xxl" type="checkbox" value="xxl"/>
                        <label class="form-check-label" for="xxl">XXL (≥1400px)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input breakpoint-checkbox" id="xxxl" type="checkbox" value="xxxl"/>
                        <label class="form-check-label" for="xxxl">XXXL (≥1600px)</label>
                    </div>
                </div>
                <button class="btn btn-primary w-100 mt-3" id="generateBtn" type="button">
                    <i class="fal fa-code me-1"></i> Generate Classes
                </button>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="output-section" id="output">
                <h5 class="mb-3 fw-bold">Generated Classes <small class="text-muted">(Copy these)</small></h5>
                <div class="class-list position-relative mb-3" id="classOutput">
                    <span
                        class="fst-italic text-muted">Select options and click "Generate Classes" to see the result.</span>
                    <button class="copy-button d-none" id="copyClasses" type="button">
                        <i class="fal fa-copy me-1"></i> Copy
                    </button>
                </div>
                <div class="app-fob-showcase-text" data-prefix="Preview">
                    <div class="mb-2" id="breakpointIndicators">
                        <span class="breakpoint-indicator" data-bp="xs">xs</span>
                        <span class="breakpoint-indicator" data-bp="sm">sm</span>
                        <span class="breakpoint-indicator" data-bp="md">md</span>
                        <span class="breakpoint-indicator" data-bp="lg">lg</span>
                        <span class="breakpoint-indicator" data-bp="xl">xl</span>
                        <span class="breakpoint-indicator" data-bp="xxl">xxl</span>
                    </div>
                    <div id="testDiv">
                        <div>This element will update based on your selections</div>
                    </div>
                </div>
                <div class="mt-4">
                    <a aria-expanded="false" class="btn btn-link btn-sm p-0" data-bs-toggle="collapse"
                       href="#cssEquivalent" role="button">
                        <i class="fal fa-code me-1"></i> Show CSS Equivalent
                    </a>
                    <div class="collapse mt-2" id="cssEquivalent">
                        <div class="position-relative">
                            <div class="css-output" id="cssOutput">
                                <span class="fst-italic text-muted">/* CSS will appear here */</span>
                            </div>
                            <button class="copy-button d-none" id="copyCSS" type="button">
                                <i class="fal fa-copy me-1"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite('resources/scripts/pages/visibilitygenerator.js')
@endsection
