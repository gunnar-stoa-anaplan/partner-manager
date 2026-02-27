@auth
@inject('licenseStatus', 'App\Services\LicenseStatusService')
@php
    $licenseWarnings = $licenseStatus->getWarningMessages();
@endphp
@foreach($licenseWarnings as $warning)
    @php
        $alertClass = match($warning['level']) {
            'danger' => 'alert-danger',
            'warning' => 'alert-warning',
            default => 'alert-info',
        };
        $borderVar = match($warning['level']) {
            'danger' => 'var(--bs-danger)',
            'warning' => 'var(--bs-warning)',
            default => 'var(--bs-info)',
        };
        $icon = match($warning['level']) {
            'danger' => 'alert-triangle',
            'warning' => 'alert-circle',
            default => 'info',
        };
    @endphp
    <div class="alert {{ $alertClass }} alert-dismissible d-flex align-items-center mb-0 rounded-0 border-start-0 border-end-0 border-top-0" role="alert" style="border-bottom: 2px solid {{ $borderVar }};">
        <svg class="sa-icon sa-icon-2x me-2 flex-shrink-0" style="width: 20px; height: 20px;">
            <use href="/icons/sprite.svg#{{ $icon }}"></use>
        </svg>
        <div class="flex-grow-1">
            {{ $warning['message'] }}
        </div>
        <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endforeach
@endauth
