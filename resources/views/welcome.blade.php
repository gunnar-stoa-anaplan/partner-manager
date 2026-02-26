@extends('layouts.vertical', ['pageTitle' => 'Welcome'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Welcome', 'pageSubTitle1' => 'Partner Manager'])
@endsection

@section('content')

    @if(Auth::user()->isAdmin())
        @include('welcome.admin')
    @elseif(Auth::user()->role === 'coe')
        @include('welcome.coe')
    @elseif(Auth::user()->role === 'manager')
        @include('welcome.manager')
    @elseif(Auth::user()->role === 'integration')
        @include('welcome.integration')
    @else
        @include('welcome.psm')
    @endif

@endsection
