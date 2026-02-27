<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workshop Guides | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('img/favicon-32x32.png') }}" type="image/png" sizes="32x32">
    @vite(['resources/sass/workshop-guides.scss'])
</head>
<body>
    <header class="docs-topbar">
        <div class="docs-topbar-inner">
            <a href="{{ route('workshop-guides.index') }}" class="docs-logo">
                <img src="{{ asset('img/logo.svg') }}" alt="{{ config('app.name') }}">
                <span>Workshop Guides</span>
            </a>
        </div>
    </header>

    <main class="workshop-catalog">
        <div class="workshop-catalog-header">
            <h1>Workshop Guides</h1>
            <p>Hands-on lab guides with step-by-step instructions, screenshots, and exercises.</p>
        </div>

        @if($guides->isEmpty())
            <div class="workshop-catalog-empty">
                <p>No workshop guides are published yet.</p>
            </div>
        @else
            <div class="workshop-catalog-grid">
                @foreach($guides as $guide)
                    <a href="{{ route('workshop-guides.show', $guide->slug) }}" class="workshop-card">
                        <div class="workshop-card-body">
                            <div class="workshop-card-meta">
                                @if($guide->product_area)
                                    <span class="workshop-card-badge">{{ $guide->product_area }}</span>
                                @endif
                                @if($guide->estimated_duration_hours)
                                    <span class="workshop-card-duration">
                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8 4v4l3 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        {{ $guide->estimated_duration_hours }}h
                                    </span>
                                @endif
                            </div>
                            <h3 class="workshop-card-title">{{ $guide->title }}</h3>
                            <p class="workshop-card-description">{{ Str::limit($guide->description, 150) }}</p>
                        </div>
                        <div class="workshop-card-footer">
                            <span class="workshop-card-pages">{{ count($guide->orderedPageSlugs()) }} pages</span>
                            <span class="workshop-card-arrow">&rarr;</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </main>
</body>
</html>
