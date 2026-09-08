@extends('layouts.vertical', ['pageTitle' => 'Create User'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Create User', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubTitle2' => 'User Management', 'pageSubTitle2Url' => route('users.index')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Add a new user to Partner Manager. Set their name, email, password, and role. Admin users get full system access, PSMs can be assigned to manage specific partners, and Managers have team oversight capabilities. The user will be able to log in immediately after creation.</p>

<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Create New User</div>
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

                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="role">Primary Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->slug }}" {{ old('role') === $role->slug ? 'selected' : '' }}>
                                    {{ $role->display_name }}{{ $role->description ? ' - ' . $role->description : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Drives the landing page. Permissions are the union of all roles below.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="additional_roles">Additional Roles</label>
                        <select class="form-select" id="additional_roles" name="additional_roles[]" multiple size="6">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected(in_array($role->id, old('additional_roles', [])))>
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Optional. Hold Ctrl/Cmd to select more than one — the user gets the combined access of the primary role plus these.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="manager_id">Reports To</label>
                        <select class="form-select" id="manager_id" name="manager_id">
                            <option value="">-- None --</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }} ({{ $manager->role?->display_name ?? 'Unknown' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Assign this user's direct manager in the reporting hierarchy</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="avatar">Profile Picture</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                        <div class="form-text">Upload a profile picture (JPEG, PNG, GIF - Max 2MB)</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Minimum 8 characters</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
