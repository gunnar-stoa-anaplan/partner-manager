@extends('layouts.vertical', ['pageTitle' => 'Influenced Leads'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Influenced Leads', 'pageSubTitle1' => 'Business Development', 'pageSubTitle1Url' => route('welcome'), 'pageSubText' => 'Leads influenced by partner referrals'])
@endsection

@section('content')
    <div class="card shadow-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Partner</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                        <tr>
                            <td class="fw-500">{{ $lead->name }}</td>
                            <td>{{ $lead->company ?? '—' }}</td>
                            <td>{{ $lead->email ?? '—' }}</td>
                            <td>{{ $lead->partner->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ $lead->status }}</span></td>
                            <td>{{ $lead->created_at->format('M j, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No influenced leads yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
