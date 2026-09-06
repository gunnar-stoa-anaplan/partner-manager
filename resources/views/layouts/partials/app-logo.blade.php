@php
    $customLogo = \App\Models\AppSetting::get('branding_logo_path');
    $appName = \App\Models\AppSetting::get('branding_app_name') ?: 'Partner Success';
@endphp
<a href="{{ route('welcome') }}" class="app-logo flex-shrink-0 ps-brand" data-action="playsound"
   data-soundpath="media/sound/" data-soundfile="sawhisp.mp3">
    @if($customLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($customLogo))
        <img src="{{ \Illuminate\Support\Facades\Storage::url($customLogo) }}" alt="Logo" class="custom-logo">
    @else
        <svg class="custom-logo">
            <use href="/img/app-logo.svg#custom-logo"></use>
        </svg>
    @endif
    <span class="app-brand-title nav-link-text">{{ $appName }}</span>
</a>

<style>
    .app-logo.ps-brand {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        padding-left: 16px;
        text-align: left;
    }
    .app-logo.ps-brand .custom-logo {
        flex: 0 0 auto;
        width: 26px;
        height: 26px;
    }
    .app-logo.ps-brand .app-brand-title {
        color: #fff;
        font-weight: 600;
        font-size: 15px;
        white-space: nowrap;
        letter-spacing: .2px;
    }
</style>
