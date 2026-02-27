/**
 * Partner Manager Documentation - Client-side interactivity
 * ScrollSpy, image zoom, mobile sidebar toggle, search.
 */
document.addEventListener('DOMContentLoaded', () => {
    initScrollSpy();
    initImageZoom();
    initMobileSidebar();
    initSearch();
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

    // Close sidebar when a nav link is clicked (mobile)
    sidebar.querySelectorAll('.docs-nav-list a').forEach(link => {
        link.addEventListener('click', close);
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

    // Populate the platform-specific shortcut key label
    const shortcutKeyEl = document.querySelector('.docs-search-shortcut-key');
    if (shortcutKeyEl) {
        shortcutKeyEl.textContent = navigator.platform.includes('Mac') ? '\u2318' : 'Ctrl+';
    }

    // Fetch the search index (lazy, on first open)
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
            emptyState.textContent = 'Type to search the documentation';
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

        searchIndex.forEach(page => {
            const titleLower = page.title.toLowerCase();
            const contentLower = page.content.toLowerCase();
            const headingTexts = page.headings.map(h => h.text.toLowerCase()).join(' ');
            const combined = titleLower + ' ' + headingTexts + ' ' + contentLower;

            // All terms must appear somewhere in the page
            if (!terms.every(term => combined.includes(term))) return;

            // Score: title matches rank highest, then headings, then content
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

        const version = document.body.dataset.docsVersion || '1.0';

        results.forEach((result, index) => {
            const link = document.createElement('a');
            link.className = 'docs-search-result';
            link.href = '/docs/' + version + '/' + result.slug;
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

    // Event: trigger button
    trigger.addEventListener('click', open);

    // Event: backdrop closes modal
    backdrop?.addEventListener('click', close);

    // Event: input
    input.addEventListener('input', () => {
        performSearch(input.value.trim());
    });

    // Event: keyboard
    document.addEventListener('keydown', (e) => {
        // Cmd+K / Ctrl+K to toggle search
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
