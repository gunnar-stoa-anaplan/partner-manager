@extends('layouts.vertical', ['pageTitle' => 'Edit User'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Edit User', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubTitle2' => 'User Management', 'pageSubTitle2Url' => route('users.index')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Update this user's name, email, role, and avatar. Role changes take effect immediately — Admin users get full system access, PSMs can manage assigned partners, and Managers have team oversight. Leave the password field blank to keep the current password.</p>

@if($user->isSyncedFromDirectory())
    <div class="alert alert-warning d-flex align-items-center mb-4">
        <svg class="sa-icon sa-icon-sm me-2"><use href="/icons/sprite.svg#refresh-cw"></use></svg>
        <div>
            <strong>Synced from {{ ucfirst($user->directory_source) }}</strong>
            — Name, email, and manager are managed by directory sync and cannot be edited here.
            @if($user->last_synced_at)
                <span class="text-muted ms-1">Last synced {{ $user->last_synced_at->diffForHumans() }}</span>
            @endif
        </div>
    </div>
@endif

<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Edit User: {{ $user->name }}</div>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" for="name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required {{ $user->isFieldReadOnly('name') ? 'disabled' : '' }}>
                        @if($user->isFieldReadOnly('name'))
                            <div class="form-text text-warning">This field is managed by directory sync</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required {{ $user->isFieldReadOnly('email') ? 'disabled' : '' }}>
                        @if($user->isFieldReadOnly('email'))
                            <div class="form-text text-warning">This field is managed by directory sync</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->slug }}" {{ old('role', $user->role?->slug) === $role->slug ? 'selected' : '' }}>
                                    {{ $role->display_name }}{{ $role->description ? ' - ' . $role->description : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="manager_id">Reports To</label>
                        <select class="form-select" id="manager_id" name="manager_id" {{ $user->isFieldReadOnly('manager_id') ? 'disabled' : '' }}>
                            <option value="">-- None --</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ old('manager_id', $user->manager_id) == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }} ({{ $manager->role?->display_name ?? 'Unknown' }})
                                </option>
                            @endforeach
                        </select>
                        @if($user->isFieldReadOnly('manager_id'))
                            <div class="form-text text-warning">This field is managed by directory sync</div>
                        @else
                            <div class="form-text">Assign this user's direct manager in the reporting hierarchy</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="avatar">Profile Picture</label>
                        @if($user->avatar)
                            <div class="mb-2">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                        <div class="form-text">Upload a new picture to replace the current one (JPEG, PNG, GIF - Max 2MB)</div>
                    </div>

                    <hr>

                    <div class="alert alert-info">
                        Leave password fields empty to keep the current password.
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">New Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <div class="form-text">Minimum 8 characters (optional)</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Confirm New Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
