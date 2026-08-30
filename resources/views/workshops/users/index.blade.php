@extends('layouts.vertical', ['pageTitle' => 'Workshop Users'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Workshop Users', 'pageSubTitle1' => 'Workshops', 'pageSubTitle1Url' => route('workshops.index'), 'pageSubText' => 'Portal access for everyone who has been enrolled as a workshop participant'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Grant Access</div></div>
            <div class="card-body">
                @if($grantableContacts->isEmpty())
                    <p class="text-muted mb-0">Every enrolled participant already has portal access.</p>
                @else
                <form action="{{ route('workshops.users.store') }}" method="POST" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label fs-sm">Contact <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="contact_id" required>
                            <option value="">Select a participant...</option>
                            @foreach($grantableContacts as $contact)
                                <option value="{{ $contact->id }}">{{ $contact->full_name }} — {{ $contact->email }} ({{ $contact->partner?->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-sm">Role <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="role_id" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Grant access &amp; generate password</button>
                    </div>
                </form>
                @endif
            </div>
        </div>

        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Users ({{ $contacts->count() }})</div></div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Partner</th>
                            <th>Role</th>
                            <th>Portal Access</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contacts as $contact)
                            <tr>
                                <td>{{ $contact->email }}</td>
                                <td>{{ $contact->full_name }}</td>
                                <td>{{ $contact->partner?->name }}</td>
                                <td>
                                    @if($contact->role)
                                        <span class="badge bg-secondary">{{ $contact->role->display_name }}</span>
                                    @else
                                        <span class="text-muted fs-sm">None</span>
                                    @endif
                                </td>
                                <td>
                                    @if($contact->portal_access)
                                        <span class="badge bg-success">Enabled</span>
                                    @else
                                        <span class="badge bg-light text-dark">Disabled</span>
                                    @endif
                                </td>
                                <td>{{ $contact->last_login_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('workshops.users.activity', $contact) }}" class="btn btn-sm btn-outline-secondary">View activity</a>
                                    @if($contact->portal_access)
                                        <form action="{{ route('workshops.users.reset-password', $contact) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset the password for {{ $contact->full_name }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Reset password</button>
                                        </form>
                                        <form action="{{ route('workshops.users.disable', $contact) }}" method="POST" class="d-inline" onsubmit="return confirm('Disable portal access for {{ $contact->full_name }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Disable</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No workshop participants yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
