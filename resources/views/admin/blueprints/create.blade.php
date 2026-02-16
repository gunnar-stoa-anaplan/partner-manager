@extends('layouts.vertical', ['pageTitle' => 'Create Blueprint'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Create Blueprint', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubTitle2' => 'Enablement Blueprints', 'pageSubTitle2Url' => route('blueprints.index'), 'pageSubText' => 'Define a new partner enablement blueprint'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Use this form to create a new enablement blueprint. Define the name, category, and role focus, then add outcomes and content items from the detail page after saving. Blueprints can be assigned to partners to track their enablement progress.</p>

    <div class="row">
        <div class="col-xl-8 offset-xl-2">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">New Blueprint</div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('blueprints.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="name">Blueprint Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category...</option>
                                    @foreach(\App\Models\Blueprint::CATEGORIES as $value => $label)
                                        <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="product_area">Product Area</label>
                                <select class="form-select" id="product_area" name="product_area">
                                    <option value="">Select Area...</option>
                                    @foreach(\App\Models\Blueprint::PRODUCT_AREAS as $area)
                                        <option value="{{ $area }}" {{ old('product_area') === $area ? 'selected' : '' }}>{{ $area }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="version">Version</label>
                                <input type="text" class="form-control" id="version" name="version" value="{{ old('version') }}" placeholder="e.g. V1.3">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    @foreach(\App\Models\Blueprint::STATUSES as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="owner_id">Owner</label>
                                <select class="form-select" id="owner_id" name="owner_id">
                                    <option value="">No Owner</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('owner_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Optional. The owner can edit and submit this blueprint for publishing.</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Create Blueprint</button>
                            <a href="{{ route('blueprints.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
