@extends('layouts.vertical', ['pageTitle' => 'Control Center'])

@section('css')
    @vite(['node_modules/jsvectormap/dist/jsvectormap.min.css'])
@endsection

@section('page-title')
    <div class="d-flex align-items-end mb-4">
        <div>
            @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Control Center', 'pageSubTitle1' => 'Insights', 'pageSubTitle2'=> 'Dashboards'])
        </div>

        <div class="ms-auto d-none d-sm-flex align-items-center ">
            <div class="d-flex align-items-center">
                <div class="d-inline-flex flex-column justify-content-center me-2">
                                    <span class="fw-500 fs-xs d-block">
                                        <small>EXPENSES</small>
                                    </span>
                    <span class="fw-500 fs-xl d-block text-primary"> $47,000 </span>
                </div>
                <div class="d-none d-md-inline-flex">
                                <span class="peity-bar d-none"
                                      data-peity='{ "fill": ["var(--primary-500)"], "height": 34, "width": "85" }'>3, 4, 3, 6, 7, 3, 3, 6, 2, 6, 4</span>
                </div>
            </div>
            <div class="d-flex align-items-center border border-top-0 border-bottom-0 border-end-0 ms-3 ps-3">
                <div class="d-inline-flex flex-column justify-content-center me-2">
                                    <span class="fw-500 fs-xs d-block">
                                        <small>PROFIT</small>
                                    </span>
                    <span class="fw-500 fs-xl d-block text-danger"> $38,500 </span>
                </div>
                <div class="d-none d-md-inline-flex">
                                <span class="peity-bar d-none"
                                      data-peity='{ "fill": ["var(--danger-500)"], "height": 34, "width": "85" }'>1, 4, 3, 6, 5, 3, 9, 6, 5, 9, 7</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <!-- Live Feeds -->
            <div class="panel panel-icon panel-locked" id="panel-1">
                <div class="panel-hdr">
                    <h2> Live <span class="fw-light"><i>Feeds</i></span>
                    </h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content border-faded border-end-0 border-start-0 border-top-0 pt-0">
                        <div class="row g-0">
                            <!-- Live Feeds -->
                            <div class="col-lg-7 col-xl-8">
                                <!-- Live Feeds Chart-->
                                <div class="apex-charts" id="apex-chart-1"></div>
                            </div>
                            <!-- My Tasks -->
                            <div class="col-lg-5 col-xl-4 ps-lg-3">
                                <div class="d-flex mt-2">
                                    My Tasks
                                    <span class="d-inline-block ms-auto">130 / 500</span>
                                </div>
                                <div class="progress progress-sm mb-4">
                                    <div aria-label="65%" aria-labelledby="My Tasks" aria-valuemax="100"
                                         aria-valuemin="0"
                                         aria-valuenow="65" class="progress-bar bg-fusion-400" role="progressbar"
                                         style="width: 65%;"></div>
                                </div>
                                <div class="d-flex">
                                    Transferred
                                    <span class="d-inline-block ms-auto">440 TB</span>
                                </div>
                                <div class="progress progress-sm mb-4">
                                    <div aria-label="34%" aria-labelledby="Transferred" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="34" class="progress-bar bg-success-500"
                                         role="progressbar" style="width: 34%;"></div>
                                </div>
                                <div class="d-flex">
                                    Resolved Issues
                                    <span class="d-inline-block ms-auto">77%</span>
                                </div>
                                <div class="progress progress-sm mb-4">
                                    <div aria-label="77%" aria-labelledby="Resolved Issues" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="77" class="progress-bar bg-info-400"
                                         role="progressbar" style="width: 77%;"></div>
                                </div>
                                <div class="d-flex">
                                    Testing Progress
                                    <span class="d-inline-block ms-auto">7 days</span>
                                </div>
                                <div class="progress progress-sm mb-g">
                                    <div aria-label="84%" aria-labelledby="Testing Progress" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="84" class="progress-bar bg-primary-300"
                                         role="progressbar" style="width: 84%;"></div>
                                </div>
                                <div class="row no-gutters">
                                    <div class="col-6 pe-1">
                                        <a class="btn btn-default btn-block waves-effect waves-themed"
                                           href="#">Generate
                                            PDF</a>
                                    </div>
                                    <div class="col-6 ps-1">
                                        <a class="btn btn-default btn-block waves-effect waves-themed" href="#">Report
                                            a Bug</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-content p-0">
                        <div class="row row-grid g-0">
                            <!-- SERVER LOAD -->
                            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-3">
                                <div class="p-3 d-flex align-items-center">
                                    <div
                                        class="js-easy-pie-chart position-relative d-inline-flex align-items-center justify-content-center"
                                        data-linecap="butt" data-linewidth="5" data-percent="75" data-piesize="50"
                                        data-scalelength="0" style="color: var(--primary-300);">
                                        <div
                                            class="d-flex flex-column align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-lg">
                                            <span class="js-percent d-block text-muted"></span>
                                        </div>
                                    </div>
                                    <span class="d-inline-block ms-2 text-muted">
                                        SERVER LOAD
                                        <i class="fal fa-caret-up color-danger-500 ms-1"></i>
                                    </span>
                                    <div class="ms-auto d-inline-flex align-items-center">
                                        <span class="peity-line d-none"
                                              data-peity='{ "fill": false, "stroke": "var(--primary-500)", "height": 30, "width": "70", "strokeWidth": 1 }'>5,6,5,3,8,6,9,7,4,2</span>
                                        <div class="d-inline-flex flex-column small ms-2">
                                            <span
                                                class="d-inline-block badge bg-success opacity-75 text-center p-1 width-6">97%</span>
                                            <span
                                                class="d-inline-block badge bg-fusion-300 opacity-75 text-center p-1 width-6 mt-1">44%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- DISK SPACE -->
                            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-3">
                                <div class="p-3 d-flex align-items-center">
                                    <div
                                        class="js-easy-pie-chart text-success position-relative d-inline-flex align-items-center justify-content-center"
                                        data-linecap="butt" data-linewidth="5" data-percent="79" data-piesize="50">
                                        <div
                                            class="d-flex flex-column align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-lg">
                                            <span class="js-percent d-block text-muted"></span>
                                        </div>
                                    </div>
                                    <span class="d-inline-block ms-2 text-muted">
                                        DISK SPACE
                                        <i class="fal fa-caret-down text-success ms-1"></i>
                                    </span>
                                    <div class="ms-auto d-inline-flex align-items-center">
                                        <span class="peity-line d-none"
                                              data-peity='{ "fill": false, "stroke": "var(--success-500)", "height": 30, "width": "70", "strokeWidth": 1 }'>5,9,7,3,5,2,5,3,9,6</span>
                                        <div class="d-inline-flex flex-column small ms-2">
                                            <span
                                                class="d-inline-block badge bg-info opacity-75 text-center p-1 width-6">76%</span>
                                            <span
                                                class="d-inline-block badge bg-warning-300 opacity-75 text-center p-1 width-6 mt-1">3%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- DATA TTF -->
                            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-3">
                                <div class="p-3 d-flex align-items-center">
                                    <div
                                        class="js-easy-pie-chart text-info position-relative d-inline-flex align-items-center justify-content-center"
                                        data-linecap="butt" data-linewidth="5" data-percent="23" data-piesize="50">
                                        <div
                                            class="d-flex flex-column align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-lg">
                                            <span class="js-percent d-block text-muted"></span>
                                        </div>
                                    </div>
                                    <span class="d-inline-block ms-2 text-muted">
                                        DATA TTF
                                        <i class="fal fa-caret-up color-success-500 ms-1"></i>
                                    </span>
                                    <div class="ms-auto d-inline-flex align-items-center">
                                        <span class="peity-line d-none"
                                              data-peity='{ "fill": false, "stroke": "var(--info-500)", "height": 30, "width": "70", "strokeWidth": 1 }'>3,5,2,5,3,9,6,5,9,7</span>
                                        <div class="d-inline-flex flex-column small ms-2">
                                            <span
                                                class="d-inline-block badge bg-fusion-500 opacity-75 text-center p-1 width-6">10GB</span>
                                            <span
                                                class="d-inline-block badge bg-fusion-300 opacity-75 text-center p-1 width-6 mt-1">10%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- TEMP -->
                            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-3">
                                <div class="p-3 d-flex align-items-center">
                                    <div
                                        class="js-easy-pie-chart position-relative d-inline-flex align-items-center justify-content-center"
                                        data-linecap="butt" data-linewidth="5" data-percent="36" data-piesize="50"
                                        style="color: var(--fusion-300);">
                                        <div
                                            class="d-flex flex-column align-items-center justify-content-center position-absolute pos-left pos-right pos-top pos-bottom fw-300 fs-lg">
                                            <span class="js-percent d-block text-muted"></span>
                                        </div>
                                    </div>
                                    <span class="d-inline-block ms-2 text-muted">
                                        TEMP.
                                        <i class="fal fa-caret-down color-success-500 ms-1"></i>
                                    </span>
                                    <div class="ms-auto d-inline-flex align-items-center">
                                        <span class="peity-line d-none"
                                              data-peity='{ "fill": false, "stroke": "var(--danger-500)", "height": 30, "width": "70", "strokeWidth": 1 }'>5,3,9,6,5,9,7,3,5,2</span>
                                        <div class="d-inline-flex flex-column small ms-2">
                                            <span
                                                class="d-inline-block badge bg-danger opacity-75 text-center p-1 width-6">124</span>
                                            <span
                                                class="d-inline-block badge bg-info text-light opacity-75 text-center p-1 width-6 mt-1">40F</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <!-- Group Chat -->
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-container">
                    <div class="d-flex flex-grow-1 p-0 w-100 h-100">
                        <!-- left sidebar -->
                        <div class="d-flex flex-column flex-grow-1 flex-0 overflow-x-auto h-100"
                             style="min-height: 599px;">
                            <div
                                class="d-flex align-items-center p-0 border-faded border-top-0 border-start-0 border-end-0 flex-shrink-0">
                                <div class="d-flex align-items-center w-100 ps-3 px-lg-3 py-2 position-relative">
                                    <div class="d-flex flex-row align-items-center mt-1 mb-1">
                                        <div class="me-2 d-inline-block">
                                            <span class="d-block position-relative">
                                                <img alt="" class="rounded profile-image profile-image-md"
                                                     src="/img/thumbs/pic-4.png"/>
                                            </span>
                                        </div>
                                        <div class="info-card-text">
                                            <div class="text-truncate text-truncate-lg">Group Chat</div>
                                            <small class="text-muted text-truncate text-truncate-md">7
                                                online</small>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-row gap-2 ms-auto">
                                        <a aria-label="Phone" class="btn btn-icon rounded-circle" data-toggle="button"
                                           href="javascript:void(0);">
                                            <svg class="sa-icon sa-icon-2x sa-bold sa-icon-subtlelight">
                                                <use href="/icons/sprite.svg#phone"></use>
                                            </svg>
                                        </a>
                                        <a aria-label="Video" class="btn btn-icon rounded-circle"
                                           href="javascript:void(0);">
                                            <svg class="sa-icon sa-icon-2x sa-bold sa-icon-subtlelight sa-nofill">
                                                <use href="/icons/sprite.svg#video"></use>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-wrap align-items-center flex-grow-1 position-relative bg-gray-50">
                                <div
                                    class="msgr position-absolute top-0 bottom-0 w-100 overflow-hidden d-flex flex-column">
                                    <!-- chat container -->
                                    <div class="w-100 p-4 px-lg-3 bg-subtlelight-fade custom-scroll flex-grow-1"
                                         id="chat_container">
                                        <!-- start .chat-segment -->
                                        <div class="chat-segment chat-segment-sent">
                                            <div class="chat-message px-2 py-2 fs-sm">
                                                <p>
                                                    <span class="fw-bold">@OliverKopyov</span>, can you
                                                    please check the files? Make sure to analyze the
                                                    solution <br/>and create a report.
                                                </p>
                                            </div>
                                            <div class="text-end fw-300 text-muted mt-1 fs-xs">
                                                3:00 pm
                                            </div>
                                        </div>
                                        <!--  end .chat-segment -->
                                        <!-- start .chat-segment -->
                                        <div class="chat-segment chat-segment-get position-relative">
                                            <span
                                                class="rounded-circle profile-image profile-image-sm d-block position-absolute me-2 mt-1"
                                                style="background-image:url('/img/demo/avatars/avatar-b.png');background-size: cover;left: 0;"></span>
                                            <div class="chat-message bg-warning bg-opacity-10 px-2 py-2 fs-sm ms-5">
                                                <p>
                                                    <span class="d-block fw-600 fs-xs mb-2">Oliver Kopyov</span>
                                                    Hi, Sorry going through a busy time in office. Yes I
                                                    analyzed the solution. It will require some
                                                    resource, which I could not manage.
                                                </p>
                                            </div>
                                            <div class="fw-300 text-muted mt-1 fs-xs ms-5"> 3:24 pm
                                            </div>
                                        </div>
                                        <!--  end .chat-segment -->
                                        <!-- start .chat-segment -->
                                        <div class="chat-segment chat-segment-sent chat-start">
                                            <div class="chat-message px-2 py-2 fs-sm">
                                                <p>
                                                    Okay
                                                </p>
                                            </div>
                                        </div>
                                        <div class="chat-segment chat-segment-sent chat-end">
                                            <div class="chat-message px-2 py-2 fs-sm">
                                                <p>
                                                    @GeraldChait, can you please help @OliverKopyov with
                                                    the report?
                                                </p>
                                            </div>
                                            <div class="text-end fw-300 text-muted mt-1 fs-xs">
                                                3:26 pm
                                            </div>
                                        </div>
                                        <!--  end .chat-segment -->
                                        <!-- start .chat-segment -->
                                        <div class="chat-segment chat-segment-get chat-start position-relative">
                                            <span
                                                class="rounded-circle profile-image profile-image-sm d-block position-absolute me-2 mt-1"
                                                style="background-image:url('/img/demo/avatars/avatar-c.png');background-size: cover;left: 0;"></span>
                                            <div class="chat-message bg-info bg-opacity-10 px-2 py-2 fs-sm ms-5">
                                                <p>
                                                    <span class="d-block fw-600 fs-xs mb-2">Melissa Emma Ayre</span>
                                                    I can help you out<br/>
                                                    Can you send the related files?
                                                </p>
                                            </div>
                                        </div>
                                        <div class="chat-segment chat-segment-get mb-3 chat-end">
                                            <div class="chat-message bg-info bg-opacity-10 px-2 py-2 fs-sm ms-5">
                                                <button aria-label="Play sound"
                                                        class="btn btn-sm btn-outline-secondary p-1 position-absolute translate-middle top-50 start-0 ms-4"
                                                        data-action="playsound" data-soundfile="demo-emma.mp3"
                                                        data-soundpath="media/sound/" type="button">
                                                    <svg class="sa-icon">
                                                        <use href="/icons/sprite.svg#play"></use>
                                                    </svg>
                                                </button>
                                                <div class="sound-image ms-5"></div>
                                            </div>
                                            <div class="fw-300 text-muted mt-1 fs-xs ms-5"> 3:29 pm
                                            </div>
                                        </div>
                                        <!--  end .chat-segment -->
                                        <!-- start .chat-segment -->
                                        <div class="chat-segment chat-segment-get chat-start position-relative">
                                            <span
                                                class="rounded-circle profile-image profile-image-sm d-block position-absolute me-2 mt-1"
                                                style="background-image:url('/img/demo/avatars/avatar-b.png');background-size: cover;left: 0;"></span>
                                            <div class="chat-message bg-warning bg-opacity-10 px-2 py-2 fs-sm ms-5">
                                                <p>
                                                    <span class="d-block fw-600 fs-xs mb-2">Oliver Kopyov</span>
                                                </p>
                                                <div class="position-relative">
                                                    <button aria-label="Play sound"
                                                            class="btn btn-sm btn-outline-secondary p-1 position-absolute translate-middle top-50 start-0 ms-3"
                                                            data-action="playsound" data-soundfile="demo-bryan.mp3"
                                                            data-soundpath="media/sound/" type="button">
                                                        <svg class="sa-icon">
                                                            <use href="/icons/sprite.svg#play"></use>
                                                        </svg>
                                                    </button>
                                                    <div class="sound-image ms-5"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="chat-segment chat-segment-get chat-end mb-3">
                                            <div class="chat-message bg-warning bg-opacity-10 px-2 py-2 fs-sm ms-5">
                                                <div
                                                    class="d-flex flex-wrap align-items-center gap-2 py-0 position-relative if-empty-display-none">
                                                    <div class="alert m-0 p-0 badge bg-primary-50 border-primary ps-2">
                                                        security_codes.pdf
                                                        <button aria-label="Download"
                                                                class="btn btn-icon btn-xs ms-1 rounded-0 border border-primary border-top-0 border-bottom-0 border-end-0 waves-effect waves-themed"
                                                                type="button">
                                                            <i class="sa sa-arrow-down"></i>
                                                        </button>
                                                    </div>
                                                    <div class="alert m-0 p-0 badge bg-primary-50 border-primary ps-2">
                                                        agreement.pdf
                                                        <button aria-label="Download"
                                                                class="btn btn-icon btn-xs ms-1 rounded-0 border border-primary border-top-0 border-bottom-0 border-end-0 waves-effect waves-themed"
                                                                type="button">
                                                            <i class="sa sa-arrow-down"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="fw-300 text-muted mt-1 fs-xs ms-5"> 3:33 pm
                                            </div>
                                        </div>
                                        <!--  end .chat-segment -->
                                        <!-- start .chat-segment -->
                                        <div class="chat-segment chat-segment-get position-relative">
                                            <span
                                                class="rounded-circle profile-image profile-image-sm d-block position-absolute me-2 mt-1"
                                                style="background-image:url('/img/demo/avatars/avatar-c.png');background-size: cover;left: 0;"></span>
                                            <div class="chat-message bg-info bg-opacity-10 px-2 py-2 fs-sm ms-5 pb-0">
                                                <span class="d-block fw-600 fs-xs mb-2">Melissa Emma Ayre</span>
                                                <div class="emoji emoji--like">
                                                    <div class="emoji__hand">
                                                        <div class="emoji__thumb"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="fw-300 text-muted mt-1 fs-xs ms-5"> Just now
                                            </div>
                                        </div>
                                        <!--  end .chat-segment -->
                                        <!-- start .chat-segment -->
                                        <div class="chat-segment chat-segment-sent chat-start">
                                            <div class="chat-message px-2 py-2 fs-sm">
                                                <p>
                                                    Thanks Melissa!
                                                </p>
                                            </div>
                                        </div>
                                        <!--  end .chat-segment -->
                                    </div>
                                    <!-- chat input -->
                                    <div
                                        class="panel-content border-faded border-start-0 border-end-0 border-bottom-0 bg-faded">
                                        <textarea
                                            class="form-control px-2 rounded-top border-bottom-start-radius-0 border-bottom-end-radius-0"
                                            placeholder="write a reply..." rows="2"></textarea>
                                        <div
                                            class="d-flex align-items-center py-1 px-2 border border-top-0 rounded-bottom">
                                            <div
                                                class="d-flex gap-1 flex-row align-items-center flex-wrap flex-shrink-0">
                                                <button aria-expanded="false" aria-label="Emoji"
                                                        class="btn btn-icon fs-xl" data-bs-toggle="dropdown"
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
                                                <button aria-label="Attach Files or Photos"
                                                        class="btn btn-icon fs-xl flex-shrink-0"
                                                        data-bs-original-title="Attach Files or Photos"
                                                        data-bs-placement="top" data-bs-toggle="tooltip" type="button">
                                                    <svg class="sa-icon sa-bold sa-icon-subtlelight">
                                                        <use href="/icons/sprite.svg#file"></use>
                                                    </svg>
                                                </button>
                                                <button aria-label="Voice"
                                                        class="btn btn-icon fs-xl width-1 flex-shrink-0"
                                                        data-bs-original-title="Voice" data-bs-placement="top"
                                                        data-bs-toggle="tooltip" type="button">
                                                    <svg class="sa-icon sa-bold sa-icon-subtlelight">
                                                        <use href="/icons/sprite.svg#mic"></use>
                                                    </svg>
                                                </button>
                                            </div>
                                            <button aria-label="Reply" class="btn btn-primary btn-xs ms-auto">
                                                Reply
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- My Schedule -->
            <div class="panel panel-icon" id="panel-3">
                <!-- <div class="panel-hdr">
                                            <h2> My <span class="fw-light"><i>Schedule</i></span></h2>
                                            <div class="panel-toolbar">
                                                <button type="button" class="btn btn-panel" data-action="panel-fullscreen" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Fullscreen">
                                                    <svg class="sa-icon">
                                                        <use href="/icons/sprite.svg#stop-circle"></use>
                                                    </svg>
                                                </button>
                                                <button type="button" class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Toggle">
                                                    <svg class="sa-icon">
                                                        <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                                        <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div> -->
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="myCalendar"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <!-- Global View -->
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2> Global <span class="fw-light"><i>View</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="globalView" style="height: 350px;"></div>
                    </div>
                    <div class="panel-content jvm-bg-ocean">
                        <div class="d-flex align-items-center">
                            <img alt="flag" class="d-inline-block js-jqvmap-flag me-3 border-faded"
                                 src="https://lipis.github.io/flag-icons/flags/4x3/us.svg"
                                 style="width:55px; height: auto;"/>
                            <div class="h4 d-inline-block fw-300 m-0 opacity-75 fs-lg">
                                Monthly Sales Volume:
                                <small class="js-jqvmap-country mb-0 fw-500"></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Subscriptions -->
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2> Subscriptions <span class="fw-light"><i>Hourly</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="subscriptionsChart" style="max-height: 200px; min-height: 200px;"></div>
                    </div>
                </div>
            </div>
            <!-- Secession -->
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2> Secession <span class="fw-light"><i>Scale</i></span></h2>
                </div>
                <div class="panel-container">
                    <div class="panel-content p-0 mb-g">
                        <div
                            class="alert bg-success bg-opacity-10 fade show border-faded border-start-0 border-end-0 rounded-0 m-0 d-flex align-items-center py-2 fs-sm"
                            role="alert">
                            <p class="m-0">Last update was on <span class="js-get-date">Friday, May 2, 2025</span>
                                - Your logs are all up to date.</p>
                            <button aria-label="Close" class="btn btn-system ms-auto" data-bs-dismiss="alert"
                                    type="button">
                                <svg class="sa-icon sa-icon-2x sa-icon-dark">
                                    <use href="/icons/sprite.svg#x"></use>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="panel-content">
                        <div class="row mb-g">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center" id="secessionChart"></div>
                            </div>
                            <div class="col-md-6 col-lg-5 me-lg-auto">
                                <div class="d-flex mt-2 mb-1 fs-xs text-primary">
                                    Current Usage
                                </div>
                                <div class="progress progress-xs mb-3">
                                    <div aria-label="70%" aria-labelledby="Current Usage" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="70" class="progress-bar" role="progressbar"
                                         style="width: 70%;"></div>
                                </div>
                                <div class="d-flex mt-2 mb-1 fs-xs text-info">
                                    Net Usage
                                </div>
                                <div class="progress progress-xs mb-3">
                                    <div aria-label="30%" aria-labelledby="Net Usage" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="30" class="progress-bar bg-info-500"
                                         role="progressbar" style="width: 30%;"></div>
                                </div>
                                <div class="d-flex mt-2 mb-1 fs-xs text-warning">
                                    Users blocked
                                </div>
                                <div class="progress progress-xs mb-3">
                                    <div aria-label="40%" aria-labelledby="Users blocked" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="40" class="progress-bar bg-warning-500"
                                         role="progressbar" style="width: 40%;"></div>
                                </div>
                                <div class="d-flex mt-2 mb-1 fs-xs text-danger">
                                    Custom cases
                                </div>
                                <div class="progress progress-xs mb-3">
                                    <div aria-label="15%" aria-labelledby="Custom cases" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="15" class="progress-bar bg-danger-500"
                                         role="progressbar" style="width: 15%;"></div>
                                </div>
                                <div class="d-flex mt-2 mb-1 fs-xs text-success">
                                    Test logs
                                </div>
                                <div class="progress progress-xs mb-3">
                                    <div aria-label="25%" aria-labelledby="Test logs" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="25" class="progress-bar bg-success-500"
                                         role="progressbar" style="width: 25%;"></div>
                                </div>
                                <div class="d-flex mt-2 mb-1 fs-xs text-dark">
                                    Uptime records
                                </div>
                                <div class="progress progress-xs mb-3">
                                    <div aria-label="10%" aria-labelledby="Uptime records" aria-valuemax="100"
                                         aria-valuemin="0" aria-valuenow="10" class="progress-bar bg-fusion-500"
                                         role="progressbar" style="width: 10%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/controlcenterdashboard.js', 'resources/scripts/pages/easypiechart.js'])
@endsection
