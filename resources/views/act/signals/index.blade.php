@extends('layouts.vertical', ['pageTitle' => 'Signal Engine'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Signal Engine', 'pageSubTitle1' => 'Act', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">The Signal Engine continuously analyzes your partner portfolio — pipeline dynamics, health trends, delivery capability gaps, enablement momentum, engagement patterns, and cross-dimensional correlations — to surface the insights that matter most. Each signal is scored by confidence and prioritized by severity so you can act on the highest-impact items first.</p>

{{-- Channel Stats Row --}}
<div class="row mb-3">
    @foreach(\App\Models\Signal::CHANNELS as $channelKey => $channelLabel)
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card shadow-0 mb-g border-top border-3 border-{{ \App\Models\Signal::CHANNEL_COLORS[$channelKey] ?? 'secondary' }}">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-{{ \App\Models\Signal::CHANNEL_COLORS[$channelKey] ?? 'secondary' }} mb-1" style="width:20px;height:20px">
                    <use href="/icons/sprite.svg#{{ \App\Models\Signal::CHANNEL_ICONS[$channelKey] ?? 'activity' }}"></use>
                </svg>
                <h3 class="fw-300 m-0">{{ $stats['channels'][$channelKey] ?? 0 }}</h3>
                <small class="text-muted">{{ $channelLabel }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Summary Stats --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card shadow-0 mb-g border-left-danger">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 text-danger">{{ $stats['critical'] }}</h2>
                <small class="text-muted">Critical Severity</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 text-primary">{{ $stats['high_confidence'] }}</h2>
                <small class="text-muted">High Confidence (&ge; 80%)</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0">{{ $stats['total_active'] }}</h2>
                <small class="text-muted">Total Active Signals</small>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card shadow-0 mb-g">
    <div class="card-body py-2">
        <form action="{{ route('act.signals.index') }}" method="GET" class="row align-items-end g-2">
            <div class="col-md-2">
                <label class="form-label fs-sm">Channel</label>
                <select name="channel" class="form-select form-select-sm">
                    <option value="">All Channels</option>
                    @foreach(\App\Models\Signal::CHANNELS as $key => $label)
                    <option value="{{ $key }}" {{ request('channel') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fs-sm">Severity</label>
                <select name="severity" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach(\App\Models\Signal::SEVERITIES as $key => $label)
                    <option value="{{ $key }}" {{ request('severity') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fs-sm">Status</label>
                <select name="status" class="form-select form-select-sm">
                    @foreach(\App\Models\Signal::STATUSES as $key => $label)
                    <option value="{{ $key }}" {{ request('status', 'active') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
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

{{-- Signal Feed --}}
<div class="card shadow-0 mb-g">
    <div class="card-body p-0">
        @forelse($signals as $signal)
        <div class="d-flex align-items-start p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            {{-- Channel + Severity Badge --}}
            <div class="me-3 text-center" style="min-width: 48px;">
                <span class="badge bg-{{ \App\Models\Signal::CHANNEL_COLORS[$signal->channel] ?? 'secondary' }} rounded-circle p-2">
                    <svg class="sa-icon text-white" style="width:16px;height:16px">
                        <use href="/icons/sprite.svg#{{ \App\Models\Signal::CHANNEL_ICONS[$signal->channel] ?? 'activity' }}"></use>
                    </svg>
                </span>
                <div class="mt-1">
                    @php
                        $sevColors = ['critical' => 'danger', 'warning' => 'warning', 'info' => 'info', 'positive' => 'success'];
                    @endphp
                    <span class="badge bg-{{ $sevColors[$signal->severity] ?? 'secondary' }}" style="font-size: 0.65rem;">
                        {{ ucfirst($signal->severity) }}
                    </span>
                </div>
            </div>

            {{-- Signal Content --}}
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <a href="{{ route('act.signals.show', $signal) }}" class="fw-500 text-decoration-none">{{ $signal->title }}</a>
                    </div>
                    <small class="text-muted text-nowrap ms-2">{{ $signal->created_at->diffForHumans() }}</small>
                </div>
                <small class="text-muted d-block">
                    {{ $signal->partner->name ?? '' }} &middot;
                    <span class="badge bg-{{ \App\Models\Signal::CHANNEL_COLORS[$signal->channel] ?? 'secondary' }}" style="font-size: 0.6rem;">{{ \App\Models\Signal::CHANNELS[$signal->channel] ?? $signal->channel }}</span>
                    &middot; {{ str_replace('_', ' ', $signal->type) }}
                </small>
                <p class="fs-sm mt-1 mb-1">{{ Str::limit($signal->description, 200) }}</p>

                {{-- Confidence Bar --}}
                <div class="d-flex align-items-center mb-1">
                    <small class="text-muted me-2" style="min-width: 70px;">Confidence:</small>
                    <div class="progress flex-grow-1" style="height: 4px; max-width: 150px;">
                        @php
                            $confPct = $signal->confidence_score * 100;
                            $confColor = $confPct >= 80 ? 'success' : ($confPct >= 60 ? 'info' : 'warning');
                        @endphp
                        <div class="progress-bar bg-{{ $confColor }}" style="width: {{ $confPct }}%"></div>
                    </div>
                    <small class="text-muted ms-2">{{ number_format($confPct, 0) }}%</small>
                </div>

                @if($signal->recommended_action)
                <p class="fs-sm text-primary mb-2"><strong>Action:</strong> {{ Str::limit($signal->recommended_action, 150) }}</p>
                @endif

                @if($signal->status === 'active')
                <div class="btn-group btn-group-sm">
                    <form action="{{ route('act.signals.acknowledge', $signal) }}" method="POST" class="d-inline">@csrf @method('PUT')
                        <button class="btn btn-outline-primary btn-sm">Acknowledge</button>
                    </form>
                    <form action="{{ route('act.signals.resolve', $signal) }}" method="POST" class="d-inline ms-1">@csrf @method('PUT')
                        <button class="btn btn-outline-success btn-sm">Resolve</button>
                    </form>
                    <form action="{{ route('act.signals.dismiss', $signal) }}" method="POST" class="d-inline ms-1">@csrf @method('PUT')
                        <button class="btn btn-outline-secondary btn-sm">Dismiss</button>
                    </form>
                </div>
                @else
                <span class="badge bg-{{ \App\Models\Signal::STATUS_COLORS[$signal->status] ?? 'secondary' }}">{{ \App\Models\Signal::STATUSES[$signal->status] ?? ucfirst($signal->status) }}</span>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <svg class="sa-icon mb-2" style="width:48px;height:48px"><use href="/icons/sprite.svg#activity"></use></svg>
            <p>No signals matching your filters. The engine is listening.</p>
        </div>
        @endforelse
    </div>
</div>

{{ $signals->withQueryString()->links() }}

@endsection
