@extends('layouts.vertical', ['pageTitle' => 'Contact Directory'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle' => 'Contact Directory', 'pageSubTitle1' => 'Partners', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('css')
<style>
.contact-card {
    border: 1px solid rgba(0,0,0,.07);
    transition: box-shadow .15s ease, transform .15s ease;
}
.contact-card:hover {
    box-shadow: 0 4px 18px rgba(0,0,0,.1) !important;
    transform: translateY(-1px);
}
.contact-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.contact-avatar-initials {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.1rem;
    color: #fff;
    flex-shrink: 0;
}
.contact-stat-label {
    font-size: 9px;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #9ca3af;
    font-weight: 600;
}
.contact-stat-value {
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.2;
    color: #374151;
}
.type-tab.active {
    background: var(--bs-primary) !important;
    color: #fff !important;
    border-color: var(--bs-primary) !important;
}
</style>
@endsection

@section('content')

{{-- Stats row --}}
<div class="row mb-g mt-3">
    <div class="col-6 col-md-3">
        <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white">
            <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($stats['total']) }}</h3>
            <span class="l-h-n">All Contacts</span>
            <i class="fal fa-address-book position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1" style="font-size: 5rem;"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="p-3 bg-faded rounded overflow-hidden position-relative">
            <h3 class="display-4 d-block l-h-n m-0 fw-500">{{ number_format($stats['general']) }}</h3>
            <span class="l-h-n text-muted">General</span>
            <i class="fal fa-user position-absolute pos-right pos-bottom opacity-10 mb-n1 me-n1" style="font-size: 5rem;"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="p-3 bg-warning-100 rounded overflow-hidden position-relative">
            <h3 class="display-4 d-block l-h-n m-0 fw-500 text-warning-700">{{ number_format($stats['influencer']) }}</h3>
            <span class="l-h-n text-warning-700">Influencers</span>
            <i class="fal fa-star position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1 text-warning" style="font-size: 5rem;"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="p-3 bg-success-100 rounded overflow-hidden position-relative">
            <h3 class="display-4 d-block l-h-n m-0 fw-500 text-success-700">{{ number_format($stats['resource']) }}</h3>
            <span class="l-h-n text-success-700">Resources</span>
            <i class="fal fa-hard-hat position-absolute pos-right pos-bottom opacity-15 mb-n1 me-n1 text-success" style="font-size: 5rem;"></i>
        </div>
    </div>
</div>

