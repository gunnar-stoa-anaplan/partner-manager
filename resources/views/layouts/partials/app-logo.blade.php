<a href="{{ route('welcome') }}" class="app-logo flex-shrink-0" data-prefix="v1.0.0" data-action="playsound"
   data-soundpath="media/sound/" data-soundfile="sawhisp.mp3">
    <!-- <img src="img/logo.svg" alt="logo"> -->

    @php $customLogo = \App\Models\AppSetting::get('branding_logo_path'); @endphp
    @if($customLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($customLogo))
        <img src="{{ \Illuminate\Support\Facades\Storage::url($customLogo) }}" alt="Logo" class="custom-logo">
    @else
        <svg class="custom-logo">
            <use href="/img/app-logo.svg#custom-logo"></use>
        </svg>
    @endif
</a>
