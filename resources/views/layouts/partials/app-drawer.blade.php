<style>
    .typing-dots span { animation: typingDot 1.4s infinite; opacity: 0.2; font-size: 1.5em; line-height: 1; }
    .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typingDot { 0%, 60%, 100% { opacity: 0.2; } 30% { opacity: 1; } }
</style>
<aside class="app-drawer js-app-drawer" data-chat-url="{{ route('api.chat') }}">
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
                        <div id="ai-chat-messages"
                            class="w-100 p-4 pb-0 px-lg-3 bg-subtlelight-fade custom-scroll flex-grow-1 d-flex flex-column">

                            <div class="mb-g" id="ai-chat-greeting">
                                <h4 class="fw-600">Hi {{ explode(' ', Auth::user()->name)[0] }},</h4>
                                <span class="text-muted">how can I help you today?</span>
                            </div>
                            <div id="ai-chat-quick-actions">
                                <div class="mt-auto ms-auto mb-2 btn btn-outline-default px-2 py-1 fw-500 fs-xs text-gradient ai-quick-action"
                                     data-prompt="Give me a summary of my portfolio">
                                    Summarize my portfolio
                                </div>
                                <p class="ms-auto mb-2 btn btn-outline-default px-2 py-1 fs-xs fw-500 text-gradient ai-quick-action"
                                   data-prompt="What does my pipeline look like?">
                                    Show my pipeline
                                </p>
                                <p class="ms-auto mb-2 btn btn-outline-default px-2 py-1 fs-xs fw-500 text-gradient ai-quick-action"
                                   data-prompt="Which partners are at risk?">
                                    Partners at risk
                                </p>
                            </div>

                        </div>

                        <!-- chat input -->
                        <div class="bg-faded m-3 rounded height-auto">
                            <textarea id="ai-chat-input" rows="2" class="form-control px-2 rounded-top"
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
                                <button id="ai-chat-send" class="btn btn-icon fs-xl width-1 flex-shrink-0 ms-auto" aria-label="Send"
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
