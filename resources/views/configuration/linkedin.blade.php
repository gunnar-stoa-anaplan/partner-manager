@extends('layouts.vertical', ['pageTitle' => 'LinkedIn Configuration'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'LinkedIn', 'pageSubTitle1' => 'Configuration'])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">LinkedIn Configuration</div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    LinkedIn integration configuration will go here. This includes API keys, profile matching, and activity tracking settings.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
