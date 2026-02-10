@extends('layouts.vertical', ['pageTitle' => 'Data Imports'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Data Imports', 'pageSubTitle1' => 'Configuration'])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">Data Import History</div>
                <a href="{{ route('imports.create') }}" class="btn btn-primary btn-sm">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#upload"></use></svg>
                    New Import
                </a>
            </div>
            <div class="card-body">
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

                @php
                    $importsWithErrors = $imports->filter(fn($i) => $i->failed_rows > 0)->count();
                    $totalErrors = $imports->sum('failed_rows');
                @endphp

                @if($importsWithErrors > 0)
                    <div class="alert alert-warning">
                        <strong>Import Errors Detected:</strong>
                        <strong>{{ $importsWithErrors }}</strong> import{{ $importsWithErrors > 1 ? 's' : '' }}
                        {{ $importsWithErrors > 1 ? 'have' : 'has' }} a total of
                        <strong>{{ number_format($totalErrors) }}</strong> failed row{{ $totalErrors > 1 ? 's' : '' }}.
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100" id="importsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>File Name</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Success</th>
                                <th>Failed</th>
                                <th>Partners</th>
                                <th>Imported By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($imports as $import)
                            <tr class="{{ $import->failed_rows > 0 ? 'table-warning' : '' }}">
                                <td>{{ $import->id }}</td>
                                <td><span class="badge bg-info">{{ ucfirst($import->type) }}</span></td>
                                <td>{{ $import->file_name }}</td>
                                <td>
                                    @if($import->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($import->status === 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @elseif($import->status === 'processing')
                                        <span class="badge bg-warning text-dark">Processing</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td>{{ number_format($import->total_rows) }}</td>
                                <td class="text-success">{{ number_format($import->successful_rows) }}</td>
                                <td>
                                    @if($import->failed_rows > 0)
                                        <span class="text-danger fw-bold">{{ number_format($import->failed_rows) }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>{{ number_format($import->partners_created) }}</td>
                                <td>{{ $import->user->name ?? 'System' }}</td>
                                <td>{{ $import->created_at->format('M d, Y H:i') }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('imports.show', $import) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#eye"></use></svg>
                                    </a>
                                    <form action="{{ route('imports.destroy', $import) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this import?')" title="Delete">
                                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#trash-2"></use></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    No imports found. <a href="{{ route('imports.create') }}">Create your first import</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $imports->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
