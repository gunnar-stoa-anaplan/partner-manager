@yield('css')

<!-- Vendor css -->
@vite(['node_modules/node-waves/dist/waves.min.css'])
@vite(['resources/webfonts/fontawesome/fontawesome.scss', 'resources/webfonts/smartadmin/sa-icons.scss'])
@vite(['resources/sass/smartapp.scss'])

<!-- Default branding theme (overridden by user localStorage preference) -->
@php
    $defaultTheme = \App\Models\AppSetting::get('branding_theme');
    $customCssPath = \App\Models\AppSetting::get('branding_custom_css_path');
@endphp
@if($defaultTheme === 'custom' && $customCssPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($customCssPath))
    <link id="default-theme-style" rel="stylesheet" href="{{ \Illuminate\Support\Facades\Storage::url($customCssPath) }}">
@elseif($defaultTheme && $defaultTheme !== 'custom')
    <link id="default-theme-style" rel="stylesheet" href="/css/themes/{{ $defaultTheme }}.css">
@endif

<!-- Custom brand color overrides -->
@php $customColorsCss = \App\Models\AppSetting::get('branding_custom_colors_css'); @endphp
@if($customColorsCss)
    <style id="custom-brand-colors">{!! $customColorsCss !!}</style>
@endif

<!-- Save/Load functionality JavaScript -->
@vite(['resources/scripts/core/saveloadscript.js'])
