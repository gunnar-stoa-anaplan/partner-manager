@extends('portal.layout', ['pageTitle' => 'Team'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Team — {{ $partner->name }}</h4>
    <a href="{{ route('portal.contacts.create') }}" class="btn btn-primary btn-sm">Add Contact</a>
</div>

<div class="card portal-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Title</th><th>Type</th><th>Role</th><th>Portal</th></tr></thead>
                <tbody>
                @forelse($contacts as $c)
                    <tr>
                        <td class="fw-500">{{ $c->full_name }}</td>
                        <td>{{ $c->email }}</td>
                        <td>{{ $c->title ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ \App\Models\Contact::CONTACT_TYPES[$c->contact_type] ?? $c->contact_type }}</span></td>
                        <td>
                            @if($c->contact_type === 'resource') {{ \App\Models\Contact::RESOURCE_ROLES[$c->resource_role] ?? '—' }}
                            @elseif($c->contact_type === 'influencer') {{ \App\Models\Contact::INFLUENCER_ROLES[$c->influencer_role] ?? '—' }}
                            @else —
                            @endif
                        </td>
                        <td>
                            @if($c->portal_access)
                                <span class="badge bg-success">{{ \App\Models\Contact::PORTAL_ROLES[$c->portal_role] ?? 'Active' }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-3">No contacts found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
