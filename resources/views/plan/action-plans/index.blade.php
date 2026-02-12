@extends('layouts.vertical', ['pageTitle' => 'Action Plans'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Action Plans', 'pageSubTitle1' => 'Plan', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Action Plans let you create and track structured initiatives for your partners — whether they come from a QBR, a partner review, an escalation, or your own proactive planning. Each plan is tied to a partner with a priority and due date, and moves across the board from open through in-progress to completed. Use this view to keep your strategic commitments organized and visible.</p>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-300 m-0">Action Plans</h3>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPlanModal">
        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> New Action Plan
    </button>
</div>

{{-- Kanban View --}}
<div class="row">
    @foreach(['open' => 'secondary', 'in_progress' => 'primary', 'overdue' => 'danger', 'completed' => 'success'] as $status => $color)
    <div class="col-lg-3">
        <h6 class="text-{{ $color }} text-uppercase fw-700 mb-2">{{ ucfirst(str_replace('_',' ',$status)) }} ({{ $grouped->get($status, collect())->count() }})</h6>
        @forelse($grouped->get($status, collect()) as $plan)
        <div class="card shadow-0 mb-2 border-start border-3 border-{{ $color }}">
            <div class="card-body p-3">
                <div class="fw-500">{{ $plan->title }}</div>
                <small class="text-muted">{{ $plan->partner->name }}</small>
                <div class="d-flex justify-content-between mt-2">
                    <span class="badge bg-{{ ['low'=>'info','medium'=>'secondary','high'=>'warning','critical'=>'danger'][$plan->priority] }}">{{ ucfirst($plan->priority) }}</span>
                    <small class="text-muted">Due {{ $plan->due_date->format('M j') }}</small>
                </div>
                @if($plan->items->isNotEmpty())
                <div class="progress mt-2" style="height: 4px;">
                    @php $done = $plan->items->where('status', 'done')->count(); $total = $plan->items->count(); @endphp
                    <div class="progress-bar bg-success" style="width: {{ $total > 0 ? ($done/$total)*100 : 0 }}%"></div>
                </div>
                <small class="text-muted">{{ $done }}/{{ $total }} items</small>
                @endif
            </div>
        </div>
        @empty
        <div class="text-muted fs-sm p-2">No plans</div>
        @endforelse
    </div>
    @endforeach
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('plan.action-plans.store') }}" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title">New Action Plan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Partner</label><select name="partner_id" class="form-select" required>
                        <option value="">Select...</option>@foreach($partners as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select></div>
                    <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control" required></div>
                        <div class="col-6 mb-3"><label class="form-label">Priority</label><select name="priority" class="form-select"><option value="medium">Medium</option><option value="high">High</option><option value="critical">Critical</option><option value="low">Low</option></select></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Source</label><select name="source" class="form-select"><option value="psm_initiative">PSM Initiative</option><option value="qbr">QBR</option><option value="partner_review">Partner Review</option><option value="escalation">Escalation</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
