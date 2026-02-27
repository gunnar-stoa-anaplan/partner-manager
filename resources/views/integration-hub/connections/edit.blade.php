@extends('layouts.vertical', ['pageTitle' => 'Edit Connection'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Edit: ' . $connection->name,
        'pageSubTitle1' => 'Integration Hub',
        'pageSubTitle1Url' => route('integration_hub.dashboard'),
        'pageSubTitle2' => 'Connections',
        'pageSubTitle2Url' => route('integration_hub.connections.index'),
    ])
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-8">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Edit Connection</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('integration_hub.connections.update', $connection) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $connection->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    @foreach(\App\Models\IntegrationConnection::availableTypes() as $value => $label)
                                        <option value="{{ $value }}" {{ old('type', $connection->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="direction" class="form-label">Direction</label>
                                <select class="form-select @error('direction') is-invalid @enderror" id="direction" name="direction" required>
                                    <option value="both" {{ old('direction', $connection->direction) === 'both' ? 'selected' : '' }}>Import & Export</option>
                                    <option value="import" {{ old('direction', $connection->direction) === 'import' ? 'selected' : '' }}>Import Only</option>
                                    <option value="export" {{ old('direction', $connection->direction) === 'export' ? 'selected' : '' }}>Export Only</option>
                                </select>
                                @error('direction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active" {{ old('status', $connection->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $connection->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="config-input" class="form-label">Configuration (JSON)</label>
                            <textarea class="form-control font-monospace @error('config') is-invalid @enderror" id="config-input" name="config" rows="6">{{ old('config', json_encode($connection->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
                            @error('config')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('integration_hub.connections.show', $connection) }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
