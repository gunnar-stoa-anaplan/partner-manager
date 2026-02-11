@extends('layouts.vertical', ['pageTitle' => 'Edit Salesforce Credentials'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Edit Salesforce Credentials', 'pageSubTitle1' => 'System'])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Update Connector Configuration</div>
            </div>
            <div class="card-body">
                <p class="text-muted fs-sm mb-4">
                    Update the details of your Salesforce Connected App credentials below.
                </p>

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form id="credential-form" method="POST" action="{{ route('salesforce-credentials.update', $credential->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="client_id" class="form-label">Salesforce Client ID <span class="text-danger">*</span></label>
                            <input type="text" name="client_id" id="client_id"
                                   class="form-control @error('client_id') is-invalid @enderror"
                                   value="{{ old('client_id', $credential->client_id) }}" required>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="client_secret" class="form-label">Client Secret <span class="text-danger">*</span></label>
                            <input type="password" name="client_secret" id="client_secret"
                                   class="form-control @error('client_secret') is-invalid @enderror"
                                   value="{{ old('client_secret') }}" required>
                            @error('client_secret')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="instance_url" class="form-label">Instance URL <span class="text-danger">*</span></label>
                            <input type="url" name="instance_url" id="instance_url"
                                   class="form-control @error('instance_url') is-invalid @enderror"
                                   value="{{ old('instance_url', $credential->instance_url) }}" required>
                            @error('instance_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="redirect_uri" class="form-label">Redirect URI <small class="text-muted">(optional)</small></label>
                            <input type="url" name="redirect_uri" id="redirect_uri"
                                   class="form-control @error('redirect_uri') is-invalid @enderror"
                                   value="{{ old('redirect_uri', $credential->redirect_uri) }}">
                            @error('redirect_uri')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="auth_method" class="form-label">Auth Method</label>
                            <select name="auth_method" id="auth_method" class="form-select">
                                <option value="jwt" {{ ($credential->auth_method ?? '') === 'jwt' ? 'selected' : '' }}>JWT Bearer Flow</option>
                                <option value="client_credentials" {{ ($credential->auth_method ?? '') === 'client_credentials' ? 'selected' : '' }}>Client Credentials</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fal fa-save me-1"></i> Update Credentials
                            </button>
                            <a href="{{ route('salesforce-credentials.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                        <button type="button" class="btn btn-outline-info" id="test-connection">
                            <i class="fal fa-plug me-1"></i> Test Connection
                        </button>
                    </div>
                </form>

                <div id="connection-result" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var testBtn = document.getElementById('test-connection');
    var resultBox = document.getElementById('connection-result');

    if (testBtn) {
        testBtn.addEventListener('click', function () {
            var form = document.getElementById('credential-form');
            var data = new FormData(form);

            testBtn.disabled = true;
            testBtn.innerHTML = '<i class="fal fa-spinner fa-spin me-1"></i> Testing...';
            resultBox.innerHTML = '';

            fetch("{{ route('salesforce-credentials.test') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: data
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
@endsection
