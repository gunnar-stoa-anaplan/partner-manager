@extends('layouts.vertical', ['pageTitle' => 'Job #' . $job->id])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Job #' . $job->id,
        'pageSubTitle1' => 'Integration Hub',
        'pageSubTitle1Url' => route('integration-hub.dashboard'),
        'pageSubTitle2' => 'Jobs',
        'pageSubTitle2Url' => route('integration-hub.jobs.index'),
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

    <div class="row">
        {{-- Job Details --}}
        <div class="col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Job Details</div>
                    @if($job->status === 'failed')
                        <form method="POST" action="{{ route('integration-hub.jobs.retry', $job) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-warning btn-sm">
                                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#refresh-cw"></use></svg>
                                Retry
                            </button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Connection</dt>
                        <dd class="col-sm-7">
                            @if($job->connection)
                                <a href="{{ route('integration-hub.connections.show', $job->connection) }}">{{ $job->connection->name }}</a>
                            @else
                                <span class="text-muted">Deleted</span>
                            @endif
                        </dd>

                        <dt class="col-sm-5">Direction</dt>
                        <dd class="col-sm-7">
                            @if($job->direction === 'import')
                                <svg class="sa-icon sa-icon-sm text-info"><use href="/icons/sprite.svg#download"></use></svg>
                            @else
                                <svg class="sa-icon sa-icon-sm text-warning"><use href="/icons/sprite.svg#upload"></use></svg>
                            @endif
                            {{ ucfirst($job->direction) }}
                        </dd>

                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            @if($job->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($job->status === 'failed')
                                <span class="badge bg-danger">Failed</span>
                            @elseif($job->status === 'running')
                                <span class="badge bg-primary">Running</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </dd>

                        <dt class="col-sm-5">Started</dt>
                        <dd class="col-sm-7">{{ $job->started_at?->format('M j, Y g:ia') ?? '—' }}</dd>

                        <dt class="col-sm-5">Completed</dt>
                        <dd class="col-sm-7">{{ $job->completed_at?->format('M j, Y g:ia') ?? '—' }}</dd>

                        <dt class="col-sm-5">Duration</dt>
                        <dd class="col-sm-7">{{ $job->durationSeconds() ? $job->durationSeconds() . 's' : '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="col-xl-8">
            <div class="row mb-g">
                <div class="col-sm-4">
                    <div class="card shadow-0 h-100">
                        <div class="card-body text-center py-3">
                            <div class="fs-1 fw-bold">{{ number_format($job->total_rows) }}</div>
                            <div class="text-muted small">Total Rows</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card shadow-0 h-100">
                        <div class="card-body text-center py-3">
                            <div class="fs-1 fw-bold text-success">{{ number_format($job->rows_processed) }}</div>
                            <div class="text-muted small">Processed</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card shadow-0 h-100">
                        <div class="card-body text-center py-3">
                            <div class="fs-1 fw-bold {{ $job->rows_failed > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($job->rows_failed) }}</div>
                            <div class="text-muted small">Failed</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($job->successRate() !== null)
                <div class="card shadow-0 mb-g">
                    <div class="card-body">
                        <label class="small text-muted">Success Rate</label>
                        <div class="progress" style="height: 24px">
                            <div class="progress-bar {{ $job->successRate() >= 95 ? 'bg-success' : ($job->successRate() >= 80 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $job->successRate() }}%">
                                {{ $job->successRate() }}%
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Summary --}}
            @if($job->summary)
                <div class="card shadow-0 mb-g">
                    <div class="card-header">
                        <div class="card-title">Summary</div>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0 small">{{ json_encode($job->summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            @endif

            {{-- Error Log --}}
            @if($job->error_log)
                <div class="card shadow-0 mb-g border-danger">
                    <div class="card-header bg-danger bg-opacity-10">
                        <div class="card-title text-danger">Error Log</div>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0 small text-danger">{{ $job->error_log }}</pre>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
