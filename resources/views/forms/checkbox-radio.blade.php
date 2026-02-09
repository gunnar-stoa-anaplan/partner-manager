@extends('layouts.vertical', ['pageTitle' => 'Checkbox & Radio'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Checkbox & Radio', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'Forms', 'pageSubText' => 'Checkboxes and radio buttons form elements with support for custom designs using classes like <code>.form-check</code> and <code>.form-check-input</code>.'])
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-6">
            <div id="panel-1" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>
                        Checkbox <span class="fw-300"><i>inputs</i></span>
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
                            Add <code>.form-check</code> wrapper to your <code>input</code> and <code>label</code> to
                            create a custom checkbox component. You can add <code>.rounded-circle</code> to the input to
                            make it circular.
                        </div>
                        <button id="js-checkbox-toggle" type="button" data-toggle="button"
                                class="btn btn-outline-danger mb-g">Change to CIRCLE
                        </button>

                        <h5 class="frame-heading">Block alignment</h5>
                        <div class="frame-wrap">
                            <div class="demo demo-checkbox">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="defaultUnchecked">
                                    <label class="form-check-label" for="defaultUnchecked">Unchecked</label>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="defaultChecked" checked>
                                    <label class="form-check-label" for="defaultChecked">Checked</label>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="defaultIndeterminate" checked>
                                    <label class="form-check-label" for="defaultIndeterminate">Indeterminate</label>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="defaultUncheckedDisabled"
                                           disabled>
                                    <label class="form-check-label" for="defaultUncheckedDisabled">Unchecked &
                                        disabled</label>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="defaultCheckedDisabled" checked
                                           disabled>
                                    <label class="form-check-label" for="defaultCheckedDisabled">Checked &
                                        disabled</label>
                                </div>
                            </div>
                        </div>

                        <h5 class="frame-heading">Inline alignment</h5>
                        <div class="frame-wrap demo-checkbox">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="defaultInline1">
                                <label class="form-check-label" for="defaultInline1">Unchecked</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="defaultInline2" checked>
                                <label class="form-check-label" for="defaultInline2">Checked</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="defaultInlined" disabled>
                                <label class="form-check-label" for="defaultInlined">Disabled</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="defaultInline3" checked disabled>
                                <label class="form-check-label" for="defaultInline3">Checked & disabled</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div id="panel-2" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>
                        Radio <span class="fw-300"><i>inputs</i></span>
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
                            Add <code>.form-check</code> wrapper to your <code>input</code> and <code>label</code> to
                            create a custom radio component. You can add <code>.rounded</code> to change it to a rounded
                            radio
                        </div>
                        <button id="js-radio-toggle" type="button" data-toggle="button"
                                class="btn btn-outline-danger mb-g">Change to ROUNDED
                        </button>
                        <h5 class="frame-heading">Block alignment</h5>
                        <div class="frame-wrap">
                            <div class="demo demo-radio">
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="defaultUncheckedRadio"
                                           name="defaultExampleRadios">
                                    <label class="form-check-label" for="defaultUncheckedRadio">Unchecked</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="defaultCheckedRadio"
                                           name="defaultExampleRadios" checked>
                                    <label class="form-check-label" for="defaultCheckedRadio">Checked</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input active" id="defaultUncheckedRadio2"
                                           name="defaultExampleRadios">
                                    <label class="form-check-label" for="defaultUncheckedRadio2">Unchecked</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="defaultUncheckedDisabledRadio2"
                                           name="defaultExampleRadios1" disabled>
                                    <label class="form-check-label"
                                           for="defaultUncheckedDisabledRadio2">Disabled</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="defaultCheckedDisabledRadio2"
                                           name="defaultCheckedDisabledRadio2" checked disabled>
                                    <label class="form-check-label" for="defaultCheckedDisabledRadio2">Checked &
                                        disabled</label>
                                </div>
                            </div>
                        </div>
                        <h5 class="frame-heading">Inline alignment</h5>
                        <div class="frame-wrap demo-radio">
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" id="defaultInline1Radio"
                                       name="inlineDefaultRadiosExample">
                                <label class="form-check-label" for="defaultInline1Radio">Unchecked</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" id="defaultInline2Radio"
                                       name="inlineDefaultRadiosExample" checked>
                                <label class="form-check-label" for="defaultInline2Radio">Checked</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" id="defaultInline4Radio"
                                       name="inlineDefaultRadiosExample2" disabled>
                                <label class="form-check-label" for="defaultInline4Radio">Disabled</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" id="defaultInline3Radio"
                                       name="inlineDefaultRadiosExample2" checked disabled>
                                <label class="form-check-label" for="defaultInline3Radio">Checked & disabled</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-12">
            <div id="panel-3" class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>
                        Switch <span class="fw-300"><i>radio and checkbox</i></span>
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
                            A switch has the markup of a custom checkbox but uses <code>.form-switch</code> to render a
                            toggle switch. Switches also support the <code>disabled</code> attribute
                        </div>
                        <div class="row">
                            <div class="col-xl-6">
                                <h5 class="frame-heading">
                                    Switch checkbox
                                </h5>
                                <div class="frame-wrap demo">
                                    <div class="demo">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="customSwitch1">
                                            <label class="form-check-label" for="customSwitch1">Unchecked</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="customSwitch2" checked>
                                            <label class="form-check-label" for="customSwitch2">Checked</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" disabled id="customSwitchd">
                                            <label class="form-check-label" for="customSwitchd">Disabled</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="customSwitch3" checked
                                                   disabled>
                                            <label class="form-check-label" for="customSwitch3">Checked &
                                                disabled</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <h5 class="frame-heading">Switch Radio</h5>
                                <div class="frame-wrap demo">
                                    <div class="demo">
                                        <div class="form-check form-switch">
                                            <input type="radio" class="form-check-input" id="customSwitch1radio"
                                                   name="defaultSwitchRadioExample">
                                            <label class="form-check-label" for="customSwitch1radio">Unchecked</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="radio" class="form-check-input" id="customSwitch2radio" checked
                                                   name="defaultSwitchRadioExample">
                                            <label class="form-check-label" for="customSwitch2radio">Checked</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="radio" class="form-check-input" disabled
                                                   id="customSwitchdradio" name="defaultSwitchRadioExample2">
                                            <label class="form-check-label" for="customSwitchdradio">Disabled</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="radio" class="form-check-input" id="customSwitch3radio" checked
                                                   disabled name="defaultSwitchRadioExample2">
                                            <label class="form-check-label" for="customSwitch3radio">Checked &
                                                disabled</label>
                                        </div>
                                    </div>
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
    @vite(['resources/scripts/pages/checkboxandradio.js'])
@endsection
