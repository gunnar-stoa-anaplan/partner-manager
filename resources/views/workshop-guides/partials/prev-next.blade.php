@php
    $prevTitle = null;
    $nextTitle = null;

    if ($surrounding['prev'] || $surrounding['next']) {
        foreach ($navigation as $pages) {
            if (is_array($pages)) {
                foreach ($pages as $title => $slug) {
                    if ($slug === $surrounding['prev']) {
                        $prevTitle = $title;
                    }
                    if ($slug === $surrounding['next']) {
                        $nextTitle = $title;
                    }
                }
            }
        }
    }
@endphp

@if($surrounding['prev'] || $surrounding['next'])
    <nav class="workshop-prev-next">
        @if($surrounding['prev'])
            <a href="{{ route('workshop-guides.show', [$guide->slug, $surrounding['prev']]) }}" class="workshop-prev-next-link workshop-prev-link">
                <span class="workshop-prev-next-label">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                        <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Previous
                </span>
                <span class="workshop-prev-next-title">{{ $prevTitle }}</span>
            </a>
        @else
            <span></span>
        @endif

        @if($surrounding['next'])
            <a href="{{ route('workshop-guides.show', [$guide->slug, $surrounding['next']]) }}" class="workshop-prev-next-link workshop-next-link">
                <span class="workshop-prev-next-label">
                    Next
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                        <path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="workshop-prev-next-title">{{ $nextTitle }}</span>
            </a>
        @endif
    </nav>
@endif
