<header class="app-header">
    <!-- Collapse icon -->
    <div class="d-flex flex-grow-1 w-100 me-auto align-items-center">

        <!-- App logo -->
        @include('layouts.partials/app-logo')

        <button class="mobile-menu-icon me-2 d-flex d-sm-flex d-md-flex d-lg-none flex-shrink-0"
                data-action="toggle-swap" data-toggleclass="app-mobile-menu-open" aria-label="Toggle Mobile Menu">
            <svg class="sa-icon">
                <use href="/icons/sprite.svg#menu"></use>
            </svg>
        </button>

        <!-- Collapse icon -->
        <button type="button" class="collapse-icon me-3 d-none d-lg-inline-flex d-xl-inline-flex d-xxl-inline-flex"
                data-action="toggle" data-class="set-nav-minified" aria-label="Toggle Navigation Size">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 5 8">
                <polygon fill="#878787" points="4.5,1 3.8,0.2 0,4 3.8,7.8 4.5,7 1.5,4"/>
            </svg>
        </button>

        <form class="app-search" role="search"  autocomplete="off">
            <input type="text" class="form-control" placeholder="Search for anything">
        </form>
    </div>

    <!-- Settings -->
    <button type="button" class="btn btn-system hidden-mobile" data-action="toggle-swap" data-toggleclass="open"
            data-target="aside.js-drawer-settings" aria-label="Open Settings">
        <svg class="sa-icon sa-icon-2x">
            <use href="/icons/sprite.svg#settings"></use>
        </svg>
    </button>

    <!-- Theme modes -->
    <button type="button" class="btn btn-system" data-action="toggle-theme" aria-label="Toggle Dark Mode"
            aria-pressed="false">
        <svg class="sa-icon sa-icon-2x sa-mode-light">
            <use href="/icons/sprite.svg#sun"></use>
        </svg>
        <svg class="sa-icon sa-icon-2x sa-mode-dark">
            <use href="/icons/sprite.svg#moon"></use>
        </svg>
    </button>

    <!-- Sidebar -->
    <button type="button" class="btn btn-system d-none d-sm-block d-sm-none d-md-none d-lg-block"
            data-action="toggle-swap" data-toggleclass="open" data-target="aside.js-app-drawer"
            aria-label="Open Sidebar">
        <svg class="sa-icon sa-icon-2x">
            <use href="/icons/sprite.svg#aperture"></use>
        </svg>
    </button>

    <!-- Full Screen Mode -->
    <button type="button" class="btn btn-system d-none d-sm-block d-sm-none d-md-none d-lg-block"
            data-action="app-fullscreen" aria-label="Toggle Dark Mode" aria-pressed="false">
        <svg class="sa-icon sa-icon-2x sa-fullscreen-on">
            <use href="/icons/sprite.svg#minimize"></use>
        </svg>
        <svg class="sa-icon sa-icon-2x sa-fullscreen-off">
            <use href="/icons/sprite.svg#maximize"></use>
        </svg>
    </button>

    <!-- Notifications -->
    <button type="button" class="btn btn-system dropdown-toggle no-arrow" data-bs-toggle="dropdown"
            aria-expanded="false" aria-label="Open Notifications">
        <span class="badge badge-icon pos-top pos-end">5</span>
        <svg class="sa-icon sa-icon-2x">
            <use href="/icons/sprite.svg#bell"></use>
        </svg>
    </button>

    <!-- Notifications dropdown -->
    @include('layouts.partials/app-notifications')

    <!-- Profile -->
    @auth
    <button type="button" data-bs-toggle="dropdown" title="{{ Auth::user()->email }}"
            class="btn-system bg-transparent d-flex flex-shrink-0 align-items-center justify-content-center"
            aria-label="Open Profile Dropdown">
        <img src="{{ Auth::user()->avatar_url }}" class="profile-image profile-image-md rounded-circle"
             alt="{{ Auth::user()->name }}">
    </button>

    <!-- Profile dropdown -->
    <div class="dropdown-menu dropdown-menu-animated">
        <div class="notification-header rounded-top mb-2">
            <div class="d-flex flex-row align-items-center mt-1 mb-1 color-white">
                <span class="status status-success d-inline-block me-2">
                    <img src="{{ Auth::user()->avatar_url }}" class="profile-image rounded-circle" alt="{{ Auth::user()->name }}">
                </span>
                <div class="info-card-text">
                    <div class="fs-lg text-truncate text-truncate-lg">{{ Auth::user()->name }}</div>
                    <span class="text-truncate text-truncate-md opacity-80 fs-sm">{{ Auth::user()->email }}</span>
                </div>
            </div>
        </div>

        <div class="dropdown-divider m-0"></div>

        <a href="{{ route('profile.edit') }}" class="dropdown-item">
            <span>User Profile</span>
        </a>
        <a href="#" class="dropdown-item" data-action="toggle-swap" data-toggleclass="open"
           data-target="aside.js-drawer-settings" role="button">
            <span data-i18n="drpdwn.settings">Settings</span>
        </a>

        <div class="dropdown-divider m-0"></div>

        <a href="#" class="dropdown-item d-flex justify-content-between align-items-center" data-action="app-fullscreen"
           aria-pressed="false" role="button">
            <span data-i18n="drpdwn.fullscreen">Fullscreen</span>
            <b class="text-muted fs-nano px-2 rounded font-monospace align-self-center border">F11</b>
        </a>

        <div class="dropdown-divider m-0"></div>

        <a class="dropdown-item py-3 fw-500" href="#"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <span class="text-danger">Logout</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
    @endauth
</header>
