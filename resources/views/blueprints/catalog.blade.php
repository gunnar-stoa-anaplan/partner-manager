@extends('layouts.vertical', ['pageTitle' => 'Blueprint Assignments'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Blueprint Assignments', 'pageSubTitle1' => 'Blueprints', 'pageSubText' => 'Browse and assign partner readiness blueprints'])
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @foreach($grouped as $category => $categoryBlueprints)
        <div class="row mb-2">
            <div class="col-12">
                <h4 class="fw-500 mb-3">
                    @php $catIcons = ['application' => 'cubes', 'product' => 'box', 'delivery' => 'rocket']; @endphp
                    <i class="fal fa-{{ $catIcons[$category] ?? 'layer-group' }} me-2 text-primary"></i>
                    {{ \App\Models\Blueprint::CATEGORIES[$category] ?? ucfirst($category) }}
                </h4>
            </div>
        </div>
        <div class="row mb-g">
            @foreach($categoryBlueprints as $blueprint)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <div class="card shadow-0 h-100 border">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start mb-2">
                                <h5 class="card-title mb-0 flex-grow-1">
                                    <a href="{{ route('blueprints.detail', $blueprint) }}" class="text-dark">
                                        {{ $blueprint->name }}
                                    </a>
                                </h5>
                                @if($blueprint->version)
                                    <small class="text-muted ms-2">{{ $blueprint->version }}</small>
                                @endif
                            </div>

                            @if($blueprint->product_area)
                                <span class="badge bg-light text-dark border mb-2 align-self-start">{{ $blueprint->product_area }}</span>
                            @endif

                            @if($blueprint->description)
                                <p class="text-muted small mb-3 flex-grow-1">{{ Str::limit($blueprint->description, 120) }}</p>
                            @else
                                <div class="flex-grow-1"></div>
                            @endif

                            <div class="d-flex justify-content-between text-muted small mb-3">
                                <span><i class="fal fa-list-ol me-1"></i> {{ $blueprint->outcomes_count }} outcomes</span>
                                <span><i class="fal fa-book me-1"></i> {{ $blueprint->outcomes->sum(fn($o) => $o->contentItems->count()) }} items</span>
                            </div>

                            <a href="{{ route('blueprints.detail', $blueprint) }}" class="btn btn-sm btn-outline-primary w-100">
                                View Blueprint
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    @if($blueprints->isEmpty())
        <div class="row">
            <div class="col-12">
                <div class="card shadow-0">
                    <div class="card-body text-center py-5">
                        <div class="mb-3"><i class="fal fa-layer-group fa-3x text-muted opacity-50"></i></div>
                        <h5 class="text-muted">No blueprints available</h5>
                        <p class="text-muted">Enablement blueprints will appear here once published by an administrator.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
@endsection
