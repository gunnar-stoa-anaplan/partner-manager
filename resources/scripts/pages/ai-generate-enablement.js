(function () {
    'use strict';

    var panel = document.getElementById('ai-generate-enablement-panel');
    if (!panel) return;

    var generateBtn = document.getElementById('ai-generate-enablement-btn');
    var generateUrl = panel.dataset.generateUrl;
    var spinner = panel.querySelector('.ai-generate-spinner');
    var errorEl = panel.querySelector('.ai-generate-error');

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    generateBtn.addEventListener('click', async function () {
        var productArea = panel.querySelector('[name="ai_product_area"]').value;
        var type = panel.querySelector('[name="ai_type"]').value;
        var topic = panel.querySelector('[name="ai_topic"]').value;
        var audience = panel.querySelector('[name="ai_audience"]').value;

        if (!productArea || !type) {
            errorEl.textContent = 'Product area and type are required.';
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
                    product_area: productArea,
                    type: type,
                    topic: topic || null,
                    audience: audience || null,
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
            var w = data.workshop;

            // Populate form fields
            var fields = {
                title: w.title,
                type: w.type,
                category: w.category,
                product_area: w.product_area,
                target_audience: w.target_audience,
                duration_hours: w.duration_hours,
                max_participants: w.max_participants,
                description: w.description,
                objectives: w.objectives,
                prerequisites: w.prerequisites,
            };

            Object.keys(fields).forEach(function (id) {
                var el = document.getElementById(id);
                if (el && fields[id] != null) {
                    el.value = fields[id];
                    el.classList.add('border-success');
                    setTimeout(function () { el.classList.remove('border-success'); }, 3000);
                }
            });

            // Collapse the AI panel
            var collapseEl = document.getElementById('aiEnablementCollapse');
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
})();
