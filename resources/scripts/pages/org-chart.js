import * as d3 from 'd3';

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('org-chart-container');
    if (!container) return;

    const dataUrl = container.dataset.url;
    const updateUrlBase = container.dataset.updateUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Role-based color scheme
    const ROLE_COLORS = {
        super_admin: { stroke: '#dc3545', fill: 'rgba(220,53,69,0.08)' },
        admin:       { stroke: '#0d6efd', fill: 'rgba(13,110,253,0.08)' },
        manager:     { stroke: '#198754', fill: 'rgba(25,135,84,0.08)' },
        coe:         { stroke: '#6f42c1', fill: 'rgba(111,66,193,0.08)' },
        psm:         { stroke: '#fd7e14', fill: 'rgba(253,126,20,0.08)' },
        pam:         { stroke: '#20c997', fill: 'rgba(32,201,151,0.08)' },
        integration: { stroke: '#6c757d', fill: 'rgba(108,117,125,0.08)' },
    };
    const DEFAULT_COLOR = { stroke: '#6c757d', fill: 'rgba(108,117,125,0.08)' };

    const CARD_W = 154;
    const CARD_H = 52;
    const DROP_THRESHOLD = 60;

    let allUsers = [];
    let svg, g, zoomBehavior;

    // ── Data loading ─────────────────────────────────────────────
    async function loadAndRender() {
        try {
            const res = await fetch(dataUrl, { headers: { Accept: 'application/json' } });
            allUsers = await res.json();
            render();
        } catch {
            container.innerHTML = '<div class="alert alert-danger m-3">Failed to load org chart data.</div>';
        }
    }

    // ── Hierarchy builder ────────────────────────────────────────
    function buildHierarchy(users) {
        const byId = new Map(users.map(u => [u.id, { ...u, children: [] }]));
        const roots = [];

        byId.forEach(node => {
            if (node.manager_id && byId.has(node.manager_id)) {
                byId.get(node.manager_id).children.push(node);
            } else {
                roots.push(node);
            }
        });

        const sortTree = n => { n.children.sort((a, b) => a.name.localeCompare(b.name)); n.children.forEach(sortTree); };
        roots.sort((a, b) => a.name.localeCompare(b.name));
        roots.forEach(sortTree);

        return { id: null, name: 'Organization', role: '', role_slug: null, children: roots };
    }

    // ── Render ───────────────────────────────────────────────────
    function render() {
        d3.select(container).select('svg').remove();

        const hierarchyData = buildHierarchy(allUsers);
        const root = d3.hierarchy(hierarchyData);

        const treeLayout = d3.tree()
            .nodeSize([CARD_W + 20, CARD_H + 40])
            .separation((a, b) => a.parent === b.parent ? 1 : 1.4);

        treeLayout(root);

        // Compute bounding box
        let x0 = Infinity, x1 = -Infinity, y0 = Infinity, y1 = -Infinity;
        root.each(d => {
            x0 = Math.min(x0, d.x);
            x1 = Math.max(x1, d.x);
            y0 = Math.min(y0, d.y);
            y1 = Math.max(y1, d.y);
        });

        const pad = 60;
        const vbX = x0 - CARD_W / 2 - pad;
        const vbY = y0 - CARD_H / 2 - pad;
        const vbW = (x1 - x0) + CARD_W + pad * 2;
        const vbH = (y1 - y0) + CARD_H + pad * 2;

        // Determine rendered height (clamp between 320-600px)
        const containerW = container.clientWidth || 900;
        const aspect = vbH / vbW;
        const renderedH = Math.max(320, Math.min(600, containerW * aspect));

        svg = d3.select(container)
            .append('svg')
            .attr('width', '100%')
            .attr('height', renderedH)
            .attr('viewBox', `${vbX} ${vbY} ${vbW} ${vbH}`)
            .style('user-select', 'none');

        g = svg.append('g');

        // Zoom / pan
        zoomBehavior = d3.zoom()
            .scaleExtent([0.25, 2.5])
            .on('zoom', e => g.attr('transform', e.transform));
        svg.call(zoomBehavior);

        // Reset-zoom button
        const resetBtn = document.getElementById('org-chart-reset-zoom');
        if (resetBtn) {
            resetBtn.onclick = () => svg.transition().duration(400).call(zoomBehavior.transform, d3.zoomIdentity);
        }

        // ── Links (skip virtual root) ────────────────────────────
        g.selectAll('.org-link')
            .data(root.links().filter(d => d.source.data.id !== null))
            .join('path')
            .attr('class', 'org-link')
            .attr('d', d3.linkVertical().x(d => d.x).y(d => d.y))
            .attr('fill', 'none')
            .attr('stroke', '#dee2e6')
            .attr('stroke-width', 1.5);

        // ── Nodes (skip virtual root) ────────────────────────────
        const realNodes = root.descendants().filter(d => d.data.id !== null);

        const nodes = g.selectAll('.org-node')
            .data(realNodes, d => d.data.id)
            .join('g')
            .attr('class', 'org-node')
            .attr('transform', d => `translate(${d.x},${d.y})`);

        // Card background
        nodes.append('rect')
            .attr('x', -CARD_W / 2)
            .attr('y', -CARD_H / 2)
            .attr('width', CARD_W)
            .attr('height', CARD_H)
            .attr('rx', 6)
            .attr('fill', d => colorFor(d).fill)
            .attr('stroke', d => colorFor(d).stroke)
            .attr('stroke-width', 1.5)
            .style('cursor', 'grab');

        // User name
        nodes.append('text')
            .attr('text-anchor', 'middle')
            .attr('dy', '-0.2em')
            .attr('fill', '#212529')
            .attr('font-size', '11.5px')
            .attr('font-weight', '600')
            .attr('pointer-events', 'none')
            .text(d => truncate(d.data.name, 20));

        // Role label
        nodes.append('text')
            .attr('text-anchor', 'middle')
            .attr('dy', '1.15em')
            .attr('fill', d => colorFor(d).stroke)
            .attr('font-size', '9.5px')
            .attr('pointer-events', 'none')
            .text(d => d.data.role);

        // ── Drag-and-drop ────────────────────────────────────────
        let originLine = null;   // dashed line from original position
        let dragOriginX, dragOriginY;

        nodes.call(d3.drag()
            .on('start', function (event, d) {
                dragOriginX = d.x;
                dragOriginY = d.y;

                // Dashed line from original position to track where it came from
                originLine = g.append('line')
                    .attr('class', 'org-drag-line')
                    .attr('x1', d.x).attr('y1', d.y)
                    .attr('x2', d.x).attr('y2', d.y)
                    .attr('stroke', '#0d6efd')
                    .attr('stroke-width', 1.5)
                    .attr('stroke-dasharray', '6,4')
                    .attr('opacity', 0.5);

                // Dim invalid targets
                nodes.select('rect').transition().duration(150)
                    .attr('opacity', n => isValidTarget(d, n) ? 1 : 0.2);

                // Style the dragged node
                d3.select(this).raise()
                    .select('rect')
                    .attr('stroke', '#0d6efd')
                    .attr('stroke-width', 2.5);

                d3.select(this).style('cursor', 'grabbing');
            })
            .on('drag', function (event, d) {
                // Move the entire node group
                d3.select(this).attr('transform', `translate(${event.x},${event.y})`);

                // Update the origin line endpoint
                if (originLine) originLine.attr('x2', event.x).attr('y2', event.y);

                // Highlight the closest valid target
                nodes.select('rect')
                    .attr('stroke-width', function (n) {
                        // Keep dragged node's own highlight
                        if (n.data.id === d.data.id) return 2.5;
                        if (!isValidTarget(d, n)) return 1.5;
                        const dist = Math.hypot(n.x - event.x, n.y - event.y);
                        return dist < DROP_THRESHOLD ? 3 : 1.5;
                    });
            })
            .on('end', function (event, d) {
                if (originLine) originLine.remove();
                originLine = null;

                // Snap back to original position
                d3.select(this)
                    .transition().duration(200)
                    .attr('transform', `translate(${dragOriginX},${dragOriginY})`);

                // Reset styles
                d3.select(this).style('cursor', 'grab')
                    .select('rect')
                    .attr('stroke', colorFor(d).stroke)
                    .attr('stroke-width', 1.5);

                nodes.select('rect').transition().duration(150)
                    .attr('opacity', 1)
                    .attr('stroke-width', 1.5);

                // Find drop target
                const target = findDropTarget(event.x, event.y, d, realNodes);
                if (target !== undefined) {
                    updateManager(d.data.id, target ? target.data.id : null);
                }
            })
        );
    }

    // ── Helpers ───────────────────────────────────────────────────
    function colorFor(d) {
        return ROLE_COLORS[d.data.role_slug] || DEFAULT_COLOR;
    }

    function truncate(str, max) {
        return str.length > max ? str.slice(0, max - 1) + '\u2026' : str;
    }

    function isValidTarget(dragged, candidate) {
        if (candidate.data.id === dragged.data.id) return false;
        // Cannot drop on own descendant (would create cycle)
        let p = candidate;
        while (p) {
            if (p.data.id === dragged.data.id) return false;
            p = p.parent;
        }
        return true;
    }

    function findDropTarget(x, y, dragged, nodes) {
        let closest = null;
        let closestDist = Infinity;

        for (const n of nodes) {
            if (!isValidTarget(dragged, n)) continue;
            const dist = Math.hypot(n.x - x, n.y - y);
            if (dist < DROP_THRESHOLD && dist < closestDist) {
                closest = n;
                closestDist = dist;
            }
        }

        if (closest) return closest;

        // Dropped on empty space → unassign (only if currently has a manager)
        if (dragged.data.manager_id !== null) return null;
        return undefined; // no-op
    }

    // ── AJAX update ──────────────────────────────────────────────
    async function updateManager(userId, newManagerId) {
        try {
            const res = await fetch(updateUrlBase + '/' + userId, {
                method: 'PUT',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ manager_id: newManagerId }),
            });

            const data = await res.json();

            if (!res.ok) {
                showToast(data.message || 'Failed to update manager.', 'danger');
                return;
            }

            // Update in-memory data
            const user = allUsers.find(u => u.id === userId);
            if (user) user.manager_id = newManagerId;

            render();
            syncTableRow(userId, data.user);
            showToast(data.message, 'success');
        } catch {
            showToast('Network error. Please try again.', 'danger');
        }
    }

    // ── Table sync ───────────────────────────────────────────────
    function syncTableRow(userId, userData) {
        const btn = document.querySelector('.edit-manager-btn[data-user-id="' + userId + '"]');
        if (!btn) return;
        const row = btn.closest('tr');
        if (!row) return;
        const cells = row.querySelectorAll('td');

        // Manager column (index 2)
        cells[2].innerHTML = userData.manager_name
            ? '<span class="badge bg-success">' + escHtml(userData.manager_name) + '</span>'
            : '<span class="badge bg-warning text-dark">None</span>';

        // Direct reports column (index 3)
        cells[3].innerHTML = userData.direct_report_count > 0
            ? '<span class="badge bg-info">' + userData.direct_report_count + '</span>'
            : '<span class="text-muted">0</span>';

        btn.setAttribute('data-current-manager-id', userData.manager_id || '');
    }

    function escHtml(str) {
        const el = document.createElement('span');
        el.textContent = str;
        return el.innerHTML;
    }

    // ── Toast notifications ──────────────────────────────────────
    function showToast(message, type) {
        const toastArea = document.getElementById('org-chart-toast');
        if (!toastArea) return;
        const alert = document.createElement('div');
        alert.className = 'alert alert-' + type + ' alert-dismissible fade show mb-0 rounded-0 border-start-0 border-end-0 py-2 px-3';
        alert.innerHTML = '<small>' + escHtml(message) + '</small><button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>';
        toastArea.prepend(alert);
        setTimeout(() => { if (alert.parentNode) alert.remove(); }, 4000);
    }

    // ── Boot ─────────────────────────────────────────────────────
    loadAndRender();
});
