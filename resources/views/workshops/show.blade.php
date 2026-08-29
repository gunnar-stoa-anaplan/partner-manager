@extends('layouts.vertical', ['pageTitle' => $workshop->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $workshop->name, 'pageSubTitle1' => 'Workshops', 'pageSubTitle1Url' => route('workshops.index'), 'pageSubText' => $workshop->short_description])
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
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">
                    <i class="fal fa-check-square me-2"></i> Sessions
                </div>
                <span class="badge bg-secondary me-2">{{ \App\Models\Workshop::STATUSES[$workshop->status] ?? $workshop->status }}</span>
                <a href="{{ route('workshops.edit', $workshop) }}" class="btn btn-outline-secondary btn-sm">Edit Workshop</a>
                <button type="button" class="btn btn-primary btn-sm ms-2" data-bs-toggle="collapse" data-bs-target="#newSessionForm">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                    New Session
                </button>
            </div>
            <div class="card-body">
                <div class="collapse mb-4" id="newSessionForm">
                    <form action="{{ route('workshops.sessions.store', $workshop) }}" method="POST" class="row g-2 align-items-end border rounded p-3 bg-light">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label fs-sm">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="name" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-sm">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="slug" placeholder="q3-2026-cohort" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-sm">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="start_date" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-sm">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="end_date" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-sm">Status <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="status" required>
                                @foreach(\App\Models\WorkshopSession::STATUSES as $value => $label)
                                    <option value="{{ $value }}" @selected($value === 'upcoming')>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm">Add</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100">
                        <thead>
                            <tr>
                                <th>Session</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th>Participants</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($workshop->sessions as $session)
                                <tr>
                                    <td><a href="{{ route('workshops.sessions.show', [$workshop, $session]) }}">{{ $session->name }}</a></td>
                                    <td>{{ $session->start_date->format('M j, Y') }} - {{ $session->end_date->format('M j, Y') }}</td>
                                    <td><span class="badge bg-secondary">{{ \App\Models\WorkshopSession::STATUSES[$session->status] ?? $session->status }}</span></td>
                                    <td>{{ $session->active_participants_count }}</td>
                                    <td>
                                        <a href="{{ route('workshops.sessions.show', [$workshop, $session]) }}" class="btn btn-sm btn-outline-secondary">Manage</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No sessions scheduled yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
