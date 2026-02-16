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
        .nav-link-portal { color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.875rem; }
        .nav-link-portal:hover, .nav-link-portal.active { color: #fff; background: rgba(255,255,255,0.15); }
    </style>
</head>
<body>
    @php $portalUser = Auth::guard('portal')->user(); @endphp

    {{-- Impersonation Banner --}}
    @if(session('impersonating_portal'))
    <div style="background: #dc3545; color: #fff; padding: 8px 0; text-align: center; font-size: 0.875rem; position: sticky; top: 0; z-index: 1100;">
        <div class="container d-flex justify-content-center align-items-center gap-3 flex-wrap">
            <span>
                <strong>Impersonation Mode</strong> &mdash; Viewing as
                <strong>{{ $portalUser?->full_name }}</strong>
                (by {{ session('impersonator_user_name', 'Admin') }})
            </span>
            <form action="{{ route('portal.impersonate.stop') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-light fw-bold">Return to Partner Manager</button>
            </form>
        </div>
    </div>
    @endif

    {{-- Portal Navigation --}}
    <nav class="navbar navbar-expand-lg portal-nav navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('portal.dashboard') }}">
                Partner Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            @auth('portal')
            <div class="collapse navbar-collapse" id="portalNav">
                <ul class="navbar-nav me-auto gap-1">
                    <li class="nav-item">
                        <a href="{{ route('portal.dashboard') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">Dashboard</a>
                    </li>

                    @if($portalUser->canAccess('goals'))
                    <li class="nav-item">
                        <a href="{{ route('portal.goals.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.goals.*') ? 'active' : '' }}">Goals</a>
                    </li>
                    @endif
                    @if($portalUser->canAccess('certifications'))
                    <li class="nav-item">
                        <a href="{{ route('portal.certifications.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.certifications.*') ? 'active' : '' }}">Certifications</a>
                    </li>
                    @endif
                    @if($portalUser->canAccess('calendar'))
                    <li class="nav-item">
                        <a href="{{ route('portal.calendar.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.calendar.*') ? 'active' : '' }}">Calendar</a>
                    </li>
                    @endif
                    @if($portalUser->canAccess('contacts'))
                    <li class="nav-item">
                        <a href="{{ route('portal.contacts.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.contacts.*') ? 'active' : '' }}">Team</a>
                    </li>
                    @endif
                    @if($portalUser->canAccess('launch_programs'))
                    <li class="nav-item">
                        <a href="{{ route('portal.launch-programs.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.launch-programs.*') ? 'active' : '' }}">Launch Programs</a>
                    </li>
                    @endif
                    @if($portalUser->canAccess('path'))
                    <li class="nav-item">
                        <a href="{{ route('portal.path.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.path.*') ? 'active' : '' }}">Path</a>
                    </li>
                    @endif
                    @if($portalUser->canAccess('deal_registrations'))
                    <li class="nav-item">
                        <a href="{{ route('portal.deal-registrations.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.deal-registrations.*') ? 'active' : '' }}">Deal Registrations</a>
                    </li>
                    @endif
                    @if($portalUser->canAccess('projects'))
                    <li class="nav-item">
                        <a href="{{ route('portal.projects.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.projects.*') ? 'active' : '' }}">Projects</a>
                    </li>
                    @endif
                    @if($portalUser->canAccess('cases'))
                    <li class="nav-item">
                        <a href="{{ route('portal.cases.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.cases.*') ? 'active' : '' }}">Cases</a>
                    </li>
                    @endif

                    {{-- All roles --}}
                    <li class="nav-item">
                        <a href="{{ route('portal.enablement.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.enablement.*') ? 'active' : '' }}">Enablement</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal.blueprints.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.blueprints.*') ? 'active' : '' }}">Blueprints</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal.labs.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.labs.*') ? 'active' : '' }}">Labs</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal.workspaces.index') }}"
                           class="nav-link-portal {{ request()->routeIs('portal.workspaces.*') ? 'active' : '' }}">Workspaces</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark" style="font-size: 0.7rem;">{{ \App\Models\Contact::PORTAL_ROLES[$portalUser->portal_role] ?? $portalUser->portal_role }}</span>
                    <span class="text-white-50" style="font-size: 0.875rem;">{{ $portalUser->full_name }}</span>
                    @if(session('impersonating_portal'))
                        <form action="{{ route('portal.impersonate.stop') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning fw-bold">Return to Admin</button>
                        </form>
                    @else
                        <form action="{{ route('portal.logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                        </form>
                    @endif
                </div>
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

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
