@yield('css')

<!-- Vendor css -->
@vite(['node_modules/node-waves/dist/waves.min.css'])
@vite(['resources/webfonts/fontawesome/fontawesome.scss', 'resources/webfonts/smartadmin/sa-icons.scss'])
@vite(['resources/sass/smartapp.scss'])

<!-- Default branding theme (overridden by user localStorage preference) -->
@php $defaultTheme = \App\Models\AppSetting::get('branding_theme'); @endphp
@if($defaultTheme)
    <link id="default-theme-style" rel="stylesheet" href="/css/themes/{{ $defaultTheme }}.css">
@endif

<!-- Save/Load functionality JavaScript -->
@vite(['resources/scripts/core/saveloadscript.js'])
