@extends('layouts.vertical', ['pageTitle' => 'Feature Flags'])

@section('page-title')
    @include('layouts.partials/app-pagetitle', [
        'pageTitle' => 'Feature Flags',
        'pageSubTitle1' => 'Feature Activation',
        'pageSubTitle1Url' => route('admin.activation.paths'),
        'pageSubText' => 'Toggle modules on or off via Pennant feature flags',
    ])
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter bar + view toggle --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="border-faded bg-faded p-3 mb-g d-flex">
                <div class="position-relative flex-grow-1">
                    <input class="form-control shadow-inset-2 form-control-lg" id="js-filter-flags"
                           placeholder="Filter flags by name, slug, description or status" type="text" />
                    <button class="btn btn-sm btn-danger position-absolute top-50 end-0 translate-middle-y me-2 d-none"
                            id="js-clear-filter" type="button">
                        <i class="fal fa-times"></i>
                    </button>
                </div>
                <div aria-label="Switch between grid and list view" class="btn-group btn-group-lg hidden-lg-down ms-3" role="group">
                    <input checked class="btn-check" id="view-grid" name="flagview" type="radio" value="grid" />
                    <label class="btn btn-outline-secondary" for="view-grid"><i class="fal fa-table"></i></label>
                    <input class="btn-check" id="view-list" name="flagview" type="radio" value="list" />
                    <label class="btn btn-outline-secondary" for="view-list"><i class="fal fa-th-list"></i></label>
                </div>
            </div>
            <div class="fs-sm text-muted mb-g" id="js-filter-counter"></div>
        </div>
    </div>

    {{-- Single container: class-toggled between grid and list --}}
    <div class="row" id="js-flags">
        @foreach($features as $feature)
        <div class="col-lg-6 col-xl-4"
             data-filter-tags="{{ strtolower($feature['name'] . ' ' . $feature['slug'] . ' ' . $feature['description'] . ' ' . ($feature['effective'] ? 'active' : 'inactive')) }}">
            <div class="card border shadow-0 mb-g shadow-sm-hover" id="flag_{{ $loop->index }}">
                {{-- Primary row: always visible --}}
                <div class="card-body border-faded border-top-0 border-start-0 border-end-0 rounded-top">
                    <div class="d-flex flex-row align-items-center">
                        <div class="rounded-circle bg-body-secondary d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:40px;height:40px">
                            <svg class="sa-icon" style="width:20px;height:20px">
                                <use href="/icons/sprite.svg#{{ $feature['icon'] }}"></use>
                            </svg>
                        </div>
                        <div class="info-card-text flex-grow-1">
                            <span class="fs-lg text-truncate text-truncate-lg d-block fw-500">{{ $feature['name'] }}</span>
                            <span class="d-block text-truncate text-truncate-lg text-muted">
                                <code class="small">{{ $feature['slug'] }}</code>
                                <span class="badge ms-2 {{ $feature['effective'] ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $feature['effective'] ? 'Active' : 'Inactive' }}
                                </span>
                            </span>
                        </div>
                        <button class="js-expand-btn btn btn-sm btn-default d-none"
                                data-bs-toggle="collapse"
                                data-bs-target="#flag_{{ $loop->index }} > .card-body + .card-body"
                                type="button" aria-expanded="false">
                            <span class="collapsed-hidden">+</span>
                            <span class="collapsed-reveal">-</span>
                        </button>
                    </div>
                </div>
                {{-- Detail section: collapses in list view --}}
                <div class="card-body p-0 collapse show">
                    <div class="p-3">
                        <p class="text-muted small mb-3">{{ $feature['description'] }}</p>

                        <div class="d-flex flex-column gap-1 small mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Env Override</span>
                                <span class="{{ $feature['env_enabled'] ? 'text-success' : 'text-danger' }}">
                                    {{ $feature['env_enabled'] ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Pennant Flag</span>
                                <span class="js-flag-status {{ $feature['pennant_active'] ? 'text-success' : 'text-danger' }}"
                                      data-slug="{{ $feature['slug'] }}">
                                    {{ $feature['pennant_active'] ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        @if($feature['env_enabled'])
                        {{-- AJAX toggle — activates/deactivates via Pennant with no page refresh --}}
                        <button type="button"
                                class="btn btn-sm w-100 mb-2 js-flag-toggle {{ $feature['pennant_active'] ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                data-slug="{{ $feature['slug'] }}"
                                data-active="{{ $feature['pennant_active'] ? '1' : '0' }}">
                            {{ $feature['pennant_active'] ? 'Deactivate' : 'Activate' }}
                        </button>
                        @if($feature['effective'])
                        <form method="POST" action="{{ route('admin.activation.training-mode') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="slug" value="{{ $feature['slug'] }}">
                            <div class="d-flex gap-1 align-items-center">
                                <select name="training_mode" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach($trainingModes as $mode)
                                    <option value="{{ $mode->value }}" {{ $feature['training_mode'] === $mode->value ? 'selected' : '' }}>
                                        {{ ucfirst($mode->value) }}
                                    </option>
                                    @endforeach
                                </select>
                                <span class="text-muted small flex-shrink-0" title="Training requirement">
                                    <i class="fal fa-graduation-cap"></i>
                                </span>
                            </div>
                        </form>
                        @endif
                        @else
                        <div class="text-center small text-muted py-1">
                            Disabled via environment variable
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var filterInput  = document.getElementById('js-filter-flags');
    var clearBtn     = document.getElementById('js-clear-filter');
    var counter      = document.getElementById('js-filter-counter');
    var container    = document.getElementById('js-flags');
    var radioButtons = document.querySelectorAll('input[type="radio"][name="flagview"]');

    // Helper: remove all classes starting with a given prefix
    function removeClassPrefix(elements, prefix) {
        elements.forEach(function (el) {
            Array.from(el.classList).forEach(function (cls) {
                if (cls.indexOf(prefix) === 0) {
                    el.classList.remove(cls);
                }
            });
        });
    }

    // View toggle — same pattern as SmartAdmin usercontact.js
    radioButtons.forEach(function (radio) {
        radio.addEventListener('change', function () {
            var cards         = container.querySelectorAll('.card');
            var colElements   = container.querySelectorAll('[class*="col-xl-"]');
            var expandButtons = container.querySelectorAll('.js-expand-btn');
            var detailBodies  = container.querySelectorAll('.card-body + .card-body');

            if (this.value === 'grid') {
                removeClassPrefix(cards, 'mb-');
                cards.forEach(function (c) { c.classList.add('mb-g'); });

                removeClassPrefix(colElements, 'col-xl-');
                colElements.forEach(function (el) { el.classList.add('col-xl-4'); });

                expandButtons.forEach(function (btn) { btn.classList.add('d-none'); });
                detailBodies.forEach(function (body) { body.classList.add('show'); });
            } else if (this.value === 'list') {
                removeClassPrefix(cards, 'mb-');
                cards.forEach(function (c) { c.classList.add('mb-1'); });

                removeClassPrefix(colElements, 'col-xl-');
                colElements.forEach(function (el) { el.classList.add('col-xl-12'); });

                expandButtons.forEach(function (btn) { btn.classList.remove('d-none'); });
                detailBodies.forEach(function (body) { body.classList.remove('show'); });
            }
        });
    });

    // Filter logic
    function applyFilter() {
        var term    = filterInput.value.toLowerCase().trim();
        var columns = container.querySelectorAll('[class*="col-xl-"]');
        var visible = 0;
        var total   = columns.length;

        columns.forEach(function (col) { col.style.display = 'none'; });

        container.querySelectorAll('[data-filter-tags]').forEach(function (col) {
            var tags = col.getAttribute('data-filter-tags') || '';
            if (!term || tags.indexOf(term) !== -1) {
                col.style.display = '';
                visible++;
            }
        });

        if (term) {
            counter.textContent = 'Showing ' + visible + ' of ' + total + ' flags';
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
        container.querySelectorAll('[class*="col-xl-"]').forEach(function (col) {
            col.style.display = '';
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

<script>
// Feature-flag toggle without a page refresh.
(function () {
    const CSRF = @js(csrf_token());
    const URL  = @js(route('admin.activation.toggle'));

    document.querySelectorAll('.js-flag-toggle').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const slug = btn.dataset.slug;
            const newActive = btn.dataset.active === '1' ? 0 : 1;
            btn.disabled = true;
            try {
                const res = await fetch(URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ slug: slug, active: newActive }),
                });
                if (!res.ok) throw new Error('toggle failed');
                const data = await res.json();
                const active = !!data.active;

                btn.dataset.active = active ? '1' : '0';
                btn.textContent = active ? 'Deactivate' : 'Activate';
                btn.classList.toggle('btn-outline-danger', active);
                btn.classList.toggle('btn-outline-success', !active);

                const sel = (window.CSS && CSS.escape) ? CSS.escape(slug) : slug;
                document.querySelectorAll('.js-flag-status[data-slug="' + sel + '"]').forEach(function (s) {
                    s.textContent = active ? 'Active' : 'Inactive';
                    s.classList.toggle('text-success', active);
                    s.classList.toggle('text-danger', !active);
                });
            } catch (e) {
                alert('Could not update the feature flag. Please try again.');
            } finally {
                btn.disabled = false;
            }
        });
    });
})();
</script>
@endsection
