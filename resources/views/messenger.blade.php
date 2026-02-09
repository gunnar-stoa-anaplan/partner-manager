@extends('layouts.vertical', ['pageTitle' => 'Messenger & Chat','extraClass' => 'layout-trimmed'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Messenger & Chat', 'pageSubTitle1' => 'Communication', 'pageSubTitle2'=> 'SmartAdmin', 'pageSubText' => 'Can be the starting page for your application...'])
@endsection

@section('content')
    <div class="d-flex flex-grow-1 p-0 w-100 h-100">
        <!-- left sidebar -->
        <div class="d-flex flex-column flex-grow-1 flex-0 overflow-x-auto h-100"
             style="min-height: calc(100vh - (var(--app-header-height) + 56px));">
            <div
                class="d-flex align-items-center p-0 border-faded border-top-0 border-start-0 border-end-0 flex-shrink-0">
                <div class="d-flex align-items-center w-100 ps-3 px-lg-5 py-2 position-relative">
                    <div class="d-flex flex-row align-items-center mt-1 mb-1">
                        <div class="me-2 d-inline-block">
                                <span
                                    class="rounded-circle profile-image d-block position-relative status status-success"
                                    style="background-image:url('/img/demo/avatars/avatar-b.png'); background-size: cover;"></span>
                        </div>
                        <div class="info-card-text">
                            <div class="text-truncate text-truncate-lg">
                                Oliver Kopyov
                            </div>
                            <small class="text-muted text-truncate text-truncate-md">Online</small>
                        </div>
                    </div>
                    <div class="d-flex flex-row gap-2 ms-auto">
                        <a class="btn btn-icon rounded-circle" data-toggle="button" href="javascript:void(0);">
                            <svg class="sa-icon sa-icon-2x sa-bold sa-icon-subtlelight">
                                <use href="/icons/sprite.svg#phone"></use>
                            </svg>
                        </a>
                        <a class="btn btn-icon rounded-circle" href="javascript:void(0);">
                            <svg class="sa-icon sa-icon-2x sa-bold sa-icon-subtlelight sa-nofill">
                                <use href="/icons/sprite.svg#video"></use>
                            </svg>
                        </a>
                        <a class="btn btn-icon rounded-circle d-flex d-lg-none me-4 position-relative js-waves-off"
                           data-action="toggle-swap" data-target="#js-slide-right"
                           data-toggleclass="slide-on-mobile-right-show" href="javascript:void(0);">
                            <span class="badge badge-icon top-0 end-0">4</span>
                            <svg class="sa-icon sa-icon-2x sa-bold sa-icon-subtlelight">
                                <use href="/icons/sprite.svg#message-circle"></use>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="flex-wrap align-items-center flex-grow-1 position-relative bg-gray-50">
                <div class="msgr position-absolute top-0 bottom-0 w-100 overflow-hidden d-flex flex-column">
                    <!-- chat container -->
                    <div class="w-100 p-4 px-lg-5 bg-subtlelight-fade custom-scroll flex-grow-1"
                         id="chat_container">
                        <div
                            class="alert bg-warning bg-opacity-10 opacity-75 border border-warning border-opacity-50 fs-nano px-3 py-2">
                            Protect your privacy by never sharing personal details like your phone number, email, or
                            passwords. Stay alert for scams,
                            suspicious links, or requests for money, and report any inappropriate behavior
                            immediately. Avoid hate speech, harassment,
                            or any illegal activities, as they are strictly prohibited. For all other rules, please
                            refer to the <a href="#" target="_blank">Terms of Service</a>.
                        </div>
                        <!-- start .chat-segment -->
                        <div class="chat-segment chat-segment-sent">
                            <div class="chat-message">
                                <p>
                                    Hey Tracey, did you get my files?
                                </p>
                            </div>
                            <div class="text-end fw-300 text-muted mt-1 fs-xs">
                                3:00 pm
                            </div>
                        </div>
                        <!--  end .chat-segment -->
                        <!-- start .chat-segment -->
                        <div class="chat-segment chat-segment-get">
                            <div class="chat-message">
                                <p>
                                    Hi
                                </p>
                                <p>
                                    Sorry going through a busy time in office. Yes I analyzed the solution.
                                </p>
                                <p>
                                    It will require some resource, which I could not manage.
                                </p>
                            </div>
                            <div class="fw-300 text-muted mt-1 fs-xs">
                                3:24 pm
                            </div>
                        </div>
                        <!--  end .chat-segment -->
                        <!-- start .chat-segment -->
                        <div class="chat-segment chat-segment-sent chat-start">
                            <div class="chat-message">
                                <p>
                                    Okay
                                </p>
                            </div>
                        </div>
                        <!--  end .chat-segment -->
                        <!-- start .chat-segment -->
                        <div class="chat-segment chat-segment-sent chat-end">
                            <div class="chat-message">
                                <p>
                                    Sending you some dough today, you can allocate the resources to this project.
                                </p>
                            </div>
                            <div class="text-end fw-300 text-muted mt-1 fs-xs">
                                3:26 pm
                            </div>
                        </div>
                        <!--  end .chat-segment -->
                        <!-- start .chat-segment -->
                        <div class="chat-segment chat-segment-get chat-start">
                            <div class="chat-message">
                                <p>
                                    Perfect. Thanks a lot!
                                </p>
                            </div>
                        </div>
                        <!--  end .chat-segment -->
                        <!-- start .chat-segment -->
                        <div class="chat-segment chat-segment-get">
                            <div class="chat-message">
                                <p>
                                    I will have them ready by tonight.
                                </p>
                            </div>
                        </div>
                        <!--  end .chat-segment -->
                        <!-- start .chat-segment -->
                        <div class="chat-segment chat-segment-get chat-end">
                            <div class="chat-message">
                                <p>
                                    Cheers
                                </p>
                            </div>
                        </div>
                        <!--  end .chat-segment -->
                        <!-- start .chat-segment for timestamp -->
                        <div class="chat-segment d-flex justify-content-center">
                            <div class="time-stamp text-center my-2 fw-400 badge bg-secondary text-white"> Jun 20
                            </div>
                        </div>
                        <!--  end .chat-segment for timestamp -->
                    </div>
                    <!-- chat input -->
                    <div class="d-flex flex-column border border-bottom-0 border-end-0 border-start-0">
                        <div class="p-3 px-lg-4 d-flex gap-1">
                            <div class="d-flex gap-1 flex-row align-items-center flex-wrap flex-shrink-0">
                                <button aria-expanded="false" class="btn btn-icon fs-xl" data-bs-toggle="dropdown"
                                        type="button">
                                    <svg class="sa-icon sa-bold sa-icon-subtlelight">
                                        <use href="/icons/sprite.svg#smile"></use>
                                    </svg>
                                </button>
                                <div class="dropdown-menu text-center rounded-pill overflow-hidden"
                                     data-popper-placement="top-start">
                                    <div class="d-flex align-items-center px-2 py-0 gap-1">
                                        <a class="emoji emoji--like" data-bs-original-title="Like"
                                           data-bs-placement="top" data-bs-toggle="tooltip"
                                           href="javascript:void(0);" title="">
                                            <div class="emoji__hand">
                                                <div class="emoji__thumb"></div>
                                            </div>
                                        </a>
                                        <a class="emoji emoji--love" data-bs-original-title="Love"
                                           data-bs-placement="top" data-bs-toggle="tooltip"
                                           href="javascript:void(0);" title="">
                                            <div class="emoji__heart"></div>
                                        </a>
                                        <a class="emoji emoji--haha" data-bs-original-title="Haha"
                                           data-bs-placement="top" data-bs-toggle="tooltip"
                                           href="javascript:void(0);" title="">
                                            <div class="emoji__face">
                                                <div class="emoji__eyes"></div>
                                                <div class="emoji__mouth">
                                                    <div class="emoji__tongue"></div>
                                                </div>
                                            </div>
                                        </a>
                                        <a class="emoji emoji--yay" data-bs-original-title="Yay"
                                           data-bs-placement="top" data-bs-toggle="tooltip"
                                           href="javascript:void(0);" title="">
                                            <div class="emoji__face">
                                                <div class="emoji__eyebrows"></div>
                                                <div class="emoji__mouth"></div>
                                            </div>
                                        </a>
                                        <a class="emoji emoji--wow" data-bs-original-title="Wow"
                                           data-bs-placement="top" data-bs-toggle="tooltip"
                                           href="javascript:void(0);" title="">
                                            <div class="emoji__face">
                                                <div class="emoji__eyebrows"></div>
                                                <div class="emoji__eyes"></div>
                                                <div class="emoji__mouth"></div>
                                            </div>
                                        </a>
                                        <a class="emoji emoji--sad" data-bs-original-title="Sad"
                                           data-bs-placement="top" data-bs-toggle="tooltip"
                                           href="javascript:void(0);" title="">
                                            <div class="emoji__face">
                                                <div class="emoji__eyebrows"></div>
                                                <div class="emoji__eyes"></div>
                                                <div class="emoji__mouth"></div>
                                            </div>
                                        </a>
                                        <a class="emoji emoji--angry" data-bs-original-title="Angry"
                                           data-bs-placement="top" data-bs-toggle="tooltip"
                                           href="javascript:void(0);" title="">
                                            <div class="emoji__face">
                                                <div class="emoji__eyebrows"></div>
                                                <div class="emoji__eyes"></div>
                                                <div class="emoji__mouth"></div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <button class="btn btn-icon fs-xl" data-bs-original-title="Attach Files or Photos"
                                        data-bs-placement="top" data-bs-toggle="tooltip" type="button">
                                    <svg class="sa-icon sa-bold sa-icon-subtlelight">
                                        <use href="/icons/sprite.svg#file"></use>
                                    </svg>
                                </button>
                            </div>
                            <input class="form-control" id="msgr_input" placeholder="Type your message here..."
                                   type="text"/>
                            <button class="btn btn-icon fs-xl width-1 flex-shrink-0" data-bs-original-title="Voice"
                                    data-bs-placement="top" data-bs-toggle="tooltip" type="button">
                                <svg class="sa-icon sa-bold sa-icon-subtlelight">
                                    <use href="/icons/sprite.svg#mic"></use>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- right sidebar -->
        <div
            class="flex-wrap flex-shrink-0 position-relative slide-on-mobile slide-on-mobile-right border-faded border-end-0 border-top-0 border-bottom-0"
            id="js-slide-right" style="--sidepanel-width: 20rem;">
            <div class="d-flex flex-column h-100">
                <!-- Story circles - horizontal avatar row -->
                <h6 class="px-3 pt-3 fw-semi-bold">Stories</h6>
                <div class="p-3">
                    <div class="d-flex gap-2 overflow-auto pb-2 story-scroll">
                        <div class="story-circle has-story text-center">
                                <span class="rounded-circle profile-image d-block position-relative"
                                      style="background-image:url('/img/demo/avatars/avatar-d.png'); background-size: cover;"></span><span
                                class="fs-nano">Emily</span>
                        </div>
                        <div class="story-circle has-story text-center">
                                <span class="rounded-circle profile-image d-block position-relative"
                                      style="background-image:url('/img/demo/avatars/avatar-a.png'); background-size: cover;"></span><span
                                class="fs-nano">Rose</span>
                        </div>
                        <div class="story-circle text-center">
                                <span class="rounded-circle profile-image d-block position-relative"
                                      style="background-image:url('/img/demo/avatars/avatar-j.png'); background-size: cover;"></span><span
                                class="fs-nano">Martha</span>
                        </div>
                        <div class="story-circle has-story text-center">
                                <span class="rounded-circle profile-image d-block position-relative"
                                      style="background-image:url('/img/demo/avatars/avatar-k.png'); background-size: cover;"></span><span
                                class="fs-nano">Kevin</span>
                        </div>
                        <div class="story-circle text-center">
                                <span class="rounded-circle profile-image d-block position-relative"
                                      style="background-image:url('/img/demo/avatars/avatar-b.png'); background-size: cover;"></span><span
                                class="fs-nano">Oliver</span>
                        </div>
                        <div class="story-circle text-center">
                                <span class="rounded-circle profile-image d-block position-relative"
                                      style="background-image:url('/img/demo/avatars/avatar-c.png'); background-size: cover;"></span><span
                                class="fs-nano">Sara</span>
                        </div>
                    </div>
                </div>
                <h6 class="px-3 py-2 fw-semi-bold">Conversations</h6>
                <!-- Contacts list -->
                <!-- Search contacts -->
                <div class="p-3 pt-0">
                    <input aria-label="Search" class="form-control" placeholder="Search messages..." type="text"/>
                </div>
                <div class="flex-grow-1 position-relative overflow-x-auto custom-scroll">
                    <div class="list-group list-group-flush px-3 gap-1 position-absolute w-100 top-0 bottom-0">
                        <a class="list-group-item list-group-item-action active px-3 py-2 m-0"
                           href="javascript:void(0);">
                            <div class="d-flex">
                                <div class="me-2 align-self-center">
<span class="rounded-circle profile-image profile-image-md d-block position-relative status status-success status-sm"
      style="background-image:url('/img/demo/avatars/avatar-b.png'); background-size: cover;">
</span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0 text-truncate fw-semi-bold">Oliver Kopyov</p>
                                        <small class="text-muted">now</small>
                                    </div>
                                    <p class="mb-0 text-truncate small text-muted">I'm definitely in!</p>
                                </div>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action px-3 py-2 m-0" href="javascript:void(0);">
                            <div class="d-flex w-100">
                                <div class="me-2 align-self-center">
<span class="rounded-circle profile-image profile-image-md d-block position-relative status status-success status-sm"
      style="background-image:url('/img/demo/avatars/avatar-h.png'); background-size: cover;">
</span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0 text-truncate fw-500">101 Study Group</p>
                                        <small class="text-muted">9m</small>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p class="mb-0 text-truncate small fw-600">Kelly sent a sticker</p>
                                        <span class="unread-badge">1</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action px-3 py-2 m-0" href="javascript:void(0);">
                            <div class="d-flex w-100">
                                <div class="me-2 align-self-center">
<span class="rounded-circle profile-image profile-image-md d-block position-relative status status-danger status-sm"
      style="background-image:url('/img/demo/avatars/avatar-c.png'); background-size: cover;">
</span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0 text-truncate">Amy Warrell</p>
                                        <small class="text-muted">37m</small>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p class="mb-0 text-truncate small" style="color:#fa5c7c;">Missed Call</p>
                                        <svg class="sa-icon sa-icon-sm" style="color:#fa5c7c;">
                                            <use href="/icons/sprite.svg#phone"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action px-3 py-2 m-0" href="javascript:void(0);">
                            <div class="d-flex w-100">
                                <div class="me-2 align-self-center">
<span class="rounded-circle profile-image profile-image-md d-block position-relative status status-sm"
      style="background-image:url('/img/demo/avatars/avatar-m.png'); background-size: cover;">
</span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0 text-truncate">Loren Newton</p>
                                        <small class="text-muted">8:24am</small>
                                    </div>
                                    <p class="mb-0 text-truncate small text-muted">See you soon!</p>
                                </div>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action px-3 py-2 m-0" href="javascript:void(0);">
                            <div class="d-flex w-100">
                                <div class="me-2 align-self-center">
<span class="rounded-circle profile-image profile-image-md d-block position-relative status status-sm"
      style="background-image:url('/img/demo/avatars/avatar-h.png'); background-size: cover;">
</span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0 text-truncate">Super Surfers</p>
                                        <small class="text-muted">Mon</small>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <p class="mb-0 text-truncate small text-muted">Tomorrow is great!</p>
                                        <div class="d-flex position-relative height-2">
                                                <span
                                                    class="rounded-circle profile-image profile-image-sm position-absolute end-0 me-n2 me-0 border border-white"
                                                    style="background-image:url('/img/demo/avatars/avatar-a.png'); background-size: cover;"></span>
                                            <span
                                                class="rounded-circle profile-image profile-image-sm position-absolute end-0 me-n2 me-3 border border-white"
                                                style="background-image:url('/img/demo/avatars/avatar-b.png'); background-size: cover;"></span>
                                            <span
                                                class="rounded-circle profile-image profile-image-sm position-absolute end-0 me-5 border border-white"
                                                style="background-image:url('/img/demo/avatars/avatar-c.png'); background-size: cover;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a class="list-group-item list-group-item-action px-3 py-2 m-0" href="javascript:void(0);">
                            <div class="d-flex w-100">
                                <div class="me-2 align-self-center">
<span class="rounded-circle profile-image profile-image-md d-block position-relative status status-sm"
      style="background-image:url('/img/demo/avatars/avatar-k.png'); background-size: cover;">
</span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0 text-truncate">Rodolfo &amp; Leon</p>
                                        <small class="text-muted">Tue</small>
                                    </div>
                                    <p class="mb-0 text-truncate small text-muted">The proposal looks amazing!</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="slide-backdrop" data-action="toggle-swap" data-target="#js-slide-right"
             data-toggleclass="slide-on-mobile-right-show"></div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/messenger.js'])
@endsection
