@if(session('feature_unlocked'))
@php
    $unlockedSlug = session('feature_unlocked');
    $registry = app(\App\Services\ModuleRegistry::class);
    $modules = $registry->allModules();
    $unlockedModule = $modules[$unlockedSlug] ?? null;
@endphp
@if($unlockedModule)
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1090">
    <div class="toast show border-success" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="8000">
        <div class="toast-header bg-success text-white">
            <svg class="sa-icon me-2" style="width: 1rem; height: 1rem; filter: brightness(0) invert(1);">
                <use href="/icons/sprite.svg#{{ $unlockedModule['icon'] }}"></use>
            </svg>
            <strong class="me-auto">Feature Unlocked!</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            <strong>{{ $unlockedModule['name'] }}</strong> is now fully available.
            <div class="mt-2">
                <a href="{{ route('welcome') }}" class="btn btn-sm btn-outline-success">Explore Now</a>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toastEl = document.querySelector('.toast[data-bs-autohide]');
    if (toastEl && typeof bootstrap !== 'undefined') {
        new bootstrap.Toast(toastEl).show();
    }
});
</script>
@endif
@endif
