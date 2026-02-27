@extends('layouts.vertical', ['pageTitle' => 'Workshop Guides'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Workshop Guides', 'pageSubTitle1' => 'Partner Enablement', 'pageSubTitle1Url' => route('enablement.calendar'), 'pageSubText' => 'Manage web-based workshop guides for participants'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Create and manage browser-based workshop guides with step-by-step instructions, screenshots, and interactive labs.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div></div>
        <a href="{{ route('admin.workshop-guides.create') }}" class="btn btn-primary btn-sm">
            <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#plus"></use></svg>
            New Workshop Guide
        </a>
    </div>

    <div class="card shadow-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Product Area</th>
                        <th>Status</th>
                        <th class="text-center">Pages</th>
                        <th>Duration</th>
                        <th>Created By</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guides as $guide)
                        <tr>
                            <td>
                                <a href="{{ route('admin.workshop-guides.show', $guide) }}" class="fw-500">
                                    {{ $guide->title }}
                                </a>
                                <div class="small text-muted">/workshops/{{ $guide->slug }}</div>
                            </td>
                            <td>{{ $guide->product_area ?? '—' }}</td>
                            <td>
                                <span class="badge" style="background-color: {{ \App\Models\WorkshopGuide::STATUS_COLORS[$guide->status] ?? '#868e96' }}">
                                    {{ \App\Models\WorkshopGuide::STATUSES[$guide->status] ?? $guide->status }}
                                </span>
                            </td>
                            <td class="text-center">{{ $guide->pages_count }}</td>
                            <td>{{ $guide->estimated_duration_hours ? $guide->estimated_duration_hours . 'h' : '—' }}</td>
                            <td>{{ $guide->creator->name ?? '—' }}</td>
                            <td class="text-end">
                                @if($guide->isPublished())
                                    <a href="{{ route('workshop-guides.show', $guide->slug) }}" class="btn btn-xs btn-outline-success" title="View" target="_blank">
                                        <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#external-link"></use></svg>
                                    </a>
                                @endif
                                <a href="{{ route('admin.workshop-guides.edit', $guide) }}" class="btn btn-xs btn-outline-primary" title="Edit">
                                    <svg class="sa-icon sa-icon-sm"><use href="/icons/sprite.svg#edit"></use></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No workshop guides yet. <a href="{{ route('admin.workshop-guides.create') }}">Create one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
