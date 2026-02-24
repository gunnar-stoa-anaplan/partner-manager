@extends('layouts.vertical', ['pageTitle' => $document->title . ' — Knowledge Base'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> $document->title,
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

    <div class="row">
        {{-- Document Details --}}
        <div class="col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Details</div>
                    <div class="d-flex gap-1">
                        <form action="{{ route('knowledge_base.documents.reingest', $document) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm">Re-ingest</button>
                        </form>
                        <form action="{{ route('knowledge_base.documents.destroy', $document) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this document and all its chunks?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">File</dt>
                        <dd class="col-sm-8">{{ $document->file_name }}</dd>

                        <dt class="col-sm-4">MIME</dt>
                        <dd class="col-sm-8"><code>{{ $document->mime_type }}</code></dd>

                        <dt class="col-sm-4">Size</dt>
                        <dd class="col-sm-8">
                            @if($document->file_size_bytes > 1048576)
                                {{ number_format($document->file_size_bytes / 1048576, 1) }} MB
                            @else
                                {{ number_format($document->file_size_bytes / 1024, 0) }} KB
                            @endif
                        </dd>

                        <dt class="col-sm-4">Source</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-secondary">{{ ucfirst($document->source->value) }}</span>
                        </dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($document->status === \App\Enums\DocumentStatus::Completed)
                                <span class="badge bg-success">Completed</span>
                            @elseif($document->status === \App\Enums\DocumentStatus::Failed)
                                <span class="badge bg-danger">Failed</span>
                            @elseif($document->status === \App\Enums\DocumentStatus::Processing)
                                <span class="badge bg-primary">Processing</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Chunks</dt>
                        <dd class="col-sm-8">{{ number_format($document->chunks_count ?? $document->chunk_count) }}</dd>

                        <dt class="col-sm-4">Ingested</dt>
                        <dd class="col-sm-8">{{ $document->ingested_at?->format('M j, Y g:i A') ?? 'Not yet' }}</dd>

                        @if($document->error_message)
                            <dt class="col-sm-4 text-danger">Error</dt>
                            <dd class="col-sm-8 text-danger">{{ $document->error_message }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        {{-- Chunks --}}
        <div class="col-xl-8">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Chunks</div>
                </div>
                <div class="card-body p-0">
                    @if($chunks->isEmpty())
                        <div class="p-4 text-center text-muted">
                            No chunks generated yet.
                        </div>
                    @else
                        @foreach($chunks as $chunk)
                            <div class="border-bottom p-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="badge bg-light text-dark">Chunk #{{ $chunk->chunk_index }}</span>
                                    <span class="text-muted small">{{ $chunk->word_count }} words</span>
                                </div>
                                <p class="mb-0 small" style="max-height: 6em; overflow: hidden;">{{ $chunk->content }}</p>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{ $chunks->links() }}
        </div>
    </div>

@endsection
