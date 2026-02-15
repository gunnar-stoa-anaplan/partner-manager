@extends('layouts.vertical', ['pageTitle' => 'Edit Blueprint'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Edit Blueprint', 'pageSubTitle1' => 'Administration', 'pageSubTitle1Url' => route('admin.dashboard'), 'pageSubTitle2' => 'Enablement Blueprints', 'pageSubTitle2Url' => route('blueprints.index'), 'pageSubText' => $blueprint->name])
@endsection

@section('content')
<p class="text-muted fst-italic mt-3 mb-4">Edit the blueprint's metadata, outcomes, and content items. Update the name, category, or description at the top, then manage the outcomes and their associated learning content below. Changes are saved immediately — existing partner assignments retain their progress.</p>

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
                    <div class="accordion" id="outcomesAccordion">
                    @forelse($blueprint->outcomes as $outcome)
                        <div class="accordion-item mb-3 border rounded">
                            <h2 class="accordion-header" id="outcomeHead{{ $outcome->id }}">
                                <div class="d-flex align-items-center bg-light p-2 ps-3">
                                    <span class="badge bg-primary me-2">{{ $outcome->sort_order }}</span>
                                    <button class="btn btn-link text-start text-decoration-none flex-grow-1 p-0 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#outcomeBody{{ $outcome->id }}" aria-expanded="false">
                                        <strong>{{ $outcome->title }}</strong>
                                        <small class="text-muted ms-2">({{ $outcome->contentItems->count() }} items)</small>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success ms-2" onclick="event.stopPropagation(); openAddItemModal({{ $outcome->id }})">
                                        <i class="fal fa-plus me-1"></i> Add Item
                                    </button>
                                    <form action="{{ route('blueprint-outcomes.destroy', $outcome) }}" method="POST" class="d-inline ms-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); return confirm('Delete this outcome and all its content items?')">
                                            <i class="fal fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </h2>
                            <div id="outcomeBody{{ $outcome->id }}" class="accordion-collapse collapse" aria-labelledby="outcomeHead{{ $outcome->id }}" data-bs-parent="#outcomesAccordion">
                            <div class="p-3">
                                @if($outcome->contentItems->count())
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead>
                                                <tr>
                                                    <th style="width:30px">#</th>
                                                    <th>Content</th>
                                                    <th>Time</th>
                                                    <th>Source</th>
                                                    <th>Prerequisites</th>
                                                    <th class="text-center" style="width:50px">GTM</th>
                                                    <th class="text-center" style="width:50px">Pre-S</th>
                                                    <th class="text-center" style="width:50px">Del</th>
                                                    <th style="width:70px"></th>
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
                                                        <td class="text-nowrap">
                                                            <button type="button" class="btn btn-xs btn-outline-primary me-1"
                                                                onclick="openEditItemModal(this)"
                                                                data-id="{{ $item->id }}"
                                                                data-title="{{ e($item->title) }}"
                                                                data-url="{{ e($item->url) }}"
                                                                data-duration-display="{{ e($item->duration_display) }}"
                                                                data-duration-minutes="{{ $item->duration_minutes }}"
                                                                data-source="{{ e($item->source) }}"
                                                                data-prerequisites="{{ e($item->prerequisites) }}"
                                                                data-gtm="{{ $item->gtm_applicable }}"
                                                                data-pre-sales="{{ $item->pre_sales_applicable }}"
                                                                data-delivery="{{ $item->delivery_applicable }}">
                                                                <i class="fal fa-pencil"></i>
                                                            </button>
                                                            <form action="{{ route('blueprint-content-items.destroy', $item) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-xs btn-outline-danger" onclick="return confirm('Delete this content item?')">
                                                                    <i class="fal fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No content items yet. <a href="javascript:void(0)" onclick="openAddItemModal({{ $outcome->id }})">Add one</a>.</p>
                                @endif
                            </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No outcomes defined. Click "Add Outcome" to get started.</p>
                    @endforelse
                    </div>
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

    {{-- Edit Content Item Modal --}}
    <div class="modal fade" id="editItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editItemForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Content Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="editTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" name="url" id="editUrl" placeholder="https://...">
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration Display</label>
                                <input type="text" class="form-control" name="duration_display" id="editDurationDisplay" placeholder="e.g. 1 hour">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" name="duration_minutes" id="editDurationMinutes" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Source</label>
                                <select class="form-select" name="source" id="editSource">
                                    <option value="">None</option>
                                    @foreach(\App\Models\BlueprintContentItem::SOURCES as $src)
                                        <option value="{{ $src }}">{{ $src }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prerequisites</label>
                            <input type="text" class="form-control" name="prerequisites" id="editPrerequisites" placeholder="e.g. Complete Module 1 first">
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">GTM (Sales)</label>
                                <select class="form-select" name="gtm_applicable" id="editGtm">
                                    <option value="na">N/A</option>
                                    <option value="required">Required</option>
                                    <option value="optional">Optional</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pre-Sales</label>
                                <select class="form-select" name="pre_sales_applicable" id="editPreSales">
                                    <option value="na">N/A</option>
                                    <option value="required">Required</option>
                                    <option value="optional">Optional</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Delivery</label>
                                <select class="form-select" name="delivery_applicable" id="editDelivery">
                                    <option value="na">N/A</option>
                                    <option value="required">Required</option>
                                    <option value="optional">Optional</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add Content Item Modal --}}
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addItemForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Content Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" name="url" placeholder="https://...">
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration Display</label>
                                <input type="text" class="form-control" name="duration_display" placeholder="e.g. 1 hour">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" name="duration_minutes" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Source</label>
                                <select class="form-select" name="source">
                                    <option value="">None</option>
                                    @foreach(\App\Models\BlueprintContentItem::SOURCES as $src)
                                        <option value="{{ $src }}">{{ $src }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prerequisites</label>
                            <input type="text" class="form-control" name="prerequisites" placeholder="e.g. Complete Module 1 first">
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">GTM (Sales)</label>
                                <select class="form-select" name="gtm_applicable">
                                    <option value="na">N/A</option>
                                    <option value="required">Required</option>
                                    <option value="optional">Optional</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pre-Sales</label>
                                <select class="form-select" name="pre_sales_applicable">
                                    <option value="na">N/A</option>
                                    <option value="required">Required</option>
                                    <option value="optional">Optional</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Delivery</label>
                                <select class="form-select" name="delivery_applicable">
                                    <option value="na">N/A</option>
                                    <option value="required">Required</option>
                                    <option value="optional">Optional</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Base URL for content item routes (no /admin prefix — routes are under admin middleware but not prefixed)
    const contentItemBaseUrl = "{{ url('/blueprint-content-items') }}";
    const outcomeItemBaseUrl = "{{ url('/blueprint-outcomes') }}";

    function openEditItemModal(btn) {
        const form = document.getElementById('editItemForm');
        form.action = contentItemBaseUrl + '/' + btn.dataset.id;

        document.getElementById('editTitle').value = btn.dataset.title || '';
        document.getElementById('editUrl').value = btn.dataset.url || '';
        document.getElementById('editDurationDisplay').value = btn.dataset.durationDisplay || '';
        document.getElementById('editDurationMinutes').value = btn.dataset.durationMinutes || '';
        document.getElementById('editSource').value = btn.dataset.source || '';
        document.getElementById('editPrerequisites').value = btn.dataset.prerequisites || '';
        document.getElementById('editGtm').value = btn.dataset.gtm || 'na';
        document.getElementById('editPreSales').value = btn.dataset.preSales || 'na';
        document.getElementById('editDelivery').value = btn.dataset.delivery || 'na';

        new bootstrap.Modal(document.getElementById('editItemModal')).show();
    }

    function openAddItemModal(outcomeId) {
        const form = document.getElementById('addItemForm');
        form.action = outcomeItemBaseUrl + '/' + outcomeId + '/items';

        // Clear all fields
        form.querySelectorAll('input[type="text"], input[type="number"]').forEach(el => el.value = '');
        form.querySelectorAll('select').forEach(el => {
            if (el.name.includes('applicable')) {
                el.value = 'na';
            } else {
                el.selectedIndex = 0;
            }
        });

        new bootstrap.Modal(document.getElementById('addItemModal')).show();
    }
</script>
@endsection
