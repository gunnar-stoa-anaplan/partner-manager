@extends('layouts.vertical', ['pageTitle' => 'Assignments — ' . $path->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'User Assignments',
        'pageSubTitle1' => $path->name,
        'pageSubTitle1Url' => route('admin.activation.paths.show', $path),
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

{{-- Assign Users --}}
<div class="card shadow-0 mb-g">
    <div class="card-header">
        <div class="card-title mb-0">Assign Users</div>
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
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }}) — {{ ucfirst($user->role) }}</option>
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
                            <th>Completed At</th>
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
                                <td><span class="badge bg-secondary">{{ ucfirst($user->role) }}</span></td>
                                <td>{{ $user->pivot->assigned_at ? \Carbon\Carbon::parse($user->pivot->assigned_at)->format('M d, Y') : '—' }}</td>
                                <td>
                                    @if($user->pivot->completed_at)
                                        <span class="badge bg-success">{{ \Carbon\Carbon::parse($user->pivot->completed_at)->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-muted">In Progress</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.activation.paths.unassign-user', [$path, $user]) }}" class="d-inline"
                                          onsubmit="return confirm('Remove {{ $user->name }} from this path?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i data-feather="user-minus" class="icon-inline"></i> Remove
                                        </button>
                                    </form>
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
