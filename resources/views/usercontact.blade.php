@extends('layouts.vertical', ['pageTitle' => 'User Contact'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'User Contact', 'pageSubTitle1' => 'Communication', 'pageSubTitle2'=> 'SmartAdmin', 'pageSubText' => 'User Contact page with notes.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="border-faded bg-faded p-3 mb-g d-flex">
                <div class="position-relative flex-grow-1">
                    <input class="form-control shadow-inset-2 form-control-lg" id="js-filter-contacts"
                           name="filter-contacts" placeholder="Filter contacts by name, email, phone or location"
                           type="text"/>
                    <button class="btn btn-sm btn-danger position-absolute top-50 end-0 translate-middle-y me-2 d-none"
                            id="js-clear-filter" type="button">
                        <i class="fal fa-times"></i>
                    </button>
                </div>
                <div aria-label="Change list &amp; grid view" class="btn-group btn-group-lg hidden-lg-down ms-3"
                     role="group">
                    <input checked="checked" class="btn-check" id="grid" name="contactview" type="radio" value="grid"/>
                    <label class="btn btn-outline-secondary" for="grid"><i class="fal fa-table"></i></label>
                    <input class="btn-check" id="table" name="contactview" type="radio" value="table"/>
                    <label class="btn btn-outline-secondary" for="table"><i class="fal fa-th-list"></i></label>
                </div>
            </div>
            <div class="ms-auto fs-sm text-muted mb-g" id="filter-result-counter"></div>
        </div>
    </div>
    <div class="row" id="js-contacts">
        <div class="col-xl-4">
            <div class="card border shadow-0 mb-g shadow-sm-hover"
                 data-filter-tags="oliver kopyov +13174562564 oliver.kopyov@smartadminwebapp.com detroit" id="c_1">
                <div class="card-body border-faded border-top-0 border-start-0 border-end-0 rounded-top">
                    <div class="d-flex flex-row align-items-center">
<span class="status status-success me-3">
<span class="rounded-circle profile-image d-block"
      style="background-image:url('/img/demo/avatars/avatar-b.png'); background-size: cover;"></span>
</span>
                        <div class="info-card-text flex-grow-1">
                            <a aria-expanded="false" class="fs-xl text-truncate text-truncate-lg"
                               data-bs-toggle="dropdown" href="javascript:void(0);">
                                Oliver Kopyov
                                <i class="fal fa-angle-down d-inline-block ms-1 fs-md"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">Send Email</a>
                                <a class="dropdown-item" href="#">Create Appointment</a>
                                <a class="dropdown-item" href="#">Block User</a>
                            </div>
                            <span class="d-block text-truncate text-truncate-lg">IT Director, Webora Themes.</span>
                        </div>
                        <button aria-expanded="false" class="js-expand-btn btn btn-sm btn-default d-none"
                                data-bs-target="#c_1 &gt; .card-body + .card-body" data-bs-toggle="collapse"
                                type="button">
                            <span class="collapsed-hidden">+</span>
                            <span class="collapsed-reveal">-</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 collapse show">
                    <div class="p-3">
                        <a class="mt-1 d-block fs-sm fw-400" href="tel:+13174562564">
                            <i class="fal fa-mobile-alt text-muted me-2"></i> +1 317-456-2564</a>
                        <a class="mt-1 d-block fs-sm fw-400" href="mailto:oliver.kopyov@smartadminwebapp.com">
                            <i class="fal fa-mouse-pointer text-muted me-2"></i> oliver.kopyov@smartadminwebapp.com</a>
                        <address class="fs-sm fw-400 mt-4 text-muted mb-0">
                            <i class="fal fa-map-pin me-2"></i> 15 Charist St, Detroit, MI, 48212, USA
                        </address>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border shadow-0 mb-g shadow-sm-hover"
                 data-filter-tags="sesha gray +13134611347 sesha.gray@smartadminwebapp.com detroit" id="c_2">
                <div class="card-body border-faded border-top-0 border-start-0 border-end-0 rounded-top">
                    <div class="d-flex flex-row align-items-center">
<span class="status status-warning me-3">
<span class="rounded-circle profile-image d-block"
      style="background-image:url('/img/demo/avatars/avatar-c.png'); background-size: cover;"></span>
</span>
                        <div class="info-card-text flex-grow-1">
                            <a aria-expanded="false" class="fs-xl text-truncate text-truncate-lg"
                               data-bs-toggle="dropdown" href="javascript:void(0);">
                                Sesha Gray
                                <i class="fal fa-angle-down d-inline-block ms-1 fs-md"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">Send Email</a>
                                <a class="dropdown-item" href="#">Create Appointment</a>
                                <a class="dropdown-item" href="#">Block User</a>
                            </div>
                            <span class="d-block text-truncate text-truncate-lg">Project Manager, Webora Themes.</span>
                        </div>
                        <button aria-expanded="false" class="js-expand-btn btn btn-sm btn-default d-none"
                                data-bs-target="#c_2 &gt; .card-body + .card-body" data-bs-toggle="collapse"
                                type="button">
                            <span class="collapsed-hidden">+</span>
                            <span class="collapsed-reveal">-</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 collapse show">
                    <div class="p-3">
                        <a class="mt-1 d-block fs-sm fw-400" href="tel:+13174562564">
                            <i class="fal fa-mobile-alt text-muted me-2"></i> +1 313-461-1347</a>
                        <a class="mt-1 d-block fs-sm fw-400" href="mailto:oliver.kopyov@smartadminwebapp.com">
                            <i class="fal fa-mouse-pointer text-muted me-2"></i> sesha.gray@smartadminwebapp.com</a>
                        <address class="fs-sm fw-400 mt-4 text-muted mb-0">
                            <i class="fal fa-map-pin me-2"></i> 134 Hamtrammac, Detroit, MI, 48314, USA
                        </address>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border shadow-0 mb-g shadow-sm-hover"
                 data-filter-tags="dr john cook phd +13137791347 john.cook@smartadminwebapp.com detroit" id="c_3">
                <div class="card-body border-faded border-top-0 border-start-0 border-end-0 rounded-top">
                    <div class="d-flex flex-row align-items-center">
<span class="status status-danger me-3">
<span class="rounded-circle profile-image d-block"
      style="background-image:url('/img/demo/avatars/avatar-e.png'); background-size: cover;"></span>
</span>
                        <div class="info-card-text flex-grow-1">
                            <a aria-expanded="false" class="fs-xl text-truncate text-truncate-lg"
                               data-bs-toggle="dropdown" href="javascript:void(0);">
                                Dr. John Cook PhD
                                <i class="fal fa-angle-down d-inline-block ms-1 fs-md"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">Send Email</a>
                                <a class="dropdown-item" href="#">Create Appointment</a>
                                <a class="dropdown-item" href="#">Block User</a>
                            </div>
                            <span class="d-block text-truncate text-truncate-lg">Human Resources, Webora Themes.</span>
                        </div>
                        <button aria-expanded="false" class="js-expand-btn btn btn-sm btn-default d-none"
                                data-bs-target="#c_3 &gt; .card-body + .card-body" data-bs-toggle="collapse"
                                type="button">
                            <span class="collapsed-hidden">+</span>
                            <span class="collapsed-reveal">-</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 collapse show">
                    <div class="p-3">
                        <a class="mt-1 d-block fs-sm fw-400" href="tel:+13174562564">
                            <i class="fal fa-mobile-alt text-muted me-2"></i> +1 313-779-1347</a>
                        <a class="mt-1 d-block fs-sm fw-400" href="mailto:oliver.kopyov@smartadminwebapp.com">
                            <i class="fal fa-mouse-pointer text-muted me-2"></i> john.cook@smartadminwebapp.com</a>
                        <address class="fs-sm fw-400 mt-4 text-muted mb-0">
                            <i class="fal fa-map-pin me-2"></i> 55 Smyth Rd, Detroit, MI, 48341, USA
                        </address>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border shadow-0 mb-g shadow-sm-hover"
                 data-filter-tags="jim ketty +13137793314 jim.ketty@smartadminwebapp.com detroit" id="c_4">
                <div class="card-body border-faded border-top-0 border-start-0 border-end-0 rounded-top">
                    <div class="d-flex flex-row align-items-center">
<span class="status status-success me-3">
<span class="rounded-circle profile-image d-block"
      style="background-image:url('/img/demo/avatars/avatar-k.png'); background-size: cover;"></span>
</span>
                        <div class="info-card-text flex-grow-1">
                            <a aria-expanded="false" class="fs-xl text-truncate text-truncate-lg"
                               data-bs-toggle="dropdown" href="javascript:void(0);">
                                Jim Ketty
                                <i class="fal fa-angle-down d-inline-block ms-1 fs-md"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">Send Email</a>
                                <a class="dropdown-item" href="#">Create Appointment</a>
                                <a class="dropdown-item" href="#">Block User</a>
                            </div>
                            <span class="d-block text-truncate text-truncate-lg">Staff Orgnizer, Webora Themes.</span>
                        </div>
                        <button aria-expanded="false" class="js-expand-btn btn btn-sm btn-default d-none"
                                data-bs-target="#c_4 &gt; .card-body + .card-body" data-bs-toggle="collapse"
                                type="button">
                            <span class="collapsed-hidden">+</span>
                            <span class="collapsed-reveal">-</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 collapse show">
                    <div class="p-3">
                        <a class="mt-1 d-block fs-sm fw-400" href="tel:+13174562564">
                            <i class="fal fa-mobile-alt text-muted me-2"></i> +1 313-779-3314</a>
                        <a class="mt-1 d-block fs-sm fw-400" href="mailto:oliver.kopyov@smartadminwebapp.com">
                            <i class="fal fa-mouse-pointer text-muted me-2"></i> jim.ketty@smartadminwebapp.com</a>
                        <address class="fs-sm fw-400 mt-4 text-muted mb-0">
                            <i class="fal fa-map-pin me-2"></i> 134 Tasy Rd, Detroit, MI, 48212, USA
                        </address>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border shadow-0 mb-g shadow-sm-hover"
                 data-filter-tags="dr john oliver oncologist +13137798134 john.oliver@smartadminwebapp.com detroit"
                 id="c_5">
                <div class="card-body border-faded border-top-0 border-start-0 border-end-0 rounded-top">
                    <div class="d-flex flex-row align-items-center">
<span class="status status-success me-3">
<span class="rounded-circle profile-image d-block"
      style="background-image:url('/img/demo/avatars/avatar-g.png'); background-size: cover;"></span>
</span>
                        <div class="info-card-text flex-grow-1">
                            <a aria-expanded="false" class="fs-xl text-truncate text-truncate-lg"
                               data-bs-toggle="dropdown" href="javascript:void(0);">
                                Dr. John Oliver
                                <i class="fal fa-angle-down d-inline-block ms-1 fs-md"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">Send Email</a>
                                <a class="dropdown-item" href="#">Create Appointment</a>
                                <a class="dropdown-item" href="#">Block User</a>
                            </div>
                            <span class="d-block text-truncate text-truncate-lg">Oncologist, Webora Themes.</span>
                        </div>
                        <button aria-expanded="false" class="js-expand-btn btn btn-sm btn-default d-none"
                                data-bs-target="#c_5 &gt; .card-body + .card-body" data-bs-toggle="collapse"
                                type="button">
                            <span class="collapsed-hidden">+</span>
                            <span class="collapsed-reveal">-</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 collapse show">
                    <div class="p-3">
                        <a class="mt-1 d-block fs-sm fw-400" href="tel:+13174562564">
                            <i class="fal fa-mobile-alt text-muted me-2"></i> +1 313-779-8134</a>
                        <a class="mt-1 d-block fs-sm fw-400" href="mailto:oliver.kopyov@smartadminwebapp.com">
                            <i class="fal fa-mouse-pointer text-muted me-2"></i> john.oliver@smartadminwebapp.com</a>
                        <address class="fs-sm fw-400 mt-4 text-muted mb-0">
                            <i class="fal fa-map-pin me-2"></i> 134 Gallery St, Detroit, MI, 46214, USA
                        </address>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border shadow-0 mb-g shadow-sm-hover"
                 data-filter-tags="sarah mcbrook xray +13137797613 sarah.mcbrook@smartadminwebapp.com detroit" id="c_6">
                <div class="card-body border-faded border-top-0 border-start-0 border-end-0 rounded-top">
                    <div class="d-flex flex-row align-items-center">
<span class="status status-success me-3">
<span class="rounded-circle profile-image d-block"
      style="background-image:url('/img/demo/avatars/avatar-h.png'); background-size: cover;"></span>
</span>
                        <div class="info-card-text flex-grow-1">
                            <a aria-expanded="false" class="fs-xl text-truncate text-truncate-lg"
                               data-bs-toggle="dropdown" href="javascript:void(0);">
                                Sarah McBrook
                                <i class="fal fa-angle-down d-inline-block ms-1 fs-md"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">Send Email</a>
                                <a class="dropdown-item" href="#">Create Appointment</a>
                                <a class="dropdown-item" href="#">Block User</a>
                            </div>
                            <span class="d-block text-truncate text-truncate-lg">Xray Division, Webora Themes.</span>
                        </div>
                        <button aria-expanded="false" class="js-expand-btn btn btn-sm btn-default d-none"
                                data-bs-target="#c_6 &gt; .card-body + .card-body" data-bs-toggle="collapse"
                                type="button">
                            <span class="collapsed-hidden">+</span>
                            <span class="collapsed-reveal">-</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 collapse show">
                    <div class="p-3">
                        <a class="mt-1 d-block fs-sm fw-400" href="tel:+13174562564">
                            <i class="fal fa-mobile-alt text-muted me-2"></i> +1 313-779-7613</a>
                        <a class="mt-1 d-block fs-sm fw-400" href="mailto:oliver.kopyov@smartadminwebapp.com">
                            <i class="fal fa-mouse-pointer text-muted me-2"></i> sarah.mcbrook@smartadminwebapp.com</a>
                        <address class="fs-sm fw-400 mt-4 text-muted mb-0">
                            <i class="fal fa-map-pin me-2"></i> 13 Jamie Rd, Detroit, MI, 48313, USA
                        </address>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border shadow-0 mb-g shadow-sm-hover"
                 data-filter-tags="jimmy fellan accounting +13137794314 jimmy.fallan@smartadminwebapp.com detroit"
                 id="c_7">
                <div class="card-body border-faded border-top-0 border-start-0 border-end-0 rounded-top">
                    <div class="d-flex flex-row align-items-center">
<span class="status status-success me-3">
<span class="rounded-circle profile-image d-block"
      style="background-image:url('/img/demo/avatars/avatar-i.png'); background-size: cover;"></span>
</span>
                        <div class="info-card-text flex-grow-1">
                            <a aria-expanded="false" class="fs-xl text-truncate text-truncate-lg"
                               data-bs-toggle="dropdown" href="javascript:void(0);">
                                Jimmy Fellan
                                <i class="fal fa-angle-down d-inline-block ms-1 fs-md"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">Send Email</a>
                                <a class="dropdown-item" href="#">Create Appointment</a>
                                <a class="dropdown-item" href="#">Block User</a>
                            </div>
                            <span class="d-block text-truncate text-truncate-lg">Accounting, Webora Themes.</span>
                        </div>
                        <button aria-expanded="false" class="js-expand-btn btn btn-sm btn-default d-none"
                                data-bs-target="#c_7 &gt; .card-body + .card-body" data-bs-toggle="collapse"
                                type="button">
                            <span class="collapsed-hidden">+</span>
                            <span class="collapsed-reveal">-</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 collapse show">
                    <div class="p-3">
                        <a class="mt-1 d-block fs-sm fw-400" href="tel:+13174562564">
                            <i class="fal fa-mobile-alt text-muted me-2"></i> +1 313-779-4314</a>
                        <a class="mt-1 d-block fs-sm fw-400" href="mailto:oliver.kopyov@smartadminwebapp.com">
                            <i class="fal fa-mouse-pointer text-muted me-2"></i> jimmy.fallan@smartadminwebapp.com</a>
                        <address class="fs-sm fw-400 mt-4 text-muted mb-0">
                            <i class="fal fa-map-pin me-2"></i> 55 Smyth Rd, Detroit, MI, 48341, USA
                        </address>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border shadow-0 mb-g shadow-sm-hover"
                 data-filter-tags="arica grace accounting +13137793347 arica.grace@smartadminwebapp.com detroit"
                 id="c_8">
                <div class="card-body border-faded border-top-0 border-start-0 border-end-0 rounded-top">
                    <div class="d-flex flex-row align-items-center">
<span class="status status-success me-3">
<span class="rounded-circle profile-image d-block"
      style="background-image:url('/img/demo/avatars/avatar-j.png'); background-size: cover;"></span>
</span>
                        <div class="info-card-text flex-grow-1">
                            <a aria-expanded="false" class="fs-xl text-truncate text-truncate-lg"
                               data-bs-toggle="dropdown" href="javascript:void(0);">
                                Arica Grace
                                <i class="fal fa-angle-down d-inline-block ms-1 fs-md"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">Send Email</a>
                                <a class="dropdown-item" href="#">Create Appointment</a>
                                <a class="dropdown-item" href="#">Block User</a>
                            </div>
                            <span class="d-block text-truncate text-truncate-lg">Accounting, Webora Themes.</span>
                        </div>
                        <button aria-expanded="false" class="js-expand-btn btn btn-sm btn-default d-none"
                                data-bs-target="#c_8 &gt; .card-body + .card-body" data-bs-toggle="collapse"
                                type="button">
                            <span class="collapsed-hidden">+</span>
                            <span class="collapsed-reveal">-</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 collapse show">
                    <div class="p-3">
                        <a class="mt-1 d-block fs-sm fw-400" href="tel:+13174562564">
                            <i class="fal fa-mobile-alt text-muted me-2"></i> +1 313-779-3347</a>
                        <a class="mt-1 d-block fs-sm fw-400" href="mailto:oliver.kopyov@smartadminwebapp.com">
                            <i class="fal fa-mouse-pointer text-muted me-2"></i> arica.grace@smartadminwebapp.com</a>
                        <address class="fs-sm fw-400 mt-4 text-muted mb-0">
                            <i class="fal fa-map-pin me-2"></i> 798 Smyth Rd, Detroit, MI, 48341, USA
                        </address>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra-content')
    <div class="content-wrapper-right">
        <div class="right-content bg-faded d-flex flex-column h-100">
            <div class="flex-grow-1 p-3">
                <style>
                    #note-content:empty:before {
                        content: 'Start typing your note...';
                        color: #999;
                        font-style: italic;
                        display: block;
                        pointer-events: none;
                    }

                    .note-item {
                        transition: all 0.2s ease;
                    }

                    .note-item .btn-outline-danger {
                        opacity: 0.7;
                        transition: opacity 0.2s ease;
                    }

                    .note-item:hover .btn-outline-danger {
                        opacity: 1;
                    }
                </style>

                <div id="notes-list-view">

                    <div id="notes-container" class="list-group"></div>
                    <div class="d-flex justify-content-center gap-2 align-items-center mb-3 mt-3">
                        <button type="button" id="clear-all-notes" class="btn btn-outline-danger btn-sm">
                            Clear Notes
                        </button>
                        <button type="button" id="create-new-note" class="btn btn-outline-secondary btn-sm">
                            Create New Note
                        </button>
                    </div>
                </div>

                <div id="note-edit-view" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <button type="button" id="show-notes-list" class="btn btn-outline-success btn-sm">
                            Back
                        </button>

                        <div class="btn-group btn-group-xs">
                            <button type="button" class="btn btn-outline-secondary btn-xs formatting-btn"
                                    data-format="bold" id="format-text-bold" title="Bold (Ctrl+B)">
                                <i class="fal fa-bold"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-xs formatting-btn"
                                    data-format="italic" id="format-text-italic" title="Italic (Ctrl+I)">
                                <i class="fal fa-italic"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-xs formatting-btn"
                                    data-format="underline" id="format-text-underline"
                                    title="Underline (Ctrl+U)">
                                <i class="fal fa-underline"></i>
                            </button>
                        </div>
                    </div>
                    <div class="editor-container mb-3">
                        <div id="note-content" class="info-container mb-3 border-faded p-3 rounded text-break"
                             contenteditable="true" placeholder="Start typing your note..."></div>
                        <div class="fs-nano text-muted"><i class="fal fa-check text-success"></i> Auto save every 5
                            seconds
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/optional/smartnotes.js','resources/scripts/pages/usercontact.js'])
@endsection
