<header class="app-header">
    <!-- Collapse icon -->
    <div class="d-flex flex-grow-1 w-100 me-auto align-items-center">

        <!-- App logo — mobile only; on desktop the sidebar carries the brand -->
        <span class="d-lg-none">@include('layouts.partials/app-logo')</span>

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

        <div class="app-search position-relative" style="flex: 1; max-width: 400px;">
            <input type="text" class="form-control" id="global-search-input" placeholder="Search partners, people..." autocomplete="off">
            <div id="global-search-results" class="dropdown-menu w-100 shadow-lg" style="display:none; max-height: 400px; overflow-y: auto;"></div>
        </div>
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

    <!-- Dev Role Switcher (local only) -->
    @include('layouts.partials.app-dev-switcher')

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('global-search-input');
    var resultsBox = document.getElementById('global-search-results');
    if (!searchInput || !resultsBox) return;

    var debounceTimer = null;
    var searchUrl = @json(route('api.search'));

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        var q = this.value.trim();
        if (q.length < 2) {
            resultsBox.style.display = 'none';
            return;
        }
        debounceTimer = setTimeout(function() {
            fetch(searchUrl + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.results || data.results.length === 0) {
                    resultsBox.innerHTML = '<div class="px-3 py-3 text-muted text-center"><i class="fal fa-search me-1"></i>No results for &ldquo;' + q.replace(/</g, '&lt;') + '&rdquo;</div>';
                    resultsBox.style.display = 'block';
                    return;
                }
                var html = '';
                data.results.forEach(function(r) {
                    html += '<a href="' + r.url + '" class="dropdown-item d-flex align-items-center py-2 px-3">' +
                        '<div class="width-3 height-3 rounded-circle bg-' + r.color + ' bg-opacity-10 d-flex align-items-center justify-content-center me-2 flex-shrink-0">' +
                        '<i class="fal ' + r.icon + ' text-' + r.color + ' fs-sm"></i></div>' +
                        '<div class="text-truncate"><div class="fw-500 text-truncate">' + r.label + '</div>' +
                        '<div class="text-muted fs-nano text-truncate">' + r.sub + '</div></div></a>';
                });
                resultsBox.innerHTML = html;
                resultsBox.style.display = 'block';
            })
            .catch(function() {
                resultsBox.style.display = 'none';
            });
        }, 250);
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            resultsBox.style.display = 'none';
            this.blur();
        }
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.style.display = 'none';
        }
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2 && resultsBox.innerHTML) {
            resultsBox.style.display = 'block';
        }
    });
});
</script>
