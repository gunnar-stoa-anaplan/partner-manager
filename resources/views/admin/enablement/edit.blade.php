@extends('layouts.vertical', ['pageTitle' => 'Edit Enablement Event'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Edit Event', 'pageSubTitle1' => 'Partner Enablement', 'pageSubTitle1Url' => route('enablement.calendar'), 'pageSubTitle2' => 'Events', 'pageSubTitle2Url' => route('enablement-events.index'), 'pageSubText' => 'Update enablement event details'])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Update this event's details, including type, category, target audience, and capacity. Changes apply to the catalog entry — existing scheduled sessions are not affected. To manage sessions or registrations, visit the event detail page after saving.</p>

    <div class="row">
        <div class="col-xl-8 offset-xl-2">
            <div class="card shadow-0 mb-g">
                <div class="card-header">
                    <div class="card-title">Edit: {{ $event->title }}</div>
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

                    <form action="{{ route('enablement-events.update', $event->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="title">Event Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $event->title) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select Type...</option>
                                    @foreach(\App\Models\EnablementEvent::TYPES as $value => $label)
                                        <option value="{{ $value }}" {{ old('type', $event->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="category">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">Select Category...</option>
                                    @foreach(\App\Models\EnablementEvent::CATEGORIES as $cat)
                                        <option value="{{ $cat }}" {{ old('category', $event->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="product_area">Product Area</label>
                                <select class="form-select" id="product_area" name="product_area">
                                    <option value="">Select Product Area...</option>
                                    @foreach(\App\Models\EnablementEvent::PRODUCT_AREAS as $group => $areas)
                                        <optgroup label="{{ $group }}">
                                            @foreach($areas as $value => $label)
                                                <option value="{{ $value }}" {{ old('product_area', $event->product_area) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    @foreach(\App\Models\EnablementEvent::STATUSES as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', $event->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Only published events are visible to PSMs.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $event->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="objectives">Learning Objectives</label>
                            <textarea class="form-control" id="objectives" name="objectives" rows="2">{{ old('objectives', $event->objectives) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="prerequisites">Prerequisites</label>
                            <textarea class="form-control" id="prerequisites" name="prerequisites" rows="2">{{ old('prerequisites', $event->prerequisites) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="target_audience">Target Audience</label>
                                <input type="text" class="form-control" id="target_audience" name="target_audience" value="{{ old('target_audience', $event->target_audience) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="duration_hours">Duration (hours)</label>
                                <input type="number" class="form-control" id="duration_hours" name="duration_hours" value="{{ old('duration_hours', $event->duration_hours) }}" step="0.5" min="0">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="max_participants">Max Participants</label>
                                <input type="number" class="form-control" id="max_participants" name="max_participants" value="{{ old('max_participants', $event->max_participants) }}" min="1">
                                <div class="form-text">Per session (can be overridden).</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="materials_url">Materials URL</label>
                            <input type="url" class="form-control" id="materials_url" name="materials_url" value="{{ old('materials_url', $event->materials_url) }}">
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('enablement-events.show', $event->id) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <svg class="sa-icon sa-icon-sm me-1"><use href="/icons/sprite.svg#save"></use></svg>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
