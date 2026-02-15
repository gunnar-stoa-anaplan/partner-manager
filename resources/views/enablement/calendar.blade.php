@extends('layouts.vertical', ['pageTitle' => 'Enablement Calendar'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Enablement Calendar', 'pageSubTitle1' => 'Partner Enablement', 'pageSubTitle1Url' => route('welcome'), 'pageSubText' => 'Browse upcoming enablement sessions and nominate your partners\' contacts'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Browse upcoming enablement sessions across all your partners. Use the calendar to find sessions by date, filter by event type or geography, and nominate partner contacts directly from the session details. This is your central hub for managing your partners' enablement participation.</p>

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
            <div class="card shadow-0 mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('enablement.my-nominations') }}" class="btn btn-outline-info btn-sm w-100">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#list"></use></svg>
                        My Nominations
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

            {{-- Upcoming Sessions --}}
            <div class="card shadow-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upcoming Sessions</h5>
                </div>
                <div class="card-body p-0">
                    <div id="upcoming-sessions-list" class="list-group list-group-flush">
                        <div class="list-group-item text-center text-muted py-3">
                            <small>Loading...</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Session Detail Modal --}}
    @include('enablement.partials.session-detail-modal')

    {{-- Nomination Modal --}}
    @include('enablement.partials.nomination-modal')

    {{-- Toast Container --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100" id="toast-container"></div>
@endsection

@section('scripts')
    <script>
        window.enablementCalendarConfig = {
            mode: 'psm',
            partners: @json((auth()->user()->role === 'admin' ? \App\Models\Partner::query() : \App\Models\Partner::where('user_id', auth()->id()))->select('id', 'name')->orderBy('name')->get()),
            eventTypes: @json(\App\Models\EnablementEvent::TYPES),
            typeColors: @json(\App\Models\EnablementEvent::TYPE_COLORS),
            apiBaseUrl: '{{ url("/api") }}',
            csrfToken: '{{ csrf_token() }}',
        };
    </script>
    @vite(['resources/scripts/pages/enablement-calendar.js'])
@endsection
