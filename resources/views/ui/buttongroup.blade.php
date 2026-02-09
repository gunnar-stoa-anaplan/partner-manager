@extends('layouts.vertical', ['pageTitle' => 'Button Group'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Button Group', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Group a series of buttons together on a single line or stack them in a vertical column.'])
@endsection

@section('content')
    <div class="info-container">
        <p class="mb-0">
            The <code>.btn</code> styles can be applied to other elements, such as <code>&lt;label&gt;</code>s, to
            provide
            checkbox or radio style button toggling. Add <code>data-bs-toggle="buttons"</code> to a
            <code>.btn-group</code>
            containing those modified buttons to enable their toggling behavior via JavaScript.
        </p>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <!--Sizes-->
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>Sizes <span class="fw-300"><i>example</i></span></h2>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Instead of applying button sizing classes to every button in a group, just add
                            <code>.btn-group-*</code> to each <code>.btn-group</code>, including each one when nesting
                            multiple groups.
                        </div>
                        <div class="demo demo-v-spacing-lg">
                            <div aria-label="Large button group" class="btn-group btn-group-lg" role="group">
                                <button class="btn btn-secondary" type="button">Left</button>
                                <button class="btn btn-secondary" type="button">Middle</button>
                                <button class="btn btn-secondary" type="button">Right</button>
                            </div>
                            <br/>
                            <div aria-label="Default button group" class="btn-group" role="group">
                                <button class="btn btn-secondary" type="button">Left</button>
                                <button class="btn btn-secondary" type="button">Middle</button>
                                <button class="btn btn-secondary" type="button">Right</button>
                            </div>
                            <br/>
                            <div aria-label="Small button group" class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-secondary" type="button">Left</button>
                                <button class="btn btn-secondary" type="button">Middle</button>
                                <button class="btn btn-secondary" type="button">Right</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Nesting-->
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>Nesting <span class="fw-300"><i>example</i></span></h2>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Place a <code>.btn-group</code> within another <code>.btn-group</code> when you want
                            dropdown
                            menus mixed with a series of buttons.
                        </div>
                        <div aria-label="Button group with nested dropdown" class="btn-group" role="group">
                            <button class="btn btn-secondary" type="button">1</button>
                            <button class="btn btn-secondary" type="button">2</button>
                            <div class="btn-group" role="group">
                                <button aria-expanded="false" class="btn btn-secondary dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    Dropdown
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                    <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Vertical-->
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>Vertical <span class="fw-300"><i>variation</i></span></h2>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Make a set of buttons appear vertically stacked rather than horizontally using class
                            <code>.btn-group-vertical</code>. <strong>Split button dropdowns are not supported
                                here.</strong>
                        </div>
                        <div aria-label="Vertical button group" class="btn-group-vertical" role="group">
                            <button class="btn btn-primary" type="button">Button</button>
                            <button class="btn btn-primary" type="button">Button</button>
                            <div class="btn-group" role="group">
                                <button aria-expanded="false" class="btn btn-primary dropdown-toggle"
                                        data-bs-toggle="dropdown" type="button">
                                    Dropdown
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                    <li><a class="dropdown-item" href="#">Dropdown link</a></li>
                                </ul>
                            </div>
                            <button class="btn btn-primary" type="button">Button</button>
                            <button class="btn btn-primary" type="button">Button</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <!--Checkbox and radio-->
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>Checkbox and Radio <span class="fw-300"><i>buttons</i></span></h2>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            <code>.button</code> styles can be applied to other elements, such as
                            <code><label></label></code>s, to provide checkbox or radio style button toggling. Add
                            <code>data-toggle="buttons"</code> to a <code>.btn-group</code> containing those modified
                            buttons to enable their toggling behavior via JavaScript and add
                            <code>.btn-group-toggle</code>
                            to style the <code>input</code>s within your buttons. <strong>Note that you can create
                                single
                                input-powered buttons or groups of them.</strong>
                        </div>
                        <div class="alert alert-info">
                            The checked state for these buttons is <strong>only updated via <code>click</code>
                                event</strong> on the button. If you use another method to update the input—e.g., with
                            <code>input type="reset"</code> or by manually applying the input’s <code>checked</code>
                            property—you’ll need to toggle <code>.active</code> on the <code>label</code> manually.
                        </div>
                        <h5 class="frame-heading">Checkbox</h5>
                        <div class="frame-wrap">
                            <div aria-label="Basic checkbox toggle button group" class="btn-group" role="group">
                                <input autocomplete="off" class="btn-check" id="btncheck1" type="checkbox"/>
                                <label class="btn btn-primary" for="btncheck1">Checkbox 1</label>
                                <input autocomplete="off" class="btn-check" id="btncheck2" type="checkbox"/>
                                <label class="btn btn-primary" for="btncheck2">Checkbox 2</label>
                                <input autocomplete="off" class="btn-check" id="btncheck3" type="checkbox"/>
                                <label class="btn btn-primary" for="btncheck3">Checkbox 3</label>
                            </div>
                        </div>
                        <h5 class="frame-heading mt-3">Radio</h5>
                        <div class="frame-wrap">
                            <div aria-label="Basic radio toggle button group" class="btn-group" role="group">
                                <input autocomplete="off" checked="" class="btn-check" id="btnradio1" name="btnradio"
                                       type="radio"/>
                                <label class="btn btn-primary" for="btnradio1">Radio 1</label>
                                <input autocomplete="off" class="btn-check" id="btnradio2" name="btnradio"
                                       type="radio"/>
                                <label class="btn btn-primary" for="btnradio2">Radio 2</label>
                                <input autocomplete="off" class="btn-check" id="btnradio3" name="btnradio"
                                       type="radio"/>
                                <label class="btn btn-primary" for="btnradio3">Radio 3</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Toolbar-->
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>Toolbar <span class="fw-300"><i>example</i></span></h2>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Combine sets of button groups into button toolbars for more complex components. Use utility
                            classes as needed to space out groups, buttons, and more.
                        </div>
                        <div aria-label="Toolbar with button groups" class="btn-toolbar mb-3" role="group">
                            <div aria-label="First group" class="btn-group me-2" role="group">
                                <button class="btn btn-primary" type="button">1</button>
                                <button class="btn btn-primary" type="button">2</button>
                                <button class="btn btn-primary" type="button">3</button>
                                <button class="btn btn-primary" type="button">4</button>
                            </div>
                            <div aria-label="Second group" class="btn-group me-2" role="group">
                                <button class="btn btn-primary" type="button">5</button>
                                <button class="btn btn-primary" type="button">6</button>
                                <button class="btn btn-primary" type="button">7</button>
                                <button class="btn btn-primary" type="button">8</button>
                            </div>
                            <div aria-label="Third group" class="btn-group" role="group">
                                <button class="btn btn-primary" type="button">9</button>
                                <button class="btn btn-primary" type="button">10</button>
                                <button class="btn btn-primary active" type="button">11</button>
                                <button class="btn btn-primary" type="button">12</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
