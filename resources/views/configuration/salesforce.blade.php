@extends('layouts.vertical', ['pageTitle' => 'Salesforce Configuration'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Salesforce', 'pageSubTitle1' => 'Configuration'])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Salesforce Configuration</div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    Salesforce integration configuration will go here. This includes OAuth credentials, API endpoints, and sync settings.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
