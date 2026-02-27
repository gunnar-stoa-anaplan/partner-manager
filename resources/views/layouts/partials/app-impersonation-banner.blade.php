@auth
@if(session('impersonating_user'))
    <div class="alert alert-warning d-flex align-items-center mb-0 rounded-0 border-start-0 border-end-0 border-top-0" role="alert" style="border-bottom: 2px solid var(--bs-warning);">
        <svg class="sa-icon me-2 flex-shrink-0" style="width: 20px; height: 20px;">
            <use href="/icons/sprite.svg#eye"></use>
        </svg>
        <div class="flex-grow-1">
            <strong>Impersonation Mode</strong> &mdash; Viewing as
            <strong>{{ auth()->user()->name }}</strong>
            ({{ auth()->user()->role?->display_name ?? 'No Role' }})
            &middot; started by {{ session('impersonator_name', 'Admin') }}
        </div>
        <form action="{{ route('impersonate.stop') }}" method="POST" class="d-inline ms-3 flex-shrink-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-dark fw-bold">Stop Impersonation</button>
        </form>
    </div>
@endif
@endauth
