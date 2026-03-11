@extends('layouts.vertical', ['pageTitle' => 'Contact Directory'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle' => 'Contact Directory', 'pageSubTitle1' => 'Partners', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('css')
<style>
.contact-card {
    transition: box-shadow 0.15s ease, transform 0.15s ease;
    border: 1px solid rgba(0,0,0,.06);
}
.contact-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,.12) !important;
    transform: translateY(-2px);
}
.contact-card .card-header-bg {
    height: 72px;
    border-radius: 4px 4px 0 0;
}
.contact-card .avatar-wrap {
    margin-top: -32px;
    position: relative;
    z-index: 1;
}
.contact-card .avatar-wrap img,
.contact-card .avatar-wrap .avatar-initials {
    width: 64px;
    height: 64px;
    border: 3px solid #fff;
    border-radius: 50%;
    object-fit: cover;
}
.contact-card .avatar-wrap .avatar-initials {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 600;
    color: #fff;
}
.contact-card .contact-meta a {
    color: inherit;
    text-decoration: none;
}
.contact-card .contact-meta a:hover {
    text-decoration: underline;
}
.type-tab.active {
    background: var(--bs-primary) !important;
    color: #fff !important;
    border-color: var(--bs-primary) !important;
}
</style>
@endsection

@section('content')

{{-- Stats --}}
<div class="row mb-g mt-3">
    <div class="col-6 col-md-3">
        <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white">
            <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($stats['total']) }}</h3>
            <span class="m-0 l-h-n">All Contacts</span>
            <i class="fal fa-address-book position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 5rem;"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="p-3 bg-faded rounded overflow-hidden position-relative">
            <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($stats['general']) }}</h3>
            <span class="m-0 l-h-n text-muted">General</span>
            <i class="fal fa-user position-absolute pos-right pos-bottom opacity-10 mb-n1 me-n1" style="font-size: 5rem;"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="p-3 bg-warning-100 rounded overflow-hidden position-relative">
            <h3 class="display-4 d-block l-h-n m-0 fw-500 text-warning-700">{{ number_format($stats['influencer']) }}</h3>
            <span class="m-0 l-h-n text-warning-700">Influencers</span>
            <i class="fal fa-star position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1 text-warning" style="font-size: 5rem;"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="p-3 bg-success-100 rounded overflow-hidden position-relative">
            <h3 class="display-4 d-block l-h-n m-0 fw-500 text-success-700">{{ number_format($stats['resource']) }}</h3>
            <span class="m-0 l-h-n text-success-700">Resources</span>
            <i class="fal fa-hard-hat position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1 text-success" style="font-size: 5rem;"></i>
        </div>
    </div>
</div>

