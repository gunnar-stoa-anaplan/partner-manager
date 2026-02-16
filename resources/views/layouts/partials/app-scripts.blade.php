<!-- Core scripts -->
@vite([ 'resources/scripts/core/smartNavigation.js',
        'resources/scripts/core/smartFilter.js',
        'resources/scripts/core/smartSlimscroll.js'])

<!-- App.js -->
@vite(['resources/scripts/core/smartApp.js'])

<!-- ApexCharts (bundled from npm to avoid CDN version mismatch) -->
@vite(['resources/scripts/core/apexcharts-global.js'])

@yield('scripts')
@stack('scripts')
