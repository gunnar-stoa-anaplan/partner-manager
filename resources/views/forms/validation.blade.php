@extends('layouts.vertical', ['pageTitle' => 'Validation'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Inputs', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'Forms', 'pageSubText' => 'Provide valuable, actionable feedback to your users with HTML5 form validation.'])
@endsection

@section('content')
    <div class="info-container">
        Validation styles are available for the following form controls and components: <code>&lt;input&gt;</code> and
        <code>&lt;textarea&gt;</code> with <code>.form-control</code>, <code>&lt;select&gt;</code> with <code>.form-select</code>,
        and <code>.form-check</code> inputs.
    </div>
    <div class="row">
        <div class="col-xl-6">
            <div id="panel-1" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>
                        Invalid <span class="fw-300"><i>form styles</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button type="button" class="btn btn-panel" data-action="panel-collapse"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Toggle">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-fullscreen"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen">
                            <svg class="sa-icon">

                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <form class="was-validated">
                            <div class="mb-3">
                                <label class="form-label" for="simpleinputInvalid">Text input</label>
                                <input type="text" class="form-control is-invalid" id="simpleinputInvalid" required>
                                <div class="invalid-feedback">
                                    Please enter a message in the textarea.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="validationTextarea3">Textarea</label>
                                <textarea class="form-control is-invalid" id="validationTextarea3"
                                          placeholder="Required example textarea" required rows="4"></textarea>
                                <div class="invalid-feedback">
                                    Please enter a message in the textarea.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Checkbox & Radio</label>
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="customControlValidation1"
                                           required>
                                    <label class="form-check-label" for="customControlValidation1">Check this custom
                                        checkbox</label>
                                    <div class="invalid-feedback">Example invalid feedback text</div>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="radio" class="form-check-input" id="customControlValidation2"
                                           name="radio-stacked" required>
                                    <label class="form-check-label" for="customControlValidation2">Toggle this custom
                                        radio</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="customControlValidation3"
                                           name="radio-stacked" required>
                                    <label class="form-check-label" for="customControlValidation3">Or toggle this other
                                        custom radio</label>
                                    <div class="invalid-feedback">More example invalid feedback text</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Switch elements</label>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input is-invalid"
                                           id="customControlValidation4" required>
                                    <label class="form-check-label" for="customControlValidation4">Toggle this switch
                                        element </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="customControlValidation5">Custom select</label>
                                <select class="form-select" required id="customControlValidation5">
                                    <option value="">Open this select menu</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                                <div class="invalid-feedback">Example invalid custom select feedback</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="customControlValidation6">Multiple Select</label>
                                <select id="customControlValidation6" multiple class="form-select" required>
                                    <option>1</option>
                                    <option>2</option>
                                    <option>3</option>
                                    <option>4</option>
                                    <option>5</option>
                                </select>
                                <div class="invalid-feedback">Please select at least one option</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="customFile">File input</label>
                                <input type="file" class="form-control" id="customFile" required>
                                <div class="invalid-feedback">Please choose a file</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="panel-2" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>
                        Custom <span class="fw-300"><i>Styles</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button type="button" class="btn btn-panel" data-action="panel-collapse"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Toggle">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-fullscreen"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen">
                            <svg class="sa-icon">

                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            <p>For custom Bootstrap form validation messages, you'll need to add the
                                <code>novalidate</code> boolean attribute to your <code>&lt;form&gt;</code>. This
                                disables the browser default feedback tooltips, but still provides access to the form
                                validation APIs in JavaScript. Try to submit the form below; our JavaScript will
                                intercept the submit button and relay feedback to you. When attempting to submit, you'll
                                see the <code>:invalid</code> and <code>:valid</code> styles applied to your form
                                controls.</p>
                            <p>Custom feedback styles apply custom colors, borders, focus styles, and background icons
                                to better communicate feedback. Background icons for <code>&lt;select&gt;</code>s are
                                only available with <code>.custom-select</code>, and not <code>.form-control</code>.</p>
                        </div>
                    </div>
                    <div class="panel-content">
                        <form class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="validationCustom01">First name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="validationCustom01"
                                           placeholder="First name" value="Codex" required>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="validationCustom02">Last name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="validationCustom02"
                                           placeholder="Last name" value="Lantern" required>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="validationCustomUsername">Username <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                                        <input type="text" class="form-control" id="validationCustomUsername"
                                               placeholder="Username" aria-describedby="inputGroupPrepend" required>
                                        <div class="invalid-feedback">Please choose a username.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="validationCustom03">City <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="validationCustom03" required>
                                    <div class="invalid-feedback">Please provide a valid city.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="validationCustom04">State <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="validationCustom04" required>
                                        <option selected disabled value="">Choose...</option>
                                        <option>Michigan</option>
                                        <option>New York</option>
                                        <option>Oklahoma</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a valid state.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="validationCustom05">Zip <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="validationCustom05" required>
                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="invalidCheck" required>
                                        <label class="form-check-label" for="invalidCheck">
                                            Agree to terms and conditions <span class="text-danger">*</span>
                                        </label>
                                        <div class="invalid-feedback">
                                            You must agree before submitting.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit">Submit form</button>
                                </div>
                            </div>
                        </form>
                        <script>
                            (() => {
                                'use strict'
                                const forms = document.querySelectorAll('.needs-validation')
                                Array.from(forms).forEach(form => {
                                    form.addEventListener('submit', event => {
                                        if (!form.checkValidity()) {
                                            event.preventDefault()
                                            event.stopPropagation()
                                        }
                                        form.classList.add('was-validated')
                                    }, false)
                                })
                            })()
                        </script>
                    </div>
                </div>
            </div>
            <div id="panel-3" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>
                        Server <span class="fw-300"><i>side</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button type="button" class="btn btn-panel" data-action="panel-collapse"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Toggle">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-fullscreen"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen">
                            <svg class="sa-icon">

                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            We recommend using client-side validation, but in case you require server-side validation,
                            you can indicate invalid and valid form fields with <code>.is-invalid</code> and <code>.is-valid</code>.
                            Note that <code>.invalid-feedback</code> is also supported with these classes
                        </div>
                        <form>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="validationServer01">First name</label>
                                    <input type="text" class="form-control is-valid" id="validationServer01"
                                           placeholder="First name" value="Mark" required>
                                    <div class="valid-feedback">
                                        Looks good!
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="validationServer02">Last name</label>
                                    <input type="text" class="form-control is-valid" id="validationServer02"
                                           placeholder="Last name" value="Otto" required>
                                    <div class="valid-feedback">
                                        Looks good!
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="validationServerUsername">Username</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text" id="inputGroupPrepend3">@</span>
                                        <input type="text" class="form-control is-invalid" id="validationServerUsername"
                                               placeholder="Username" aria-describedby="inputGroupPrepend3" required>
                                        <div class="invalid-feedback">
                                            Please choose a username.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="validationServer03">City</label>
                                    <input type="text" class="form-control is-invalid" id="validationServer03"
                                           placeholder="City" required>
                                    <div class="invalid-feedback">
                                        Please provide a valid city.
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="validationServer04">State</label>
                                    <input type="text" class="form-control is-invalid" id="validationServer04"
                                           placeholder="State" required>
                                    <div class="invalid-feedback">
                                        Please provide a valid state.
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="validationServer05">Zip</label>
                                    <input type="text" class="form-control is-invalid" id="validationServer05"
                                           placeholder="Zip" required>
                                    <div class="invalid-feedback">
                                        Please provide a valid zip.
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input is-invalid" type="checkbox" value=""
                                           id="invalidCheck3" required>
                                    <label class="form-check-label" for="invalidCheck3">
                                        Agree to terms and conditions
                                    </label>
                                    <div class="invalid-feedback">
                                        You must agree before submitting.
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary" type="submit">Submit form</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div id="panel-4" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>
                        Valid <span class="fw-300"><i>form styles</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button type="button" class="btn btn-panel" data-action="panel-collapse"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Toggle">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-fullscreen"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen">
                            <svg class="sa-icon">

                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <form class="was-validated">
                            <div class="mb-3">
                                <label class="form-label" for="simpleinputInvalid2">Text input</label>
                                <input type="text" class="form-control is-valid" id="simpleinputInvalid2" required
                                       value="test">
                                <div class="valid-feedback">
                                    Please enter a message in the textarea.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="validationTextarea">Textarea</label>
                                <textarea class="form-control is-valid" id="validationTextarea"
                                          placeholder="Required example textarea" required rows="4">test</textarea>
                                <div class="valid-feedback">
                                    Please enter a message in the textarea.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Checkbox & Radio</label>
                                <div class="custom-control mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                               id="customControlValidationSuccess1" required checked="">
                                        <label class="form-check-label" for="customControlValidationSuccess1">Check this
                                            custom checkbox</label>
                                        <div class="valid-feedback">Example valid feedback text</div>
                                    </div>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="radio" class="form-check-input" id="customControlValidationSuccess2"
                                           name="radio-stacked" required checked="">
                                    <label class="form-check-label" for="customControlValidationSuccess2">Toggle this
                                        custom radio</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="customControlValidationSuccess3"
                                           name="radio-stacked" required>
                                    <label class="form-check-label" for="customControlValidationSuccess3">Or toggle this
                                        other custom radio</label>
                                    <div class="valid-feedback">More example valid feedback text</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Switch elements</label>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input is-valid"
                                           id="customControlValidationSuccess4" required checked="">
                                    <label class="form-check-label" for="customControlValidationSuccess4">Toggle this
                                        switch element </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="customControlValidationSuccess5">Custom select</label>
                                <select class="form-select" required id="customControlValidationSuccess5">
                                    <option value="">Open this select menu</option>
                                    <option value="1" selected="">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                                <div class="valid-feedback">Example valid custom select feedback</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="customControlValidationSuccess6">Multiple Select</label>
                                <select id="customControlValidationSuccess6" multiple class="form-select" required>
                                    <option selected="">1</option>
                                    <option selected="">2</option>
                                    <option>3</option>
                                    <option>4</option>
                                    <option>5</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="customFile">File input</label>
                                <input type="file" class="form-control" id="customFile" required>
                                <div class="invalid-feedback">Please choose a file</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="panel-5" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>
                        Tooltip <span class="fw-300"><i>Styles</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button type="button" class="btn btn-panel" data-action="panel-collapse"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Toggle">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-fullscreen"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen">
                            <svg class="sa-icon">

                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#x-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            <p>If your form layout allows it, you can swap the <code>.{valid|invalid}-feedback</code>
                                classes for <code>.{valid|invalid}-tooltip</code> classes to display validation feedback
                                in a styled tooltip. Be sure to have a parent with <code>position: relative</code> on it
                                for tooltip positioning. In the example below, our column classes have this already, but
                                your project may require an alternative setup.</p>
                        </div>
                    </div>
                    <div class="panel-content p-0">
                        <form class="needs-validation" novalidate>
                            <div class="panel-content">
                                <div class="row">
                                    <div class="col-md-4 mb-3 position-relative">
                                        <label class="form-label" for="validationTooltip01">First name <span
                                                class="text-danger">*</span> </label>
                                        <input type="text" class="form-control" id="validationTooltip01"
                                               placeholder="First name" value="Codex" required>
                                        <div class="valid-tooltip">
                                            Looks good!
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 position-relative">
                                        <label class="form-label" for="validationTooltip02">Last name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="validationTooltip02"
                                               placeholder="Last name" value="Lantern" required>
                                        <div class="valid-tooltip">
                                            Looks good!
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 position-relative">
                                        <label class="form-label" for="validationTooltip03">Username <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text" id="inputGroupPrepend2">@</span>
                                            <input type="text" class="form-control" id="validationTooltip03"
                                                   placeholder="Username" aria-describedby="inputGroupPrepend2"
                                                   required>
                                            <div class="invalid-tooltip">
                                                Please choose a username.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3 position-relative">
                                        <label class="form-label" for="validationTooltip04">City <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="validationTooltip04"
                                               placeholder="City" required>
                                        <div class="invalid-tooltip">
                                            Please provide a valid city.
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3 position-relative">
                                        <label class="form-label" for="validationCustom04">State <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="validationCustom04" required="">
                                            <option selected="" disabled="" value="">Choose...</option>
                                            <option>Michigan</option>
                                            <option>New York</option>
                                            <option>Oklahoma</option>
                                        </select>
                                        <div class="invalid-tooltip">Please select a valid state.</div>
                                    </div>
                                    <div class="col-md-3 mb-3 position-relative">
                                        <label class="form-label" for="validationTooltip06">Zip <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="validationTooltip06"
                                               placeholder="Zip" required>
                                        <div class="invalid-tooltip">
                                            Please provide a valid zip.
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3 position-relative">
                                        <label class="form-label" for="validationTooltip07">Comment <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control" id="validationTooltip07"
                                                  placeholder="Required example textarea" required=""></textarea>
                                        <div class="invalid-tooltip">
                                            Please enter a message in the textarea.
                                        </div>
                                    </div>
                                    <div class="col-12 position-relative">
                                        <label class="form-label mb-2">Please disclose your gender profile <span
                                                class="text-danger">*</span></label>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input"
                                                   id="validationTooltipGenderMale" name="radio-stacked" required="">
                                            <label class="form-check-label"
                                                   for="validationTooltipGenderMale">Male</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input"
                                                   id="validationTooltipGenderFemale" name="radio-stacked" required="">
                                            <label class="form-check-label"
                                                   for="validationTooltipGenderFemale">Female</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input"
                                                   id="validationTooltipgenderPrivate" name="radio-stacked" required="">
                                            <label class="form-check-label" for="validationTooltipgenderPrivate">Prefer
                                                not to say</label>
                                            <div class="invalid-tooltip">Please select at least one</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="panel-content border-faded border-start-0 border-end-0 border-bottom-0 d-flex flex-row align-items-center">
                                <div class="form-check position-relative">
                                    <input type="checkbox" class="form-check-input" id="validationTooltipAgreement"
                                           required>
                                    <label class="form-check-label" for="validationTooltipAgreement">Agree to terms and
                                        conditions <span class="text-danger">*</span></label>
                                    <div class="invalid-tooltip">
                                        You must agree before submitting.
                                    </div>
                                </div>

                                <button class="btn btn-primary ms-auto" type="submit">Submit form</button>
                            </div>
                        </form>
                        <script>
                            (() => {
                                'use strict'
                                const forms = document.querySelectorAll('.needs-validation')
                                Array.from(forms).forEach(form => {
                                    form.addEventListener('submit', event => {
                                        if (!form.checkValidity()) {
                                            event.preventDefault()
                                            event.stopPropagation()
                                        }
                                        form.classList.add('was-validated')
                                    }, false)
                                })
                            })()
                        </script>
                    </div>
                </div>
            </div>
            <div id="panel-6" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>
                        Browser <span class="fw-300"><i>defaults</i></span>
                    </h2>
                    <div class="panel-toolbar">
                        <button type="button" class="btn btn-panel" data-action="panel-collapse"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Toggle">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-fullscreen"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10"
                                data-bs-original-title="Fullscreen">
                            <svg class="sa-icon">

                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                        <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
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
                                Not interested in custom validation or JavaScript? No problem—use browser defaults.
                                Try submitting the form below to see built-in feedback styles, which vary by browser and
                                OS.
                                While CSS can't style them, you can customize the text with JavaScript.
                            </p>
                        </div>
                    </div>
                    <div class="panel-content p-0">
                        <form>
                            <div class="panel-content">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="validationDefault01">First name</label>
                                        <input type="text" class="form-control" id="validationDefault01"
                                               placeholder="First name" value="Mark" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="validationDefault02">Last name</label>
                                        <input type="text" class="form-control" id="validationDefault02"
                                               placeholder="Last name" value="Otto" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="validationDefaultUsername">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text" id="inputGroupPrepend4">@</span>
                                            <input type="text" class="form-control" id="validationDefaultUsername"
                                                   placeholder="Username" aria-describedby="inputGroupPrepend4"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="validationDefault03">City</label>
                                        <input type="text" class="form-control" id="validationDefault03"
                                               placeholder="City" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label" for="validationDefault04">State</label>
                                        <input type="text" class="form-control" id="validationDefault04"
                                               placeholder="State" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label" for="validationDefault05">Zip</label>
                                        <input type="text" class="form-control" id="validationDefault05"
                                               placeholder="Zip" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="invalidCheck2" required="">
                                        <label class="form-check-label" for="invalidCheck2">Agree to terms and
                                            conditions</label>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="panel-content border-faded border-start-0 border-end-0 border-bottom-0 d-flex flex-row">
                                <button class="btn btn-primary ms-auto" type="submit">Submit form</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
