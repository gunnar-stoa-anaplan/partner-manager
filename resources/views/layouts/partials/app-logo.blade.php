@php
    $brand = $brand ?? false;
    $customLogo = \App\Models\AppSetting::get('branding_logo_path');
    $appName = \App\Models\AppSetting::get('branding_app_name') ?: 'Partner Success';
@endphp
<a href="{{ route('welcome') }}" class="app-logo flex-shrink-0 {{ $brand ? 'ps-brand' : '' }}" data-action="playsound"
   data-soundpath="media/sound/" data-soundfile="sawhisp.mp3">
    @if($customLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($customLogo))
        <img src="{{ \Illuminate\Support\Facades\Storage::url($customLogo) }}" alt="Logo" class="custom-logo"
             @if($brand) style="width:24px !important;height:24px !important;min-width:24px;max-width:24px;object-fit:contain;flex:0 0 auto;" @endif>
    @else
        <svg class="custom-logo" @if($brand) style="width:24px !important;height:24px !important;flex:0 0 auto;" @endif>
            <use href="/img/app-logo.svg#custom-logo"></use>
        </svg>
    @endif
    @if($brand)<span class="app-brand-title nav-link-text" style="color:#fff;font-weight:600;font-size:14px;white-space:nowrap;">{{ $appName }}</span>@endif
</a>

@if($brand)
<style>
    .app-logo.ps-brand {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 8px !important;
        padding: 0 12px !important;
        text-align: left !important;
    }
    .app-logo.ps-brand .custom-logo {
        flex: 0 0 auto !important;
        width: 24px !important;
        height: 24px !important;
        margin: 0 !important;
    }
    .app-logo.ps-brand .app-brand-title {
        color: #fff;
        font-weight: 600;
        font-size: 14px;
        white-space: nowrap;
        letter-spacing: .2px;
        margin: 0 !important;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endif
