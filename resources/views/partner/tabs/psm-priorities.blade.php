{{-- PSM Priorities Tab: Active objectives + recently completed --}}

<div class="row">
    <div class="col-12">
        <h5 class="mb-3">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#target"></use></svg>
            Active Objectives
        </h5>

        @forelse($activeObjectives as $objective)
            <div class="card mb-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="fw-500">{{ $objective->title }}</span>
                            <span class="badge bg-{{ \App\Models\PsmObjective::CATEGORY_COLORS[$objective->category] ?? 'secondary' }} ms-1">
                                {{ \App\Models\PsmObjective::CATEGORIES[$objective->category] ?? $objective->category }}
                            </span>
                            @if($objective->suggested_by)
                                <span class="badge bg-info ms-1">Manager Suggested</span>
                            @endif
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Due {{ $objective->target_date->format('M j, Y') }}</small>
                            <small class="text-muted">PSM: {{ $objective->user->name }}</small>
                        </div>
                    </div>

                    @if($objective->description)
                        <p class="text-muted small mb-2">{{ $objective->description }}</p>
                    @endif

                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">
                            {{ \App\Models\PsmObjective::METRIC_TYPES[$objective->metric_type] ?? $objective->metric_type }}
                        </small>
                        <small class="fw-500">
                            {{ number_format($objective->current_value) }} / {{ number_format($objective->target_value) }}
                        </small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        @php $pct = $objective->progressPercentage(); @endphp
                        <div class="progress-bar bg-{{ $pct >= 75 ? 'success' : ($pct >= 40 ? 'primary' : 'warning') }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block">{{ number_format($pct, 0) }}% complete</small>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <svg class="sa-icon mb-2" style="width:32px;height:32px"><use href="/icons/sprite.svg#target"></use></svg>
                <p class="mb-0">No active objectives for this partner.</p>
            </div>
        @endforelse
    </div>
</div>

@if($recentCompleted->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#check-circle"></use></svg>
                Recently Completed <small class="text-muted">(Last 30 days)</small>
            </h5>

            @foreach($recentCompleted as $objective)
                <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                    <span class="badge bg-success me-2">Completed</span>
                    <div class="flex-grow-1">
                        <span>{{ $objective->title }}</span>
                        <small class="text-muted ms-1">
                            {{ $objective->completed_at->format('M j') }}
                        </small>
                    </div>
                    <small class="text-muted">{{ $objective->user->name }}</small>
                </div>
            @endforeach
        </div>
    </div>
@endif
