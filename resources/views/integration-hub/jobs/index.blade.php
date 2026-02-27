@extends('layouts.vertical', ['pageTitle' => 'Integration Jobs'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Integration Jobs',
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="row mb-3">
        <div class="col-xl-12">
            <form method="GET" action="{{ route('integration_hub.jobs.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
                <div>
                    <label class="form-label small mb-1">Direction</label>
                    <select name="direction" class="form-select form-select-sm" style="width: 140px">
                        <option value="">All</option>
                        <option value="import" {{ request('direction') === 'import' ? 'selected' : '' }}>Import</option>
                        <option value="export" {{ request('direction') === 'export' ? 'selected' : '' }}>Export</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm" style="width: 140px">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>Running</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">Connection</label>
                    <select name="connection_id" class="form-select form-select-sm" style="width: 200px">
                        <option value="">All Connections</option>
                        @foreach($connections as $conn)
                            <option value="{{ $conn->id }}" {{ request('connection_id') == $conn->id ? 'selected' : '' }}>{{ $conn->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-outline-info">Filter</button>
                @if(request()->hasAny(['direction', 'status', 'connection_id']))
                    <a href="{{ route('integration_hub.jobs.index') }}" class="btn btn-sm btn-light">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        Job History
                        <span class="text-muted small">({{ $jobs->total() }} total)</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($jobs->isEmpty())
                        <div class="p-4 text-center text-muted">No jobs match the selected filters.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Connection</th>
                                        <th>Direction</th>
                                        <th>Status</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Processed</th>
                                        <th class="text-end">Failed</th>
                                        <th>Success Rate</th>
                                        <th>Duration</th>
                                        <th>Started</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jobs as $job)
                                        <tr>
                                            <td>{{ $job->id }}</td>
                                            <td>
                                                @if($job->connection)
                                                    <a href="{{ route('integration_hub.connections.show', $job->connection) }}">{{ $job->connection->name }}</a>
                                                @else
                                                    <span class="text-muted">Deleted</span>
                                                @endif
                                            </td>
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
                                            <td class="text-end">{{ number_format($job->total_rows) }}</td>
                                            <td class="text-end">{{ number_format($job->rows_processed) }}</td>
                                            <td class="text-end {{ $job->rows_failed > 0 ? 'text-danger' : '' }}">{{ number_format($job->rows_failed) }}</td>
                                            <td>
                                                @if($job->successRate() !== null)
                                                    <div class="progress" style="height: 18px; min-width: 80px">
                                                        <div class="progress-bar {{ $job->successRate() >= 95 ? 'bg-success' : ($job->successRate() >= 80 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $job->successRate() }}%">
                                                            {{ $job->successRate() }}%
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $job->durationSeconds() ? $job->durationSeconds() . 's' : '—' }}</td>
                                            <td class="text-nowrap">{{ $job->started_at?->format('M j, g:ia') ?? '—' }}</td>
                                            <td class="text-center text-nowrap">
                                                <a href="{{ route('integration_hub.jobs.show', $job) }}" class="btn btn-outline-info btn-sm">
                                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#eye"></use></svg>
                                                </a>
                                                @if($job->status === 'failed')
                                                    <form method="POST" action="{{ route('integration_hub.jobs.retry', $job) }}" class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-outline-warning btn-sm" title="Retry">
                                                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#refresh-cw"></use></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="card-body">
                            {{ $jobs->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
