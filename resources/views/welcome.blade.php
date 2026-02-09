@extends('layouts.vertical', ['pageTitle' => 'Welcome'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Welcome', 'pageSubTitle1' => 'Partner Manager'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-0 mb-g">
                <div class="card-body p-4">
                    <h4>Welcome, {{ Auth::user()->name }}</h4>
                    <p class="text-muted mb-0">You are logged in as <strong>{{ Auth::user()->role }}</strong>.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
