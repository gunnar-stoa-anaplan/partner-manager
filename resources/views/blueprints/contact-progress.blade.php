@extends('layouts.vertical', ['pageTitle' => 'Contact Progress'])

@section('css')
<style>
    .progress-item {
        transition: background-color 0.2s ease;
    }
    .progress-item:hover {
        background-color: rgba(0,0,0,0.02);
    }
    .progress-item.completed {
        opacity: 0.7;
    }
    .progress-item.completed .item-title {
        text-decoration: line-through;
    }
    .check-toggle {
        cursor: pointer;
        font-size: 1.25rem;
    }
</style>
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> $enrollment->contact->first_name . ' ' . $enrollment->contact->last_name,
        'pageSubTitle1' => $enrollment->assignment->blueprint->name,
        'pageSubText' => \App\Models\PartnerBlueprintAssignment::ROLES[$enrollment->assignment->role] . ' Progress'
    ])
@endsection

@section('content')
    {{-- Summary Card --}}
    <div class="row mb-g">
        <div class="col-lg-12">
            <div class="card shadow-0">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        {{ $enrollment->contact->first_name }} {{ $enrollment->contact->last_name }}
                        @if($enrollment->contact->title)
                            <small class="text-muted ms-2">{{ $enrollment->contact->title }}</small>
                        @endif
                    </div>
                    <a href="{{ route('blueprint-assignments.show', $enrollment->assignment) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fal fa-arrow-left me-1"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Blueprint</small>
                            <strong>{{ $enrollment->assignment->blueprint->name }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Partner</small>
                            <strong>{{ $enrollment->assignment->partner->name }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Role</small>
                            <span class="badge" style="background-color: {{ \App\Models\PartnerBlueprintAssignment::ROLE_COLORS[$role] ?? '#868e96' }}">
                                {{ \App\Models\PartnerBlueprintAssignment::ROLES[$role] ?? $role }}
                            </span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge" style="background-color: {{ \App\Models\BlueprintEnrollment::STATUS_COLORS[$enrollment->status] ?? '#868e96' }}" id="enrollment-status">
                                {{ \App\Models\BlueprintEnrollment::STATUSES[$enrollment->status] ?? $enrollment->status }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Completion</small>
                            @php
                                $pct = $enrollment->completionPercentage();
                                $completedCount = $enrollment->completedItemsCount();
                                $totalCount = $applicableItems->count();
                                $progressColor = $pct >= 100 ? 'success' : ($pct >= 50 ? 'info' : ($pct > 0 ? 'warning' : 'secondary'));
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 10px;">
                                    <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $pct }}%" id="progress-bar"></div>
                                </div>
                                <strong id="progress-text">{{ $completedCount }}/{{ $totalCount }}</strong>
                                <small class="text-muted" id="progress-pct">({{ $pct }}%)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Content Items by Outcome --}}
    @foreach($outcomes as $outcome)
        @php
            $roleColumn = match($role) {
                'gtm' => 'gtm_applicable',
                'pre_sales' => 'pre_sales_applicable',
                'delivery' => 'delivery_applicable',
                default => 'delivery_applicable',
            };
            $outcomeItems = $outcome->contentItems->where($roleColumn, '!=', 'na');
        @endphp

        @if($outcomeItems->count())
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-0 mb-g">
                        <div class="card-header">
                            <div class="card-title">
                                <span class="badge bg-primary me-2">{{ $outcome->sort_order }}</span>
                                {{ $outcome->title }}
                                <small class="text-muted ms-2">
                                    ({{ $outcomeItems->filter(fn($i) => $progressMap->has($i->id) && $progressMap->get($i->id)->completed)->count() }}/{{ $outcomeItems->count() }})
                                </small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @foreach($outcomeItems as $item)
                                @php
                                    $progress = $progressMap->get($item->id);
                                    $isCompleted = $progress && $progress->completed;
                                    $applicability = $item->$roleColumn;
                                @endphp
                                <div class="progress-item d-flex align-items-center p-3 border-bottom {{ $isCompleted ? 'completed' : '' }}" data-item-id="{{ $item->id }}">
                                    <span class="check-toggle me-3" onclick="toggleItem({{ $enrollment->id }}, {{ $item->id }}, this)">
                                        @if($isCompleted)
                                            <i class="fas fa-check-square text-success"></i>
                                        @else
                                            <i class="far fa-square text-muted"></i>
                                        @endif
                                    </span>
                                    <div class="flex-grow-1">
                                        <span class="item-title">
                                            @if($item->url)
                                                <a href="{{ $item->url }}" target="_blank">{{ $item->title }} <i class="fal fa-external-link ms-1 small"></i></a>
                                            @else
                                                {{ $item->title }}
                                            @endif
                                        </span>
                                        @if($applicability === 'optional')
                                            <span class="badge bg-light text-muted border ms-2 small">optional</span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-3 text-muted small ms-3 text-nowrap">
                                        @if($item->duration_display)
                                            <span><i class="fal fa-clock me-1"></i>{{ $item->duration_display }}</span>
                                        @endif
                                        @if($item->source)
                                            <span>{{ $item->source }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection

@section('scripts')
    <script>
        function toggleItem(enrollmentId, contentItemId, element) {
            fetch('{{ route("api.blueprint-progress.toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    enrollment_id: enrollmentId,
                    content_item_id: contentItemId,
                }),
            })
            .then(r => r.json())
            .then(data => {
                const row = element.closest('.progress-item');
                if (data.completed) {
                    element.innerHTML = '<i class="fas fa-check-square text-success"></i>';
                    row.classList.add('completed');
                } else {
                    element.innerHTML = '<i class="far fa-square text-muted"></i>';
                    row.classList.remove('completed');
                }

                // Update progress bar
                const pct = data.completion_percentage;
                const bar = document.getElementById('progress-bar');
                const pctText = document.getElementById('progress-pct');
                bar.style.width = pct + '%';
                bar.className = 'progress-bar bg-' + (pct >= 100 ? 'success' : (pct >= 50 ? 'info' : (pct > 0 ? 'warning' : 'secondary')));
                pctText.textContent = '(' + pct + '%)';

                // Update completed count
                const allChecks = document.querySelectorAll('.fas.fa-check-square');
                const allItems = document.querySelectorAll('.progress-item');
                document.getElementById('progress-text').textContent = allChecks.length + '/' + allItems.length;
            })
            .catch(err => console.error('Error:', err));
        }
    </script>
@endsection
