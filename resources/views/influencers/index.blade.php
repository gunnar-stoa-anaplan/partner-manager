@extends('layouts.vertical', ['pageTitle' => 'Influencer Directory'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Influencer Directory', 'pageSubTitle1' => 'Field Enablement', 'pageSubTitle1Url' => route('influencers.index')])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Track key influencers across your partner portfolio — client account leads, technical leads, architects, and specialists. Log informal interactions like webcasts, lunch-and-learns, product updates, and dinners to build deeper partner relationships.</p>

{{-- Stats Row --}}
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 text-primary">{{ $stats['total'] }}</h2>
                <small class="text-muted">Total Influencers</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 text-success">{{ $stats['interactions_this_month'] }}</h2>
                <small class="text-muted">Interactions This Month</small>
            </div>
        </div>
    </div>
    @foreach($stats['by_role']->take(2) as $role => $count)
    <div class="col-md-3">
        <div class="card shadow-0 mb-g">
            <div class="card-body text-center py-3">
                <h2 class="fw-300 m-0 text-info">{{ $count }}</h2>
                <small class="text-muted">{{ \App\Models\Contact::INFLUENCER_ROLES[$role] ?? ucfirst(str_replace('_', ' ', $role)) }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Actions --}}
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('influencers.create') }}" class="btn btn-sm btn-primary">
        <i class="fal fa-plus me-1"></i> Add Influencer
    </a>
</div>

{{-- Influencer Table --}}
<div class="card shadow-0 mb-g">
    <div class="card-body">
        @if($influencers->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fal fa-users fa-3x mb-3 opacity-50"></i>
                <p>No influencers tracked yet.</p>
                <a href="{{ route('influencers.create') }}" class="btn btn-sm btn-outline-primary">Add Your First Influencer</a>
            </div>
        @else
            <table class="table table-hover table-sm" id="influencers-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Partner</th>
                        <th>Role</th>
                        <th>Title</th>
                        <th>Organization</th>
                        <th class="text-center">Interactions</th>
                        <th>Last Interaction</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($influencers as $inf)
                    <tr>
                        <td>
                            <a href="{{ route('influencers.show', $inf) }}" class="fw-500">
                                {{ $inf->first_name }} {{ $inf->last_name }}
                            </a>
                        </td>
                        <td>{{ $inf->partner->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-primary bg-opacity-25 text-primary">
                                {{ \App\Models\Contact::INFLUENCER_ROLES[$inf->influencer_role] ?? $inf->influencer_role }}
                            </span>
                        </td>
                        <td>{{ $inf->title ?? '-' }}</td>
                        <td>{{ $inf->organization ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $inf->interactions_count > 0 ? 'bg-success' : 'bg-secondary' }} bg-opacity-25 {{ $inf->interactions_count > 0 ? 'text-success' : 'text-secondary' }}">
                                {{ $inf->interactions_count }}
                            </span>
                        </td>
                        <td>
                            @php $last = $inf->interactions->first(); @endphp
                            {{ $last ? $last->interaction_date->diffForHumans() : 'Never' }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('influencers.show', $inf) }}" class="btn btn-xs btn-outline-info" title="View">
                                <i class="fal fa-eye"></i>
                            </a>
                            <a href="{{ route('influencers.edit', $inf) }}" class="btn btn-xs btn-outline-primary" title="Edit">
                                <i class="fal fa-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.SmartTables && document.querySelector('#influencers-table tbody tr td:not([colspan])')) {
        new window.SmartTables('influencers-table', { perPage: 25, search: true, pagination: true });
    }
});
</script>
@endsection
