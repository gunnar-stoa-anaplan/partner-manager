@auth
@if(app()->environment('local'))
    @php
        $roleOrder = ['super_admin', 'admin', 'psm', 'manager', 'coe', 'integration'];
        $devUsers = \App\Models\User::query()
            ->orderBy('name')
            ->get()
            ->groupBy('role')
            ->sortBy(fn ($group, $role) => array_search($role, $roleOrder));
        $currentRole = auth()->user()->role;
        $isImpersonating = session('impersonating_user');
    @endphp

    <div class="dropdown d-inline-flex align-items-center">
        <button type="button"
                class="btn btn-sm fw-bold border d-flex align-items-center gap-1 px-2 py-1"
                data-bs-toggle="dropdown" aria-expanded="false"
                style="font-size: 11px; background: {{ $isImpersonating ? '#fff3cd' : '#e8f5e9' }}; border-color: {{ $isImpersonating ? '#ffc107' : '#81c784' }};">
            <svg class="sa-icon" style="width: 14px; height: 14px;">
                <use href="/icons/sprite.svg#users"></use>
            </svg>
            <span class="text-uppercase">{{ $currentRole }}</span>
        </button>

        <div class="dropdown-menu dropdown-menu-end shadow" style="min-width: 240px;">
            <div class="px-3 py-1">
                <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;">Dev Role Switcher</small>
            </div>
            <div class="dropdown-divider m-0"></div>

            @if($isImpersonating)
                <form action="{{ route('impersonate.stop') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger fw-bold py-2">
                        <svg class="sa-icon me-1" style="width: 14px; height: 14px;">
                            <use href="/icons/sprite.svg#log-out"></use>
                        </svg>
                        Stop Impersonation
                    </button>
                </form>
                <div class="dropdown-divider m-0"></div>
            @endif

            @foreach($devUsers as $role => $users)
                <div class="px-3 py-1 bg-light">
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 10px;">{{ $role }}</small>
                </div>
                @foreach($users as $user)
                    @if($user->id === auth()->id())
                        <div class="dropdown-item disabled d-flex align-items-center py-1" style="font-size: 12px;">
                            <img src="{{ $user->avatar_url }}" class="rounded-circle me-2" width="20" height="20" alt="">
                            <div>
                                <div class="fw-bold">{{ $user->name }}</div>
                                <div class="text-muted" style="font-size: 10px;">{{ $user->email }} (current)</div>
                            </div>
                        </div>
                    @else
                        <form action="{{ route('impersonate.start', $user) }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center py-1" style="font-size: 12px;">
                                <img src="{{ $user->avatar_url }}" class="rounded-circle me-2" width="20" height="20" alt="">
                                <div>
                                    <div class="fw-bold">{{ $user->name }}</div>
                                    <div class="text-muted" style="font-size: 10px;">{{ $user->email }}</div>
                                </div>
                            </button>
                        </form>
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>
@endif
@endauth
