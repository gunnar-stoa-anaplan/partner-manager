@extends('layouts.vertical', ['pageTitle' => 'Welcome'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Welcome', 'pageSubTitle1' => 'Partner Manager'])
@endsection

@section('content')

    @php
        $roleSlug = Auth::user()->role?->slug;
    @endphp

    @if(Auth::user()->isAdmin())
        @include('welcome.admin')
    @elseif($roleSlug === 'coe')
        @include('welcome.coe')
    @elseif($roleSlug === 'manager')
        @include('welcome.manager')
    @elseif($roleSlug === 'integration')
        @include('welcome.integration')
    @else
        @include('welcome.psm')
    @endif

@endsection
