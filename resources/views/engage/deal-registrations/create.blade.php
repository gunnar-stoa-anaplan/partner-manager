@extends('layouts.vertical', ['pageTitle' => 'Register a Deal'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Register a Deal', 'pageSubTitle1' => 'Deal Registrations', 'pageSubTitle1Url' => route('engage.deal-registrations.index')])
@endsection

@section('content')
<div class="row justify-content-center mt-3">
    <div class="col-lg-8">
        <div class="card shadow-0 mb-g">
            <div class="card-body">
                <form action="{{ route('engage.deal-registrations.store') }}" method="POST">
                    @csrf

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-500">Partner <span class="text-danger">*</span></label>
                        <select name="partner_id" class="form-select" required>
                            <option value="">Select partner...</option>
                            @foreach($partners as $p)
                            <option value="{{ $p->id }}" {{ old('partner_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Deal Name <span class="text-danger">*</span></label>
                        <input type="text" name="deal_name" class="form-control" value="{{ old('deal_name') }}" placeholder="e.g. Acme Corp — FP&A Implementation" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Customer Contact Email</label>
                            <input type="email" name="customer_contact_email" class="form-control" value="{{ old('customer_contact_email') }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-500">Estimated Value</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="estimated_value" class="form-control" value="{{ old('estimated_value') }}" step="1000" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Expected Close Date</label>
                            <input type="date" name="expected_close_date" class="form-control" value="{{ old('expected_close_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Product Area</label>
                            <select name="product_area" class="form-select">
                                <option value="">Select...</option>
                                @foreach($productAreas as $key => $label)
                                <option value="{{ $key }}" {{ old('product_area') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-500">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the opportunity, customer need, and proposed solution...">{{ old('description') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('engage.deal-registrations.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Submit Registration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
