@extends('layouts.vertical', ['pageTitle' => 'Contact Directory'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle' => 'Contact Directory', 'pageSubTitle1' => 'Partners', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('css')
<style>
.card-hovered {
    border: 1px solid rgba(0,0,0,.08);
    transition: box-shadow .15s ease, transform .15s ease;
}
.card-hovered:hover {
    box-shadow: 0 6px 24px rgba(0,0,0,.1) !important;
    transform: translateY(-2px);
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
        $typeBadgeColors = [
            'general'    => 'bg-secondary',
            'influencer' => 'bg-warning',
            'resource'   => 'bg-success',
        ];
        $badgeColor = $typeBadgeColors[$contact->contact_type] ?? 'bg-secondary';

        $avatarColors = [
            'general'    => '#6b7280',
            'influencer' => '#d97706',
            'resource'   => '#059669',
        ];
        $avatarBg = $avatarColors[$contact->contact_type] ?? '#6b7280';
        $initials = strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1));

        $roleLabel = match($contact->contact_type) {
            'influencer' => \App\Models\Contact::INFLUENCER_ROLES[$contact->influencer_role] ?? null,
            'resource'   => \App\Models\Contact::RESOURCE_ROLES[$contact->resource_role] ?? null,
            default      => null,
        };
        $partnerName = $contact->partner->name ?? '';
        $bio = $contact->linkedin_headline
            ?: ($contact->notes ? \Illuminate\Support\Str::limit($contact->notes, 100) : 'Works at ' . $partnerName . '.');
    @endphp
    <div class="col-md-6 col-xl-4 contact-item"
         data-type="{{ $contact->contact_type }}"
         data-partner="{{ $contact->partner_id }}"
         data-search="{{ strtolower($contact->first_name . ' ' . $contact->last_name . ' ' . $partnerName . ' ' . $contact->title . ' ' . $contact->email) }}">
        <div class="card card-hovered shadow-0 mb-0 h-100">
            <div class="card-body">

                {{-- Avatar + name + menu --}}
                <div class="d-flex align-items-center mb-4">
                    <div class="me-3 position-relative flex-shrink-0" style="width:72px;height:72px;">
                        @if($contact->linkedin_image_url)
                            <img src="{{ $contact->linkedin_image_url }}" alt="" class="rounded-circle w-100 h-100" style="object-fit:cover;">
                        @elseif($contact->avatar)
                            <img src="{{ asset($contact->avatar) }}" alt="" class="rounded-circle w-100 h-100" style="object-fit:cover;">
                        @else
                            <div class="rounded-circle w-100 h-100 d-flex align-items-center justify-content-center text-white fw-700"
                                 style="background:{{ $avatarBg }};font-size:1.4rem;">
                                {{ $initials }}
                            </div>
                        @endif
                        <span class="position-absolute bottom-0 end-0 badge {{ $badgeColor }} rounded-circle p-1 shadow-sm"
                              title="{{ ucfirst($contact->contact_type) }}">
                            <i class="fal {{ $contact->contact_type === 'resource' ? 'fa-hard-hat' : ($contact->contact_type === 'influencer' ? 'fa-star' : 'fa-user') }} text-white" style="font-size:10px;"></i>
                        </span>
                    </div>

                    <div>
                        <h5 class="mb-1 d-flex align-items-center">
                            <span class="fw-600 fs-md">{{ $contact->first_name }} {{ $contact->last_name }}</span>
                        </h5>
                        <p class="text-muted mb-0" style="font-size:.8rem;">{{ $contact->title ?? ucfirst($contact->contact_type) }}</p>
                        @if($roleLabel)
                            <p class="text-muted mb-0" style="font-size:.75rem;">{{ $roleLabel }}</p>
                        @endif
                    </div>

                    <div class="ms-auto">
                        <div class="dropdown">
                            <a href="#" class="btn btn-sm btn-light text-muted px-2 py-1" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fal fa-ellipsis-v"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($contact->email)
                                    <li><a class="dropdown-item fs-xs" href="mailto:{{ $contact->email }}"><i class="fal fa-envelope me-2"></i>{{ $contact->email }}</a></li>
                                @endif
                                @if($contact->phone)
                                    <li><a class="dropdown-item fs-xs" href="tel:{{ $contact->phone }}"><i class="fal fa-phone me-2"></i>{{ $contact->phone }}</a></li>
                                @endif
                                @if($contact->linkedin_url)
                                    <li><a class="dropdown-item fs-xs" href="{{ $contact->linkedin_url }}" target="_blank" rel="noopener"><i class="fab fa-linkedin me-2"></i>LinkedIn</a></li>
                                @endif
                                @if($contact->email || $contact->phone || $contact->linkedin_url)
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li><a class="dropdown-item fs-xs" href="{{ route('partners.show', $contact->partner_id) }}"><i class="fal fa-building me-2"></i>View Partner</a></li>
                                @if($contact->contact_type === 'influencer')
                                    <li><a class="dropdown-item fs-xs" href="{{ route('influencers.show', $contact) }}"><i class="fal fa-user me-2"></i>View Profile</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Bio --}}
                <p class="text-muted mb-2" style="font-size:.82rem; min-height:2.5rem;">{{ $bio }}</p>

                {{-- Partner link --}}
                <a href="{{ route('partners.show', $contact->partner_id) }}" class="d-inline-flex align-items-center gap-1 fw-600 text-primary text-decoration-none" style="font-size:.82rem;">
                    {{ $partnerName }} <i class="fal fa-arrow-right" style="font-size:.75rem;"></i>
                </a>
            </div>

            {{-- Stats footer --}}
            <div class="card-footer bg-transparent">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="text-muted fs-xs mb-1">PROJECTS</div>
                        <div class="fw-bold">{{ $contact->projects_count }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted fs-xs mb-1">CERTS</div>
                        <div class="fw-bold">{{ $contact->certifications_count }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted fs-xs mb-1">INTERACTIONS</div>
                        <div class="fw-bold">{{ $contact->interactions_count }}</div>
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
