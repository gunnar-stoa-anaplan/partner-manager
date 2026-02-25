@extends('layouts.vertical', ['pageTitle' => 'Ingest Documents — Knowledge Base'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Ingest Documents',
        'pageSubTitle1' => 'Knowledge Base',
    ])
@endsection

@section('content')

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Upload Files --}}
        <div class="col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Upload Files</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('knowledge_base.ingest.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="documents" class="form-label">Select documents</label>
                            <input type="file" class="form-control" id="documents" name="documents[]" multiple accept=".pdf,.docx,.xlsx,.xls,.pptx,.ppt,.txt,.md,.html,.csv">
                            <div class="form-text">
                                Supported: PDF, DOCX, XLSX, XLS, PPTX, PPT, TXT, MD, HTML, CSV. Max 50 MB per file.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#upload-cloud"></use></svg>
                            Upload & Ingest
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Directory Ingest --}}
        <div class="col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Ingest from Directory</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('knowledge_base.ingest.directory') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="path" class="form-label">Directory path</label>
                            <input type="text" class="form-control" id="path" name="path" placeholder="/path/to/documents" value="{{ old('path') }}">
                            <div class="form-text">
                                Absolute path on the server. Recursively scans for supported files. Duplicates (same checksum) are skipped.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#folder"></use></svg>
                            Scan & Ingest
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Storage Disk Ingest --}}
        <div class="col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Ingest from Storage Disk</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('knowledge_base.ingest.storage-disk') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="disk" class="form-label">Storage disk</label>
                            <select class="form-select" id="disk" name="disk">
                                @foreach($disks as $diskName)
                                    <option value="{{ $diskName }}" @selected(old('disk') === $diskName)>
                                        {{ $diskName }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Select a configured Laravel Storage disk (S3, SFTP, etc.).
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="prefix" class="form-label">Path prefix (optional)</label>
                            <input type="text" class="form-control" id="prefix" name="prefix" placeholder="documents/2026" value="{{ old('prefix') }}">
                            <div class="form-text">
                                Only scan files under this folder/prefix. Leave empty to scan the entire disk.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#cloud"></use></svg>
                            Scan & Ingest
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
