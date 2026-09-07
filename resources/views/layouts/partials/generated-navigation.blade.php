@php
    $registry = app(\App\Services\ModuleRegistry::class);
    $focused = ! empty($devFocus);
@endphp
<ul id="js-nav-menu" class="nav-menu">

    {{-- Welcome (always visible) --}}
    <li>
        <a href="{{ route('welcome') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#home"></use>
            </svg>
            <span class="nav-link-text">Welcome</span>
        </a>
    </li>

    @auth
    {{-- PSM Engagement & Workshops for dashboard-only users. Normal users get
         these through the permission-gated module nav below (partner_success /
         workshops modules), so only render these extra entries for the
         restricted dashboard_viewer role. --}}
    @if(auth()->user()?->isDashboardOnly() && Route::has('partner-success.index'))
    <li>
        <a href="{{ route('partner-success.index') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#bar-chart-2"></use>
            </svg>
            <span class="nav-link-text">PSM Engagement</span>
        </a>
    </li>
    @endif

    @if(auth()->user()?->isDashboardOnly() && Route::has('workshops.dashboard'))
    <li>
        <a href="{{ route('workshops.dashboard') }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#check-square"></use>
            </svg>
            <span class="nav-link-text">Workshops</span>
        </a>
    </li>
    @endif

    {{-- Everything below is hidden for dashboard-only users (Welcome + Partner
         Success + Workshops are the only nav they get). --}}
    @unless(auth()->user()?->isDashboardOnly())

    {{-- Dynamic Module Navigation — rendered from config/modules.php sections --}}
    @foreach($navSections as $section)
    @php
        // Dev Focus: only show sections belonging to the focused feature
        if ($focused && $section['feature'] !== $devFocus) {
            continue;
        }

        $sectionState = $section['state'] ?? 'unlocked';
        $isLocked = $sectionState === 'locked';
        $isNudge = $sectionState === 'nudge';
        $progress = $section['training_progress'] ?? null;

        // Filter to items whose named routes exist
        $validItems = array_filter($section['items'], fn($item) => Route::has($item['route']));
    @endphp
    @if(! empty($validItems) || $isLocked)
    <li class="nav-item {{ $isLocked ? 'nav-locked' : '' }} {{ $isNudge ? 'nav-nudge' : '' }}">
        @if($isLocked)
        <a href="{{ route('features.preview', $section['feature']) }}" title="{{ $section['label'] }} — Complete training to unlock" data-filter-tags="{{ strtolower($section['label']) }}">
        @else
        <a href="#" title="{{ $section['label'] }}" data-filter-tags="{{ strtolower($section['label']) }}">
        @endif
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#{{ $section['icon'] }}"></use>
            </svg>
            <span class="nav-link-text">{{ $section['label'] }}</span>
            @if($isLocked)
            <span class="nav-lock-indicator ms-auto d-flex align-items-center gap-1">
                @if($progress && $progress['total'] > 0)
                <span class="badge bg-warning text-dark rounded-pill nav-progress-badge">{{ $progress['completed'] }}/{{ $progress['total'] }}</span>
                @endif
                <svg class="sa-icon sa-icon-xs"><use href="/icons/sprite.svg#lock"></use></svg>
            </span>
            @elseif($isNudge && $progress && $progress['total'] > 0 && $progress['completed'] < $progress['total'])
            <span class="badge bg-info bg-opacity-25 text-info rounded-pill ms-auto nav-nudge-badge" title="Training available">{{ $progress['completed'] }}/{{ $progress['total'] }}</span>
            @endif
        </a>
        @if(! empty($validItems))
        <ul>
            @foreach($validItems as $item)
            <li>
                @if($isLocked)
                <a href="{{ route('features.preview', $section['feature']) }}" class="nav-locked-item" data-filter-tags="{{ strtolower($item['label']) }}">
                    <span class="nav-link-text">{{ $item['label'] }}</span>
                </a>
                @else
                <a href="{{ route($item['route'], $item['params'] ?? []) }}" title="{{ $item['label'] }}" data-filter-tags="{{ strtolower($item['label']) }}">
                    <span class="nav-link-text">{{ $item['label'] }}</span>
                </a>
                @endif
                {{-- State of Business: inject My Partners & Custom Filters sub-nav --}}
                @if(($item['route'] ?? '') === 'state-of-business' && !$isLocked)
                <ul>
                    @php
                        $user = auth()->user();
                        $sobPartners = $user && $user->isAdmin()
                            ? \App\Models\Partner::orderBy('name')->get(['id', 'name'])
                            : ($user ? \App\Models\Partner::where('user_id', $user->id)->orderBy('name')->get(['id', 'name']) : collect());
                        $sobFilters = $user
                            ? \App\Models\SavedFilter::where('user_id', $user->id)->where('context', 'state-of-business')->orderBy('name')->get()
                            : collect();
                    @endphp
                    <li>
                        <a href="#" title="My Partners" data-filter-tags="my partners">
                            <span class="nav-link-text">My Partners</span>
                        </a>
                        <ul>
                            @foreach($sobPartners as $p)
                            <li>
                                <a href="{{ route('state-of-business', ['partner' => $p->id]) }}" data-filter-tags="{{ strtolower($p->name) }}">
                                    <span class="nav-link-text">{{ $p->name }}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </li>
                    <li>
                        <a href="#" title="Custom Filters" data-filter-tags="custom filters">
                            <span class="nav-link-text">Custom Filters</span>
                        </a>
                        <ul>
                            <li>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#newFilterModal" data-filter-tags="new filter">
                                    <span class="nav-link-text">New Filter</span>
                                </a>
                            </li>
                            @foreach($sobFilters as $sf)
                            <li>
                                <a href="#" class="apply-saved-filter" data-criteria="{{ json_encode($sf->criteria) }}" data-filter-tags="{{ strtolower($sf->name) }}">
                                    <span class="nav-link-text">{{ $sf->name }}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </li>
                </ul>
                @endif
            </li>
            @endforeach
        </ul>
        @endif
    </li>
    @endif
    @endforeach

    {{-- Feature Activation (always-on, gated by manage-activation gate) --}}
    @can('manage-activation')
    @if(! $focused || $devFocus === 'feature_activation')
    <li class="nav-title"><span>Feature Activation</span></li>
    <li class="nav-item">
        <a href="{{ route('admin.activation.flags') }}" title="Feature Activation" data-filter-tags="feature activation">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#toggle-left"></use>
            </svg>
            <span class="nav-link-text">Feature Activation</span>
        </a>
        <ul>
            <li><a href="{{ route('admin.activation.flags') }}" data-filter-tags="feature flags"><span class="nav-link-text">Feature Flags</span></a></li>
            <li><a href="{{ route('admin.activation.progression') }}" data-filter-tags="progression"><span class="nav-link-text">Progression</span></a></li>
            <li><a href="{{ route('admin.activation.paths') }}" data-filter-tags="activation paths"><span class="nav-link-text">Activation Paths</span></a></li>
            <li><a href="{{ route('admin.activation.log') }}" data-filter-tags="audit log"><span class="nav-link-text">Audit Log</span></a></li>
            <li><a href="{{ route('admin.activation.benchmarks') }}" data-filter-tags="benchmarks"><span class="nav-link-text">Benchmarks</span></a></li>
            <li><a href="{{ route('admin.products.index') }}" data-filter-tags="product bundles"><span class="nav-link-text">Product Bundles</span></a></li>
        </ul>
    </li>
    @endif
    @endcan

    {{-- Journeys — partner journeys, gated by partner_management permission so a
         role with no module access doesn't see it; hidden for the focused
         Workshops-only testing roles. --}}
    @if(Route::has('journeys.index') && auth()->user()?->hasModulePermission('partner_management') && ! in_array(auth()->user()->role?->slug, ['workshop_administrator', 'session_administrator'], true))
    <li>
        <a href="{{ route('journeys.index') }}" title="Journeys" data-filter-tags="journeys">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#map"></use>
            </svg>
            <span class="nav-link-text">Journeys</span>
        </a>
    </li>
    @endif

    {{-- Profile lives in the top-right avatar menu (User Profile + Logout), so it
         is intentionally not repeated in the sidebar — the sidebar shows only
         Welcome plus the modules a role has been granted. --}}

    @endunless
    @endauth
</ul>
