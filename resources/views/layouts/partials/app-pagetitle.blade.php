@if (!empty($pageTitle))
    <h1 class="subheader-title mb-2">{{ $pageTitle }}</h1>
@endif

@if (!empty($pageTitle))
    <nav class="app-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb ms-0 text-muted mb-0">
            @if (!empty($pageSubTitle1))
                <li class="breadcrumb-item">
                    @if (!empty($pageSubTitle1Url))
                        <a href="{{ $pageSubTitle1Url }}">{{ $pageSubTitle1 }}</a>
                    @else
                        {{ $pageSubTitle1 }}
                    @endif
                </li>
            @endif

            @if (!empty($pageSubTitle2))
                <li class="breadcrumb-item">
                    @if (!empty($pageSubTitle2Url))
                        <a href="{{ $pageSubTitle2Url }}">{{ $pageSubTitle2 }}</a>
                    @else
                        {{ $pageSubTitle2 }}
                    @endif
                </li>
            @endif

            <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
        </ol>
    </nav>
@endif

@if (!empty($pageSubText) && $pageSubText !== 'false')
    <h6 class="mt-3 mb-4 fst-italic">{!! $pageSubText !!}</h6>
@endif
