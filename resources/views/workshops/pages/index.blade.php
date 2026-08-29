@extends('layouts.vertical', ['pageTitle' => $workshop->name.' - Content'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Content Pages', 'pageSubTitle1' => $workshop->name, 'pageSubTitle1Url' => route('workshops.show', $workshop)])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1"><i class="fal fa-file-alt me-2"></i> Content Pages</div>
                <a href="{{ route('workshops.toolkit.index', $workshop) }}" class="btn btn-outline-secondary btn-sm me-2">Toolkit Assets</a>
                <a href="{{ route('workshops.pages.create', $workshop) }}" class="btn btn-primary btn-sm">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                    New Page
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Checks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pages as $page)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $page->title }}</td>
                                <td><code>{{ $page->slug }}</code></td>
                                <td>{{ count($page->checks()) }}</td>
                                <td>
                                    <a href="{{ route('workshops.pages.edit', [$workshop, $page]) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('workshops.pages.destroy', [$workshop, $page]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this page?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No content pages yet - add the first page to build the curriculum.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
