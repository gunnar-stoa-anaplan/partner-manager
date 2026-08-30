@extends('layouts.vertical', ['pageTitle' => 'Credential Refresh Queue'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Refresh Queue', 'pageSubTitle1' => 'Workshops', 'pageSubTitle1Url' => route('workshops.dashboard'), 'pageSubText' => 'Credentials never refreshed, or refreshed more than 30 days ago, across every workshop'])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card shadow-0 mb-g">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Workshop</th>
                            <th>Username</th>
                            <th>Last Refreshed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($credentials as $credential)
                            <tr>
                                <td><a href="{{ route('workshops.credentials.index', $credential->workshop) }}">{{ $credential->workshop->name }}</a></td>
                                <td>{{ $credential->username }}</td>
                                <td>{{ $credential->last_refreshed_at?->format('M j, Y') ?? 'Never' }}</td>
                                <td>
                                    <form action="{{ route('workshops.credentials.mark-refreshed', $credential) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">Mark Refreshed</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Nothing needs refreshing right now.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
