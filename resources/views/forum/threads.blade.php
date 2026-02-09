@extends('layouts.vertical', ['pageTitle' => 'Forum: Threads'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Forum: Threads', 'pageSubTitle1' => 'Layouts', 'pageSubTitle2'=> 'Miscellaneous', 'pageSubText' => 'Thread view with posts, replies, pagination, etc, with a functional "New Thread" button.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="input-group input-group-lg mb-g">
                <input class="form-control shadow-inset-2" placeholder="Search Threads" type="text"/>
                <button class="btn btn-outline-default" type="button">
                    <i class="fal fa-search"></i>
                </button>
            </div>
            <div class="card mb-g border shadow-0">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <span class="h6 fw-bold text-uppercase d-flex align-items-center mb-0">
                                <a class="btn btn-icon btn-outline-default me-2" href="{{ route('second', ['forum', 'index']) }}">
                                    <i class="fal fa-chevron-left"></i>
                                </a>
                                Account information &amp; Security
                            </span>
                        </div>
                        <div class="d-flex align-items-center">
                            <a class="btn btn-success shadow-0 btn-sm ms-auto flex-shrink-0 waves-effect waves-themed"
                               data-bs-target="#newThreadModal" data-bs-toggle="modal" href="javascript:void(0);">New
                                Thread</a>
                        </div>
                    </div>
                </div>
                <div class="card-header p-0">
                    <div class="row g-0 row-grid align-items-stretch">
                        <div class="col-12 col-md">
                            <div class="text-uppercase text-muted py-2 px-3">Title</div>
                        </div>
                        <div class="col-sm-6 col-md-2 col-xl-1 d-none d-md-block">
                            <div class="text-uppercase text-muted py-2 px-3">Replies</div>
                        </div>
                        <div class="col-sm-6 col-md-3 d-none d-md-block">
                            <div class="text-uppercase text-muted py-2 px-3">Last update</div>
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
                                        <div class="d-flex flex-column">
                                            <a class="fs-lg fw-500 d-flex align-items-start"
                                               href="{{ route('second', ['forum', 'discussion']) }}">
                                                Package location <span class="badge bg-warning ms-auto">Sticky</span>
                                            </a>
                                            <div class="d-block text-muted fs-sm">
                                                Started by <a href="javascript:void(0);">c_lantern</a> on January 21,
                                                2019
                                                @12:30PM
                                            </div>
                                        </div>
                                        <div class="d-flex">
                                            <ul class="pagination pagination-xs mb-0 mt-3 align-self-end">
                                                <li class="page-item"><a class="page-link" href="#">1</a></li>
                                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                                <li class="paginate_button page-item disabled px-0">…</li>
                                                <li class="page-item"><a class="page-link" href="#">7</a></li>
                                                <li class="page-item"><a class="page-link" href="#">25</a></li>
                                                <li class="page-item">
                                                    <a aria-label="Next" class="page-link" href="#">
                                                        <span aria-hidden="true">Last page</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">72 <i>Replies</i></span>
                                        <span class="d-block text-muted">4314 <i>Views</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)">
                                                    Nam at justo magna. Aenean facilisis ultricies turpis
                                                </a>
                                                <div class="text-muted small text-truncate">
                                                    Today, 12:13 <a href="javascript:void(0)">c_lantern</a>
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
                                        <div class="d-flex flex-column">
                                            <a class="fs-lg fw-500 d-flex align-items-start"
                                               href="{{ route('second', ['forum', 'discussion']) }}">
                                                Vestibulum molestie, ipsum vitae feugiat lacinia
                                            </a>
                                            <div class="d-block text-muted fs-sm">
                                                Started by <a href="javascript:void(0);">jamie</a> on January 12, 2019
                                                @4:23PM
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">15 <i>Replies</i></span>
                                        <span class="d-block text-muted">4314 <i>Views</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)">
                                                    Morbi id enim a ex gravida dignissim nec eu massa
                                                </a>
                                                <div class="text-muted small text-truncate">
                                                    Today, 06:01 <a href="javascript:void(0)">jamie</a>
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
                                        <div class="d-flex flex-column">
                                            <a class="fs-lg fw-500 d-flex align-items-start"
                                               href="{{ route('second', ['forum', 'discussion']) }}">
                                                Nam viverra diam magna, eget lobortis orci tincidunt sed <span
                                                    class="badge bg-danger ms-auto"><i class="fal fa-lock"></i> <span
                                                        class="ms-1 d-none d-md-inline">Locked</span></span>
                                            </a>
                                            <div class="d-block text-muted fs-sm">
                                                Started by <a href="javascript:void(0);">maggie</a> on February 17, 2019
                                                @8:01AM
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">764 <i>Replies</i></span>
                                        <span class="d-block text-muted">534 <i>Views</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)">
                                                    Duis placerat in sapien et placerat. Duis quis feugiat leo.
                                                    Curabitur
                                                    laoreet ex nec odio interdum
                                                </a>
                                                <div class="text-muted small text-truncate">
                                                    Today, 12:25 <a href="javascript:void(0)">josekras_alma</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- thread -end -->
                        <!-- thread -->
                        <div class="col-12 disabled">
                            <div class="row g-0 row-grid align-items-stretch">
                                <div class="col-md">
                                    <div class="p-3">
                                        <div class="d-flex flex-column">
                                            <span class="fs-lg fw-500 d-flex align-items-start"> Proin vehicula nibh a nisl
                                                porta laoreet <span class="badge bg-secondary ms-auto">Disabled</span>
                                            </span>
                                            <div class="d-block text-muted fs-sm"> Started by <a
                                                    href="javascript:void(0);">larry85</a> on December 12, 2019 @12:30PM
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">87 <i>Replies</i></span>
                                        <span class="d-block text-muted">674 <i>Views</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <span class="d-block text-truncate"> Praesent mollis congue bibendum. Etiam
                                                    ipsum augue, sodales id metus a, molestie hendrerit felis. </span>
                                                <div class="text-muted small text-truncate"> Today, 05:25
                                                    <span>lambert</span>
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
                                        <div class="d-flex flex-column">
                                            <a class="fs-lg fw-500 d-flex align-items-start"
                                               href="{{ route('second', ['forum', 'discussion']) }}">
                                                Nulla auctor hendrerit purus, sit amet lacinia tellus placerat nec.
                                            </a>
                                            <div class="d-block text-muted fs-sm">
                                                Started by <a href="javascript:void(0);">gi_34</a> on January 01, 2019
                                                @2:30PM
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">731<i>Replies</i></span>
                                        <span class="d-block text-muted">1246 <i>Views</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)">
                                                    Proin non felis lobortis, porta arcu a, iaculis arcu. Morbi
                                                    tincidunt
                                                    non ante et fermentum
                                                </a>
                                                <div class="text-muted small text-truncate">
                                                    Today, 05:25 <a href="javascript:void(0)">elmo</a>
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
                                        <div class="d-flex flex-column">
                                            <a class="fs-lg fw-500 d-flex align-items-start"
                                               href="{{ route('second', ['forum', 'discussion']) }}">
                                                Donec tincidunt augue auctor, ullamcorper urna ac
                                            </a>
                                            <div class="d-block text-muted fs-sm">
                                                Started by <a href="javascript:void(0);">zico03</a> on August 25, 2019
                                                @7:45PM
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">3943<i>Replies</i></span>
                                        <span class="d-block text-muted">9843 <i>Views</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)">
                                                    Sed felis eros, facilisis eu cursus at, efficitur et felis
                                                </a>
                                                <div class="text-muted small text-truncate">
                                                    Today, 05:25 <a href="javascript:void(0)">katty60</a>
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
                                        <div class="d-flex flex-column">
                                            <a class="fs-lg fw-500 d-flex align-items-start"
                                               href="{{ route('second', ['forum', 'discussion']) }}">
                                                Nullam in dictum lacus. Nulla auctor hendrerit purus, sit amet lacinia
                                                tellus placerat nec
                                            </a>
                                            <div class="d-block text-muted fs-sm">
                                                Started by <a href="javascript:void(0);">c_lantern</a> on January 21,
                                                2019
                                                @12:30PM
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">38<i>Replies</i></span>
                                        <span class="d-block text-muted">55 <i>Views</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)">
                                                    Phasellus quis sem diam. Sed commodo metus in ultrices consequat
                                                </a>
                                                <div class="text-muted small text-truncate">
                                                    Today, 05:25 <a href="javascript:void(0)">laigma76</a>
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
                                        <div class="d-flex flex-column">
                                            <a class="fs-lg fw-500 d-flex align-items-start"
                                               href="{{ route('second', ['forum', 'discussion']) }}">
                                                Sit amet lacinia tellus placerat nec. Nunc condimentum
                                            </a>
                                            <div class="d-block text-muted fs-sm">
                                                Started by <a href="javascript:void(0);">lambert89</a> on March 12, 2019
                                                @7:30AM
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-2 col-xl-1 d-none d-md-block">
                                    <div class="p-3 p-md-3 fs-sm">
                                        <span class="d-block text-muted">24<i>Replies</i></span>
                                        <span class="d-block text-muted">100 <i>Views</i></span>
                                    </div>
                                </div>
                                <div class="col-8 col-md-3 d-none d-md-block">
                                    <div class="p-3 p-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-1 min-width-0 fs-sm">
                                                <a class="d-block text-truncate" href="javascript:void(0)">
                                                    ullamcorper cursus. Proin sodales odio sed aliquet pulvinar. Duis
                                                    ipsum
                                                    erat, ultricies a dolor non, tempor dictum ante
                                                </a>
                                                <div class="text-muted small text-truncate">
                                                    Today, 05:25 <a href="javascript:void(0)">laigma76</a>
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
            <ul class="pagination pagination-sm mt-3">
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
        </div>
    </div>

    <div class="modal fade" id="newThreadModal" tabindex="-1" aria-labelledby="newThreadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newThreadModalLabel">Create New Thread</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="threadTitle" class="form-label">Thread Title</label>
                            <input type="text" class="form-control" id="threadTitle"
                                   placeholder="Enter a descriptive title for your thread">
                        </div>

                        <div class="mb-3">
                            <label for="threadCategory" class="form-label">Category</label>
                            <select class="form-select" id="threadCategory">
                                <option selected>Account information &amp; Security</option>
                                <option>General Discussion</option>
                                <option>Troubleshooting</option>
                                <option>Feature Requests</option>
                                <option>Product Announcements</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="threadTags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="threadTags"
                                   placeholder="Add tags separated by commas">
                            <div class="form-text">Optional tags to help categorize your thread</div>
                        </div>

                        <div class="mb-3">
                            <label for="threadContent" class="form-label">Thread Content</label>
                            <!-- <div class="btn-toolbar mb-2" role="toolbar">
                <div class="btn-group me-2" role="group">
                    <button type="button" class="btn btn-outline-secondary"><i class="fal fa-bold"></i></button>
                    <button type="button" class="btn btn-outline-secondary"><i class="fal fa-italic"></i></button>
                    <button type="button" class="btn btn-outline-secondary"><i class="fal fa-underline"></i></button>
                    <button type="button" class="btn btn-outline-secondary"><i class="fal fa-strikethrough"></i></button>
                </div>
                <div class="btn-group me-2" role="group">
                    <button type="button" class="btn btn-outline-secondary"><i class="fal fa-list-ul"></i></button>
                    <button type="button" class="btn btn-outline-secondary"><i class="fal fa-list-ol"></i></button>
                    <button type="button" class="btn btn-outline-secondary"><i class="fal fa-indent"></i></button>
                    <button type="button" class="btn btn-outline-secondary"><i class="fal fa-outdent"></i></button>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary"><i class="fal fa-link"></i></button>
                    <button type="button" class="btn btn-outline-secondary"><i class="fal fa-image"></i></button>
                    <button type="button" class="btn btn-outline-secondary"><i class="fal fa-code"></i></button>
                </div>
            </div> -->
                            <textarea class="form-control" id="threadContent" rows="8"
                                      placeholder="Describe your topic in detail"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="threadAttachments" class="form-label">Attachments</label>
                            <input class="form-control" type="file" id="threadAttachments" multiple>
                            <div class="form-text">Max file size: 2MB. Allowed formats: jpg, png, gif, pdf, doc, zip
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="notifyReplies">
                            <label class="form-check-label" for="notifyReplies">Notify me when someone replies</label>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="stickyThread">
                            <label class="form-check-label" for="stickyThread">Make this thread sticky</label>
                            <div class="form-text">Admin approval required</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success">Create Thread</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
