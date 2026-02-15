@extends('layouts.vertical', ['pageTitle' => 'Launch Programs'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Launch Programs', 'pageSubTitle1' => 'Partner Enablement', 'pageSubTitle1Url' => route('admin.launch-programs.index'), 'pageSubText' => 'Manage product launch partner programs'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Create and manage launch partner programs that give selected partners early access to new products, dedicated enablement, and pre-release environments. Track partner enrollment, milestone completion, and feedback across all programs.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div></div>
        <a href="{{ route('admin.launch-programs.create') }}" class="btn btn-primary btn-sm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
            New Launch Program
        </a>
    </div>

    <div class="card shadow-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Product</th>
                        <th>Status</th>
                        <th class="text-center">Partners</th>
                        <th class="text-center">Milestones</th>
                        <th>GA Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programs as $program)
                        <tr>
                            <td>
                                <a href="{{ route('admin.launch-programs.show', $program) }}" class="fw-500">
                                    {{ $program->name }}
                                </a>
                            </td>
                            <td>{{ $program->product->name ?? '—' }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ \App\Models\LaunchProgram::STATUS_COLORS[$program->status] ?? '#868e96' }}">
                                    {{ \App\Models\LaunchProgram::STATUSES[$program->status] ?? $program->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                {{ $program->program_partners_count }}
                                @if($program->max_partners)
                                    <span class="text-muted">/ {{ $program->max_partners }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $program->milestones_count }}</td>
                            <td>{{ $program->target_ga_date?->format('M j, Y') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.launch-programs.edit', $program) }}" class="btn btn-xs btn-outline-primary" title="Edit">
                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit"></use></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No launch programs yet. <a href="{{ route('admin.launch-programs.create') }}">Create one</a>.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
