@extends('portal.layout', ['pageTitle' => 'Add Contact'])

@section('content')
<h4 class="mb-4">Add Contact</h4>

<div class="card portal-card">
    <div class="card-body">
        <form action="{{ route('portal.contacts.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Portal Access</label>
                    <select name="portal_access" class="form-select">
                        <option value="0" {{ !old('portal_access') ? 'selected' : '' }}>Disabled</option>
                        <option value="1" {{ old('portal_access') ? 'selected' : '' }}>Enabled</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Portal Role</label>
                    <select name="portal_role" class="form-select">
                        <option value="">— None —</option>
                        @foreach(\App\Models\Contact::PORTAL_ROLES as $key => $label)
                            <option value="{{ $key }}" {{ old('portal_role') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Add Contact</button>
                <a href="{{ route('portal.contacts.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
