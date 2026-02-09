@yield('css')

<!-- Vendor css -->
@vite(['node_modules/node-waves/dist/waves.min.css'])
@vite(['resources/webfonts/fontawesome/fontawesome.scss', 'resources/webfonts/smartadmin/sa-icons.scss'])
@vite(['resources/sass/smartapp.scss'])

<!-- Save/Load functionality JavaScript -->
@vite(['resources/scripts/core/saveloadscript.js'])
