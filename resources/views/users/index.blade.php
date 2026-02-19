@extends('layouts.vertical', ['pageTitle' => 'User Management'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'User Management', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">User Management lets you create, edit, and deactivate user accounts and assign roles — Admin, PSM, or Manager. Each user's role determines what they can see and do in the application. Use this view to onboard new team members, update permissions, and manage the team that runs your partner program.</p>

<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">User Management & Roles</div>
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#user-plus"></use></svg>
                    Add New User
                </a>
            </div>
            <div class="card-body">
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

                <div class="alert alert-info mb-3">
                    Manage users and assign roles. PSMs can be assigned to manage partners.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100" id="usersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Partners</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                                            <strong>{{ $user->name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-danger">Admin</span>
                                        @elseif($user->role === 'psm')
                                            <span class="badge bg-success">PSM</span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($user->role) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $user->partners_count }} Partners</span>
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td class="text-nowrap">
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('impersonate.start', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-info" title="Login as {{ $user->name }}">
                                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#log-in"></use></svg>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit User">
                                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit"></use></svg>
                                        </a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?')" title="Delete">
                                                <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#trash-2"></use></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        No users found. <a href="{{ route('users.create') }}">Create your first user</a>
                                    </td>
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
