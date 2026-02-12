@extends('layouts.vertical', ['pageTitle' => 'Enablement Events'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Enablement Events', 'pageSubTitle1' => 'Partner Enablement', 'pageSubTitle1Url' => route('enablement.calendar'), 'pageSubText' => 'Create and manage partner enablement events catalog'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The Events Catalog is where you create and manage the enablement offerings available to your partners — workshops, academies, bootcamps, certifications, and more. Each event can have multiple sessions scheduled on the calendar. Use this view to build your catalog, then assign sessions to specific dates from the Calendar view.</p>
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        <i class="fal fa-award me-2"></i> Enablement Events Catalog
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('enablement-calendar') }}" class="btn btn-outline-info btn-sm">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#calendar"></use></svg>
                            Calendar View
                        </a>
                        <a href="{{ route('enablement-events.create') }}" class="btn btn-primary btn-sm">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                            Create Event
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped w-100" id="enablement-events-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Product Area</th>
                                    <th>Sessions</th>
                                    <th>Registrations</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                    <tr>
                                        <td>
                                            <a href="{{ route('enablement-events.show', $event->id) }}" class="fw-500">
                                                {{ $event->title }}
                                            </a>
                                            @if($event->target_audience)
                                                <br><small class="text-muted">{{ $event->target_audience }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: {{ \App\Models\EnablementEvent::TYPE_COLORS[$event->type] ?? '#868e96' }}">
                                                {{ \App\Models\EnablementEvent::TYPES[$event->type] ?? $event->type }}
                                            </span>
                                        </td>
                                        <td>{{ $event->category ?? '—' }}</td>
                                        <td>{{ $event->product_area ?? '—' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $event->sessions_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $event->registrations_count }}</span>
                                        </td>
                                        <td>
                                            @if($event->status === 'published')
                                                <span class="badge bg-success">Published</span>
                                            @elseif($event->status === 'draft')
                                                <span class="badge bg-warning text-dark">Draft</span>
                                            @else
                                                <span class="badge bg-secondary">Archived</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('enablement-events.show', $event->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                                <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#eye"></use></svg>
                                            </a>
                                            <a href="{{ route('enablement-events.edit', $event->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Event">
                                                <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit"></use></svg>
                                            </a>
                                            <form action="{{ route('enablement-events.destroy', $event->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this event and all its sessions?')" title="Delete">
                                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#trash-2"></use></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="fal fa-award fa-3x text-muted opacity-50"></i>
                                            </div>
                                            <h5 class="fw-400 text-muted">No enablement events yet</h5>
                                            <p class="text-muted mb-3">Create your first enablement event to get started.</p>
                                            <a href="{{ route('enablement-events.create') }}" class="btn btn-primary btn-sm">
                                                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                                                Create Event
                                            </a>
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
            if (window.SmartTables && document.querySelector('#enablement-events-table tbody tr td:not([colspan])')) {
                new window.SmartTables('enablement-events-table', {
                    perPage: 15,
                    responsive: {
                        enabled: true,
                        breakpoint: 768,
                        columnPriorities: { 0: 1, 6: 2, 1: 3, 4: 4, 5: 5, 2: 6, 3: 7, 7: 8 }
                    }
                });
            }
        });
    </script>
@endsection
