@extends('layouts.vertical', ['pageTitle' => 'Directory Sync'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Directory Sync',
        'pageSubTitle1' => 'Team',
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

    <p class="text-muted fst-italic mt-3 mb-4">Import team members from an external directory or HR system via CSV. Synced fields (name, email, manager) are managed by the directory — role, avatar, and partner assignments remain locally editable.</p>

    {{-- Upload Card --}}
    <div class="row mb-4">
        <div class="col-xl-8 offset-xl-2">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Upload Directory CSV</div>
                </div>
                <div class="card-body">
                    @if($connections->isEmpty())
                        <div class="alert alert-warning mb-0">
                            No directory sync connections configured. Create a connection in the
                            <a href="{{ route('integration_hub.dashboard') }}">Integration Hub</a> using the "Directory Sync" connector profile.
                        </div>
                    @else
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('directory-sync.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="connection_id">Connection</label>
                                <select class="form-select" id="connection_id" name="connection_id" required>
                                    @foreach($connections as $connection)
                                        <option value="{{ $connection->id }}" {{ old('connection_id') == $connection->id ? 'selected' : '' }}>
                                            {{ $connection->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="csv_file">CSV File</label>
                                <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                                <div class="form-text">CSV with headers matching your field mappings (max 5MB). Expected columns: employee_id, full_name, email_address, manager_employee_id</div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#upload"></use></svg>
                                    Start Import
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Sync History --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Sync History</div>
                </div>
                <div class="card-body p-0">
                    @if($jobs->isEmpty())
                        <div class="p-4 text-center text-muted">No directory sync jobs yet. Upload a CSV to get started.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Connection</th>
                                        <th>Status</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Created</th>
                                        <th class="text-end">Updated</th>
                                        <th class="text-end">Skipped</th>
                                        <th>Duration</th>
                                        <th>Started</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jobs as $job)
                                        <tr>
                                            <td>{{ $job->id }}</td>
                                            <td>{{ $job->connection?->name ?? 'Deleted' }}</td>
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
                                            <td class="text-end">{{ $job->summary['created'] ?? '—' }}</td>
                                            <td class="text-end">{{ $job->summary['updated'] ?? '—' }}</td>
                                            <td class="text-end">{{ $job->summary['skipped'] ?? '—' }}</td>
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
