@extends('layouts.vertical', ['pageTitle' => $partner->name])

@section('css')
<style>
    #partner-tabs .nav-link {
        font-weight: 500;
        padding: 0.65rem 1.25rem;
        color: var(--bs-body-color);
        border: none;
        border-bottom: 2px solid transparent;
        transition: all 0.15s ease;
    }
    #partner-tabs .nav-link:hover {
        color: var(--bs-primary);
        border-bottom-color: var(--bs-primary);
        background: transparent;
    }
    #partner-tabs .nav-link.active {
        color: var(--bs-primary);
        border-bottom-color: var(--bs-primary);
        background: transparent;
        font-weight: 600;
    }
    #partner-tabs .nav-link .badge {
        font-size: 0.65rem;
        vertical-align: middle;
    }
    .tab-pane { min-height: 200px; }
    .tab-pane .spinner-loader {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem 0;
    }
    @media (max-width: 768px) {
        #partner-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        #partner-tabs .nav-item { flex-shrink: 0; }
    }
</style>
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $partner->name, 'pageSubTitle1' => 'Partner Profile', 'pageSubTitle1Url' => route('partners.my')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The Partner Profile is your single-pane-of-glass for this partner — bringing together active projects, pipeline health, certification status, and engagement history in one place. Use it to prepare for partner meetings, identify areas that need attention, and track progress against the metrics that matter most for this relationship.</p>

    {{-- KPI Cards (always visible) --}}
    <div class="row sortable-off">
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="d-flex align-items-center justify-content-between px-3 py-2">
                    <div>
                        <p class="p-0 mb-1 text-muted text-truncate fs-sm">Active Projects</p>
                        <span class="h5 mb-0">{{ number_format($activeProjects) }}</span>
                    </div>
                    <div class="width-4 height-4 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fad fa-check-circle text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="d-flex align-items-center justify-content-between px-3 py-2">
                    <div>
                        <p class="p-0 mb-1 text-muted text-truncate fs-sm">Model Builders</p>
                        <span class="h5 mb-0">{{ number_format($certifiedModelBuilders) }}</span>
                    </div>
                    <div class="width-4 height-4 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fad fa-cogs text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="d-flex align-items-center justify-content-between px-3 py-2">
                    <div>
                        <p class="p-0 mb-1 text-muted text-truncate fs-sm">Solution Architects</p>
                        <span class="h5 mb-0">{{ number_format($certifiedSolutionArchitects) }}</span>
                    </div>
                    <div class="width-4 height-4 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fad fa-drafting-compass text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-xl-3 mb-g">
            <div class="card">
                <div class="d-flex align-items-center justify-content-between px-3 py-2">
                    <div>
                        <p class="p-0 mb-1 text-muted text-truncate fs-sm">Total ACV</p>
                        <span class="h5 mb-0">${{ number_format($totalAcv, 0) }}</span>
                    </div>
                    <div class="width-4 height-4 bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fad fa-chart-line text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $registry = app(\App\Services\ModuleRegistry::class);
        $partnerTabs = $registry->getPartnerDetailTabs();
        $serverRenderedSlugs = collect($partnerTabs)->where('server_rendered', true)->pluck('slug')->all();
    @endphp

    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs border-bottom mb-g" id="partner-tabs" role="tablist">
        @foreach($partnerTabs as $tab)
        <li class="nav-item">
            <a class="nav-link {{ $loop->first ? 'active' : '' }}" href="#{{ $tab['slug'] }}" data-tab="{{ $tab['slug'] }}" role="tab">
                <i class="fal fa-{{ $tab['icon'] }} me-1 d-none d-md-inline"></i> {{ $tab['label'] }}
                @if(isset($tab['badge_relation']) && $partner->relationLoaded($tab['badge_relation']))
                    <span class="badge bg-primary bg-opacity-25 text-primary ms-1">{{ $partner->{$tab['badge_relation']}->count() }}</span>
                @endif
            </a>
        </li>
        @endforeach
    </ul>

    {{-- Tab Content --}}
    <div id="partner-tab-content">
        @foreach($partnerTabs as $tab)
        <div class="tab-pane" id="tab-{{ $tab['slug'] }}" style="display: {{ $loop->first ? 'block' : 'none' }};">
            @if(! empty($tab['server_rendered']))
                @include($tab['partial'])
            @else
                <div class="spinner-loader">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading {{ strtolower($tab['label']) }} data...</p>
                </div>
            @endif
        </div>
        @endforeach
    </div>
