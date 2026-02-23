@extends('layouts.vertical', ['pageTitle' => 'New Field Mapping'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'New Field Mapping',
        'pageSubTitle1' => 'Integration Hub',
        'pageSubTitle1Url' => route('integration-hub.dashboard'),
        'pageSubTitle2' => 'Field Mappings',
        'pageSubTitle2Url' => route('integration-hub.mappings.index'),
    ])
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-8">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Mapping Details</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('integration-hub.mappings.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="connection_id" class="form-label">Connection</label>
                            <select class="form-select @error('connection_id') is-invalid @enderror" id="connection_id" name="connection_id" required>
                                <option value="">Select connection...</option>
                                @foreach($connections as $conn)
                                    <option value="{{ $conn->id }}" {{ old('connection_id', request('connection_id')) == $conn->id ? 'selected' : '' }}>{{ $conn->name }}</option>
                                @endforeach
                            </select>
                            @error('connection_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Mapping Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Partner Name Import">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        <h6>Field Configuration</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="source_field" class="form-label">Source Field</label>
                                <input type="text" class="form-control @error('source_field') is-invalid @enderror" id="source_field" name="source_field" value="{{ old('source_field') }}" required placeholder="e.g. partner_name or Column A">
                                @error('source_field')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="target_model" class="form-label">Target Model</label>
                                <select class="form-select @error('target_model') is-invalid @enderror" id="target_model" name="target_model" required>
                                    <option value="">Select model...</option>
                                    @foreach($targetModels as $key => $label)
                                        <option value="{{ $key }}" {{ old('target_model') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('target_model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="target_field" class="form-label">Target Field</label>
                                <input type="text" class="form-control @error('target_field') is-invalid @enderror" id="target_field" name="target_field" value="{{ old('target_field') }}" required placeholder="e.g. name">
                                @error('target_field')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="transform" class="form-label">Transform</label>
                                <select class="form-select" id="transform" name="transform">
                                    @foreach(\App\Models\FieldMapping::availableTransforms() as $key => $label)
                                        <option value="{{ $key }}" {{ old('transform') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="default_value" class="form-label">Default Value</label>
                                <input type="text" class="form-control" id="default_value" name="default_value" value="{{ old('default_value') }}" placeholder="Used when source is empty">
                            </div>
                            <div class="col-md-2">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check mb-3">
                                    <input type="hidden" name="is_required" value="0">
                                    <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1" {{ old('is_required') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_required">Required</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('integration-hub.mappings.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Mapping</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Available Transforms</div>
                </div>
                <div class="card-body">
                    <dl>
                        @foreach(\App\Models\FieldMapping::availableTransforms() as $key => $label)
                            @if($key)
                                <dt><code>{{ $key }}</code></dt>
                                <dd class="text-muted small">{{ $label }}</dd>
                            @endif
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>
    </div>

@endsection
