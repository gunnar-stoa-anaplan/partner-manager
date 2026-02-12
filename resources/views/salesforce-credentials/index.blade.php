@extends('layouts.vertical', ['pageTitle' => 'Salesforce Credentials'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Salesforce Credentials', 'pageSubTitle1' => 'System', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Salesforce Credentials configures the connection between Partner Manager and your Salesforce instance. Set the client ID, secret, and endpoint URLs here to enable data synchronization. The connection status shows whether Partner Manager can reach your Salesforce org — use the test button to verify before saving changes.</p>

<div class="row">
    <div class="col-xl-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Current Configuration</div>
            </div>
            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

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
                                <i class="fal fa-external-link ms-1"></i>
                            </a>
                        </dd>

                        <dt class="col-sm-4 mb-2">Redirect URI</dt>
                        <dd class="col-sm-8 mb-2">{{ $credential->redirect_uri ?? '-' }}</dd>

                        <dt class="col-sm-4 mb-2">Auth Method</dt>
                        <dd class="col-sm-8 mb-2">
                            <span class="badge bg-primary">{{ strtoupper($credential->auth_method) }}</span>
                        </dd>

                        <dt class="col-sm-4 mb-2">Last Updated</dt>
                        <dd class="col-sm-8 mb-2">{{ $credential->updated_at->format('M j, Y g:i A') }}</dd>
                    </dl>

                    <hr>

                    <div class="d-flex gap-2">
                        <a href="{{ route('salesforce-credentials.edit', $credential->id) }}" class="btn btn-primary">
                            <i class="fal fa-edit me-1"></i> Update Credentials
                        </a>
                        <button type="button" class="btn btn-outline-info" id="test-connection">
                            <i class="fal fa-plug me-1"></i> Test Connection
                        </button>
                    </div>

                    <div id="connection-result" class="mt-3"></div>
                @else
                    <div class="text-center py-4">
                        <i class="fal fa-cloud fa-3x text-muted opacity-50 mb-3"></i>
                        <h5 class="text-muted">No Salesforce credentials configured</h5>
                        <p class="text-muted mb-3">Set up your Salesforce Connected App credentials to enable data synchronization.</p>
                        <a href="{{ route('salesforce-credentials.create') }}" class="btn btn-primary">
                            <i class="fal fa-plus me-1"></i> Add Credentials
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Setup Guide</div>
            </div>
            <div class="card-body">
                <p class="text-muted fs-sm">This integration uses the <strong>JWT Bearer Flow</strong> for server-to-server authentication with Salesforce.</p>
                <ol class="text-muted fs-sm mb-0">
                    <li class="mb-2">Create a Connected App in Salesforce Setup</li>
                    <li class="mb-2">Enable OAuth and select the JWT Bearer flow</li>
                    <li class="mb-2">Upload the public key (X.509 certificate) to the Connected App</li>
                    <li class="mb-2">Place the private key at <code>storage/oauth/private.key</code></li>
                    <li class="mb-2">Enter the Client ID and Username here</li>
                    <li>Use "Test Connection" to verify the configuration</li>
                </ol>
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
            testBtn.innerHTML = '<i class="fal fa-spinner fa-spin me-1"></i> Testing...';
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
                resultBox.innerHTML = '<i class="fal ' + (data.success ? 'fa-check-circle' : 'fa-exclamation-circle') + ' me-1"></i> ' +
                    data.message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            })
            .catch(function(err) {
                resultBox.className = 'mt-3 alert alert-danger alert-dismissible fade show';
                resultBox.innerHTML = '<i class="fal fa-exclamation-circle me-1"></i> Error: ' + err.message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            })
            .finally(function() {
                testBtn.disabled = false;
                testBtn.innerHTML = '<i class="fal fa-plug me-1"></i> Test Connection';
            });
        });
    }
});
</script>
@endif
@endsection
