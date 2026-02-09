@extends('layouts.vertical', ['pageTitle' => 'SmartTables: Manage Records'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Manage Records', 'pageSubTitle1' => 'Smart Tables', 'pageSubTitle2'=> 'Data Visualization', 'pageSubText' => 'Functionality for managing records with full CRUD (Create, Read, Update, Delete) operations. This allows you to build interactive data management interfaces with minimal effort.'])
@endsection

@section('content')
    <div class="info-container">
        Turn on <strong>Edit, Add, and Delete</strong> feature by using <code>addRecord: true</code> and use hooks
        create
        custom form elements.
    </div>
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-striped table-bordered table-hover d-none" id="clientTable">
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/smarttablesmanagerecords.js'])
@endsection
