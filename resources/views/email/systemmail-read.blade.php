@extends('layouts.vertical', ['pageTitle' => 'Email Design','extraClass' => 'd-flex layout-trimmed'])

@section('css')
@endsection

@section('content')
    <!-- notice the utilities added to the wrapper below -->
    <div class="d-flex flex-grow-1 p-0 w-100">
        <!-- left slider panel : must have unique ID-->
        <div class="flex-wrap flex-shrink-0 position-relative slide-on-mobile slide-on-mobile-left" id="js-slide-left">
            <div class="position-absolute top-0 bottom-0 w-100">
                <div class="d-flex h-100 flex-column px-2">
                    <div class="px-4 py-3 align-items-center">
                        <div aria-label="Button group with nested dropdown" class="btn-group btn-group-sm w-100"
                             role="group">
                            <button class="btn btn-danger fs-lg waves-effect waves-themed"
                                    data-bs-target="#default-example-modal-lg-center" data-bs-toggle="modal"
                                    type="button">Compose
                            </button>
                            <div class="btn-group" role="group">
                                <button aria-expanded="false" class="btn btn-danger dropdown-toggle px-2 js-waves-off"
                                        data-bs-toggle="dropdown" id="btnGroupDrop1" type="button"></button>
                                <div aria-labelledby="btnGroupDrop1" class="dropdown-menu p-0">
                                    <a class="dropdown-item" href="#">Work group A</a>
                                    <a class="dropdown-item" href="#">New page</a>
                                    <a class="dropdown-item" href="#">Edit signature</a>
                                    <div class="dropdown-divider m-0"></div>
                                    <a class="dropdown-item" href="#"><i class="fas fa-plus width-1"></i> Add more</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 px-4">
                        <a class="dropdown-item d-flex justify-content-between rounded-pill p-2" href="{{ route('second', ['email', 'systemmail']) }}">
                            <div>
                                <i class="fas fa-folder-open width-1"></i>Inbox
                            </div>
                            <div
                                class="fw-normal badge bg-primary align-self-center rounded-circle if-empty-display-none">
                                7
                            </div>
                        </a>
                        <a class="dropdown-item d-flex justify-content-between rounded-pill p-2" href="{{ route('second', ['email', 'systemmail']) }}">
                            <div>
                                <i class="fas fa-star width-1"></i>Starred
                            </div>
                        </a>
                        <a class="dropdown-item d-flex justify-content-between rounded-pill p-2" href="{{ route('second', ['email', 'systemmail']) }}">
                            <div>
                                <i class="fas fa-edit width-1"></i>Drafts
                            </div>
                            <div
                                class="fw-normal badge bg-primary align-self-center rounded-circle if-empty-display-none">
                                5
                            </div>
                        </a>
                        <a class="dropdown-item d-flex justify-content-between rounded-pill p-2" href="{{ route('second', ['email', 'systemmail']) }}">
                            <div>
                                <i class="fas fa-paper-plane width-1"></i>Sent
                            </div>
                        </a>
                        <a class="dropdown-item d-flex justify-content-between rounded-pill p-2" href="{{ route('second', ['email', 'systemmail']) }}">
                            <div>
                                <i class="fas fa-trash width-1"></i>Trash
                            </div>
                        </a>
                        <a class="dropdown-item d-flex justify-content-between rounded-pill p-2" href="{{ route('second', ['email', 'systemmail']) }}">
                            <div>
                                <i class="fas fa-exclamation-triangle width-1"></i>Junk
                            </div>
                        </a>
                    </div>
                    <div class="px-4 fs-xs py-3 fw-300"> 1.5 GB (10%) of 15 GB used
                        <div class="progress mt-1"
                             style="height: 7px;">
                            <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="11"
                                 class="progress-bar bg-primary"
                                 role="progressbar" style="width: 11%;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- left slider panel backdrop : activated on mobile, must be place immideately after left slider closing tag -->
        <div class="slide-backdrop" data-action="toggle-swap" data-target="#js-slide-left"
             data-toggleclass="slide-on-mobile-left-show"></div>
        <!-- middle content area -->
        <div class="d-flex flex-column flex-grow-1 flex-0 overflow-x-auto">
            <div class="flex-grow-0">
                <!-- end inbox title -->
                <!-- inbox button shortcut -->
                <div
                    class="d-flex flex-wrap align-items-center ps-2 pe-1 ps-sm-2 py-2 px-sm-4 border border-top-0 border-start-0 border-end-0">
                    <div class="flex-1 d-flex align-items-center gap-1">
                        <a class="btn btn-icon rounded-circle me-2 me-lg-2" href="{{ route('second', ['email', 'systemmail']) }}">
                            <i class="fas fa-arrow-left fs-lg"></i>
                        </a>
                        <button class="btn btn-icon rounded-circle" type="button">
                            <i class="fas fa-redo fs-md"></i>
                        </button>
                        <button class="btn btn-icon rounded-circle" type="button">
                            <i class="fas fa-exclamation-circle fs-md"></i>
                        </button>
                        <button class="btn btn-icon rounded-circle" type="button">
                            <i class="fas fa-trash fs-md"></i>
                        </button>
                    </div>
                    <div class="text-muted me-1 me-lg-3 ms-auto hidden-mobile"> 1 - 50 <span class="hidden-lg-down"> of 135
                        </span>
                    </div>
                    <div class="d-flex flex-wrap hidden-mobile">
                        <button class="btn btn-icon rounded-circle" type="button"><i
                                class="fas fa-chevron-left"></i></button>
                        <button class="btn btn-icon rounded-circle" type="button"><i
                                class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                <!-- end inbox button shortcut -->
            </div>
            <div
                class="d-flex align-items-center ps-2 pe-3 py-3 ps-sm-3 pe-sm-4 py-sm-4 pe-lg-4 px-lg-6 py-lg-3 flex-shrink-0 gap-2">
                <!-- button for mobile -->
                <button
                    class="pl-3 pr-3 py-2 d-flex d-lg-none align-items-center justify-content-center btn waves-effect waves-themed"
                    data-action="toggle-swap" data-target="#js-slide-left" data-toggleclass="slide-on-mobile-left-show"
                    type="button">
                    <i class="fal fa-ellipsis-v h1 mb-0"></i>
                </button>
                <!-- end button for mobile -->
                <h5 class="mb-0 ms-1 ms-lg-3 me-auto"> Re: New security codes </h5>
                <span class="badge bg-primary hidden-mobile align-self-center">INBOX</span>
                <div class="d-flex position-relative">
                    <a class="btn btn-icon rounded-circle fs-lg" href="#" title="starred">
                        <svg class="sa-icon sa-medium">
                            <use href="/icons/sprite.svg#star"></use>
                        </svg>
                    </a>
                    <a aria-expanded="true" class="btn btn-icon rounded-circle fs-lg" data-bs-target=".js-collapse"
                       data-bs-toggle="collapse" href="javascript:void(0);">
                        <i class="fas fa-arrows-v"></i>
                    </a>
                    <button class="btn btn-icon rounded-circle fs-lg hidden-mobile" type="button">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
            <div class="flex-grow-1 position-relative">
                <!-- message 1 -->
                <div
                    class="d-flex flex-column border-faded border-top-0 border-end-0 border-start-0 py-3 px-3 px-sm-4 px-lg-0 me-0 me-lg-4 flex-shrink-0"
                    id="msg-01">
                    <div class="d-flex align-items-center flex-row">
                        <div class="ms-0 me-3 mx-lg-3">
                            <img alt="Melissa Ayre" class="profile-image profile-image-md rounded-circle"
                                 src="/img/demo/avatars/avatar-c.png"/>
                        </div>
                        <div aria-expanded="true"
                             class="fw-500 flex-grow-1 flex-shrink-0 d-flex flex-column cursor-pointer"
                             data-bs-target="#msg-01 &gt; .js-collapse" data-bs-toggle="collapse">
                            <div class="fs-lg"> Melissa Ayre <span
                                    class="fs-nano text-muted opacity-75 fw-400 text-truncate width-xs">&lt;melissaa@example.com&gt;</span>
                            </div>
                            <div class="fs-nano text-muted opacity-75"> to Me</div>
                        </div>
                        <div class="text-muted fs-sm"> 1:00 AM <span class="hidden-mobile">(12 hours
                                ago)</span>
                        </div>
                        <div class="collapsed-reveal">
                            <a class="btn btn-icon fs-lg rounded-circle waves-effect waves-themed"
                               href="javascript:void(0);">
                                <i class="fal fa-reply"></i>
                            </a>
                        </div>
                    </div>
                    <div class="js-collapse collapse show">
                        <div class="ps-lg-3 ms-lg-6 pt-3 pb-4 fs-md">
                            We've taken customer feedback on board and are pleased to announce that the
                            the industry's
                            most recommended and secure option for Two-Factor Authentication (2FA) is
                            now available to
                            use on our site. So, if you've tried our previous 2FA methods and have not
                            been convinced,
                            why not try our new Time-Based One-Time Password (TOTP)? It's super easy to
                            use, and it works
                            with many third-party 2FA apps you might already have on your phone. We're
                            positive you'll love
                            the new improved experience.
                        </div>
                        <div class="ps-lg-3 ms-lg-6 pt-3 pb-4">
                            <i class="fs-sm text-muted">Attachments:</i>
                            <div
                                class="d-flex flex-wrap align-items-center gap-2 py-3 position-relative if-empty-display-none">
                                <div class="alert m-0 p-0 badge bg-primary-50 border-primary ps-2">
                                    security_codes.pdf
                                    <button
                                        class="btn btn-icon btn-xs ms-1 rounded-0 border border-primary border-top-0 border-bottom-0 border-end-0 waves-effect waves-themed"
                                        data-bs-dismiss="alert">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="alert m-0 p-0 badge bg-primary-50 border-primary ps-2">
                                    agreement.pdf
                                    <button
                                        class="btn btn-icon btn-xs ms-1 rounded-0 border border-primary border-top-0 border-bottom-0 border-end-0 waves-effect waves-themed"
                                        data-bs-dismiss="alert">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <a class="fs-xs text-secondary" href="#">show 3 more</a>
                            </div>
                            <div class="fs-sm d-flex align-items-center gap-2">
                                <a href="#" title="Cell A-0012">
                                    <span class="d-block img-share"
                                          style="background-image:url('/img/thumbs/pic-7.png'); background-size: cover;"></span>
                                </a>
                                <a href="#" title="Patient A-473 saliva">
                                    <span class="d-block img-share"
                                          style="background-image:url('/img/thumbs/pic-8.png'); background-size: cover;"></span>
                                </a>
                                <a href="#" title="Patient A-473 Membrane O.C">
                                    <span class="d-block img-share"
                                          style="background-image:url('/img/thumbs/pic-12.png'); background-size: cover;"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- message 2 -->
                <div
                    class="d-flex flex-column border-faded border-top-0 border-end-0 border-start-0 py-3 px-3 px-sm-4 px-lg-0 me-0 me-lg-4 flex-shrink-0"
                    id="msg-02">
                    <div class="d-flex align-items-center flex-row">
                        <div class="ms-0 me-3 mx-lg-3">
                            <img alt="Sunny A." class="profile-image profile-image-md rounded-circle"
                                 src="/img/demo/avatars/avatar-admin.png"/>
                        </div>
                        <div aria-expanded="true"
                             class="fw-500 flex-grow-1 flex-shrink-0 d-flex flex-column cursor-pointer"
                             data-bs-target="#msg-02 &gt; .js-collapse" data-bs-toggle="collapse">
                            <div class="fs-lg"> Sunny A. <span
                                    class="fs-nano text-muted opacity-75 fw-400 text-truncate width-xs">me</span>
                            </div>
                            <div class="fs-nano text-muted opacity-75"> to
                                &lt;melissa.ayre@getwebora.com&gt;
                            </div>
                        </div>
                        <div class="text-muted fs-sm"> 1:00 AM <span class="hidden-mobile">(12 hours
                                ago)</span>
                        </div>
                        <div class="collapsed-reveal">
                            <a class="btn btn-icon fs-lg rounded-circle waves-effect waves-themed"
                               href="javascript:void(0);">
                                <i class="fal fa-reply"></i>
                            </a>
                        </div>
                    </div>
                    <div class="js-collapse collapse show">
                        <div class="ps-lg-3 ms-lg-6 pt-3 pb-4 fs-md">
                            We've taken customer feedback on board and are pleased to announce that the
                            the industry's most recommended and secure option for Two-Factor
                            Authentication (2FA) is now available to use on our site.
                            So, if you've tried our previous 2FA methods and have not been convinced,
                            why not try our new Time-Based One-Time Password (TOTP)? It's super easy to
                            use, and it works with many third-party 2FA apps you might already have on
                            your phone. We're positive you'll love the new improved experience.
                            <br/>
                            <br/>
                            Best,
                            <br/>
                            <span class="fw-500 fs-lg d-block l-h-n">Sunny A.</span>
                            <span class="fw-400 fs-nano d-block l-h-n mb-1">Software Engineer</span>
                            <br/>
                            <br/>
                            <div class="text-muted fs-nano">
                                PRIVATE AND CONFIDENTIAL. This e-mail, its contents and attachments are
                                private and confidential and is
                                intended for the recipient only. Any disclosure, copying or unauthorized
                                use of such information is prohibited.
                                If you receive this message in error, please notify us immediately and
                                delete the original and any copies and
                                attachments.
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="d-flex flex-column border-faded border-top-0 border-end-0 border-start-0 py-3 px-3 px-sm-4 px-lg-0 me-0 me-lg-4 flex-shrink-0"
                    id="msg-03">
                    <div class="d-flex align-items-center flex-row">
                        <div class="ms-0 me-3 mx-lg-3">
                            <img alt="Melissa Ayre" class="profile-image profile-image-md rounded-circle"
                                 src="/img/demo/avatars/avatar-c.png"/>
                        </div>
                        <div aria-expanded="true"
                             class="fw-500 flex-grow-1 flex-shrink-0 d-flex flex-column cursor-pointer"
                             data-bs-target="#msg-03 &gt; .js-collapse" data-bs-toggle="collapse">
                            <div class="fs-lg"> Melissa Ayre <span
                                    class="fs-nano text-muted opacity-75 fw-400 text-truncate width-xs">&lt;melissaa@example.com&gt;</span>
                            </div>
                            <div class="fs-nano text-muted opacity-75"> to Me</div>
                        </div>
                        <div class="text-muted fs-sm"> 1:00 AM <span class="hidden-mobile">(12 hours
                                ago)</span>
                        </div>
                        <div class="collapsed-reveal">
                            <a class="btn btn-icon fs-lg rounded-circle waves-effect waves-themed"
                               href="javascript:void(0);">
                                <i class="fal fa-reply"></i>
                            </a>
                        </div>
                    </div>
                    <div class="js-collapse collapse show">
                        <div class="ps-lg-3 ms-lg-6 pt-3 pb-4 fs-md"> We've taken customer feedback on
                            board and are pleased to announce that the the industry's most recommended
                            and secure option for Two-Factor Authentication (2FA) is now available to
                            use on our site. So, if you've tried our previous 2FA methods and have not
                            been convinced, why not try our new Time-Based One-Time Password (TOTP)?
                            It's super easy to use, and it works with many third-party 2FA apps you
                            might already have on your phone. We're positive you'll love the new
                            improved experience.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal : Compose mail-->
    <div aria-hidden="true" class="modal fade" id="default-example-modal-lg-center" style="display: none;"
         tabindex="-1">
        <div class="modal-dialog modal-lg modal-fullscreen-sm-down modal-dialog-centered modal-dialog-scrollable"
             role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger-500">
                    <div class="modal-title fw-600">Compose Mail</div>
                    <button aria-label="Close" class="btn btn-system ms-auto" data-bs-dismiss="modal" type="button">
                        <svg class="sa-icon sa-icon-light sa-icon-2x">
                            <use href="/icons/sprite.svg#x"></use>
                        </svg>
                    </button>
                </div>
                <div class="modal-header border-bottom-0 p-0">
                    <div class="px-3 w-100 position-relative">
                        <input
                            class="form-control bg-transparent border-top-0 border-start-0 border-end-0 px-0 rounded-0 fs-md mt-2 pe-5"
                            id="message-to" placeholder="Recipients" tabindex="2" type="text"
                            value="carlie@gmail.com"/>
                        <a class="position-absolute end-0 top-0 mt-3 me-4" data-bs-original-title="Add Cc recipients"
                           data-bs-placement="bottom" data-bs-toggle="tooltip" href="javascript:void(0)">Cc</a>
                        <input
                            class="form-control bg-transparent border-top-0 border-left-0 border-right-0 px-0 rounded-0 fs-md mt-2 d-none"
                            id="message-to-cc" placeholder="Cc" tabindex="3" type="text"/>
                        <input
                            class="form-control bg-transparent fw-bold border-top-0 border-start-0 border-end-0 px-0 rounded-0 fs-md mt-2"
                            placeholder="Subject" tabindex="4" type="text" value="Re: New security codes (2)"/>
                        <div
                            class="d-flex flex-wrap align-items-center gap-2 py-3 position-relative if-empty-display-none"
                            id="message-attachments">
                            <div class="alert m-0 p-0 badge bg-primary-50 border-primary ps-2">
                                security_codes.pdf
                                <button
                                    class="btn btn-icon btn-xs ms-1 rounded-0 border border-primary border-top-0 border-bottom-0 border-end-0 waves-effect waves-themed"
                                    data-bs-dismiss="alert" type="button">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="alert m-0 p-0 badge bg-primary-50 border-primary ps-2">
                                agreement.pdf
                                <button
                                    class="btn btn-icon btn-xs ms-1 rounded-0 border border-primary border-top-0 border-bottom-0 border-end-0 waves-effect waves-themed"
                                    data-bs-dismiss="alert" type="button">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <a class="fs-xs text-secondary" href="#">show 3 more</a>
                            <a class="position-absolute end-0 top-0 mt-3 me-2" href="javascript:void(0)">
                                <i class="fas fa-paperclip"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-body custom-scrollbar p-0">
                    <div class="panel-container show rounded-0 flex-1 d-flex flex-column">
                        <div class="flex-1" style="overflow-y: auto;">
                            <div class="form-control rounded-0 w-100 h-100 border-0 py-3 height-sm"
                                 contenteditable="true"
                                 id="fake_textarea" tabindex="5">
                                <p>Hi Carlie,</p>
                                <p>I'm writing to you about the new security codes. Please find the
                                    updated codes attached
                                    for your reference. Ensure you update your system with these codes
                                    by tomorrow to maintain security
                                    compliance. Let me know if you encounter any issues during the
                                    process.</p>
                                <p>Best regards,</p>
                                <!-- sender info -->
                                <div class="d-flex d-column align-items-start mb-3 gap-2">
                                    <img alt="SmartAdmin WebApp" class="mt-1 rounded-circle width-2"
                                         src="/img/demo/avatars/avatar-admin.png"/>
                                    <div class="border-left pl-3">
                                        <span class="fw-500 fs-lg d-block l-h-n">Sunny A.</span>
                                        <span class="fw-400 fs-nano d-block l-h-n mb-1">Software
                                            Engineer</span>
                                    </div>
                                </div>
                                <!-- footer -->
                                <div class="text-muted fs-nano">
                                    ​PRIVATE AND CONFIDENTIAL. This e-mail, its contents and attachments
                                    are private and confidential and is intended for the recipient only.
                                    Any disclosure, copying or unauthorized use of such information is
                                    prohibited. If you receive this message in error, please notify us
                                    immediately and delete the original and any copies and attachments.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex align-items-center flex-wrap flex-shrink-0 me-auto gap-2 flex-grow-1">
                        <button class="btn btn-icon fs-xl" data-bs-original-title="Formatting options"
                                data-bs-placement="top" data-bs-toggle="tooltip" type="button">
                            <i class="fas fa-font color-fusion-300"></i>
                        </button>
                        <button class="btn btn-icon fs-xl" data-bs-original-title="Attach files" data-bs-placement="top"
                                data-bs-toggle="tooltip" type="button">
                            <i class="fas fa-paperclip color-fusion-300"></i>
                        </button>
                        <button class="btn btn-icon fs-xl hidden-mobile" data-bs-original-title="Insert photo"
                                data-bs-placement="top" data-bs-toggle="tooltip" type="button">
                            <i class="fas fa-camera color-fusion-300"></i>
                        </button>
                        <div class="ms-auto">
                            <button class="btn btn-icon" data-bs-original-title="Disregard draft"
                                    data-bs-toggle="tooltip" data-placement="top" type="button">
                                <i class="fas fa-trash color-fusion-300"></i>
                            </button>
                            <button class="btn btn-icon width-1" data-bs-original-title="More options"
                                    data-bs-placement="top" data-bs-toggle="tooltip" type="button">
                                <i class="fas fa-ellipsis-v-alt color-fusion-300"></i>
                            </button>
                        </div>
                        <button class="btn btn-secondary btn-sm" type="button">Save Draft</button>
                        <button class="btn btn-primary btn-sm" type="button">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
