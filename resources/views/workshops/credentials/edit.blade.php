@extends('layouts.vertical', ['pageTitle' => 'Edit Credential'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Edit Credential', 'pageSubTitle1' => 'Tenant Pool', 'pageSubTitle1Url' => route('workshops.credentials.index'), 'pageSubText' => $credential->username])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <form action="{{ route('workshops.credentials.update', $credential) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" for="username">Anaplan Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" value="{{ old('username', $credential->username) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $credential->email) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input type="text" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                        <div class="form-text">Only fill in to rotate the password. Stored encrypted at rest.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            @foreach(\App\Models\WorkshopCredential::STATUSES as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $credential->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $credential->notes) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('workshops.credentials.index') }}" class="btn btn-outline-secondary">Cancel</a>

                    <hr class="my-4">
                </form>

                <form action="{{ route('workshops.credentials.destroy', $credential) }}" method="POST" onsubmit="return confirm('Retire this credential?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Retire Credential</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
