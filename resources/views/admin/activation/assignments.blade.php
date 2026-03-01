@extends('layouts.vertical', ['pageTitle' => 'Assignments — ' . $path->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'User Assignments',
        'pageSubTitle1' => 'Feature Activation',
        'pageSubTitle1Url' => route('admin.activation.paths'),
        'pageSubTitle2' => 'Activation Paths',
        'pageSubTitle2Url' => route('admin.activation.paths'),
        'pageSubTitle3' => $path->name,
        'pageSubTitle3Url' => route('admin.activation.paths.show', $path),
        'pageSubText' => 'Manage which users are assigned to this activation path',
    ])
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Assign by Role --}}
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title mb-0">Assign by Role</div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.activation.paths.assign-role', $path) }}">
            @csrf
            <div class="row align-items-end">
                <div class="col-auto">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="">Select a role…</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i data-feather="users" class="icon-inline"></i> Assign All Users with Role
                    </button>
                </div>
            </div>
            <div class="form-text mt-2">Assigns every user with the selected role who is not already on this path.</div>
        </form>
    </div>
</div>

{{-- Assign Individual Users --}}
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title mb-0">Assign Individual Users</div>
    </div>
    <div class="card-body">
        @if($availableUsers->isEmpty())
            <p class="text-muted mb-0">All users are already assigned to this path.</p>
        @else
            <form method="POST" action="{{ route('admin.activation.paths.assign-users', $path) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Select users to assign</label>
                    <select name="user_ids[]" class="form-select" multiple size="6">
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }}) — {{ $user->role?->display_name ?? 'No Role' }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple users.</div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-feather="user-plus" class="icon-inline"></i> Assign Selected
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Current Assignments --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title mb-0">Assigned Users ({{ $path->users->count() }})</div>
    </div>
    <div class="card-body p-0">
        @if($path->users->isEmpty())
            <div class="p-3 text-muted">No users assigned yet.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Assigned At</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($path->users as $user)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.activation.user-progress', $user) }}">{{ $user->name }}</a>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge bg-secondary">{{ $user->role?->display_name ?? 'No Role' }}</span></td>
                                <td>{{ $user->pivot->assigned_at ? \Carbon\Carbon::parse($user->pivot->assigned_at)->format('M d, Y') : '—' }}</td>
                                <td>
                                    @if($user->pivot->completed_at)
                                        <span class="badge bg-success">Completed {{ \Carbon\Carbon::parse($user->pivot->completed_at)->format('M d, Y') }}</span>
                                    @elseif($user->pivot->training_bypassed_at)
                                        <span class="badge bg-info">Early Access</span>
                                    @else
                                        <span class="text-muted">In Progress</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        @if(! $user->pivot->completed_at)
                                            @if($user->pivot->training_bypassed_at)
                                            <form method="POST" action="{{ route('admin.activation.paths.revoke-bypass', [$path, $user]) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Revoke early access">
                                                    <i class="fal fa-lock"></i>
                                                </button>
                                            </form>
                                            @else
                                            <form method="POST" action="{{ route('admin.activation.paths.bypass-training', [$path, $user]) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-info" title="Grant early access">
                                                    <i class="fal fa-unlock"></i>
                                                </button>
                                            </form>
                                            @endif
                                        @endif
                                        <form method="POST" action="{{ route('admin.activation.paths.unassign-user', [$path, $user]) }}" class="d-inline"
                                              onsubmit="return confirm('Remove {{ $user->name }} from this path?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove from path">
                                                <i data-feather="user-minus" class="icon-inline"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
