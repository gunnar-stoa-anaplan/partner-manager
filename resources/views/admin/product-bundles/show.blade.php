@extends('layouts.vertical', ['pageTitle' => $module->name . ' — Product Bundle'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => $module->name,
        'pageSubTitle1' => 'Product Bundles',
        'pageSubTitle1Url' => route('admin.products.index'),
        'pageSubText' => 'Manage sections, navigation items, and partner detail tabs',
    ])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Product Header --}}
    <div class="card border shadow-0 mb-g">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-body-secondary d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px">
                    <svg class="sa-icon" style="width:24px;height:24px">
                        <use href="/icons/sprite.svg#{{ $module->icon }}"></use>
                    </svg>
                </div>
                <div>
                    <h4 class="mb-0">{{ $module->name }}</h4>
                    <span class="text-muted">
                        <code>{{ $module->slug }}</code>
                        <span class="badge bg-body-secondary text-muted ms-2">{{ ucfirst($module->group) }}</span>
                        <span class="badge {{ $module->is_active ? 'bg-success' : 'bg-secondary' }} ms-1">{{ $module->is_active ? 'Active' : 'Inactive' }}</span>
                    </span>
                </div>
            </div>
            @if($module->description)
            <p class="text-muted mt-2 mb-0">{{ $module->description }}</p>
            @endif
        </div>
    </div>

    {{-- Sections --}}
    <h5 class="fw-500 mb-3">Sections <span class="badge bg-body-secondary text-muted">{{ count($sections) }}</span></h5>

    <div class="accordion" id="sectionsAccordion">
        @foreach($sections as $sectionKey => $section)
        @php $sectionId = 'section-' . Str::slug($sectionKey); @endphp
        <div class="card border shadow-0 mb-2">
            <div class="card-header p-0" id="heading-{{ $sectionId }}">
                <div class="d-flex align-items-center p-3">
                    <button class="btn btn-link text-start p-0 text-decoration-none flex-grow-1 d-flex align-items-center collapsed"
                            type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $sectionId }}" aria-expanded="false">
                        <svg class="sa-icon sa-icon-sm me-2 flex-shrink-0">
                            <use href="/icons/sprite.svg#{{ $section['icon'] }}"></use>
                        </svg>
                        <span class="fw-500">{{ $section['label'] }}</span>
                        <code class="ms-2 small text-muted">{{ $sectionKey }}</code>
                        <span class="badge bg-body-secondary text-muted ms-2">{{ count($section['items'] ?? []) }} {{ Str::plural('item', count($section['items'] ?? [])) }}</span>
                    </button>
                    <form method="POST" action="{{ route('admin.products.sections.destroy', [$module, $sectionKey]) }}"
                          class="ms-2" onsubmit="return confirm('Remove this section and all its items?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove section">
                            <i class="fal fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div id="collapse-{{ $sectionId }}" class="collapse" data-bs-parent="#sectionsAccordion">
                <div class="card-body border-top">

                    {{-- Section properties --}}
                    <form method="POST" action="{{ route('admin.products.sections.update', [$module, $sectionKey]) }}" class="mb-4">
                        @csrf @method('PUT')
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small text-muted">Label</label>
                                <input type="text" class="form-control form-control-sm" name="label" value="{{ $section['label'] }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Icon</label>
                                <input type="text" class="form-control form-control-sm" name="icon" value="{{ $section['icon'] }}" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Update Section</button>
                            </div>
                        </div>
                    </form>

                    {{-- Items table --}}
                    @if(count($section['items'] ?? []) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-3">
                            <thead>
                                <tr class="text-muted small">
                                    <th style="width:30px">#</th>
                                    <th style="width:30px"></th>
                                    <th>Label</th>
                                    <th>Route</th>
                                    <th>Icon</th>
                                    <th style="width:140px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($section['items'] as $itemIndex => $item)
                                <tr>
                                    <form method="POST" action="{{ route('admin.products.items.update', [$module, $sectionKey, $itemIndex]) }}">
                                        @csrf @method('PUT')
                                        <td class="text-muted small align-middle">{{ $itemIndex + 1 }}</td>
                                        <td class="align-middle">
                                            <svg class="sa-icon sa-icon-xs text-muted">
                                                <use href="/icons/sprite.svg#{{ $item['icon'] }}"></use>
                                            </svg>
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm" name="label" value="{{ $item['label'] }}" required></td>
                                        <td><input type="text" class="form-control form-control-sm font-monospace" name="route" value="{{ $item['route'] }}" required></td>
                                        <td><input type="text" class="form-control form-control-sm" name="icon" value="{{ $item['icon'] }}" required></td>
                                        <td class="text-end">
                                            <button type="submit" class="btn btn-xs btn-outline-primary" title="Save">
                                                <i class="fal fa-check"></i>
                                            </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.products.items.destroy', [$module, $sectionKey, $itemIndex]) }}" class="d-inline"
                                          onsubmit="return confirm('Remove this item?')">
                                        @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Remove">
                                                <i class="fal fa-times"></i>
                                            </button>
                                    </form>
                                        </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted small fst-italic mb-3">No navigation items in this section.</p>
                    @endif

                    {{-- Add item form --}}
                    <form method="POST" action="{{ route('admin.products.items.store', [$module, $sectionKey]) }}">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">New Item Label</label>
                                <input type="text" class="form-control form-control-sm" name="label" placeholder="e.g. Dashboard" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Route Name</label>
                                <input type="text" class="form-control form-control-sm font-monospace" name="route" placeholder="e.g. module.index" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted">Icon</label>
                                <input type="text" class="form-control form-control-sm" name="icon" placeholder="e.g. home" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                    <i class="fal fa-plus me-1"></i> Add
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Add section form --}}
    <div class="card border shadow-0 mb-g mt-3">
        <div class="card-header">
            <div class="card-title">Add Section</div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.sections.store', $module) }}">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Section Key</label>
                        <input type="text" class="form-control form-control-sm font-monospace" name="key" placeholder="e.g. reporting" required pattern="^[a-z_]+$" title="Lowercase letters and underscores only">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Label</label>
                        <input type="text" class="form-control form-control-sm" name="label" placeholder="e.g. Reporting" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Icon</label>
                        <input type="text" class="form-control form-control-sm" name="icon" placeholder="e.g. bar-chart" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fal fa-plus me-1"></i> Add Section
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Extensions (read-only summary) --}}
    @if(count($extends) > 0)
    <h5 class="fw-500 mb-3 mt-4">Extensions <span class="badge bg-body-secondary text-muted">{{ count($extends) }}</span></h5>
    <div class="card border shadow-0 mb-g">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>Target</th>
                            <th>Type</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($extends as $target => $extension)
                        <tr>
                            <td><code>{{ $target }}</code></td>
                            <td>
                                @if(isset($extension['tabs']))
                                    <span class="badge bg-info bg-opacity-10 text-info">Tabs ({{ count($extension['tabs']) }})</span>
                                @endif
                                @if(isset($extension['items']))
                                    <span class="badge bg-primary bg-opacity-10 text-primary">Items ({{ count($extension['items']) }})</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($extension['tabs']))
                                    @foreach($extension['tabs'] as $tab)
                                        <span class="badge bg-body-secondary text-muted me-1">{{ $tab['label'] }}</span>
                                    @endforeach
                                @endif
                                @if(isset($extension['items']))
                                    @foreach($extension['items'] as $item)
                                        <span class="badge bg-body-secondary text-muted me-1">{{ $item['label'] }}</span>
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

@endsection
