@extends('layouts.vertical', ['pageTitle' => 'Enablement Blueprints'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Enablement Blueprints', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubText' => 'Manage partner readiness blueprints'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Enablement Blueprints define the enablement curriculum your partners need to complete for each product area. Each blueprint contains outcomes with specific content items — organized by role (GTM, Pre-Sales, Delivery). Create new blueprints here, or click into an existing one to manage its outcomes, content, and partner assignments.</p>
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Blueprints</div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('blueprints.create') }}" class="btn btn-primary btn-sm">
                            <i class="fal fa-plus me-1"></i> Create Blueprint
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="blueprints-table" class="table table-bordered table-hover table-striped w-100">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Product Area</th>
                                    <th>Outcomes</th>
                                    <th>Content Items</th>
                                    <th>Assignments</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($blueprints as $blueprint)
                                    <tr>
                                        <td>
                                            <a href="{{ route('blueprints.show', $blueprint) }}" class="fw-500">
                                                {{ $blueprint->name }}
                                            </a>
                                            @if($blueprint->version)
                                                <small class="text-muted ms-1">{{ $blueprint->version }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $blueprint->category === 'application' ? 'info' : 'warning' }}-50 text-{{ $blueprint->category === 'application' ? 'info' : 'warning' }}">
                                                {{ \App\Models\Blueprint::CATEGORIES[$blueprint->category] ?? $blueprint->category }}
                                            </span>
                                        </td>
                                        <td>{{ $blueprint->product_area ?? '-' }}</td>
                                        <td class="text-center">{{ $blueprint->outcomes_count }}</td>
                                        <td class="text-center">{{ $blueprint->outcomes->sum(fn($o) => $o->contentItems->count()) }}</td>
                                        <td class="text-center">{{ $blueprint->assignments_count }}</td>
                                        <td>
                                            @php
                                                $statusColors = ['draft' => 'secondary', 'published' => 'success', 'archived' => 'warning'];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$blueprint->status] ?? 'secondary' }}">
                                                {{ ucfirst($blueprint->status) }}
                                            </span>
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('blueprints.show', $blueprint) }}" class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fal fa-eye"></i>
                                            </a>
                                            <a href="{{ route('blueprints.edit', $blueprint) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fal fa-pencil"></i>
                                            </a>
                                            <form action="{{ route('blueprints.destroy', $blueprint) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this blueprint?')">
                                                    <i class="fal fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="mb-3"><i class="fal fa-layer-group fa-3x text-muted opacity-50"></i></div>
                                            <h5 class="text-muted">No blueprints yet</h5>
                                            <p class="text-muted mb-3">Create your first enablement blueprint to get started.</p>
                                            <a href="{{ route('blueprints.create') }}" class="btn btn-primary btn-sm">Create Blueprint</a>
                                        </td>
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

@section('scripts')
    @vite(['resources/scripts/pages/table-init.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.SmartTables && document.querySelector('#blueprints-table tbody tr td:not([colspan])')) {
                new window.SmartTables('blueprints-table', {
                    perPage: 15,
                    responsive: {
                        enabled: true,
                        breakpoint: 768,
                        columnPriorities: { 0: 1, 6: 2, 1: 3, 2: 4, 5: 5, 3: 6, 4: 7, 7: 8 }
                    }
                });
            }
        });
    </script>
@endsection
