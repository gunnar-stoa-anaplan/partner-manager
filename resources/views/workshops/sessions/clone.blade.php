@extends('layouts.vertical', ['pageTitle' => 'Clone '.$session->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Clone Session', 'pageSubTitle1' => $workshop->name, 'pageSubTitle1Url' => route('workshops.show', $workshop), 'pageSubText' => 'Carries over the name and notes from "'.$session->name.'" - pick a fresh slug and dates for the new cohort.'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <form action="{{ route('workshops.sessions.store', $workshop) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $session->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="slug" placeholder="q3-2026-cohort" value="{{ old('slug') }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" value="{{ old('start_date') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="end_date" value="{{ old('end_date') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" required>
                            @foreach(\App\Models\WorkshopSession::STATUSES as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', 'upcoming') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3">{{ old('notes', $session->notes) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Session</button>
                    <a href="{{ route('workshops.show', $workshop) }}" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
