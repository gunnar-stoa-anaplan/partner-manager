@extends('layouts.vertical', ['pageTitle' => 'Product Bundles'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Product Bundles',
        'pageSubTitle1' => 'Feature Activation',
        'pageSubTitle1Url' => route('admin.activation.flags'),
        'pageSubText' => 'View and manage product sections, navigation items, and partner detail tabs',
    ])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter bar --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="border-faded bg-faded p-3 mb-g d-flex">
                <div class="position-relative flex-grow-1">
                    <input class="form-control shadow-inset-2 form-control-lg" id="js-filter-products"
                           placeholder="Filter products by name, slug, or group" type="text" />
                    <button class="btn btn-sm btn-danger position-absolute top-50 end-0 translate-middle-y me-2 d-none"
                            id="js-clear-filter" type="button">
                        <i class="fal fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="fs-sm text-muted mb-g" id="js-filter-counter"></div>
        </div>
    </div>

    @foreach($groups as $groupName => $groupProducts)
    <h5 class="text-muted fw-500 text-uppercase fs-sm mb-3 mt-4">{{ ucfirst($groupName) }}</h5>
    <div class="row" id="js-products-{{ $groupName }}">
        @foreach($groupProducts as $product)
        @php $module = $product['module']; @endphp
        <div class="col-lg-6 col-xl-4"
             data-filter-tags="{{ strtolower($module->name . ' ' . $module->slug . ' ' . $module->group) }}">
            <div class="card border shadow-0 mb-g shadow-sm-hover">
                <div class="card-body">
                    <div class="d-flex flex-row align-items-center">
                        <div class="rounded-circle bg-body-secondary d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:40px;height:40px">
                            <svg class="sa-icon" style="width:20px;height:20px">
                                <use href="/icons/sprite.svg#{{ $module->icon }}"></use>
                            </svg>
                        </div>
                        <div class="info-card-text flex-grow-1">
                            <a href="{{ route('admin.products.show', $module) }}" class="fs-lg text-truncate text-truncate-lg d-block fw-500">
                                {{ $module->name }}
                            </a>
                            <span class="d-block text-truncate text-truncate-lg text-muted">
                                <code class="small">{{ $module->slug }}</code>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body border-top p-3">
                    <div class="d-flex gap-3 small text-muted">
                        <span>
                            <i class="fal fa-th-list me-1"></i>
                            {{ $product['section_count'] }} {{ Str::plural('section', $product['section_count']) }}
                        </span>
                        <span>
                            <i class="fal fa-link me-1"></i>
                            {{ $product['item_count'] }} {{ Str::plural('item', $product['item_count']) }}
                        </span>
                        @if($product['extends_count'] > 0)
                        <span>
                            <i class="fal fa-plug me-1"></i>
                            {{ $product['extends_count'] }} {{ Str::plural('extension', $product['extends_count']) }}
                        </span>
                        @endif
                    </div>

                    @if($module->description)
                    <p class="text-muted small mt-2 mb-0">{{ Str::limit($module->description, 100) }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var filterInput = document.getElementById('js-filter-products');
    var clearBtn    = document.getElementById('js-clear-filter');
    var counter     = document.getElementById('js-filter-counter');

    function applyFilter() {
        var term    = filterInput.value.toLowerCase().trim();
        var columns = document.querySelectorAll('[data-filter-tags]');
        var visible = 0;
        var total   = columns.length;

        columns.forEach(function (col) {
            var tags = col.getAttribute('data-filter-tags') || '';
            if (!term || tags.indexOf(term) !== -1) {
                col.style.display = '';
                visible++;
            } else {
                col.style.display = 'none';
            }
        });

        // Hide empty group headings
        document.querySelectorAll('h5.text-uppercase').forEach(function (heading) {
            var row = heading.nextElementSibling;
            if (row) {
                var visibleCards = row.querySelectorAll('[data-filter-tags]:not([style*="display: none"])');
                heading.style.display = visibleCards.length ? '' : 'none';
            }
        });

        if (term) {
            counter.textContent = 'Showing ' + visible + ' of ' + total + ' products';
            filterInput.classList.add('border-primary');
            clearBtn.classList.remove('d-none');
        } else {
            counter.textContent = '';
            filterInput.classList.remove('border-primary');
            clearBtn.classList.add('d-none');
        }
    }

    filterInput.addEventListener('input', applyFilter);

    clearBtn.addEventListener('click', function (e) {
        e.preventDefault();
        filterInput.value = '';
        document.querySelectorAll('[data-filter-tags]').forEach(function (col) {
            col.style.display = '';
        });
        document.querySelectorAll('h5.text-uppercase').forEach(function (h) {
            h.style.display = '';
        });
        counter.textContent = '';
        filterInput.classList.remove('border-primary');
        clearBtn.classList.add('d-none');
    });

    filterInput.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            this.value = '';
            clearBtn.click();
        }
    });
});
</script>
@endsection
