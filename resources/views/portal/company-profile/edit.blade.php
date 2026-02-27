@extends('portal.layout', ['pageTitle' => 'Company Profile'])

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1">{{ $partner->name }}</h2>
        <p class="text-muted mb-0">Manage your company profile and marketplace presence.</p>
    </div>
    @if($partner->logo_url)
        <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}" style="max-height: 60px; max-width: 200px;" class="rounded">
    @endif
</div>

{{-- Marketplace Status Banner --}}
@if($partner->marketplace_status === 'draft')
    <div class="alert alert-secondary d-flex align-items-center mb-4">
        <i class="fal fa-info-circle me-2"></i>
        <div>Complete your marketplace profile below and save to submit for review.</div>
    </div>
@elseif($partner->marketplace_status === 'submitted')
    <div class="alert alert-warning d-flex align-items-center mb-4">
        <i class="fal fa-clock me-2"></i>
        <div>Your marketplace profile is <strong>under review</strong>. You will be notified once it has been reviewed.</div>
    </div>
@elseif($partner->marketplace_status === 'approved')
    <div class="alert alert-success d-flex align-items-center mb-4">
        <i class="fal fa-check-circle me-2"></i>
        <div>Your marketplace profile has been <strong>approved</strong>.</div>
    </div>
@elseif($partner->marketplace_status === 'rejected')
    <div class="alert alert-danger d-flex align-items-center mb-4">
        <i class="fal fa-exclamation-triangle me-2"></i>
        <div>
            Your marketplace profile requires changes.
            @if($partner->marketplace_rejection_reason)
                <br><strong>Feedback:</strong> {{ $partner->marketplace_rejection_reason }}
            @endif
            <br><small class="text-muted">Please update your profile and save to resubmit.</small>
        </div>
    </div>
@endif

