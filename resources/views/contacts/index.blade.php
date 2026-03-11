@extends('layouts.vertical', ['pageTitle' => 'Contact Directory'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle' => 'Contact Directory', 'pageSubTitle1' => 'Partners', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('css')
<style>
.contact-card {
    border: 1px solid rgba(0,0,0,.08);
    transition: box-shadow .15s ease, transform .15s ease;
}
.contact-card:hover {
    box-shadow: 0 6px 24px rgba(0,0,0,.1) !important;
    transform: translateY(-2px);
}
.contact-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.contact-avatar-initials {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.4rem;
    color: #fff;
    flex-shrink: 0;
}
.contact-stat-label {
    font-size: 9px;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: #9ca3af;
    font-weight: 700;
    display: block;
    margin-bottom: 2px;
}
.contact-stat-value {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1;
}
.view-profile-link {
    font-size: .8rem;
    font-weight: 600;
    text-decoration: none;
}
.view-profile-link:hover { text-decoration: underline; }
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
        $typeColors = [
            'general'    => ['bg' => '#6b7280', 'badge_bg' => '#f3f4f6', 'badge_text' => '#374151', 'link' => '#6b7280'],
            'influencer' => ['bg' => '#d97706', 'badge_bg' => '#fef3c7', 'badge_text' => '#92400e', 'link' => '#d97706'],
            'resource'   => ['bg' => '#059669', 'badge_bg' => '#d1fae5', 'badge_text' => '#065f46', 'link' => '#059669'],
        ];
        $tc = $typeColors[$contact->contact_type] ?? $typeColors['general'];
        $initials = strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1));
        $typeLabel = ucfirst($contact->contact_type);
        $roleLabel = match($contact->contact_type) {
            'influencer' => \App\Models\Contact::INFLUENCER_ROLES[$contact->influencer_role] ?? null,
            'resource'   => \App\Models\Contact::RESOURCE_ROLES[$contact->resource_role] ?? null,
            default      => null,
        };
        $partnerName = $contact->partner->name ?? '';
        // Use notes, LinkedIn headline, or role as the card bio line
        $bio = $contact->linkedin_headline
            ?: ($contact->notes ? \Illuminate\Support\Str::limit($contact->notes, 100) : null);
    @endphp
    <div class="col-md-6 col-xl-4 contact-item"
         data-type="{{ $contact->contact_type }}"
         data-partner="{{ $contact->partner_id }}"
         data-search="{{ strtolower($contact->first_name . ' ' . $contact->last_name . ' ' . $partnerName . ' ' . $contact->title . ' ' . $contact->email) }}">
        <div class="card shadow-0 contact-card mb-0 d-flex flex-column h-100">
            <div class="card-body flex-grow-1 pb-2">

                {{-- Header: avatar + name/title + menu --}}
                <div class="d-flex align-items-start gap-3 mb-3">
                    @if($contact->linkedin_image_url)
                        <img src="{{ $contact->linkedin_image_url }}" alt="" class="contact-avatar">
                    @elseif($contact->avatar)
                        <img src="{{ asset($contact->avatar) }}" alt="" class="contact-avatar">
                    @else
                        <div class="contact-avatar-initials" style="background:{{ $tc['bg'] }};">{{ $initials }}</div>
                    @endif

                    <div class="flex-grow-1 min-width-0 pt-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <p class="fw-700 mb-0" style="font-size:.95rem; line-height:1.3;">
                                    {{ $contact->first_name }} {{ $contact->last_name }}
                                </p>
                                <p class="text-muted mb-2" style="font-size:.8rem;">{{ $contact->title ?? '—' }}</p>
                            </div>
                            {{-- ⋮ menu --}}
                            <div class="dropdown">
                                <button class="btn btn-link btn-sm p-0 text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fal fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @if($contact->email)
                                    <li><a class="dropdown-item fs-xs" href="mailto:{{ $contact->email }}"><i class="fal fa-envelope me-2"></i>Send Email</a></li>
                                    @endif
                                    @if($contact->phone)
                                    <li><a class="dropdown-item fs-xs" href="tel:{{ $contact->phone }}"><i class="fal fa-phone me-2"></i>{{ $contact->phone }}</a></li>
                                    @endif
                                    @if($contact->linkedin_url)
                                    <li><a class="dropdown-item fs-xs" href="{{ $contact->linkedin_url }}" target="_blank"><i class="fab fa-linkedin me-2"></i>LinkedIn</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item fs-xs" href="{{ route('partners.show', $contact->partner_id) }}"><i class="fal fa-building me-2"></i>View Partner</a></li>
                                    @if($contact->contact_type === 'influencer')
                                    <li><a class="dropdown-item fs-xs" href="{{ route('influencers.show', $contact) }}"><i class="fal fa-user me-2"></i>View Profile</a></li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        {{-- Type + role badge --}}
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge rounded-pill fs-xs fw-600 px-2"
                                  style="background:{{ $tc['badge_bg'] }};color:{{ $tc['badge_text'] }};">
                                {{ $typeLabel }}{{ $roleLabel ? ' · ' . $roleLabel : '' }}
                            </span>
                            @if($contact->portal_access)
                                <span class="badge rounded-pill bg-info bg-opacity-15 text-info fs-xs fw-600 px-2">Portal</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Bio / headline --}}
                <p class="text-muted mb-2" style="font-size:.8rem; min-height:2.4rem; line-height:1.5;">
                    {{ $bio ?? 'Works at ' . $partnerName . '.' }}
                </p>

                {{-- View partner link --}}
                <a href="{{ route('partners.show', $contact->partner_id) }}"
                   class="view-profile-link" style="color:{{ $tc['link'] }};">
                    {{ $partnerName }} <i class="fal fa-arrow-right ms-1" style="font-size:.7rem;"></i>
                </a>
            </div>

            {{-- Stats footer --}}
            <div class="card-footer bg-transparent border-top py-2 px-0">
                <div class="row g-0 text-center">
                    <div class="col border-end">
                        <span class="contact-stat-label">Projects</span>
                        <span class="contact-stat-value d-block">{{ $contact->projects_count }}</span>
                    </div>
                    <div class="col border-end">
                        <span class="contact-stat-label">Certs</span>
                        <span class="contact-stat-value d-block">{{ $contact->certifications_count }}</span>
                    </div>
                    <div class="col">
                        <span class="contact-stat-label">Interactions</span>
                        <span class="contact-stat-value d-block">{{ $contact->interactions_count }}</span>
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
