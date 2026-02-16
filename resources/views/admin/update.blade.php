@extends('layouts.vertical', ['pageTitle' => 'System Update'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'System Update', 'pageSubTitle1' => 'Administration', 'pageSubText' => 'Review and accept available updates'])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            {{-- Version Comparison --}}
            <div class="card mb-g">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="text-muted fw-normal mb-1">Current Version</h5>
                            <h2 class="mb-0">v{{ $currentVersion }}</h2>
                        </div>
                        <div class="px-4">
                            <i class="fal fa-arrow-right fa-2x text-muted"></i>
                        </div>
                        <div>
                            <h5 class="text-muted fw-normal mb-1">Available Version</h5>
                            <h2 class="mb-0 text-info">v{{ $update['version'] }}</h2>
                        </div>
                    </div>

                    <div class="row text-center border-top pt-3">
                        <div class="col-4">
                            <span class="text-muted d-block mb-1">Type</span>
                            <span class="badge bg-{{ $update['type'] === 'upgrade' ? 'warning' : ($update['type'] === 'patch' ? 'success' : 'info') }} fs-sm">
                                {{ ucfirst($update['type'] ?? 'update') }}
                            </span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block mb-1">Released</span>
                            <strong>{{ $update['released'] ?? 'N/A' }}</strong>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block mb-1">Status</span>
                            @if($isAccepted)
                                <span class="badge bg-success fs-sm">Accepted — Deploying</span>
                            @else
                                <span class="badge bg-secondary fs-sm">Pending Review</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Release Summary --}}
            <div class="card mb-g">
                <div class="card-header">
                    <h4 class="card-title mb-0">Release Summary</h4>
                </div>
                <div class="card-body">
                    <p class="fs-md">{{ $update['summary'] ?? 'No summary available.' }}</p>

                    @if(!empty($update['changelog']))
                        <h6 class="mt-4 mb-3">What's Changed</h6>
                        <ul class="list-unstyled">
                            @foreach($update['changelog'] as $change)
                                <li class="d-flex align-items-start mb-2">
                                    <i class="fal fa-check-circle text-success me-2 mt-1"></i>
                                    <span>{{ $change }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if(!empty($update['notes_url']))
                        <a href="{{ $update['notes_url'] }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm mt-3">
                            <i class="fal fa-external-link me-1"></i> Full Release Notes
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Accept / Status Panel --}}
            <div class="card mb-g">
                <div class="card-header">
                    <h4 class="card-title mb-0">Deploy Update</h4>
                </div>
                <div class="card-body">
                    @if($isAccepted)
                        <div class="text-center py-3">
                            <div class="spinner-border text-success mb-3" role="status">
                                <span class="visually-hidden">Deploying...</span>
                            </div>
                            <p class="fw-500 mb-1">Update Accepted</p>
                            <p class="text-muted fs-sm">The agent will deploy v{{ $update['version'] }} within the next 60 seconds. This page will reload automatically.</p>
                        </div>
                        <script>setTimeout(function() { location.reload(); }, 30000);</script>
                    @else
                        <p class="text-muted fs-sm mb-3">
                            Accepting this update will instruct the agent to deploy version {{ $update['version'] }}.
                            The application will be briefly unavailable (5–15 seconds) during the container restart.
                        </p>

                        <div class="alert alert-warning fs-sm mb-3">
                            <strong>Note:</strong> This action cannot be undone from this interface.
                            Contact your MyService operator if you need to roll back after deployment.
                        </div>

                        <form action="{{ route('admin.update.accept') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Accept and deploy version {{ $update['version'] }}? The application will restart briefly.')">
                                <i class="fal fa-cloud-download me-1"></i> Accept & Deploy v{{ $update['version'] }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- What Happens Next --}}
            <div class="card mb-g">
                <div class="card-header">
                    <h4 class="card-title mb-0">What Happens Next</h4>
                </div>
                <div class="card-body">
                    <ol class="list-group list-group-numbered list-group-flush fs-sm">
                        <li class="list-group-item border-0 px-0">Agent picks up the acceptance on next heartbeat (≤30s)</li>
                        <li class="list-group-item border-0 px-0">Agent verifies the image signature (Ed25519)</li>
                        <li class="list-group-item border-0 px-0">Agent pulls the new image from the registry</li>
                        <li class="list-group-item border-0 px-0">Application container restarts (5–15s downtime)</li>
                        <li class="list-group-item border-0 px-0">Database migrations run automatically</li>
                        <li class="list-group-item border-0 px-0">This page reloads on the new version</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

@endsection
