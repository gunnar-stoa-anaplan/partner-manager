{{-- Portal Users Tab: Manage portal access, roles, passwords, and impersonation --}}

{{-- KPI Cards --}}
<div class="row mb-g">
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Total Contacts</p>
                    <span class="h3 mb-0 fw-500">{{ $contacts->count() }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-users fa-lg text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Portal Users</p>
                    <span class="h3 mb-0 fw-500">{{ $contacts->where('portal_access', true)->count() }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-user-shield fa-lg text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">Logged In (30d)</p>
                    <span class="h3 mb-0 fw-500">{{ $contacts->where('portal_access', true)->filter(fn($c) => $c->last_login_at && $c->last_login_at->greaterThan(now()->subDays(30)))->count() }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-sign-in fa-lg text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-g mb-xl-0">
        <div class="card shadow-0 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-muted mb-1 small">No Password Set</p>
                    @php $noPassword = $contacts->where('portal_access', true)->filter(fn($c) => !$c->password)->count(); @endphp
                    <span class="h3 mb-0 fw-500 {{ $noPassword > 0 ? 'text-warning' : '' }}">{{ $noPassword }}</span>
                </div>
                <div class="width-4 height-4 rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center ms-3">
                    <i class="fal fa-key fa-lg text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Contacts Table --}}
<div class="card shadow-0 mb-g">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Partner Contacts</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('partners.portal-users.template') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fal fa-download me-1"></i> Template
            </a>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#importContactsModal">
                <i class="fal fa-file-import me-1"></i> Import
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Title</th>
                        <th class="text-center">Portal Access</th>
                        <th>Portal Role</th>
                        <th>Last Login</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
                    <tr>
                        <td class="fw-500">
                            @if($contact->avatar)
                                <img src="{{ asset($contact->avatar) }}" class="rounded-circle me-1" width="24" height="24" alt="">
                            @endif
                            {{ $contact->full_name }}
                        </td>
                        <td class="text-muted small">{{ $contact->email }}</td>
                        <td class="text-muted small">{{ $contact->title ?? '—' }}</td>
                        <td class="text-center">
                            <form action="{{ route('partners.portal-users.toggle-access', [$partner, $contact]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-{{ $contact->portal_access ? 'success' : 'outline-secondary' }} rounded-pill px-3" style="font-size: 0.75rem;">
                                    {{ $contact->portal_access ? 'Enabled' : 'Disabled' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('partners.portal-users.update-role', [$partner, $contact]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <select name="portal_role" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto; font-size: 0.8rem;">
                                    <option value="">— None —</option>
                                    @foreach(\App\Models\Contact::PORTAL_ROLES as $key => $label)
                                        <option value="{{ $key }}" {{ $contact->portal_role === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="text-muted small text-nowrap">
                            @if($contact->last_login_at)
                                {{ $contact->last_login_at->diffForHumans() }}
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#setPasswordModal{{ $contact->id }}" title="Set Password">
                                <i class="fal fa-key"></i>
                            </button>
                            @if($contact->portal_access && $contact->portal_role)
                                <form action="{{ route('portal.impersonate.start', $contact) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-info" title="Login as {{ $contact->full_name }}">
                                        <i class="fal fa-sign-in"></i> Login As
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No contacts found for this partner.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Set Password Modals --}}
@foreach($contacts as $contact)
<div class="modal fade" id="setPasswordModal{{ $contact->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form action="{{ route('partners.portal-users.set-password', [$partner, $contact]) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h6 class="modal-title">Set Password — {{ $contact->full_name }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small">New Password</label>
                        <input type="password" name="password" class="form-control form-control-sm" required minlength="8">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-sm" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Set Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- Import Contacts Modal --}}
<div class="modal fade" id="importContactsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('partners.portal-users.import', $partner) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title">Import Contacts</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Upload an Excel or CSV file with contact data. Existing contacts (matched by email) will be updated.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-500">File (xlsx, xls, csv — max 5MB)</label>
                        <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="alert alert-light border small p-2 mb-0">
                        <strong>Expected columns:</strong> First Name, Last Name, Email, Phone, Title, Portal Role<br>
                        <a href="{{ route('partners.portal-users.template') }}" class="alert-link">Download template</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
