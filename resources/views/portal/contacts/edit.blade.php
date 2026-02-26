@extends('portal.layout', ['pageTitle' => 'Edit Contact'])

@section('content')
<h4 class="mb-4">Edit Contact — {{ $contact->full_name }}</h4>

<div class="card portal-card">
    <div class="card-body">
        <form action="{{ route('portal.contacts.update', $contact) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $contact->first_name) }}" required>
                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $contact->last_name) }}" required>
                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $contact->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $contact->phone) }}">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $contact->title) }}">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Portal Access</label>
                    <select name="portal_access" class="form-select @error('portal_access') is-invalid @enderror">
                        <option value="0" {{ !old('portal_access', $contact->portal_access) ? 'selected' : '' }}>Disabled</option>
                        <option value="1" {{ old('portal_access', $contact->portal_access) ? 'selected' : '' }}>Enabled</option>
                    </select>
                    @error('portal_access') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Portal Role</label>
                    <select name="portal_role" class="form-select @error('portal_role') is-invalid @enderror">
                        <option value="">— None —</option>
                        @foreach(\App\Models\Role::portal()->orderBy('sort_order')->get() as $portalRole)
                            <option value="{{ $portalRole->slug }}" {{ old('portal_role', $contact->role?->slug) === $portalRole->slug ? 'selected' : '' }}>{{ $portalRole->display_name }}</option>
                        @endforeach
                    </select>
                    @error('portal_role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('portal.contacts.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
