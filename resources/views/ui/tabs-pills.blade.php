@extends('layouts.vertical', ['pageTitle' => 'Tabs & Pills'])

@section('css')

@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Tabs & Pills', 'pageSubTitle1' => 'Design', 'pageSubTitle2'=> 'UI Components', 'pageSubText' => 'Takes the basic nav from above and adds the <code>.nav-tabs</code> class to generate a tabbed interface. Use them to create tabbable regions with our tab JavaScript plugin.'])
@endsection

@section('content')
    <div class="info-container">
        <p>
            Tabs and pills are UI components that allow users to navigate between different sections of content without
            leaving the current page. They enhance user experience by organizing information in a compact and accessible
            manner, making it easier to switch between related content areas. This interface is particularly useful for
            displaying content that is closely related but requires separation for clarity.
        </p>
        <p class="mb-0">
            Learn more about this component on bootstrap's <a href="https://getbootstrap.com/docs/5.3/components/tabs/"
                                                              target="_blank">official documentation</a>.
        </p>
    </div>
    <div class="row">
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2>
                        Default <span class="fw-300"><i>Tabs</i></span>
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
                            The most minimalistic approach to tabs. We add <code>.nav.nav-tabs</code> to an UL and
                            <code>.tab-content</code> to adjacet element. Tabs are triggered by the data attribute
                            <code>data-toggle="tab"</code> and <code>href="#tab_content"</code> you will link the tab
                            you would like to display
                        </div>
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab_default-1"
                                                    role="tab">Home</a></li>
                            <li class="nav-item dropdown">
                                <a aria-expanded="false" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
                                   href="#" role="button">Profile</a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" data-bs-toggle="tab" href="#tab_default-2" role="tab">Personal</a>
                                    <a class="dropdown-item" href="#">Friends</a>
                                    <a class="dropdown-item" href="#">Settings</a>
                                </div>
                            </li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_default-3"
                                                    role="tab">Set</a></li>
                            <li class="nav-item"><a class="nav-link disabled" data-bs-toggle="tab" href="#" role="tab">Disabled</a>
                            </li>
                        </ul>
                        <div class="tab-content p-3">
                            <div class="tab-pane fade show active" id="tab_default-1" role="tabpanel">
                                Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown
                                aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan
                                helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher synth. Cosby sweater eu
                                banh mi, qui irure terry richardson ex squid. Aliquip placeat salvia cillum iphone.
                            </div>
                            <div class="tab-pane fade" id="tab_default-2" role="tabpanel">
                                Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid.
                                Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson
                                artisan four loko farm-to-table craft beer twee. Qui photo booth letterpress, commodo
                                enim craft beer mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud
                                organic.
                            </div>
                            <div class="tab-pane fade" id="tab_default-3" role="tabpanel">
                                Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's
                                organic lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify
                                pitchfork tattooed craft beer, iphone skateboard locavore carles etsy salvia banksy
                                hoodie helvetica. DIY synth PBR banksy irony. Leggings gentrify squid 8-bit cred
                                pitchfork.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2>
                        Tab <span class="fw-300"><i>sizes</i></span>
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
                            Tabs are flexible and can be resized using the utility classes
                        </div>
                        <div class="demo-v-spacing">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active fs-xs py-1 px-2" data-bs-toggle="tab" href="#" role="tab">
                                        <i class="sa sa-home text-success"></i>
                                        <span class="hidden-sm-down ms-1">Home</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fs-xs py-1 px-2" data-bs-toggle="tab" href="#" role="tab">
                                        <i class="sa sa-user text-primary"></i>
                                        <span class="hidden-sm-down ms-1">Profile</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fs-xs py-1 px-2" data-bs-toggle="tab" href="#" role="tab">
                                        <i class="sa sa-cog text-danger"></i>
                                        <span class="hidden-sm-down ms-1">Settings</span>
                                    </a>
                                </li>
                            </ul>
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#" role="tab">
                                        <i class="sa sa-home text-success"></i>
                                        <span class="hidden-sm-down ms-1">Home</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#" role="tab">
                                        <i class="sa sa-user text-primary"></i>
                                        <span class="hidden-sm-down ms-1">Profile</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#" role="tab">
                                        <i class="sa sa-cog text-danger"></i>
                                        <span class="hidden-sm-down ms-1">Settings</span>
                                    </a>
                                </li>
                            </ul>
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active fs-lg px-4" data-bs-toggle="tab" href="#" role="tab">
                                        <i class="sa sa-home text-success"></i>
                                        <span class="hidden-sm-down ms-1">Home</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fs-lg px-4" data-bs-toggle="tab" href="#" role="tab">
                                        <i class="sa sa-user text-primary"></i>
                                        <span class="hidden-sm-down ms-1">Profile</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fs-lg px-4" data-bs-toggle="tab" href="#" role="tab">
                                        <i class="sa sa-cog text-danger"></i>
                                        <span class="hidden-sm-down ms-1">Settings</span>
                                    </a>
                                </li>
                            </ul>
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active fs-xl py-3 px-5" data-bs-toggle="tab" href="#" role="tab">
                                        <i class="sa sa-home text-success"></i>
                                        <span class="hidden-sm-down ms-1">Home</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fs-xl py-3 px-5" data-bs-toggle="tab" href="#" role="tab">
                                        <i class="sa sa-user text-primary"></i>
                                        <span class="hidden-sm-down ms-1">Profile</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link fs-xl py-3 px-5" data-bs-toggle="tab" href="#" role="tab">
                                        <i class="sa sa-cog text-danger"></i>
                                        <span class="hidden-sm-down ms-1">Settings</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-3">
                <div class="panel-hdr">
                    <h2>
                        Directional <span class="fw-300"><i>tabs</i></span>
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
                            Direction of the tabs can be changed by adding <code>.justify-content-end</code> and <code>justify-content-center</code>
                            to <code>.nav.nav-tabs</code>
                            <br/>
                            <br/>
                            <div class="btn-group btn-group-sm btn-group-toggle" data-bs-toggle="buttons"
                                 id="js_change_tab_direction">
                                <input checked="" class="btn-check" id="option1" name="radioNameTabDirection"
                                       type="radio" value="nav nav-tabs"/>
                                <label class="btn btn-info" for="option1">Left aligned</label>
                                <input class="btn-check" id="option2" name="radioNameTabDirection" type="radio"
                                       value="nav nav-tabs justify-content-center"/>
                                <label class="btn btn-info" for="option2">Center aligned</label>
                                <input class="btn-check" id="option3" name="radioNameTabDirection" type="radio"
                                       value="nav nav-tabs justify-content-end"/>
                                <label class="btn btn-info" for="option3">Right aligned</label>
                            </div>
                        </div>
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab_direction-1"
                                                    role="tab">Home</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_direction-2"
                                                    role="tab">Profile</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_direction-3"
                                                    role="tab">Time</a></li>
                        </ul>
                        <div class="tab-content p-3">
                            <div class="tab-pane fade show active" id="tab_direction-1" role="tabpanel">
                                Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown
                                aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan
                                helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher synth. Cosby sweater eu
                                banh mi, qui irure terry richardson ex squid. Aliquip placeat salvia cillum iphone.
                            </div>
                            <div class="tab-pane fade" id="tab_direction-2" role="tabpanel">
                                Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid.
                                Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson
                                artisan four loko farm-to-table craft beer twee. Qui photo booth letterpress, commodo
                                enim craft beer mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud
                                organic.
                            </div>
                            <div class="tab-pane fade" id="tab_direction-3" role="tabpanel">
                                Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's
                                organic lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify
                                pitchfork tattooed craft beer, iphone skateboard locavore carles etsy salvia banksy
                                hoodie helvetica. DIY synth PBR banksy irony. Leggings gentrify squid 8-bit cred
                                pitchfork.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-4">
                <div class="panel-hdr">
                    <h2>
                        Justified <span class="fw-300"><i>tabs</i></span>
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
                            For equal-width elements, use <code>.nav-justified</code>. All horizontal space will be
                            occupied by nav links
                        </div>
                        <ul class="nav nav-tabs nav-fill" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab_justified-1"
                                                    role="tab">Home</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_justified-2"
                                                    role="tab">Profile</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_justified-3"
                                                    role="tab">Time</a></li>
                            <li class="nav-item">
                                <a class="nav-link disabled" href="#" tabindex="-1">Disabled</a>
                            </li>
                        </ul>
                        <div class="tab-content p-3">
                            <div class="tab-pane fade show active" id="tab_justified-1" role="tabpanel">
                                Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown
                                aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan
                                helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher synth. Cosby sweater eu
                                banh mi, qui irure terry richardson ex squid. Aliquip placeat salvia cillum iphone.
                            </div>
                            <div class="tab-pane fade" id="tab_justified-2" role="tabpanel">
                                Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid.
                                Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson
                                artisan four loko farm-to-table craft beer twee. Qui photo booth letterpress, commodo
                                enim craft beer mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud
                                organic.
                            </div>
                            <div class="tab-pane fade" id="tab_justified-3" role="tabpanel">
                                Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's
                                organic lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify
                                pitchfork tattooed craft beer, iphone skateboard locavore carles etsy salvia banksy
                                hoodie helvetica. DIY synth PBR banksy irony. Leggings gentrify squid 8-bit cred
                                pitchfork.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-5">
                <div class="panel-hdr">
                    <h2>
                        Borders <span class="fw-300"><i>&amp; icons</i></span>
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
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#tab_borders_icons-1"
                                   role="tab"><i class="sa sa-home me-1"></i> Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab_borders_icons-2" role="tab"><i
                                        class="sa sa-user me-1"></i> Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab_borders_icons-3" role="tab"><i
                                        class="sa sa-clock me-1"></i> Time</a>
                            </li>
                        </ul>
                        <div class="tab-content border border-top-0 p-3">
                            <div class="tab-pane fade show active" id="tab_borders_icons-1" role="tabpanel">
                                Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown
                                aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan
                                helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher synth. Cosby sweater eu
                                banh mi, qui irure terry richardson ex squid. Aliquip placeat salvia cillum iphone.
                            </div>
                            <div class="tab-pane fade" id="tab_borders_icons-2" role="tabpanel">
                                Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid.
                                Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson
                                artisan four loko farm-to-table craft beer twee. Qui photo booth letterpress, commodo
                                enim craft beer mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud
                                organic.
                            </div>
                            <div class="tab-pane fade" id="tab_borders_icons-3" role="tabpanel">
                                Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's
                                organic lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify
                                pitchfork tattooed craft beer, iphone skateboard locavore carles etsy salvia banksy
                                hoodie helvetica. DIY synth PBR banksy irony. Leggings gentrify squid 8-bit cred
                                pitchfork.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-6">
                <div class="panel-hdr">
                    <h2>
                        Default <span class="fw-300"><i>Panel</i></span>
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
                            Add a clean look to your tabs by adding <code>.nav-tabs-clean</code> to
                            <code>.nav-tabs</code>
                        </div>
                        <ul class="nav nav-tabs nav-tabs-clean" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-home"
                                                    role="tab">Home</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-profile"
                                                    role="tab">Profile</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-time" role="tab">Time</a>
                            </li>
                        </ul>
                        <div class="tab-content p-3">
                            <div aria-labelledby="tab-home" class="tab-pane fade show active" id="tab-home"
                                 role="tabpanel">Raw denim you probably haven't heard of them jean shorts Austin.
                                Nesciunt tofu stumptown aliqua, retro synth master cleanse. Mustache cliche tempor,
                                williamsburg carles vegan helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher
                                synth. Cosby sweater eu banh mi, qui irure terry richardson ex squid. Aliquip placeat
                                salvia cillum iphone.
                            </div>
                            <div aria-labelledby="tab-profile" class="tab-pane fade" id="tab-profile" role="tabpanel">
                                Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid.
                                Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson
                                artisan four loko farm-to-table craft beer twee. Qui photo booth letterpress, commodo
                                enim craft beer mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud
                                organic.
                            </div>
                            <div aria-labelledby="tab-time" class="tab-pane fade" id="tab-time" role="tabpanel">Etsy
                                mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's organic
                                lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify pitchfork
                                tattooed craft beer, iphone skateboard locavore carles etsy salvia banksy hoodie
                                helvetica. DIY synth PBR banksy irony. Leggings gentrify squid 8-bit cred pitchfork.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="panel panel-icon" id="panel-7">
                <div class="panel-hdr">
                    <h2>
                        Default <span class="fw-300"><i>Pills</i></span>
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
                            The nav pills is the same HTML structure as tabs, but we replace <code>.nav-tabs</code> with
                            <code>.nav-pills</code>
                        </div>
                        <ul class="nav nav-pills" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill"
                                                    href="#nav_pills_default-1">Home</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#nav_pills_default-2">Profile</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#nav_pills_default-3">Set</a>
                            </li>
                            <li class="nav-item"><a class="nav-link disabled" data-bs-toggle="pill"
                                                    href="#">Disabled</a></li>
                        </ul>
                        <div class="tab-content py-3">
                            <div class="tab-pane fade show active" id="nav_pills_default-1" role="tabpanel">
                                Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown
                                aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan
                                helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher synth. Cosby sweater eu
                                banh mi, qui irure terry richardson ex squid. Aliquip placeat salvia cillum iphone.
                            </div>
                            <div class="tab-pane fade" id="nav_pills_default-2" role="tabpanel">
                                Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid.
                                Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson
                                artisan four loko farm-to-table craft beer twee. Qui photo booth letterpress, commodo
                                enim craft beer mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud
                                organic.
                            </div>
                            <div class="tab-pane fade" id="nav_pills_default-3" role="tabpanel">
                                Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's
                                organic lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify
                                pitchfork tattooed craft beer, iphone skateboard locavore carles etsy salvia banksy
                                hoodie helvetica. DIY synth PBR banksy irony. Leggings gentrify squid 8-bit cred
                                pitchfork.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-8">
                <div class="panel-hdr">
                    <h2>
                        Pills <span class="fw-300"><i>as nav</i></span>
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
                            We can display nav pills inside the <code>nav</code> HTML element, which then allows you to
                            inherit properties of buttons, various sizes and different colors
                        </div>
                        <div class="demo-v-spacing">
                            <nav class="nav nav-pills">
                                <a class="nav-item nav-link active btn-xs" data-bs-toggle="pill" href="#"><i
                                        class="sa sa-home me-1"></i> Home</a>
                                <a class="nav-item nav-link btn-xs" data-bs-toggle="pill" href="#"><i
                                        class="sa sa-user me-1"></i> Profile</a>
                                <a class="nav-item nav-link btn-xs" data-bs-toggle="pill" href="#"><i
                                        class="sa sa-cog me-1"></i> Settings</a>
                            </nav>
                            <nav class="nav nav-pills">
                                <a class="nav-item nav-link active btn-lg" data-bs-toggle="pill" href="#"><i
                                        class="sa sa-home me-1"></i> Home</a>
                                <a class="nav-item nav-link btn-lg" data-bs-toggle="pill" href="#"><i
                                        class="sa sa-user me-1"></i> Profile</a>
                                <a class="nav-item nav-link btn-lg" data-bs-toggle="pill" href="#"><i
                                        class="sa sa-cog me-1"></i> Settings</a>
                            </nav>
                            <nav class="nav nav-pills">
                                <a class="nav-item nav-link active btn btn-icon btn-lg rounded-circle me-1"
                                   data-bs-toggle="pill" href="javascript:void(0);">
                                    <i class="sa sa-home"></i>
                                </a>
                                <a class="nav-item nav-link btn btn-icon btn-lg rounded-circle me-1"
                                   data-bs-toggle="pill" href="javascript:void(0);">
                                    <i class="sa sa-user"></i>
                                </a>
                                <a class="nav-item nav-link btn btn-icon btn-lg rounded-circle" data-bs-toggle="pill"
                                   href="#"><i class="sa sa-cog"></i></a>
                            </nav>
                            <nav class="nav nav-pills">
                                <a class="nav-item nav-link active btn btn-icon rounded-circle me-1"
                                   data-bs-toggle="pill" href="javascript:void(0);">
                                    <i class="sa sa-home"></i>
                                </a>
                                <a class="nav-item nav-link btn btn-icon rounded-circle me-1" data-bs-toggle="pill"
                                   href="javascript:void(0);">
                                    <i class="sa sa-user"></i>
                                </a>
                                <a class="nav-item nav-link btn btn-icon rounded-circle" data-bs-toggle="pill" href="#"><i
                                        class="sa sa-cog"></i></a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-9">
                <div class="panel-hdr">
                    <h2>
                        Directional <span class="fw-300"><i>pills</i></span>
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
                            Direction of the pills can be changed by adding <code>.justify-content-end</code> and <code>justify-content-center</code>
                            to <code>.nav.nav-tabs</code>
                            <br/>
                            <br/>
                            <div class="btn-group btn-group-sm" data-bs-toggle="buttons" id="js_change_pill_direction">
                                <input checked="" class="btn-check" id="pillOption1" name="radioNamePillDirection"
                                       type="radio" value="nav nav-pills"/>
                                <label class="btn btn-success" for="pillOption1">Left aligned</label>
                                <input class="btn-check" id="pillOption2" name="radioNamePillDirection" type="radio"
                                       value="nav nav-pills justify-content-center"/>
                                <label class="btn btn-success" for="pillOption2">Center aligned</label>
                                <input class="btn-check" id="pillOption3" name="radioNamePillDirection" type="radio"
                                       value="nav nav-pills justify-content-end"/>
                                <label class="btn btn-success" for="pillOption3">Right aligned</label>
                            </div>
                        </div>
                        <ul class="nav nav-pills" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab"
                                                    href="#js_change_pill_direction-1">Home</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                    href="#js_change_pill_direction-2">Profile</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                    href="#js_change_pill_direction-3">Time</a></li>
                        </ul>
                        <div class="tab-content py-3">
                            <div class="tab-pane fade show active" id="js_change_pill_direction-1" role="tabpanel">
                                Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown
                                aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan
                                helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher synth. Cosby sweater eu
                                banh mi, qui irure terry richardson ex squid. Aliquip placeat salvia cillum iphone.
                            </div>
                            <div class="tab-pane fade" id="js_change_pill_direction-2" role="tabpanel">
                                Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid.
                                Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson
                                artisan four loko farm-to-table craft beer twee. Qui photo booth letterpress, commodo
                                enim craft beer mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud
                                organic.
                            </div>
                            <div class="tab-pane fade" id="js_change_pill_direction-3" role="tabpanel">
                                Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's
                                organic lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify
                                pitchfork tattooed craft beer, iphone skateboard locavore carles etsy salvia banksy
                                hoodie helvetica. DIY synth PBR banksy irony. Leggings gentrify squid 8-bit cred
                                pitchfork.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-10">
                <div class="panel-hdr">
                    <h2>
                        Justified <span class="fw-300"><i>pills</i></span>
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
                            For equal-width elements, use <code>.nav-justified</code>. All horizontal space will be
                            occupied by nav links
                        </div>
                        <ul class="nav nav-pills nav-justified" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab"
                                                    href="#js_change_pill_justified-1">Home</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                    href="#js_change_pill_justified-2">Profile</a></li>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                    href="#js_change_pill_justified-3">Time</a></li>
                            <li class="nav-item"><a class="nav-link disabled" data-bs-toggle="tab" href="#">Disabled</a>
                            </li>
                        </ul>
                        <div class="tab-content py-3">
                            <div class="tab-pane fade show active" id="js_change_pill_justified-1" role="tabpanel">
                                Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu stumptown
                                aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg carles vegan
                                helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher synth. Cosby sweater eu
                                banh mi, qui irure terry richardson ex squid. Aliquip placeat salvia cillum iphone.
                            </div>
                            <div class="tab-pane fade" id="js_change_pill_justified-2" role="tabpanel">
                                Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid.
                                Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson
                                artisan four loko farm-to-table craft beer twee. Qui photo booth letterpress, commodo
                                enim craft beer mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud
                                organic.
                            </div>
                            <div class="tab-pane fade" id="js_change_pill_justified-3" role="tabpanel">
                                Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's
                                organic lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify
                                pitchfork tattooed craft beer, iphone skateboard locavore carles etsy salvia banksy
                                hoodie helvetica. DIY synth PBR banksy irony. Leggings gentrify squid 8-bit cred
                                pitchfork.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-11">
                <div class="panel-hdr">
                    <h2>
                        Pill <span class="fw-300"><i>borders &amp; icons</i></span>
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
                        <div class="border px-3 pt-3 pb-0 rounded">
                            <ul class="nav nav-pills" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab"
                                                        href="#js_pill_border_icon-1"><i class="sa sa-home me-1"></i>Home</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                        href="#js_pill_border_icon-2"><i class="sa sa-user me-1"></i>Profile</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                                        href="#js_pill_border_icon-3"><i class="sa sa-clock me-1"></i>Time</a>
                                </li>
                            </ul>
                            <div class="tab-content py-3">
                                <div class="tab-pane fade show active" id="js_pill_border_icon-1" role="tabpanel">
                                    Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt tofu
                                    stumptown aliqua, retro synth master cleanse. Mustache cliche tempor, williamsburg
                                    carles vegan helvetica. Reprehenderit butcher retro keffiyeh dreamcatcher synth.
                                    Cosby sweater eu banh mi, qui irure terry richardson ex squid. Aliquip placeat
                                    salvia cillum iphone.
                                </div>
                                <div class="tab-pane fade" id="js_pill_border_icon-2" role="tabpanel">
                                    Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee
                                    squid. Exercitation +1 labore velit, blog sartorial PBR leggings next level wes
                                    anderson artisan four loko farm-to-table craft beer twee. Qui photo booth
                                    letterpress, commodo enim craft beer mlkshk aliquip jean shorts ullamco ad vinyl
                                    cillum PBR. Homo nostrud organic.
                                </div>
                                <div class="tab-pane fade" id="js_pill_border_icon-3" role="tabpanel">
                                    Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's
                                    organic lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify
                                    pitchfork tattooed craft beer, iphone skateboard locavore carles etsy salvia banksy
                                    hoodie helvetica. DIY synth PBR banksy irony. Leggings gentrify squid 8-bit cred
                                    pitchfork.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-icon" id="panel-12">
                <div class="panel-hdr">
                    <h2>
                        Pill <span class="fw-300"><i>vertical</i></span>
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
                            <div class="col-auto">
                                <div aria-orientation="vertical" class="nav flex-column nav-pills" id="v-pills-tab"
                                     role="tablist">
                                    <a aria-controls="v-pills-home" aria-selected="true"
                                       class="nav-link active d-flex align-items-center" data-bs-toggle="pill"
                                       href="#v-pills-home" id="v-pills-home-tab" role="tab">
                                        <i class="sa sa-home me-2"></i>
                                        <span class="hidden-sm-down ms-1"> Home</span>
                                    </a>
                                    <a aria-controls="v-pills-profile" aria-selected="false"
                                       class="nav-link d-flex align-items-center" data-bs-toggle="pill"
                                       href="#v-pills-profile" id="v-pills-profile-tab" role="tab">
                                        <i class="sa sa-user me-2"></i>
                                        <span class="hidden-sm-down ms-1"> Profile</span>
                                    </a>
                                    <a aria-controls="v-pills-messages" aria-selected="false"
                                       class="nav-link d-flex align-items-center" data-bs-toggle="pill"
                                       href="#v-pills-messages" id="v-pills-messages-tab" role="tab">
                                        <i class="sa sa-envelope me-2"></i>
                                        <span class="hidden-sm-down ms-1"> Messages</span>
                                    </a>
                                    <a aria-controls="v-pills-settings" aria-selected="false"
                                       class="nav-link d-flex align-items-center" data-bs-toggle="pill"
                                       href="#v-pills-settings" id="v-pills-settings-tab" role="tab">
                                        <i class="sa sa-settings me-2"></i>
                                        <span class="hidden-sm-down ms-1"> Settings</span>
                                    </a>
                                </div>
                            </div>
                            <div class="col">
                                <div class="tab-content" id="v-pills-tabContent">
                                    <div aria-labelledby="v-pills-home-tab" class="tab-pane fade show active"
                                         id="v-pills-home" role="tabpanel">
                                        <h3>
                                            Home
                                        </h3>
                                        <p> Raw denim you probably haven't heard of them jean shorts Austin. Nesciunt
                                            tofu stumptown aliqua, retro synth master cleanse. Mustache cliche tempor,
                                            williamsburg carles vegan helvetica. </p>
                                        <p> Organic lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag
                                            gentrify pitchfork tattooed craft beer, iphone skateboard locavore.</p>
                                        <span class="fs-sm d-flex align-items-center mt-3 flex-wrap">
