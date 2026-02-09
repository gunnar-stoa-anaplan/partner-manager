@extends('layouts.vertical', ['pageTitle' => 'Forum: Discussion'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Forum: Discussion', 'pageSubTitle1' => 'Layouts', 'pageSubTitle2'=> 'Miscellaneous', 'pageSubText' => 'Discussion view with short and long reply action buttons, jump to dropdown, pagination, likes and star rating system.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="input-group input-group-lg mb-g">
                <input class="form-control shadow-inset-2" placeholder="Search Discussion" type="text"/>
                <button class="btn btn-outline-default" type="button">
                    <i class="fal fa-search"></i>
                </button>
            </div>
            <div class="d-flex align-items-center mb-3">
                <div class="h5 mb-0 d-inline-block">
                    <a href="{{ route('second', ['forum', 'threads']) }}">Account information &amp; Security</a> /
                    Package location
                </div>
                <div class="dropdown ms-auto">
                    <button aria-expanded="false" class="btn btn-outline-secondary dropdown-toggle btn-sm"
                            data-bs-toggle="dropdown" id="jumpToRepliesDropdown" type="button">
                        Jump to...
                    </button>
                    <ul aria-labelledby="jumpToRepliesDropdown" class="dropdown-menu">
                        <li><a class="dropdown-item" href="#post-1">Sunny A. (Thread Owner)</a></li>
                        <li><a class="dropdown-item" href="#post-2">Oliver Kopyov</a></li>
                        <li><a class="dropdown-item" href="#post-3">Dr. John Cook PhD</a></li>
                        <li><a class="dropdown-item" href="#post-4">Sarah McBrook</a></li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li><a class="dropdown-item" href="#mainReplyForm">Post a New Reply</a></li>
                    </ul>
                </div>
            </div>
            <!-- post -->
            <div class="card mb-g border shadow-0" id="post-1">
                <div class="card-header p-0">
                    <div class="d-flex align-items-center justify-content-between p-3">
                        <div class="d-flex align-items-center">
                            <div class="d-block flex-shrink-0">
                                <img alt="" class="img-fluid img-thumbnail"
                                     src="/img/demo/avatars/avatar-admin.png"/>
                            </div>
                            <div class="d-block ms-2">
                                <a class="fs-sm h6 fw-500 mb-0 d-block" href="javascript:void(0);">Sunny A. <span
                                        class="badge bg-primary">Thread Owner</span> </a>
                                <div class="d-flex mt-1 text-warning align-items-center">
                                    <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                        <use href="/icons/sprite.svg#star"></use>
                                    </svg>
                                    <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                        <use href="/icons/sprite.svg#star"></use>
                                    </svg>
                                    <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                        <use href="/icons/sprite.svg#star"></use>
                                    </svg>
                                    <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                        <use href="/icons/sprite.svg#star"></use>
                                    </svg>
                                    <svg class="sa-icon me-1">
                                        <use href="/icons/sprite.svg#star"></use>
                                    </svg>
                                    <span class="text-muted fs-xs font-italic">
                                        (140 votes)
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="d-inline-flex align-items-center ms-auto align-self-start cursor-pointer">
                            <span>55</span><i class="fas fa-heart ms-1 text-danger"></i>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p>
                        Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;
                        Phasellus
                        quis sem diam. Sed commodo metus in ultrices consequat. Vestibulum eu orci ante. Mauris vel
                        tincidunt mauris. Cras finibus, purus eu pharetra molestie, orci felis lacinia orci, ac congue
                        quam
                        turpis a nibh
                    </p>
                    <p>
                        Nam viverra diam magna, eget lobortis orci tincidunt sed. Donec et lobortis est. Morbi eget
                        massa
                        est. In iaculis odio lectus, sed efficitur nunc viverra non. Nullam molestie eros magna, eu
                        posuere
                        mauris posuere sit amet. Pellentesque hendrerit condimentum ipsum, euismod ornare lectus
                        pharetra
                        eget. Praesent semper est erat, commodo mollis arcu efficitur vitae. Maecenas gravida sit amet
                        nisi
                        vel interdum.
                    </p>
                    <p>
                        Vestibulum molestie, ipsum vitae feugiat lacinia, nisi magna accumsan velit, ac semper nisi
                        felis
                        vitae augue. Vivamus mattis quis erat eu gravida. Integer venenatis risus vitae ullamcorper
                        cursus.
                        Proin sodales odio sed aliquet pulvinar. Duis ipsum erat, ultricies a dolor non, tempor dictum
                        ante.
                        Morbi vel metus lectus
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex align-items-center">
                        <span class="text-sm text-muted font-italic"><i class="fal fa-clock me-1"></i> Posted 1 week
                            ago</span>
                        <a class="flex-shrink-0 ms-auto" data-bs-target="#reply-box-1" data-bs-toggle="collapse"
                           href="javascript:void(0);">Reply <i class="fal fa-reply ms-2"></i> </a>
                    </div>
                    <div class="collapse mt-3" id="reply-box-1">
                        <textarea class="form-control" placeholder="Quick reply..." rows="3"></textarea>
                        <div class="text-end pt-2">
                            <button class="btn btn-success" type="button">Quick Post</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- post -end -->
            <!-- post -->
            <div class="card mb-g border shadow-0" id="post-2">
                <div class="card-header p-0">
                    <div class="p-3 d-flex align-items-center">
                        <div class="d-block flex-shrink-0">
                            <img alt="" class="img-fluid img-thumbnail" src="/img/demo/avatars/avatar-b.png"/>
                        </div>
                        <div class="d-block ms-2">
                            <span class="h6 fw-bold d-block m-0">RE: Package location</span>
                            <a class="fs-sm h6 fw-500 mb-0 d-block" href="javascript:void(0);">Oliver Kopyov</a>
                            <div class="d-flex mt-1 text-warning align-items-center">
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <svg class="sa-icon me-1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <svg class="sa-icon me-1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <span class="text-muted fs-xs font-italic">
                                    (90 votes)
                                </span>
                            </div>
                        </div>
                        <div class="d-inline-flex align-items-center ms-auto align-self-start cursor-pointer">
                            <i class="fal fa-heart ms-1 text-muted"></i>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <blockquote class="font-italic fw-sm border border-top-0 border-end-0 border-bottom-0 p-3">
                        Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;
                        Phasellus
                        quis sem diam. Sed commodo metus in ultrices consequat. Vestibulum eu orci ante. Mauris vel
                        tincidunt mauris. Cras finibus, purus eu pharetra molestie, orci felis lacinia orci, ac congue
                        quam
                        turpis a nibh...
                    </blockquote>
                    <p>
                        Proin vehicula nibh a nisl porta laoreet. Etiam quis massa quam. Etiam elementum nunc accumsan
                        ullamcorper viverra. Sed aliquam ligula eu porta posuere. Nullam in dictum lacus. Nulla auctor
                        hendrerit purus, sit amet lacinia tellus placerat nec. Nunc condimentum urna sit amet blandit
                        egestas. Nam non placerat nisi. Sed sit amet massa porta, porttitor tellus porttitor, sagittis
                        ex.
                        Duis vitae dolor sit amet sem mattis molestie. Aliquam id pharetra leo.
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex align-items-center">
                        <span class="text-sm text-muted font-italic"><i class="fal fa-clock me-1"></i> Posted 4 days
                            ago</span>
                        <a class="flex-shrink-0 ms-auto" data-bs-target="#reply-box-2" data-bs-toggle="collapse"
                           href="javascript:void(0);">Reply <i class="fal fa-reply ms-2"></i> </a>
                    </div>
                    <div class="collapse mt-3" id="reply-box-2">
                        <textarea class="form-control" placeholder="Quick reply..." rows="3"></textarea>
                        <div class="text-end pt-2">
                            <button class="btn btn-success" type="button">Quick Post</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- post -end -->
            <!-- post -->
            <div class="card mb-g border shadow-0" id="post-3">
                <div class="card-header p-0">
                    <div class="p-3 d-flex align-items-center">
                        <div class="d-block flex-shrink-0">
                            <img alt="" class="img-fluid img-thumbnail" src="/img/demo/avatars/avatar-e.png"/>
                        </div>
                        <div class="d-block ms-2">
                            <div class="h6 mb-0">Replying to <a href="javascript:void(0);">Oliver Kopyov</a></div>
                            <a class="fs-sm h6 fw-500 mb-0 d-block" href="javascript:void(0);">Dr. John Cook PhD</a>
                            <div class="d-flex mt-1 text-warning align-items-center">
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <span class="text-muted fs-xs font-italic">
                                    (77 votes)
                                </span>
                            </div>
                        </div>
                        <div class="d-inline-flex align-items-center ms-auto align-self-start cursor-pointer">
                            <i class="fal fa-heart ms-1 text-muted"></i>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p>
                        Proin vehicula nibh a nisl porta laoreet. Etiam quis massa quam. Etiam elementum nunc accumsan
                        ullamcorper viverra. Sed aliquam ligula eu porta posuere. Nullam in dictum lacus. Nulla auctor
                        hendrerit purus, sit amet lacinia tellus placerat nec. Nunc condimentum urna sit amet blandit
                        egestas. Nam non placerat nisi. Sed sit amet massa porta, porttitor tellus porttitor, sagittis
                        ex.
                        Duis vitae dolor sit amet sem mattis molestie. Aliquam id pharetra leo.
                    </p>
                    <p>
                        Vivamus at sapien eget lorem suscipit laoreet. Donec tincidunt augue auctor, ullamcorper urna
                        ac,
                        bibendum odio. Donec ac facilisis mauris. Quisque egestas quam libero, in vulputate purus
                        accumsan
                        vitae
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex align-items-center">
                        <span class="text-sm text-muted font-italic"><i class="fal fa-clock me-1"></i> Posted 2 days
                            ago</span>
                        <a class="flex-shrink-0 ms-auto" data-bs-target="#reply-box-3" data-bs-toggle="collapse"
                           href="javascript:void(0);">Reply <i class="fal fa-reply ms-2"></i> </a>
                    </div>
                    <div class="collapse mt-3" id="reply-box-3">
                        <textarea class="form-control" placeholder="Quick reply..." rows="3"></textarea>
                        <div class="text-end pt-2">
                            <button class="btn btn-success" type="button">Quick Post</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- post -end -->
            <!-- post -->
            <div class="card mb-g border shadow-0" id="post-4">
                <div class="card-header p-0">
                    <div class="p-3 d-flex align-items-center">
                        <div class="d-block flex-shrink-0">
                            <img alt="" class="img-fluid img-thumbnail" src="/img/demo/avatars/avatar-h.png"/>
                        </div>
                        <div class="d-block ms-2">
                            <span class="h6 d-block m-0">RE: Package location</span>
                            <a class="fs-sm h6 fw-500 mb-0 d-block" href="javascript:void(0);">Sarah McBrook</a>
                            <div class="d-flex mt-1 text-warning align-items-center">
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <svg class="sa-icon sa-icon-warning me-1" style="--sa-fill-opacity: 1">
                                    <use href="/icons/sprite.svg#star"></use>
                                </svg>
                                <span class="text-muted fs-xs font-italic">
                                    (146 votes)
                                </span>
                            </div>
                        </div>
                        <div class="d-inline-flex align-items-center ms-auto align-self-start cursor-pointer">
                            <span>3</span><i class="fas fa-heart ms-1 text-danger"></i>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p>
                        Curabitur sollicitudin eros eget nibh commodo posuere. Aenean non diam at massa accumsan
                        elementum
                        eget in arcu. Cras vestibulum ornare nulla, vel porttitor turpis tristique at. In laoreet
                        eleifend
                        dolor, ac eleifend nunc porttitor et. Praesent volutpat risus in metus lacinia porttitor
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex align-items-center">
                        <span class="text-sm text-muted font-italic"><i class="fal fa-clock me-1"></i> Posted 1 hour
                            ago</span>
                        <a class="flex-shrink-0 ms-auto" data-bs-target="#reply-box-4" data-bs-toggle="collapse"
                           href="javascript:void(0);">Reply <i class="fal fa-reply ms-2"></i> </a>
                    </div>
                    <div class="collapse mt-3" id="reply-box-4">
                        <textarea class="form-control" placeholder="Quick reply..." rows="3"></textarea>
                        <div class="text-end pt-2">
                            <button class="btn btn-success" type="button">Quick Post</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- post -end -->
            <!-- Pagination -->
            <ul class="pagination pagination-sm mt-3 mb-g">
                <li class="page-item disabled">
                    <a aria-label="First" class="page-link" href="#">
                        First
                    </a>
                </li>
                <li class="page-item disabled">
                    <a aria-label="Previous" class="page-link" href="#">
                        Prev
                    </a>
                </li>
                <li aria-current="page" class="page-item active">
                    <span class="page-link">
                        1
                        <span class="sr-only">(current)</span>
                    </span>
                </li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="paginate_button page-item disabled px-0">…</li>
                <li class="page-item"><a class="page-link" href="#">7</a></li>
                <li class="page-item"><a class="page-link" href="#">8</a></li>
                <li class="page-item">
                    <a aria-label="Next" class="page-link" href="#">
                        Next
                    </a>
                </li>
                <li class="page-item">
                    <a aria-label="Last" class="page-link" href="#">
                        Last
                    </a>
                </li>
            </ul>
            <!-- Reply button -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-primary" data-bs-target="#mainReplyForm" data-bs-toggle="collapse"
                        type="button">
                    Reply to this thread
                </button>
            </div>
            <!-- Reply form -->
            <div class="collapse" id="mainReplyForm">
                <div class="card mb-g border shadow-0">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">Post a Reply</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="row mb-3">
                                <div class="col-lg-8">
                                    <div class="d-flex mb-2">
                                        <div class="btn-group me-2">
                                            <button class="btn btn-outline-secondary btn-sm" type="button"><i
                                                    class="fal fa-bold"></i></button>
                                            <button class="btn btn-outline-secondary btn-sm" type="button"><i
                                                    class="fal fa-italic"></i></button>
                                            <button class="btn btn-outline-secondary btn-sm" type="button"><i
                                                    class="fal fa-underline"></i></button>
                                        </div>
                                        <div class="btn-group me-2">
                                            <button class="btn btn-outline-secondary btn-sm" type="button"><i
                                                    class="fal fa-list-ul"></i></button>
                                            <button class="btn btn-outline-secondary btn-sm" type="button"><i
                                                    class="fal fa-list-ol"></i></button>
                                            <button class="btn btn-outline-secondary btn-sm" type="button"><i
                                                    class="fal fa-link"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-control p-3 mb-3" id="quotePreview" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold">Quoting <span id="quoteName">Sarah McBrook</span></span>
                                        <button aria-label="Close" class="btn-close"
                                                onclick="document.getElementById('quotePreview').style.display = 'none';"
                                                type="button"></button>
                                    </div>
                                    <blockquote class="font-italic p-2 border-start border-3 border-primary ps-3">
                                        <p class="mb-0">Curabitur sollicitudin eros eget nibh commodo posuere. Aenean
                                            non
                                            diam at
                                            massa accumsan elementum eget in arcu...</p>
                                    </blockquote>
                                </div>
                                <textarea class="form-control" placeholder="Type your reply here..."
                                          rows="6"></textarea>
                            </div>
                            <div class="d-flex justify-content-end align-items-center">
                                <button class="btn btn-outline-secondary me-2" type="button">Save Draft</button>
                                <button class="btn btn-success" type="submit">Post</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End Reply form -->
        </div>
    </div>
@endsection

@section('scripts')
@endsection
