@extends('layouts.vertical', ['pageTitle' => $event->title])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $event->title, 'pageSubTitle1' => 'Enablement Events', 'pageSubText' => 'Event details, sessions, and registrations'])
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Event Overview Card --}}
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        <span class="badge me-2" style="background-color: {{ \App\Models\EnablementEvent::TYPE_COLORS[$event->type] ?? '#868e96' }}">
                            {{ \App\Models\EnablementEvent::TYPES[$event->type] ?? $event->type }}
                        </span>
                        {{ $event->title }}
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('enablement-events.edit', $event->id) }}" class="btn btn-outline-primary btn-sm">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#edit"></use></svg>
                            Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Status</small>
                            @if($event->status === 'published')
                                <span class="badge bg-success">Published</span>
                            @elseif($event->status === 'draft')
                                <span class="badge bg-warning text-dark">Draft</span>
                            @else
                                <span class="badge bg-secondary">Archived</span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Category</small>
                            <strong>{{ $event->category ?? '—' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Product Area</small>
                            <strong>{{ $event->product_area ?? '—' }}</strong>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Target Audience</small>
                            <strong>{{ $event->target_audience ?? '—' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Duration</small>
                            <strong>{{ $event->duration_hours ? $event->duration_hours . ' hours' : '—' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Max Participants (default)</small>
                            <strong>{{ $event->max_participants ?? 'Unlimited' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Created By</small>
                            <strong>{{ $event->creator?->name ?? '—' }}</strong>
                        </div>
                    </div>

                    @if($event->description)
                        <div class="mb-3">
                            <small class="text-muted d-block">Description</small>
                            <p class="mb-0">{{ $event->description }}</p>
                        </div>
                    @endif

                    @if($event->objectives)
                        <div class="mb-3">
                            <small class="text-muted d-block">Learning Objectives</small>
                            <p class="mb-0">{{ $event->objectives }}</p>
                        </div>
                    @endif

                    @if($event->prerequisites)
                        <div class="mb-3">
                            <small class="text-muted d-block">Prerequisites</small>
                            <p class="mb-0">{{ $event->prerequisites }}</p>
                        </div>
                    @endif

                    @if($event->materials_url)
                        <div class="mb-0">
                            <small class="text-muted d-block">Materials</small>
                            <a href="{{ $event->materials_url }}" target="_blank">{{ $event->materials_url }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stats Card --}}
        <div class="col-lg-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Statistics</div>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h3 class="mb-0 text-primary">{{ $event->sessions_count }}</h3>
                            <small class="text-muted">Sessions</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h3 class="mb-0 text-info">{{ $event->registrations_count }}</h3>
                            <small class="text-muted">Registrations</small>
                        </div>
                    </div>

                    @php
                        $statusCounts = $event->registrations()
                            ->selectRaw('enablement_registrations.status, count(*) as count')
                            ->groupBy('enablement_registrations.status')
                            ->pluck('count', 'enablement_registrations.status');
                    @endphp

                    @if($statusCounts->isNotEmpty())
                        <hr>
                        <h6 class="text-muted mb-2">Registration Breakdown</h6>
                        @foreach($statusCounts as $status => $count)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-{{ \App\Models\EnablementRegistration::STATUSES[$status]['color'] ?? 'secondary' }}">
                                    {{ \App\Models\EnablementRegistration::STATUSES[$status]['label'] ?? ucfirst($status) }}
                                </span>
                                <strong>{{ $count }}</strong>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Sessions Table --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        <i class="fal fa-calendar me-2"></i> Sessions
                    </div>
                    <a href="{{ route('enablement-events.sessions.create', $event->id) }}" class="btn btn-primary btn-sm">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                        Add Session
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped w-100" id="sessions-table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Title Override</th>
                                    <th>Location</th>
                                    <th>Instructor</th>
                                    <th>Geography</th>
                                    <th>Registrations</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $session)
                                    <tr>
                                        <td class="text-nowrap">
                                            @if($session->all_day)
                                                {{ $session->start_at->format('M d, Y') }}
                                                <br><small class="text-muted">All Day</small>
                                            @else
                                                {{ $session->start_at->format('M d, Y') }}
                                                <br><small class="text-muted">{{ $session->start_at->format('g:i A') }}{{ $session->end_at ? ' - ' . $session->end_at->format('g:i A') : '' }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $session->title_override ?? '—' }}</td>
                                        <td>
                                            {{ $session->location ?? '—' }}
                                            @if($session->meeting_url)
                                                <br><a href="{{ $session->meeting_url }}" target="_blank" class="small">Meeting Link</a>
                                            @endif
                                        </td>
                                        <td>{{ $session->instructor ?? '—' }}</td>
                                        <td>{{ $session->geography ?? '—' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('enablement-sessions.registrations', $session->id) }}" class="badge bg-info text-decoration-none">
                                                {{ $session->registrations_count }}
                                                @if($session->effective_max_participants)
                                                    / {{ $session->effective_max_participants }}
                                                @endif
                                            </a>
                                        </td>
                                        <td>
                                            @if($session->status === 'scheduled')
                                                <span class="badge bg-primary">Scheduled</span>
                                            @elseif($session->status === 'in_progress')
                                                <span class="badge bg-warning text-dark">In Progress</span>
                                            @elseif($session->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('enablement-sessions.registrations', $session->id) }}" class="btn btn-sm btn-outline-info" title="View Registrations">
                                                <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#users"></use></svg>
                                            </a>
                                            <a href="{{ route('enablement-sessions.edit', $session->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Session">
                                                <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit"></use></svg>
                                            </a>
                                            <form action="{{ route('enablement-sessions.destroy', $session->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this session and all its registrations?')" title="Delete">
                                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#trash-2"></use></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            No sessions scheduled yet.
                                            <a href="{{ route('enablement-events.sessions.create', $event->id) }}">Add the first session</a>.
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
@endsection