<a class="me-1 mt-1" href="#" title="Cell A-0012">
<img alt="pic-7" class="img-share" src="/img/thumbs/pic-7.png"/>
</a>
<a class="me-1 mt-1" href="#" title="Patient A-473 saliva">
<img alt="pic-8" class="img-share" src="/img/thumbs/pic-8.png"/>
</a>
<a class="me-1 mt-1" href="#" title="Patient A-473 blood cells">
<img alt="pic-11" class="img-share" src="/img/thumbs/pic-11.png"/>
</a>
<a class="me-1 mt-1" href="#" title="Patient A-473 Membrane O.C">
<img alt="pic-12" class="img-share" src="/img/thumbs/pic-12.png"/>
</a>
</span>
                                    </div>
                                    <div aria-labelledby="v-pills-profile-tab" class="tab-pane fade"
                                         id="v-pills-profile" role="tabpanel">
                                        <h3>
                                            Profile
                                        </h3>
                                        <div class="d-flex flex-row rounded-top mb-3">
                                            <div class="d-flex flex-row align-items-center mt-1 mb-1">
<span class="me-2">
<img alt="Sunny A." class="rounded-circle profile-image" src="/img/demo/avatars/avatar-admin.png"/>
</span>
                                                <div class="info-card-text">
                                                    <div class="fs-lg text-truncate text-truncate-lg">Sunny A.</div>
                                                    <span class="text-truncate text-truncate-md opacity-80">sunny@example.com</span>
                                                </div>
                                            </div>
                                        </div>
                                        <p>
                                            Food truck fixie locavore, accusamus mcsweeney's marfa nulla single-origin
                                            coffee squid. Exercitation +1 labore velit, blog sartorial PBR leggings next
                                            level wes anderson artisan four loko farm-to-table craft beer twee.
                                        </p>
                                        <p>
                                            Qui photo booth letterpress, commodo enim craft beer mlkshk aliquip jean
                                            shorts ullamco ad vinyl cillum PBR. Homo nostrud organic.
                                        </p>
                                    </div>
                                    <div aria-labelledby="v-pills-messages-tab" class="tab-pane fade"
                                         id="v-pills-messages" role="tabpanel">
                                        <h3>
                                            Messages
                                        </h3>
                                        <ul class="notification">
                                            <li>
                                                <a class="d-flex align-items-center py-2 px-0" href="#">
<span class="d-flex flex-column flex-1">
<span class="name">Melissa Ayre</span>
<span class="msg-a fs-sm">Re: New security codes</span>
</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="d-flex align-items-center py-2 px-0" href="#">
<span class="d-flex flex-column flex-1">
<span class="name">Adison Lee</span>
<span class="msg-a fs-sm">Msed quia non numquam eius</span>
</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="d-flex align-items-center py-2 px-0" href="#">
<span class="d-flex flex-column flex-1">
<span class="name">Oliver Kopyuv</span>
<span class="msg-a fs-sm">Msed quia non numquam eius</span>
</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div aria-labelledby="v-pills-settings-tab" class="tab-pane fade"
                                         id="v-pills-settings" role="tabpanel">
                                        <h3>Settings</h3>
                                        <div class="alert alert-success">
                                            <strong>
                                                Settings saved
                                            </strong>
                                            <p class="m-0">
                                                All your settings changes have been saved!
                                            </p>
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

@endsection
