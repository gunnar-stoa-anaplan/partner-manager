@extends('layouts.vertical', ['pageTitle' => 'Workshops'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Workshops', 'pageSubText' => 'Create and manage partner delivery workshops'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">
                    <i class="fal fa-check-square me-2"></i> Workshops
                </div>
                <a href="{{ route('workshops.create') }}" class="btn btn-primary btn-sm">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                    Create Workshop
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Sessions</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($workshops as $workshop)
                                <tr>
                                    <td><a href="{{ route('workshops.show', $workshop) }}">{{ $workshop->name }}</a></td>
                                    <td><span class="badge bg-secondary">{{ \App\Models\Workshop::STATUSES[$workshop->status] ?? $workshop->status }}</span></td>
                                    <td>{{ $workshop->sessions_count }}</td>
                                    <td>{{ $workshop->creator?->name }}</td>
                                    <td>
                                        <a href="{{ route('workshops.show', $workshop) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        <a href="{{ route('workshops.edit', $workshop) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No workshops yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
