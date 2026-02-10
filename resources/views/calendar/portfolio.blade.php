@extends('layouts.vertical', ['pageTitle' => 'Partner Calendar'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Partner Calendar', 'pageSubTitle1' => 'Events', 'pageSubText' => 'Events across all your partners'])
@endsection

@section('content')
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
                    <button class="btn btn-primary btn-sm w-100" id="addEventBtn">
                        <i class="fal fa-plus me-1"></i> New Event
                    </button>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Filter by Partner</h5>
                    <button class="btn btn-xs btn-link p-0" id="toggleAllPartners">All</button>
                </div>
                <div class="card-body">
                    <div id="partner-filters">
                        @foreach($partners as $p)
                        <div class="form-check">
                            <input class="form-check-input partner-filter" type="checkbox"
                                   value="{{ $p->id }}" id="partner-filter-{{ $p->id }}" checked>
                            <label class="form-check-label" for="partner-filter-{{ $p->id }}">
                                {{ $p->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
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

        </div>
    </div>

    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100" id="toast-container"></div>

    @include('calendar.partials.event-modal')
    @include('calendar.partials.delete-modal')
@endsection

@section('scripts')
    <script>
        window.calendarConfig = {
            mode: 'portfolio',
            partnerId: null,
            partners: @json($partners),
            contacts: [],
            opportunities: [],
            projects: [],
            eventTypes: @json(\App\Models\PartnerEvent::TYPES),
            typeColors: @json(\App\Models\PartnerEvent::TYPE_COLORS),
            enablementTypes: @json(\App\Models\EnablementEvent::TYPES),
            enablementTypeColors: @json(\App\Models\EnablementEvent::TYPE_COLORS),
            enablementCalendarUrl: '{{ route('enablement.calendar') }}',
            apiBaseUrl: '{{ url("/api") }}',
            csrfToken: '{{ csrf_token() }}',
        };
    </script>
    @vite(['resources/scripts/pages/partner-calendar.js'])
@endsection
