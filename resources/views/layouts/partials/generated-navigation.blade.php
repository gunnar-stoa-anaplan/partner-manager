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
    {{-- My Training (shown if pending activation paths exist) --}}
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

    {{-- Dynamic Module Navigation — rendered from config/modules.php sections --}}
    @foreach($navSections as $section)
    @php
        // Dev Focus: only show sections belonging to the focused feature
        if ($focused && $section['feature'] !== $devFocus) {
            continue;
        }

        // Filter to items whose named routes exist
        $validItems = array_filter($section['items'], fn($item) => Route::has($item['route']));
    @endphp
    @if(! empty($validItems))
    <li class="nav-item">
        <a href="#" title="{{ $section['label'] }}" data-filter-tags="{{ strtolower($section['label']) }}">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#{{ $section['icon'] }}"></use>
            </svg>
            <span class="nav-link-text">{{ $section['label'] }}</span>
        </a>
        <ul>
            @foreach($validItems as $item)
            <li>
                <a href="{{ route($item['route'], $item['params'] ?? []) }}" title="{{ $item['label'] }}" data-filter-tags="{{ strtolower($item['label']) }}">
                    <span class="nav-link-text">{{ $item['label'] }}</span>
                </a>
            </li>
            @endforeach
        </ul>
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
        </ul>
    </li>
    @endif
    @endcan

    {{-- User Profile (always visible) --}}
    <li>
        <a href="{{ route('profile.edit') }}" title="Profile" data-filter-tags="profile settings">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#user"></use>
            </svg>
            <span class="nav-link-text">Profile</span>
        </a>
    </li>

    @endauth
</ul>
