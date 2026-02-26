@extends('layouts.vertical', ['pageTitle' => 'API Tokens'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'API Tokens', 'pageSubTitle1' => 'System', 'pageSubTitle1Url' => route('admin.dashboard')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">API Tokens control external system access to the Partner Manager REST API. Each token is scoped with specific abilities — import data, read partners, or access the enablement calendar. Create tokens for integrations like Salesforce exports, PMO tools, or portal publishing, and revoke them when no longer needed.</p>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('new_token'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>New Token Created</strong> — Copy this token now. It will not be shown again.
        <div class="mt-2">
            <code class="d-block p-2 bg-dark text-light rounded user-select-all" style="word-break: break-all;">{{ session('new_token') }}</code>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    {{-- Token List --}}
    <div class="col-xl-8">
        <div class="card shadow-0 mb-g">
            <div class="card-header d-flex align-items-center">
                <div class="card-title flex-grow-1">Active Tokens</div>
                <span class="badge bg-primary">{{ $tokens->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>User</th>
                                <th>Abilities</th>
                                <th>Last Used</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tokens as $token)
                            <tr>
                                <td class="fw-500">{{ $token->name }}</td>
                                <td>
                                    @if($token->tokenable)
                                        {{ $token->tokenable->name }}
                                        <small class="d-block text-muted">{{ $token->tokenable->email }}</small>
                                    @else
                                        <span class="text-muted">Deleted user</span>
                                    @endif
                                </td>
                                <td>
                                    @foreach($token->abilities as $ability)
                                        <span class="badge bg-info bg-opacity-25 text-info me-1 mb-1">{{ $ability }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($token->last_used_at)
                                        <span title="{{ $token->last_used_at->format('M d, Y H:i:s') }}">
                                            {{ $token->last_used_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td>{{ $token->created_at->format('M d, Y') }}</td>
                                <td>
                                    <form action="{{ route('admin.api-tokens.destroy', $token->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Revoke this token? Any integrations using it will stop working.')" title="Revoke Token">
                                            <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#trash-2"></use></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No API tokens created yet. Use the form to create your first token.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Token Form --}}
    <div class="col-xl-4">
        <div class="card shadow-0 mb-g">
            <div class="card-header">
                <div class="card-title">Create Token</div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.api-tokens.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="user_id" class="form-label">User</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            <option value="">Select user...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role?->display_name ?? 'No Role' }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Token Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}"
                               placeholder="e.g. Salesforce Integration" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">A descriptive name for this integration</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Abilities (Scopes)</label>
                        @error('abilities')
                            <div class="text-danger small mb-1">{{ $message }}</div>
                        @enderror
                        @foreach($abilities as $key => $label)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="abilities[]"
                                       value="{{ $key }}" id="ability_{{ Str::slug($key) }}"
                                       {{ is_array(old('abilities')) && in_array($key, old('abilities')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="ability_{{ Str::slug($key) }}">
                                    <code class="me-1">{{ $key }}</code>
                                    <small class="d-block text-muted">{{ $label }}</small>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#key"></use></svg>
                        Generate Token
                    </button>
                </form>

                <hr>

                <div class="mt-3">
                    <h6 class="text-muted mb-2">API Documentation</h6>
                    <p class="text-muted fs-sm">Interactive API documentation is available for all authenticated users.</p>
                    <a href="/docs/api" class="btn btn-outline-info btn-sm w-100" target="_blank">
                        <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#book-open"></use></svg>
                        View API Docs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
