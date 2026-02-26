@extends('layouts.vertical', ['pageTitle' => 'New Connection'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'New Connection',
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
                    <div class="card-title">Connection Details</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('integration_hub.connections.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Salesforce Production">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Select type...</option>
                                    @foreach(\App\Models\IntegrationConnection::availableTypes() as $value => $label)
                                        <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="direction" class="form-label">Direction</label>
                                <select class="form-select @error('direction') is-invalid @enderror" id="direction" name="direction" required>
                                    <option value="both" {{ old('direction', 'both') === 'both' ? 'selected' : '' }}>Import & Export</option>
                                    <option value="import" {{ old('direction') === 'import' ? 'selected' : '' }}>Import Only</option>
                                    <option value="export" {{ old('direction') === 'export' ? 'selected' : '' }}>Export Only</option>
                                </select>
                                @error('direction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- REST API config --}}
                        <div id="config-rest_api" class="config-section d-none">
                            <hr>
                            <h6>REST API Configuration</h6>
                            <div class="mb-3">
                                <label class="form-label">Base URL</label>
                                <input type="url" class="form-control config-field" data-key="base_url" placeholder="https://api.example.com/v1">
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Auth Type</label>
                                    <select class="form-select config-field" data-key="auth_type">
                                        <option value="none">None</option>
                                        <option value="bearer">Bearer Token</option>
                                        <option value="basic">Basic Auth</option>
                                        <option value="api_key">API Key</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Auth Value</label>
                                    <input type="password" class="form-control config-field" data-key="auth_value" placeholder="Token or credentials">
                                </div>
                            </div>
                        </div>

                        {{-- Salesforce config --}}
                        <div id="config-salesforce" class="config-section d-none">
                            <hr>
                            <h6>Salesforce Configuration</h6>
                            <div class="mb-3">
                                <label class="form-label">Instance URL</label>
                                <input type="url" class="form-control config-field" data-key="instance_url" placeholder="https://yourorg.my.salesforce.com">
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Client ID</label>
                                    <input type="text" class="form-control config-field" data-key="client_id">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Client Secret</label>
                                    <input type="password" class="form-control config-field" data-key="client_secret">
                                </div>
                            </div>
                        </div>

                        {{-- Webhook config --}}
                        <div id="config-webhook" class="config-section d-none">
                            <hr>
                            <h6>Webhook Configuration</h6>
                            <div class="mb-3">
                                <label class="form-label">Webhook URL (for outbound)</label>
                                <input type="url" class="form-control config-field" data-key="webhook_url" placeholder="https://example.com/webhook">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Secret Key</label>
                                <input type="password" class="form-control config-field" data-key="webhook_secret" placeholder="Shared secret for signature verification">
                            </div>
                        </div>

                        {{-- Database config --}}
                        <div id="config-database" class="config-section d-none">
                            <hr>
                            <h6>Database Configuration</h6>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Driver</label>
                                    <select class="form-select config-field" data-key="driver">
                                        <option value="mysql">MySQL</option>
                                        <option value="pgsql">PostgreSQL</option>
                                        <option value="sqlsrv">SQL Server</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Host</label>
                                    <input type="text" class="form-control config-field" data-key="host" placeholder="db.example.com">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Port</label>
                                    <input type="number" class="form-control config-field" data-key="port" placeholder="3306">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Database</label>
                                    <input type="text" class="form-control config-field" data-key="database">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control config-field" data-key="username">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control config-field" data-key="password">
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="config" id="config-json">

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('integration_hub.connections.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Connection</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Connection Types</div>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>CSV File Upload</dt>
                        <dd class="text-muted small">Upload CSV or Excel files manually for batch imports.</dd>

                        <dt>REST API</dt>
                        <dd class="text-muted small">Connect to external REST APIs for automated data sync.</dd>

                        <dt>Salesforce</dt>
                        <dd class="text-muted small">Direct integration with Salesforce CRM using OAuth.</dd>

                        <dt>Webhook</dt>
                        <dd class="text-muted small">Receive inbound data pushes or send outbound notifications.</dd>

                        <dt>External Database</dt>
                        <dd class="text-muted small">Direct database-to-database connection for bulk transfers.</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
const typeSelect = document.getElementById('type');
const configJson = document.getElementById('config-json');

typeSelect.addEventListener('change', function() {
    document.querySelectorAll('.config-section').forEach(s => s.classList.add('d-none'));
    const section = document.getElementById('config-' + this.value);
    if (section) {
        section.classList.remove('d-none');
    }
});

document.querySelector('form').addEventListener('submit', function() {
    const activeSection = document.querySelector('.config-section:not(.d-none)');
    if (!activeSection) {
        configJson.value = '{}';
        return;
    }

    const config = {};
    activeSection.querySelectorAll('.config-field').forEach(field => {
        const key = field.dataset.key;
        if (field.value) {
            config[key] = field.value;
        }
    });
    configJson.value = JSON.stringify(config);
});

// Trigger on load if old value
if (typeSelect.value) {
    typeSelect.dispatchEvent(new Event('change'));
}
</script>
@endsection
