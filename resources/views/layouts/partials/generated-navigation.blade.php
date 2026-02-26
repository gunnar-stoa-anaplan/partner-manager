@php
    /**
     * Dev Focus: when a feature is selected, only show nav items tagged with that feature.
     * When no focus is set ($devFocus is empty), show everything normally using section toggles.
     */
    $focused = !empty($devFocus);
    $show = fn(string ...$features) => !$focused || in_array($devFocus, $features);
    $registry = app(\App\Services\ModuleRegistry::class);
@endphp
<ul id="js-nav-menu" class="nav-menu">

    <li>
        <a href="{{ route('welcome') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#home"></use>
            </svg>
            <span class="nav-link-text">Welcome</span>
        </a>
    </li>

    @auth
    @php
        $pendingTrainingCount = auth()->user()->activationPaths()
            ->wherePivotNull('completed_at')
            ->count();
    @endphp
    @if($pendingTrainingCount > 0)
    <li>
        <a href="{{ route('training.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#book-open"></use>
            </svg>
            <span class="nav-link-text">My Training</span>
            <span class="badge bg-warning text-dark rounded-pill ms-1">{{ $pendingTrainingCount }}</span>
        </a>
    </li>
    @endif
    @endauth

    @auth
    @if(Auth::user()->role !== 'coe' && app(\App\Services\ModuleRegistry::class)->isEnabled('idea_room'))
    <li class="nav-item">
        <a href="#" title="Idea Room" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#star"></use>
            </svg>
            <span class="nav-link-text">Idea Room</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('idea_room.dashboard') }}">
                    <span class="nav-link-text">Gallery</span>
                </a>
            </li>
            <li>
                <a href="{{ route('idea_room.rooms') }}">
                    <span class="nav-link-text">Idea Rooms</span>
                </a>
            </li>
            <li>
                <a href="{{ route('idea_room.ideas') }}">
                    <span class="nav-link-text">Ideas</span>
                </a>
            </li>
            <li>
                <a href="{{ route('idea_room.whiteboard') }}">
                    <span class="nav-link-text">Whiteboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('idea_room.constellation') }}">
                    <span class="nav-link-text">Constellation</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    @if(Auth::user()->role !== 'coe')
    <li class="nav-item">
        <a href="#" title="Partners" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#briefcase"></use>
            </svg>
            <span class="nav-link-text">Partners</span>
        </a>
        <ul>
            @if($registry->isAccessible('partner_management_user'))
            <li>
                <a href="{{ route('dashboard') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('partners.my') }}">
                    <span class="nav-link-text">My Partners</span>
                </a>
            </li>
            @endif
            @if($show('analysis') && $registry->isAccessible('analytics'))
            @if(!$focused || Auth::user()->isAdmin() || ($navSettings['nav_visible_analysis'] ?? '1') === '1')
            <li>
                <a href="{{ route('custom-filters.index') }}">
                    <span class="nav-link-text">Custom Filters</span>
                </a>
            </li>
            <li>
                <a href="{{ route('wizard.index') }}">
                    <span class="nav-link-text">Wizard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('coach.index') }}">
                    <span class="nav-link-text">Coach</span>
                </a>
            </li>
            @endif
            @endif
        </ul>
    </li>
    @endif

    {{-- Plan Section (temporarily removed) --}}

    {{-- Engage Section (temporarily removed) --}}

    {{-- Key Metrics (Partner Profile) - only show when a partner is selected during focus mode --}}
    @if(Auth::user()->role !== 'coe')
    @if($focused ? session('selected_partner_id') : (Auth::user()->isAdmin() || session('selected_partner_id')))
    @if(!$focused || ($navSettings['nav_visible_key_metrics'] ?? '1') === '1')
    <li class="nav-item">
        <a href="#" title="Key Metrics" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#activity"></use>
            </svg>
            <span class="nav-link-text">Key Metrics</span>
            @if(!session('selected_partner_id'))
            <span class="badge badge-warning fs-nano">select partner</span>
            @endif
        </a>
        <ul>
            <li>
                <a href="{{ session('selected_partner_id') ? route('partners.show', session('selected_partner_id')) : '#' }}"@if(!session('selected_partner_id')) class="text-muted"@endif>
                    <span class="nav-link-text">Partner Profile</span>
                </a>
            </li>
            <li>
                <a href="{{ session('selected_partner_id') ? route('partners.summary', session('selected_partner_id')) : '#' }}"@if(!session('selected_partner_id')) class="text-muted"@endif>
                    <span class="nav-link-text">Summary</span>
                </a>
            </li>
            <li>
                <a href="{{ session('selected_partner_id') ? route('partners.insights', session('selected_partner_id')) : '#' }}"@if(!session('selected_partner_id')) class="text-muted"@endif>
                    <span class="nav-link-text">Insights</span>
                </a>
            </li>
            <li>
                <a href="{{ session('selected_partner_id') ? route('partners.calendar', session('selected_partner_id')) : '#' }}"@if(!session('selected_partner_id')) class="text-muted"@endif>
                    <span class="nav-link-text">Calendar</span>
                </a>
            </li>
            <li>
                <a href="{{ session('selected_partner_id') ? route('partners.blueprints', session('selected_partner_id')) : '#' }}"@if(!session('selected_partner_id')) class="text-muted"@endif>
                    <span class="nav-link-text">Blueprints</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    {{-- Business Development --}}
    @if($show('deal_registration') && $registry->isAccessible('deal_registration_user'))
    @if(!$focused || Auth::user()->isAdmin() || ($navSettings['nav_visible_business_dev'] ?? '1') === '1')
    <li class="nav-item">
        <a href="#" title="Business Development" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#dollar-sign"></use>
            </svg>
            <span class="nav-link-text">Business Development</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('leads.sourced') }}">
                    <span class="nav-link-text">Sourced Leads</span>
                </a>
            </li>
            <li>
                <a href="{{ route('leads.influenced') }}">
                    <span class="nav-link-text">Influenced Leads</span>
                </a>
            </li>
            <li>
                <a href="{{ route('funding.development') }}">
                    <span class="nav-link-text">Development Funding</span>
                </a>
            </li>
        </ul>
    </li>
    @endif
    @endif
    @endif
    @endif {{-- end coe exclusion for Key Metrics / Business Development --}}

    {{-- Measure Section (temporarily removed) --}}

    {{-- Act Section (temporarily removed) --}}

    {{-- Partner Admin - Admin Only --}}
    @if(Auth::user()->isAdmin())
    @if($show('goals_scorecards', 'partner_paths', 'deal_registration', 'anaplan', 'blueprints', 'enablement_events', 'labs', 'launch_programs'))
    <li class="nav-title"><span>Partner Administration</span></li>

    @if($show('goals_scorecards') && $registry->isAccessible('goals_targets_admin'))
    <li class="nav-item">
        <a href="#" title="Goals" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#target"></use>
            </svg>
            <span class="nav-link-text">Goals</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('partner-goals.index') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.goal-templates.index') }}">
                    <span class="nav-link-text">Templates</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.scorecard-weights.index') }}">
                    <span class="nav-link-text">Scorecards</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    @if($show('partner_paths'))
    <li class="nav-item">
        <a href="#" title="Partner Paths" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#map"></use>
            </svg>
            <span class="nav-link-text">Partner Paths</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('plan.paths.index') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.paths.index') }}">
                    <span class="nav-link-text">Path Configuration</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.task-templates.index') }}">
                    <span class="nav-link-text">Task Templates</span>
                </a>
            </li>
            <li>
                <a href="{{ route('act.path-reviews.index') }}">
                    <span class="nav-link-text">Path Reviews</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    @if($show('partner_paths', 'goals_scorecards'))
    <li>
        <a href="{{ route('partners.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#link"></use>
            </svg>
            <span class="nav-link-text">Partner Assignments</span>
        </a>
    </li>
    @endif

    @if($show('deal_registration') && $registry->isAccessible('deal_registration_admin'))
    <li class="nav-item">
        <a href="#" title="Lead Assignment" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#user-check"></use>
            </svg>
            <span class="nav-link-text">Lead Assignment</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('leads.unassigned') }}">
                    <span class="nav-link-text">New Leads</span>
                </a>
            </li>
            <li>
                <a href="{{ route('leads.assigned') }}">
                    <span class="nav-link-text">Assigned Leads</span>
                </a>
            </li>
            <li>
                <a href="{{ route('leads.rejected') }}">
                    <span class="nav-link-text">Rejected Leads</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    @if($show('anaplan') && $registry->isAccessible('provisioning_admin'))
    <li class="nav-item">
        <a href="#" title="Provisioning" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#server"></use>
            </svg>
            <span class="nav-link-text">Provisioning</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('admin.provisioning-summary') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.provisioning-workspaces.index') }}">
                    <span class="nav-link-text">Workspaces</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    @if($show('blueprints', 'enablement_events') && $registry->isAccessible('enablement_events_admin'))
    <li class="nav-item">
        <a href="#" title="Enablement" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#layers"></use>
            </svg>
            <span class="nav-link-text">Enablement</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('admin.enablement-dashboard') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ Auth::user()->isAdmin() ? route('blueprints.index') : route('blueprints.catalog') }}">
                    <span class="nav-link-text">Blueprints</span>
                </a>
            </li>
            <li>
                <a href="{{ route('enablement-events.index') }}">
                    <span class="nav-link-text">Enablement Events</span>
                </a>
            </li>
            <li>
                <a href="{{ route('enablement-calendar') }}">
                    <span class="nav-link-text">Enablement Calendar</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    @if($show('labs') && $registry->isAccessible('workshop_labs_admin'))
    <li class="nav-item">
        <a href="#" title="Workshops" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#check-square"></use>
            </svg>
            <span class="nav-link-text">Workshops</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('labs.index') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('labs.index') }}">
                    <span class="nav-link-text">Labs</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    @if($show('launch_programs') && $registry->isAccessible('launch_programs_admin'))
    <li class="nav-item">
        <a href="#" title="Launch Programs" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#zap"></use>
            </svg>
            <span class="nav-link-text">Launch Programs</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('admin.launch-programs.dashboard') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.launch-programs.index') }}">
                    <span class="nav-link-text">Program Details</span>
                </a>
            </li>
        </ul>
    </li>
    @endif
    @endif
    @endif {{-- end admin-only Partner Administration --}}

    {{-- CoE Program Administration --}}
    @if(Auth::user()->role === 'coe' || Auth::user()->role === 'super_admin')
    <li class="nav-title"><span>Program Administration</span></li>

    <li>
        <a href="{{ route('program-dashboard') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#compass"></use>
            </svg>
            <span class="nav-link-text">Program Dashboard</span>
        </a>
    </li>

    {{-- Strategy Workbench for CoE users --}}
    @if($registry->isAccessible('strategy'))
    <li class="nav-item">
        <a href="#" title="Strategy Workbench" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#compass"></use>
            </svg>
            <span class="nav-link-text">Strategy Workbench</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('strategy.dashboard') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('strategy.plans.index') }}">
                    <span class="nav-link-text">Plans</span>
                </a>
            </li>
            <li>
                <a href="{{ route('strategy.scenarios.index') }}">
                    <span class="nav-link-text">Scenarios</span>
                </a>
            </li>
            <li>
                <a href="{{ route('strategy.benchmarks') }}">
                    <span class="nav-link-text">Benchmarks</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    @if($show('partner_paths'))
    <li class="nav-item">
        <a href="#" title="Partner Paths" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#map"></use>
            </svg>
            <span class="nav-link-text">Partner Paths</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('plan.paths.index') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.paths.index') }}">
                    <span class="nav-link-text">Path Configuration</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.task-templates.index') }}">
                    <span class="nav-link-text">Task Templates</span>
                </a>
            </li>
            <li>
                <a href="{{ route('act.path-reviews.index') }}">
                    <span class="nav-link-text">Path Reviews</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    {{-- Goals for CoE users --}}
    @if($show('goals_scorecards') && $registry->isAccessible('goals_targets_admin'))
    <li class="nav-item">
        <a href="#" title="Goals" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#target"></use>
            </svg>
            <span class="nav-link-text">Goals</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('partner-goals.index') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.goal-templates.index') }}">
                    <span class="nav-link-text">Templates</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    {{-- Launch Programs for CoE users --}}
    @if($show('launch_programs') && $registry->isAccessible('launch_programs_admin'))
    <li class="nav-item">
        <a href="#" title="Launch Programs" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#zap"></use>
            </svg>
            <span class="nav-link-text">Launch Programs</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('admin.launch-programs.dashboard') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.launch-programs.index') }}">
                    <span class="nav-link-text">Program Details</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    {{-- Knowledge Base for CoE users --}}
    @if($registry->isAccessible('knowledge_base_admin'))
    <li class="nav-item">
        <a href="#" title="Knowledge Base" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#book-open"></use>
            </svg>
            <span class="nav-link-text">Knowledge Base</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('knowledge_base.dashboard') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('knowledge_base.documents.index') }}">
                    <span class="nav-link-text">Documents</span>
                </a>
            </li>
            <li>
                <a href="{{ route('knowledge_base.ingest.create') }}">
                    <span class="nav-link-text">Ingest</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    {{-- Branding for CoE users --}}
    <li>
        <a href="{{ route('admin.branding') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#edit"></use>
            </svg>
            <span class="nav-link-text">Branding</span>
        </a>
    </li>

    {{-- Enablement Blueprints for CoE users --}}
    @if($registry->isAccessible('enablement_admin'))
    <li class="nav-item">
        <a href="#" title="Enablement Blueprints" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#layers"></use>
            </svg>
            <span class="nav-link-text">Enablement Blueprints</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('blueprints.catalog') }}">
                    <span class="nav-link-text">Catalog</span>
                </a>
            </li>
            <li>
                <a href="{{ route('blueprints.index') }}">
                    <span class="nav-link-text">Manage Blueprints</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    {{-- AI Agents for CoE users --}}
    @if($registry->isAccessible('ai_agents_admin'))
    <li>
        <a href="{{ route('admin.ai-agents.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#cpu"></use>
            </svg>
            <span class="nav-link-text">AI Agents</span>
        </a>
    </li>
    @endif
    @endif

    {{-- Blueprint & Enablement Admin for non-admin users --}}
    @if(!Auth::user()->isAdmin() && $registry->isAccessible('enablement_events_admin'))
    @if($show('blueprints', 'enablement_events'))
    <li class="nav-title"><span>Enablement Administration</span></li>

    <li>
        <a href="{{ route('admin.enablement-dashboard') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#trending-up"></use>
            </svg>
            <span class="nav-link-text">Dashboard</span>
        </a>
    </li>

    <li class="nav-item">
        <a href="#" title="Enablement" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#layers"></use>
            </svg>
            <span class="nav-link-text">Enablement</span>
        </a>
        <ul>
            @if(Auth::user()->role !== 'coe')
            <li>
                <a href="{{ route('blueprints.catalog') }}">
                    <span class="nav-link-text">Blueprints</span>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('enablement-events.index') }}">
                    <span class="nav-link-text">Enablement Events</span>
                </a>
            </li>
            <li>
                <a href="{{ route('enablement-calendar') }}">
                    <span class="nav-link-text">Enablement Calendar</span>
                </a>
            </li>
        </ul>
    </li>
    @endif
    @endif

    {{-- System - always show Configuration so dev can change focus --}}
    @if(Auth::user()->role !== 'coe')
    @if(!$focused || Auth::user()->isAdmin() || ($navSettings['nav_visible_system'] ?? '1') === '1')
    <li class="nav-title"><span>System Administration</span></li>

    <li>
        <a href="{{ route('admin.dashboard') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#monitor"></use>
            </svg>
            <span class="nav-link-text">Admin Dashboard</span>
        </a>
    </li>

    <li>
        <a href="{{ route('admin.system-status') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#server"></use>
            </svg>
            <span class="nav-link-text">System Status</span>
        </a>
    </li>

    <li>
        <a href="{{ route('configuration.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#settings"></use>
            </svg>
            <span class="nav-link-text">Configuration</span>
        </a>
    </li>

    <li>
        <a href="{{ route('admin.branding') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#edit"></use>
            </svg>
            <span class="nav-link-text">Branding</span>
        </a>
    </li>

    @if($registry->isAccessible('ai_agents_admin'))
    <li>
        <a href="{{ route('admin.ai-agents.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#cpu"></use>
            </svg>
            <span class="nav-link-text">AI Agents</span>
        </a>
    </li>
    @endif

    @if($registry->isAccessible('knowledge_base_admin'))
    <li class="nav-item">
        <a href="#" title="Knowledge Base" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#book-open"></use>
            </svg>
            <span class="nav-link-text">Knowledge Base</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('knowledge_base.dashboard') }}">
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('knowledge_base.documents.index') }}">
                    <span class="nav-link-text">Documents</span>
                </a>
            </li>
            <li>
                <a href="{{ route('knowledge_base.ingest.create') }}">
                    <span class="nav-link-text">Ingest</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    @if(!$focused)
    <li>
        <a href="{{ route('imports.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#upload"></use>
            </svg>
            <span class="nav-link-text">Data Imports</span>
        </a>
    </li>

    <li>
        <a href="{{ route('salesforce-credentials.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#cloud"></use>
            </svg>
            <span class="nav-link-text">Salesforce Credentials</span>
        </a>
    </li>

    <li>
        <a href="/pulse" target="_blank">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#activity"></use>
            </svg>
            <span class="nav-link-text">System Pulse</span>
            <span class="dl-ref badge badge-info">live</span>
        </a>
    </li>

    <li>
        <a href="/telescope" target="_blank">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#search"></use>
            </svg>
            <span class="nav-link-text">Telescope</span>
            <span class="dl-ref badge badge-info">live</span>
        </a>
    </li>
    @endif

    @if(app(\App\Services\ModuleRegistry::class)->isEnabled('idea_room'))
    <li class="nav-item">
        <a href="#" title="Idea Room" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#star"></use>
            </svg>
            <span class="nav-link-text">Idea Room</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('idea_room.admin.settings') }}">
                    <span class="nav-link-text">Settings</span>
                </a>
            </li>
            <li>
                <a href="{{ route('idea_room.admin.themes.index') }}">
                    <span class="nav-link-text">Gallery Themes</span>
                </a>
            </li>
        </ul>
    </li>
    @endif
    @endif
    @endif {{-- end coe exclusion for System Administration --}}

    {{-- Feature Activation — visible to admin and authorized CoE --}}
    @can('manage-activation')
    <li class="nav-title"><span>Feature Activation</span></li>
    <li class="nav-item">
        <a href="#" title="Feature Activation" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#unlock"></use>
            </svg>
            <span class="nav-link-text">Activation</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('admin.activation.flags') }}">
                    <span class="nav-link-text">Feature Flags</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.activation.paths') }}">
                    <span class="nav-link-text">Activation Paths</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.activation.progression') }}">
                    <span class="nav-link-text">Progression Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.activation.log') }}">
                    <span class="nav-link-text">Activation Log</span>
                </a>
            </li>
        </ul>
    </li>
    @endcan

    @endauth
</ul>
