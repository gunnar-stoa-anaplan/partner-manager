@extends('layouts.vertical', ['pageTitle' => 'Portfolio Priorities'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Portfolio Priorities',
        'pageSubTitle1' => 'Partner Management',
    ])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- KPI Summary -->
    <div class="row mb-g">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Partners</p>
                            <h3 class="mb-0">{{ $totalPartners }}</h3>
                        </div>
                        <div class="text-primary opacity-50">
                            <svg class="sa-icon" style="width:32px;height:32px"><use href="/icons/sprite.svg#users"></use></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">At Risk + Critical</p>
                            <h3 class="mb-0 {{ $atRiskCount > 0 ? 'text-danger' : '' }}">{{ $atRiskCount }}</h3>
                        </div>
                        <div class="text-danger opacity-50">
                            <svg class="sa-icon" style="width:32px;height:32px"><use href="/icons/sprite.svg#alert-triangle"></use></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Active Objectives</p>
                            <h3 class="mb-0">{{ $activeObjectives }}</h3>
                        </div>
                        <div class="text-info opacity-50">
                            <svg class="sa-icon" style="width:32px;height:32px"><use href="/icons/sprite.svg#target"></use></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Avg Progress</p>
                            <h3 class="mb-0">{{ number_format($avgProgress, 1) }}%</h3>
                        </div>
                        <div class="text-success opacity-50">
                            <svg class="sa-icon" style="width:32px;height:32px"><use href="/icons/sprite.svg#trending-up"></use></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Partner Cards -->
        <div class="col-xl-8">
            <div class="row mb-2">
                <div class="col-12">
                    <h4 class="fw-500 mb-0">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#briefcase"></use></svg>
                        Partners by Urgency
                    </h4>
                    <hr class="mt-2 mb-3">
                </div>
            </div>
            <div class="row">
                @forelse($portfolio as $item)
                    <div class="col-xl-6 col-lg-6 mb-g">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0">
                                        <a href="{{ route('partners.show', $item['partner']) }}#psm-priorities" class="text-decoration-none">
                                            {{ $item['partner']->name }}
                                        </a>
                                    </h5>
                                    @if($item['snapshot'])
                                        <span class="badge bg-{{ \App\Models\PartnerHealthSnapshot::RISK_COLORS[$item['snapshot']->risk_level] ?? 'secondary' }}">
                                            {{ \App\Models\PartnerHealthSnapshot::RISK_LEVELS[$item['snapshot']->risk_level] ?? $item['snapshot']->risk_level }}
                                        </span>
                                    @endif
                                </div>

                                @if($item['snapshot'])
                                    <!-- Health Score Bar -->
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted">Health Score</small>
                                            <small class="fw-500">{{ $item['snapshot']->score }}/100</small>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $item['snapshot']->score >= 70 ? 'success' : ($item['snapshot']->score >= 50 ? 'warning' : 'danger') }}"
                                                 style="width: {{ $item['snapshot']->score }}%"></div>
                                        </div>
                                    </div>

                                    <!-- Top Signals -->
                                    @if(!empty($item['snapshot']->signals))
                                        <div class="mb-2">
                                            @foreach(array_slice($item['snapshot']->signals, 0, 2) as $signal)
                                                <small class="d-block text-muted">
                                                    <svg class="sa-icon" style="width:12px;height:12px"><use href="/icons/sprite.svg#alert-circle"></use></svg>
                                                    {{ Str::limit($signal, 60) }}
                                                </small>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif

                                <!-- Objectives Summary -->
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <small class="text-muted">
                                        {{ $item['objectives_count'] }} active objective{{ $item['objectives_count'] !== 1 ? 's' : '' }}
                                    </small>
                                    @if($item['objectives_count'] > 0)
                                        <small class="fw-500">{{ number_format($item['avg_progress'], 0) }}% avg</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">No partners assigned. Partner assignments are managed in Partner Administration.</div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Suggestions Panel -->
        <div class="col-xl-4">
            <div class="row mb-2">
                <div class="col-12">
                    <h4 class="fw-500 mb-0">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#zap"></use></svg>
                        Suggested Objectives
                    </h4>
                    <hr class="mt-2 mb-3">
                </div>
            </div>

            @if(!empty($suggestions['urgent']))
                @foreach($suggestions['urgent'] as $suggestion)
                    <div class="card border-danger mb-2">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-start mb-1">
                                <span class="badge bg-danger me-2">Urgent</span>
                                <small class="text-muted">{{ $suggestion['partner']->name }}</small>
                            </div>
                            <p class="mb-2 fw-500">{{ $suggestion['title'] }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    {{ \App\Models\PsmObjective::CATEGORIES[$suggestion['category']] ?? $suggestion['category'] }}
                                    &middot; Target: {{ $suggestion['suggested_target'] }}
                                </small>
                                <form action="{{ route('priorities.objectives.store') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="partner_id" value="{{ $suggestion['partner']->id }}">
                                    <input type="hidden" name="title" value="{{ $suggestion['title'] }}">
                                    <input type="hidden" name="category" value="{{ $suggestion['category'] }}">
                                    <input type="hidden" name="metric_type" value="{{ $suggestion['metric_type'] }}">
                                    <input type="hidden" name="target_value" value="{{ $suggestion['suggested_target'] }}">
                                    <input type="hidden" name="target_date" value="{{ now()->addWeeks(4)->toDateString() }}">
                                    <input type="hidden" name="source_signal" value="{{ $suggestion['signal'] }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Accept</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            @if(!empty($suggestions['recommendations']))
                @foreach($suggestions['recommendations'] as $suggestion)
                    <div class="card mb-2">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-start mb-1">
                                <span class="badge bg-info me-2">Recommended</span>
                                <small class="text-muted">{{ $suggestion['partner']->name }}</small>
                            </div>
                            <p class="mb-2 fw-500">{{ $suggestion['title'] }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    {{ \App\Models\PsmObjective::CATEGORIES[$suggestion['category']] ?? $suggestion['category'] }}
                                    &middot; Target: {{ $suggestion['suggested_target'] }}
                                </small>
                                <form action="{{ route('priorities.objectives.store') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="partner_id" value="{{ $suggestion['partner']->id }}">
                                    <input type="hidden" name="title" value="{{ $suggestion['title'] }}">
                                    <input type="hidden" name="category" value="{{ $suggestion['category'] }}">
                                    <input type="hidden" name="metric_type" value="{{ $suggestion['metric_type'] }}">
                                    <input type="hidden" name="target_value" value="{{ $suggestion['suggested_target'] }}">
                                    <input type="hidden" name="target_date" value="{{ now()->addWeeks(4)->toDateString() }}">
                                    <input type="hidden" name="source_signal" value="{{ $suggestion['signal'] }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Accept</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            @if(empty($suggestions['urgent']) && empty($suggestions['recommendations']))
                <div class="card">
                    <div class="card-body text-center text-muted p-4">
                        <svg class="sa-icon mb-2" style="width:32px;height:32px"><use href="/icons/sprite.svg#check-circle"></use></svg>
                        <p class="mb-0">No new suggestions. Your portfolio is in good shape!</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
