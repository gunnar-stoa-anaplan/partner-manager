@extends('layouts.vertical', ['pageTitle' => 'Development Funding'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Development Funding', 'pageSubTitle1' => 'Business Development', 'pageSubTitle1Url' => route('welcome'), 'pageSubText' => 'Track and manage partner development fund allocations'])
@endsection

@section('content')
    <div class="card shadow-0">
        <div class="card-body text-center py-5">
            <svg class="sa-icon text-muted mb-3" style="width: 48px; height: 48px;">
                <use href="/icons/sprite.svg#dollar-sign"></use>
            </svg>
            <h5 class="text-muted">Development Funding</h5>
            <p class="text-muted mb-0">This feature is coming soon.</p>
        </div>
    </div>
@endsection
