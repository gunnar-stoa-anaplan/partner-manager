@extends('layouts.vertical', ['pageTitle' => 'SmartAdmin Icons'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'SmartAdmin Icons', 'pageSubTitle1' => 'Icons', 'pageSubTitle2'=> 'Iconography', 'pageSubText' => 'An extreamly lightweight icon set, to meet your basic needs.'])
@endsection

@section('content')
    <div class="card">
        <div class="card-body" style="min-height: calc(100vh - 340px);">
            <!-- Search -->
            <div class="input-group mb-3">
                <input autocomplete="off" class="form-control form-control-lg" id="searchIcons"
                       placeholder="Search icons..."
                       type="text"/>
                <select aria-label="Filter Icons" class="form-select pe-2 ps-3" id="iconSetSelect"
                        style="max-width: 140px;">
                    <option selected="" value="sa">SmartAdmin</option>
                    <option value="base">Base</option>
                </select>
                <span class="input-group-text px-3">
                    <i class="sa sa-magnifier"></i>
                </span>
            </div>
            <!-- Results Count and Search History -->
            <div class="row mb-3">
                <div class="search-history col-sm-6 text-sm-start d-flex flex-wrap gap-2" id="searchHistory"></div>
                <p class="results-count text-muted mb-0 col-sm-6 text-sm-end"></p>
            </div>
            <!-- Suggestions -->
            <div id="suggestions"></div>
            <!-- Icons List -->
            <ul class="list-unstyled row g-4" id="iconList"></ul>
            <!-- No Results Message -->
            <div class="no-results-msg pt-3">
                <h5 class="mb-4">
                    No icons found. Try searching with different keywords
                </h5>
            </div>
        </div>
    </div>
    <!-- Toast -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div aria-atomic="true" aria-live="assertive" class="toast hide align-items-center border-0 py-2 px-3"
             id="liveToast" role="alert" style="z-index: 11">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center justify-content-center"></div>
                <button aria-label="Close" class="btn btn-system ms-auto" data-bs-dismiss="toast" type="button">
                    <svg class="sa-icon">
                        <use href="/icons/sprite.svg#x"></use>
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/smartadminicons.js'])
@endsection
