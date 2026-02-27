@if(count($toc) > 0)
    <nav class="docs-toc-nav">
        <h6 class="docs-toc-heading">On This Page</h6>
        <ul class="docs-toc-list">
            @foreach($toc as $heading)
                <li class="docs-toc-item {{ $heading['level'] === 3 ? 'docs-toc-h3' : '' }}">
                    <a href="#{{ $heading['id'] }}">{{ $heading['text'] }}</a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
