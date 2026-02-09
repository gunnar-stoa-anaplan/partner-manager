@extends('layouts.vertical', ['pageTitle' => 'Modal'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Modal', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Modal dialogs for lightboxes, user notifications, or completely custom content.'])
@endsection

@section('content')
    <div class="info-container">
        <p>Modals are built with HTML, CSS, and JavaScript. They're positioned over everything else in the document and
            remove
            scroll from the <code>body</code> so that modal content scrolls instead. Bootstrap only supports one modal
            window
            at a time. Nested modals aren't supported as it is believed to be poor user experiences.
            Modals use <code>position: fixed</code>, which can sometimes be a bit particular about its rendering.
            Whenever possible, place your modal HTML in a top-level position to avoid potential interference from other
            elements.
            You'll likely run into issues when nesting a .modal within another fixed element</p>
        <p class="mb-0">
            Find in-depth, guidelines, tutorials and more on Bootstrap Modals's at the
            <a href="https://getbootstrap.com/docs/5.3/components/modal/" target="_blank">official documentation</a>.
        </p>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <!--Basic Modals-->
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Basic <span class="fw-300"><i>Modals</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button class="btn btn-panel" data-action="panel-collapse" data-bs-offset="0,10"
                                data-bs-original-title="Toggle" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button class="btn btn-panel" data-action="panel-fullscreen" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button class="btn btn-panel" data-action="panel-close" data-bs-offset="0,10"
                                data-bs-original-title="Close" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Below is a static modal example (meaning its position and display have been overridden).
                            Included are the modal header, modal body (required for padding), and modal footer
                            (optional). We ask that you include modal headers with dismiss actions whenever possible, or
                            provide another explicit dismiss action.
                        </div>
                        <!-- Modal Small -->
                        <div aria-hidden="true" class="modal fade" id="default-example-modal-sm" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-sm" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Modal Small
                                        </h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Default -->
                        <div aria-hidden="true" class="modal fade" id="default-example-modal" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Modal Default
                                        </h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Large -->
                        <div aria-hidden="true" class="modal fade" id="default-example-modal-lg" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Modal Large</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal XL -->
                        <div aria-hidden="true" class="modal fade" id="default-example-modal-xl" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Modal XL</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Small Center -->
                        <div aria-hidden="true" class="modal fade" id="default-example-modal-sm-center" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Modal Small Center</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal center -->
                        <div aria-hidden="true" class="modal fade" id="default-example-modal-center" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Modal Center
                                        </h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Large Center -->
                        <div aria-hidden="true" class="modal fade" id="default-example-modal-lg-center" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Modal Large Center</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal XL Center -->
                        <div aria-hidden="true" class="modal fade" id="default-example-modal-xl-center" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Modal XL Center</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Default
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <!-- Small modal -->
                                <button class="btn btn-default" data-bs-target="#default-example-modal-sm"
                                        data-bs-toggle="modal" type="button">
                                    Small
                                </button>
                                <!-- Button trigger modal -->
                                <button class="btn btn-default" data-bs-target="#default-example-modal"
                                        data-bs-toggle="modal" type="button">
                                    Normal
                                </button>
                                <!-- Large modal -->
                                <button class="btn btn-default" data-bs-target="#default-example-modal-lg"
                                        data-bs-toggle="modal" type="button">
                                    Large
                                </button>
                                <!-- XL modal -->
                                <button class="btn btn-default" data-bs-target="#default-example-modal-xl"
                                        data-bs-toggle="modal" type="button">
                                    XL
                                </button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Centered
                        </h5>
                        <div class="frame-wrap mb-0">
                            <div class="demo">
                                <!-- Small modal -->
                                <button class="btn btn-default" data-bs-target="#default-example-modal-sm-center"
                                        data-bs-toggle="modal" type="button">
                                    Small
                                </button>
                                <!-- Button trigger modal -->
                                <button class="btn btn-default" data-bs-target="#default-example-modal-center"
                                        data-bs-toggle="modal" type="button">
                                    Normal
                                </button>
                                <!-- Large modal -->
                                <button class="btn btn-default" data-bs-target="#default-example-modal-lg-center"
                                        data-bs-toggle="modal" type="button">
                                    Large
                                </button>
                                <!-- XL modal -->
                                <button class="btn btn-default" data-bs-target="#default-example-modal-xl-center"
                                        data-bs-toggle="modal" type="button">
                                    XL
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Side Modals-->
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Side <span class="fw-300"><i>Modals</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button class="btn btn-panel" data-action="panel-collapse" data-bs-offset="0,10"
                                data-bs-original-title="Toggle" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button class="btn btn-panel" data-action="panel-fullscreen" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button class="btn btn-panel" data-action="panel-close" data-bs-offset="0,10"
                                data-bs-original-title="Close" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Modals can be positioned on different sides of the page using modifier classes like <code>.modal-dialog-start</code>,
                            <code>.modal-dialog-end</code>, <code>.modal-dialog-top</code>, and <code>.modal-dialog-bottom</code>
                            on <code>.modal-dialog</code>. These side modals also support size classes.
                        </div>
                        <!-- ALL END MODALS -->
                        <!-- Modal End -->
                        <div aria-hidden="true" class="modal fade default-example-modal-end" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-end">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title h4">Modal End</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal End Small -->
                        <div aria-hidden="true" class="modal fade default-example-modal-end-sm" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-end modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title h4">Modal End Small</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal End Large -->
                        <div aria-hidden="true" class="modal fade default-example-modal-end-lg" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-end modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title h4">Modal End Large</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal End XL -->
                        <div aria-hidden="true" class="modal fade default-example-modal-end-xl" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-end modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title h4">Modal End XL</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ALL START MODALS -->
                        <!-- Modal Start -->
                        <div aria-hidden="true" class="modal fade default-example-modal-start" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-start">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title h4">Modal Start</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Start Small -->
                        <div aria-hidden="true" class="modal fade default-example-modal-start-sm" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-start modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title h4">Modal Start Small</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Start Large -->
                        <div aria-hidden="true" class="modal fade default-example-modal-start-lg" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-start modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title h4">Modal Start Large</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Start XL -->
                        <div aria-hidden="true" class="modal fade default-example-modal-start-xl" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-start modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title h4">Modal Start XL</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ALL HORIZONTAL MODALS -->
                        <!-- Modal Top -->
                        <div aria-hidden="true" class="modal fade default-example-modal-top" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-top">
                                <div class="modal-content">
                                    <div class="modal-body">
                                        <div class="alert alert-warning m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Bottom -->
                        <div aria-hidden="true" class="modal fade default-example-modal-bottom" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-bottom">
                                <div class="modal-content">
                                    <div class="modal-body">
                                        <div class="alert alert-success m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            End
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-default" data-bs-target=".default-example-modal-end-sm"
                                        data-bs-toggle="modal" type="button">
                                    Small
                                </button>
                                <button class="btn btn-default" data-bs-target=".default-example-modal-end"
                                        data-bs-toggle="modal" type="button">
                                    Normal
                                </button>
                                <button class="btn btn-default" data-bs-target=".default-example-modal-end-lg"
                                        data-bs-toggle="modal" type="button">
                                    Large
                                </button>
                                <button class="btn btn-default" data-bs-target=".default-example-modal-end-xl"
                                        data-bs-toggle="modal" type="button">
                                    XL
                                </button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Start
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-default" data-bs-target=".default-example-modal-start-sm"
                                        data-bs-toggle="modal" type="button">
                                    Small
                                </button>
                                <button class="btn btn-default" data-bs-target=".default-example-modal-start"
                                        data-bs-toggle="modal" type="button">
                                    Normal
                                </button>
                                <button class="btn btn-default" data-bs-target=".default-example-modal-start-lg"
                                        data-bs-toggle="modal" type="button">
                                    Large
                                </button>
                                <button class="btn btn-default" data-bs-target=".default-example-modal-start-xl"
                                        data-bs-toggle="modal" type="button">
                                    XL
                                </button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Top &amp; Bottom
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-default" data-bs-target=".default-example-modal-top"
                                        data-bs-toggle="modal" type="button">
                                    Top
                                </button>
                                <button class="btn btn-default" data-bs-target=".default-example-modal-bottom"
                                        data-bs-toggle="modal" type="button">
                                    Bottom
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <!--Modifications-->
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Modifications <span class="fw-300"><i>for modals</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button class="btn btn-panel" data-action="panel-collapse" data-bs-offset="0,10"
                                data-bs-original-title="Toggle" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button class="btn btn-panel" data-action="panel-fullscreen" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button class="btn btn-panel" data-action="panel-close" data-bs-offset="0,10"
                                data-bs-original-title="Close" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Modal fullscreen uses combination of utility classes and the modifier class <code>.modal-fullscreen</code>.
                            Using the modifer class <code>.modal-backdrop-transparent</code> makes the backdrop
                            transparent. You can also translate the modals to cast as an alert by using the modifier
                            class <code>.modal-alert</code>
                        </div>
                        <h5 class="frame-heading">
                            Fullscreen
                        </h5>
                        <div class="frame-wrap">
                            <button class="btn btn-lg btn-default" data-bs-target=".example-modal-fullscreen"
                                    data-bs-toggle="modal" type="button">Modal Fullscreen
                            </button>
                        </div>
                        <!-- Fullscreen Modal -->
                        <div aria-hidden="true" class="modal fade example-modal-fullscreen" role="dialog" tabindex="-1">
                            <div class="modal-dialog modal-fullscreen" role="document">
                                <div class="modal-dialog modal-fullscreen">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-4" id="exampleModalFullscreenLabel">Full screen
                                                modal</h1>
                                            <button aria-label="Close" class="btn btn-system ms-auto"
                                                    data-bs-dismiss="modal" type="button">
                                                <svg class="sa-icon sa-icon-2x">
                                                    <use href="/icons/sprite.svg#x"></use>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Another override is the option to pop up a modal that covers the user
                                                viewport, available via modifier
                                                classes that are placed on a <code>.modal-dialog</code>.</p>
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                    <tr>
                                                        <th>Class</th>
                                                        <th>Availability</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <td><code>.modal-fullscreen</code></td>
                                                        <td>Always</td>
                                                    </tr>
                                                    <tr>
                                                        <td><code>.modal-fullscreen-sm-down</code></td>
                                                        <td><code>576px</code></td>
                                                    </tr>
                                                    <tr>
                                                        <td><code>.modal-fullscreen-md-down</code></td>
                                                        <td><code>768px</code></td>
                                                    </tr>
                                                    <tr>
                                                        <td><code>.modal-fullscreen-lg-down</code></td>
                                                        <td><code>992px</code></td>
                                                    </tr>
                                                    <tr>
                                                        <td><code>.modal-fullscreen-xl-down</code></td>
                                                        <td><code>1200px</code></td>
                                                    </tr>
                                                    <tr>
                                                        <td><code>.modal-fullscreen-xxl-down</code></td>
                                                        <td><code>1400px</code></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Alert
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn-default btn-has-sound d-flex-inline align-items-center"
                                        data-action="playsound" data-bs-target="#example-modal-alert"
                                        data-bs-toggle="modal" data-soundfile="error.mp3" data-soundpath="media/sound/"
                                        type="button">
                                    <i class="sa sa-bell me-1"></i>
                                    Modal Alert (with sound)
                                </button>
                                <button class="btn btn-default d-flex-inline align-items-center"
                                        data-bs-target="#example-modal-alert-alt" data-bs-toggle="modal" type="button">
                                    Modal Alert Alt
                                </button>
                            </div>
                        </div>
                        <!-- modal alert -->
                        <div aria-hidden="true" class="modal modal-alert fade" id="example-modal-alert" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content bg-dark bg-opacity-75 shadow-5 border-left-0 border-right-0">
                                    <div class="modal-header border-bottom-0">
                                        <h5 class="modal-title">
                                            <svg class="sa-icon me-2 sa-icon-warning sa-icon-2x sa-bold">
                                                <use href="/icons/sprite.svg#alert-triangle"></use>
                                            </svg>
                                            Big Modal Alert
                                        </h5>
                                    </div>
                                    <div class="modal-body border-bottom-0 fw-300 fs-sm">
                                        <span class="fw-bold">Warning:</span> With a bold stern warning message to get
                                        your attention.
                                    </div>
                                    <div class="modal-footer border-top-0">
                                        <button class="btn btn-light" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-warning" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- modal alert alt -->
                        <div aria-hidden="true" class="modal modal-alert fade" id="example-modal-alert-alt"
                             role="dialog" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content bg-success bg-opacity-75 border-left-0 border-right-0">
                                    <div class="modal-header border-bottom-0">
                                        <h5 class="modal-title">Modal title</h5>
                                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"
                                                type="button"></button>
                                    </div>
                                    <div class="modal-body border-bottom-0">
                                        We use bootstrap utilities to create a cutomized alert modal
                                    </div>
                                    <div class="modal-footer border-top-0">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel
                                        </button>
                                        <button class="btn btn-danger" type="button">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            No backdrops
                        </h5>
                        <div class="frame-wrap">
                            <button class="btn btn-default" data-bs-target="#example-modal-backdrop-transparent"
                                    data-bs-toggle="modal" type="button">Modal No Backdrop
                            </button>
                        </div>
                        <!-- Modal center Large no backdrop -->
                        <div aria-hidden="true" class="modal fade modal-backdrop-transparent" data-bs-backdrop="false"
                             id="example-modal-backdrop-transparent" role="dialog" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Modal title</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger">
                                            Notice how there is no backdrop to this modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Static backdrop
                        </h5>
                        <div class="frame-wrap">
                            <button class="btn btn-default" data-bs-target="#example-modal-backdrop-static"
                                    data-bs-toggle="modal" type="button">Modal Static Backdrop
                            </button>
                        </div>
                        <!-- Modal center Large no backdrop -->
                        <div aria-hidden="true" class="modal fade example-modal-backdrop-static"
                             data-bs-backdrop="static" data-bs-keyboard="false" id="example-modal-backdrop-static"
                             role="dialog" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Modal Static Backdrop</h5>
                                        <button aria-label="Close" class="btn btn-system ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-danger">
                                            Notice how there is a static backdrop to this modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Transparent-->
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Transparent <span class="fw-300"><i>Modal (Tinted)</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button class="btn btn-panel" data-action="panel-collapse" data-bs-offset="0,10"
                                data-bs-original-title="Toggle" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button class="btn btn-panel" data-action="panel-fullscreen" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button class="btn btn-panel" data-action="panel-close" data-bs-offset="0,10"
                                data-bs-original-title="Close" data-bs-placement="top" data-bs-toggle="tooltip"
                                type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Give modals a 'transparent tinted glass' feel using only utility classes.
                        </div>
                        <!-- Modal Top Transparent -->
                        <div aria-hidden="true" class="modal fade example-modal-top-transparent" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-top">
                                <div class="modal-content bg-dark bg-opacity-75 shadow-5">
                                    <div class="modal-body">
                                        <div
                                            class="alert alert-primary bg-primary border-primary text-primary border-opacity-50 bg-opacity-10 m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Bottom Transparent -->
                        <div aria-hidden="true" class="modal fade example-modal-bottom-transparent" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-bottom">
                                <div class="modal-content bg-dark bg-opacity-75 shadow-5">
                                    <div class="modal-body">
                                        <div
                                            class="alert alert-warning bg-warning border-warning text-warning border-opacity-50 bg-opacity-10 m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal End Transparent-->
                        <div aria-hidden="true" class="modal fade example-modal-end-transparent" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-end modal-sm">
                                <div class="modal-content bg-dark bg-opacity-75 shadow-5">
                                    <div class="modal-header border-bottom-0">
                                        <h4 class="modal-title text-white">
                                            Transparent End
                                            <small class="m-0 text-white opacity-70">
                                                Below is a static modal example
                                            </small>
                                        </h4>
                                        <button aria-label="Close" class="btn btn-system btn-system-light ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x sa-icon-light">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div
                                            class="alert alert-danger bg-danger border-danger text-danger border-opacity-50 bg-opacity-10">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top-0">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Start Transparent-->
                        <div aria-hidden="true" class="modal fade example-modal-start-transparent" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-start modal-sm">
                                <div class="modal-content modal-content bg-dark bg-opacity-75 shadow-5">
                                    <div class="modal-header border-bottom-0">
                                        <h5 class="modal-title h4 text-white">Large start side modal</h5>
                                        <button aria-label="Close" class="btn-close text-white" data-bs-dismiss="modal"
                                                type="button"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div
                                            class="alert alert-danger bg-danger border-danger text-danger border-opacity-50 bg-opacity-10 m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top-0">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Center Transparent -->
                        <div aria-hidden="true" class="modal fade example-modal-centered-transparent" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content bg-dark bg-opacity-75 shadow-5">
                                    <div class="modal-header border-bottom-0">
                                        <h4 class="modal-title text-white">
                                            Transparent Centered
                                            <small class="m-0 text-white opacity-70">
                                                Below is a static modal example
                                            </small>
                                        </h4>
                                        <button aria-label="Close" class="btn btn-system btn-system-light ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x sa-icon-light">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div
                                            class="alert alert-success bg-success border-success text-success border-opacity-50 bg-opacity-10 m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top-0">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal Default Transparent -->
                        <div aria-hidden="true" class="modal fade example-modal-default-transparent" role="dialog"
                             tabindex="-1">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content bg-dark bg-opacity-75 shadow-5">
                                    <div class="modal-header border-bottom-0">
                                        <h4 class="modal-title text-white">
                                            Transparent Default
                                            <small class="m-0 text-white opacity-70">
                                                Below is a static modal example
                                            </small>
                                        </h4>
                                        <button aria-label="Close" class="btn btn-system btn-system-light ms-auto"
                                                data-bs-dismiss="modal" type="button">
                                            <svg class="sa-icon sa-icon-2x sa-icon-light">
                                                <use href="/icons/sprite.svg#x"></use>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div
                                            class="alert alert-success bg-success border-success text-success border-opacity-50 bg-opacity-10 m-0">
                                            This is the content area of the modal.
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top-0">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close
                                        </button>
                                        <button class="btn btn-primary" type="button">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Default
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn btn-dark" data-bs-target=".example-modal-default-transparent"
                                        data-bs-toggle="modal" type="button">
                                    Default
                                </button>
                                <button class="btn btn btn-dark" data-bs-target=".example-modal-centered-transparent"
                                        data-bs-toggle="modal" type="button">
                                    Centered
                                </button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Start &amp; End
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn btn-dark" data-bs-target=".example-modal-start-transparent"
                                        data-bs-toggle="modal" type="button">
                                    Start
                                </button>
                                <button class="btn btn btn-dark" data-bs-target=".example-modal-end-transparent"
                                        data-bs-toggle="modal" type="button">
                                    End
                                </button>
                            </div>
                        </div>
                        <h5 class="frame-heading">
                            Top &amp; Bottom
                        </h5>
                        <div class="frame-wrap">
                            <div class="demo">
                                <button class="btn btn btn-dark" data-bs-target=".example-modal-top-transparent"
                                        data-bs-toggle="modal" type="button">
                                    Top
                                </button>
                                <button class="btn btn btn-dark" data-bs-target=".example-modal-bottom-transparent"
                                        data-bs-toggle="modal" type="button">
                                    Bottom
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
