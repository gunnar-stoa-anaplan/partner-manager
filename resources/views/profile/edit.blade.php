@extends('layouts.vertical', ['pageTitle' => 'User Profile', 'extraClass' => 'layout-trimmed profile-page position-relative'])

@section('css')

@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Your profile page shows your account details, role, and assigned partners. Use the edit button to update your name, email, avatar, or password. Your role and partner assignments are managed by an administrator.</p>

    <!-- Edit Profile Modal -->
    <div aria-hidden="true" aria-labelledby="editProfileModalLabel" class="modal fade"
         id="editProfileModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"
                            type="button"></button>
                </div>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row mb-g">
                            <div class="col-md-12">
                                <h6 class="mb-3 text-muted">Personal Information</h6>
                                <div class="mb-3">
                                    <label class="form-label" for="fullName">Full Name <span class="text-danger">*</span></label>
                                    <input class="form-control @error('name') is-invalid @enderror" id="fullName"
                                           name="name" required type="text" value="{{ old('name', $user->name) }}"/>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                                    <input class="form-control @error('email') is-invalid @enderror" id="email"
                                           name="email" required type="email" value="{{ old('email', $user->email) }}"/>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <h6 class="mb-3 text-muted">Profile Picture</h6>
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                 class="rounded-circle shadow-sm me-3" style="width: 64px; height: 64px; object-fit: cover;">
                            <div class="flex-1">
                                <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                       id="avatar" name="avatar" accept="image/*">
                                @error('avatar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">JPEG, PNG, or GIF — Max 2MB</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" data-bs-dismiss="modal" type="button">Cancel</button>
                        <button class="btn btn-primary" type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div aria-hidden="true" aria-labelledby="changePasswordModalLabel" class="modal fade"
         id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"
                            type="button"></button>
                </div>
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="alert alert-info border-0">
                            <div class="d-flex align-items-start">
                                <svg class="sa-icon me-2 mt-1"><use href="/icons/sprite.svg#info"></use></svg>
                                <div>Leave fields empty if you don't want to change your password.</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">New Password <span class="text-danger">*</span></label>
                            <input class="form-control @error('password') is-invalid @enderror" id="password"
                                   name="password" type="password" placeholder="Enter new password"/>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                            <input class="form-control" id="password_confirmation"
                                   name="password_confirmation" type="password" placeholder="Confirm new password"/>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" data-bs-dismiss="modal" type="button">Cancel</button>
                        <button class="btn btn-primary" type="submit">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Profile Header Background -->
    <div class="profile-page-header-underlay bg-info-gradient bg-info-500"></div>

    <!-- Main Profile Content -->
    <div class="container-xl position-relative">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Profile Header Section -->
        <div class="profile-page-header rounded-3 body-bg shadow-3 mb-4">
            <div class="d-flex flex-column flex-md-row align-items-center position-relative p-4">
                <!-- Profile Avatar -->
                <div class="profile-page-header-avatar mb-3 mb-md-0">
                    <div class="position-relative">
                        <img alt="{{ $user->name }}"
                             class="rounded-circle border border-3 border-white shadow-2"
                             height="160" src="{{ $user->avatar_url }}" width="160"
                             style="object-fit: cover;"/>
                    </div>
                </div>
                <!-- Profile Info -->
                <div class="profile-page-header-info ms-md-4 text-center text-md-start">
                    <h1 class="fs-xxl fw-700 mb-2">{{ $user->name }}</h1>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                        @if($user->role === 'super_admin')
                            <span class="badge bg-dark text-white">Super Admin</span>
                        @elseif($user->role === 'admin')
                            <span class="badge bg-danger-100 text-danger">Admin</span>
                        @elseif($user->role === 'psm')
                            <span class="badge bg-success-100 text-success">Partner Success Manager</span>
                        @else
                            <span class="badge bg-info-100 text-info">{{ ucfirst($user->role) }}</span>
                        @endif
                        <span class="badge bg-primary-100 text-primary">{{ $user->partners()->count() }} Partners</span>
                        <span class="badge bg-primary-100 text-primary">Member since {{ $user->created_at->format('M Y') }}</span>
                    </div>
                </div>
                <!-- Profile Actions -->
                <div class="profile-page-header-actions ms-md-auto mt-3 mt-md-0 d-flex">
                    <a class="btn btn-outline-default me-2 d-flex"
                       data-bs-target="#editProfileModal" data-bs-toggle="modal" href="#">
                        <svg class="sa-icon me-2 inline-text">
                            <use href="/icons/sprite.svg#edit-3"></use>
                        </svg>
                        Edit Profile
                    </a>
                </div>
            </div>
            <!-- Profile Navigation -->
            <div class="profile-page-nav border-top">
                <ul class="nav nav-tabs-clean">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#profile-about">
                            <svg class="sa-icon me-2">
                                <use href="/icons/sprite.svg#user"></use>
                            </svg>
                            About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#profile-security">
                            <svg class="sa-icon me-2">
                                <use href="/icons/sprite.svg#lock"></use>
                            </svg>
                            Security
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="tab-content">
            <!-- About Tab -->
            <div class="tab-pane fade show active" id="profile-about">
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Account Details -->
                        <div class="panel mb-4">
                            <div class="panel-hdr">
                                <h2>Account Details</h2>
                            </div>
                            <div class="panel-container">
                                <div class="panel-content">
                                    <div class="d-flex flex-column gap-3">
                                        <div class="d-flex align-items-center">
                                            <svg class="sa-icon me-2">
                                                <use href="/icons/sprite.svg#user"></use>
                                            </svg>
                                            <strong class="me-2">Name:</strong> {{ $user->name }}
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <svg class="sa-icon me-2">
                                                <use href="/icons/sprite.svg#mail"></use>
                                            </svg>
                                            <strong class="me-2">Email:</strong> {{ $user->email }}
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <svg class="sa-icon me-2">
                                                <use href="/icons/sprite.svg#shield"></use>
                                            </svg>
                                            <strong class="me-2">Role:</strong>
                                            @if($user->role === 'super_admin')
                                                <span class="badge bg-dark">Super Admin</span>
                                            @elseif($user->role === 'admin')
                                                <span class="badge bg-danger">Admin</span>
                                            @elseif($user->role === 'psm')
                                                <span class="badge bg-success">PSM</span>
                                            @else
                                                <span class="badge bg-info">{{ ucfirst($user->role) }}</span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <svg class="sa-icon me-2">
                                                <use href="/icons/sprite.svg#calendar"></use>
                                            </svg>
                                            <strong class="me-2">Joined:</strong> {{ $user->created_at->format('F d, Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <!-- Partner Assignments -->
                        <div class="panel mb-4">
                            <div class="panel-hdr">
                                <h2>Assigned Partners</h2>
                            </div>
                            <div class="panel-container">
                                <div class="panel-content">
                                    @if($user->partners->count() > 0)
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($user->partners as $partner)
                                                <span class="badge bg-primary">{{ $partner->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No partners assigned yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- Quick Actions -->
                        <div class="panel mb-4">
                            <div class="panel-hdr">
                                <h2>Quick Actions</h2>
                            </div>
                            <div class="panel-container">
                                <div class="panel-content">
                                    <div class="d-grid gap-2">
                                        <a href="#" class="btn btn-outline-primary btn-sm d-flex align-items-center"
                                           data-bs-target="#editProfileModal" data-bs-toggle="modal">
                                            <svg class="sa-icon me-2"><use href="/icons/sprite.svg#edit-3"></use></svg>
                                            Edit Profile
                                        </a>
                                        <a href="#" class="btn btn-outline-primary btn-sm d-flex align-items-center"
                                           data-bs-target="#changePasswordModal" data-bs-toggle="modal">
                                            <svg class="sa-icon me-2"><use href="/icons/sprite.svg#lock"></use></svg>
                                            Change Password
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Tab -->
            <div class="tab-pane fade" id="profile-security">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="panel mb-4">
                            <div class="panel-hdr">
                                <h2>Password</h2>
                            </div>
                            <div class="panel-container">
                                <div class="panel-content">
                                    <p class="text-muted mb-3">Update your password to keep your account secure.</p>
                                    <a href="#" class="btn btn-primary d-inline-flex align-items-center"
                                       data-bs-target="#changePasswordModal" data-bs-toggle="modal">
                                        <svg class="sa-icon me-2"><use href="/icons/sprite.svg#lock"></use></svg>
                                        Change Password
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="panel mb-4">
                            <div class="panel-hdr">
                                <h2>Session Info</h2>
                            </div>
                            <div class="panel-container">
                                <div class="panel-content">
                                    <div class="d-flex flex-column gap-2">
                                        <div class="d-flex align-items-center">
                                            <svg class="sa-icon me-2">
                                                <use href="/icons/sprite.svg#clock"></use>
                                            </svg>
                                            <strong class="me-2">Last updated:</strong> {{ $user->updated_at->format('F d, Y \a\t g:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
