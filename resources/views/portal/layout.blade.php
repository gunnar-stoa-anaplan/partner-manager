<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'Partner Portal' }} — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .portal-nav { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .portal-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    {{-- Portal Navigation --}}
    <nav class="navbar navbar-expand-lg portal-nav navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('portal.dashboard') }}">
                Partner Portal
            </a>
            @auth('portal')
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('portal.dashboard') }}" class="text-white text-decoration-none">Dashboard</a>
                <a href="{{ route('portal.deal-registrations.index') }}" class="text-white text-decoration-none">Deal Registrations</a>
                <span class="text-white-50">|</span>
                <span class="text-white-50">{{ Auth::guard('portal')->user()->full_name }}</span>
                <form action="{{ route('portal.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            </div>
            @endauth
        </div>
    </nav>

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
