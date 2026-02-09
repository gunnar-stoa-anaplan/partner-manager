@extends('layouts.vertical', ['pageTitle' => 'Stack Library'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Stack Library', 'pageSubTitle1' => 'Icons', 'pageSubTitle2'=> 'Iconography', 'pageSubText' => 'Your saved stacked icons collection.'])
@endsection

@section('content')
    <div class="card">
        <div class="card-body" style="min-height: calc(100vh - 340px);">
            <div class="mb-3">
                <input autocomplete="off" class="form-control form-control-lg" id="searchIcons"
                       placeholder="Search saved icons..." type="text"/>
            </div>
            <div id="stack-library-container">
                <!-- Icons will be dynamically loaded here -->
                <ul class="list-unstyled row g-4" id="iconList"></ul>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/stacklibrary.js'])
@endsection
