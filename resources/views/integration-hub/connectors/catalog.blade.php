@extends('layouts.vertical', ['pageTitle' => 'Connectors'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Connectors',
        'pageSubTitle1' => 'Integration Hub',
        'pageSubTitle1Url' => route('integration-hub.dashboard'),
    ])
@endsection

@section('content')

    <div class="mb-4">
        <p class="text-muted">Choose a pre-built connector to quickly set up a connection with recommended field mappings.</p>
    </div>

    <div class="row">
        @foreach($profiles as $profile)
        <div class="col-xl-3 col-lg-6">
            <div class="card shadow-0 mb-g">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <svg class="sa-icon text-primary" style="width:48px;height:48px"><use href="/icons/sprite.svg#{{ $profile->icon }}"></use></svg>
                    </div>
                    <h5 class="card-title">{{ $profile->name }}</h5>
                    <p class="text-muted small mb-3">{{ $profile->description }}</p>

                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $profile->connection_type)) }}</span>
                        <span class="badge bg-outline-secondary">{{ ucfirst($profile->direction) }}</span>
                    </div>

                    @if(!empty($profile->template_slugs))
                        <div class="text-muted small mb-3">
                            {{ count($profile->template_slugs) }} template(s) included
                        </div>
                    @endif

                    <a href="{{ route('integration-hub.connectors.setup', $profile) }}" class="btn btn-primary btn-sm w-100">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#settings"></use></svg>
                        Set Up
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-3">
        <p class="text-muted small">
            Need a custom connection? <a href="{{ route('integration-hub.connections.create') }}">Create one manually</a> and
            <a href="{{ route('integration-hub.templates.index') }}">apply templates</a> separately.
        </p>
    </div>

@endsection
