@extends('layouts.vertical', ['pageTitle' => 'Feature Flags'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Feature Flags', 'pageSubTitle1' => 'Feature Activation', 'pageSubText' => 'Toggle modules on or off via Pennant feature flags'])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        @foreach($features as $feature)
        <div class="col-lg-6 col-xl-4">
            <div class="card shadow-0 mb-g">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-body-secondary d-flex align-items-center justify-content-center" style="width:40px;height:40px">
                                <svg class="sa-icon" style="width:20px;height:20px">
                                    <use href="/icons/sprite.svg#{{ $feature['icon'] }}"></use>
                                </svg>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $feature['name'] }}</h6>
                                <code class="small text-muted">{{ $feature['slug'] }}</code>
                            </div>
                        </div>
                        <span class="badge {{ $feature['effective'] ? 'bg-success' : 'bg-secondary' }}">
                            {{ $feature['effective'] ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <p class="text-muted small mb-3">{{ $feature['description'] }}</p>

                    <div class="d-flex flex-column gap-1 small mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Env Override</span>
                            <span class="{{ $feature['env_enabled'] ? 'text-success' : 'text-danger' }}">
                                {{ $feature['env_enabled'] ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Pennant Flag</span>
                            <span class="{{ $feature['pennant_active'] ? 'text-success' : 'text-danger' }}">
                                {{ $feature['pennant_active'] ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    @if($feature['env_enabled'])
                    <form method="POST" action="{{ route('admin.activation.toggle') }}">
                        @csrf
                        <input type="hidden" name="slug" value="{{ $feature['slug'] }}">
                        <input type="hidden" name="active" value="{{ $feature['pennant_active'] ? '0' : '1' }}">
                        <button type="submit" class="btn btn-sm w-100 {{ $feature['pennant_active'] ? 'btn-outline-danger' : 'btn-outline-success' }}">
                            {{ $feature['pennant_active'] ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    @else
                    <div class="text-center small text-muted py-1">
                        Disabled via environment variable
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

@endsection
