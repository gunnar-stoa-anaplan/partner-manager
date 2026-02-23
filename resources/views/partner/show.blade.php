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

    @php $registry = app(\App\Services\ModuleRegistry::class); @endphp

    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs border-bottom mb-g" id="partner-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="#overview" data-tab="overview" role="tab">
                <i class="fal fa-tachometer-alt me-1 d-none d-md-inline"></i> Overview
            </a>
        </li>
        @if($registry->isEnabled('deal_registration_user'))
        <li class="nav-item">
            <a class="nav-link" href="#pipeline" data-tab="pipeline" role="tab">
                <i class="fal fa-chart-line me-1 d-none d-md-inline"></i> Pipeline
                <span class="badge bg-primary bg-opacity-25 text-primary ms-1">{{ $partner->opportunities->count() }}</span>
            </a>
        </li>
        @endif
        @if($registry->isEnabled('project_health_user'))
        <li class="nav-item">
            <a class="nav-link" href="#projects" data-tab="projects" role="tab">
                <i class="fal fa-project-diagram me-1 d-none d-md-inline"></i> Projects
                <span class="badge bg-info bg-opacity-25 text-info ms-1">{{ $partner->projects->count() }}</span>
            </a>
        </li>
        @endif
        @if($registry->isEnabled('enablement_user'))
        <li class="nav-item">
            <a class="nav-link" href="#enablement" data-tab="enablement" role="tab">
                <i class="fal fa-award me-1 d-none d-md-inline"></i> Enablement
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#calendar" data-tab="calendar" role="tab">
                <i class="fal fa-calendar me-1 d-none d-md-inline"></i> Calendar
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#influencers" data-tab="influencers" role="tab">
                <i class="fal fa-users me-1 d-none d-md-inline"></i> Influencers
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" href="#resources" data-tab="resources" role="tab">
                <i class="fal fa-user-hard-hat me-1 d-none d-md-inline"></i> Resources
            </a>
        </li>
        @if($registry->isEnabled('enablement_user'))
        <li class="nav-item">
            <a class="nav-link" href="#app-enablement" data-tab="app-enablement" role="tab">
                <i class="fal fa-th me-1 d-none d-md-inline"></i> Nicole's View
            </a>
        </li>
        @endif
        @if($registry->isEnabled('launch_programs_user'))
        <li class="nav-item">
            <a class="nav-link" href="#launch-programs" data-tab="launch-programs" role="tab">
                <i class="fal fa-rocket me-1 d-none d-md-inline"></i> Launch Programs
            </a>
        </li>
        @endif
        @if($registry->isEnabled('provisioning_user'))
        <li class="nav-item">
            <a class="nav-link" href="#workspaces" data-tab="workspaces" role="tab">
                <i class="fal fa-server me-1 d-none d-md-inline"></i> Workspaces
                <span class="badge bg-info bg-opacity-25 text-info ms-1">{{ $partner->anaplanWorkspaces->count() }}</span>
            </a>
        </li>
        @endif
        @if($registry->isEnabled('partner_portal_user'))
        <li class="nav-item">
            <a class="nav-link" href="#portal-users" data-tab="portal-users" role="tab">
                <i class="fal fa-user-shield me-1 d-none d-md-inline"></i> Portal Users
                <span class="badge bg-info bg-opacity-25 text-info ms-1">{{ $partner->contacts->where('portal_access', true)->count() }}</span>
            </a>
        </li>
        @endif
    </ul>

    {{-- Tab Content --}}
    <div id="partner-tab-content">
        {{-- Overview (server-rendered) --}}
        <div class="tab-pane" id="tab-overview" style="display: block;">
            @include('partner.tabs.overview')
        </div>

        @if($registry->isEnabled('deal_registration_user'))
        {{-- Pipeline (AJAX) --}}
        <div class="tab-pane" id="tab-pipeline" style="display: none;">
            <div class="spinner-loader">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading pipeline data...</p>
            </div>
        </div>
        @endif

        @if($registry->isEnabled('project_health_user'))
        {{-- Projects (AJAX) --}}
        <div class="tab-pane" id="tab-projects" style="display: none;">
            <div class="spinner-loader">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading project data...</p>
            </div>
        </div>
        @endif

        @if($registry->isEnabled('enablement_user'))
        {{-- Enablement (AJAX) --}}
        <div class="tab-pane" id="tab-enablement" style="display: none;">
            <div class="spinner-loader">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading enablement data...</p>
            </div>
        </div>

        {{-- Calendar (AJAX) --}}
        <div class="tab-pane" id="tab-calendar" style="display: none;">
            <div class="spinner-loader">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading calendar data...</p>
            </div>
        </div>

        {{-- Influencers (AJAX) --}}
        <div class="tab-pane" id="tab-influencers" style="display: none;">
            <div class="spinner-loader">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading influencer data...</p>
            </div>
        </div>
        @endif

        {{-- Resources (AJAX) --}}
        <div class="tab-pane" id="tab-resources" style="display: none;">
            <div class="spinner-loader">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading resource data...</p>
            </div>
        </div>

        @if($registry->isEnabled('enablement_user'))
        {{-- App Enablement (AJAX) --}}
        <div class="tab-pane" id="tab-app-enablement" style="display: none;">
            <div class="spinner-loader">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading app enablement data...</p>
            </div>
        </div>
        @endif

        @if($registry->isEnabled('launch_programs_user'))
        {{-- Launch Programs (AJAX) --}}
        <div class="tab-pane" id="tab-launch-programs" style="display: none;">
            <div class="spinner-loader">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading launch programs...</p>
            </div>
        </div>
        @endif

        @if($registry->isEnabled('provisioning_user'))
        {{-- Workspaces (AJAX) --}}
        <div class="tab-pane" id="tab-workspaces" style="display: none;">
            <div class="spinner-loader">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading workspace data...</p>
            </div>
        </div>
        @endif

        @if($registry->isEnabled('partner_portal_user'))
        {{-- Portal Users (AJAX) --}}
        <div class="tab-pane" id="tab-portal-users" style="display: none;">
            <div class="spinner-loader">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading portal users...</p>
            </div>
        </div>
        @endif
    </div>
@endsection

@section('scripts')
@vite(['resources/scripts/pages/table-init.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    var PARTNER_ID = {{ $partner->id }};
    var TAB_BASE_URL = '{{ url("/partners") }}/' + PARTNER_ID + '/tab/';
    var AJAX_TABS = ['pipeline', 'projects', 'enablement', 'calendar', 'influencers', 'resources', 'app-enablement', 'launch-programs', 'workspaces', 'portal-users'];
    var ALL_TABS = ['overview'].concat(AJAX_TABS);

    // State
    var tabLoaded = { overview: true };
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
    if (initialHash && ALL_TABS.indexOf(initialHash) !== -1 && initialHash !== 'overview') {
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
