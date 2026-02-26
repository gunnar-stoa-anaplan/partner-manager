@extends('layouts.vertical', ['pageTitle' => 'Configuration'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Configuration', 'pageSubTitle1' => 'System', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Configuration manages the system-level settings that control how Partner Manager behaves — navigation visibility, fiscal year boundaries, Salesforce integration, and default values. Changes here affect all users, so update with care. Use the sections below to fine-tune the platform to match your program's operating model.</p>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-xl-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Application Settings</div>
            </div>
            <div class="card-body">
                <form action="{{ route('configuration.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                   id="company_name" name="company_name"
                                   value="{{ old('company_name', $settings['company_name'] ?? 'Anaplan') }}">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="default_currency" class="form-label">Default Currency</label>
                            <input type="text" class="form-control @error('default_currency') is-invalid @enderror"
                                   id="default_currency" name="default_currency"
                                   value="{{ old('default_currency', $settings['default_currency'] ?? 'USD') }}"
                                   maxlength="10">
                            @error('default_currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Currency code (e.g. USD, EUR, GBP)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fiscal_year_start_month" class="form-label">Fiscal Year Start Month</label>
                            <select class="form-select @error('fiscal_year_start_month') is-invalid @enderror"
                                    id="fiscal_year_start_month" name="fiscal_year_start_month">
                                @php
                                    $months = [
                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                                    ];
                                    $selected = old('fiscal_year_start_month', $settings['fiscal_year_start_month'] ?? 2);
                                @endphp
                                @foreach($months as $num => $name)
                                    <option value="{{ $num }}" {{ (int)$selected === $num ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('fiscal_year_start_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">The month your fiscal year begins (used for quarter calculations)</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="historical_lookback_weeks" class="form-label">Historical Lookback (Weeks)</label>
                            <input type="number" class="form-control @error('historical_lookback_weeks') is-invalid @enderror"
                                   id="historical_lookback_weeks" name="historical_lookback_weeks"
                                   value="{{ old('historical_lookback_weeks', $settings['historical_lookback_weeks'] ?? 12) }}"
                                   min="1" max="52">
                            @error('historical_lookback_weeks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Number of weeks shown in trend analysis charts</small>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#save"></use></svg>
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        {{-- AI Assistant --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">AI Assistant</div>
            </div>
            <div class="card-body">
                <p class="text-muted fs-sm mb-3">Configure the AI service that powers the chat assistant. When no provider is configured, the built-in simulated assistant is used.</p>

                <form action="{{ route('configuration.update-ai') }}" method="POST">
                    @csrf
                    @method('PUT')

                    @php
                        $aiProvider = old('ai_provider', $settings['ai_provider'] ?? '');
                        $aiModel = old('ai_model', $settings['ai_model'] ?? '');
                        $aiKeySet = !empty($settings['ai_api_key']);
                        $providers = [
                            '' => 'Simulated (built-in)',
                            'openai' => 'OpenAI',
                            'anthropic' => 'Anthropic',
                            'azure_openai' => 'Azure OpenAI',
                        ];
                    @endphp

                    <div class="mb-3">
                        <label for="ai_provider" class="form-label">Provider</label>
                        <select class="form-select @error('ai_provider') is-invalid @enderror"
                                id="ai_provider" name="ai_provider">
                            @foreach($providers as $value => $label)
                                <option value="{{ $value }}" {{ $aiProvider === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('ai_provider')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ai_api_key" class="form-label">API Key</label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('ai_api_key') is-invalid @enderror"
                                   id="ai_api_key" name="ai_api_key"
                                   placeholder="{{ $aiKeySet ? '••••••••••••••••' : 'Not set' }}"
                                   autocomplete="off">
                            @if($aiKeySet)
                                <span class="input-group-text text-success" title="Key is configured">
                                    <svg class="sa-icon sa-icon-xs"><use href="/icons/sprite.svg#check"></use></svg>
                                </span>
                            @endif
                        </div>
                        @error('ai_api_key')
                            <div class="text-danger fs-sm mt-1">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Leave blank to keep the current key. Stored encrypted.</small>
                    </div>

                    <div class="mb-3">
                        <label for="ai_model" class="form-label">Model</label>
                        <input type="text" class="form-control @error('ai_model') is-invalid @enderror"
                               id="ai_model" name="ai_model"
                               value="{{ $aiModel }}"
                               placeholder="e.g. gpt-4o, claude-sonnet-4-5-20250929">
                        @error('ai_model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">The model identifier for the selected provider.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#save"></use></svg>
                        Save AI Settings
                    </button>
                </form>
            </div>
        </div>

        {{-- Salesforce Credentials --}}
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Salesforce Credentials</div>
            </div>
            <div class="card-body">
                <p class="text-muted fs-sm mb-3">Configure the connection between Partner Manager and your Salesforce instance to enable data synchronization.</p>

                @if($credential)
                    <dl class="row mb-0">
                        <dt class="col-sm-4 mb-2">Client ID</dt>
                        <dd class="col-sm-8 mb-2"><code>{{ $credential->client_id }}</code></dd>

                        <dt class="col-sm-4 mb-2">Username</dt>
                        <dd class="col-sm-8 mb-2">{{ $credential->username }}</dd>

                        <dt class="col-sm-4 mb-2">Instance URL</dt>
                        <dd class="col-sm-8 mb-2">
                            <a href="{{ $credential->instance_url }}" target="_blank" class="text-info">
                                {{ $credential->instance_url }}
                                <svg class="sa-icon sa-icon-xs ms-1"><use href="/icons/sprite.svg#external-link"></use></svg>
                            </a>
                        </dd>

                        <dt class="col-sm-4 mb-2">Auth Method</dt>
                        <dd class="col-sm-8 mb-2">
                            <span class="badge bg-primary">{{ strtoupper($credential->auth_method) }}</span>
                        </dd>

                        <dt class="col-sm-4 mb-2">Last Updated</dt>
                        <dd class="col-sm-8 mb-2">{{ $credential->updated_at->format('M j, Y g:i A') }}</dd>
                    </dl>

                    <hr>

                    <div class="d-flex gap-2">
                        <a href="{{ route('salesforce-credentials.edit', $credential->id) }}" class="btn btn-sm btn-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#edit-3"></use></svg>
                            Update Credentials
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-info" id="test-connection">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#zap"></use></svg>
                            Test Connection
                        </button>
                    </div>

                    <div id="connection-result" class="mt-3"></div>
                @else
                    <div class="text-center py-3">
                        <svg class="sa-icon mb-2 text-muted opacity-50" style="width:48px;height:48px"><use href="/icons/sprite.svg#cloud"></use></svg>
                        <h6 class="text-muted">No Salesforce credentials configured</h6>
                        <p class="text-muted fs-sm mb-3">Set up your Salesforce Connected App credentials to enable data synchronization.</p>
                        <a href="{{ route('salesforce-credentials.create') }}" class="btn btn-sm btn-primary">
                            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
                            Add Credentials
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($credential)
<script>
document.addEventListener('DOMContentLoaded', function () {
    var testBtn = document.getElementById('test-connection');
    var resultBox = document.getElementById('connection-result');

    if (testBtn) {
        testBtn.addEventListener('click', function () {
            testBtn.disabled = true;
            testBtn.innerHTML = '<svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#loader"></use></svg> Testing...';
            resultBox.innerHTML = '';

            fetch("{{ route('salesforce-credentials.test') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    client_id: @json($credential->client_id),
                    username: @json($credential->username ?? ''),
                    instance_url: @json($credential->instance_url),
                    auth_method: @json($credential->auth_method)
                })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                resultBox.className = 'mt-3 alert alert-dismissible fade show ' + (data.success ? 'alert-success' : 'alert-danger');
                resultBox.innerHTML = data.message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            })
            .catch(function(err) {
                resultBox.className = 'mt-3 alert alert-danger alert-dismissible fade show';
                resultBox.innerHTML = 'Error: ' + err.message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            })
            .finally(function() {
                testBtn.disabled = false;
                testBtn.innerHTML = '<svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#zap"></use></svg> Test Connection';
            });
        });
    }
});
</script>
@endif
@endsection
