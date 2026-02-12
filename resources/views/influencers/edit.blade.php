@extends('layouts.vertical', ['pageTitle' => 'Edit Influencer'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Edit Influencer', 'pageSubTitle1' => 'Influencers', 'pageSubTitle1Url' => route('influencers.index')])
@endsection

@section('content')
<div class="row justify-content-center mt-3">
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Edit {{ $contact->first_name }} {{ $contact->last_name }}</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('influencers.update', $contact) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="partner_id">Partner <span class="text-danger">*</span></label>
                            <select class="form-select @error('partner_id') is-invalid @enderror" id="partner_id" name="partner_id" required>
                                <option value="">Select Partner...</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}" {{ old('partner_id', $contact->partner_id) == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>
                                @endforeach
                            </select>
                            @error('partner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="influencer_role">Influencer Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('influencer_role') is-invalid @enderror" id="influencer_role" name="influencer_role" required>
                                <option value="">Select Role...</option>
                                @foreach(\App\Models\Contact::INFLUENCER_ROLES as $key => $label)
                                    <option value="{{ $key }}" {{ old('influencer_role', $contact->influencer_role) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('influencer_role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', $contact->first_name) }}" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $contact->last_name) }}" required>
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $contact->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="phone">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $contact->phone) }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="title">Job Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $contact->title) }}">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="organization">Organization</label>
                            <input type="text" class="form-control @error('organization') is-invalid @enderror" id="organization" name="organization" value="{{ old('organization', $contact->organization) }}">
                            @error('organization') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $contact->notes) }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('influencers.show', $contact) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>

                {{-- Delete form must be OUTSIDE the edit form --}}
                <div class="mt-3 pt-3 border-top">
                    <form method="POST" action="{{ route('influencers.destroy', $contact) }}" onsubmit="return confirm('Remove this influencer and all their interactions?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Remove Influencer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
