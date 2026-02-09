@extends('layouts.vertical', ['pageTitle' => 'SmartTables'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'JSON Data Source', 'pageSubTitle1' => 'Data Visualization', 'pageSubTitle2'=> 'Smart Tables', 'pageSubText' => 'Loading the table with External JSON data source with over 1000 records.'])
@endsection

@section('content')

    <div class="info-container">
        The <code>render</code> function allows you to manipulate data, apply <strong>custom styling</strong>,
        implement <strong>logic</strong>, and add various functionalities. For example, you can <strong>hover over a
            product to see its image</strong>, style emails based on mail providers, or use <strong>data
            matching</strong>
        to encode badges with colors.
    </div>
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-striped table-bordered table-hover d-none" id="myTable"></table>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/smarttablesjsonsource.js'])
@endsection
