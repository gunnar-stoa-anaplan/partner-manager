@extends('layouts.vertical', ['pageTitle' => 'Welcome'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Welcome', 'pageSubTitle1' => 'Partner Manager'])
@endsection

@section('content')

    @if(Auth::user()->role === 'coe')
        @include('welcome.coe')
    @elseif(Auth::user()->isAdmin())
        @include('welcome.admin')
    @else
        @include('welcome.psm')
    @endif

@endsection
