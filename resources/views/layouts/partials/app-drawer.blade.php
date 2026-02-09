<aside class="app-drawer js-app-drawer">
    <button type="button" class="btn btn-system position-absolute top-0 end-0 z-2 overflow-hidden p-3"
            data-action="toggle-swap" data-toggleclass="open" data-target="aside.js-app-drawer" aria-label="Close">
        <svg class="sa-icon sa-icon-2x">
            <use href="/icons/sprite.svg#x"></use>
        </svg>
    </button>

    <div class="custom-scrollbar h-100 p-0">
        <div class="d-flex flex-grow-1 p-0 w-100 h-100">
            <!-- left sidebar -->
            <div class="d-flex flex-column flex-grow-1 flex-0 overflow-x-auto h-100">

                <div class="flex-wrap align-items-center flex-grow-1 position-relative bg-gray-50">
                    <div class="position-absolute top-0 bottom-0 w-100 overflow-hidden d-flex flex-column">

                        <!-- chat container -->
                        <div
                            class="w-100 p-4 pb-0 px-lg-3 bg-subtlelight-fade custom-scroll flex-grow-1 d-flex flex-column">

                            <div class="mb-g">
                                <h4 class="fw-600">Hi Sunny,</h4>
                                <span class="text-muted">how can I help you today?</span>
                            </div>
                            <div
                                class="mt-auto ms-auto mb-2 btn btn-outline-default px-2 py-1 fw-500 fs-xs text-gradient">
                                Analyze my data
                            </div>
                            <p class="ms-auto mb-2 btn btn-outline-default px-2 py-1 fs-xs fw-500 text-gradient">
                                Create a new report
                            </p>
                            <p class="ms-auto mb-2 btn btn-outline-default px-2 py-1 fs-xs fw-500 text-gradient">
                                Summarize my Calendar
                            </p>

                        </div>

                        <!-- chat input -->
                        <div class="bg-faded m-3 rounded height-auto">
                            <textarea rows="2" class="form-control px-2 rounded-top"
                                      placeholder="Ask me anything"></textarea>
                            <div class="d-flex align-items-center py-2 px-2 border border-top-0 rounded-bottom">
                                <div class="d-flex gap-1 flex-row align-items-center flex-wrap flex-shrink-0">
                                    <button class="btn btn-icon fs-xl flex-shrink-0" aria-label="Attach Files or Photos"
                                            type="button" data-bs-toggle="tooltip"
                                            data-bs-original-title="Attach Files or Photos" data-bs-placement="top">
                                        <svg class="sa-icon sa-bold sa-icon-subtlelight">
                                            <use href="/icons/sprite.svg#file"></use>
                                        </svg>
                                    </button>
                                    <button class="btn btn-icon fs-xl width-1 flex-shrink-0" aria-label="Voice"
                                            type="button" data-bs-toggle="tooltip" data-bs-original-title="Voice"
                                            data-bs-placement="top">
                                        <svg class="sa-icon sa-bold sa-icon-subtlelight">
                                            <use href="/icons/sprite.svg#mic"></use>
                                        </svg>
                                    </button>
                                </div>
                                <button class="btn btn-icon fs-xl width-1 flex-shrink-0 ms-auto" aria-label="Send"
                                        type="button" data-bs-toggle="tooltip" data-bs-original-title="Send"
                                        data-bs-placement="top">
                                    <svg class="sa-icon sa-bold sa-icon-subtlelight sa-icon-2x">
                                        <use href="/icons/sprite.svg#arrow-up-circle"></use>
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>

<div class="backdrop" data-action="toggle-swap" data-toggleclass="open" data-target="aside.js-app-drawer"></div>
