@extends('layouts.vertical', ['pageTitle' => 'Knowledge Base'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Knowledge Base',
        'pageSubTitle1' => 'Intelligence',
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
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold text-primary">{{ $stats['total_documents'] }}</div>
                    <div class="text-muted small">Total Documents</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold text-success">{{ $stats['completed_documents'] }}</div>
                    <div class="text-muted small">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold {{ $stats['failed_documents'] > 0 ? 'text-danger' : 'text-success' }}">{{ $stats['failed_documents'] }}</div>
                    <div class="text-muted small">Failed</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card shadow-0 h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold text-info">{{ number_format($stats['total_chunks']) }}</div>
                    <div class="text-muted small">Total Chunks</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Recent Documents --}}
        <div class="col-xl-8">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Recent Documents</div>
                    <a href="{{ route('knowledge_base.ingest.create') }}" class="btn btn-primary btn-sm me-1">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#upload-cloud"></use></svg>
                        Ingest
                    </a>
                    <a href="{{ route('knowledge_base.documents.index') }}" class="btn btn-outline-info btn-sm">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentDocuments->isEmpty())
                        <div class="p-4 text-center text-muted">
                            <p>No documents ingested yet.</p>
                            <a href="{{ route('knowledge_base.ingest.create') }}" class="btn btn-primary btn-sm">
                                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#upload-cloud"></use></svg>
                                Upload Documents
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Source</th>
                                        <th>Status</th>
                                        <th class="text-end">Chunks</th>
                                        <th>Ingested</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentDocuments as $document)
                                        <tr class="cursor-pointer" onclick="window.location='{{ route('knowledge_base.documents.show', $document) }}'">
                                            <td class="fw-bold">{{ $document->title }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst($document->source->value) }}</span>
                                            </td>
                                            <td>
                                                @if($document->status === \App\Enums\DocumentStatus::Completed)
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($document->status === \App\Enums\DocumentStatus::Failed)
                                                    <span class="badge bg-danger">Failed</span>
                                                @elseif($document->status === \App\Enums\DocumentStatus::Processing)
                                                    <span class="badge bg-primary">Processing</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ number_format($document->chunk_count) }}</td>
                                            <td class="text-nowrap">{{ $document->ingested_at?->diffForHumans() ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Quick Actions</div>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('knowledge_base.ingest.create') }}" class="btn btn-outline-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#upload-cloud"></use></svg>
                            Upload Documents
                        </a>
                        <a href="{{ route('knowledge_base.documents.index') }}" class="btn btn-outline-info">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#file-text"></use></svg>
                            Browse All Documents
                        </a>
                    </div>
                </div>
            </div>

            @if($stats['pending_documents'] > 0)
                <div class="card shadow-0 mb-g border-warning">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-warning">{{ $stats['pending_documents'] }}</div>
                        <div class="text-muted small">Documents awaiting ingestion</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
