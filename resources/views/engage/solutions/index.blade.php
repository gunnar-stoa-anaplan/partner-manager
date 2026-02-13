@extends('layouts.vertical', ['pageTitle' => 'Solution Lifecycle'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Solution Lifecycle', 'pageSubTitle1' => 'Engage', 'pageSubTitle1Url' => route('welcome')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Solution Lifecycle tracks your partners' solution offerings from build through to scale. Use the pipeline view to see which solutions are in development, which are market-ready, and where you might need to invest in co-innovation. Click into any solution to review its maturity details and partner ownership.</p>
{{-- Stage Pipeline --}}
<div class="row mb-g">
    @foreach(['build' => ['Build','secondary','tool'], 'market' => ['Market','info','trending-up'], 'sell' => ['Sell','success','dollar-sign'], 'grow' => ['Grow','primary','star']] as $key => [$label, $color, $icon])
    <div class="col-lg-3">
        <div class="card shadow-0 mb-g border-top border-3 border-{{ $color }}">
            <div class="card-body text-center py-3">
                <svg class="sa-icon text-{{ $color }}" style="width:28px;height:28px"><use href="/icons/sprite.svg#{{ $icon }}"></use></svg>
                <h1 class="fw-300 m-0 mt-1">{{ $stageCounts[$key] ?? 0 }}</h1>
                <small class="text-muted">{{ $label }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-300 m-0">Solutions</h3>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createSolutionModal">
        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg> New Solution
    </button>
</div>

<div class="card shadow-0 mb-g">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="solutions-table" class="table table-bordered table-hover table-striped w-100 mb-0">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>Solution</th>
                        <th>Product Area</th>
                        <th>Industry</th>
                        <th>Stage</th>
                        <th>Days in Stage</th>
                        <th>Est. ACV</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($solutions as $sol)
                    <tr>
                        <td>{{ $sol->partner->name }}</td>
                        <td><strong>{{ $sol->name }}</strong></td>
                        <td>{{ $sol->product_area ?? '—' }}</td>
                        <td>{{ $sol->industry_focus ?? '—' }}</td>
                        <td>
                            @php $stageColors = ['build'=>'secondary','market'=>'info','sell'=>'success','grow'=>'primary']; @endphp
                            <span class="badge bg-{{ $stageColors[$sol->lifecycle_stage] }}">{{ ucfirst($sol->lifecycle_stage) }}</span>
                        </td>
                        <td data-sort="{{ $sol->stage_entered_at ? (int) now()->diffInDays($sol->stage_entered_at) : 0 }}">
                            {{ $sol->stage_entered_at ? (int) now()->diffInDays($sol->stage_entered_at) : 0 }}d
                        </td>
                        <td data-sort="{{ $sol->estimated_acv ?? 0 }}">{{ $sol->estimated_acv ? '$'.number_format($sol->estimated_acv) : '—' }}</td>
                        <td>
                            @if($sol->lifecycle_stage !== 'grow')
                            <form action="{{ route('engage.solutions.advance', $sol) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-xs btn-outline-success" title="Advance to {{ ucfirst(['build'=>'market','market'=>'sell','sell'=>'grow'][$sol->lifecycle_stage] ?? '') }}">
                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#arrow-right"></use></svg>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No solutions tracked yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Create Solution Modal --}}
<div class="modal fade" id="createSolutionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('engage.solutions.store') }}" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title">New Solution</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Partner</label><select name="partner_id" class="form-select" required><option value="">Select...</option>@foreach($partners as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
                    <div class="mb-3"><label class="form-label">Solution Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Product Area</label><input type="text" name="product_area" class="form-control"></div>
                        <div class="col-6 mb-3"><label class="form-label">Industry Focus</label><input type="text" name="industry_focus" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Stage</label><select name="lifecycle_stage" class="form-select"><option value="build">Build</option><option value="market">Market</option><option value="sell">Sell</option><option value="grow">Grow</option></select></div>
                        <div class="col-6 mb-3"><label class="form-label">Est. ACV</label><input type="number" name="estimated_acv" class="form-control" step="1000"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/table-init.js'])
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.SmartTables && document.querySelector('#solutions-table tbody tr td:not([colspan])')) {
            new window.SmartTables('solutions-table', {
                perPage: 15,
                sortable: true,
                responsive: {
                    enabled: true,
                    breakpoint: 768,
                    columnPriorities: { 0: 1, 1: 2, 4: 3, 6: 4, 5: 5, 2: 6, 3: 7, 7: 8 }
                }
            });
        }
    });
    </script>
@endsection
