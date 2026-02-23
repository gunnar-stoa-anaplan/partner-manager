@extends('layouts.vertical', ['pageTitle' => 'Idea Room — Gallery', 'extraClass' => 'layout-trimmed'])

@section('content')
<div x-data="worldView">

    {{-- Loading --}}
    <template x-if="loading">
        <div class="d-flex justify-content-center align-items-center py-5">
            <div class="d-flex gap-1">
                <span class="ir-loading-dot"></span>
                <span class="ir-loading-dot"></span>
                <span class="ir-loading-dot"></span>
            </div>
        </div>
    </template>

    {{-- Gallery Wall --}}
    <template x-if="!loading">
        <div class="gallery-viewport d-flex justify-content-center" style="height: calc(100vh - var(--app-header-height));">
            <div class="gallery-bg shadow-lg" :style="'--ar-w:' + arW + ';--ar-h:' + arH + (backgroundImageUrl ? '; background-image: url(' + backgroundImageUrl + ')' : '')">

                {{-- Static mode --}}
                <template x-if="galleryMode === 'static' && galleryImageUrl">
                    <img :src="galleryImageUrl" alt="Gallery Wall" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover pe-none" />
                </template>

                {{-- Video mode --}}
                <template x-if="galleryMode === 'video'">
                    <div class="position-absolute top-0 start-0 w-100 h-100">
                        <template x-for="(slot, i) in frameSlots" :key="'vid-'+i">
                            <template x-if="videoForSlot(i)">
                                <video autoplay loop muted playsinline
                                       class="gallery-video position-absolute object-fit-cover"
                                       :class="greyscale ? 'gallery-video--bw' : ''"
                                       :style="slotStyle(slot)"
                                       :src="videoForSlot(i).url">
                                </video>
                            </template>
                        </template>
                        <img :src="templateImageUrl" alt="" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover pe-none" style="z-index:5" />
                    </div>
                </template>

                {{-- Frame hotspots --}}
                <template x-for="(slot, i) in frameSlots" :key="i">
                    <div>
                        {{-- Whiteboard slot (always last frame) --}}
                        <template x-if="isWhiteboardSlot(i)">
                            <a :href="whiteboardUrl"
                               @click="enterRoom($event, null)"
                               class="frame-overlay frame-whiteboard"
                               :style="slotStyle(slot)">
                                <span class="frame-placard">Whiteboard</span>
                            </a>
                        </template>

                        {{-- Active room slot --}}
                        <template x-if="!isWhiteboardSlot(i) && roomForSlot(i)">
                            <a :href="'/idea-room/rooms/' + roomForSlot(i).uuid"
                               @click="enterRoom($event, roomForSlot(i).uuid)"
                               class="frame-overlay frame-overlay--active"
                               :style="slotStyle(slot)">
                                <div class="frame-label">
                                    <span class="frame-label-dot" :class="'frame-label-dot--' + (roomForSlot(i).idea?.lifecycle_state || 'spark')"></span>
                                    <span x-text="roomForSlot(i).name"></span>
                                </div>
                                <span class="frame-placard" x-text="framePlacard(i)"></span>
                            </a>
                        </template>

                        {{-- Vacant slot --}}
                        <template x-if="!isWhiteboardSlot(i) && !roomForSlot(i)">
                            <div class="frame-overlay frame-overlay--vacant"
                                 :style="slotStyle(slot)">
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Floating toolbar (anchored to gallery image) --}}
                <div class="position-absolute d-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-dark bg-opacity-50 text-white" style="bottom: 1.5rem; left: 50%; transform: translateX(-50%); z-index: 100; backdrop-filter: blur(8px); white-space: nowrap;">
                    <button type="button" class="btn btn-sm rounded-pill" :class="roomFilter === 'all' ? 'btn-light' : 'btn-outline-light border-0'" @click="roomFilter = 'all'">All</button>
                    <button type="button" class="btn btn-sm rounded-pill" :class="roomFilter === 'personal' ? 'btn-light' : 'btn-outline-light border-0'" @click="roomFilter = 'personal'">Personal</button>
                    <button type="button" class="btn btn-sm rounded-pill" :class="roomFilter === 'business' ? 'btn-light' : 'btn-outline-light border-0'" @click="roomFilter = 'business'">Business</button>

                    <template x-if="stats">
                        <div class="d-flex align-items-center gap-2 small ms-2 opacity-75">
                            <span x-show="stats.total_ideas > 0"><span x-text="stats.total_ideas"></span> ideas</span>
                            <span x-show="stats.active_rooms > 0"><span class="text-warning" x-text="stats.active_rooms"></span> rooms</span>
                        </div>
                    </template>
                    <div class="dropup" x-show="templates.length > 1" @click.outside="showTemplatePicker = false">
                        <button class="btn btn-sm btn-outline-light border-0 rounded-pill" type="button" @click="showTemplatePicker = !showTemplatePicker">
                            Template
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-2" :class="showTemplatePicker ? 'show' : ''" style="min-width: 16rem">
                            <template x-for="tpl in templates" :key="tpl.slug">
                                <button @click="selectTemplate(tpl.slug)" class="dropdown-item rounded d-flex align-items-center gap-2 px-3 py-2"
                                        :class="tpl.slug === currentTemplate ? 'active' : ''">
                                    <img :src="tpl.thumbnail_url || ''" alt="" class="rounded" style="width: 48px; height: 27px; object-fit: cover;" x-show="tpl.thumbnail_url">
                                    <div class="flex-grow-1">
                                        <div class="fw-medium" x-text="tpl.name"></div>
                                        <div class="small text-muted">
                                            <span x-text="tpl.max_frames + ' frames'"></span> &middot;
                                            <span x-text="tpl.greyscale ? 'B&W' : 'Color'"></span>
                                        </div>
                                    </div>
                                    <span x-show="tpl.slug === currentTemplate" class="text-warning">&#10003;</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </template>

</div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/idea-room/alpine-app.js'])
@endsection
