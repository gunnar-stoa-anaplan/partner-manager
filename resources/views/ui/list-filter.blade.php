@extends('layouts.vertical', ['pageTitle' => 'List Filter'])

@section('css')
    @vite('node_modules/@highlightjs/cdn-assets/styles/night-owl.css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle' => 'Smart Filter', 'pageSubTitle1' => 'Components', 'pageSubTitle2' => 'UI Components', 'pageSubText' => 'Advanced navigation filtering with fuzzy search capabilities.'])
@endsection

@section('content')
    <div class="row">
        <!-- Did you mean suggestions example -->
        <div class="col-xl-6">
            <div class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>Did you mean suggestions</h2>
                    <div class="panel-toolbar">
                        <button class="btn btn-panel" data-action="panel-collapse" data-offset="0,10"
                                data-original-title="Collapse" data-toggle="tooltip" type="button">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button class="btn btn-panel" data-action="panel-fullscreen" data-offset="0,10"
                                data-original-title="Fullscreen" data-toggle="tooltip" type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Try typing <strong>"javas"</strong> to see suggestions.
                            You can also finetune this for better suggestions via an separate json file.
                            Checkout this in the works in our <a href="{{ route('second', ['icons', 'system']) }}">Icon Filter</a> page.
                        </div>
                        <div class="input-group mb-3">
                        <span class="input-group-text">
                            <i class="sa sa-magnifier"></i>
                        </span>
                            <input class="form-control" id="suggestionsFilterInput"
                                   placeholder="Search with suggestions..."
                                   type="text"/>
                            <span class="input-group-text text-sm" id="suggestionsFilterMessage"></span>
                        </div>
                        <nav class="nav-menu">
                            <ul class="nav-menu-list list-unstyled list-spaced" id="suggestionsNav">
                                <li class="nav-title">Programming Languages</li>
                                <li><a href="#"><span class="nav-link-text">JavaScript</span></a></li>
                                <li><a href="#"><span class="nav-link-text">TypeScript</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Python</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Java</span></a></li>
                                <li class="nav-title">Framework Components</li>
                                <li><a href="#"><span class="nav-link-text">Components</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Compnent</span></a></li>
                                <li class="nav-title">Authentication Terms</li>
                                <li><a href="#"><span class="nav-link-text">Auth</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Authenticate</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Authntication</span></a></li>
                                <li class="nav-title">Development Tools</li>
                                <li><a href="#"><span class="nav-link-text">Develop</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Developer</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Devlopment</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Developement</span></a></li>
                                <li class="nav-title">Configuration Items</li>
                                <li><a href="#"><span class="nav-link-text">Config</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Configure</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Configuraton</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Configration</span></a></li>
                                <li class="nav-title">Database Terms</li>
                                <li><a href="#"><span class="nav-link-text">Database</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Data</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Databse</span></a></li>
                                <li><a href="#"><span class="nav-link-text">Datatbase</span></a></li>
                                <li><a href="#"><span class="nav-link-text">DataBase</span></a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- File Explorer Example -->
        <div class="col-xl-6">
            <div class="panel panel-icon">
                <div class="panel-hdr">
                    <h2>File Explorer with Nested Structure</h2>
                    <div class="panel-toolbar">
                        <button class="btn btn-panel" data-action="panel-collapse" data-offset="0,10"
                                data-original-title="Collapse" data-toggle="tooltip" type="button">
                            <svg class="sa-icon">
                                <use class="panel-collapsed-icon" href="/img/sprite.svg#minus-circle"></use>
                                <use class="panel-expand-icon" href="/img/sprite.svg#plus-circle"></use>
                            </svg>
                        </button>
                        <button class="btn btn-panel" data-action="panel-fullscreen" data-offset="0,10"
                                data-original-title="Fullscreen" data-toggle="tooltip" type="button">
                            <svg class="sa-icon">
                                <use href="/icons/sprite.svg#stop-circle"></use>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="panel-tag">
                            Search through nested file structure with automatic parent folder expansion. Try typing
                            partial
                            words or misspelled terms (e.g., "btn" for "Button.js")
                        </div>
                        <div class="input-group input-group-lg mb-3">
                        <span class="input-group-text">
                            <i class="sa sa-folder"></i>
                        </span>
                            <input class="form-control" id="fileFilterInput" placeholder="Search files..." type="text"/>
                            <span class="input-group-text" id="fileFilterMessage"></span>
                        </div>
                        <nav class="nav-menu">
                            <ul class="nav-menu-list list-unstyled list-spaced" id="fileExplorer">
                                <li class="nav-title">Project Root</li>
                                <li>
                                    <a href="#"><span class="nav-link-text">src/</span></a>
                                    <ul>
                                        <li>
                                            <a href="#"><span class="nav-link-text">components/</span></a>
                                            <ul>
                                                <li><a href="#"><span class="nav-link-text">Button.js</span></a></li>
                                                <li><a href="#"><span class="nav-link-text">Card.js</span></a></li>
                                                <li><a href="#"><span class="nav-link-text">Modal.js</span></a></li>
                                                <li><a href="#"><span class="nav-link-text">Form.js</span></a></li>
                                                <li><a href="#"><span class="nav-link-text">Table.js</span></a></li>
                                                <li><a href="#"><span class="nav-link-text">Chart.js</span></a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#"><span class="nav-link-text">utils/</span></a>
                                            <ul>
                                                <li><a href="#"><span class="nav-link-text">helpers.js</span></a></li>
                                                <li><a href="#"><span class="nav-link-text">constants.js</span></a></li>
                                                <li><a href="#"><span class="nav-link-text">validation.js</span></a>
                                                </li>
                                                <li><a href="#"><span class="nav-link-text">formatting.js</span></a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="#"><span class="nav-link-text">services/</span></a>
                                            <ul>
                                                <li><a href="#"><span class="nav-link-text">api.js</span></a></li>
                                                <li><a href="#"><span class="nav-link-text">auth.js</span></a></li>
                                                <li><a href="#"><span class="nav-link-text">storage.js</span></a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="#"><span class="nav-link-text">public/</span></a>
                                    <ul>
                                        <li><a href="#"><span class="nav-link-text">{{ route('any', 'index') }}</span></a></li>
                                        <li><a href="#"><span class="nav-link-text">styles.css</span></a></li>
                                        <li><a href="#"><span class="nav-link-text">assets/</span></a>
                                            <ul>
                                                <li><a href="#"><span class="nav-link-text">images/</span></a></li>
                                                <li><a href="#"><span class="nav-link-text">fonts/</span></a></li>
                                                <li><a href="#"><span class="nav-link-text">icons/</span></a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="#"><span class="nav-link-text">tests/</span></a>
                                    <ul>
                                        <li><a href="#"><span class="nav-link-text">unit/</span></a></li>
                                        <li><a href="#"><span class="nav-link-text">integration/</span></a></li>
                                        <li><a href="#"><span class="nav-link-text">e2e/</span></a></li>
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    @vite(['resources/scripts/pages/listfilter.js'])

    @vite(['resources/scripts/pages/code-highlight.js'])
@endsection
