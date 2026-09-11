@props(['screen'])

@if (config('agent.enabled'))
    {{-- Embedded screen agent. Renders only when the agent feature is enabled,
         so it is safe to include on any view. Grounded in the caller's scoped
         data; all rendering uses textContent (XSS-safe). --}}
    <button type="button" class="btn btn-sm btn-primary position-fixed"
            style="bottom:1.25rem; right:1.25rem; z-index:1045; border-radius:2rem;"
            data-bs-toggle="offcanvas" data-bs-target="#screen-agent">
        <i class="fal fa-sparkles me-1"></i> Ask about this screen
    </button>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="screen-agent" aria-labelledby="screen-agent-title">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="screen-agent-title">Screen assistant</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <div class="text-muted small mb-2">
                Grounded in <span id="agent-scope">your data</span> ·
                <span id="agent-provider" class="text-uppercase"></span>
            </div>

            <h6 class="text-uppercase text-muted small mb-2">Insights</h6>
            <ul id="agent-insights" class="list-unstyled small mb-3">
                <li class="text-muted">Loading…</li>
            </ul>

            <h6 class="text-uppercase text-muted small mb-2">Ask a question</h6>
            <div id="agent-answer" class="small mb-2"></div>
            <div class="mt-auto">
                <div class="input-group input-group-sm">
                    <input type="text" id="agent-question" class="form-control"
                           placeholder="e.g. What is my open pipeline?" maxlength="1000">
                    <button class="btn btn-primary" type="button" id="agent-ask-btn">Ask</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const screen = @js($screen);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const el = (id) => document.getElementById(id);
        let loaded = false;

        async function post(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
                body: JSON.stringify(body),
            });
            if (!res.ok) throw new Error('request failed');
            return res.json();
        }

        function setList(items) {
            const ul = el('agent-insights');
            ul.textContent = '';
            (items || []).forEach((text) => {
                const li = document.createElement('li');
                li.className = 'mb-1';
                li.textContent = '• ' + text; // textContent — no HTML injection
                ul.appendChild(li);
            });
        }

        async function loadInsights() {
            if (loaded) return;
            try {
                const data = await post(@js(route('agent.insights')), {screen});
                el('agent-scope').textContent = data.scope || 'your data';
                el('agent-provider').textContent = data.provider || '';
                setList(data.insights);
                loaded = true;
            } catch (e) {
                setList(['Could not load insights right now.']);
            }
        }

        async function ask() {
            const q = el('agent-question').value.trim();
            if (!q) return;
            el('agent-answer').textContent = 'Thinking…';
            try {
                const data = await post(@js(route('agent.ask')), {screen, question: q});
                el('agent-answer').textContent = data.answer || '';
            } catch (e) {
                el('agent-answer').textContent = 'Could not answer right now.';
            }
        }

        document.getElementById('screen-agent').addEventListener('show.bs.offcanvas', loadInsights);
        el('agent-ask-btn').addEventListener('click', ask);
        el('agent-question').addEventListener('keydown', (e) => { if (e.key === 'Enter') ask(); });
    })();
    </script>
@endif
