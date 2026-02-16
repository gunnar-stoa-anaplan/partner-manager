(function () {
    'use strict';

    const drawer = document.querySelector('aside.js-app-drawer');
    if (!drawer) return;

    const chatUrl = drawer.dataset.chatUrl;
    if (!chatUrl) return;

    const messagesContainer = document.getElementById('ai-chat-messages');
    const input = document.getElementById('ai-chat-input');
    const sendBtn = document.getElementById('ai-chat-send');
    const quickActions = document.getElementById('ai-chat-quick-actions');

    if (!messagesContainer || !input || !sendBtn) return;

    let isStreaming = false;

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function createBubble(text, type) {
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex mb-2 ' + (type === 'user' ? 'justify-content-end' : 'justify-content-start');

        const bubble = document.createElement('div');
        bubble.className = type === 'user'
            ? 'bg-primary text-white rounded px-3 py-2 fs-sm'
            : 'bg-white border rounded px-3 py-2 fs-sm ai-response-bubble';
        bubble.style.maxWidth = '85%';
        bubble.style.whiteSpace = 'pre-wrap';
        bubble.style.wordBreak = 'break-word';

        if (text) {
            bubble.textContent = text;
        }

        wrapper.appendChild(bubble);
        messagesContainer.appendChild(wrapper);
        scrollToBottom();

        return bubble;
    }

    function createTypingIndicator() {
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex mb-2 justify-content-start';
        wrapper.id = 'ai-typing-indicator';

        const bubble = document.createElement('div');
        bubble.className = 'bg-white border rounded px-3 py-2 fs-sm';
        bubble.innerHTML = '<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>';

        wrapper.appendChild(bubble);
        messagesContainer.appendChild(wrapper);
        scrollToBottom();

        return wrapper;
    }

    function removeTypingIndicator() {
        const indicator = document.getElementById('ai-typing-indicator');
        if (indicator) indicator.remove();
    }

    function formatMarkdown(text) {
        // Bold
        let formatted = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        // Line breaks
        formatted = formatted.replace(/\n/g, '<br>');
        // Simple list items
        formatted = formatted.replace(/^- /gm, '&bull; ');
        return formatted;
    }

    async function sendMessage(text) {
        if (isStreaming || !text.trim()) return;

        isStreaming = true;
        input.disabled = true;
        sendBtn.disabled = true;

        // Hide quick actions on first message
        if (quickActions) {
            quickActions.style.display = 'none';
        }

        // Show user bubble
        createBubble(text.trim(), 'user');

        // Show typing indicator
        createTypingIndicator();

        try {
            const response = await fetch(chatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/plain',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ message: text.trim() }),
            });

            if (response.status === 419) {
                removeTypingIndicator();
                createBubble('Your session has expired. Please refresh the page and try again.', 'assistant');
                return;
            }

            if (!response.ok) {
                removeTypingIndicator();
                createBubble('Sorry, something went wrong. Please try again.', 'assistant');
                return;
            }

            removeTypingIndicator();

            // Create assistant bubble and stream into it
            const assistantBubble = createBubble('', 'assistant');
            let fullText = '';

            const reader = response.body.getReader();
            const decoder = new TextDecoder();

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                const chunk = decoder.decode(value, { stream: true });
                fullText += chunk;
                assistantBubble.innerHTML = formatMarkdown(fullText);
                scrollToBottom();
            }

            // Final decode flush
            const remaining = decoder.decode();
            if (remaining) {
                fullText += remaining;
                assistantBubble.innerHTML = formatMarkdown(fullText);
                scrollToBottom();
            }

        } catch (err) {
            removeTypingIndicator();
            createBubble('Unable to connect. Please check your connection and try again.', 'assistant');
        } finally {
            isStreaming = false;
            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();
        }
    }

    // Send on button click
    sendBtn.addEventListener('click', function () {
        sendMessage(input.value);
        input.value = '';
    });

    // Enter sends, Shift+Enter for newline
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(input.value);
            input.value = '';
        }
    });

    // Quick action buttons
    if (quickActions) {
        quickActions.querySelectorAll('.ai-quick-action').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var prompt = this.dataset.prompt;
                if (prompt) {
                    sendMessage(prompt);
                }
            });
        });
    }
})();
