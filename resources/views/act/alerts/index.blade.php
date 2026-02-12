@extends('layouts.vertical', ['pageTitle' => 'Alerts & Recommendations'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Alerts & Recommendations', 'pageSubTitle1' => 'Act', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Alerts & Recommendations surfaces the items across your portfolio that need your attention right now — from expiring certifications and stalled deals to missed QBR cadences and at-risk projects. Each alert is prioritized by severity so you can focus on what matters most first, and recommendations suggest concrete next steps to resolve each issue.</p>

{{-- Stats Row --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card shadow-0 mb-g border-left-danger">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 text-danger">{{ $stats['critical'] }}</h2>
                <small class="text-muted">Critical Alerts</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 text-warning">{{ $stats['warning'] }}</h2>
                <small class="text-muted">Warnings</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 text-primary">{{ $stats['total_active'] }}</h2>
                <small class="text-muted">Total Active</small>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card shadow-0 mb-g">
    <div class="card-body py-2">
        <form action="{{ route('act.alerts.index') }}" method="GET" class="row align-items-end g-2">
            <div class="col-md-3">
                <label class="form-label fs-sm">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="acknowledged" {{ request('status') === 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fs-sm">Severity</label>
                <select name="severity" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Info</option>
                    <option value="positive" {{ request('severity') === 'positive' ? 'selected' : '' }}>Positive</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fs-sm">Partner</label>
                <select name="partner_id" class="form-select form-select-sm">
                    <option value="">All Partners</option>
                    @foreach($partners as $p)
                    <option value="{{ $p->id }}" {{ request('partner_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Alert Feed --}}
<div class="card shadow-0 mb-g">
    <div class="card-body p-0">
        @forelse($alerts as $alert)
        <div class="d-flex align-items-start p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div class="me-3">
                @php
                    $sevColors = ['critical' => 'danger', 'warning' => 'warning', 'info' => 'info', 'positive' => 'success'];
                    $sevIcons = ['critical' => 'alert-circle', 'warning' => 'alert-triangle', 'info' => 'info', 'positive' => 'check-circle'];
                @endphp
                <span class="badge bg-{{ $sevColors[$alert->severity] ?? 'secondary' }} rounded-circle p-2">
                    <svg class="sa-icon text-white" style="width:16px;height:16px"><use href="/icons/sprite.svg#{{ $sevIcons[$alert->severity] ?? 'info' }}"></use></svg>
                </span>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between">
                    <h6 class="m-0">{{ $alert->title }}</h6>
                    <small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                </div>
                <small class="text-muted d-block">{{ $alert->partner->name ?? '' }} &middot; {{ str_replace('_', ' ', $alert->type) }}</small>
                <p class="fs-sm mt-1 mb-1">{{ $alert->description }}</p>
                @if($alert->recommended_action)
                <p class="fs-sm text-primary mb-2"><strong>Action:</strong> {{ $alert->recommended_action }}</p>
                @endif
                @if($alert->status === 'active')
                <div class="btn-group btn-group-sm">
                    <form action="{{ route('act.alerts.acknowledge', $alert) }}" method="POST" class="d-inline">@csrf @method('PUT')
                        <button class="btn btn-outline-primary btn-sm">Acknowledge</button>
                    </form>
                    <form action="{{ route('act.alerts.resolve', $alert) }}" method="POST" class="d-inline ms-1">@csrf @method('PUT')
                        <button class="btn btn-outline-success btn-sm">Resolve</button>
                    </form>
                    <form action="{{ route('act.alerts.dismiss', $alert) }}" method="POST" class="d-inline ms-1">@csrf @method('PUT')
                        <button class="btn btn-outline-secondary btn-sm">Dismiss</button>
                    </form>
                </div>
                @else
                <span class="badge bg-secondary">{{ ucfirst($alert->status) }}</span>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <svg class="sa-icon mb-2" style="width:48px;height:48px"><use href="/icons/sprite.svg#check-circle"></use></svg>
            <p>No alerts matching your filters. All clear!</p>
        </div>
        @endforelse
    </div>
</div>

{{ $alerts->withQueryString()->links() }}

@endsection
