@extends('layouts.vertical', ['pageTitle' => 'Add Salesforce Credentials'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Add Salesforce Credentials', 'pageSubTitle1' => 'System'])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Connector Configuration (JWT Bearer Flow)</div>
            </div>
            <div class="card-body">
                <p class="text-muted fs-sm mb-4">
                    Configure the Salesforce Connected App credentials using <code>JWT Bearer Flow</code>.
                    Ensure your Connected App is set to use JWT and that the public key is uploaded.
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

                <form id="credential-form" method="POST" action="{{ route('salesforce-credentials.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="client_id" class="form-label">Salesforce Client ID <span class="text-danger">*</span></label>
                            <input type="text" name="client_id" id="client_id"
                                   class="form-control @error('client_id') is-invalid @enderror"
                                   value="{{ old('client_id') }}" required>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Salesforce Username <span class="text-danger">*</span></label>
                            <input type="email" name="username" id="username"
                                   class="form-control @error('username') is-invalid @enderror"
                                   value="{{ old('username') }}" required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">The Salesforce user email that authorized the Connected App</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="instance_url" class="form-label">Instance URL <span class="text-danger">*</span></label>
                            <input type="url" name="instance_url" id="instance_url"
                                   class="form-control @error('instance_url') is-invalid @enderror"
                                   value="{{ old('instance_url', 'https://login.salesforce.com') }}" required>
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
                                   value="{{ old('redirect_uri') }}">
                            @error('redirect_uri')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <input type="hidden" name="auth_method" value="jwt">

                    <hr>

                    <div class="d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fal fa-save me-1"></i> Save Credentials
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
