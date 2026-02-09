@extends('layouts.vertical', ['pageTitle' => 'Forum View'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Forum View', 'pageSubTitle1' => 'Layouts', 'pageSubTitle2'=> 'Miscellaneous', 'pageSubText' => 'Full forum view system, with threads and posts.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="input-group input-group-lg mb-g">
                <input class="form-control shadow-inset-2" placeholder="Search topics" type="text"/>
                <button class="btn btn-outline-default" type="button">
                    <i class="fal fa-search"></i>
                </button>
            </div>
            <div class="card mb-g border shadow-0">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <span class="h6 fw-bold text-uppercase">General</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <a class="btn btn-outline-success btn-sm ms-auto me-2 flex-shrink-0 waves-effect waves-themed"
                               href="javascript:void(0);">Add</a>
                            <a class="btn btn-outline-danger btn-sm flex-shrink-0 waves-effect waves-themed"
                               href="javascript:void(0);">Edit</a>
                        </div>
                    </div>
                </div>
                <div class="card-header p-0">
                    <div class="row g-0 row-grid align-items-stretch">
                        <div class="col-12 col-md">
                            <div class="text-uppercase text-muted py-2 px-3">Title</div>
                        </div>
                        <div class="col-sm-6 col-md-2 col-xl-1 d-none d-md-block">
                            <div class="text-uppercase text-muted py-2 px-3">Status</div>
                        </div>
                        <div class="col-sm-6 col-md-3 d-none d-md-block">
                            <div class="text-uppercase text-muted py-2 px-3">Last posts</div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0 row-grid">
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <span class="d-flex me-3 flex-shrink-0" style="font-size: 2.1rem">
                                                <div class="stack-icon">
                                                    <div class="icon-layers">
                                                        <i class="sa sa-base-2 stack-3x alpha-7 text-primary"></i>
                                                    </div>
                                                    <div class="icon-layers">
                                                        <i class="sa sa-base-10 text-light stack-2x"></i>
                                                    </div>
                                                    <div class="icon-layers"><i
                                                            class="sa sa-profile stack-2x text-primary"></i></div>
                                                </div>
                                            </span>
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}"> Account
                                                    information &amp; Security <span
                                                        class="badge bg-warning rounded">Sticky</span>
                                                </a>
                                                <div class="d-block text-muted fs-sm"> Fusce at velit viverra, luctus
                                                    augue
                                                    nec, dapibus urna
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">243 <i>Topics</i></span>
                                        <span class="d-block text-muted">407 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)"> Duis vitae
                                                    sapien urna. Proin pellentesque laoreet ligula pharetra semper. </a>
                                                <div class="text-muted small text-truncate"> Today, 12:12 <a
                                                        href="javascript:void(0)">katty60</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <span class="d-flex me-3 flex-shrink-0" style="font-size: 2.1rem">
                                                <div class="stack-icon">
                                                    <div class="icon-layers"><i
                                                            class="sa sa-base-7 stack-3x text-primary alpha-7"></i></div>
                                                    <div class="icon-layers"><i
                                                            class="sa sa-base-7 text-primary stack-1x"></i></div>
                                                    <div class="icon-layers"><i
                                                            class="sa sa-graph text-light stack-1x"></i>
                                                    </div>
                                                </div>
                                            </span>
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}"> Global
                                                    Stats </a>
                                                <div class="d-block text-muted fs-sm"> Sed felis eros, facilisis eu
                                                    cursus
                                                    at, efficitur et felis
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">64 <i>Topics</i></span>
                                        <span class="d-block text-muted">102 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)"> Nunc id
                                                    varius
                                                    nisl, a feugiat eros </a>
                                                <div class="text-muted small text-truncate"> Today, 05:01 <a
                                                        href="javascript:void(0)">gsnoopy520</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <span class="d-flex me-3 flex-shrink-0" style="font-size: 2.1rem">
                                                <div class="stack-icon">
                                                    <div class="icon-layers"><i
                                                            class="sa sa-base-19 stack-3x text-primary alpha-7"></i></div>
                                                    <div class="icon-layers"><i
                                                            class="sa sa-base-7 stack-2x text-dark alpha-2"></i></div>
                                                    <div class="icon-layers"><i
                                                            class="sa sa-base-7 stack-1x text-primary"></i></div>
                                                    <div class="icon-layers"><i
                                                            class="sa sa-globe stack-1x text-light"></i></div>
                                                </div>
                                            </span>
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}"> Global
                                                    Accouncements </a>
                                                <div class="d-block text-muted fs-sm"> Curabitur scelerisque ipsum nec
                                                    dui
                                                    lacinia bibendum
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">124 <i>Topics</i></span>
                                        <span class="d-block text-muted">314 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)"> Integer id
                                                    eros vitae leo semper sodales vel a est. </a>
                                                <div class="text-muted small text-truncate"> Today, 02:25 <a
                                                        href="javascript:void(0)">lerem03</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                    </div>
                </div>
            </div>
            <div class="card mb-g border shadow-0">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <span class="h6 fw-bold text-uppercase">Policy</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <a class="btn btn-outline-success btn-sm ms-auto me-2 flex-shrink-0 waves-effect waves-themed"
                               href="javascript:void(0);">Add</a>
                            <a class="btn btn-outline-danger btn-sm flex-shrink-0 waves-effect waves-themed"
                               href="javascript:void(0);">Edit</a>
                        </div>
                    </div>
                </div>
                <div class="card-header p-0">
                    <div class="row g-0 row-grid align-items-stretch">
                        <div class="col-12 col-md">
                            <div class="text-uppercase text-muted py-2 px-3">Title</div>
                        </div>
                        <div class="col-sm-6 col-md-2 col-xl-1 d-none d-md-block">
                            <div class="text-uppercase text-muted py-2 px-3">Status</div>
                        </div>
                        <div class="col-sm-6 col-md-3 d-none d-md-block">
                            <div class="text-uppercase text-muted py-2 px-3">Last posts</div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0 row-grid">
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <span class="d-flex me-3 flex-shrink-0" style="font-size: 2.1rem">
                                                <div class="stack-icon">
                                                    <div class="icon-layers">
                                                        <i class="sa sa-base-2 stack-3x text-primary"></i>
                                                    </div>
                                                    <div class="icon-layers"><i
                                                            class="sa sa-base-3 stack-1x text-light alpha-2"></i></div>
                                                    <div class="icon-layers"><i
                                                            class="fal fa-user-md stack-1x text-light"></i></div>
                                                </div>
                                            </span>
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}"> Clients &amp;
                                                    Staff </a>
                                                <div class="d-block text-muted fs-sm"> Fusce at velit viverra, luctus
                                                    augue
                                                    nec, dapibus urna
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">72 <i>Topics</i></span>
                                        <span class="d-block text-muted">89 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)"> Nam at justo
                                                    magna. Aenean facilisis ultricies turpis </a>
                                                <div class="text-muted small text-truncate"> Today, 08:13 <a
                                                        href="javascript:void(0)">erakalambert</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <span class="d-flex me-3 flex-shrink-0" style="font-size: 2.1rem">
                                                <div class="stack-icon">
                                                    <div class="icon-layers"><i
                                                            class="sa sa-base-7 stack-3x text-success"></i></div>
                                                    <div class="icon-layers"><i
                                                            class="sa sa-base-4 alpha-1 stack-2x text-light"></i></div>
                                                    <div class="icon-layers"><i
                                                            class="fal fa-briefcase-medical text-light stack-1x"></i></div>
                                                </div>
                                            </span>
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}"> Software
                                                    &amp;
                                                    Hardware Equipments </a>
                                                <div class="d-block text-muted fs-sm"> Nulla laoreet dignissim justo, id
                                                    viverra nibh suscipit in.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">99 <i>Topics</i></span>
                                        <span class="d-block text-muted">72 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)"> Integer
                                                    felis
                                                    justo, condimentum vitae pharetra vel, hendrerit eget orci. </a>
                                                <div class="text-muted small text-truncate"> Today, 06:01 <a
                                                        href="javascript:void(0)">elisamury</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <span class="d-flex me-3 flex-shrink-0" style="font-size: 2.1rem">
                                                <div class="stack-icon">
                                                    <div class="icon-layers"><i
                                                            class="sa sa-base-15 stack-3x text-success"></i></div>
                                                    <div class="icon-layers"><i
                                                            class="sa sa-base-10 stack-2x alpha-2 text-dark"></i></div>
                                                    <div class="icon-layers"><i
                                                            class="fal fa-dollar-sign stack-1x text-light"></i></div>
                                                </div>
                                            </span>
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}"> Billing
                                                    Department </a>
                                                <div class="d-block text-muted fs-sm"> Curabitur scelerisque ipsum nec
                                                    dui
                                                    lacinia bibendum
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">88 <i>Topics</i></span>
                                        <span class="d-block text-muted">109 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)"> Duis
                                                    placerat
                                                    in sapien et placerat. Duis quis feugiat leo. Curabitur laoreet ex
                                                    nec
                                                    odio interdum </a>
                                                <div class="text-muted small text-truncate"> Today, 12:25 <a
                                                        href="javascript:void(0)">josekras_alma</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <span class="d-flex me-3 flex-shrink-0" style="font-size: 2.1rem">
                                                <div class="stack-icon">
                                                    <div class="icon-layers"><i
                                                            class="sa sa-base-3 stack-3x text-warning"></i></div>
                                                    <div class="icon-layers"><i
                                                            class="sa sa-base-3 stack-2x text-light alpha-3"></i></div>
                                                    <div class="icon-layers"><i
                                                            class="fal fa-question stack-1x text-dark"></i></div>
                                                </div>
                                            </span>
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}">
                                                    Miscellaneous,
                                                    Lost &amp; Found </a>
                                                <div class="d-block text-muted fs-sm"> Phasellus pharetra felis et leo
                                                    malesuada
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">50 <i>Topics</i></span>
                                        <span class="d-block text-muted">66 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)"> Nunc id
                                                    varius
                                                    nisl, a feugiat eros. Nulla laoreet dignissim justo, id viverra nibh
                                                    suscipit in. Curabitur scelerisque ipsum nec dui lacinia
                                                    bibendum </a>
                                                <div class="text-muted small text-truncate"> Today, 05:25 <a
                                                        href="javascript:void(0)">kraluakosh</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                    </div>
                </div>
            </div>
            <div class="card mb-g border shadow-0">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <span class="h6 fw-bold text-uppercase">Password Protected Threads</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <a class="btn btn-outline-success btn-sm ms-auto me-2 flex-shrink-0 waves-effect waves-themed"
                               href="javascript:void(0);">Add</a>
                            <a class="btn btn-outline-danger btn-sm flex-shrink-0 waves-effect waves-themed"
                               href="javascript:void(0);">Edit</a>
                        </div>
                    </div>
                </div>
                <div class="card-header p-0">
                    <div class="row g-0 row-grid align-items-stretch">
                        <div class="col-12 col-md">
                            <div class="text-uppercase text-muted py-2 px-3">Title</div>
                        </div>
                        <div class="col-sm-6 col-md-2 col-xl-1 d-none d-md-block">
                            <div class="text-uppercase text-muted py-2 px-3">Status</div>
                        </div>
                        <div class="col-sm-6 col-md-3 d-none d-md-block">
                            <div class="text-uppercase text-muted py-2 px-3">Last posts</div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0 row-grid">
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}">Design Interns
                                                    (UI/UX, Visual, Interaction) <span class="badge bg-success">Has
                                                        Access</span>
                                                </a>
                                                <div class="d-block text-muted fs-sm"> Proin pellentesque laoreet ligula
                                                    pharetra semper
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">1024 <i>Topics</i></span>
                                        <span class="d-block text-muted">5431 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)"> Nam at justo
                                                    magna. Aenean facilisis ultricies turpis </a>
                                                <div class="text-muted small text-truncate"> Today, 12:13 <a
                                                        href="javascript:void(0)">c_lantern</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}"> Frontend
                                                    Developers (React, Vue, Angular) <span class="badge bg-success">Has
                                                        Access</span>
                                                </a>
                                                <div class="d-block text-muted fs-sm"> Nulla laoreet dignissim justo, id
                                                    viverra nibh suscipit in.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">1205 <i>Topics</i></span>
                                        <span class="d-block text-muted">4314 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)"> Morbi id
                                                    enim
                                                    a ex gravida dignissim nec eu massa </a>
                                                <div class="text-muted small text-truncate"> Today, 06:01 <a
                                                        href="javascript:void(0)">jamie</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}"> Backend
                                                    Developers (Node.js, Python, Ruby) <span
                                                        class="badge bg-danger">Password</span>
                                                </a>
                                                <div class="d-block text-muted fs-sm"> Curabitur scelerisque ipsum nec
                                                    dui
                                                    lacinia bibendum
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">764 <i>Topics</i></span>
                                        <span class="d-block text-muted">534 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate text-blur" href="javascript:void(0)">
                                                    Duis
                                                    placerat in sapien et placerat. Duis quis feugiat leo. Curabitur
                                                    laoreet
                                                    ex nec odio interdum </a>
                                                <div class="text-muted small text-truncate"> Today, 12:25 <a
                                                        href="javascript:void(0)">josekras_alma</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}"> UX
                                                    Researchers
                                                    <span class="badge bg-danger">Password</span>
                                                </a>
                                                <div class="d-block text-muted fs-sm"> Duis placerat in sapien et
                                                    placerat
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">87 <i>Topics</i></span>
                                        <span class="d-block text-muted">674 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate text-blur" href="javascript:void(0)">
                                                    Praesent mollis congue bibendum. Etiam ipsum augue, sodales id metus
                                                    a,
                                                    molestie hendrerit felis. </a>
                                                <div class="text-muted small text-truncate"> Today, 05:25 <a
                                                        href="javascript:void(0)">lambert</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}"> Product
                                                    Designers <span class="badge bg-success">Has Access</span>
                                                </a>
                                                <div class="d-block text-muted fs-sm"> Duis placerat in sapien et
                                                    placerat
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">731<i>Topics</i></span>
                                        <span class="d-block text-muted">1246 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)"> Proin non
                                                    felis lobortis, porta arcu a, iaculis arcu. Morbi tincidunt non ante
                                                    et
                                                    fermentum </a>
                                                <div class="text-muted small text-truncate"> Today, 05:25 <a
                                                        href="javascript:void(0)">elmo</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                        <!-- thread -->
                        <div class="col-12">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex">
                                            <div class="d-inline-flex flex-column">
                                                <a class="fs-lg fw-500 d-block" href="{{ route('second', ['forum', 'threads']) }}"> DevOps &amp;
                                                    QA
                                                    Engineers <span class="badge bg-success">Has Access</span>
                                                </a>
                                                <div class="d-block text-muted fs-sm"> Sit amet luctus nulla suscipit.
                                                    Phasellus pharetra felis
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">3943<i>Topics</i></span>
                                        <span class="d-block text-muted">9843 <i>Posts</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)"> Sed felis
                                                    eros, facilisis eu cursus at, efficitur et felis </a>
                                                <div class="text-muted small text-truncate"> Today, 05:25 <a
                                                        href="javascript:void(0)">katty60</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
