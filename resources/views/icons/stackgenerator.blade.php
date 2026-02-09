@extends('layouts.vertical', ['pageTitle' => 'Stack Generator'])

@section('css')
@endsection


@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Stack Generator', 'pageSubTitle1' => 'Icons', 'pageSubTitle2'=> 'Iconography', 'pageSubText' => 'Create custom, unqiue, layered icons from various libraries for projects, offering flexibility and scalability.'])
@endsection

@section('content')
    <div
        class="main-content icon-zoom-4 grid-background position-relative overflow-hidden sortable-inactive d-flex flex-column border"
        id="stackgenerator-container">
        <div class="position-absolute d-flex flex-row justify-content-between top-0 start-0 end-0 p-3">
            <div class="d-flex flex-row gap-2">
                <button class="btn btn-icon btn-secondary" data-action="toggle-swap"
                        data-target="#stackgenerator-container"
                        data-toggleclass="grid-background-dark" type="button">
                    <svg class="sa-icon sa-icon-2x sa-icon-light">
                        <use href="/icons/sprite.svg#sun"></use>
                    </svg>
                </button>
                <div aria-label="Default button group" class="btn-group" role="group">
                    <button class="btn btn-icon btn-secondary" id="zoomInBtn" type="button">
                        <svg class="sa-icon sa-icon-2x sa-icon-light">
                            <use href="/icons/sprite.svg#plus-circle"></use>
                        </svg>
                    </button>
                    <button class="btn btn-icon btn-secondary" id="zoomOutBtn" type="button">
                        <svg class="sa-icon sa-icon-2x sa-icon-light">
                            <use href="/icons/sprite.svg#minus-circle"></use>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="d-flex flex-row gap-2"></div>
        </div>
        <div class="position-absolute d-flex flex-row justify-content-between bottom-0 start-0 end-0 p-3">
            <div class="d-flex flex-row gap-2">
                <button class="btn btn-sm btn-secondary shadow-0" id="copy-icon" type="button">
                    Copy Icon
                </button>
                <button class="btn btn-sm btn-danger shadow-0" id="reset-layers" type="button">
                    Reset
                </button>
                <button class="btn btn-sm btn-success shadow-0" id="save-icon" type="button">
                    Save
                </button>
            </div>
        </div>
        <!-- Stack Control Panel -->
        <div class="panel border-secondary border-3 border-opacity-50 rounded overflow-hidden rounded"
             id="stack-control">
            <div class="panel-hdr text-white ps-3 pe-2 rounded-top-0 border-bottom">
                <h2 class="ps-0"> Controls</h2>
                <div class="panel-toolbar pe-0">
                    <button class="btn btn-xs btn-outline-secondary shadow-0 align-self-center"
                            data-bs-target="#example-modal-backdrop-transparent" data-bs-toggle="modal" id="add-layer"
                            type="button">
                        Add Layer
                    </button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content p-0">
                    <!-- Layer 1 -->
                    <div class="stack-layers d-flex flex-row border-bottom position-relative">
                        <div class="stack-icon-preview flex-shrink-0 d-flex align-items-center justify-content-center">
                            <svg class="sa-icon sa-icon-3x">
                                <use href="/icons/sprite.svg#sun"></use>
                            </svg>
                        </div>
                        <div class="icon-settings flex-grow-1 border-left px-2 py-1">
                            fal fa-car
                            <span class="badge badge-icon">
                                Layer 1
                            </span>
                        </div>
                    </div>
                    <!-- Layer 2 -->
                    <div class="stack-layers d-flex flex-row border-bottom position-relative">
                        <div class="stack-icon-preview flex-shrink-0 d-flex align-items-center justify-content-center">
                            <i class="sa base-2 sa-icon-3x"></i>
                        </div>
                        <div class="icon-settings flex-grow-1 border-left px-2 py-1">
                            sa base-2
                            <span class="badge badge-icon">
                                Layer 2
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-grow-1 d-flex align-items-center justify-content-center" id="stack-playground"
             style="min-height: 684px;">
            <div id="my-icon">
                <div class="icon-layers">
                    <i class="sa sa-base-2 stack-3x text-success"></i>
                </div>
                <div class="icon-layers">
                    <i class="sa sa-base-10 stack-1x text-dark"></i>
                </div>
                <div class="icon-layers">
                    <svg class="sa-icon sa-bold stack-1x sa-icon-danger">
                        <use href="/icons/sprite.svg#alert-triangle"></use>
                    </svg>
                </div>
                <div class="icon-layers">
                    <svg class="sa-icon sa-bold sa-icon-warning stack-1x">
                        <use href="/icons/sprite.svg#sun"></use>
                    </svg>
                </div>
            </div>
            <div class="empty-msg d-none text-center p-3 body-bg border border-2 rounded text-muted">
                <h4>To get started</h4>
                <p class="mb-0"><strong>Add Layer</strong> to your icon stack from <strong>Controls</strong></p>
            </div>
        </div>
    </div>
    <!-- modal -->
    <div aria-hidden="true" class="modal fade modal-backdrop-transparent" id="example-modal-backdrop-transparent"
         style="display: none;" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select an Icon</h5>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Search -->
                    <div class="input-group p-3">
                        <input autocomplete="off" class="form-control form-control-lg" id="searchIcons"
                               placeholder="Search icons..." type="text"/>
                        <select aria-label="Filter Icons" class="form-select pe-2 ps-3" id="iconSetSelect"
                                style="max-width: 130px;">
                            <option selected="" value="svg">SVG</option>
                            <option value="base">Base</option>
                            <option value="sa">SmartAdmin</option>
                            <option value="fal">Light</option>
                            <option value="far">Regular</option>
                            <option value="fas">Solid</option>
                            <option value="fad">Duotone</option>
                        </select>
                    </div>
                    <!-- Results Count and Search History -->
                    <div class="row mb-3 hidden-sm">
                        <div class="col-sm-8">
                            <div class="search-history text-sm-start d-flex flex-wrap gap-2 ps-3" id="searchHistory">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <p class="results-count text-muted mb-0 text-sm-end ps-3 ps-sm-0 pe-lg-3"></p>
                        </div>
                    </div>
                    <!-- Suggestions -->
                    <div class="px-3" id="suggestions"></div>
                    <!-- Icons List -->
                    <ul class="stack-demo list-unstyled d-flex flex-wrap justify-content-evenly gap-2 py-1 px-2 mb-0"
                        id="iconList" style="max-height: 350px;"></ul>
                    <!-- No Results Message -->
                    <div class="no-results-msg pt-3">
                        <h5 class="mb-4 px-3">
                            No icons found. Try searching with different keywords
                        </h5>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary waves-effect waves-themed" data-bs-dismiss="modal"
                            type="button">Close
                    </button>
                    <button class="btn btn-primary waves-effect waves-themed" type="button">Select Icon</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/stackgenerator.js'])
@endsection
