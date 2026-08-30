@extends('layouts.vertical', ['pageTitle' => $workshop->name.' - Tenant Pool'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Tenant Pool', 'pageSubTitle1' => $workshop->name, 'pageSubTitle1Url' => route('workshops.show', $workshop), 'pageSubText' => 'Pre-provisioned Anaplan sandbox credentials for hands-on labs'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1"><i class="fal fa-key me-2"></i> Credential Pool</div>
                <a href="{{ route('workshops.credentials.refresh-queue') }}" class="btn btn-outline-warning btn-sm me-2">Refresh Queue</a>
                <a href="{{ route('workshops.credentials.create', $workshop) }}" class="btn btn-primary btn-sm">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                    Add Credential
                </a>
            </div>
            <div class="card-body border-bottom">
                <form action="{{ route('workshops.credentials.bulk-upload', $workshop) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-9">
                        <label class="form-label fs-sm">Bulk import - CSV/XLSX (columns: username, email, password, notes)</label>
                        <input type="file" class="form-control form-control-sm" name="file" accept=".csv,.xlsx,.xls" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-outline-primary btn-sm">Import</button>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($credentials as $credential)
                            @php $active = $credential->assignments->first(); @endphp
                            <tr>
                                <td>{{ $credential->username }}</td>
                                <td>{{ $credential->email }}</td>
                                <td>
                                    <span class="badge {{ $credential->isAvailable() ? 'bg-success' : 'bg-secondary' }}">
                                        {{ \App\Models\WorkshopCredential::STATUSES[$credential->status] ?? $credential->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($active)
                                        {{ $active->contact->full_name }} <span class="text-muted">({{ $active->contact->partner?->name }})</span>
                                        @if($active->hold_until)
                                            <div class="text-muted fs-sm">Hold until {{ $active->hold_until->format('M j, Y') }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('workshops.credentials.edit', $credential) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    @if($active)
                                        <form action="{{ route('workshops.credentials.assignments.extend', $active) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="date" name="hold_until" value="{{ $active->hold_until?->format('Y-m-d') }}" class="form-control form-control-sm d-inline-block" style="width: auto;">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Extend</button>
                                        </form>
                                        <form action="{{ route('workshops.credentials.assignments.release', $active) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning">Release</button>
                                        </form>
                                    @elseif($credential->isAvailable())
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#assign-{{ $credential->id }}">Assign</button>
                                    @endif
                                </td>
                            </tr>
                            @if($credential->isAvailable())
                            <tr class="collapse" id="assign-{{ $credential->id }}">
                                <td colspan="5" class="bg-light">
                                    <form action="{{ route('workshops.credentials.assign', $credential) }}" method="POST" class="row g-2 align-items-end">
                                        @csrf
                                        <div class="col-md-4">
                                            <label class="form-label fs-sm">Session</label>
                                            <select class="form-select form-select-sm" name="workshop_session_id" required>
                                                @foreach($credential->workshop->sessions as $session)
                                                    <option value="{{ $session->id }}">{{ $session->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fs-sm">Participant (contact ID)</label>
                                            <input type="number" class="form-control form-control-sm" name="contact_id" placeholder="Contact ID" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fs-sm">Hold Until</label>
                                            <input type="date" class="form-control form-control-sm" name="hold_until">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="submit" class="btn btn-primary btn-sm">Go</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No credentials in the pool yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
