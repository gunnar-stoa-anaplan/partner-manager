@extends('layouts.vertical', ['pageTitle' => 'Idea Room — Gallery Themes'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Gallery Themes',
        'pageSubTitle1' => 'Idea Room',
        'pageSubTitle1Url' => route('idea_room.dashboard'),
    ])
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

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Manage gallery wall themes for the Idea Room landing page.</p>
    <a href="{{ route('idea_room.admin.themes.create') }}" class="btn btn-primary">
        <i class="fal fa-plus me-1"></i> Create Theme
    </a>
</div>

<div class="row">
    @forelse($themes as $theme)
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card shadow-0 h-100 {{ $theme->trashed() ? 'opacity-50' : '' }}">
                <div class="position-relative">
                    @if($theme->background_image_url)
                        <img src="{{ $theme->background_image_url }}" alt="{{ $theme->name }}"
                             class="card-img-top" style="height: 140px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center"
                             style="height: 140px;">
                            <i class="fal fa-image text-white fa-2x"></i>
                        </div>
                    @endif

                    {{-- Badges --}}
                    <div class="position-absolute top-0 end-0 p-2 d-flex gap-1">
                        @if($theme->is_default)
                            <span class="badge bg-warning text-dark">Default</span>
                        @endif
                        @if($theme->is_published)
                            <span class="badge bg-success">Published</span>
                        @else
                            <span class="badge bg-secondary">Draft</span>
                        @endif
                    </div>

                    {{-- Frame count --}}
                    <div class="position-absolute bottom-0 start-0 p-2">
                        <span class="badge bg-dark bg-opacity-75">{{ $theme->max_frames }} frames</span>
                    </div>
                </div>

                <div class="card-body pb-2">
                    <h6 class="card-title mb-1">{{ $theme->name }}</h6>
                    <small class="text-muted">{{ $theme->slug }}</small>
                    @if($theme->description)
                        <p class="small text-muted mt-1 mb-0">{{ Str::limit($theme->description, 80) }}</p>
                    @endif
                </div>

                <div class="card-footer bg-transparent border-0 pt-0 d-flex gap-2 flex-wrap">
                    <a href="{{ route('idea_room.admin.themes.edit', $theme) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fal fa-pen me-1"></i> Edit
                    </a>

                    @if(!$theme->is_default)
                        <form action="{{ route('idea_room.admin.themes.set-default', $theme) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                <i class="fal fa-star me-1"></i> Set Default
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('idea_room.admin.themes.toggle-publish', $theme) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-{{ $theme->is_published ? 'secondary' : 'success' }}">
                            {{ $theme->is_published ? 'Unpublish' : 'Publish' }}
                        </button>
                    </form>

                    @if(!$theme->is_default)
                        <form action="{{ route('idea_room.admin.themes.destroy', $theme) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this theme?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fal fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card shadow-0">
                <div class="card-body text-center py-5 text-muted">
                    <i class="fal fa-image fa-3x mb-3 d-block"></i>
                    No gallery themes yet. Create one to get started.
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection
