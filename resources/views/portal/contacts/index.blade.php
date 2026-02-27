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
                        <th class="text-center">Portal Access</th>
                        <th>Portal Role</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($contacts as $c)
                    <tr>
                        <td class="fw-500">{{ $c->full_name }}</td>
                        <td>{{ $c->email }}</td>
                        <td>{{ $c->title ?? '—' }}</td>
                        <td class="text-center">
                            @if($c->id === $contact->id)
                                <span class="badge bg-success">You</span>
                            @else
                                <form action="{{ route('portal.contacts.toggle-access', $c) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-{{ $c->portal_access ? 'success' : 'outline-secondary' }} rounded-pill px-3" style="font-size: 0.75rem;">
                                        {{ $c->portal_access ? 'Enabled' : 'Disabled' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td>
                            @if($c->id === $contact->id)
                                <span class="badge bg-primary">{{ $c->role?->display_name ?? '—' }}</span>
                            @else
                                <form action="{{ route('portal.contacts.update-role', $c) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="portal_role" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto; font-size: 0.8rem;">
                                        <option value="">— None —</option>
                                        @foreach(\App\Models\Role::portal()->orderBy('sort_order')->get() as $portalRole)
                                            <option value="{{ $portalRole->slug }}" {{ $c->portal_role_id === $portalRole->id ? 'selected' : '' }}>{{ $portalRole->display_name }}</option>
                                        @endforeach
                                    </select>
                                </form>
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
                    <tr><td colspan="6" class="text-muted text-center py-3">No contacts found.</td></tr>
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
                { select: 3, sortable: false },
                { select: 4, sortable: false },
                { select: 5, sortable: false }
            ]
        });
    }
});
</script>
@endsection
