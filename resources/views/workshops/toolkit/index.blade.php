@extends('layouts.vertical', ['pageTitle' => $workshop->name.' - Toolkit'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Toolkit Assets', 'pageSubTitle1' => $workshop->name, 'pageSubTitle1Url' => route('workshops.show', $workshop)])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title"><i class="fal fa-folder-open me-2"></i> Upload Asset</div>
            </div>
            <div class="card-body">
                <form action="{{ route('workshops.toolkit.store', $workshop) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label fs-sm">Title</label>
                        <input type="text" class="form-control form-control-sm" name="title" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-sm">Description</label>
                        <input type="text" class="form-control form-control-sm" name="description">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-sm">File</label>
                        <input type="file" class="form-control form-control-sm" name="file" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-0 mb-g">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>File</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                            <tr>
                                <td>{{ $asset->title }}</td>
                                <td>{{ $asset->description }}</td>
                                <td>{{ $asset->file_name }}</td>
                                <td>{{ $asset->created_at->format('M j, Y') }}</td>
                                <td>
                                    <a href="{{ route('workshops.toolkit.download', [$workshop, $asset]) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                    <form action="{{ route('workshops.toolkit.destroy', [$workshop, $asset]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this asset?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No toolkit assets uploaded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
