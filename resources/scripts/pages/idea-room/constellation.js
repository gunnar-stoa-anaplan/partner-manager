import * as d3 from 'd3';
import axios from 'axios';

const API_BASE = '/api/v1/idea-room';

const STATE_COLORS = {
    spark:     { fill: '#f59e0b', glow: 'rgba(245, 158, 11, 0.3)', cls: 'ir-state-badge--spark', dot: 'bg-warning' },
    growing:   { fill: '#22c55e', glow: 'rgba(34, 197, 94, 0.3)',  cls: 'ir-state-badge--growing', dot: 'bg-success' },
    developed: { fill: '#3b82f6', glow: 'rgba(59, 130, 246, 0.3)', cls: 'ir-state-badge--developed', dot: 'bg-primary' },
    archived:  { fill: '#71717a', glow: 'rgba(113, 113, 122, 0.2)', cls: 'ir-state-badge--archived', dot: 'bg-secondary' },
    dropped:   { fill: '#ef4444', glow: 'rgba(239, 68, 68, 0.2)',  cls: 'ir-state-badge--dropped', dot: 'bg-danger' },
};

function formatDate(iso) {
    if (!iso) return '\u2014';
    return new Date(iso).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function hexToRgba(hex, alpha) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

document.addEventListener('DOMContentLoaded', async function () {
    const container = document.getElementById('ir-graph-container');
    if (!container) return;

    let allNodes, allEdges;
    try {
        const res = await axios.get(`${API_BASE}/ideas/constellation`);
        allNodes = res.data.nodes || [];
        allEdges = res.data.edges || [];
    } catch (err) {
        console.error('Failed to load constellation:', err);
        allNodes = [];
        allEdges = [];
    }

    const loadingEl = document.getElementById('ir-loading-indicator');
    if (loadingEl) loadingEl.remove();

    if (allNodes.length === 0) {
        const empty = document.getElementById('ir-empty-state');
        if (empty) { empty.classList.remove('d-none'); empty.classList.add('d-flex'); }
        return;
    }

    // Build bucket filter
    const bucketSelect = document.getElementById('ir-filter-bucket');
    const parentMap = {}, childrenOf = {};
    allNodes.forEach(n => n.buckets?.forEach(b => {
        if (b.parent_bucket_name) {
            parentMap[b.name] = b.parent_bucket_name;
            if (!childrenOf[b.parent_bucket_name]) childrenOf[b.parent_bucket_name] = new Set();
            childrenOf[b.parent_bucket_name].add(b.name);
        }
    }));
    const allGrouped = new Set([...Object.keys(childrenOf), ...Object.keys(parentMap), ...Object.values(parentMap)]);
    const allBucketNames = new Set();
    allNodes.forEach(n => n.buckets?.forEach(b => allBucketNames.add(b.name)));

    Object.keys(childrenOf).sort().forEach(parent => {
        const opt = new Option('\u25B8 ' + parent + ' (all)', 'parent:' + parent);
        opt.style.fontWeight = '600';
        bucketSelect.add(opt);
        [...childrenOf[parent]].sort().forEach(child => {
            bucketSelect.add(new Option('    ' + child, child));
        });
    });
    [...allBucketNames].sort().filter(b => !allGrouped.has(b)).forEach(b => {
        bucketSelect.add(new Option(b, b));
    });

    // Filter logic
    let filteredNodes = [...allNodes];
    let filteredEdges = [...allEdges];

    function applyFilters() {
        const bucket = bucketSelect.value;
        const state = document.getElementById('ir-filter-state').value;

        filteredNodes = allNodes.filter(n => {
            if (bucket) {
                if (bucket.startsWith('parent:')) {
                    const p = bucket.slice(7);
                    if (!n.buckets?.some(b => b.parent_bucket_name === p || b.name === p)) return false;
                } else {
                    if (!n.buckets?.some(b => b.name === bucket)) return false;
                }
            }
            if (state && n.lifecycle_state !== state) return false;
            return true;
        });

        const nodeUuids = new Set(filteredNodes.map(n => n.uuid));
        filteredEdges = allEdges.filter(e => nodeUuids.has(e.source) && nodeUuids.has(e.target));
        updateGraph();
    }

    bucketSelect.addEventListener('change', applyFilters);
    document.getElementById('ir-filter-state').addEventListener('change', applyFilters);

    // D3 graph
    const width = container.clientWidth;
    const height = container.clientHeight;

    const svg = d3.select(container)
        .append('svg')
        .attr('width', '100%')
        .attr('height', '100%')
        .attr('viewBox', [0, 0, width, height]);

    const g = svg.append('g');
    svg.call(d3.zoom().scaleExtent([0.3, 3]).on('zoom', (event) => g.attr('transform', event.transform)));

    const linkGroup = g.append('g').attr('class', 'links');
    const nodeGroup = g.append('g').attr('class', 'nodes');

    let simulation;
    let selectedUuid = null;

    function nodeRadius(d) { return 24 + Math.min(d.title.length, 30) * 0.3; }

    function updateGraph() {
        const nodes = filteredNodes.map(n => ({ ...n, r: nodeRadius(n) }));
        const nodeById = new Map(nodes.map(n => [n.uuid, n]));
        const links = filteredEdges
            .map(e => ({ source: nodeById.get(e.source), target: nodeById.get(e.target), ...e }))
            .filter(e => e.source && e.target);

        const link = linkGroup.selectAll('line').data(links, d => d.source.uuid + '-' + d.target.uuid);
        link.exit().remove();
        const linkEnter = link.enter().append('line')
            .attr('stroke', 'rgba(113, 113, 122, 0.25)')
            .attr('stroke-width', 1);
        const linkAll = linkEnter.merge(link);

        const node = nodeGroup.selectAll('g.node').data(nodes, d => d.uuid);
        node.exit().remove();

        const nodeEnter = node.enter().append('g')
            .attr('class', 'node')
            .style('cursor', 'pointer')
            .call(d3.drag()
                .on('start', dragStarted)
                .on('drag', dragged)
                .on('end', dragEnded)
            );

        nodeEnter.append('circle').attr('class', 'glow')
            .attr('r', d => d.r + 8)
            .attr('fill', d => (STATE_COLORS[d.lifecycle_state] || STATE_COLORS.spark).glow)
            .attr('opacity', 0);

        nodeEnter.append('circle').attr('class', 'main')
            .attr('r', d => d.r)
            .attr('fill', d => hexToRgba((STATE_COLORS[d.lifecycle_state] || STATE_COLORS.spark).fill, 0.15))
            .attr('stroke', d => (STATE_COLORS[d.lifecycle_state] || STATE_COLORS.spark).fill)
            .attr('stroke-width', 1.5);

        nodeEnter.append('text')
            .attr('text-anchor', 'middle')
            .attr('dy', d => d.r + 14)
            .attr('fill', '#52525b')
            .attr('font-size', '12px')
            .attr('font-weight', '500')
            .attr('font-family', 'system-ui, -apple-system, sans-serif')
            .text(d => d.title.length > 28 ? d.title.substring(0, 27) + '\u2026' : d.title);

        nodeEnter.each(function(d) {
            if (!d.buckets || d.buckets.length === 0) return;
            const dotG = d3.select(this);
            const dotSpacing = 6;
            const startX = -((d.buckets.length - 1) * dotSpacing) / 2;
            d.buckets.slice(0, 4).forEach((bucket, i) => {
                dotG.append('circle').attr('class', 'bucket-dot')
                    .attr('cx', startX + i * dotSpacing)
                    .attr('cy', d.r + 8).attr('r', 2)
                    .attr('fill', bucket.color || (STATE_COLORS[d.lifecycle_state] || STATE_COLORS.spark).fill);
            });
        });

        nodeEnter.on('click', (event, d) => { event.stopPropagation(); selectNode(d); });

        const nodeAll = nodeEnter.merge(node);

        svg.on('click', () => { selectedUuid = null; updateSelection(nodes); hidePanel(); });

        if (simulation) simulation.stop();
        simulation = d3.forceSimulation(nodes)
            .force('charge', d3.forceManyBody().strength(-300))
            .force('link', d3.forceLink(links).id(d => d.uuid).distance(120))
            .force('center', d3.forceCenter(width / 2, height / 2))
            .force('collision', d3.forceCollide().radius(d => d.r + 10))
            .on('tick', () => {
                linkAll.attr('x1', d => d.source.x).attr('y1', d => d.source.y)
                       .attr('x2', d => d.target.x).attr('y2', d => d.target.y);
                nodeAll.attr('transform', d => `translate(${d.x},${d.y})`);
            });

        function dragStarted(event, d) {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            d.fx = d.x; d.fy = d.y;
        }
        function dragged(event, d) { d.fx = event.x; d.fy = event.y; }
        function dragEnded(event, d) {
            if (!event.active) simulation.alphaTarget(0);
            d.fx = null; d.fy = null;
        }
    }

    function updateSelection(nodes) {
        nodeGroup.selectAll('g.node').each(function(d) {
            const gEl = d3.select(this);
            const colors = STATE_COLORS[d.lifecycle_state] || STATE_COLORS.spark;
            const isSel = d.uuid === selectedUuid;
            gEl.select('.glow').attr('opacity', isSel ? 1 : 0);
            gEl.select('.main')
                .attr('fill', isSel ? colors.fill : hexToRgba(colors.fill, 0.15))
                .attr('stroke-width', isSel ? 2.5 : 1.5);
            gEl.select('text').attr('fill', isSel ? '#fff' : '#e4e4e7');
        });
    }

    function selectNode(d) {
        selectedUuid = d.uuid;
        updateSelection(filteredNodes);
        showPanel(d);
    }

    const panel = document.getElementById('ir-detail-panel');
    document.getElementById('ir-detail-close')?.addEventListener('click', () => {
        selectedUuid = null;
        updateSelection(filteredNodes);
        hidePanel();
    });

    function showPanel(d) {
        const colors = STATE_COLORS[d.lifecycle_state] || STATE_COLORS.spark;

        document.getElementById('ir-detail-state').textContent = d.lifecycle_state;
        document.getElementById('ir-detail-state').className = 'ir-state-badge ' + colors.cls;
        document.getElementById('ir-detail-title').textContent = d.title;
        document.getElementById('ir-detail-text').textContent = d.original_text || '';
        document.getElementById('ir-detail-created').textContent = formatDate(d.created_at);
        document.getElementById('ir-detail-activity').textContent = formatDate(d.last_activity_at);

        const bucketsEl = document.getElementById('ir-detail-buckets');
        bucketsEl.innerHTML = '';
        if (d.buckets?.length > 0) {
            d.buckets.forEach(b => {
                const span = document.createElement('span');
                span.className = 'badge bg-body-secondary text-body me-1 mb-1';
                span.innerHTML = `${b.full_path || b.name} <span class="text-warning">${Math.round(b.confidence * 100)}%</span>`;
                bucketsEl.appendChild(span);
            });
        } else {
            bucketsEl.innerHTML = '<span class="text-muted small">No buckets assigned</span>';
        }

        const tagsEl = document.getElementById('ir-detail-tags');
        tagsEl.innerHTML = '';
        if (d.tags?.length > 0) {
            d.tags.forEach(t => {
                const span = document.createElement('span');
                span.className = 'badge bg-warning bg-opacity-10 text-warning me-1 mb-1';
                span.textContent = t;
                tagsEl.appendChild(span);
            });
        } else {
            tagsEl.innerHTML = '<span class="text-muted small">No tags</span>';
        }

        const connUuids = new Set();
        allEdges.forEach(e => {
            if (e.source === d.uuid) connUuids.add(e.target);
            if (e.target === d.uuid) connUuids.add(e.source);
        });
        const connected = allNodes.filter(n => connUuids.has(n.uuid));
        const connSection = document.getElementById('ir-detail-connected');
        const connList = document.getElementById('ir-detail-connected-list');
        connList.innerHTML = '';

        if (connected.length > 0) {
            connSection.classList.remove('d-none');
            connected.forEach(c => {
                const btn = document.createElement('button');
                btn.className = 'btn btn-sm btn-outline-secondary d-flex align-items-center gap-2 w-100 text-start mb-1';
                btn.innerHTML = `<span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:${(STATE_COLORS[c.lifecycle_state] || STATE_COLORS.spark).fill}"></span><span class="text-truncate">${c.title}</span>`;
                btn.addEventListener('click', () => {
                    const target = filteredNodes.find(n => n.uuid === c.uuid);
                    if (target) selectNode(target);
                });
                connList.appendChild(btn);
            });
        } else {
            connSection.classList.add('d-none');
        }

        document.getElementById('ir-detail-enter-room').href = `/idea-room/ideas/${d.uuid}/enter`;
        panel.classList.remove('d-none');
    }

    function hidePanel() { panel.classList.add('d-none'); }

    updateGraph();

    window.addEventListener('resize', () => {
        const w = container.clientWidth;
        const h = container.clientHeight;
        svg.attr('viewBox', [0, 0, w, h]);
        if (simulation) simulation.force('center', d3.forceCenter(w / 2, h / 2)).alpha(0.3).restart();
    });
});
