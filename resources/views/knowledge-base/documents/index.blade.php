@extends('layouts.vertical', ['pageTitle' => 'Documents — Knowledge Base'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Documents',
        'pageSubTitle1' => 'Knowledge Base',
    ])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-0 mb-g">
        <div class="card-header d-flex align-items-center">
            <div class="card-title flex-grow-1">All Documents</div>
            <a href="{{ route('knowledge_base.ingest.create') }}" class="btn btn-primary btn-sm">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#upload-cloud"></use></svg>
                Ingest
            </a>
        </div>
        <div class="card-body p-0">
            @if($documents->isEmpty())
                <div class="p-4 text-center text-muted">
                    No documents found.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>File</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th class="text-end">Size</th>
                                <th class="text-end">Chunks</th>
                                <th>Ingested</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                                <tr class="cursor-pointer" onclick="window.location='{{ route('knowledge_base.documents.show', $document) }}'">
                                    <td class="fw-bold">{{ $document->title }}</td>
                                    <td class="text-muted small">{{ $document->file_name }}</td>
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
                                    <td class="text-end text-nowrap">
                                        @if($document->file_size_bytes > 1048576)
                                            {{ number_format($document->file_size_bytes / 1048576, 1) }} MB
                                        @else
                                            {{ number_format($document->file_size_bytes / 1024, 0) }} KB
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

    {{ $documents->links() }}

@endsection
