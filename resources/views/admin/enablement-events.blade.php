@extends('layouts.vertical', ['pageTitle' => 'Enablement Events'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Enablement Events', 'pageSubTitle1' => 'Administration', 'pageSubText' => 'Manage partner enablement events and training sessions'])
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-icon" id="panel-enablement-events">
                <div class="panel-hdr">
                    <h2><i class="fal fa-award me-2"></i> Enablement Events <span class="fw-light"><i>Management</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fal fa-award fa-4x text-muted opacity-50"></i>
                            </div>
                            <h4 class="fw-400 text-muted mb-3">Coming Soon</h4>
                            <p class="text-muted mb-0" style="max-width: 480px; margin: 0 auto;">
                                This section will allow you to create and manage enablement events, including workshops, training sessions, certification exams, and partner onboarding programs.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
