@auth
@if(auth()->user()->isAdmin())
    @inject('versionCheck', 'App\Services\VersionCheckService')
    @php
        $availableUpdate = $versionCheck->getAvailableUpdate();
        $updateAccepted = $versionCheck->isUpdateAccepted();
    @endphp
    @if($availableUpdate && !$updateAccepted)
        <div class="alert alert-info alert-dismissible d-flex align-items-center mb-0 rounded-0 border-start-0 border-end-0 border-top-0" role="alert" style="border-bottom: 2px solid var(--bs-info);">
            <svg class="sa-icon sa-icon-2x me-2 flex-shrink-0" style="width: 20px; height: 20px;">
                <use href="/icons/sprite.svg#download-cloud"></use>
            </svg>
            <div class="flex-grow-1">
                <strong>Version {{ $availableUpdate['version'] }} is available.</strong>
                {{ $availableUpdate['summary'] ?? '' }}
            </div>
            <a href="{{ route('admin.update') }}" class="btn btn-sm btn-info ms-3 flex-shrink-0">Review Update</a>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif($updateAccepted)
        <div class="alert alert-success d-flex align-items-center mb-0 rounded-0 border-start-0 border-end-0 border-top-0" role="alert" style="border-bottom: 2px solid var(--bs-success);">
            <svg class="sa-icon sa-icon-2x me-2 flex-shrink-0" style="width: 20px; height: 20px;">
                <use href="/icons/sprite.svg#refresh-cw"></use>
            </svg>
            <div class="flex-grow-1">
                <strong>Update accepted.</strong> The new version will be deployed shortly. This page will reload automatically.
            </div>
        </div>
        <script>setTimeout(function() { location.reload(); }, 30000);</script>
    @endif
@endif
@endauth
