@extends('layouts.vertical', ['pageTitle' => 'Salesforce Credentials'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Salesforce Credentials', 'pageSubTitle1' => 'System', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-8">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title">Salesforce Connection</div>
                    @if($credential)
                        <a href="{{ route('salesforce-credentials.edit', $credential) }}" class="btn btn-sm btn-primary">Edit</a>
                    @else
                        <a href="{{ route('salesforce-credentials.create') }}" class="btn btn-sm btn-primary">Add Credentials</a>
                    @endif
                </div>
                <div class="card-body">
                    @if($credential)
                        <p class="text-muted fs-sm mb-4">The active Salesforce Connected App used for authentication (JWT Bearer Flow). The client secret is encrypted and never displayed.</p>
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-muted">Auth method</dt>
                            <dd class="col-sm-8">{{ strtoupper($credential->auth_method ?? 'jwt') }}</dd>

                            <dt class="col-sm-4 text-muted">Client ID</dt>
                            <dd class="col-sm-8 text-break">{{ $credential->client_id ?? '—' }}</dd>

                            <dt class="col-sm-4 text-muted">Username</dt>
                            <dd class="col-sm-8">{{ $credential->username ?? '—' }}</dd>

                            <dt class="col-sm-4 text-muted">Instance URL</dt>
                            <dd class="col-sm-8 text-break">{{ $credential->instance_url ?? '—' }}</dd>

                            <dt class="col-sm-4 text-muted">Redirect URI</dt>
                            <dd class="col-sm-8 text-break">{{ $credential->redirect_uri ?? '—' }}</dd>

                            <dt class="col-sm-4 text-muted">Client secret</dt>
                            <dd class="col-sm-8"><span class="badge bg-secondary">encrypted</span></dd>
                        </dl>
                    @else
                        <p class="text-muted fst-italic mb-0">No Salesforce credentials configured yet. Add a Connected App to enable the Salesforce integration.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
