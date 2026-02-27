@extends('layouts.vertical', ['pageTitle' => 'Set Up ' . $profile->name])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Set Up ' . $profile->name,
        'pageSubTitle1' => 'Integration Hub',
        'pageSubTitle1Url' => route('integration_hub.dashboard'),
        'pageSubTitle2' => 'Connectors',
        'pageSubTitle2Url' => route('integration_hub.connectors.catalog'),
    ])
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-8">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#{{ $profile->icon }}"></use></svg>
                        {{ $profile->name }} Configuration
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('integration_hub.connectors.store', $profile) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Connection Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   value="{{ old('name', $profile->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(!empty($profile->config_schema))
                            <hr>
                            <h6 class="text-muted mb-3">Connection Settings</h6>

                            @foreach($profile->config_schema as $field)
                                <div class="mb-3">
                                    <label for="config-{{ $field['key'] }}" class="form-label">
                                        {{ $field['label'] }}
                                        @if($field['required'] ?? false)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    @if(($field['type'] ?? 'text') === 'select' && isset($field['options']))
                                        <select class="form-select @error('config.' . $field['key']) is-invalid @enderror"
                                                id="config-{{ $field['key'] }}"
                                                name="config[{{ $field['key'] }}]">
                                            @foreach($field['options'] as $value => $label)
                                                <option value="{{ $value }}" {{ old('config.' . $field['key']) === $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="{{ $field['type'] ?? 'text' }}"
                                               class="form-control @error('config.' . $field['key']) is-invalid @enderror"
                                               id="config-{{ $field['key'] }}"
                                               name="config[{{ $field['key'] }}]"
                                               value="{{ old('config.' . $field['key']) }}"
                                               placeholder="{{ $field['placeholder'] ?? '' }}"
                                               {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                    @endif

                                    @error('config.' . $field['key'])
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        @endif

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('integration_hub.connectors.catalog') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#check"></use></svg>
                                Create Connection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">What Happens Next</div>
                </div>
                <div class="card-body">
                    <ol class="list-group list-group-numbered list-group-flush">
                        <li class="list-group-item border-0 px-0">A new <strong>{{ ucfirst(str_replace('_', ' ', $profile->connection_type)) }}</strong> connection is created</li>
                        @if($templates->isNotEmpty())
                            <li class="list-group-item border-0 px-0">Field mappings are auto-applied from <strong>{{ $templates->count() }}</strong> template(s)</li>
                        @endif
                        <li class="list-group-item border-0 px-0">You can test and customize the connection immediately</li>
                    </ol>
                </div>
            </div>

            @if($templates->isNotEmpty())
                <div class="card shadow-0 mb-g">
                    <div class="card-header">
                        <div class="card-title">Templates to Apply</div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Model</th>
                                        <th class="text-end">Fields</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)
                                        <tr>
                                            <td>
                                                <span class="badge bg-info me-1">{{ $template->target_model }}</span>
                                                {{ $template->name }}
                                            </td>
                                            <td class="text-end">{{ count($template->fields) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
