@extends('layouts.vertical', ['pageTitle' => 'Add Credential'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Add Credential', 'pageSubTitle1' => 'Tenant Pool', 'pageSubTitle1Url' => route('workshops.credentials.index'), 'pageSubText' => 'Add a pre-provisioned Anaplan sandbox to the pool'])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8 offset-xl-2">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <form action="{{ route('workshops.credentials.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="workshop_id">Workshop <span class="text-danger">*</span></label>
                        <select class="form-select" id="workshop_id" name="workshop_id" required>
                            @foreach($workshops as $workshop)
                                <option value="{{ $workshop->id }}">{{ $workshop->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="username">Anaplan Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="password" name="password" required>
                        <div class="form-text">Stored encrypted at rest.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Add to Pool</button>
                    <a href="{{ route('workshops.credentials.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
