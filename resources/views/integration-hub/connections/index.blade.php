@extends('layouts.vertical', ['pageTitle' => 'Connections'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Connections',
        'pageSubTitle1' => 'Integration Hub',
        'pageSubTitle1Url' => route('integration-hub.dashboard'),
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
                    <div class="card-title flex-grow-1">All Connections</div>
                    <a href="{{ route('integration-hub.connectors.catalog') }}" class="btn btn-outline-info btn-sm me-1">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#package"></use></svg>
                        Browse Connectors
                    </a>
                    <a href="{{ route('integration-hub.connections.create') }}" class="btn btn-primary btn-sm">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                        New Connection
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($connections->isEmpty())
                        <div class="p-4 text-center text-muted">
                            <p>No connections have been configured yet.</p>
                            <a href="{{ route('integration-hub.connectors.catalog') }}" class="btn btn-primary btn-sm me-1">
                                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#package"></use></svg>
                                Browse Connectors
                            </a>
                            <a href="{{ route('integration-hub.connections.create') }}" class="btn btn-outline-info btn-sm">Create Manually</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Direction</th>
                                        <th>Status</th>
                                        <th class="text-end">Total Jobs</th>
                                        <th class="text-end">Successful</th>
                                        <th>Last Tested</th>
                                        <th>Last Sync</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($connections as $connection)
                                        <tr>
                                            <td class="fw-bold">
                                                <a href="{{ route('integration-hub.connections.show', $connection) }}">{{ $connection->name }}</a>
                                            </td>
                                            <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $connection->type)) }}</span></td>
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
                                            <td class="text-end">{{ $connection->successful_jobs_count }}</td>
                                            <td class="text-nowrap">{{ $connection->last_tested_at?->diffForHumans() ?? '—' }}</td>
                                            <td class="text-nowrap">{{ $connection->last_sync_at?->diffForHumans() ?? '—' }}</td>
                                            <td class="text-center text-nowrap">
                                                <button class="btn btn-outline-info btn-sm btn-test-connection" data-id="{{ $connection->id }}" data-url="{{ route('integration-hub.connections.test', $connection) }}">
                                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#zap"></use></svg>
                                                </button>
                                                <a href="{{ route('integration-hub.connections.edit', $connection) }}" class="btn btn-outline-info btn-sm">
                                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit"></use></svg>
                                                </a>
                                                <form method="POST" action="{{ route('integration-hub.connections.destroy', $connection) }}" class="d-inline" onsubmit="return confirm('Delete this connection and all its mappings?')">
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
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
document.querySelectorAll('.btn-test-connection').forEach(btn => {
    btn.addEventListener('click', function() {
        const url = this.dataset.url;
        const original = this.innerHTML;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
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
            const label = data.success
                ? `<span class="text-success">OK (${data.latency_ms}ms)</span>`
                : `<span class="text-danger">${data.message}</span>`;
            this.innerHTML = original;
            this.disabled = false;
            this.closest('td').insertAdjacentHTML('beforeend', `<div class="small mt-1">${label}</div>`);
        })
        .catch(() => {
            this.innerHTML = original;
            this.disabled = false;
        });
    });
});
</script>
@endsection
