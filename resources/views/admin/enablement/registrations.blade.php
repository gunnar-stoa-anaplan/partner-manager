@extends('layouts.vertical', ['pageTitle' => 'Session Registrations'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle'=> 'Registrations',
        'pageSubTitle1' => $session->display_title,
        'pageSubText' => 'Manage attendee registrations for this session'
    ])
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Session Info Card --}}
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-0 mb-g">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Event</small>
                            <a href="{{ route('enablement-events.show', $session->event->id) }}">
                                <strong>{{ $session->event->title }}</strong>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Date & Time</small>
                            <strong>
                                {{ $session->start_at->format('M d, Y') }}
                                @if(!$session->all_day)
                                    {{ $session->start_at->format('g:i A') }}
                                @endif
                            </strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Location</small>
                            <strong>{{ $session->location ?? 'TBD' }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Instructor</small>
                            <strong>{{ $session->instructor ?? '—' }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Spots</small>
                            <strong>
                                {{ $session->registrations->whereNotIn('status', ['cancelled'])->count() }}
                                @if($session->effective_max_participants)
                                    / {{ $session->effective_max_participants }}
                                @endif
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Registrations Table --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">
                        <i class="fal fa-users me-2"></i> Registrations ({{ $session->registrations->count() }})
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped w-100" id="registrations-table">
                            <thead>
                                <tr>
                                    <th>Contact</th>
                                    <th>Partner</th>
                                    <th>Nominated By</th>
                                    <th>Status</th>
                                    <th>Nominated</th>
                                    <th>Confirmed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($session->registrations as $registration)
                                    <tr>
                                        <td>
                                            <strong>{{ $registration->contact->first_name }} {{ $registration->contact->last_name }}</strong>
                                            @if($registration->contact->email)
                                                <br><small class="text-muted">{{ $registration->contact->email }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $registration->partner->name ?? '—' }}</td>
                                        <td>{{ $registration->nominator->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $registration->status_color }}">
                                                {{ $registration->status_label }}
                                            </span>
                                        </td>
                                        <td class="text-nowrap">{{ $registration->nominated_at?->format('M d, Y') }}</td>
                                        <td class="text-nowrap">{{ $registration->confirmed_at?->format('M d, Y') ?? '—' }}</td>
                                        <td class="text-nowrap">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Update Status
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @foreach(\App\Models\EnablementRegistration::STATUSES as $statusKey => $statusInfo)
                                                        @if($statusKey !== $registration->status)
                                                            <li>
                                                                <form action="{{ route('enablement-registrations.update', $registration->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="status" value="{{ $statusKey }}">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <span class="badge bg-{{ $statusInfo['color'] }} me-1">{{ $statusInfo['label'] }}</span>
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fal fa-users fa-2x mb-2 d-block opacity-50"></i>
                                                No registrations yet. PSMs can nominate contacts from the Enablement Calendar.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/table-init.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.SmartTables && document.querySelector('#registrations-table tbody tr td:not([colspan])')) {
                new window.SmartTables('registrations-table', {
                    perPage: 25,
                    responsive: {
                        enabled: true,
                        breakpoint: 768,
                        columnPriorities: { 0: 1, 3: 2, 1: 3, 2: 4, 4: 5, 5: 6, 6: 7 }
                    }
                });
            }
        });
    </script>
@endsection
