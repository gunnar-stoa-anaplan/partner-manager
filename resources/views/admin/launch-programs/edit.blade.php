@extends('layouts.vertical', ['pageTitle' => 'Edit: ' . $program->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Edit Launch Program', 'pageSubTitle1' => 'Program Details', 'pageSubTitle1Url' => route('admin.launch-programs.index')])
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-0">
                <div class="card-body">
                    <form action="{{ route('admin.launch-programs.update', $program) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Program Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $program->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product</label>
                            <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id">
                                <option value="">— None —</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" @selected(old('product_id', $program->product_id) == $product->id)>{{ $product->name }} ({{ $product->product_line }})</option>
                                @endforeach
                            </select>
                            @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $program->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    @foreach(\App\Models\LaunchProgram::STATUSES as $key => $label)
                                        <option value="{{ $key }}" @selected(old('status', $program->status) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="owner_id" class="form-label">PSM Owner</label>
                                <select class="form-select @error('owner_id') is-invalid @enderror" id="owner_id" name="owner_id">
                                    <option value="">— No owner —</option>
                                    @foreach($psms as $psm)
                                        <option value="{{ $psm->id }}" @selected(old('owner_id', $program->owner_id) == $psm->id)>{{ $psm->name }}</option>
                                    @endforeach
                                </select>
                                @error('owner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="starts_at" class="form-label">Start Date</label>
                                <input type="date" class="form-control @error('starts_at') is-invalid @enderror" id="starts_at" name="starts_at" value="{{ old('starts_at', $program->starts_at?->format('Y-m-d')) }}">
                                @error('starts_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="target_ga_date" class="form-label">Target GA Date</label>
                                <input type="date" class="form-control @error('target_ga_date') is-invalid @enderror" id="target_ga_date" name="target_ga_date" value="{{ old('target_ga_date', $program->target_ga_date?->format('Y-m-d')) }}">
                                @error('target_ga_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="ends_at" class="form-label">End Date</label>
                                <input type="date" class="form-control @error('ends_at') is-invalid @enderror" id="ends_at" name="ends_at" value="{{ old('ends_at', $program->ends_at?->format('Y-m-d')) }}">
                                @error('ends_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="date_change_reason" class="form-label">Reason for Date Changes</label>
                            <input type="text" class="form-control @error('date_change_reason') is-invalid @enderror" id="date_change_reason" name="date_change_reason" value="{{ old('date_change_reason') }}" placeholder="If changing any dates above, explain why (optional)">
                            @error('date_change_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="max_partners" class="form-label">Max Partners</label>
                                <input type="number" class="form-control @error('max_partners') is-invalid @enderror" id="max_partners" name="max_partners" value="{{ old('max_partners', $program->max_partners) }}" min="1">
                                @error('max_partners') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="partner_strategy" class="form-label">Partner Strategy <span class="text-danger">*</span></label>
                                <select class="form-select @error('partner_strategy') is-invalid @enderror" id="partner_strategy" name="partner_strategy" required>
                                    @foreach(\App\Models\LaunchProgram::PARTNER_STRATEGIES as $key => $label)
                                        <option value="{{ $key }}" @selected(old('partner_strategy', $program->partner_strategy) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('partner_strategy') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3" id="exclusion-reason-group" style="{{ old('partner_strategy', $program->partner_strategy) === 'exclude' ? '' : 'display: none;' }}">
                            <label for="exclusion_reason" class="form-label">Exclusion Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('exclusion_reason') is-invalid @enderror" id="exclusion_reason" name="exclusion_reason" rows="2" placeholder="Explain why this program does not include launch partners...">{{ old('exclusion_reason', $program->exclusion_reason) }}</textarea>
                            @error('exclusion_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.launch-programs.show', $program) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Program</button>
                        </div>
                    </form>

                    @if($program->status === 'draft')
                        <hr>
                        <form action="{{ route('admin.launch-programs.destroy', $program) }}" method="POST" onsubmit="return confirm('Delete this program?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">Delete Program</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var strategySelect = document.getElementById('partner_strategy');
    var exclusionGroup = document.getElementById('exclusion-reason-group');
    strategySelect.addEventListener('change', function() {
        exclusionGroup.style.display = this.value === 'exclude' ? '' : 'none';
    });
});
</script>
@endsection
