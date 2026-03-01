<!DOCTYPE html>
<html lang="en" data-bs-theme="light" class="set-nav-dark">

<head>
    @include('layouts.partials/app-meta-title')

    @include('layouts.partials/app-head-css')
</head>

<body>
<div class="app-wrap">
    @include('layouts.partials/app-header')

    @include('layouts.partials/app-sidebar')

    @include('layouts.partials/app-update-banner')

    @include('layouts.partials/app-license-banner')

    <main class="app-body">

        @include('layouts.partials/app-impersonation-banner')

        <div class="app-content">
            <div class="content-wrapper">

                @yield('page-title')

                <div class="main-content {{$extraClass ?? ''}}">

                    @yield('content')

                </div>

            </div>

            @yield('extra-content')

        </div>

        @include('layouts.partials/app-footer')

    </main>

    @include('layouts.partials/app-drawer')

    @include('layouts.partials/app-settings')

    @include('layouts.partials/app-unlock-toast')

</div>

@include('layouts.partials/app-scripts')
</body>

</html>
