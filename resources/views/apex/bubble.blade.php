@extends('layouts.vertical', ['pageTitle' => 'Apex Bubble Chart'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Apex Bubble Chart', 'pageSubTitle1' => 'Data Visualization', 'pageSubTitle2'=> 'Apex Charts', 'pageSubText' => 'Utilize this collection of Apex Bubble Chart examples to blow up your development game!'])
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-6">
            <div class="panel panel-icon" id="panel-1">
                <div class="panel-hdr">
                    <h2> Simple <span class="fw-light"><i>Bubble</i></span>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="simple-bubble-chart"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="panel panel-icon" id="panel-2">
                <div class="panel-hdr">
                    <h2> 3D Bubble <span class="fw-light"><i>Chart</i></span>
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
                <div class="panel-container">
                    <div class="panel-content">
                        <div id="3d-bubble-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/apexbubblechart.js'])
@endsection
