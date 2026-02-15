@extends('layouts.vertical', ['pageTitle' => 'Slack Configuration'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Slack', 'pageSubTitle1' => 'Configuration'])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Slack Configuration</div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    Slack integration configuration will go here. This includes webhook URLs, channel mappings, and notification settings.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