{{-- Toolbar --}}
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <div class="btn-group btn-group-sm" role="group">
        <button type="button" class="btn btn-outline-primary type-tab active" data-type="all">All</button>
        <button type="button" class="btn btn-outline-primary type-tab" data-type="general">General</button>
        <button type="button" class="btn btn-outline-primary type-tab" data-type="influencer">Influencer</button>
        <button type="button" class="btn btn-outline-primary type-tab" data-type="resource">Resource</button>
    </div>

    <select id="partner-filter" class="form-select form-select-sm" style="max-width: 220px;">
        <option value="">All Partners</option>
        @foreach($partners as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
        @endforeach
    </select>

    <div class="input-group input-group-sm ms-auto" style="max-width: 240px;">
        <span class="input-group-text"><i class="fal fa-search"></i></span>
        <input type="text" id="contact-search" class="form-control" placeholder="Search contacts…">
    </div>

    <span class="text-muted fs-xs text-nowrap" id="result-count"></span>
</div>

{{-- Card grid --}}
<div class="row g-3" id="contacts-grid">
    @foreach($contacts as $contact)
    @php
        $avatarColors = [
            'general'    => '#6b7280',
            'influencer' => '#d97706',
            'resource'   => '#059669',
        ];
        $bgColor = $avatarColors[$contact->contact_type] ?? '#6b7280';
        $initials = strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1));

        $typeLabels = ['general' => 'General', 'influencer' => 'Influencer', 'resource' => 'Resource'];
        $typeBadges = [
            'general'    => 'bg-secondary bg-opacity-15 text-secondary',
            'influencer' => 'bg-warning bg-opacity-15 text-warning-700',
            'resource'   => 'bg-success bg-opacity-15 text-success-700',
        ];
        $roleLabel = match($contact->contact_type) {
            'influencer' => \App\Models\Contact::INFLUENCER_ROLES[$contact->influencer_role] ?? null,
            'resource'   => \App\Models\Contact::RESOURCE_ROLES[$contact->resource_role] ?? null,
            default      => null,
        };
        $partnerName = $contact->partner->name ?? '';
    @endphp
    <div class="col-md-6 col-xl-4 contact-item"
         data-type="{{ $contact->contact_type }}"
         data-partner="{{ $contact->partner_id }}"
         data-search="{{ strtolower($contact->first_name . ' ' . $contact->last_name . ' ' . $partnerName . ' ' . $contact->title . ' ' . $contact->email) }}">
        <div class="card shadow-0 contact-card mb-0 h-100">
            <div class="card-body">
                {{-- Top: avatar + name/title/badges --}}
                <div class="d-flex align-items-start gap-3 mb-3">
                    @if($contact->avatar)
                        <img src="{{ asset($contact->avatar) }}" alt="{{ $contact->first_name }}" class="contact-avatar">
                    @elseif($contact->linkedin_image_url)
                        <img src="{{ $contact->linkedin_image_url }}" alt="{{ $contact->first_name }}" class="contact-avatar">
                    @else
                        <div class="contact-avatar-initials" style="background:{{ $bgColor }};">{{ $initials }}</div>
                    @endif

                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div>
                                <p class="fw-600 mb-0 lh-sm">{{ $contact->first_name }} {{ $contact->last_name }}</p>
                                @if($contact->title)
                                    <p class="text-muted fs-xs mb-1">{{ $contact->title }}</p>
                                @endif
                            </div>
                            @if($contact->linkedin_url)
                            <a href="{{ $contact->linkedin_url }}" target="_blank" rel="noopener" class="text-primary flex-shrink-0" title="LinkedIn">
                                <i class="fab fa-linkedin" style="font-size:1rem;"></i>
                            </a>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            <span class="badge {{ $typeBadges[$contact->contact_type] ?? $typeBadges['general'] }} fs-xs">
                                {{ $typeLabels[$contact->contact_type] ?? 'General' }}
                            </span>
                            @if($roleLabel)
                                <span class="badge bg-light text-dark border fs-xs">{{ $roleLabel }}</span>
                            @endif
                            @if($contact->portal_access)
                                <span class="badge bg-info bg-opacity-15 text-info-800 fs-xs">Portal</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Partner --}}
                <p class="fs-xs mb-2">
                    <i class="fal fa-building text-muted me-1"></i>
                    <a href="{{ route('partners.show', $contact->partner_id) }}" class="text-primary fw-500">{{ $partnerName }}</a>
                </p>

                {{-- Contact links --}}
                <div class="d-flex flex-column gap-1 fs-xs text-muted">
                    @if($contact->email)
                        <div class="d-flex align-items-center gap-2 text-truncate">
                            <i class="fal fa-envelope" style="width:14px;flex-shrink:0;"></i>
                            <a href="mailto:{{ $contact->email }}" class="text-truncate">{{ $contact->email }}</a>
                        </div>
                    @endif
                    @if($contact->phone)
                        <div class="d-flex align-items-center gap-2">
                            <i class="fal fa-phone" style="width:14px;flex-shrink:0;"></i>
                            <a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Stats footer --}}
            <div class="card-footer bg-transparent border-top py-2 px-3">
                <div class="row g-0 text-center">
                    @php
                        $certCount = $contact->certifications_count;
                        $projectCount = $contact->projects_count;
                        $interactionCount = $contact->interactions_count;
                    @endphp
                    <div class="col border-end">
                        <div class="contact-stat-label">Projects</div>
                        <div class="contact-stat-value">{{ $projectCount }}</div>
                    </div>
                    <div class="col border-end">
                        <div class="contact-stat-label">Certs</div>
                        <div class="contact-stat-value">{{ $certCount }}</div>
                    </div>
                    <div class="col">
                        <div class="contact-stat-label">Interactions</div>
                        <div class="contact-stat-value">{{ $interactionCount }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

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
            var show = (activeType === 'all' || el.dataset.type === activeType)
                    && (!partner || el.dataset.partner === partner)
                    && (!search || el.dataset.search.includes(search));
            el.style.display = show ? '' : 'none';
            if (show) { visible++; }
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
