@extends('layouts.vertical', ['pageTitle' => 'Field Mappings'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Field Mappings',
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

    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">All Field Mappings</div>
                    <a href="{{ route('integration_hub.templates.index') }}" class="btn btn-outline-info btn-sm me-1">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#copy"></use></svg>
                        Browse Templates
                    </a>
                    <a href="{{ route('integration_hub.mappings.create') }}" class="btn btn-primary btn-sm">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                        New Mapping
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($mappings->isEmpty())
                        <div class="p-4 text-center text-muted">
                            <p>No field mappings configured yet.</p>
                            <a href="{{ route('integration_hub.templates.index') }}" class="btn btn-primary btn-sm me-1">
                                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#copy"></use></svg>
                                Apply a Template
                            </a>
                            <a href="{{ route('integration_hub.mappings.create') }}" class="btn btn-outline-secondary btn-sm">Create Manually</a>
                        </div>
                    @else
                        @foreach($connections as $connection)
                            @if(isset($mappings[$connection->id]))
                            <div class="px-3 pt-3">
                                <h6 class="d-flex align-items-center gap-2">
                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#link-2"></use></svg>
                                    <a href="{{ route('integration_hub.connections.show', $connection) }}">{{ $connection->name }}</a>
                                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $connection->type)) }}</span>
                                </h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped w-100 mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th>Name</th>
                                            <th>Source Field</th>
                                            <th>Target Model</th>
                                            <th>Target Field</th>
                                            <th>Transform</th>
                                            <th>Required</th>
                                            <th class="text-center" style="width: 10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mappings[$connection->id]->sortBy('sort_order') as $mapping)
                                            <tr>
                                                <td class="text-muted">{{ $mapping->sort_order }}</td>
                                                <td class="fw-bold">{{ $mapping->name }}</td>
                                                <td><code>{{ $mapping->source_field }}</code></td>
                                                <td><span class="badge bg-info">{{ $mapping->target_model }}</span></td>
                                                <td><code>{{ $mapping->target_field }}</code></td>
                                                <td>{{ $mapping->transform ?: '—' }}</td>
                                                <td>
                                                    @if($mapping->is_required)
                                                        <span class="badge bg-warning text-dark">Required</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center text-nowrap">
                                                    <a href="{{ route('integration_hub.mappings.edit', $mapping) }}" class="btn btn-outline-info btn-sm">
                                                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit"></use></svg>
                                                    </a>
                                                    <form method="POST" action="{{ route('integration_hub.mappings.destroy', $mapping) }}" class="d-inline" onsubmit="return confirm('Delete this mapping?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-outline-danger btn-sm">
                                                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#trash-2"></use></svg>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
