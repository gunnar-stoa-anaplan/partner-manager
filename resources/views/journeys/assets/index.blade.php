@extends('layouts.vertical', ['pageTitle' => $journey->name.' — Assets'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $journey->name.' — Asset Workflow',
        'pageSubTitle1' => 'Journeys',
        'pageSubTitle1Url' => route('journeys.index'),
        'pageSubTitle2' => $journey->name,
        'pageSubTitle2Url' => route('journeys.show', $journey),
        'pageSubText' => $statusCounts['total'].' assets tracked',
    ])
@endsection

@php
    $statusMeta = [
        'missing' => ['label' => 'Missing', 'class' => 'bg-danger', 'text' => 'text-danger', 'badge' => 'bg-danger'],
        'in_review' => ['label' => 'In Review', 'class' => 'bg-info', 'text' => 'text-info', 'badge' => 'bg-info'],
        'needs_revision' => ['label' => 'Needs Revision', 'class' => 'bg-warning', 'text' => 'text-warning', 'badge' => 'bg-warning'],
        'approved' => ['label' => 'Approved', 'class' => 'bg-success', 'text' => 'text-success', 'badge' => 'bg-success'],
    ];
@endphp

@section('content')
    @if (session('flash.success'))
        <div class="alert alert-success">{{ session('flash.success') }}</div>
    @endif

    {{-- Top header: link back, status pills, navigate to standup/blockers --}}
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        <a href="{{ route('journeys.show', $journey) }}" class="btn btn-sm btn-outline-secondary">&laquo; Journey overview</a>
        <a href="{{ route('journeys.standup', $journey) }}" class="btn btn-sm btn-outline-secondary">Standup</a>
        <a href="{{ route('journeys.blockers.index', $journey) }}" class="btn btn-sm btn-outline-secondary">Blockers</a>
        <span class="ms-auto"></span>
        @foreach ($statusMeta as $key => $meta)
            <span class="badge {{ $meta['badge'] }} fw-normal">
                {{ $meta['label'] }}: {{ $statusCounts[$key] ?? 0 }}
            </span>
        @endforeach
    </div>

    @foreach ($byStage as $stageNumber => $stageAssets)
        @php $sc = $stageStatusCounts[$stageNumber] ?? []; @endphp
        <div class="card shadow-0 mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Stage {{ $stageNumber }} — {{ \App\Services\JourneyDatabase::STAGES[$stageNumber]['name'] ?? '' }}</h5>
                    <div class="small">
                        @foreach ($statusMeta as $key => $meta)
                            <span class="ms-2 {{ $meta['text'] }}">{{ $meta['label'] }}: {{ $sc[$key] ?? 0 }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Asset</th>
                                <th>Kind</th>
                                <th>Linked Asset</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($stageAssets as $asset)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $asset->asset_name }}</div>
                                    @if ($asset->task_id)
                                        <div class="text-muted small font-monospace">{{ $asset->task_id }}</div>
                                    @endif
                                </td>
                                <td><span class="text-muted small">{{ $asset->asset_kind ?? '—' }}</span></td>
                                <td>
                                    @if ($asset->linkedAsset)
                                        <span class="badge bg-light text-dark border">{{ $asset->linkedAsset->name }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($asset->assignedToUser)
                                        {{ $asset->assignedToUser->name }}
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $statusMeta[$asset->status]['badge'] }}">{{ $statusMeta[$asset->status]['label'] }}</span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="collapse" data-bs-target="#asset-edit-{{ $asset->id }}">Edit</button>
                                </td>
                            </tr>
                            <tr class="collapse" id="asset-edit-{{ $asset->id }}">
                                <td colspan="6" class="bg-light">
                                    <form method="POST" action="{{ route('journeys.assets.update', [$journey, $asset]) }}" class="row g-2">
                                        @csrf
                                        @method('PATCH')
                                        <div class="col-md-3">
                                            <label class="form-label small">Status</label>
                                            <select name="status" class="form-select form-select-sm">
                                                @foreach (\App\Models\JourneyAsset::STATUSES as $s)
                                                    <option value="{{ $s }}" @selected($s === $asset->status)>{{ $statusMeta[$s]['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Linked Asset</label>
                                            <select name="linked_asset_id" class="form-select form-select-sm">
                                                <option value="">— none —</option>
                                                @foreach ($linkableAssets as $linkable)
                                                    <option value="{{ $linkable->id }}" @selected($asset->linked_asset_id === $linkable->id)>{{ $linkable->name }} ({{ $linkable->type }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Assigned To</label>
                                            <select name="assigned_to_user_id" class="form-select form-select-sm">
                                                <option value="">— none —</option>
                                                @foreach ($assignableUsers as $u)
                                                    <option value="{{ $u->id }}" @selected($asset->assigned_to_user_id === $u->id)>{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small">Notes</label>
                                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Notes, links, context...">{{ $asset->notes }}</textarea>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endsection
