(function () {
    'use strict';

    var panel = document.getElementById('ai-generate-lab-panel');
    if (!panel) return;

    var generateBtn = document.getElementById('ai-generate-lab-btn');
    var generateUrl = panel.dataset.generateUrl;
    var spinner = panel.querySelector('.ai-generate-spinner');
    var errorEl = panel.querySelector('.ai-generate-error');
    var checkpointPreview = document.getElementById('ai-checkpoint-preview');
    var checkpointsInput = document.getElementById('checkpoints_json');

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    generateBtn.addEventListener('click', async function () {
        var tenant = panel.querySelector('[name="ai_tenant"]').value;
        var topic = panel.querySelector('[name="ai_topic"]').value;
        var durationMinutes = panel.querySelector('[name="ai_duration_minutes"]').value;

        if (!tenant || !topic) {
            errorEl.textContent = 'Tenant and topic are required.';
            errorEl.classList.remove('d-none');
            return;
        }

        generateBtn.disabled = true;
        spinner.classList.remove('d-none');
        errorEl.classList.add('d-none');

        try {
            var response = await fetch(generateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    tenant: tenant,
                    topic: topic,
                    duration_minutes: durationMinutes ? parseInt(durationMinutes) : null,
                }),
            });

            if (response.status === 419) {
                errorEl.textContent = 'Session expired. Please refresh the page and try again.';
                errorEl.classList.remove('d-none');
                return;
            }

            if (!response.ok) {
                var err = await response.json().catch(function () { return {}; });
                throw new Error(err.message || 'Generation failed');
            }

            var data = await response.json();
            var lab = data.lab;

            // Populate form fields
            var fields = {
                name: lab.name,
                tenant: lab.tenant,
                duration_minutes: lab.duration_minutes,
                description: lab.description,
                instructions: lab.instructions,
            };

            Object.keys(fields).forEach(function (id) {
                var el = document.getElementById(id);
                if (el && fields[id] != null) {
                    el.value = fields[id];
                    el.classList.add('border-success');
                    setTimeout(function () { el.classList.remove('border-success'); }, 3000);
                }
            });

            // Store checkpoints JSON in hidden field
            checkpointsInput.value = JSON.stringify(lab.checkpoints || []);

            // Render checkpoint preview
            renderCheckpointPreview(lab.checkpoints || []);

            // Collapse the AI panel
            var collapseEl = document.getElementById('aiLabCollapse');
            var bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
            if (bsCollapse) bsCollapse.hide();

        } catch (err) {
            errorEl.textContent = err.message || 'Failed to generate. Please try again.';
            errorEl.classList.remove('d-none');
        } finally {
            generateBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    });

    function renderCheckpointPreview(checkpoints) {
        if (!checkpoints.length) {
            checkpointPreview.classList.add('d-none');
            return;
        }

        checkpointPreview.classList.remove('d-none');
        var container = checkpointPreview.querySelector('.checkpoint-cards');
        container.innerHTML = '';

        var typeColors = {
            formula: 'info',
            structure: 'primary',
            config: 'warning',
            value: 'success',
            list: 'secondary',
        };

        checkpoints.forEach(function (cp) {
            var color = typeColors[cp.checkpoint_type] || 'secondary';
            var card = document.createElement('div');
            card.className = 'border rounded p-2 mb-2';
            card.innerHTML =
                '<div class="d-flex justify-content-between align-items-center">' +
                    '<strong>' + cp.sort_order + '. ' + escapeHtml(cp.title) + '</strong>' +
                    '<span class="badge bg-' + color + ' bg-opacity-25 text-' + color + '">' + escapeHtml(cp.checkpoint_type) + '</span>' +
                '</div>' +
                '<small class="text-muted">' + escapeHtml(cp.description || '') + '</small>' +
                '<div class="mt-1">' +
                    '<span class="badge bg-primary bg-opacity-25 text-primary me-1">' + cp.points + ' pts</span>' +
                    (cp.is_required ? '<span class="badge bg-success bg-opacity-25 text-success">Required</span>' : '<span class="badge bg-secondary bg-opacity-25 text-secondary">Optional</span>') +
                '</div>';
            container.appendChild(card);
        });
    }
})();
