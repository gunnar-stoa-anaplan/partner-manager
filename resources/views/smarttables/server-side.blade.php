@extends('layouts.vertical', ['pageTitle' => 'SmartTables: Server-Side Mode'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'SmartTables: Server-Side Mode', 'pageSubTitle1' => 'Data Visualization', 'pageSubTitle2'=> 'Smart Tables', 'pageSubText' => 'Loading the table with server side data.'])
@endsection

@section('content')
    <div class="info-container">
        <strong>Here</strong> we use <code>mock-server.php</code> to load data via AJAX, data resets every 24 hours.
        Configure with:
        <code>
            data: { type: 'ajax', source: 'API_URL', serverSide: true, method: 'GET', cacheDuration: 300000 }
        </code>
        Processing happens server-side; client only renders. <strong>Searched, paging, and sorting</strong> is cached
        for 5 minutes.
    </div>

    <div class="row">
        <div class="col-lg-12">
            <table id="demo-table"></table>
        </div>
        <div class="col-lg-12">

        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/smarttablesserverside.js'])
@endsection
