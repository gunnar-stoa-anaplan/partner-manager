@extends('layouts.vertical', ['pageTitle' => 'Signal Catalog'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Signal Catalog', 'pageSubTitle1' => 'Signal Engine', 'pageSubTitle1Url' => route('act.signals.index')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Reference for all signal processors, the signals they generate, and the thresholds that trigger them. Each processor runs on a daily or weekly cadence across all partners.</p>

{{-- Summary Stats --}}
<div class="row mb-g">
    <div class="col-md-3">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 text-primary">{{ count($catalog) }}</h2>
                <small class="text-muted">Processors</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                @php $totalSignalTypes = collect($catalog)->sum(fn($p) => count($p['signal_types'] ?? [])); @endphp
                <h2 class="fw-300 m-0 text-info">{{ $totalSignalTypes }}</h2>
                <small class="text-muted">Signal Types</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 text-success">{{ collect($catalog)->where('cadence', 'daily')->count() }}</h2>
                <small class="text-muted">Daily Processors</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 text-warning">{{ collect($catalog)->where('cadence', 'weekly')->count() }}</h2>
                <small class="text-muted">Weekly Processors</small>
            </div>
        </div>
    </div>
</div>

{{-- Processor Cards --}}
@foreach($catalog as $processorName => $processor)
@php
    $channelColor = \App\Models\Signal::CHANNEL_COLORS[$processor['channel']] ?? 'secondary';
    $channelIcon = \App\Models\Signal::CHANNEL_ICONS[$processor['channel']] ?? 'activity';
    $totalGenerated = $signalStats[$processorName] ?? 0;
    $activeCount = $activeStats[$processorName] ?? 0;
@endphp
<div class="card shadow-0 mb-g" id="processor-{{ $processorName }}">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <span class="badge bg-{{ $channelColor }} rounded-circle p-2 me-3">
                <svg class="sa-icon text-white" style="width:18px;height:18px">
                    <use href="/icons/sprite.svg#{{ $channelIcon }}"></use>
                </svg>
            </span>
            <div>
                <h5 class="fw-500 mb-0">{{ str_replace('_', ' ', ucwords($processorName, '_')) }}</h5>
                <div class="d-flex gap-2 mt-1">
                    <span class="badge bg-{{ $channelColor }}">{{ \App\Models\Signal::CHANNELS[$processor['channel']] ?? $processor['channel'] }}</span>
                    <span class="badge bg-{{ $processor['cadence'] === 'daily' ? 'success' : 'warning' }}">{{ ucfirst($processor['cadence']) }}</span>
                </div>
            </div>
        </div>
        <div class="text-end">
            <div class="fs-sm text-muted">Generated</div>
            <span class="fw-500">{{ $totalGenerated }} total</span>
            @if($activeCount > 0)
                <span class="badge bg-primary ms-1">{{ $activeCount }} active</span>
            @endif
        </div>
    </div>

    <div class="card-body">
        <p class="mb-3">{{ $processor['description'] }}</p>

        {{-- Data Sources --}}
        <div class="mb-3">
            <h6 class="fw-500 fs-sm text-muted text-uppercase mb-2">Data Sources</h6>
            <div class="d-flex flex-wrap gap-2">
                @foreach($processor['data_sources'] ?? [] as $source)
                    <span class="badge bg-light text-dark border">
                        <svg class="sa-icon sa-icon-sm me-1" style="width:12px;height:12px"><use href="/icons/sprite.svg#database"></use></svg>
                        {{ $source }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Signal Types --}}
        <div class="mb-3">
            <h6 class="fw-500 fs-sm text-muted text-uppercase mb-2">Signal Types</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 220px;">Type</th>
                            <th style="width: 220px;">Severity</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($processor['signal_types'] ?? [] as $type => $meta)
                        <tr>
                            <td><code class="small">{{ $type }}</code></td>
                            <td><span class="fs-sm">{{ $meta['severity'] }}</span></td>
                            <td class="fs-sm">{{ $meta['description'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Thresholds --}}
        <div>
            <h6 class="fw-500 fs-sm text-muted text-uppercase mb-2">Thresholds & Rules</h6>
            <div class="row">
                @foreach($processor['thresholds'] ?? [] as $key => $value)
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="d-flex align-items-start">
                        <svg class="sa-icon text-muted me-2 flex-shrink-0" style="width:14px;height:14px;margin-top:2px"><use href="/icons/sprite.svg#sliders"></use></svg>
                        <div>
                            <small class="fw-500 d-block">{{ str_replace('_', ' ', ucfirst($key)) }}</small>
                            <small class="text-muted">{{ $value }}</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
