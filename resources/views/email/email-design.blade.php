@extends('layouts.vertical', ['pageTitle' => 'Email Design'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Email Design', 'pageSubTitle1' => 'Layouts', 'pageSubTitle2'=> 'Authentication', 'pageSubText' => 'Variety of email designs for different scenarios.'])
@endsection

@section('content')
    <div class="info-container">
        <p class="mb-0">
            You can find your email templates inside the source directory <code>SmartAdmin/src/e-templates</code>
        </p>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="card m-auto border mb-g">
                <div class="card-header d-flex align-items-center justify-content-between">
                    Order Confirmation
                </div>
                <div class="card-body">
                    <img alt="Login Page" class="img-fluid inverse-color" src="/img/demo/pages/email-order.webp"/>
                </div>
            </div>
            <div class="card m-auto border mb-g">
                <div class="card-header d-flex align-items-center justify-content-between">
                    New Device Detected
                </div>
                <div class="card-body">
                    <img alt="New Device Detected" class="img-fluid inverse-color"
                         src="/img/demo/pages/new-device.webp"/>
                </div>
            </div>
            <div class="card m-auto border">
                <div class="card-header d-flex align-items-center justify-content-between">
                    Verify Email
                </div>
                <div class="card-body">
                    <img alt="Verify Email" class="img-fluid inverse-color" src="/img/demo/pages/verify-email.webp"/>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card m-auto border mb-g">
                <div class="card-header d-flex align-items-center justify-content-between">
                    Password Change Request
                </div>
                <div class="card-body">
                    <img alt="Password Change Request" class="img-fluid inverse-color"
                         src="/img/demo/pages/password-request.webp"/>
                </div>
            </div>
            <div class="card m-auto border">
                <div class="card-header d-flex align-items-center justify-content-between">
                    One Time Code
                </div>
                <div class="card-body">
                    <img alt="Password Reset" class="img-fluid inverse-color"
                         src="/img/demo/pages/password-reset.webp"/>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
