@extends('layouts.vertical', ['pageTitle' => 'Mapping Templates'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Mapping Templates',
        'pageSubTitle1' => 'Integration Hub',
        'pageSubTitle1Url' => route('integration_hub.dashboard'),
    ])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($connections->isEmpty())
        <div class="alert alert-info">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#info"></use></svg>
            Create a <a href="{{ route('integration_hub.connections.create') }}">connection</a> first, then apply templates to it.
        </div>
    @endif

    <div class="row">
        @forelse($templates as $template)
        <div class="col-xl-4 col-lg-6">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        <span class="badge bg-info me-1">{{ $template->target_model }}</span>
                        {{ $template->name }}
                    </div>
                    <span class="badge bg-secondary">v{{ $template->version }}</span>
                </div>
                <div class="card-body">
                    @if($template->description)
                        <p class="text-muted small mb-3">{{ $template->description }}</p>
                    @endif

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="text-center">
                            <div class="fs-3 fw-bold text-primary">{{ count($template->fields) }}</div>
                            <div class="text-muted small">Fields</div>
                        </div>
                        <div class="text-center">
                            <div class="fs-3 fw-bold text-warning">{{ collect($template->fields)->where('is_required', true)->count() }}</div>
                            <div class="text-muted small">Required</div>
                        </div>
                        @if($template->required_feature)
                        <div class="text-center">
                            <span class="badge bg-outline-secondary">{{ ucfirst($template->required_feature) }}</span>
                            <div class="text-muted small">Feature</div>
                        </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <span class="text-muted small">Fields: </span>
                        @foreach(array_slice($template->fields, 0, 5) as $field)
                            <code class="small">{{ $field['key'] }}</code>@if(!$loop->last), @endif
                        @endforeach
                        @if(count($template->fields) > 5)
                            <span class="text-muted small">+{{ count($template->fields) - 5 }} more</span>
                        @endif
                    </div>

                    @if($connections->isNotEmpty())
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#copy"></use></svg>
                                Apply to Connection
                            </button>
                            <ul class="dropdown-menu w-100">
                                @foreach($connections as $connection)
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center justify-content-between"
                                           href="{{ route('integration_hub.templates.review', ['template' => $template, 'connection_id' => $connection->id]) }}">
                                            {{ $connection->name }}
                                            @if(isset($updatesAvailable[$connection->id][$template->target_model]))
                                                <span class="badge bg-warning text-dark ms-2">Update</span>
                                            @elseif(isset(($connection->applied_templates ?? [])[$template->target_model]))
                                                <span class="badge bg-success ms-2">Applied</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card shadow-0 mb-g">
                <div class="card-body text-center py-5 text-muted">
                    <svg class="sa-icon mb-2" style="width:48px;height:48px"><use href="/icons/sprite.svg#copy"></use></svg>
                    <p>No mapping templates are available for your current license.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

@endsection
