@extends('portal.layout', ['pageTitle' => 'Team'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Team — {{ $partner->name }}</h4>
    <a href="{{ route('portal.contacts.create') }}" class="btn btn-primary btn-sm">Add Contact</a>
</div>

<div class="card portal-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="teamTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Title</th>
                        <th>Portal</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($contacts as $c)
                    <tr>
                        <td class="fw-500">{{ $c->full_name }}</td>
                        <td>{{ $c->email }}</td>
                        <td>{{ $c->title ?? '—' }}</td>
                        <td>
                            @if($c->portal_access)
                                <span class="badge bg-success">{{ \App\Models\Contact::PORTAL_ROLES[$c->portal_role] ?? 'Active' }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('portal.contacts.edit', $c) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @if($c->id !== $contact->id)
                                <form action="{{ route('portal.contacts.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove {{ $c->full_name }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-3">No contacts found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@9/dist/style.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@9" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('teamTable');
    if (table && table.querySelector('tbody tr td:not([colspan])')) {
        new simpleDatatables.DataTable(table, {
            perPage: 15,
            perPageSelect: [10, 15, 25, 50],
            searchable: true,
            sortable: true,
            labels: {
                placeholder: "Search team...",
                noRows: "No contacts found",
                info: "Showing {start} to {end} of {rows} contacts",
            },
            columns: [
                { select: 4, sortable: false }
            ]
        });
    }
});
</script>
@endsection
