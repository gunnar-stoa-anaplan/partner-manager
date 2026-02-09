@extends('layouts.vertical', ['pageTitle' => 'System Icons'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'System Icons', 'pageSubTitle1' => 'Icons', 'pageSubTitle2'=> 'Iconography', 'pageSubText' => 'Fully customizable system icons with a variety of weights, colors, and styles.'])
@endsection

@section('content')
    <div class="card">
        <div class="card-body" style="min-height: calc(100vh - 340px);">
            <!-- Search -->
            <div class="input-group mb-3">
                <input autocomplete="off" class="form-control form-control-lg" id="searchIcons"
                       placeholder="Search icons..."
                       type="text"/>
                <!-- Option Selector Instead of Dropdown -->
                <select aria-label="Filter Icons" class="form-select pe-2 ps-3" id="iconSetSelect"
                        style="max-width: 110px;">
                    <option selected="" value="sa-thin">Thin</option>
                    <option value="sa-regular">Normal</option>
                    <option value="sa-medium">Medium</option>
                    <option value="sa-bold">Bold</option>
                </select>
                <div class="input-group-text px-3">
                    <input class="form-check-input mt-0" id="fillIcons" type="checkbox"/>
                    <label class="form-check-label ps-2" for="fillIcons">No Fill</label>
                </div>
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
    @vite(['resources/scripts/pages/systemicons.js'])
@endsection