@endsection

@section('scripts')
@vite(['resources/scripts/pages/table-init.js', 'resources/scripts/pages/ai-insights-partner.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    var PARTNER_ID = {{ $partner->id }};
    var TAB_BASE_URL = '{{ url("/partners") }}/' + PARTNER_ID + '/tab/';
    var SERVER_RENDERED = @json($serverRenderedSlugs);
    var ALL_TABS = @json(collect($partnerTabs)->pluck('slug')->values());
    var AJAX_TABS = ALL_TABS.filter(function(t) { return SERVER_RENDERED.indexOf(t) === -1; });

    // State — mark server-rendered tabs as already loaded
    var tabLoaded = {};
    SERVER_RENDERED.forEach(function(t) { tabLoaded[t] = true; });

    var tabLinks = document.querySelectorAll('#partner-tabs .nav-link');
    var tabPanes = document.querySelectorAll('#partner-tab-content .tab-pane');

    /**
     * Switch the visible tab.
     */
    function activateTab(tabName) {
        tabLinks.forEach(function(link) {
            if (link.dataset.tab === tabName) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
        tabPanes.forEach(function(pane) {
            var paneTab = pane.id.replace('tab-', '');
            pane.style.display = (paneTab === tabName) ? 'block' : 'none';
        });
    }

    /**
     * Execute <script> tags from AJAX-loaded HTML.
     * innerHTML does not execute scripts, so we create new script elements.
     */
    function executeScripts(container) {
        var scripts = container.querySelectorAll('script');
        scripts.forEach(function(oldScript) {
            var newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(function(attr) {
                newScript.setAttribute(attr.name, attr.value);
            });
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    /**
     * Load a tab via AJAX fetch.
     */
    function loadTab(tabName) {
        // If already loaded, just switch
        if (tabLoaded[tabName]) {
            activateTab(tabName);
            return;
        }

        // Show the tab immediately (with spinner)
        activateTab(tabName);

        fetch(TAB_BASE_URL + tabName, {
            method: 'GET',
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            if (!response.ok) throw new Error('Failed to load tab: ' + response.status);
            return response.text();
        })
        .then(function(html) {
            if (!html) return;
            var pane = document.getElementById('tab-' + tabName);
            pane.innerHTML = html;
            executeScripts(pane);
            tabLoaded[tabName] = true;
        })
        .catch(function(error) {
            var pane = document.getElementById('tab-' + tabName);
            pane.innerHTML = '<div class="alert alert-danger m-3">' +
                '<i class="fal fa-exclamation-triangle me-1"></i> ' +
                'Failed to load tab content. ' +
                '<a href="javascript:void(0)" onclick="window.partnerTabs.reload(\'' + tabName + '\')" class="alert-link">Retry</a>' +
                '</div>';
        });
    }

    /**
     * Force reload a tab (clears cache).
     */
    function reloadTab(tabName) {
        tabLoaded[tabName] = false;
        var pane = document.getElementById('tab-' + tabName);
        pane.innerHTML = '<div class="spinner-loader"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading...</p></div>';
        loadTab(tabName);
    }

    // Expose for retry links
    window.partnerTabs = { reload: reloadTab };

    // Tab click handlers
    tabLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var tabName = this.dataset.tab;
            loadTab(tabName);
            history.replaceState(null, '', '#' + tabName);
        });
    });

    // Handle initial hash (direct link like /partners/5#pipeline)
    var initialHash = window.location.hash.replace('#', '');
    if (initialHash && ALL_TABS.indexOf(initialHash) !== -1 && SERVER_RENDERED.indexOf(initialHash) === -1) {
        loadTab(initialHash);
    }

    // Handle browser back/forward
    window.addEventListener('popstate', function() {
        var hash = window.location.hash.replace('#', '');
        if (hash && ALL_TABS.indexOf(hash) !== -1) {
            loadTab(hash);
        }
    });
});
</script>
@endsection
