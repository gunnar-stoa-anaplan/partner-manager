@extends('layouts.vertical', ['pageTitle' => 'Integration Hub'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Integration Hub',
        'pageSubTitle1' => 'Integrations',
    ])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Row --}}
    <div class="row mb-g">
        <div class="col-sm-6 col-xl-2">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold text-primary">{{ $stats['total_connections'] }}</div>
                    <div class="text-muted small">Connections</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold text-success">{{ $stats['active_connections'] }}</div>
                    <div class="text-muted small">Active</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold">{{ $stats['jobs_today'] }}</div>
                    <div class="text-muted small">Jobs Today</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold {{ $stats['failed_jobs_24h'] > 0 ? 'text-danger' : 'text-success' }}">{{ $stats['failed_jobs_24h'] }}</div>
                    <div class="text-muted small">Failed (24h)</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold text-info">{{ number_format($stats['rows_imported_30d']) }}</div>
                    <div class="text-muted small">Rows Imported (30d)</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold text-info">{{ number_format($stats['rows_exported_30d']) }}</div>
                    <div class="text-muted small">Rows Exported (30d)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Connections --}}
        <div class="col-xl-6">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Connections</div>
                    <a href="{{ route('integration_hub.connectors.catalog') }}" class="btn btn-outline-info btn-sm me-1">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#package"></use></svg>
                        Connectors
                    </a>
                    <a href="{{ route('integration_hub.connections.create') }}" class="btn btn-primary btn-sm">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                        New Connection
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($connections->isEmpty())
                        <div class="p-4 text-center text-muted">
                            <p>No connections configured yet.</p>
                            <a href="{{ route('integration_hub.connectors.catalog') }}" class="btn btn-primary btn-sm">
                                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#package"></use></svg>
                                Browse Connectors
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Direction</th>
                                        <th>Status</th>
                                        <th class="text-end">Jobs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($connections as $connection)
                                        <tr class="cursor-pointer" onclick="window.location='{{ route('integration_hub.connections.show', $connection) }}'">
                                            <td class="fw-bold">{{ $connection->name }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $connection->type)) }}</span>
                                            </td>
                                            <td>{{ ucfirst($connection->direction) }}</td>
                                            <td>
                                                @if($connection->status === 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @elseif($connection->status === 'error')
                                                    <span class="badge bg-danger">Error</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ $connection->jobs_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Jobs --}}
        <div class="col-xl-6">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Recent Jobs</div>
                    <a href="{{ route('integration_hub.jobs.index') }}" class="btn btn-outline-info btn-sm">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentJobs->isEmpty())
                        <div class="p-4 text-center text-muted">
                            No jobs have been run yet.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Connection</th>
                                        <th>Direction</th>
                                        <th>Status</th>
                                        <th class="text-end">Rows</th>
                                        <th>When</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentJobs as $job)
                                        <tr class="cursor-pointer" onclick="window.location='{{ route('integration_hub.jobs.show', $job) }}'">
                                            <td>{{ $job->connection->name ?? 'Deleted' }}</td>
                                            <td>
                                                @if($job->direction === 'import')
                                                    <svg class="sa-icon sa-icon-sm text-info"><use href="/icons/sprite.svg#download"></use></svg>
                                                @else
                                                    <svg class="sa-icon sa-icon-sm text-warning"><use href="/icons/sprite.svg#upload"></use></svg>
                                                @endif
                                                {{ ucfirst($job->direction) }}
                                            </td>
                                            <td>
                                                @if($job->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($job->status === 'failed')
                                                    <span class="badge bg-danger">Failed</span>
                                                @elseif($job->status === 'running')
                                                    <span class="badge bg-primary">Running</span>
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ number_format($job->rows_processed) }}</td>
                                            <td class="text-nowrap">{{ $job->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
