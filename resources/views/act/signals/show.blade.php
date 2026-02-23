@extends('layouts.vertical', ['pageTitle' => 'Signal Detail'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $signal->title, 'pageSubTitle1' => 'Signal Engine', 'pageSubTitle1Url' => route('act.signals.index')])
@endsection

@section('content')
<div class="row mt-3">
    {{-- Main Signal Detail --}}
    <div class="col-xl-8">
        {{-- Signal Header --}}
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <span class="badge bg-{{ \App\Models\Signal::CHANNEL_COLORS[$signal->channel] ?? 'secondary' }} rounded-circle p-2 me-3">
                        <svg class="sa-icon text-white" style="width:20px;height:20px">
                            <use href="/icons/sprite.svg#{{ \App\Models\Signal::CHANNEL_ICONS[$signal->channel] ?? 'activity' }}"></use>
                        </svg>
                    </span>
                    <div class="flex-grow-1">
                        <h5 class="mb-1">{{ $signal->title }}</h5>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-{{ \App\Models\Signal::CHANNEL_COLORS[$signal->channel] ?? 'secondary' }}">{{ \App\Models\Signal::CHANNELS[$signal->channel] ?? $signal->channel }}</span>
                            @php $sevColors = ['critical' => 'danger', 'warning' => 'warning', 'info' => 'info', 'positive' => 'success']; @endphp
                            <span class="badge bg-{{ $sevColors[$signal->severity] ?? 'secondary' }}">{{ ucfirst($signal->severity) }}</span>
                            <span class="badge bg-{{ \App\Models\Signal::STATUS_COLORS[$signal->status] ?? 'secondary' }}">{{ \App\Models\Signal::STATUSES[$signal->status] ?? ucfirst($signal->status) }}</span>
                        </div>

                        <p class="mb-3">{{ $signal->description }}</p>

                        {{-- Confidence --}}
                        <div class="d-flex align-items-center mb-3">
                            <span class="fw-500 me-3">Confidence:</span>
                            @php
                                $confPct = $signal->confidence_score * 100;
                                $confColor = $confPct >= 80 ? 'success' : ($confPct >= 60 ? 'info' : 'warning');
                            @endphp
                            <div class="progress flex-grow-1" style="height: 8px; max-width: 200px;">
                                <div class="progress-bar bg-{{ $confColor }}" style="width: {{ $confPct }}%"></div>
                            </div>
                            <span class="ms-2 fw-500">{{ number_format($confPct, 0) }}%</span>
                        </div>

                        {{-- Meta Info --}}
                        <div class="row fs-sm text-muted">
                            <div class="col-md-4">
                                <strong>Partner:</strong>
                                @if($signal->partner)
                                <a href="{{ route('partners.show', $signal->partner) }}">{{ $signal->partner->name }}</a>
                                @else
                                N/A
                                @endif
                            </div>
                            <div class="col-md-4">
                                <strong>Source:</strong> {{ str_replace('_', ' ', $signal->source_processor) }}
                            </div>
                            <div class="col-md-4">
                                <strong>Created:</strong> {{ $signal->created_at->format('M j, Y g:i A') }}
                            </div>
                        </div>
                        @if($signal->expires_at)
                        <div class="fs-sm text-muted mt-1">
                            <strong>Expires:</strong> {{ $signal->expires_at->format('M j, Y') }} ({{ $signal->expires_at->diffForHumans() }})
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Recommended Action --}}
        @if($signal->recommended_action)
        <div class="card shadow-0 mb-g border-left-primary">
            <div class="card-header py-2">
                <h6 class="m-0">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#zap"></use></svg>
                    Recommended Action
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $signal->recommended_action }}</p>
            </div>
        </div>
        @endif

        {{-- How This Was Calculated --}}
        @if($processorMeta)
        <div class="card shadow-0 mb-g border-start border-3 border-info">
            <div class="card-header py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#cpu"></use></svg>
                        How This Was Calculated
                    </h6>
                    <a href="{{ route('act.signals.catalog') }}#processor-{{ $signal->source_processor }}" class="btn btn-xs btn-outline-info">
                        View in Catalog
                    </a>
                </div>
            </div>
            <div class="card-body">
                <p class="fs-sm mb-3">{{ $processorMeta['description'] }}</p>

                @if(isset($processorMeta['signal_types'][$signal->type]))
                    @php $typeMeta = $processorMeta['signal_types'][$signal->type]; @endphp
                    <div class="bg-light rounded p-2 mb-3">
                        <small class="fw-500 d-block"><code>{{ $signal->type }}</code></small>
                        <small class="text-muted">{{ $typeMeta['description'] }}</small>
                        <small class="d-block mt-1"><strong>Severity rule:</strong> {{ $typeMeta['severity'] }}</small>
                    </div>
                @endif

                @if(!empty($processorMeta['thresholds']))
                <div class="row">
                    @foreach($processorMeta['thresholds'] as $key => $rule)
                    <div class="col-md-6 mb-1">
                        <small>
                            <span class="fw-500">{{ str_replace('_', ' ', ucfirst($key)) }}:</span>
                            <span class="text-muted">{{ $rule }}</span>
                        </small>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Signal Data --}}
        @if($signal->data)
        <div class="card shadow-0 mb-g">
            <div class="card-header py-2">
                <h6 class="m-0">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#database"></use></svg>
                    Measured Values
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                        @foreach($signal->data as $key => $value)
                            <tr>
                                <td class="fw-500 text-muted" style="width: 200px;">{{ str_replace('_', ' ', ucfirst($key)) }}</td>
                                <td>
                                    @if(is_array($value))
                                        <code class="small">{{ json_encode($value, JSON_PRETTY_PRINT) }}</code>
                                    @elseif(is_bool($value))
                                        <span class="badge bg-{{ $value ? 'success' : 'secondary' }}">{{ $value ? 'Yes' : 'No' }}</span>
                                    @elseif(is_numeric($value) && $value > 1000)
                                        ${{ number_format($value) }}
                                    @elseif(is_numeric($value) && str_contains($key, 'pct'))
                                        {{ number_format($value, 1) }}%
                                    @elseif(is_numeric($value) && str_contains($key, 'ratio'))
                                        {{ number_format($value, 2) }}x
                                    @elseif(is_numeric($value) && str_contains($key, 'score'))
                                        {{ number_format($value, 1) }}
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Linked Alert --}}
        @if($signal->linkedAlert)
        <div class="card shadow-0 mb-g">
            <div class="card-header py-2">
                <h6 class="m-0">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#link"></use></svg>
                    Linked Alert
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <span class="badge bg-{{ ['critical' => 'danger', 'warning' => 'warning', 'info' => 'info', 'positive' => 'success'][$signal->linkedAlert->severity] ?? 'secondary' }} me-2">
                        {{ ucfirst($signal->linkedAlert->severity) }}
                    </span>
                    <span class="fw-500">{{ $signal->linkedAlert->title }}</span>
                    <span class="badge bg-{{ ['active' => 'primary', 'acknowledged' => 'info', 'resolved' => 'success', 'dismissed' => 'dark'][$signal->linkedAlert->status] ?? 'secondary' }} ms-2">
                        {{ ucfirst($signal->linkedAlert->status) }}
                    </span>
                </div>
                <p class="fs-sm text-muted mt-1 mb-0">{{ $signal->linkedAlert->description }}</p>
            </div>
        </div>
        @endif

        {{-- Actions --}}
        @if($signal->status === 'active' || $signal->status === 'acknowledged')
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <div class="d-flex gap-2">
                    @if($signal->status === 'active')
                    <form action="{{ route('act.signals.acknowledge', $signal) }}" method="POST">@csrf @method('PUT')
                        <button class="btn btn-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#eye"></use></svg>
                            Acknowledge
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('act.signals.resolve', $signal) }}" method="POST">@csrf @method('PUT')
                        <button class="btn btn-success">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#check"></use></svg>
                            Resolve
                        </button>
                    </form>
                    <form action="{{ route('act.signals.dismiss', $signal) }}" method="POST">@csrf @method('PUT')
                        <button class="btn btn-outline-secondary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#x"></use></svg>
                            Dismiss
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-xl-4">
        {{-- Correlated Signals --}}
        @if($correlatedSignals->isNotEmpty())
        <div class="card shadow-0 mb-g">
            <div class="card-header py-2">
                <h6 class="m-0">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#git-merge"></use></svg>
                    Correlated Signals ({{ $correlatedSignals->count() }})
                </h6>
            </div>
            <div class="card-body p-0">
                @foreach($correlatedSignals as $corr)
                <a href="{{ route('act.signals.show', $corr) }}" class="d-flex align-items-center p-2 text-decoration-none {{ !$loop->last ? 'border-bottom' : '' }}">
                    <span class="badge bg-{{ \App\Models\Signal::CHANNEL_COLORS[$corr->channel] ?? 'secondary' }} rounded-circle p-1 me-2" style="width:24px;height:24px;">
                        <svg class="sa-icon text-white" style="width:12px;height:12px">
                            <use href="/icons/sprite.svg#{{ \App\Models\Signal::CHANNEL_ICONS[$corr->channel] ?? 'activity' }}"></use>
                        </svg>
                    </span>
                    <div class="flex-grow-1">
                        <small class="fw-500 d-block">{{ Str::limit($corr->title, 60) }}</small>
                        <small class="text-muted">{{ \App\Models\Signal::CHANNELS[$corr->channel] ?? $corr->channel }} &middot; {{ ucfirst($corr->severity) }}</small>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Other Partner Signals --}}
        @if($partnerSignals->isNotEmpty())
        <div class="card shadow-0 mb-g">
            <div class="card-header py-2">
                <h6 class="m-0">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#layers"></use></svg>
                    Other {{ $signal->partner->name ?? 'Partner' }} Signals
                </h6>
            </div>
            <div class="card-body p-0">
                @foreach($partnerSignals as $ps)
                <a href="{{ route('act.signals.show', $ps) }}" class="d-flex align-items-center p-2 text-decoration-none {{ !$loop->last ? 'border-bottom' : '' }}">
                    <span class="badge bg-{{ \App\Models\Signal::CHANNEL_COLORS[$ps->channel] ?? 'secondary' }} rounded-circle p-1 me-2" style="width:24px;height:24px;">
                        <svg class="sa-icon text-white" style="width:12px;height:12px">
                            <use href="/icons/sprite.svg#{{ \App\Models\Signal::CHANNEL_ICONS[$ps->channel] ?? 'activity' }}"></use>
                        </svg>
                    </span>
                    <div class="flex-grow-1">
                        <small class="fw-500 d-block">{{ Str::limit($ps->title, 60) }}</small>
                        <small class="text-muted">{{ \App\Models\Signal::CHANNELS[$ps->channel] ?? $ps->channel }} &middot; {{ number_format($ps->confidence_score * 100, 0) }}%</small>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Acknowledged Info --}}
        @if($signal->acknowledgedByUser)
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <small class="text-muted d-block">Acknowledged by</small>
                <span class="fw-500">{{ $signal->acknowledgedByUser->name }}</span>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
