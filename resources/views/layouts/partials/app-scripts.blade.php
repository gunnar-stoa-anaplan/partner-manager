<!-- Core scripts -->
@vite([ 'resources/scripts/core/smartNavigation.js',
        'resources/scripts/core/smartFilter.js',
        'resources/scripts/core/smartSlimscroll.js'])

<!-- App.js -->
@vite(['resources/scripts/core/smartApp.js'])

<!-- ApexCharts (bundled from npm to avoid CDN version mismatch) -->
@vite(['resources/scripts/core/apexcharts-global.js'])

<!-- AI Chat sidebar -->
@vite(['resources/scripts/pages/ai-chat.js'])

<!-- Shepherd.js guided tour runner -->
@vite(['resources/scripts/pages/training/tour-runner.js'])

@yield('scripts')
@stack('scripts')
