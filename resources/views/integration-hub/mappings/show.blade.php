@extends('layouts.vertical', ['pageTitle' => $mapping->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> $mapping->name,
        'pageSubTitle1' => 'Integration Hub',
        'pageSubTitle1Url' => route('integration_hub.dashboard'),
        'pageSubTitle2' => 'Field Mappings',
        'pageSubTitle2Url' => route('integration_hub.mappings.index'),
    ])
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-6">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Mapping Details</div>
                    <a href="{{ route('integration_hub.mappings.edit', $mapping) }}" class="btn btn-outline-info btn-sm">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#edit"></use></svg>
                        Edit
                    </a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Connection</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('integration_hub.connections.show', $mapping->connection) }}">{{ $mapping->connection->name }}</a>
                        </dd>

                        <dt class="col-sm-4">Source Field</dt>
                        <dd class="col-sm-8"><code>{{ $mapping->source_field }}</code></dd>

                        <dt class="col-sm-4">Target</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-info">{{ $mapping->target_model }}</span>.<code>{{ $mapping->target_field }}</code>
                        </dd>

                        <dt class="col-sm-4">Transform</dt>
                        <dd class="col-sm-8">{{ $mapping->transform ?: 'None (pass-through)' }}</dd>

                        <dt class="col-sm-4">Default Value</dt>
                        <dd class="col-sm-8">{{ $mapping->default_value ?: '—' }}</dd>

                        <dt class="col-sm-4">Required</dt>
                        <dd class="col-sm-8">
                            @if($mapping->is_required)
                                <span class="badge bg-warning text-dark">Yes</span>
                            @else
                                No
                            @endif
                        </dd>

                        <dt class="col-sm-4">Sort Order</dt>
                        <dd class="col-sm-8">{{ $mapping->sort_order }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

@endsection
