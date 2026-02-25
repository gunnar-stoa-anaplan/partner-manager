@extends('layouts.vertical', ['pageTitle' => $guide->title])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> $guide->title, 'pageSubTitle1' => 'Workshop Guides', 'pageSubTitle1Url' => route('admin.workshop-guides.index')])
@endsection

@section('content')

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

    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <div>
            <span class="badge" style="background-color: {{ \App\Models\WorkshopGuide::STATUS_COLORS[$guide->status] ?? '#868e96' }}">
                {{ \App\Models\WorkshopGuide::STATUSES[$guide->status] ?? $guide->status }}
            </span>
            @if($guide->product_area)
                <span class="badge bg-secondary ms-1">{{ $guide->product_area }}</span>
            @endif
            @if($guide->estimated_duration_hours)
                <span class="text-muted ms-2 small">{{ $guide->estimated_duration_hours }}h estimated</span>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if($guide->isPublished())
                <a href="{{ route('workshop-guides.show', $guide->slug) }}" class="btn btn-outline-success btn-sm" target="_blank">
                    <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#external-link"></use></svg>
                    View Public
                </a>
            @endif
            @if($guide->isDraft())
                <form action="{{ route('admin.workshop-guides.publish', $guide) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">Publish</button>
                </form>
            @endif
            <a href="{{ route('admin.workshop-guides.edit', $guide) }}" class="btn btn-outline-primary btn-sm">Edit</a>
            @unless($guide->isPublished())
                <form action="{{ route('admin.workshop-guides.destroy', $guide) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this workshop guide?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
            @endunless
        </div>
    </div>

    {{-- Details --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-0 mb-3">
                <div class="card-header fw-500">Description</div>
                <div class="card-body">
                    <p class="mb-0">{{ $guide->description }}</p>
                </div>
            </div>

            {{-- Pages --}}
            <div class="card shadow-0 mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-500">Pages ({{ $guide->pages->count() }})</span>
                </div>
                <div class="card-body p-0">
                    @if($guide->pages->isEmpty())
                        <div class="text-center text-muted py-4">No pages yet. Add pages below or generate with AI.</div>
                    @else
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Slug</th>
                                    <th>Section</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($guide->pages as $page)
                                    <tr>
                                        <td>{{ $page->sort_order + 1 }}</td>
                                        <td class="fw-500">{{ $page->title }}</td>
                                        <td class="font-monospace small">{{ $page->slug }}</td>
                                        <td>{{ $page->section }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            {{-- Add Page --}}
            <div class="card shadow-0 mb-3">
                <div class="card-header fw-500">Add Page</div>
                <div class="card-body">
                    <form action="{{ route('admin.workshop-guides.pages.store', $guide) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="page_title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="page_title" name="title" required>
                            </div>
                            <div class="col-md-4">
                                <label for="page_slug" class="form-label">Slug</label>
                                <input type="text" class="form-control font-monospace" id="page_slug" name="slug" required pattern="[a-z0-9\-]+">
                            </div>
                            <div class="col-md-4">
                                <label for="page_section" class="form-label">Section</label>
                                <input type="text" class="form-control" id="page_section" name="section" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="page_content" class="form-label">Markdown Content</label>
                            <textarea class="form-control font-monospace" id="page_content" name="content" rows="8" placeholder="# Page Title&#10;&#10;Write markdown content here..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Add Page</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Missing Images --}}
            @if(count($missingImages) > 0)
                <div class="card shadow-0 mb-3 border-warning">
                    <div class="card-header fw-500 text-warning">Missing Images ({{ count($missingImages) }})</div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($missingImages as $image)
                                <li class="list-group-item small font-monospace">{{ $image }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Upload Image --}}
            <div class="card shadow-0 mb-3">
                <div class="card-header fw-500">Upload Image</div>
                <div class="card-body">
                    <form action="{{ route('admin.workshop-guides.images.store', $guide) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Upload</button>
                    </form>
                </div>
            </div>

            {{-- Navigation Preview --}}
            <div class="card shadow-0 mb-3">
                <div class="card-header fw-500">Navigation Structure</div>
                <div class="card-body">
                    @foreach($guide->navigation ?? [] as $section => $pages)
                        @if(is_array($pages))
                            <h6 class="text-uppercase small fw-bold text-muted mb-1">{{ $section }}</h6>
                            <ul class="list-unstyled mb-3">
                                @foreach($pages as $title => $slug)
                                    <li class="small mb-1">
                                        <span class="text-muted">{{ $loop->iteration }}.</span>
                                        {{ $title }}
                                        <code class="small">{{ $slug }}</code>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Meta --}}
            <div class="card shadow-0">
                <div class="card-header fw-500">Details</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Created by</dt>
                        <dd class="col-7">{{ $guide->creator->name ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Created</dt>
                        <dd class="col-7">{{ $guide->created_at->format('M d, Y') }}</dd>
                        @if($guide->published_at)
                            <dt class="col-5 text-muted">Published</dt>
                            <dd class="col-7">{{ $guide->published_at->format('M d, Y') }}</dd>
                        @endif
                        <dt class="col-5 text-muted">Auth Required</dt>
                        <dd class="col-7">{{ $guide->requires_auth ? 'Yes' : 'No' }}</dd>
                        @if($guide->enablementEvent)
                            <dt class="col-5 text-muted">Linked Event</dt>
                            <dd class="col-7">{{ $guide->enablementEvent->title }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
