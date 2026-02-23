@extends('layouts.vertical', ['pageTitle' => $connection->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> $connection->name,
        'pageSubTitle1' => 'Integration Hub',
        'pageSubTitle1Url' => route('integration-hub.dashboard'),
        'pageSubTitle2' => 'Connections',
        'pageSubTitle2Url' => route('integration-hub.connections.index'),
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
        {{-- Connection Details --}}
        <div class="col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Details</div>
                    <a href="{{ route('integration-hub.connections.edit', $connection) }}" class="btn btn-outline-info btn-sm">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#edit"></use></svg>
                        Edit
                    </a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Type</dt>
                        <dd class="col-sm-7"><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $connection->type)) }}</span></dd>

                        <dt class="col-sm-5">Direction</dt>
                        <dd class="col-sm-7">{{ ucfirst($connection->direction) }}</dd>

                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            @if($connection->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($connection->status === 'error')
                                <span class="badge bg-danger">Error</span>
                            @else
                                <span class="badge bg-warning text-dark">Inactive</span>
                            @endif
                        </dd>

                        <dt class="col-sm-5">Last Tested</dt>
                        <dd class="col-sm-7">{{ $connection->last_tested_at?->diffForHumans() ?? '—' }}</dd>

                        <dt class="col-sm-5">Last Sync</dt>
                        <dd class="col-sm-7">{{ $connection->last_sync_at?->diffForHumans() ?? '—' }}</dd>

                        <dt class="col-sm-5">Created</dt>
                        <dd class="col-sm-7">{{ $connection->created_at->format('M j, Y') }}</dd>
                    </dl>

                    <hr>
                    <button class="btn btn-outline-info btn-sm w-100 btn-test-connection" data-url="{{ route('integration-hub.connections.test', $connection) }}">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#zap"></use></svg>
                        Test Connection
                    </button>
                    <div id="test-result" class="small mt-2 text-center"></div>
                </div>
            </div>

            {{-- Field Mappings --}}
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Field Mappings</div>
                    <a href="{{ route('integration-hub.templates.index') }}" class="btn btn-outline-info btn-sm me-1">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#copy"></use></svg>
                        Templates
                    </a>
                    <a href="{{ route('integration-hub.mappings.create', ['connection_id' => $connection->id]) }}" class="btn btn-primary btn-sm">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                        Add
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($connection->mappings->isEmpty())
                        <div class="p-3 text-center text-muted small">
                            No field mappings configured.
                            <a href="{{ route('integration-hub.templates.index') }}" class="d-block mt-1">Apply a template</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Source</th>
                                        <th>Target</th>
                                        <th>Transform</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($connection->mappings->sortBy('sort_order') as $mapping)
                                        <tr>
                                            <td class="small">{{ $mapping->source_field }}</td>
                                            <td class="small">{{ $mapping->target_model }}.{{ $mapping->target_field }}</td>
                                            <td class="small text-muted">{{ $mapping->transform ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Job History --}}
        <div class="col-xl-8">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Job History</div>
                    <form method="POST" action="{{ route('integration-hub.jobs.store') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="connection_id" value="{{ $connection->id }}">
                        <input type="hidden" name="direction" value="import">
                        <button class="btn btn-primary btn-sm me-1" {{ $connection->direction === 'export' ? 'disabled' : '' }}>
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#download"></use></svg>
                            Run Import
                        </button>
                    </form>
                    <form method="POST" action="{{ route('integration-hub.jobs.store') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="connection_id" value="{{ $connection->id }}">
                        <input type="hidden" name="direction" value="export">
                        <button class="btn btn-outline-info btn-sm" {{ $connection->direction === 'import' ? 'disabled' : '' }}>
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#upload"></use></svg>
                            Run Export
                        </button>
                    </form>
                </div>
                <div class="card-body p-0">
                    @if($connection->jobs->isEmpty())
                        <div class="p-4 text-center text-muted">No jobs have been run for this connection yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Direction</th>
                                        <th>Status</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Processed</th>
                                        <th class="text-end">Failed</th>
                                        <th>Duration</th>
                                        <th>Started</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($connection->jobs as $job)
                                        <tr class="cursor-pointer" onclick="window.location='{{ route('integration-hub.jobs.show', $job) }}'">
                                            <td>{{ $job->id }}</td>
                                            <td>{{ ucfirst($job->direction) }}</td>
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
                                            <td>{{ $job->durationSeconds() ? $job->durationSeconds() . 's' : '—' }}</td>
                                            <td class="text-nowrap">{{ $job->started_at?->format('M j, g:ia') ?? '—' }}</td>
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

@section('scripts')
<script>
document.querySelector('.btn-test-connection')?.addEventListener('click', function() {
    const url = this.dataset.url;
    const resultEl = document.getElementById('test-result');
    const original = this.innerHTML;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Testing...';
    this.disabled = true;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        resultEl.innerHTML = data.success
            ? `<span class="text-success">Connected (${data.latency_ms}ms)</span>`
            : `<span class="text-danger">${data.message}</span>`;
        this.innerHTML = original;
        this.disabled = false;
    })
    .catch(() => {
        resultEl.innerHTML = '<span class="text-danger">Request failed</span>';
        this.innerHTML = original;
        this.disabled = false;
    });
});
</script>
@endsection
