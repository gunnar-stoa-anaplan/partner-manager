@extends('portal.layout', ['pageTitle' => 'Deal Registrations'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Deal Registrations</h2>
    <a href="{{ route('portal.deal-registrations.create') }}" class="btn btn-primary">Register New Deal</a>
</div>

<div class="card portal-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Deal Name</th>
                        <th>Customer</th>
                        <th class="text-end">Est. Value</th>
                        <th>Product Area</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Expires</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($deals as $deal)
                    <tr>
                        <td class="fw-500">{{ $deal->deal_name }}</td>
                        <td>{{ $deal->customer_name }}</td>
                        <td class="text-end">${{ number_format($deal->estimated_value) }}</td>
                        <td>{{ $deal->product_area ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ \App\Models\DealRegistration::STATUS_COLORS[$deal->status] ?? 'secondary' }}">
                                {{ \App\Models\DealRegistration::STATUSES[$deal->status] ?? $deal->status }}
                            </span>
                        </td>
                        <td>{{ $deal->submitted_at?->format('M d, Y') ?? '—' }}</td>
                        <td>{{ $deal->expires_at?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No deal registrations found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{ $deals->links() }}
@endsection
