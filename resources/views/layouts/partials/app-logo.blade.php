<a href="{{ route('welcome') }}" class="app-logo flex-shrink-0" data-prefix="v1.0.0" data-action="playsound"
   data-soundpath="media/sound/" data-soundfile="sawhisp.mp3">
    <!-- <img src="img/logo.svg" alt="logo"> -->

    @php $customLogo = \App\Models\AppSetting::get('branding_logo_path'); @endphp
    @if($customLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($customLogo))
        <img src="{{ \Illuminate\Support\Facades\Storage::url($customLogo) }}" alt="Logo" class="custom-logo" style="max-height: 40px;">
    @else
        <svg class="custom-logo">
            <use href="/img/app-logo.svg#custom-logo"></use>
        </svg>
    @endif

    <!-- Logo Backdrop Animation -->
    <div class="logo-backdrop">
        <div class="blobs">
            <svg viewbox="0 0 1200 1200">
                <g class="blob blob-1">
                    <path d="M 100 600 q 0 -700, 500 -500 t 500 500 t -500 500 T 100 600 z"/>
                </g>
                <g class="blob blob-2">
                    <path d="M 100 600 q -50 -400, 500 -500 t 450 550 t -500 500 T 100 600 z"/>
                </g>
                <g class="blob blob-3">
                    <path d="M 100 600 q 0 -400, 500 -500 t 400 500 t -500 500 T 100 600 z"/>
                </g>
                <g class="blob blob-4">
                    <path d="M 150 600 q 0 -600, 500 -500 t 500 550 t -500 500 T 150 600 z"/>
                </g>
                <g class="blob blob-1 alt">
                    <path d="M 150 600 q 0 -600, 500 -500 t 500 550 t -500 500 T 150 600 z"/>
                </g>
                <g class="blob blob-2 alt">
                    <path d="M 100 600 q 100 -600, 500 -500 t 400 500 t -500 500 T 100 600 z"/>
                </g>
                <g class="blob blob-3 alt">
                    <path d="M 100 600 q 0 -400, 500 -500 t 400 500 t -500 500 T 100 600 z"/>
                </g>
                <g class="blob blob-4 alt">
                    <path d="M 150 600 q 0 -600, 500 -500 t 500 550 t -500 500 T 150 600 z"/>
                </g>
            </svg>
        </div>
    </div>
</a>
