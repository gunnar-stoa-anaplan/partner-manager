@extends('layouts.vertical', ['pageTitle' => $partner->name . ' - Calendar'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $partner->name, 'pageSubTitle1' => 'Engage', 'pageSubTitle1Url' => route('welcome'), 'pageSubTitle2' => 'Field Engagements', 'pageSubTitle2Url' => route('calendar.portfolio'), 'pageSubText' => 'Events for ' . $partner->name])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">This calendar shows all scheduled events and milestones for this specific partner. Use it to track upcoming meetings, enablement sessions, QBRs, and key dates — ensuring you stay on top of the engagement rhythm and never miss a critical touchpoint.</p>
    <div class="row mb-4">
        <div class="col-lg-8 col-xl-9">
            <div class="card">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-xl-3">

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary btn-sm w-100 mb-2" id="addEventBtn">
                        <i class="fal fa-plus me-1"></i> New Event
                    </button>
                    <a href="{{ route('partners.show', $partner->id) }}" class="btn btn-outline-default btn-sm w-100">
                        <i class="fal fa-arrow-left me-1"></i> Back to Partner
                    </a>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Filter by Type</h5>
                    <button class="btn btn-xs btn-link p-0" id="toggleAllTypes">All</button>
                </div>
                <div class="card-body">
                    <div id="type-filters"></div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upcoming Events</h5>
                </div>
                <div class="card-body">
                    <div id="upcoming-events-list">
                        <p class="text-muted small">Loading...</p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#award"></use></svg>
                        Enablement
                    </h5>
                    <a href="{{ route('partners.enablement', $partner->id) }}" class="btn btn-xs btn-outline-info" title="Full Enablement Calendar">
                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#calendar"></use></svg>
                    </a>
                </div>
                <div class="card-body">
                    <div id="enablement-sessions-list">
                        <p class="text-muted small">Loading...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100" id="toast-container"></div>

    @include('calendar.partials.event-modal')
    @include('calendar.partials.delete-modal')
@endsection

@section('scripts')
    <script>
        window.calendarConfig = {
            mode: 'partner',
            partnerId: {{ $partner->id }},
            partners: [@json($partner->only('id', 'name'))],
            contacts: @json($partner->contacts->map(fn($c) => ['id' => $c->id, 'name' => $c->first_name . ' ' . $c->last_name])),
            opportunities: @json($partner->opportunities->map(fn($o) => ['id' => $o->id, 'name' => $o->opportunity_name])),
            projects: @json($partner->projects->map(fn($p) => ['id' => $p->id, 'name' => $p->name])),
            eventTypes: @json(\App\Models\PartnerEvent::TYPES),
            typeColors: @json(\App\Models\PartnerEvent::TYPE_COLORS),
            apiBaseUrl: '{{ url("/api") }}',
            csrfToken: '{{ csrf_token() }}',
            enablementTypes: @json(\App\Models\EnablementEvent::TYPES),
            enablementTypeColors: @json(\App\Models\EnablementEvent::TYPE_COLORS),
            enablementCalendarUrl: '{{ route('partners.enablement', $partner->id) }}',
        };
    </script>
    @vite(['resources/scripts/pages/partner-calendar.js'])
@endsection
