@extends('layouts.vertical', ['pageTitle' => 'Role Management'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Role Management',
        'pageSubTitle1' => 'Administration',
        'pageSubTitle1Url' => route('admin.dashboard')
    ])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">
    Define roles and permissions for internal team members and portal users.
    Protected roles cannot be deleted but their display name, description, and permissions can be customized.
</p>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Tab Navigation --}}
<ul class="nav nav-tabs mb-g" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#internal" role="tab">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#users"></use></svg>
            Internal Roles
            <span class="badge bg-secondary ms-1">{{ $internalRoles->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#portal" role="tab">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#globe"></use></svg>
            Portal Roles
            <span class="badge bg-secondary ms-1">{{ $portalRoles->count() }}</span>
        </a>
    </li>
</ul>

<div class="tab-content">
    {{-- Internal Roles Tab --}}
    <div class="tab-pane fade show active" id="internal" role="tabpanel">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">Internal Team Roles</div>
                <a href="{{ route('admin.roles.create', ['type' => 'internal']) }}" class="btn btn-primary btn-sm">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                    New Internal Role
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px">#</th>
                                <th>Role</th>
                                <th>Slug</th>
                                <th class="text-center">Users</th>
                                <th class="text-center">Bypass</th>
                                <th>Landing Route</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($internalRoles as $role)
                            <tr>
                                <td class="text-muted small">{{ $role->sort_order }}</td>
                                <td>
                                    <div class="fw-500">{{ $role->display_name }}</div>
                                    @if($role->description)
                                        <div class="text-muted small text-truncate" style="max-width: 300px;">{{ $role->description }}</div>
                                    @endif
                                </td>
                                <td><code>{{ $role->slug }}</code></td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $role->users_count > 0 ? 'primary' : 'secondary' }}">{{ $role->users_count }}</span>
                                </td>
                                <td class="text-center">
                                    @if($role->bypass_permissions)
                                        <span class="badge bg-warning text-dark">Bypass</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $role->landing_route ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    @if($role->is_protected)
                                        <span class="badge bg-light text-muted me-1" title="Protected role">
                                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#lock"></use></svg>
                                        </span>
                                    @endif
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit"></use></svg>
                                    </a>
                                    @if(!$role->is_protected)
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                onclick="return confirm('Delete role &quot;{{ $role->display_name }}&quot;? This cannot be undone.')">
                                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#trash-2"></use></svg>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No internal roles defined.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Portal Roles Tab --}}
    <div class="tab-pane fade" id="portal" role="tabpanel">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">Portal Roles</div>
                <a href="{{ route('admin.roles.create', ['type' => 'portal']) }}" class="btn btn-primary btn-sm">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                    New Portal Role
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px">#</th>
                                <th>Role</th>
                                <th>Slug</th>
                                <th class="text-center">Contacts</th>
                                <th>Dashboard Group</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($portalRoles as $role)
                            <tr>
                                <td class="text-muted small">{{ $role->sort_order }}</td>
                                <td>
                                    <div class="fw-500">{{ $role->display_name }}</div>
                                    @if($role->description)
                                        <div class="text-muted small text-truncate" style="max-width: 300px;">{{ $role->description }}</div>
                                    @endif
                                </td>
                                <td><code>{{ $role->slug }}</code></td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $role->contacts_count > 0 ? 'primary' : 'secondary' }}">{{ $role->contacts_count }}</span>
                                </td>
                                <td>
                                    @if($role->dashboard_group)
                                        <span class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($role->dashboard_group) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if($role->is_protected)
                                        <span class="badge bg-light text-muted me-1" title="Protected role">
                                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#lock"></use></svg>
                                        </span>
                                    @endif
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit"></use></svg>
                                    </a>
                                    @if(!$role->is_protected)
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                                onclick="return confirm('Delete role &quot;{{ $role->display_name }}&quot;? This cannot be undone.')">
                                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#trash-2"></use></svg>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No portal roles defined.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
