/**
 * Workshop Guides - Client-side interactivity
 * Forked from docs.js with additions:
 * - Prev/next keyboard navigation (arrow keys)
 * - localStorage page-visited tracking
 */
document.addEventListener('DOMContentLoaded', () => {
    initScrollSpy();
    initImageZoom();
    initMobileSidebar();
    initSearch();
    initPrevNextNav();
    initPageTracking();
});

// ---------------------------------------------------------------------------
// ScrollSpy — highlights the current heading in the right TOC
// ---------------------------------------------------------------------------
function initScrollSpy() {
    const tocLinks = document.querySelectorAll('.docs-toc-item a');
    if (tocLinks.length === 0) return;

    const headingIds = Array.from(tocLinks).map(link => {
        const id = link.getAttribute('href')?.replace('#', '');
        return id ? document.getElementById(id) : null;
    }).filter(Boolean);

    if (headingIds.length === 0) return;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    tocLinks.forEach(link => link.classList.remove('active'));
                    const activeLink = document.querySelector(
                        `.docs-toc-item a[href="#${entry.target.id}"]`
                    );
                    if (activeLink) {
                        activeLink.classList.add('active');
                    }
                }
            });
        },
        {
            rootMargin: '-80px 0px -60% 0px',
            threshold: 0,
        }
    );

    headingIds.forEach(heading => observer.observe(heading));
}

// ---------------------------------------------------------------------------
// Image zoom — click article images to view full-size in an overlay
// ---------------------------------------------------------------------------
function initImageZoom() {
    const overlay = document.getElementById('docsZoomOverlay');
    const zoomImage = document.getElementById('docsZoomImage');
    if (!overlay || !zoomImage) return;

    document.querySelectorAll('.docs-article img').forEach(img => {
        img.addEventListener('click', () => {
            zoomImage.src = img.src;
            zoomImage.alt = img.alt;
            overlay.classList.add('active');
        });
    });

    overlay.addEventListener('click', () => {
        overlay.classList.remove('active');
        zoomImage.src = '';
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('active')) {
            overlay.classList.remove('active');
            zoomImage.src = '';
        }
    });
}

// ---------------------------------------------------------------------------
// Mobile sidebar toggle
// ---------------------------------------------------------------------------
function initMobileSidebar() {
    const toggle = document.querySelector('.docs-sidebar-toggle');
    const sidebar = document.querySelector('.docs-sidebar');
    const backdrop = document.querySelector('.docs-sidebar-backdrop');
    if (!toggle || !sidebar) return;

    function open() {
        sidebar.classList.add('open');
        backdrop?.classList.add('active');
    }

    function close() {
        sidebar.classList.remove('open');
        backdrop?.classList.remove('active');
    }

    toggle.addEventListener('click', () => {
        sidebar.classList.contains('open') ? close() : open();
    });

    backdrop?.addEventListener('click', close);

    sidebar.querySelectorAll('.docs-nav-list a').forEach(link => {
        link.addEventListener('click', close);
    });
}

// ---------------------------------------------------------------------------
// Prev / Next keyboard navigation (arrow keys)
// ---------------------------------------------------------------------------
function initPrevNextNav() {
    const prevLink = document.querySelector('.workshop-prev-link');
    const nextLink = document.querySelector('.workshop-next-link');

    if (!prevLink && !nextLink) return;

    document.addEventListener('keydown', (e) => {
        // Don't navigate if user is typing in search or another input
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        // Don't interfere with search modal keyboard nav
        if (document.getElementById('docsSearchModal')?.classList.contains('active')) return;

        if (e.key === 'ArrowLeft' && prevLink) {
            window.location.href = prevLink.href;
        } else if (e.key === 'ArrowRight' && nextLink) {
            window.location.href = nextLink.href;
        }
    });
}

// ---------------------------------------------------------------------------
// Page-visited tracking via localStorage
// ---------------------------------------------------------------------------
function initPageTracking() {
    const guideSlug = document.body.dataset.guideSlug;
    if (!guideSlug) return;

    const storageKey = `workshop-visited-${guideSlug}`;

    // Load previously visited pages
    let visited;
    try {
        visited = JSON.parse(localStorage.getItem(storageKey)) || [];
    } catch {
        visited = [];
    }

    // Mark current page as visited
    const navLinks = document.querySelectorAll('.docs-nav-list a[data-page-slug]');
    const currentSlug = Array.from(navLinks)
        .find(link => link.classList.contains('active'))
        ?.dataset.pageSlug;

    if (currentSlug && !visited.includes(currentSlug)) {
        visited.push(currentSlug);
        try {
            localStorage.setItem(storageKey, JSON.stringify(visited));
        } catch {
            // localStorage full or unavailable — skip
        }
    }

    // Apply visited class to sidebar links
    navLinks.forEach(link => {
        const slug = link.dataset.pageSlug;
        if (slug && visited.includes(slug)) {
            link.classList.add('visited');
        }
    });
}

