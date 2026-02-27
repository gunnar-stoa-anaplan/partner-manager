<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} - {{ $guide->title }} | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('img/favicon-32x32.png') }}" type="image/png" sizes="32x32">
    @vite(['resources/sass/workshop-guides.scss', 'resources/scripts/workshop-guides/workshop-guides.js'])
</head>
<body data-search-url="{{ route('workshop-guides.search-index', ['guide' => $guide->slug]) }}" data-guide-slug="{{ $guide->slug }}">
    {{-- Top bar --}}
    <header class="docs-topbar">
        <div class="docs-topbar-inner">
            <button class="docs-sidebar-toggle" aria-label="Toggle navigation">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </button>
            <a href="{{ route('workshop-guides.index') }}" class="docs-logo">
                <img src="{{ asset('img/logo.svg') }}" alt="{{ config('app.name') }}">
                <span>{{ $guide->title }}</span>
            </a>
            <div class="docs-topbar-right">
                {{-- Search --}}
                <div class="docs-search" id="docsSearch">
                    <button class="docs-search-trigger" id="docsSearchTrigger" type="button" aria-label="Search workshop guide">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M10.5 10.5L14.5 14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <span class="docs-search-trigger-text">Search...</span>
                        <kbd class="docs-search-shortcut"><span class="docs-search-shortcut-key"></span>K</kbd>
                    </button>
                </div>

                {{-- Duration badge --}}
                @if($guide->estimated_duration_hours)
                    <span class="workshop-duration-badge">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M8 4v4l3 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        {{ $guide->estimated_duration_hours }}h
                    </span>
                @endif

                <a href="{{ route('workshop-guides.index') }}" class="workshop-back-link">All Workshops</a>
            </div>
        </div>
    </header>

    <div class="docs-wrapper">
        {{-- Left sidebar --}}
        <aside class="docs-sidebar">
            @include('workshop-guides.partials.navigation')
        </aside>

        {{-- Backdrop for mobile sidebar --}}
        <div class="docs-sidebar-backdrop"></div>

        {{-- Main content --}}
        <main class="docs-content">
            <article class="docs-article">
                {!! $content !!}
            </article>

            {{-- Prev / Next navigation --}}
            @include('workshop-guides.partials.prev-next')
        </main>

        {{-- Right TOC sidebar --}}
        <aside class="docs-toc">
            @include('workshop-guides.partials.toc')
        </aside>
    </div>

    {{-- Image zoom overlay --}}
    <div class="docs-zoom-overlay" id="docsZoomOverlay">
        <img src="" alt="" id="docsZoomImage">
    </div>

    {{-- Search modal --}}
    <div class="docs-search-backdrop" id="docsSearchBackdrop"></div>
    <div class="docs-search-modal" id="docsSearchModal">
        <div class="docs-search-input-wrapper">
            <svg class="docs-search-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                <path d="M10.5 10.5L14.5 14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <input
                type="text"
                class="docs-search-input"
                id="docsSearchInput"
                placeholder="Search this workshop..."
                autocomplete="off"
                spellcheck="false"
            >
            <kbd class="docs-search-esc">Esc</kbd>
        </div>
        <div class="docs-search-results" id="docsSearchResults">
            <div class="docs-search-empty" id="docsSearchEmpty">
                Type to search the workshop guide
            </div>
        </div>
    </div>
</body>
</html>