<form action="{{ route('portal.company-profile.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Company Information --}}
    <div class="card portal-card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Company Information</h5></div>
        <div class="card-body">
            <p class="text-muted mb-3">Please provide your company's details below.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Company Logo</label>
                    @if($partner->logo_url)
                        <div class="mb-2">
                            <img src="{{ $partner->logo_url }}" alt="Current logo" style="max-height: 48px;" class="rounded border p-1">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="removeLogo">
                                <label class="form-check-label text-muted small" for="removeLogo">Remove current logo</label>
                            </div>
                        </div>
                    @endif
                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">JPEG, PNG, GIF, or SVG. Max 2MB.</small>
                    @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Website</label>
                    <input type="text" class="form-control" value="{{ $partner->website }}" disabled>
                    <small class="text-muted">Managed by your partner success manager.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $partner->phone) }}">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sales Territory</label>
                    <input type="text" name="sales_territory" class="form-control @error('sales_territory') is-invalid @enderror" value="{{ old('sales_territory', $partner->sales_territory) }}">
                    @error('sales_territory') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Address Information --}}
    <div class="card portal-card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Address Information</h5></div>
        <div class="card-body">
            <p class="text-muted mb-3">Please provide your company's address below.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mailing Street</label>
                    <input type="text" name="mailing_street" class="form-control @error('mailing_street') is-invalid @enderror" value="{{ old('mailing_street', $partner->mailing_street) }}">
                    @error('mailing_street') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mailing Suite</label>
                    <input type="text" name="mailing_suite" class="form-control @error('mailing_suite') is-invalid @enderror" value="{{ old('mailing_suite', $partner->mailing_suite) }}">
                    @error('mailing_suite') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mailing City</label>
                    <input type="text" name="mailing_city" class="form-control @error('mailing_city') is-invalid @enderror" value="{{ old('mailing_city', $partner->mailing_city) }}">
                    @error('mailing_city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mailing State</label>
                    <input type="text" name="mailing_state" class="form-control @error('mailing_state') is-invalid @enderror" value="{{ old('mailing_state', $partner->mailing_state) }}">
                    @error('mailing_state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mailing Postal Code</label>
                    <input type="text" name="mailing_postal_code" class="form-control @error('mailing_postal_code') is-invalid @enderror" value="{{ old('mailing_postal_code', $partner->mailing_postal_code) }}">
                    @error('mailing_postal_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mailing Country</label>
                    <input type="text" name="mailing_country" class="form-control @error('mailing_country') is-invalid @enderror" value="{{ old('mailing_country', $partner->mailing_country) }}">
                    @error('mailing_country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Marketplace Profile --}}
    <div class="card portal-card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Marketplace Profile</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Partner Locator Email Distribution List</label>
                    <textarea name="partner_locator_emails" class="form-control @error('partner_locator_emails') is-invalid @enderror" rows="2">{{ old('partner_locator_emails', $partner->partner_locator_emails) }}</textarea>
                    <small class="text-muted">For leads coming from partner directory (be sure to separate emails with a ';')</small>
                    @error('partner_locator_emails') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Headline</label>
                    <input type="text" name="headline" class="form-control @error('headline') is-invalid @enderror" value="{{ old('headline', $partner->headline) }}" maxlength="255">
                    @error('headline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Company Description</label>
                    <textarea name="company_description" class="form-control @error('company_description') is-invalid @enderror" rows="4">{{ old('company_description', $partner->company_description) }}</textarea>
                    @error('company_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Profile</button>
            </div>
        </div>
    </div>
</form>

{{-- Marketplace Entries --}}
<div class="card portal-card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Marketplace Entries</h5>
        <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addEntryForm">
            + Add Entry
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">Submit one or more entries to promote your solutions and customer stories across industry categories.</p>

        {{-- Add Entry Form (collapsed) --}}
        <div class="collapse mb-4" id="addEntryForm">
            <div class="border rounded p-3 bg-light">
                <h6 class="mb-3">New Marketplace Entry</h6>
                <form action="{{ route('portal.company-profile.entries.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}" min="0" max="999">
                            @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link <span class="text-danger">*</span></label>
                            <input type="url" name="link" class="form-control @error('link') is-invalid @enderror" value="{{ old('link') }}" placeholder="https://..." required>
                            @error('link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" required>{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-sm">Add Entry</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm ms-1" data-bs-toggle="collapse" data-bs-target="#addEntryForm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Existing Entries grouped by category --}}
        @php $hasEntries = false; @endphp
        @foreach($categories as $key => $label)
            @if(isset($entries[$key]) && $entries[$key]->count())
                @php $hasEntries = true; @endphp
                <h6 class="mt-3 mb-2 text-primary">{{ $label }}</h6>
                @foreach($entries[$key] as $entry)
                    <div class="border rounded p-3 mb-2 bg-white">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $entry->title }}</strong>
                                <span class="text-muted small ms-2">(order: {{ $entry->sort_order }})</span>
                                <br>
                                <a href="{{ $entry->link }}" target="_blank" class="text-decoration-none small">{{ Str::limit($entry->link, 60) }}</a>
                                <p class="text-muted small mt-1 mb-0">{{ Str::limit($entry->description, 150) }}</p>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editEntry{{ $entry->id }}">Edit</button>
                                <form action="{{ route('portal.company-profile.entries.destroy', $entry) }}" method="POST" onsubmit="return confirm('Remove this entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </div>

                        {{-- Inline Edit Form --}}
                        <div class="collapse mt-3" id="editEntry{{ $entry->id }}">
                            <form action="{{ route('portal.company-profile.entries.update', $entry) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Category</label>
                                        <select name="category" class="form-select form-select-sm" required>
                                            @foreach($categories as $catKey => $catLabel)
                                                <option value="{{ $catKey }}" {{ $entry->category === $catKey ? 'selected' : '' }}>{{ $catLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Sort Order</label>
                                        <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ $entry->sort_order }}" min="0" max="999">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Title</label>
                                        <input type="text" name="title" class="form-control form-control-sm" value="{{ $entry->title }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Link</label>
                                        <input type="url" name="link" class="form-control form-control-sm" value="{{ $entry->link }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small">Description</label>
                                        <textarea name="description" class="form-control form-control-sm" rows="2" required>{{ $entry->description }}</textarea>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#editEntry{{ $entry->id }}">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif
        @endforeach

        @unless($hasEntries)
            <p class="text-muted text-center py-3 mb-0">No marketplace entries yet. Click "Add Entry" to get started.</p>
        @endunless
    </div>
</div>
@endsection
