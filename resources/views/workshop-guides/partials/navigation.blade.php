<nav class="docs-nav">
    @php $stepNumber = 0; @endphp
    @foreach($navigation as $section => $pages)
        @if(is_array($pages))
            <div class="docs-nav-section">
                <h6 class="docs-nav-heading">{{ $section }}</h6>
                <ul class="docs-nav-list">
                    @foreach($pages as $title => $slug)
                        @php $stepNumber++; @endphp
                        <li>
                            <a href="{{ route('workshop-guides.show', [$guide->slug, $slug]) }}"
                               class="{{ $currentPage === $slug ? 'active' : '' }}"
                               data-page-slug="{{ $slug }}">
                                <span class="workshop-step-number">{{ $stepNumber }}</span>
                                {{ $title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endforeach
</nav>
