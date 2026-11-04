@extends('layouts.vertical', ['pageTitle' => $journey->name.' — Blockers'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $journey->name.' — Blockers',
        'pageSubTitle1' => 'Journeys',
        'pageSubTitle1Url' => route('journeys.index'),
        'pageSubTitle2' => $journey->name,
        'pageSubTitle2Url' => route('journeys.show', $journey),
        'pageSubText' => 'Daily standup blocker log',
    ])
@endsection

@section('content')
    @if (session('flash.success'))
        <div class="alert alert-success">{{ session('flash.success') }}</div>
    @endif

    {{-- New Blocker --}}
    <div class="card shadow-0 mb-3">
        <div class="card-body">
            <h5 class="card-title mb-3">Log a blocker</h5>
            <form method="POST" action="{{ route('journeys.blockers.store', $journey) }}" class="row g-2">
                @csrf
                <div class="col-md-5">
                    <input type="text" name="title" class="form-control form-control-sm" placeholder="Short title (e.g., 'Strategy webcast author missing')" required>
                </div>
                <div class="col-md-3">
                    <select name="journey_asset_id" class="form-select form-select-sm">
                        <option value="">Optional — link to asset</option>
                        @foreach ($journeyAssets as $a)
                            <option value="{{ $a->id }}">[{{ $a->status }}] Stage {{ $a->stage_number }} — {{ \Illuminate\Support\Str::limit($a->asset_name, 60) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="assigned_to_user_id" class="form-select form-select-sm">
                        <option value="">Assign user…</option>
                        @foreach ($assignableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="target_resolution_date" class="form-control form-control-sm" placeholder="Target date">
                </div>
                <div class="col-md-9">
                    <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Description / who/what/when context"></textarea>
                </div>
                <div class="col-md-3 d-flex flex-column gap-2">
                    <input type="text" name="assigned_to_external" class="form-control form-control-sm" placeholder="Or external assignee (e.g., 'Acme Solutions Marketing')">
                    <div class="form-check">
                        <input type="checkbox" name="raised_in_standup" value="1" class="form-check-input" id="raised">
                        <label class="form-check-label small" for="raised">Raised in today's standup</label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100">Log blocker</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Existing blockers --}}
    <div class="card shadow-0">
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Title</th>
                        <th>Linked Asset</th>
                        <th>Assigned</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($blockers as $blocker)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $blocker->title }}</div>
                            @if ($blocker->description)
                                <div class="text-muted small">{{ $blocker->description }}</div>
                            @endif
                            @if ($blocker->raised_in_standup_at)
                                <div class="small text-info">Raised in standup {{ $blocker->raised_in_standup_at->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td>
                            @if ($blocker->journeyAsset)
                                <span class="small">Stage {{ $blocker->journeyAsset->stage_number }} — {{ \Illuminate\Support\Str::limit($blocker->journeyAsset->asset_name, 50) }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>{{ $blocker->assignedToUser?->name ?? $blocker->assigned_to_external ?? '—' }}</td>
                        <td>
                            @if ($blocker->target_resolution_date)
                                <span class="@if ($blocker->target_resolution_date->isPast() && $blocker->status !== 'resolved') text-danger fw-bold @endif">
                                    {{ $blocker->target_resolution_date->format('Y-m-d') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('journeys.blockers.update', [$journey, $blocker]) }}" class="d-flex gap-1">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach (\App\Models\Blocker::STATUSES as $s)
                                        <option value="{{ $s }}" @selected($s === $blocker->status)>{{ str_replace('_', ' ', $s) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('journeys.blockers.destroy', [$journey, $blocker]) }}" onsubmit="return confirm('Delete this blocker?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No blockers logged. Use the form above when issues surface in standups.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
