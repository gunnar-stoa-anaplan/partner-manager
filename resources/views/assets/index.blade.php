@extends('layouts.vertical', ['pageTitle' => 'Asset Catalog'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Asset Catalog',
        'pageSubText' => 'Browse Anaplan app content assets — sales/pre-sales/delivery collateral, demos, and courses.',
    ])
@endsection

@section('content')

    {{-- Filter bar --}}
    <div class="card shadow-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('assets.index') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-1">LOB</label>
                    <select name="lob" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($lobs as $lob)
                            <option value="{{ $lob }}" @selected(($filters['lob'] ?? null) === $lob)>{{ $lob }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Persona</label>
                    <select name="persona" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($personas as $p)
                            <option value="{{ $p }}" @selected(($filters['persona'] ?? null) === $p)>{{ ucfirst(str_replace('_', '-', $p)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['type'] ?? null) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" @selected(($filters['status'] ?? null) === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Application contains</label>
                    <input type="text" name="application" value="{{ $filters['application'] ?? '' }}" class="form-control form-control-sm" placeholder="e.g. IFP">
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-primary btn-sm">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Results --}}
    @if($assets->isEmpty())
        <div class="card shadow-0">
            <div class="card-body text-center py-5 text-muted">No assets match those filters.</div>
        </div>
    @else
        <div class="card shadow-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Application</th>
                            <th>LOB</th>
                            <th>Persona</th>
                            <th>Type</th>
                            <th>Name / Link</th>
                            <th>Owner</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assets as $asset)
                            <tr>
                                <td class="small">{{ $asset->application }}</td>
                                <td><span class="badge bg-light text-dark">{{ $asset->lob }}</span></td>
                                <td class="small">{{ str_replace('_', '-', $asset->persona) }}</td>
                                <td class="small">{{ $asset->typeLabel() }}</td>
                                <td class="small">
                                    @if($asset->url)
                                        <a href="{{ $asset->url }}" target="_blank" rel="noopener">{{ $asset->name }}</a>
                                    @else
                                        <span class="text-muted">{{ $asset->name }}</span>
                                    @endif
                                </td>
                                <td class="small">
                                    {{ $asset->owner_name }}
                                    @if($asset->secondary_owner_name)
                                        <div class="text-muted">{{ $asset->secondary_owner_name }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusBadge = [
                                            'available' => 'success',
                                            'partial' => 'warning',
                                            'not_available' => 'danger',
                                            'deprecated' => 'secondary',
                                        ][$asset->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusBadge }}">{{ str_replace('_', ' ', $asset->status) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $assets->links() }}
            </div>
        </div>
    @endif

@endsection
