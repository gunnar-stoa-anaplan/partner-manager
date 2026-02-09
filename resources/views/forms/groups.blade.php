@extends('layouts.vertical', ['pageTitle' => 'Groups'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Groups', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'Forms', 'pageSubText' => 'Input groups allow you to combine input fields with additional elements like text, icons, or buttons for enhanced functionality and design.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Basic <span class="fw-300"><i>example</i></span>
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
                            <p>Place one add-on or button on either side of an input. You may also place one on both
                                sides
                                of an input. Remember to place <code>&lt;label&gt;</code>s outside the input group.</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="basic-addon1">Prepend</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input aria-describedby="basic-addon1" aria-label="Username" autocomplete="username"
                                       class="form-control" id="basic-addon1" name="username" placeholder="Username"
                                       type="text"/>
                            </div>
                            <div class="form-text">Some help content goes here</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="basic-addon2">Append</label>
                            <div class="input-group">
                                <input aria-describedby="basic-addon2" aria-label="Recipient's username"
                                       autocomplete="off"
                                       class="form-control" id="basic-addon2" name="recipient"
                                       placeholder="Recipient's username" type="text"/>
                                <span class="input-group-text">@example.com</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="basic-url">Your vanity URL</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">https://example.com/users/</span>
                                <input aria-describedby="basic-addon3" autocomplete="off" class="form-control"
                                       id="basic-url" name="vanity-url" type="text"/>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prepend and append</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input aria-label="Amount (to the nearest dollar)" autocomplete="off"
                                       class="form-control"
                                       id="amount-input" name="amount" type="text"/>
                                <span class="input-group-text">.00</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Text area example</label>
                            <div class="input-group">
                                <span class="input-group-text">Comment</span>
                                <textarea aria-label="With textarea" autocomplete="off" class="form-control"
                                          id="comment-textarea" name="comment"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Wrapping <span class="fw-300"><i>inputs</i></span>
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
                            Input groups wrap by default via <code>flex-wrap: wrap</code> in order to accommodate custom
                            form field validation within an input group. You may disable this with
                            <code>.flex-nowrap</code>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="addon-wrapping-left">Prepend with flex-nowrap</label>
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text"><i class="sa sa-user fs-xl"></i></span>
                                <input aria-describedby="addon-wrapping-left" aria-label="Username"
                                       autocomplete="username" class="form-control" id="addon-wrapping-left"
                                       name="username-left" placeholder="Username" type="text"/>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="addon-wrapping-right">Append with flex-nowrap</label>
                            <div class="input-group flex-nowrap">
                                <input aria-describedby="addon-wrapping-right" aria-label="Username"
                                       autocomplete="username" class="form-control" id="addon-wrapping-right"
                                       name="username-right" placeholder="Username" type="text"/>
                                <span class="input-group-text"><i class="sa sa-user fs-xl"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Various <span class="fw-300"><i>sizing</i></span>
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
                            Add the relative form sizing classes to the <code>.input-group</code> itself and contents
                            within
                            will automatically resize—no need for repeating the form control size classes on each
                            element.
                            <strong>Sizing on the individual input group elements isn't supported</strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="input-group-lg-size">Large Input Group</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text py-1 px-3">
                                    <div class="stack-icon" style="font-size: 2.25rem;">
                                        <div class="icon-layers stack-3x">
                                            <i class="sa sa-base-2 text-success"></i>
                                        </div>
                                        <div class="icon-layers stack-2x">
                                            <i class="sa sa-base-10 stack-2x alpha-2"></i>
                                        </div>
                                        <div class="icon-layers stack-1x">
                                            <i class="sa sa-base-10 stack-1x text-light"></i>
                                        </div>
                                        <div class="icon-layers stack-1x">
                                            <i class="sa sa-profile stack-1x text-dark"></i>
                                        </div>
                                    </div>
                                </span>
                                <input aria-describedby="input-group-lg-size" class="form-control"
                                       id="input-group-lg-size" name="large-input" placeholder="Large size"
                                       type="text"/>
                            </div>
                            <span class="form-text text-muted">Some help content goes here</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="input-group-lg-size-2">Large Input Group</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    0.00%
                                </span>
                                <span class="input-group-text text-danger">
                                    <i class="sa sa-lock"></i>
                                </span>
                                <input aria-describedby="input-group-lg-size-2" class="form-control"
                                       id="input-group-lg-size-2" name="large-input-2" placeholder="Large size"
                                       type="text"/>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="input-group-sm-size">Small Input Group</label>
                            <div class="input-group input-group-sm">
                                <input aria-describedby="input-group-sm-size" class="form-control"
                                       id="input-group-sm-size" name="small-input" placeholder="Small size"
                                       type="text"/>
                                <span class="input-group-text">
                                    <i class="sa sa-reload"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="input-group-sm-size-2">Small Input Group</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">
                                    <i class="sa sa-reload"></i>
                                </span>
                                <input aria-describedby="input-group-sm-size-2" class="form-control"
                                       id="input-group-sm-size-2" name="small-input-2" placeholder="Small size"
                                       type="text"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Checkboxes <span class="fw-300"><i>and radios</i></span>
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
                            Place any checkbox or radio option within an input group's addon instead of text
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="checkbox-group-1">Checkbox generic</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <input aria-label="Checkbox for following text input" id="checkbox-generic"
                                           name="checkbox-generic" type="checkbox"/>
                                </span>
                                <input aria-label="Text input with checkbox" autocomplete="off" class="form-control"
                                       id="checkbox-group-1" name="checkbox-text" type="text"/>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="checkbox-group-2">Checkbox</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" id="checkmeout1" name="checkmeout1"
                                               type="checkbox"/>
                                        <label class="form-check-label" for="checkmeout1">I agree</label>
                                    </div>
                                </span>
                                <span class="input-group-text text-success">
                                    <span class="fw-500">$</span>
                                </span>
                                <input aria-label="Text input with checkbox" autocomplete="off" class="form-control"
                                       id="checkbox-group-2" name="checkbox-text-2" type="text"/>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="checkbox-group-3">Checkbox split</label>
                            <div class="input-group">
                                <span class="input-group-text text-success">
                                    <i class="sa sa-big-plus fs-xl"></i>
                                </span>
                                <input aria-label="Text input with checkbox" autocomplete="off" class="form-control"
                                       id="checkbox-group-3" name="checkbox-text-3" type="text"/>
                                <span class="input-group-text">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" id="checkmeout2" name="checkmeout2"
                                               type="checkbox"/>
                                        <label class="form-check-label" for="checkmeout2">I agree</label>
                                    </div>
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="checkbox-group-4">Checkbox switch</label>
                            <div class="input-group">
                                <input aria-label="Text input with checkbox" autocomplete="off" class="form-control"
                                       id="checkbox-group-4" name="checkbox-text-4" placeholder="Record title"
                                       type="text"/>
                                <span class="input-group-text">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" id="eventlog-switch-1" name="debugger-active"
                                               type="checkbox"/>
                                        <label class="form-check-label fw-500" for="eventlog-switch-1">Debugger
                                            Active</label>
                                    </div>
                                </span>
                                <span class="input-group-text">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" id="eventlog-switch-2" name="record-logs"
                                               type="checkbox"/>
                                        <label class="form-check-label fw-500"
                                               for="eventlog-switch-2">Record Logs</label>
                                    </div>
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="radio-group-1">Radio generic</label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" id="customRadio1" name="customRadio"
                                               type="radio"/>
                                        <label class="form-check-label" for="customRadio1">Yes</label>
                                    </div>
                                </div>
                                <div class="input-group-text">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" id="customRadio2" name="customRadio"
                                               type="radio"/>
                                        <label class="form-check-label" for="customRadio2">No</label>
                                    </div>
                                </div>
                                <input aria-label="Text input with radio button" class="form-control" id="radio-group-1"
                                       name="radio-text" type="text"/>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="radio-group-2">Radio generic</label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" id="customRadio3" name="customRadio2"
                                               type="radio"/>
                                        <label class="form-check-label" for="customRadio3">Yes</label>
                                    </div>
                                </div>
                                <div class="input-group-text text-danger">
                                    <span class="fw-500">$</span>
                                </div>
                                <input aria-label="Text input with radio button" class="form-control" id="radio-group-2"
                                       name="radio-text-2" type="text"/>
                                <div class="input-group-text">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" id="customRadio4" name="customRadio2"
                                               type="radio"/>
                                        <label class="form-check-label" for="customRadio4">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Multiple <span class="fw-300"><i>inputs</i></span>
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
                            Add the relative form sizing classes to the <code>.input-group</code> itself and contents
                            within
                            will automatically resize—no need for repeating the form control size classes on each
                            element.
                            You can add <code>.input-group-multi-transition</code> to <code>.input-group</code> to
                            animate
                            focused inputs
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="name-f">Two input fields</label>
                            <div class="input-group">
                                <span class="input-group-text text-success">
                                    <i class="sa sa-user fs-xl"></i>
                                </span>
                                <input aria-label="First name" autocomplete="given-name" class="form-control"
                                       id="name-f" name="first-name" placeholder="First name" type="text"/>
                                <input aria-label="Last name" autocomplete="family-name" class="form-control"
                                       id="name-l" name="last-name" placeholder="Last name" type="text"/>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="dollar-1">Three input fields (animated)</label>
                            <div class="input-group input-group-multi-transition">
                                <span class="input-group-text">@</span>
                                <input aria-label="Dollar amount (with dot and two decimal places)" autocomplete="off"
                                       class="form-control" id="dollar-1" name="recipient" placeholder="Recipient"
                                       type="text"/>
                                <input aria-label="Person name" autocomplete="email" class="form-control" id="dollar-2"
                                       name="email" placeholder="Email" type="text"/>
                                <input aria-label="Person name" autocomplete="off" class="form-control" id="dollar-3"
                                       name="amount" placeholder="Amount" type="text"/>
                                <span class="input-group-text">0.00</span>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Between two inputs</label>
                            <div class="input-group">
                                <input autocomplete="off" class="form-control" id="between-input-1"
                                       name="between-input-1" type="text"/>
                                <span class="input-group-text">
                                    <i class="sa sa-arrow-right fs-xl"></i>
                                </span>
                                <input autocomplete="off" class="form-control" id="between-input-2"
                                       name="between-input-2" type="text"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-9">
                <div class="panel-hdr">
                    <h2>
                        Segmented <span class="fw-300"><i>buttons</i></span>
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
                            Dropdown with action buttons
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="drop-w-action-left">Button from left</label>
                            <div class="input-group">
                                <button class="btn btn-outline-default" id="button-addon1" name="button-addon1"
                                        type="button">Go
                                </button>
                                <button aria-expanded="false"
                                        class="btn btn-outline-default dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" type="button">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Action</a></li>
                                    <li><a class="dropdown-item" href="#">Another action</a></li>
                                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                                    <li>
                                        <hr class="dropdown-divider"/>
                                    </li>
                                    <li><a class="dropdown-item" href="#">Separated link</a></li>
                                </ul>
                                <input aria-label="Text input with segmented dropdown button" class="form-control"
                                       id="drop-w-action-left" name="dropdown-input-left" type="text"/>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="drop-w-action-right">Button from right</label>
                            <div class="input-group">
                                <input aria-label="Text input with segmented dropdown button" class="form-control"
                                       id="drop-w-action-right" name="dropdown-input-right" type="text"/>
                                <button class="btn btn-outline-default" id="button-addon2" name="button-addon2"
                                        type="button">Action
                                </button>
                                <button aria-expanded="false"
                                        class="btn btn-outline-default dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" type="button">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#">Action</a></li>
                                    <li><a class="dropdown-item" href="#">Another action</a></li>
                                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                                    <li>
                                        <hr class="dropdown-divider"/>
                                    </li>
                                    <li><a class="dropdown-item" href="#">Separated link</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Seamless <span class="fw-300"><i>icon</i></span>
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
                            Add seamless icons using various utility classes
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon on left</label>
                            <div class="input-group body-bg shadow-inset-2 rounded">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="sa sa-magnifier text-dark"></i>
                                </span>
                                <input autocomplete="off" class="form-control border-start-0 bg-transparent ps-0"
                                       id="search-left" name="search-left" placeholder="Search..." type="text"/>
                            </div>
                            <span class="form-text text-muted">Some help content goes here</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon on Right</label>
                            <div class="input-group body-bg shadow-inset-2 rounded">
                                <input autocomplete="off" class="form-control border-end-0 bg-transparent pe-0"
                                       id="search-right" name="search-right" placeholder="Search..." type="text"/>
                                <span class="input-group-text bg-transparent border-start-0">
                                    <i class="sa sa-magnifier text-dark"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Large icon</label>
                            <div class="input-group input-group-lg body-bg shadow-inset-2 rounded">
                                <span class="input-group-text bg-transparent border-end-0 py-1 px-3">
                                    <div class="stack-icon" style="font-size: 2.2rem;">
                                        <div class="icon-layers">
                                            <i class="sa sa-base-7 text-info"></i>
                                        </div>
                                        <div class="icon-layers">
                                            <svg class="sa-icon stack-1x sa-icon-light sa-bold">
                                                <use href="/icons/sprite.svg#database"></use>
                                            </svg>
                                        </div>
                                    </div>
                                </span>
                                <input autocomplete="off" class="form-control border-start-0 bg-transparent ps-0"
                                       id="search-large" name="search-large" placeholder="Search..." type="text"/>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Small icon with button</label>
                            <div class="input-group input-group-sm body-bg shadow-inset-2 rounded">
                                <span class="input-group-text bg-transparent border-end-0 py-1 px-3">
                                    <i class="sa sa-magnifier text-success"></i>
                                </span>
                                <input autocomplete="off" class="form-control border-start-0 bg-transparent ps-0"
                                       id="search-small" name="search-small" placeholder="Type here..." type="text"/>
                                <button class="btn btn-outline-default" id="search-btn" name="search-btn"
                                        type="button">Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        Button <span class="fw-300"><i>addon</i></span>
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
                        <div class="mb-3">
                            <label class="form-label" for="button-addon1">Button on left</label>
                            <div class="input-group">
                                <button class="btn btn-outline-default" id="button-addon1" name="button-addon1"
                                        type="button">Go
                                </button>
                                <input aria-describedby="button-addon1" aria-label="Example text with button addon"
                                       autocomplete="off" class="form-control" id="button-input-1" name="button-input-1"
                                       placeholder="Search anything..." type="text"/>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="button-addon2">Button on right</label>
                            <div class="input-group">
                                <input aria-describedby="button-addon2" aria-label="Recipient's username"
                                       autocomplete="off" class="form-control" id="button-input-2" name="button-input-2"
                                       placeholder="Recipient's username" type="text"/>
                                <button class="btn btn-outline-default" id="button-addon2" name="button-addon2"
                                        type="button">Look up!
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="button-addon3">Button on both sides</label>
                            <div class="input-group">
                                <button class="btn btn-outline-default" id="button-addon3-1" name="button-addon3-1"
                                        type="button">Go
                                </button>
                                <input aria-describedby="button-addon3" aria-label="Example text with two button addons"
                                       autocomplete="off" class="form-control" id="button-addon3" name="button-input-3"
                                       placeholder="Search for anything..." type="text"/>
                                <button class="btn btn-outline-default" id="button-addon3-2" name="button-addon3-2"
                                        type="button">Go
                                </button>
                                <button class="btn btn-outline-default" id="button-addon3-3" name="button-addon3-3"
                                        type="button">Go
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="button-addon4">Button on left with icon</label>
                            <div class="input-group">
                                <button class="btn btn-info shadow-0" id="button-addon4" name="button-addon4"
                                        type="button"><i class="sa sa-magnifier"></i></button>
                                <input aria-describedby="button-addon4" aria-label="Example text with button addon"
                                       autocomplete="off" class="form-control" id="button-input-4" name="button-input-4"
                                       placeholder="Search anything..." type="text"/>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="button-addon5">Button on right</label>
                            <div class="input-group">
                                <input aria-describedby="button-addon5" aria-label="Recipient's username"
                                       autocomplete="off" class="form-control" id="button-input-5" name="button-input-5"
                                       placeholder="Recipient's username" type="text"/>
                                <button class="btn btn-primary shadow-0" id="button-addon5" name="button-addon5"
                                        type="button"><i class="sa sa-magnifier"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-8">
                <div class="panel-hdr">
                    <h2>
                        Buttons <span class="fw-300"><i>with dropdown</i></span>
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
                            Dropdowns are easily integrated just like buttons, you can have more than one dropdowns
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="dropdown-on-left">Dropdown on left</label>
                            <div class="input-group">
                                <button aria-expanded="false" aria-haspopup="true"
                                        class="btn btn-outline-default dropdown-toggle" data-bs-toggle="dropdown"
                                        id="dropdown-on-left" name="dropdown-on-left" type="button">Dropdown
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider" role="separator"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                                <input aria-label="Text input with dropdown button" autocomplete="off"
                                       class="form-control" id="dropdown-input-left" name="dropdown-input-left"
                                       type="text"/>
                            </div>
                            <span class="form-text text-muted">Some help content goes here</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="dropdown-on-right">Dropdown on right</label>
                            <div class="input-group">
                                <input aria-label="Text input with dropdown button" autocomplete="off"
                                       class="form-control" id="dropdown-input-right" name="dropdown-input-right"
                                       type="text"/>
                                <button aria-expanded="false" aria-haspopup="true"
                                        class="btn btn-outline-default dropdown-toggle" data-bs-toggle="dropdown"
                                        id="dropdown-on-right" name="dropdown-on-right" type="button">Dropdown
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider" role="separator"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </div>
                            <span class="form-text text-muted">Some help content goes here</span>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Dropdown on both</label>
                            <div class="input-group">
                                <button aria-expanded="false" aria-haspopup="true"
                                        class="btn btn-outline-default dropdown-toggle" data-bs-toggle="dropdown"
                                        id="dropdown-on-both-left" name="dropdown-on-both-left"
                                        type="button">Dropdown
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider" role="separator"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                                <input aria-label="Text input with dropdown button" autocomplete="off"
                                       class="form-control" id="dropdown-input-both" name="dropdown-input-both"
                                       type="text"/>
                                <button aria-expanded="false" aria-haspopup="true"
                                        class="btn btn-outline-default dropdown-toggle" data-bs-toggle="dropdown"
                                        id="dropdown-on-both-right" name="dropdown-on-both-right"
                                        type="button">Dropdown
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                    <div class="dropdown-divider" role="separator"></div>
                                    <a class="dropdown-item" href="#">Separated link</a>
                                </div>
                            </div>
                            <span class="form-text text-muted">Some help content goes here</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-10">
                <div class="panel-hdr">
                    <h2>
                        Custom <span class="fw-300"><i>forms</i></span>
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
                            Input groups include support for custom selects and custom file inputs. Browser default
                            versions
                            of these are not supported
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="inputGroupSelect01">Label &amp; select on left</label>
                            <div class="input-group">
                                <label class="input-group-text" for="inputGroupSelect01">Options</label>
                                <select autocomplete="off" class="form-select" id="inputGroupSelect01"
                                        name="inputGroupSelect01">
                                    <option selected="">Choose...</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="inputGroupSelect02">Label &amp; select on right</label>
                            <div class="input-group">
                                <select autocomplete="off" class="form-select" id="inputGroupSelect02"
                                        name="inputGroupSelect02">
                                    <option selected="">Choose...</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                                <label class="input-group-text" for="inputGroupSelect02">Options</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="inputGroupSelect03">Button &amp; select on left</label>
                            <div class="input-group">
                                <button class="btn btn-outline-default" id="button-select-1" name="button-select-1"
                                        type="button">Button
                                </button>
                                <select aria-label="Example select with button addon" autocomplete="off"
                                        class="form-select" id="inputGroupSelect03" name="inputGroupSelect03">
                                    <option selected="">Choose...</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="inputGroupSelect04">Button &amp; select on right</label>
                            <div class="input-group">
                                <select aria-label="Example select with button addon" autocomplete="off"
                                        class="form-select" id="inputGroupSelect04" name="inputGroupSelect04">
                                    <option selected="">Choose...</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                                <button class="btn btn-outline-default" id="button-select-2" name="button-select-2"
                                        type="button">Button
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-11">
                <div class="panel-hdr">
                    <h2>
                        File <span class="fw-300"><i>upload</i></span>
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
                            Combine file upload with addon elements
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="inputGroupFile01">File input with addon</label>
                            <div class="input-group">
                                <span class="input-group-text" id="inputGroupFileAddon01">Upload</span>
                                <input aria-describedby="inputGroupFileAddon01" class="form-control"
                                       id="inputGroupFile01" name="inputGroupFile01" type="file"/>
                            </div>
                            <div class="form-text">Some help content goes here</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="inputGroupFile02">Button &amp; select on right</label>
                            <div class="input-group">
                                <input aria-describedby="inputGroupFileAddon02" class="form-control"
                                       id="inputGroupFile02" name="inputGroupFile02" type="file"/>
                                <span class="input-group-text" id="inputGroupFileAddon02">Upload</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="inputGroupFile03">Button &amp; select on right</label>
                            <div class="input-group">
                                <button class="btn btn-outline-default" id="inputGroupFileAddon03"
                                        name="inputGroupFileAddon03" type="button">Button
                                </button>
                                <input aria-describedby="inputGroupFileAddon03" class="form-control"
                                       id="inputGroupFile03" name="inputGroupFile03" type="file"/>
                            </div>
                            <div class="form-text">Some help content goes here</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="inputGroupFile04">Button &amp; select on right</label>
                            <div class="input-group">
                                <input aria-describedby="inputGroupFileAddon04" class="form-control"
                                       id="inputGroupFile04" name="inputGroupFile04" type="file"/>
                                <button class="btn btn-outline-default" id="inputGroupFileAddon04"
                                        name="inputGroupFileAddon04" type="button">Button
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-12">
                <div class="panel-hdr">
                    <h2>
                        Kitchen <span class="fw-300"><i>sink</i></span>
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
                            A combination of elements working in harmony
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="name-f">Combined elements</label>
                            <div class="input-group">
                                <span class="input-group-text text-success">
                                    <i class="sa sa-user fs-xl"></i>
                                </span>
                                <input autocomplete="username" class="form-control" id="user" name="user"
                                       placeholder="User" type="text"/>
                                <span class="input-group-text">
                                    <i class="sa sa-arrow-right fs-xl"></i>
                                </span>
                                <select aria-label="usertype" autocomplete="off" class="form-select" id="usertype"
                                        name="usertype">
                                    <option selected="">User type</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                                <button class="btn btn-success shadow-0" id="save-button" name="save-button"
                                        type="button">Save
                                </button>
                                <button aria-expanded="false"
                                        class="btn btn-success shadow-0 dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" id="save-dropdown" name="save-dropdown"
                                        type="button">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#">Action</a></li>
                                    <li><a class="dropdown-item" href="#">Another action</a></li>
                                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                                    <li>
                                        <hr class="dropdown-divider"/>
                                    </li>
                                    <li><a class="dropdown-item" href="#">Separated link</a></li>
                                </ul>
                            </div>
                            <div class="form-text">Some help content goes here</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
