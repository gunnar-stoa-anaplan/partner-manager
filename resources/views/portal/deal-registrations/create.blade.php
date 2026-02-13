@extends('portal.layout', ['pageTitle' => 'Register a Deal'])

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h2 class="fw-bold mb-4">Register a New Deal</h2>

        <div class="card portal-card">
            <div class="card-body">
                <form action="{{ route('portal.deal-registrations.store') }}" method="POST">
                    @csrf

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">Deal Name <span class="text-danger">*</span></label>
                        <input type="text" name="deal_name" class="form-control" value="{{ old('deal_name') }}" placeholder="e.g. Acme Corp — FP&A Implementation" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Customer Email</label>
                            <input type="email" name="customer_contact_email" class="form-control" value="{{ old('customer_contact_email') }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Estimated Value</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="estimated_value" class="form-control" value="{{ old('estimated_value') }}" step="1000" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Expected Close Date</label>
                            <input type="date" name="expected_close_date" class="form-control" value="{{ old('expected_close_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Product Area</label>
                            <select name="product_area" class="form-select">
                                <option value="">Select...</option>
                                @foreach($productAreas as $key => $label)
                                <option value="{{ $key }}" {{ old('product_area') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the opportunity...">{{ old('description') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('portal.deal-registrations.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Submit Registration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
