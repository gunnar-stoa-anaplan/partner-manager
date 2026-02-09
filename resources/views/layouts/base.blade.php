<!DOCTYPE html>
<html lang="en" data-bs-theme="light" class="set-nav-dark">

<head>
    @include('layouts.partials/app-meta-title')

    @include('layouts.partials/app-head-css')
</head>

<body class="{{$bodyClass ?? ''}}">

@yield('content')

<!-- vendor-scripts -->
@include('layouts.partials/app-scripts')

</body>

</html>
