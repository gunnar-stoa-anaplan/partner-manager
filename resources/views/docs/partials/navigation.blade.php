<nav class="docs-nav">
    @foreach($navigation as $section => $pages)
        <div class="docs-nav-section">
            <h6 class="docs-nav-heading">{{ $section }}</h6>
            <ul class="docs-nav-list">
                @foreach($pages as $title => $slug)
                    <li>
                        <a href="{{ route('docs.show', [$currentVersion, $slug]) }}"
                           class="{{ $currentPage === $slug ? 'active' : '' }}">
                            {{ $title }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
