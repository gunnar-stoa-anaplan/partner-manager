<ul id="js-nav-menu" class="nav-menu">

    <li class="nav-title"><span>Partner Manager</span></li>

    <li>
        <a href="{{ route('welcome') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#home"></use>
            </svg>
            <span class="nav-link-text">Welcome</span>
        </a>
    </li>

    @auth
    <li>
        <a href="{{ route('partners.my') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#briefcase"></use>
            </svg>
            <span class="nav-link-text">My Partners</span>
        </a>
    </li>

    <li>
        <a href="{{ route('analysis.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#trending-up"></use>
            </svg>
            <span class="nav-link-text">Custom Analysis</span>
        </a>
    </li>

    <li>
        <a href="{{ route('analysis.guided') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#compass"></use>
            </svg>
            <span class="nav-link-text">Guided Analysis</span>
        </a>
    </li>

    <li>
        <a href="{{ route('analysis.coach') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#message-circle"></use>
            </svg>
            <span class="nav-link-text">Partner Coach</span>
        </a>
    </li>

    @if(session('selected_partner_id'))
    <li class="nav-item">
        <a href="#" title="Key Metrics" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#activity"></use>
            </svg>
            <span class="nav-link-text">Key Metrics</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('partners.show', session('selected_partner_id')) }}">
                    <span class="nav-link-text">Partner Profile</span>
                </a>
            </li>
            <li>
                <a href="{{ route('partners.summary', session('selected_partner_id')) }}">
                    <span class="nav-link-text">Summary</span>
                </a>
            </li>
            <li>
                <a href="{{ route('partners.insights', session('selected_partner_id')) }}">
                    <span class="nav-link-text">Insights</span>
                </a>
            </li>
            <li>
                <a href="{{ route('partners.calendar', session('selected_partner_id')) }}">
                    <span class="nav-link-text">Calendar</span>
                </a>
            </li>
            <li>
                <a href="{{ route('partners.blueprints', session('selected_partner_id')) }}">
                    <span class="nav-link-text">Blueprints</span>
                </a>
            </li>
        </ul>
    </li>

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

    <li>
        <a href="{{ route('projects.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#clipboard"></use>
            </svg>
            <span class="nav-link-text">Project Delivery</span>
        </a>
    </li>

    <li class="nav-item">
        <a href="#" title="Field Enablement" data-filter-tags>
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#users"></use>
            </svg>
            <span class="nav-link-text">Field Enablement</span>
        </a>
        <ul>
            <li>
                <a href="{{ route('field.key-influencers') }}">
                    <span class="nav-link-text">Key Influencers</span>
                </a>
            </li>
            <li>
                <a href="{{ route('field.breadth') }}">
                    <span class="nav-link-text">Breadth</span>
                </a>
            </li>
            <li>
                <a href="{{ route('field.depth') }}">
                    <span class="nav-link-text">Depth</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    {{-- Dashboards --}}
    <li class="nav-title"><span>Dashboards</span></li>

    <li>
        <a href="{{ route('partners.full-summary') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#bar-chart-2"></use>
            </svg>
            <span class="nav-link-text">Full Summary</span>
        </a>
    </li>

    <li>
        <a href="{{ route('partners.full-insights') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#pie-chart"></use>
            </svg>
            <span class="nav-link-text">Full Insights</span>
        </a>
    </li>

    <li>
        <a href="{{ route('calendar.portfolio') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#calendar"></use>
            </svg>
            <span class="nav-link-text">Partner Calendar</span>
        </a>
    </li>

    <li>
        <a href="{{ route('enablement.calendar') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#award"></use>
            </svg>
            <span class="nav-link-text">Enablement Calendar</span>
        </a>
    </li>

    {{-- Administration - Admin Only --}}
    @if(Auth::user()->role === 'admin')
    <li class="nav-title"><span>Administration</span></li>

    <li>
        <a href="{{ route('partner-goals.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#target"></use>
            </svg>
            <span class="nav-link-text">Partner Goals</span>
        </a>
    </li>

    <li>
        <a href="{{ route('blueprints.catalog') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#layers"></use>
            </svg>
            <span class="nav-link-text">Enablement Blueprints</span>
        </a>
    </li>

    <li>
        <a href="{{ route('partners.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#link"></use>
            </svg>
            <span class="nav-link-text">Partner Assignments</span>
        </a>
    </li>

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

    <li>
        <a href="{{ route('users.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#users"></use>
            </svg>
            <span class="nav-link-text">User Management</span>
        </a>
    </li>

    {{-- Program Administration --}}
    <li class="nav-title"><span>Program Administration</span></li>

    <li>
        <a href="{{ route('enablement-events.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#award"></use>
            </svg>
            <span class="nav-link-text">Enablement Events</span>
        </a>
    </li>

    <li>
        <a href="{{ route('enablement-calendar') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#calendar"></use>
            </svg>
            <span class="nav-link-text">Enablement Calendar</span>
        </a>
    </li>

    <li>
        <a href="{{ route('blueprints.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#layers"></use>
            </svg>
            <span class="nav-link-text">Blueprints</span>
        </a>
    </li>

    {{-- System --}}
    <li class="nav-title"><span>System</span></li>

    <li>
        <a href="{{ route('configuration.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#settings"></use>
            </svg>
            <span class="nav-link-text">Configuration</span>
        </a>
    </li>

    <li>
        <a href="{{ route('imports.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#upload"></use>
            </svg>
            <span class="nav-link-text">Data Imports</span>
        </a>
    </li>

    @endif
    @endauth
</ul>