// ---------------------------------------------------------------------------
// Search — Cmd+K / Ctrl+K modal with client-side filtering
// ---------------------------------------------------------------------------
function initSearch() {
    const trigger = document.getElementById('docsSearchTrigger');
    const modal = document.getElementById('docsSearchModal');
    const backdrop = document.getElementById('docsSearchBackdrop');
    const input = document.getElementById('docsSearchInput');
    const resultsContainer = document.getElementById('docsSearchResults');
    const emptyState = document.getElementById('docsSearchEmpty');
    if (!trigger || !modal || !input || !resultsContainer) return;

    let searchIndex = null;
    let activeIndex = -1;

    const shortcutKeyEl = document.querySelector('.docs-search-shortcut-key');
    if (shortcutKeyEl) {
        shortcutKeyEl.textContent = navigator.platform.includes('Mac') ? '\u2318' : 'Ctrl+';
    }

    function ensureIndex() {
        if (searchIndex !== null) return Promise.resolve();
        const url = document.body.dataset.searchUrl;
        if (!url) return Promise.reject();
        return fetch(url)
            .then(res => res.json())
            .then(data => { searchIndex = data; });
    }

    function open() {
        ensureIndex();
        modal.classList.add('active');
        backdrop?.classList.add('active');
        input.value = '';
        input.focus();
        resetResults();
    }

    function close() {
        modal.classList.remove('active');
        backdrop?.classList.remove('active');
        input.value = '';
        activeIndex = -1;
        resetResults();
    }

    function isOpen() {
        return modal.classList.contains('active');
    }

    function resetResults() {
        resultsContainer.innerHTML = '';
        if (emptyState) {
            resultsContainer.appendChild(emptyState);
            emptyState.style.display = '';
            emptyState.textContent = 'Type to search the workshop guide';
        }
        activeIndex = -1;
    }

    function performSearch(query) {
        if (!searchIndex || query.length < 2) {
            resetResults();
            return;
        }

        const terms = query.toLowerCase().split(/\s+/).filter(Boolean);
        const results = [];
        const guideSlug = document.body.dataset.guideSlug;

        searchIndex.forEach(page => {
            const titleLower = page.title.toLowerCase();
            const contentLower = page.content.toLowerCase();
            const headingTexts = page.headings.map(h => h.text.toLowerCase()).join(' ');
            const combined = titleLower + ' ' + headingTexts + ' ' + contentLower;

            if (!terms.every(term => combined.includes(term))) return;

            let score = 0;
            terms.forEach(term => {
                if (titleLower.includes(term)) score += 10;
                if (headingTexts.includes(term)) score += 5;
                if (contentLower.includes(term)) score += 1;
            });

            results.push({
                slug: page.slug,
                title: page.title,
                section: page.section,
                snippet: extractSnippet(page.content, terms),
                score: score,
                guideSlug: guideSlug,
            });
        });

        results.sort((a, b) => b.score - a.score);
        renderResults(results.slice(0, 15), terms);
    }

    function extractSnippet(content, terms) {
        const lower = content.toLowerCase();
        let firstIndex = lower.length;
        terms.forEach(term => {
            const idx = lower.indexOf(term);
            if (idx !== -1 && idx < firstIndex) firstIndex = idx;
        });

        const start = Math.max(0, firstIndex - 40);
        const end = Math.min(content.length, firstIndex + 160);
        let snippet = content.substring(start, end).trim();

        if (start > 0) snippet = '...' + snippet;
        if (end < content.length) snippet = snippet + '...';

        return snippet;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function highlightTerms(text, terms) {
        if (!terms.length) return escapeHtml(text);
        let safe = escapeHtml(text);
        const escaped = terms.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
        const pattern = new RegExp('(' + escaped.join('|') + ')', 'gi');
        return safe.replace(pattern, '<span class="docs-search-highlight">$1</span>');
    }

    function renderResults(results, terms) {
        resultsContainer.innerHTML = '';
        activeIndex = -1;

        if (results.length === 0) {
            const noResults = document.createElement('div');
            noResults.className = 'docs-search-no-results';
            noResults.textContent = 'No results found';
            resultsContainer.appendChild(noResults);
            return;
        }

        results.forEach((result, index) => {
            const link = document.createElement('a');
            link.className = 'docs-search-result';
            link.href = '/workshops/' + result.guideSlug + '/' + result.slug;
            link.dataset.index = index;

            link.innerHTML =
                '<div class="docs-search-result-section">' + escapeHtml(result.section) + '</div>' +
                '<div class="docs-search-result-title">' + highlightTerms(result.title, terms) + '</div>' +
                '<div class="docs-search-result-snippet">' + highlightTerms(result.snippet, terms) + '</div>';

            link.addEventListener('mouseenter', () => setActive(index));

            resultsContainer.appendChild(link);
        });
    }

    function setActive(index) {
        const items = resultsContainer.querySelectorAll('.docs-search-result');
        items.forEach(item => item.classList.remove('active'));
        activeIndex = index;
        if (items[index]) {
            items[index].classList.add('active');
            items[index].scrollIntoView({ block: 'nearest' });
        }
    }

    function navigateToActive() {
        const items = resultsContainer.querySelectorAll('.docs-search-result');
        if (items[activeIndex]) {
            window.location.href = items[activeIndex].href;
        }
    }

    trigger.addEventListener('click', open);
    backdrop?.addEventListener('click', close);
    input.addEventListener('input', () => {
        performSearch(input.value.trim());
    });

    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            isOpen() ? close() : open();
            return;
        }

        if (!isOpen()) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            close();
            return;
        }

        const items = resultsContainer.querySelectorAll('.docs-search-result');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setActive(activeIndex < items.length - 1 ? activeIndex + 1 : 0);
            return;
        }

        if (e.key === 'ArrowUp') {
            e.preventDefault();
            setActive(activeIndex > 0 ? activeIndex - 1 : items.length - 1);
            return;
        }

        if (e.key === 'Enter') {
            e.preventDefault();
            navigateToActive();
        }
    });
}
