@extends('layouts.vertical', ['pageTitle' => 'Edit Partner'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Edit Partner', 'pageSubTitle1' => 'Partners', 'pageSubTitle1Url' => route('dashboard'), 'pageSubText' => $partner->name])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div class="card shadow-0">
                <div class="card-header"><div class="card-title">{{ $partner->name }}</div></div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('partners.update', $partner) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Owner (PSM)</label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">— Unassigned —</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" @selected((int) old('user_id', $partner->user_id) === (int) $u->id)>{{ $u->name }}</option>
                                @endforeach
                            </select>
                            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('partners.show', $partner->id) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
