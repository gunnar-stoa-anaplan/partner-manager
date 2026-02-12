@extends('layouts.vertical', ['pageTitle' => $partner->name . ' - Enablement'])

@section('css')
<style>
    #draggable-sessions .fc-event {
        cursor: grab;
        padding: 5px 8px;
        margin-bottom: 5px;
        border-radius: 4px;
        font-size: 0.8rem;
        color: #fff;
        border: none;
    }
    #draggable-sessions .fc-event:active { cursor: grabbing; }
    #draggable-sessions .fc-event .event-meta {
        font-size: 0.7rem;
        opacity: 0.85;
    }
</style>
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Enablement Calendar',
        'pageSubTitle1' => $partner->name,
        'pageSubText' => 'Drag sessions from the sidebar to register this partner, then click to assign contacts'
    ])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">This is the partner-specific enablement calendar showing sessions relevant to this partner. Browse upcoming sessions, nominate contacts for training, and track their registration status. Use the sidebar to filter by event type or nominate contacts directly from available sessions.</p>

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
            {{-- Actions --}}
            <div class="card shadow-0 mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ $partner->name }}</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('partners.show', $partner->id) }}" class="btn btn-outline-secondary btn-sm w-100 mb-2">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#arrow-left"></use></svg>
                        Back to Partner
                    </a>
                    <a href="{{ route('enablement.calendar') }}" class="btn btn-outline-info btn-sm w-100">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#calendar"></use></svg>
                        Global Calendar
                    </a>
                </div>
            </div>

            {{-- Available Sessions to Drag --}}
            <div class="card shadow-0 mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Available Sessions</h5>
                </div>
                <div class="card-body p-2">
                    <div class="form-text mb-2 px-1">Drag a session onto the calendar to register this partner.</div>
                    <div id="draggable-sessions">
                        <div class="text-center text-muted py-2">
                            <small>Loading sessions...</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Type Filter --}}
            <div class="card shadow-0">
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
        </div>
    </div>

    {{-- Contact Assignment Modal --}}
    <div class="modal fade" id="contactAssignModal" tabindex="-1" aria-labelledby="contactAssignTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactAssignTitle">Assign {{ $partner->name }} Contacts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="assignSessionId">

                    {{-- Session Info --}}
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h6 class="text-muted mb-1">Session</h6>
                            <h5 id="assignSessionTitle" class="mb-0"></h5>
                        </div>
                        <div class="col-md-4 text-end">
                            <span id="assignTypeBadge" class="badge"></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Date</small>
                            <strong id="assignSessionDate"></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Location</small>
                            <strong id="assignSessionLocation"></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Instructor</small>
                            <strong id="assignSessionInstructor"></strong>
                        </div>
                    </div>

                    <hr>

                    {{-- Already Assigned --}}
                    <h6 class="text-muted mb-2"><i class="fal fa-users me-1"></i> Assigned Contacts</h6>
                    <div id="assignedContactsList" class="mb-3">
                        <p class="text-muted">Loading...</p>
                    </div>

                    <hr>

                    {{-- Add Contacts --}}
                    <h6 class="text-muted mb-2"><i class="fal fa-user-plus me-1"></i> Add {{ $partner->name }} Contacts</h6>
                    <div id="availableContacts">
                        @foreach($partner->contacts as $contact)
                            <div class="form-check" id="contact-row-{{ $contact->id }}">
                                <input class="form-check-input assign-contact-cb" type="checkbox"
                                       value="{{ $contact->id }}" id="assign-contact-{{ $contact->id }}">
                                <label class="form-check-label" for="assign-contact-{{ $contact->id }}">
                                    {{ $contact->first_name }} {{ $contact->last_name }}
                                    @if($contact->email)
                                        <small class="text-muted">({{ $contact->email }})</small>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                        @if($partner->contacts->isEmpty())
                            <p class="text-muted mb-0">No contacts for this partner.</p>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger me-auto" id="removePartnerBtn">
                        <i class="fal fa-times me-1"></i> Remove from Calendar
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="assignContactsBtn" disabled>
                        <i class="fal fa-user-plus me-1"></i> Assign Selected
                    </button>
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
            mode: 'partner',
            partnerId: {{ $partner->id }},
            partnerName: @json($partner->name),
            contacts: @json($partner->contacts->map(fn($c) => ['id' => $c->id, 'name' => $c->first_name . ' ' . $c->last_name, 'email' => $c->email])),
            eventTypes: @json(\App\Models\EnablementEvent::TYPES),
            typeColors: @json(\App\Models\EnablementEvent::TYPE_COLORS),
            apiBaseUrl: '{{ url("/api") }}',
            csrfToken: '{{ csrf_token() }}',
        };
    </script>
    @vite(['resources/scripts/pages/enablement-calendar.js'])
@endsection