{{-- Toolbar --}}
<div class="d-flex flex-wrap align-items-center gap-2 mb-g">
    {{-- Type filter tabs --}}
    <div class="btn-group btn-group-sm" role="group" id="type-filters">
        <button type="button" class="btn btn-outline-primary type-tab active" data-type="all">All</button>
        <button type="button" class="btn btn-outline-primary type-tab" data-type="general">General</button>
        <button type="button" class="btn btn-outline-primary type-tab" data-type="influencer">Influencer</button>
        <button type="button" class="btn btn-outline-primary type-tab" data-type="resource">Resource</button>
    </div>

    {{-- Partner filter --}}
    <select id="partner-filter" class="form-select form-select-sm" style="max-width: 220px;">
        <option value="">All Partners</option>
        @foreach($partners as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
        @endforeach
    </select>

    {{-- Search --}}
    <div class="input-group input-group-sm ms-auto" style="max-width: 240px;">
        <span class="input-group-text"><i class="fal fa-search"></i></span>
        <input type="text" id="contact-search" class="form-control" placeholder="Search contacts…">
    </div>

    {{-- Result count --}}
    <span class="text-muted fs-xs" id="result-count"></span>
</div>

{{-- Card Grid --}}
<div class="row g-3" id="contacts-grid">
    @foreach($contacts as $contact)
    @php
        $typeColors = [
            'general'    => ['bg' => '#6c757d', 'badge' => 'bg-secondary'],
            'influencer' => ['bg' => '#f59e0b', 'badge' => 'bg-warning text-dark'],
            'resource'   => ['bg' => '#198754', 'badge' => 'bg-success'],
        ];
        $color = $typeColors[$contact->contact_type] ?? $typeColors['general'];

        $initials = strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1));

        $roleLabel = match($contact->contact_type) {
            'influencer' => \App\Models\Contact::INFLUENCER_ROLES[$contact->influencer_role] ?? null,
            'resource'   => \App\Models\Contact::RESOURCE_ROLES[$contact->resource_role] ?? null,
            default      => null,
        };

        $partnerName = $contact->partner->name ?? '';
    @endphp
    <div class="col-sm-6 col-lg-4 col-xl-3 contact-item"
         data-type="{{ $contact->contact_type }}"
         data-partner="{{ $contact->partner_id }}"
         data-search="{{ strtolower($contact->first_name . ' ' . $contact->last_name . ' ' . $partnerName . ' ' . $contact->title . ' ' . $contact->email) }}">
        <div class="card shadow-0 contact-card h-100 mb-0">
            {{-- Colored header band --}}
            <div class="card-header-bg" style="background: {{ $color['bg'] }}; opacity: 0.85;"></div>

            <div class="card-body pt-0 text-center">
                {{-- Avatar --}}
                <div class="avatar-wrap d-flex justify-content-center mb-2">
                    @if($contact->avatar)
                        <img src="{{ asset($contact->avatar) }}" alt="{{ $contact->first_name }}">
                    @elseif($contact->linkedin_image_url)
                        <img src="{{ $contact->linkedin_image_url }}" alt="{{ $contact->first_name }}">
                    @else
                        <div class="avatar-initials" style="background: {{ $color['bg'] }};">{{ $initials }}</div>
                    @endif
                </div>

                {{-- Name & title --}}
                <h6 class="mb-0 fw-600">{{ $contact->first_name }} {{ $contact->last_name }}</h6>
                @if($contact->title)
                    <p class="text-muted fs-xs mb-1">{{ $contact->title }}</p>
                @endif

                {{-- Partner --}}
                <p class="fs-xs mb-2">
                    <a href="{{ route('partners.show', $contact->partner_id) }}" class="text-primary fw-500">
                        {{ $partnerName }}
                    </a>
                </p>

                {{-- Type + role badges --}}
                <div class="d-flex justify-content-center gap-1 flex-wrap mb-3">
                    <span class="badge {{ $color['badge'] }} fs-xs text-capitalize">{{ $contact->contact_type }}</span>
                    @if($roleLabel)
                        <span class="badge bg-light text-dark border fs-xs">{{ $roleLabel }}</span>
                    @endif
                    @if($contact->portal_access)
                        <span class="badge bg-info-200 text-info-800 fs-xs">Portal</span>
                    @endif
                </div>

                {{-- Contact info --}}
                <div class="contact-meta text-start fs-xs border-top pt-2">
                    @if($contact->email)
                    <div class="d-flex align-items-center gap-2 mb-1 text-truncate">
                        <i class="fal fa-envelope text-muted" style="width:14px;"></i>
                        <a href="mailto:{{ $contact->email }}" class="text-truncate">{{ $contact->email }}</a>
                    </div>
                    @endif
                    @if($contact->phone)
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fal fa-phone text-muted" style="width:14px;"></i>
                        <a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a>
                    </div>
                    @endif
                    @if($contact->linkedin_url)
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fab fa-linkedin text-primary" style="width:14px;"></i>
                        <a href="{{ $contact->linkedin_url }}" target="_blank" rel="noopener">LinkedIn Profile</a>
                    </div>
                    @endif
                    @if(!$contact->email && !$contact->phone && !$contact->linkedin_url)
                    <span class="text-muted fst-italic">No contact info</span>
                    @endif
                </div>
            </div>

            {{-- Footer actions --}}
            <div class="card-footer bg-transparent border-top-0 pt-0 pb-2 px-3">
                <div class="d-flex gap-1">
                    <a href="{{ route('partners.show', $contact->partner_id) }}"
                       class="btn btn-xs btn-outline-primary flex-grow-1" title="View Partner">
                        <i class="fal fa-building me-1"></i> Partner
                    </a>
                    @if($contact->contact_type === 'influencer')
                    <a href="{{ route('influencers.show', $contact) }}"
                       class="btn btn-xs btn-outline-secondary" title="View Profile">
                        <i class="fal fa-user"></i>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Empty state --}}
<div id="no-results" class="text-center py-5 text-muted d-none">
    <i class="fal fa-search fa-3x mb-3 opacity-50"></i>
    <p class="mb-0">No contacts match your filters.</p>
</div>

@endsection

@section('scripts')
<script>
(function () {
    var items = Array.from(document.querySelectorAll('.contact-item'));
    var searchEl = document.getElementById('contact-search');
    var partnerEl = document.getElementById('partner-filter');
    var typeTabs = document.querySelectorAll('.type-tab');
    var noResults = document.getElementById('no-results');
    var resultCount = document.getElementById('result-count');
    var activeType = 'all';

    function applyFilters() {
        var search = searchEl.value.toLowerCase().trim();
        var partner = partnerEl.value;
        var visible = 0;

        items.forEach(function (el) {
            var matchType    = activeType === 'all' || el.dataset.type === activeType;
            var matchPartner = !partner || el.dataset.partner === partner;
            var matchSearch  = !search || el.dataset.search.includes(search);
            var show = matchType && matchPartner && matchSearch;
            el.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        noResults.classList.toggle('d-none', visible > 0);
        resultCount.textContent = visible + ' contact' + (visible !== 1 ? 's' : '');
    }

    typeTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            typeTabs.forEach(function (t) { t.classList.remove('active'); });
            this.classList.add('active');
            activeType = this.dataset.type;
            applyFilters();
        });
    });

    searchEl.addEventListener('input', applyFilters);
    partnerEl.addEventListener('change', applyFilters);

    applyFilters();
})();
</script>
@endsection
