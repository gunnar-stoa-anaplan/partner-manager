@extends('layouts.vertical', ['pageTitle' => 'Create Launch Program'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Create Launch Program', 'pageSubTitle1' => 'Launch Programs', 'pageSubTitle1Url' => route('admin.launch-programs.index')])
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-0">
                <div class="card-body">
                    <form action="{{ route('admin.launch-programs.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Program Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product</label>
                            <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id">
                                <option value="">— None —</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }} ({{ $product->product_line }})</option>
                                @endforeach
                            </select>
                            @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                @foreach(\App\Models\LaunchProgram::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @selected(old('status', 'draft') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="starts_at" class="form-label">Start Date</label>
                                <input type="date" class="form-control @error('starts_at') is-invalid @enderror" id="starts_at" name="starts_at" value="{{ old('starts_at') }}">
                                @error('starts_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="target_ga_date" class="form-label">Target GA Date</label>
                                <input type="date" class="form-control @error('target_ga_date') is-invalid @enderror" id="target_ga_date" name="target_ga_date" value="{{ old('target_ga_date') }}">
                                @error('target_ga_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="ends_at" class="form-label">End Date</label>
                                <input type="date" class="form-control @error('ends_at') is-invalid @enderror" id="ends_at" name="ends_at" value="{{ old('ends_at') }}">
                                @error('ends_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="max_partners" class="form-label">Max Partners</label>
                            <input type="number" class="form-control @error('max_partners') is-invalid @enderror" id="max_partners" name="max_partners" value="{{ old('max_partners') }}" min="1" style="max-width: 150px;">
                            @error('max_partners') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.launch-programs.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Program</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
