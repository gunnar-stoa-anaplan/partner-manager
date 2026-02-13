<!-- Core scripts -->
@vite([ 'resources/scripts/core/smartNavigation.js',
        'resources/scripts/core/smartFilter.js',
        'resources/scripts/core/smartSlimscroll.js'])

<!-- App.js -->
@vite(['resources/scripts/core/smartApp.js'])

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@yield('scripts')
@stack('scripts')
