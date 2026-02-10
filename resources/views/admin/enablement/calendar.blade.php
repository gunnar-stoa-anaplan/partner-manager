@extends('layouts.vertical', ['pageTitle' => 'Enablement Calendar (Admin)'])

@section('css')
<style>
    #draggable-events .fc-event {
        cursor: grab;
        padding: 6px 10px;
        margin-bottom: 6px;
        border-radius: 4px;
        font-size: 0.85rem;
        color: #fff;
        border: none;
    }
    #draggable-events .fc-event:active { cursor: grabbing; }
    #draggable-events .fc-event .event-meta {
        font-size: 0.75rem;
        opacity: 0.85;
    }
</style>
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Enablement Calendar', 'pageSubTitle1' => 'Administration', 'pageSubText' => 'Drag published events onto the calendar to schedule sessions'])
@endsection

@section('content')
    <div class="row mb-4">
        {{-- Calendar --}}
        <div class="col-lg-9">
            <div class="card shadow-0">
                <div class="card-body">
                    <div id="enablement-calendar"></div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-3">
            {{-- Draggable Published Events --}}
            <div class="card shadow-0 mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Published Events</h5>
                    <a href="{{ route('enablement-events.create') }}" class="btn btn-xs btn-primary" title="Create New Event">
                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#plus"></use></svg>
                    </a>
                </div>
                <div class="card-body p-2">
                    <div class="form-text mb-2 px-1">Drag an event onto the calendar to schedule a session.</div>
                    <div id="draggable-events">
                        @php
                            $publishedEvents = \App\Models\EnablementEvent::where('status', 'published')
                                ->orderBy('title')
                                ->get();
                        @endphp
                        @forelse($publishedEvents as $event)
                            <div class="fc-event"
                                 data-event-id="{{ $event->id }}"
                                 data-event-type="{{ $event->type }}"
                                 data-duration="{{ $event->duration_hours ? ($event->duration_hours * 60) : 60 }}"
                                 style="background-color: {{ \App\Models\EnablementEvent::TYPE_COLORS[$event->type] ?? '#868e96' }}">
                                <div class="fw-500">{{ $event->title }}</div>
                                <div class="event-meta">
                                    {{ \App\Models\EnablementEvent::TYPES[$event->type] ?? $event->type }}
                                    @if($event->duration_hours)
                                        &middot; {{ $event->duration_hours }}h
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-2 mb-0">
                                <small>No published events. <a href="{{ route('enablement-events.create') }}">Create one</a>.</small>
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card shadow-0 mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('enablement-events.index') }}" class="btn btn-outline-primary btn-sm w-100">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#list"></use></svg>
                        Event Catalog
                    </a>
                </div>
            </div>

            {{-- Type Filter --}}
            <div class="card shadow-0 mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Filter by Type</h5>
                    <button class="btn btn-xs btn-link p-0" id="toggleAllTypes">All</button>
                </div>
                <div class="card-body">
                    <div id="type-filters">
                        @foreach(\App\Models\EnablementEvent::TYPES as $typeKey => $typeLabel)
                            <div class="form-check">
                                <input class="form-check-input type-filter" type="checkbox"
                                       value="{{ $typeKey }}" id="type-filter-{{ $typeKey }}" checked>
                                <label class="form-check-label" for="type-filter-{{ $typeKey }}">
                                    <span class="badge me-1" style="background-color: {{ \App\Models\EnablementEvent::TYPE_COLORS[$typeKey] ?? '#868e96' }}; width: 10px; height: 10px; display: inline-block; border-radius: 50%; padding: 0;"></span>
                                    {{ $typeLabel }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Geo Filter --}}
            <div class="card shadow-0 mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Filter by Geo</h5>
                    <button class="btn btn-xs btn-link p-0" id="toggleAllGeos">All</button>
                </div>
                <div class="card-body">
                    <div id="geo-filters">
                        @foreach(\App\Models\EnablementSession::GEOGRAPHIES as $geoKey => $geoLabel)
                            <div class="form-check">
                                <input class="form-check-input geo-filter" type="checkbox"
                                       value="{{ $geoKey }}" id="geo-filter-{{ $geoKey }}" checked>
                                <label class="form-check-label" for="geo-filter-{{ $geoKey }}">
                                    {{ $geoLabel }}
                                </label>
                            </div>
                        @endforeach
                        <div class="form-check">
                            <input class="form-check-input geo-filter" type="checkbox"
                                   value="_none" id="geo-filter-_none" checked>
                            <label class="form-check-label text-muted" for="geo-filter-_none">
                                <em>No Geo Set</em>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Geo Selection Modal (shown when drag-dropping events onto calendar) --}}
    <div class="modal fade" id="geoSelectModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Geography</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-2">Which geography is this session for?</p>
                    <select class="form-select" id="geoSelectValue">
                        <option value="">-- None --</option>
                        @foreach(\App\Models\EnablementSession::GEOGRAPHIES as $geoKey => $geoLabel)
                            <option value="{{ $geoKey }}">{{ $geoLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" id="geoSelectConfirm">Schedule Session</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100" id="toast-container"></div>
@endsection

@section('scripts')
    <script>
        window.enablementCalendarConfig = {
            mode: 'admin',
            eventTypes: @json(\App\Models\EnablementEvent::TYPES),
            typeColors: @json(\App\Models\EnablementEvent::TYPE_COLORS),
            geographies: @json(\App\Models\EnablementSession::GEOGRAPHIES),
            apiBaseUrl: '{{ url("/api") }}',
            csrfToken: '{{ csrf_token() }}',
            sessionDetailBaseUrl: '{{ url("/enablement-sessions") }}',
            eventDetailBaseUrl: '{{ url("/enablement-events") }}',
        };
    </script>
    @vite(['resources/scripts/pages/enablement-calendar.js'])
@endsection
