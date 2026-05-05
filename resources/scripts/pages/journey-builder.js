/*!
 * Journey Builder — drag/drop assets onto stages, with auto-routing connectors.
 * SortableJS for the drag mechanic; hand-rolled SVG for the connector overlay.
 */

import Sortable from 'sortablejs';

const TYPE_COLORS = {
    academy_course: '#1A8A4A',
    ilt_workshop: '#1A5276',
    specialist_engagement: '#B7540F',
    blueprint: '#6B21A8',
    reading: '#0D9488',
    video: '#B45309',
    mentor_session: '#991B1B',
};

const DEFAULT_COLOR = '#0A2F46';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-journey-builder]');
    if (!root) return;

    const journeyId = root.dataset.journeyId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const attachUrl = root.dataset.attachUrl;
    const detachUrlTemplate = root.dataset.detachUrlTemplate;

    const svg = root.querySelector('[data-connector-overlay]');
    const library = root.querySelector('[data-asset-library]');
    const stageLists = root.querySelectorAll('[data-stage-list]');

    // Library is the source — drag from here, items get cloned into a stage.
    new Sortable(library, {
        group: { name: 'journey-items', pull: 'clone', put: false },
        sort: false,
        animation: 150,
        ghostClass: 'jb-ghost',
        chosenClass: 'jb-chosen',
        dragClass: 'jb-drag',
    });

    stageLists.forEach((list) => {
        new Sortable(list, {
            group: { name: 'journey-items', pull: false, put: true },
            animation: 150,
            ghostClass: 'jb-ghost',
            chosenClass: 'jb-chosen',
            dragClass: 'jb-drag',
            onAdd: (evt) => onDropOntoStage(evt, list),
        });
    });

    async function onDropOntoStage(evt, stageList) {
        const card = evt.item;
        const stageNumber = parseInt(stageList.dataset.stageNumber, 10);
        const itemType = card.dataset.itemType;
        const itemId = parseInt(card.dataset.itemId, 10);

        // Optimistically render — replace the cloned card with a "saving" state until persisted.
        card.classList.add('jb-saving');
        card.setAttribute('data-stage-card', '');

        try {
            const response = await fetch(attachUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    stage_number: stageNumber,
                    item_type: itemType,
                    item_id: itemId,
                }),
            });

            if (!response.ok) {
                throw new Error(`Attach failed: ${response.status}`);
            }

            const persisted = await response.json();
            card.classList.remove('jb-saving');
            card.dataset.stageItemId = persisted.item_id;
            ensureRemoveButton(card);
            redrawConnectors();
        } catch (err) {
            console.error(err);
            card.remove();
            redrawConnectors();
            alert('Could not attach to stage. Please retry.');
        }
    }

    function ensureRemoveButton(card) {
        if (card.querySelector('.jb-remove')) return;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'jb-remove btn-close btn-close-white opacity-75';
        btn.setAttribute('aria-label', 'Remove from stage');
        btn.addEventListener('click', () => detachItem(card));
        card.appendChild(btn);
    }

    async function detachItem(card) {
        const stageItemId = card.dataset.stageItemId;
        if (!stageItemId) {
            card.remove();
            redrawConnectors();
            return;
        }
        const url = detachUrlTemplate.replace('__ITEM__', stageItemId);
        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            if (!response.ok) throw new Error(`Detach failed: ${response.status}`);
            card.remove();
            redrawConnectors();
        } catch (err) {
            console.error(err);
            alert('Could not remove. Please retry.');
        }
    }

    // Hook up remove buttons on already-rendered items.
    root.querySelectorAll('[data-stage-card]').forEach((card) => {
        const btn = card.querySelector('.jb-remove');
        if (btn) btn.addEventListener('click', () => detachItem(card));
    });

    // ── Connector engine ──────────────────────────────────────────────────
    function redrawConnectors() {
        if (!svg) return;
        const overlayRect = svg.getBoundingClientRect();
        svg.setAttribute('viewBox', `0 0 ${overlayRect.width} ${overlayRect.height}`);
        svg.innerHTML = '';

        // For each placed stage card, find its source asset card and draw a curve.
        const placed = root.querySelectorAll('[data-stage-card]');
        placed.forEach((stageCard) => {
            const itemId = stageCard.dataset.itemId;
            const itemType = stageCard.dataset.itemType;
            const sourceCard = library.querySelector(`[data-item-type="${itemType}"][data-item-id="${itemId}"]`);
            if (!sourceCard) return;

            const color = TYPE_COLORS[stageCard.dataset.assetType] || DEFAULT_COLOR;
            drawCurve(svg, overlayRect, sourceCard, stageCard, color);
        });
    }

    function drawCurve(svg, overlayRect, fromEl, toEl, color) {
        const fr = fromEl.getBoundingClientRect();
        const tr = toEl.getBoundingClientRect();

        const x1 = fr.right - overlayRect.left;
        const y1 = fr.top + fr.height / 2 - overlayRect.top;
        const x2 = tr.left - overlayRect.left;
        const y2 = tr.top + tr.height / 2 - overlayRect.top;

        const cx = (x1 + x2) / 2;
        const d = `M ${x1} ${y1} C ${cx} ${y1}, ${cx} ${y2}, ${x2} ${y2}`;

        const trail = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        trail.setAttribute('d', d);
        trail.setAttribute('fill', 'none');
        trail.setAttribute('stroke', color);
        trail.setAttribute('stroke-width', '6');
        trail.setAttribute('stroke-opacity', '0.10');
        trail.setAttribute('stroke-linecap', 'round');
        svg.appendChild(trail);

        const line = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        line.setAttribute('d', d);
        line.setAttribute('fill', 'none');
        line.setAttribute('stroke', color);
        line.setAttribute('stroke-width', '2');
        line.setAttribute('stroke-opacity', '0.85');
        line.setAttribute('stroke-linecap', 'round');
        svg.appendChild(line);

        const dotFrom = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        dotFrom.setAttribute('cx', x1);
        dotFrom.setAttribute('cy', y1);
        dotFrom.setAttribute('r', '4');
        dotFrom.setAttribute('fill', color);
        svg.appendChild(dotFrom);

        const dotTo = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        dotTo.setAttribute('cx', x2);
        dotTo.setAttribute('cy', y2);
        dotTo.setAttribute('r', '4');
        dotTo.setAttribute('fill', color);
        svg.appendChild(dotTo);
    }

    // Auto-redraw on layout changes.
    const ro = new ResizeObserver(() => redrawConnectors());
    ro.observe(root);

    const mo = new MutationObserver(() => redrawConnectors());
    mo.observe(root, { childList: true, subtree: true, attributes: false });

    window.addEventListener('scroll', redrawConnectors, { passive: true });
    window.addEventListener('resize', redrawConnectors);

    // Asset library type filter.
    const filterChips = root.querySelectorAll('[data-type-filter]');
    filterChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            filterChips.forEach((c) => c.classList.remove('active'));
            chip.classList.add('active');
            const filter = chip.dataset.typeFilter;
            library.querySelectorAll('[data-library-card]').forEach((card) => {
                if (filter === 'all' || card.dataset.itemType === 'asset' && card.dataset.assetType === filter) {
                    card.classList.remove('d-none');
                } else {
                    card.classList.add('d-none');
                }
            });
            redrawConnectors();
        });
    });

    // Search filter.
    const searchInput = root.querySelector('[data-asset-search]');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim().toLowerCase();
            library.querySelectorAll('[data-library-card]').forEach((card) => {
                const name = (card.dataset.searchText || '').toLowerCase();
                if (term === '' || name.includes(term)) {
                    card.classList.remove('d-none-search');
                } else {
                    card.classList.add('d-none-search');
                }
            });
            redrawConnectors();
        });
    }

    // Initial draw after layout settles.
    requestAnimationFrame(() => requestAnimationFrame(redrawConnectors));
});
