@extends('layouts.vertical', ['pageTitle' => 'ST: Fuzzy Matching'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Fuzzy Matching', 'pageSubTitle1' => 'Data Visualization', 'pageSubTitle2'=> 'Smart Tables', 'pageSubText' => "Fuzzy Matching allows matching text that isn't an exact match, making search more forgiving and intuitive."])
@endsection

@section('content')
    <div class="info-container">
        <p class="mb-0">Adjust the settings below to see how different fuzzy matching parameters affect search results.
            After configuring your settings, click <strong>Build Table</strong> to initialize the table with those
            settings.
        
        </p>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-g">
                <div class="card-body">
                    <div class="row row-grid g-0 border-faded mb-g">
                        <div class="col-sm-6 p-3">
                            <div class="mb-1">
                                <label class="form-label" for="threshold">
                                    <strong>Threshold:</strong> <span id="thresholdValue"></span>
                                    <small class="d-block text-muted">0 = Exact match only | 1 = Maximum
                                        fuzziness</small>
                                </label>
                                <input class="form-range" id="threshold" type="range"/>
                            </div>
                        </div>
                        <div class="col-sm-6 p-3">
                            <div class="mb-1">
                                <label class="form-label" for="minMatchLength">
                                    <strong>Minimum Match Length:</strong> <span id="minMatchLengthValue"></span>
                                    <small class="d-block text-muted">Minimum characters required for a match</small>
                                </label>
                                <input class="form-range" id="minMatchLength" type="range"/>
                            </div>
                        </div>
                        <div class="col-sm-6 p-3">
                            <div class="mb-1">
                                <label class="form-label" for="multiWordThreshold">
                                    <strong>Multi-Word Threshold:</strong> <span id="multiWordThresholdValue"></span>
                                    <small class="d-block text-muted">0 = Match any word | 1 = Match all words</small>
                                </label>
                                <input class="form-range" id="multiWordThreshold" type="range"/>
                            </div>
                        </div>
                        <div class="col-sm-6 p-3">
                            <div class="mb-1">
                                <label class="form-label" for="maxDistance">
                                    <strong>Max Distance:</strong> <span id="maxDistanceValue"></span>
                                    <small class="d-block text-muted">Maximum character mismatches allowed (typo
                                        tolerance)</small>
                                </label>
                                <input class="form-range" id="maxDistance" type="range"/>
                            </div>
                        </div>
                    </div>
                    <div class="app-fob-showcase-text mb-g" data-prefix="Search Behavior">
                        <div class="mb-2" id="description"></div>
                        <div class="text-muted fst-italic small" id="searchExample"></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <button class="btn btn-secondary me-auto" id="resetSettings" type="button">
                            Balanced <span class="d-none d-sm-inline">Settings</span>
                        </button>
                        <button class="btn btn-danger" id="destroyTable" type="button">
                            Destroy <span class="d-none d-sm-inline">Table</span>
                        </button>
                        <button class="btn btn-outline-success" id="buildTable" type="button">
                            Build <span class="d-none d-sm-inline">Table</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="d-none mb-5" id="tableContainer">
                <div class="info-container mb-g">
                    <h5 class="card-title">Try the Search</h5>
                    <p class="mb-0">Use the search box below to test how your fuzzy search settings perform. Try partial
                        words, typos, or multi-word searches.</p>
                </div>
                <table class="table table-striped table-bordered table-hover" id="myTable"></table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/smarttablesfuzzymatching.js'])
@endsection
