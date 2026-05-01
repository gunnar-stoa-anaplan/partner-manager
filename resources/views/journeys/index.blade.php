@extends('layouts.vertical', ['pageTitle' => 'Journeys'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Journeys',
        'pageSubText' => 'Manage your assigned journey agents and track partner progress',
    ])
@endsection

@section('content')

    @if($journeys->isEmpty())
        <div class="card shadow-0">
            <div class="card-body text-center py-5">
                <svg class="sa-icon sa-icon-2x text-muted mb-3"><use href="/icons/sprite.svg#map"></use></svg>
                <h5 class="text-muted">No journeys assigned</h5>
                <p class="text-muted">Contact an administrator to be assigned to a journey.</p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($journeys as $journey)
                <div class="col-md-6 col-xl-4">
                    <div class="card shadow-0 h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">{{ $journey->name }}</h5>
                                @php
                                    $statusColors = ['active' => 'success', 'pilot' => 'warning', 'planned' => 'secondary'];
                                    $statusColor = $statusColors[$journey->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }} ms-2">{{ ucfirst($journey->status) }}</span>
                            </div>
                            <div class="text-muted small mb-1">
                                <strong>Product Area:</strong> {{ $journey->product_area }}
                            </div>
                            <div class="text-muted small mb-3">
                                <strong>Persona:</strong> {{ $journey->persona }}
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <svg class="sa-icon sa-icon-sm text-muted me-1"><use href="/icons/sprite.svg#users"></use></svg>
                                <span class="text-muted small">{{ $journey->participant_count }} participant{{ $journey->participant_count !== 1 ? 's' : '' }}</span>
                            </div>
                            @php
                                $pct = $journey->asset_approved_pct;
                                if ($pct === null) {
                                    $pctColor = 'secondary';
                                    $pctLabel = 'No assets';
                                } elseif ($pct >= 100) {
                                    $pctColor = 'success';
                                    $pctLabel = $pct.'% ready';
                                } elseif ($pct >= 80) {
                                    $pctColor = 'warning';
                                    $pctLabel = $pct.'% ready';
                                } else {
                                    $pctColor = 'danger';
                                    $pctLabel = $pct.'% ready';
                                }
                            @endphp
                            <div class="d-flex align-items-center mb-4">
                                <svg class="sa-icon sa-icon-sm text-muted me-1"><use href="/icons/sprite.svg#check-circle"></use></svg>
                                <span class="badge bg-{{ $pctColor }}">{{ $pctLabel }}</span>
                                @if ($journey->asset_total > 0)
                                    <span class="text-muted small ms-2">({{ $journey->asset_approved }}/{{ $journey->asset_total }} assets approved)</span>
                                @endif
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('journeys.show', $journey) }}" class="btn btn-primary btn-sm w-100">
                                    View Journey
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection
