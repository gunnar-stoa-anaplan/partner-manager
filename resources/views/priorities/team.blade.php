@extends('layouts.vertical', ['pageTitle' => 'Team Overview'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Team Overview',
        'pageSubTitle1' => 'Partner Management',
        'pageSubTitle2' => 'Priorities',
        'pageSubTitle2Url' => route('priorities.index'),
    ])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#users"></use></svg>
                Direct Reports
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>PSM</th>
                            <th class="text-center">Partners</th>
                            <th class="text-center">Active Objectives</th>
                            <th class="text-center">Completed</th>
                            <th class="text-center">Items This Week</th>
                            <th class="text-center">Avg Health</th>
                            <th class="text-center">At Risk</th>
                            <th class="text-center">Last Review</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teamData as $member)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $member['user']->avatar_url }}" class="rounded-circle me-2"
                                             width="32" height="32" alt="{{ $member['user']->name }}">
                                        <div>
                                            <span class="fw-500">{{ $member['user']->name }}</span>
                                            <br><small class="text-muted">{{ $member['user']->role?->display_name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ $member['partners_count'] }}</td>
                                <td class="text-center">{{ $member['objectives_active'] }}</td>
                                <td class="text-center">
                                    <span class="text-success">{{ $member['objectives_completed'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-500">{{ $member['items_done'] }}</span>
                                    <span class="text-muted">/{{ $member['items_total'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $member['avg_health'] >= 70 ? 'success' : ($member['avg_health'] >= 50 ? 'warning' : 'danger') }}">
                                        {{ number_format($member['avg_health'], 0) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($member['at_risk_count'] > 0)
                                        <span class="badge bg-danger">{{ $member['at_risk_count'] }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($member['last_review'])
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($member['last_review'])->diffForHumans() }}</small>
                                    @else
                                        <small class="text-muted">Never</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('priorities.team.member', $member['user']) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#eye"></use></svg>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No direct reports found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
