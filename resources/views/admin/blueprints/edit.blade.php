@extends('layouts.vertical', ['pageTitle' => 'Edit Blueprint'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Edit Blueprint', 'pageSubTitle1' => 'Enablement Blueprints', 'pageSubText' => $blueprint->name])
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Blueprint Details Form --}}
    <div class="row">
        <div class="col-xl-8 offset-xl-2">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Blueprint Details</div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('blueprints.update', $blueprint) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="name">Blueprint Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $blueprint->name) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    @foreach(\App\Models\Blueprint::CATEGORIES as $value => $label)
                                        <option value="{{ $value }}" {{ old('category', $blueprint->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="product_area">Product Area</label>
                                <select class="form-select" id="product_area" name="product_area">
                                    <option value="">Select Area...</option>
                                    @foreach(\App\Models\Blueprint::PRODUCT_AREAS as $area)
                                        <option value="{{ $area }}" {{ old('product_area', $blueprint->product_area) === $area ? 'selected' : '' }}>{{ $area }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="version">Version</label>
                                <input type="text" class="form-control" id="version" name="version" value="{{ old('version', $blueprint->version) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $blueprint->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                @foreach(\App\Models\Blueprint::STATUSES as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $blueprint->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Blueprint</button>
                            <a href="{{ route('blueprints.show', $blueprint) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Outcomes & Content Items --}}
    <div class="row">
        <div class="col-xl-10 offset-xl-1">
            <div class="card shadow-0 mb-g">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title flex-grow-1">Outcomes & Content Items</div>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addOutcomeModal">
                        <i class="fal fa-plus me-1"></i> Add Outcome
                    </button>
                </div>
                <div class="card-body">
                    @forelse($blueprint->outcomes as $outcome)
                        <div class="border rounded mb-3">
                            <div class="d-flex align-items-center p-3 bg-light border-bottom">
                                <span class="badge bg-primary me-2">{{ $outcome->sort_order }}</span>
                                <strong class="flex-grow-1">{{ $outcome->title }}</strong>
                                <form action="{{ route('blueprint-outcomes.destroy', $outcome) }}" method="POST" class="d-inline ms-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this outcome and all its content items?')">
                                        <i class="fal fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="p-3">
                                @if($outcome->contentItems->count())
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Content</th>
                                                    <th>Time</th>
                                                    <th>Source</th>
                                                    <th>Prerequisites</th>
                                                    <th class="text-center" style="width:50px">GTM</th>
                                                    <th class="text-center" style="width:50px">Pre-S</th>
                                                    <th class="text-center" style="width:50px">Del</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($outcome->contentItems as $item)
                                                    <tr>
                                                        <td>{{ $item->sort_order }}</td>
                                                        <td>
                                                            @if($item->url)
                                                                <a href="{{ $item->url }}" target="_blank">{{ $item->title }}</a>
                                                            @else
                                                                {{ $item->title }}
                                                            @endif
                                                        </td>
                                                        <td class="text-nowrap">{{ $item->duration_display ?? '-' }}</td>
                                                        <td>{{ $item->source ?? '-' }}</td>
                                                        <td><small>{{ $item->prerequisites ?? '-' }}</small></td>
                                                        <td class="text-center">
                                                            @if($item->gtm_applicable === 'required')
                                                                <i class="fas fa-check text-success"></i>
                                                            @elseif($item->gtm_applicable === 'optional')
                                                                <i class="fas fa-check text-muted"></i>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if($item->pre_sales_applicable === 'required')
                                                                <i class="fas fa-check text-success"></i>
                                                            @elseif($item->pre_sales_applicable === 'optional')
                                                                <i class="fas fa-check text-muted"></i>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if($item->delivery_applicable === 'required')
                                                                <i class="fas fa-check text-success"></i>
                                                            @elseif($item->delivery_applicable === 'optional')
                                                                <i class="fas fa-check text-muted"></i>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No content items yet.</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No outcomes defined. Click "Add Outcome" to get started.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Add Outcome Modal --}}
    <div class="modal fade" id="addOutcomeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('blueprints.outcomes.store', $blueprint) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Outcome</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" value="{{ $blueprint->outcomes->count() + 1 }}" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required placeholder="e.g. Partners understand App Strategy & Positioning">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Outcome</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
