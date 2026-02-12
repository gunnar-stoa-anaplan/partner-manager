{{-- Calendar Tab: Upcoming events + event stats + link to full calendar --}}

<div class="row">
    {{-- Event Stats --}}
    <div class="col-lg-4 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">Event Summary</div>
                <a href="{{ route('partners.calendar', $partner->id) }}" class="btn btn-sm btn-primary">
                    <i class="fal fa-calendar me-1"></i> Open Full Calendar
                </a>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="h3 mb-0 fw-500">{{ $upcomingEvents->count() }}</div>
                        <small class="text-muted">Upcoming</small>
                    </div>
                    <div class="col-4">
                        <div class="h3 mb-0 fw-500">{{ $pastEvents->count() }}</div>
                        <small class="text-muted">Past</small>
                    </div>
                    <div class="col-4">
                        <div class="h3 mb-0 fw-500">{{ $events->count() }}</div>
                        <small class="text-muted">Total</small>
                    </div>
                </div>

                @if($eventsByType->isNotEmpty())
                <h6 class="text-muted mb-2 fw-500 mt-4">By Type</h6>
                @foreach($eventsByType->sortDesc() as $type => $count)
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge me-2" style="background-color: {{ \App\Models\PartnerEvent::TYPE_COLORS[$type] ?? '#868e96' }}; width: 10px; height: 10px; padding: 0; border-radius: 50%;"></span>
                        <span class="fs-sm flex-grow-1">{{ \App\Models\PartnerEvent::TYPES[$type] ?? ucfirst($type) }}</span>
                        <span class="badge bg-light text-dark border">{{ $count }}</span>
                    </div>
                @endforeach
                @endif

                <div class="mt-4">
                    <a href="{{ route('partners.enablement', $partner->id) }}" class="btn btn-outline-info btn-sm w-100 mb-2">
                        <i class="fal fa-award me-1"></i> Enablement Calendar
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Upcoming Events --}}
    <div class="col-lg-8 mb-g">
        <div class="card shadow-0 h-100">
            <div class="card-header">
                <div class="card-title mb-0">Upcoming Events</div>
            </div>
            <div class="card-body p-0">
                @if($upcomingEvents->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="upcoming-events-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingEvents as $event)
                            <tr>
                                <td>
                                    <strong>{{ $event->title }}</strong>
                                    @if($event->description)
                                        <div class="fs-xs text-muted text-truncate" style="max-width: 250px;">{{ $event->description }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ \App\Models\PartnerEvent::TYPE_COLORS[$event->type] ?? '#868e96' }}">
                                        {{ \App\Models\PartnerEvent::TYPES[$event->type] ?? $event->type }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    {{ $event->start_at->format('M j, Y') }}
                                    @if(!$event->all_day)
                                        <div class="fs-xs text-muted">{{ $event->start_at->format('g:i A') }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $event->status === 'completed' ? 'success' : ($event->status === 'cancelled' ? 'danger' : 'primary') }}">
                                        {{ \App\Models\PartnerEvent::STATUSES[$event->status] ?? $event->status }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $event->location ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fal fa-calendar-times fa-3x text-muted opacity-50 mb-3"></i>
                    <h5 class="text-muted">No upcoming events</h5>
                    <p class="text-muted mb-3">Schedule meetings, QBRs, and training sessions for {{ $partner->name }}.</p>
                    <a href="{{ route('partners.calendar', $partner->id) }}" class="btn btn-primary btn-sm">
                        <i class="fal fa-plus me-1"></i> Open Calendar
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Recent Past Events --}}
@if($pastEvents->count() > 0)

<div class="row">
    <div class="col-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title mb-0">Recent Past Events</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="past-events-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Outcome</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pastEvents->sortByDesc('start_at') as $event)
                            <tr>
                                <td><strong>{{ $event->title }}</strong></td>
                                <td>
                                    <span class="badge" style="background-color: {{ \App\Models\PartnerEvent::TYPE_COLORS[$event->type] ?? '#868e96' }}">
                                        {{ \App\Models\PartnerEvent::TYPES[$event->type] ?? $event->type }}
                                    </span>
                                </td>
                                <td class="text-nowrap text-muted">{{ $event->start_at->format('M j, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $event->status === 'completed' ? 'success' : ($event->status === 'cancelled' ? 'danger' : 'secondary') }}">
                                        {{ \App\Models\PartnerEvent::STATUSES[$event->status] ?? $event->status }}
                                    </span>
                                </td>
                                <td class="text-muted">
                                    @if($event->outcome)
                                        <div class="text-truncate" style="max-width: 300px;">{{ $event->outcome }}</div>
                                    @else
                                        <span class="text-muted fst-italic">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script>
(function() {
    if (window.SmartTables) {
        if (document.querySelector('#upcoming-events-table tbody tr td:not([colspan])')) {
            new window.SmartTables('upcoming-events-table', {
                perPage: 10,
                sortable: true,
                responsive: { enabled: true, breakpoint: 768, columnPriorities: { 0: 1, 2: 2, 1: 3, 3: 4, 4: 5 } }
            });
        }
        if (document.querySelector('#past-events-table tbody tr td:not([colspan])')) {
            new window.SmartTables('past-events-table', {
                perPage: 10,
                sortable: true,
                responsive: { enabled: true, breakpoint: 768, columnPriorities: { 0: 1, 2: 2, 1: 3, 3: 4, 4: 5 } }
            });
        }
    }
})();
</script>
