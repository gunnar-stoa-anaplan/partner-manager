(function () {
    'use strict';

    var panel = document.getElementById('ai-insights-partner-panel');
    if (!panel) return;

    var generateBtn = document.getElementById('ai-insights-btn');
    var advisorUrl = panel.dataset.advisorUrl;
    var spinner = panel.querySelector('.ai-generate-spinner');
    var errorEl = panel.querySelector('.ai-generate-error');
    var resultsEl = panel.querySelector('.ai-insights-results');

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function scoreColor(score) {
        if (score >= 70) return 'success';
        if (score >= 40) return 'warning';
        return 'danger';
    }

    function riskColor(level) {
        var l = (level || '').toLowerCase();
        if (l === 'low') return 'success';
        if (l === 'medium') return 'warning';
        return 'danger';
    }

    generateBtn.addEventListener('click', async function () {
        generateBtn.disabled = true;
        spinner.classList.remove('d-none');
        errorEl.classList.add('d-none');
        resultsEl.classList.add('d-none');

        try {
            var response = await fetch(advisorUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });

            if (response.status === 419) {
                errorEl.textContent = 'Session expired. Please refresh the page and try again.';
                errorEl.classList.remove('d-none');
                return;
            }

            if (!response.ok) {
                var err = await response.json().catch(function () { return {}; });
                throw new Error(err.message || 'Analysis failed');
            }

            var data = await response.json();
            var a = data.analysis;

            var html = '<div class="row g-3">';

            // Health Score + Risk Level header
            html += '<div class="col-12">';
            html += '<div class="d-flex align-items-center gap-3 mb-2">';
            html += '<div class="text-center">';
            html += '<div class="fs-xs text-muted mb-1">Health Score</div>';
            html += '<span class="badge bg-' + scoreColor(a.health_score) + ' fs-lg px-3 py-2">' + escapeHtml(String(a.health_score)) + '</span>';
            html += '</div>';
            html += '<div class="text-center">';
            html += '<div class="fs-xs text-muted mb-1">Risk Level</div>';
            html += '<span class="badge bg-' + riskColor(a.risk_level) + ' fs-md px-3 py-2">' + escapeHtml(a.risk_level) + '</span>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            // Summary
            html += '<div class="col-12">';
            html += '<div class="fs-sm">' + escapeHtml(a.summary) + '</div>';
            html += '</div>';

            // Recommendations
            html += '<div class="col-12">';
            html += '<div class="fw-500 fs-sm mb-2"><i class="fal fa-lightbulb me-1 text-warning"></i> Recommendations</div>';
            html += '<ol class="fs-sm mb-0 ps-3">';
            a.recommendations.forEach(function (rec) {
                html += '<li class="mb-1">' + escapeHtml(rec) + '</li>';
            });
            html += '</ol>';
            html += '</div>';

            // Strength + Risk cards
            html += '<div class="col-md-6">';
            html += '<div class="border rounded p-2 border-success bg-success bg-opacity-10">';
            html += '<div class="fw-500 fs-xs text-success mb-1"><i class="fal fa-check-circle me-1"></i> Key Strength</div>';
            html += '<div class="fs-sm">' + escapeHtml(a.key_strength) + '</div>';
            html += '</div>';
            html += '</div>';

            html += '<div class="col-md-6">';
            html += '<div class="border rounded p-2 border-danger bg-danger bg-opacity-10">';
            html += '<div class="fw-500 fs-xs text-danger mb-1"><i class="fal fa-exclamation-triangle me-1"></i> Key Risk</div>';
            html += '<div class="fs-sm">' + escapeHtml(a.key_risk) + '</div>';
            html += '</div>';
            html += '</div>';

            html += '</div>';

            resultsEl.innerHTML = html;
            resultsEl.classList.remove('d-none');
            generateBtn.classList.add('d-none');

        } catch (err) {
            errorEl.textContent = err.message || 'Failed to generate insights. Please try again.';
            errorEl.classList.remove('d-none');
        } finally {
            generateBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    });
})();
