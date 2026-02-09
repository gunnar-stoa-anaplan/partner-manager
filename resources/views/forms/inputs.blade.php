@extends('layouts.vertical', ['pageTitle' => ''])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Inputs', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'Forms', 'pageSubText' => 'Form inputs styled and customizable input elements using classes like <code>.form-control</code>, <code>.form-check</code>, and <code>.form-select</code> for consistent design and functionality.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        General <span class="fw-300"><i>inputs</i></span>
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
                            <p>Be sure to use an appropriate type attribute on all inputs (e.g., code <code>email</code>
                                for
                                email address or <code>number</code> for numerical information) to take advantage of
                                newer
                                input controls like email verification, number selection, and more.</p>
                        </div>
                        <form>
                            <div class="mb-3">
                                <label class="form-label" for="simpleinput">Text</label>
                                <input class="form-control" id="simpleinput" type="text"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-email-2">Email</label>
                                <input class="form-control" id="example-email-2" name="example-email-2"
                                       placeholder="Email"
                                       type="email"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-password">Password</label>
                                <input class="form-control" id="example-password" type="password" value="password"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-palaceholder">Placeholder</label>
                                <input class="form-control" id="example-palaceholder" placeholder="placeholder"
                                       type="text"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-textarea">Text area</label>
                                <textarea class="form-control" id="example-textarea" rows="5"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-static">Static control</label>
                                <input class="form-control-plaintext" id="example-static" readonly="" type="text"
                                       value="email@example.com"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-helping">Helping text</label>
                                <input class="form-control" id="example-helping" placeholder="Helping text"
                                       type="text"/>
                                <div class="form-text">
                                    A block of help text that breaks onto a new line and may extend beyond one line.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-select">Input Select</label>
                                <select class="form-select" id="example-select">
                                    <option>1</option>
                                    <option>2</option>
                                    <option>3</option>
                                    <option>4</option>
                                    <option>5</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-multiselect">Multiple Select</label>
                                <select class="form-select" id="example-multiselect" multiple="">
                                    <option>1</option>
                                    <option>2</option>
                                    <option>3</option>
                                    <option>4</option>
                                    <option>5</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-fileinput">Default file input</label>
                                <input class="form-control" id="example-fileinput" type="file"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-datetime-local-input">Date and time</label>
                                <input class="form-control" id="example-datetime-local-input" type="datetime-local"
                                       value="2023-07-23T11:25:00"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-date">Date</label>
                                <input class="form-control" id="example-date" name="date" type="date"
                                       value="2023-07-23"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-month">Month</label>
                                <input class="form-control" id="example-month" name="month" type="month"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-week">Week</label>
                                <input class="form-control" id="example-week" name="week" type="week"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-time-2">Time</label>
                                <input class="form-control" id="example-time-2" name="time" type="time"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-time-3">Time</label>
                                <input class="form-control" id="example-time-3" name="time" type="time"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-number">Number</label>
                                <input class="form-control" id="example-number" name="number" type="number"
                                       value="839473"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-color">Color</label>
                                <input class="form-control form-control-color" id="example-color" name="color"
                                       type="color" value="#727cf5"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-range">Range (form-control)</label>
                                <input class="form-control" id="example-range" max="100" min="0"
                                       name="range" type="range"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="customRange2">Range</label>
                                <input class="form-range" id="customRange2" type="range"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Select (custom)</label>
                                <select class="form-select">
                                    <option selected="">Open this select menu</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">File Browser</label>
                                <input class="form-control" id="customFile" type="file"/>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        General <span class="fw-300"><i>inputs (disabled)</i></span>
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
                            <p>
                                Add disabled to an <code>&lt;input&gt;</code>, <code>&lt;select&gt;</code>, or
                                <code>&lt;button&gt;</code> to block interactions, with Bootstrap styling them
                                accordingly. Controls inside a <code>&lt;fieldset disabled&gt;</code> are also disabled.
                                For
                                <code>&lt;a&gt;</code> elements, Bootstrap
                                uses <code>pointer-events: none;</code> and reduced opacity instead.
                            </p>
                        </div>
                        <form>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="simpleinput-disabled">Text disabled</label>
                                <input class="form-control" disabled="" id="simpleinput-disabled" type="text"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-email-disabled">Email disabled</label>
                                <input class="form-control" disabled="" id="example-email-disabled"
                                       name="example-email-disabled" placeholder="Email" type="email"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-password-disabled">Password
                                    disabled</label>
                                <input class="form-control" disabled="" id="example-password-disabled"
                                       type="password" value="password"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-palaceholder-disabled">Placeholder
                                    disabled</label>
                                <input class="form-control" disabled="" id="example-palaceholder-disabled"
                                       placeholder="placeholder" type="text"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-textarea-disabled">Text area
                                    disabled</label>
                                <textarea class="form-control" disabled="" id="example-textarea-disabled"
                                          rows="5"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-static-disabled">Static control
                                    disabled</label>
                                <input class="form-control-plaintext" disabled="" id="example-static-disabled"
                                       readonly="" type="text" value="email@example.com"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-helping-disabled">Helping text
                                    disabled</label>
                                <input class="form-control" disabled="" id="example-helping-disabled"
                                       placeholder="Helping text" type="text"/>
                                <div class="form-text">
                                    A block of help text that breaks onto a new line and may extend beyond one line.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-select-disabled">Input Select
                                    disabled</label>
                                <select class="form-select" disabled="" id="example-select-disabled">
                                    <option>1</option>
                                    <option>2</option>
                                    <option>3</option>
                                    <option>4</option>
                                    <option>5</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-multiselect-disabled">Multiple Select
                                    disabled</label>
                                <select class="form-select" disabled="" id="example-multiselect-disabled"
                                        multiple="">
                                    <option>1</option>
                                    <option>2</option>
                                    <option>3</option>
                                    <option>4</option>
                                    <option>5</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-fileinput-disabled">Default file input
                                    disabled</label>
                                <input class="form-control" disabled="" id="example-fileinput-disabled"
                                       type="file"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-datetime-local-input-disabled">Date
                                    and
                                    time disabled</label>
                                <input class="form-control" disabled="" id="example-datetime-local-input-disabled"
                                       type="datetime-local" value="2023-07-23T11:25:00"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-date-disabled">Date disabled</label>
                                <input class="form-control" disabled="" id="example-date-disabled" name="date"
                                       type="date"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-month-disabled">Month disabled</label>
                                <input class="form-control" disabled="" id="example-month-disabled" name="month"
                                       type="month"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-week-disabled">Week disabled</label>
                                <input class="form-control" disabled="" id="example-week-disabled" name="week"
                                       type="week"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-time-disabled">Time disabled</label>
                                <input class="form-control" disabled="" id="example-time-disabled" name="time"
                                       type="time"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-time-disabled-2">Time disabled</label>
                                <input class="form-control" disabled="" id="example-time-disabled-2" name="time"
                                       type="time"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-number">Number disabled</label>
                                <input class="form-control" disabled="" id="example-number-disabled" name="number"
                                       type="number"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-color">Color disabled</label>
                                <input class="form-control form-control-color disabled" disabled=""
                                       id="example-color-disabled" name="color" type="color" value="#ed1c24"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="example-range-disabled">Range disabled</label>
                                <input class="form-control" disabled="" id="example-range-disabled" max="100"
                                       min="0" name="range" type="range"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted" for="customRange1">Range (disabled)</label>
                                <input class="form-range" disabled="" id="customRange1" type="range"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Select (disabled)</label>
                                <select class="form-select" disabled="">
                                    <option selected="">Open this select menu</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label text-muted">File Browser (disabled)</label>
                                <input class="form-control" disabled="" id="customFile-2" type="file"/>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Floating <span class="fw-300"><i>Labels</i></span>
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
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="frame-wrap mb-4">
                                    <div class="form-floating mb-3">
                                        <input class="form-control" id="floatingInput" placeholder="name@example.com"
                                               type="email"/>
                                        <label for="floatingInput">Email address</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input class="form-control" id="floatingPassword" placeholder="Password"
                                               type="password"/>
                                        <label for="floatingPassword">Password</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <textarea class="form-control" id="floatingTextarea"
                                                  placeholder="Leave a comment here" style="height: 100px"></textarea>
                                        <label for="floatingTextarea">Comments</label>
                                    </div>
                                    <div class="form-floating">
                                        <select aria-label="Floating label select example" class="form-select"
                                                id="floatingSelect">
                                            <option selected="">Open this select menu</option>
                                            <option value="1">One</option>
                                            <option value="2">Two</option>
                                            <option value="3">Three</option>
                                        </select>
                                        <label for="floatingSelect">Works with selects</label>
                                    </div>
                                </div>
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
                        Floating <span class="fw-300"><i>Labels (Disabled)</i></span>
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
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="frame-wrap mb-4">
                                    <div class="form-floating mb-3">
                                        <input class="form-control" disabled="" id="floatingInputDisabled"
                                               placeholder="name@example.com" type="email"/>
                                        <label for="floatingInputDisabled">Email address</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <input class="form-control" disabled="" id="floatingPasswordDisabled"
                                               placeholder="Password" type="password"/>
                                        <label for="floatingPasswordDisabled">Password</label>
                                    </div>
                                    <div class="form-floating mb-3">
                                        <textarea class="form-control" disabled="" id="floatingTextareaDisabled"
                                                  placeholder="Leave a comment here"
                                                  style="height: 100px"></textarea>
                                        <label for="floatingTextareaDisabled">Comments</label>
                                    </div>
                                    <div class="form-floating">
                                        <select aria-label="Floating label select example" class="form-select"
                                                disabled="" id="floatingSelectDisabled">
                                            <option selected="">Open this select menu</option>
                                            <option value="1">One</option>
                                            <option value="2">Two</option>
                                            <option value="3">Three</option>
                                        </select>
                                        <label for="floatingSelectDisabled">Works with selects</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-12">
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Input <span class="fw-300"><i>Sizes</i></span>
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
                            Set heights using classes like <code>.form-control-lg</code>, <code>.form-control-sm</code>
                            and
                            for custom inputs, you can use <code>.form-select-sm</code>, <code>.form-select-lg</code>,
                            and
                            set widths using grid column classes like <code>.col-lg-*</code>
                        </div>
                        <form>
                            <div class="mb-3">
                                <label class="form-label" for="example-input-small">Small</label>
                                <input class="form-control form-control-sm" id="example-input-small"
                                       name="example-input-small" placeholder=".form-control-sm" type="text"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-input-normal">Normal</label>
                                <input class="form-control" id="example-input-normal" name="example-input-normal"
                                       placeholder="Normal" type="text"/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-input-large">Large</label>
                                <input class="form-control form-control-lg" id="example-input-large"
                                       name="example-input-large" placeholder=".form-control-lg" type="text"/>
                            </div>
                            <div class="mb-0">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="example-gridsize">Grid Sizes</label>
                                    </div>
                                    <div class="col-6 text-end">
                                        <a href="{{ route('second', [ 'utilities' , 'responsivegrid']) }}"
                                           target="_blank" title="Responsive Grid">Check out
                                            how it works</a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-3">
                                        <input class="form-control border-primary" id="example-gridsize"
                                               placeholder=".col-3" type="text"/>
                                    </div>
                                    <div class="col">
                                        <input class="form-control border-success" id="example-gridsize-2"
                                               placeholder=".col" type="text"/>
                                    </div>
                                    <div class="col-12">
                                        <div class="mt-3">
                                            <input class="form-range" id="gridrange" max="12" min="1"
                                                   name="gridrange" type="range" value="3"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Altering <span class="fw-300"><i>with utilities</i></span>
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
                                <use href="/icons/sprite    svg#stop-circle"></use>
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
                            You can easily alter the border, border-radius and padding using the utility classes.
                        </div>
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="form-label" for="example-input-square">Square edges</label>
                                    <input class="form-control rounded-0" id="example-input-square"
                                           placeholder="Square borders" type="text"/>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="form-label" for="example-input-pill">Rounded pill</label>
                                    <input class="form-control rounded-pill" id="example-input-pill"
                                           placeholder="Rounded pill" type="text"/>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="form-label" for="example-input-material">Material</label>
                                    <input
                                        class="form-control rounded-0 border-top-0 border-start-0 border-end-0 px-0 bg-transparent"
                                        id="example-input-material" placeholder="Material" type="text"/>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="form-label" for="example-input-border">Border color</label>
                                    <input class="form-control border-info" id="example-input-border"
                                           placeholder="Border colors" type="text"/>
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
@endsection
