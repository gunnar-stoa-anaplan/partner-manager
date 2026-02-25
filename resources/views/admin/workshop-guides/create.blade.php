@extends('layouts.vertical', ['pageTitle' => 'Create Workshop Guide'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Create Workshop Guide', 'pageSubTitle1' => 'Workshop Guides', 'pageSubTitle1Url' => route('admin.workshop-guides.index')])
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-0">
            <div class="card-body">
                <form action="{{ route('admin.workshop-guides.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product_area" class="form-label">Product Area</label>
                            <select class="form-select @error('product_area') is-invalid @enderror"
                                    id="product_area" name="product_area">
                                <option value="">Select...</option>
                                @foreach(\App\Models\EnablementEvent::PRODUCT_AREAS as $group => $areas)
                                    <optgroup label="{{ $group }}">
                                        @foreach($areas as $key => $label)
                                            <option value="{{ $key }}" @selected(old('product_area') == $key)>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('product_area') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="enablement_event_id" class="form-label">Linked Event</label>
                            <select class="form-select @error('enablement_event_id') is-invalid @enderror"
                                    id="enablement_event_id" name="enablement_event_id">
                                <option value="">None</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" @selected(old('enablement_event_id') == $event->id)>{{ $event->title }}</option>
                                @endforeach
                            </select>
                            @error('enablement_event_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                @foreach(\App\Models\WorkshopGuide::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @selected(old('status', 'draft') == $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="estimated_duration_hours" class="form-label">Duration (hours)</label>
                            <input type="number" step="0.5" min="0" class="form-control @error('estimated_duration_hours') is-invalid @enderror"
                                   id="estimated_duration_hours" name="estimated_duration_hours" value="{{ old('estimated_duration_hours') }}">
                            @error('estimated_duration_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="requires_auth" class="form-label">Requires Login</label>
                            <select class="form-select @error('requires_auth') is-invalid @enderror"
                                    id="requires_auth" name="requires_auth">
                                <option value="0" @selected(old('requires_auth') == '0')>No</option>
                                <option value="1" @selected(old('requires_auth') == '1')>Yes</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="navigation" class="form-label">Navigation JSON <span class="text-danger">*</span></label>
                        <textarea class="form-control font-monospace @error('navigation') is-invalid @enderror"
                                  id="navigation" name="navigation" rows="10" required>{{ old('navigation', json_encode([
                            'Getting Started' => [
                                'Workshop Overview' => 'overview',
                                'Environment Setup' => 'setup',
                            ],
                            'Labs' => [
                                'Lab 1: Foundation' => 'lab-1-foundation',
                            ],
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
                        <div class="form-text">JSON object mapping sections to pages. Keys are section names, values are objects of "Title" => "slug" pairs.</div>
                        @error('navigation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.workshop-guides.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Workshop Guide</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
