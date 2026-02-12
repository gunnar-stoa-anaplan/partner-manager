@extends('layouts.vertical', ['pageTitle' => 'Measure — Analytics'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Analytics Dashboard', 'pageSubTitle1' => 'Measure', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The Analytics Dashboard brings together your core performance metrics in one place — pipeline health, lead flow, funding utilization, revenue attainment, enablement progress, and delivery quality. Use the tabs to switch between dimensions and the time filters to compare periods. This is where you go to answer "how are we doing" across your portfolio.</p>

{{-- Tab Navigation --}}
<ul class="nav nav-tabs mb-3" role="tablist">
    @php
        $tabs = [
            'at-a-glance' => 'At a Glance',
            'pipeline' => 'Pipeline',
            'leads' => 'Lead Flow',
            'funding' => 'Funding',
            'revenue' => 'Revenue',
            'enablement' => 'Enablement',
            'delivery' => 'Delivery',
        ];
    @endphp
    @foreach($tabs as $key => $label)
    <li class="nav-item">
        <a class="nav-link {{ $tab === $key ? 'active' : '' }}" href="{{ route('measure.index', ['tab' => $key]) }}">
            {{ $label }}
        </a>
    </li>
    @endforeach
</ul>

{{-- Tab Content --}}
@if($tab === 'at-a-glance')
    @include('measure._at-a-glance')
@elseif($tab === 'pipeline')
    @include('measure._pipeline')
@elseif($tab === 'leads')
    @include('measure._leads')
@elseif($tab === 'funding')
    @include('measure._funding')
@elseif($tab === 'revenue')
    @include('measure._revenue')
@elseif($tab === 'enablement')
    @include('measure._enablement')
@elseif($tab === 'delivery')
    @include('measure._delivery')
@endif

@endsection
