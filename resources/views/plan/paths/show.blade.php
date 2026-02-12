@extends('layouts.vertical', ['pageTitle' => $enrollment->partner->name . ' — Path Review'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $enrollment->partner->name, 'pageSubTitle1' => 'Partner Paths', 'pageSubTitle1Url' => route('plan.paths.index')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">This is the detailed path review for a single partner. It shows exactly where they stand in the progression, which gate requirements they've satisfied and which are still outstanding, and provides a full history of every stage transition. When all gate criteria are met, you can advance the partner to their next stage directly from this page.</p>

{{-- Stage Progress Bar --}}
<div class="card shadow-0 mb-g">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            @foreach($enrollment->path->stages->sortBy('stage_number') as $stage)
            <div class="text-center flex-fill">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $stage->id === $enrollment->current_stage_id ? 'border border-3' : '' }}"
                     style="width: 50px; height: 50px; background-color: {{ $stage->stage_number <= $enrollment->currentStage->stage_number ? $stage->color : '#e9ecef' }}; border-color: {{ $stage->color }} !important;">
                    <span class="text-white fw-700">{{ $stage->stage_number }}</span>
                </div>
                <div class="mt-1 fs-sm {{ $stage->id === $enrollment->current_stage_id ? 'fw-700' : 'text-muted' }}">{{ $stage->name }}</div>
            </div>
            @if(!$loop->last)
            <div class="flex-fill" style="height: 3px; background: {{ $stage->stage_number < $enrollment->currentStage->stage_number ? $stage->color : '#e9ecef' }}; margin-top: -20px;"></div>
            @endif
            @endforeach
        </div>
        <div class="text-center text-muted">
            Enrolled {{ $enrollment->enrolled_at->diffForHumans() }} · In <strong>{{ $enrollment->currentStage->name }}</strong> for {{ $enrollment->stage_entered_at->diffInDays(now()) }} days
        </div>
    </div>
</div>

<div class="row">
    {{-- Gate Requirements --}}
    <div class="col-lg-7">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Gate Requirements → {{ $nextStage ? $nextStage->name : 'Final Stage' }}</div>
            </div>
            <div class="card-body">
                @if($gateResults->isEmpty())
                    <p class="text-muted">Partner is at the final stage or no gates are configured.</p>
                @else
                    @php $metCount = $gateResults->where('met', true)->count(); @endphp
                    <div class="mb-3">
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ ($metCount / $gateResults->count()) * 100 }}%"></div>
                        </div>
                        <small class="text-muted">{{ $metCount }} of {{ $gateResults->count() }} requirements met</small>
                    </div>
                    <table class="table table-sm">
                        <thead><tr><th>Requirement</th><th>Threshold</th><th>Actual</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($gateResults as $gate)
                        <tr>
                            <td>{{ $gate['requirement_label'] }}</td>
                            <td>
                                @if($gate['unit'] === 'currency')${{ number_format($gate['threshold']) }}
                                @elseif($gate['unit'] === 'percentage'){{ $gate['threshold'] }}%
                                @else{{ $gate['threshold'] }}@endif
                            </td>
                            <td>
                                @if($gate['unit'] === 'currency')${{ number_format($gate['actual_value']) }}
                                @elseif($gate['unit'] === 'percentage'){{ number_format($gate['actual_value'], 1) }}%
                                @else{{ $gate['actual_value'] }}@endif
                            </td>
                            <td>
                                @if($gate['met'])
                                    <span class="badge bg-success">Met</span>
                                @else
                                    <span class="badge bg-danger">Not Met</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if($metCount === $gateResults->count() && $nextStage)
                    <form action="{{ route('plan.paths.advance', $enrollment) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="mb-2">
                            <textarea name="notes" class="form-control" rows="2" placeholder="Advancement notes (optional)"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#arrow-right"></use></svg>
                            Advance to {{ $nextStage->name }}
                        </button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Stage History --}}
    <div class="col-lg-5">
        <div class="card shadow-0 mb-g">
            <div class="card-header"><div class="card-title">Stage History</div></div>
            <div class="card-body">
                @forelse($history as $entry)
                <div class="d-flex align-items-start mb-3">
                    <div class="me-3">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                            <svg class="sa-icon sa-icon-sm text-white"><use href="/icons/sprite.svg#arrow-right"></use></svg>
                        </div>
                    </div>
                    <div>
                        <div class="fw-500">{{ $entry->fromStage ? $entry->fromStage->name : 'Enrolled' }} → {{ $entry->toStage->name }}</div>
                        <small class="text-muted">{{ $entry->transitioned_at->format('M j, Y') }} · By {{ $entry->approver->name }}</small>
                        @if($entry->notes)<div class="fs-sm text-muted mt-1">{{ $entry->notes }}</div>@endif
                    </div>
                </div>
                @empty
                <p class="text-muted">No stage transitions yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
