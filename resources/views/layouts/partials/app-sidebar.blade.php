<aside class="app-sidebar d-flex flex-column">

    @include('layouts.partials/app-logo')

    @include('layouts.partials/app-menu-filter')

    <nav id="js-primary-nav" class="primary-nav flex-grow-1 custom-scroll">

        @include('layouts.partials/generated-navigation')

        <div class="no-results-msg pt-3 info-container">
            <h6 class="mb-1"> No menu items found.</h6>
            <p class="fs-sm">Try searching with different keywords.</p>
            <div class="d-flex align-items-center gap-1 fs-xs fw-500 font-style-italic">
                <kbd class="kbd-key">
                    <svg width="15" height="15" aria-label="Escape key" role="img">
                        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                           stroke-width="1.2">
                            <path
                                d="M13.6167 8.936c-.1065.3583-.6883.962-1.4875.962-.7993 0-1.653-.9165-1.653-2.1258v-.5678c0-1.2548.7896-2.1016 1.653-2.1016.8634 0 1.3601.4778 1.4875 1.0724M9 6c-.1352-.4735-.7506-.9219-1.46-.8972-.7092.0246-1.344.57-1.344 1.2166s.4198.8812 1.3445.9805C8.465 7.3992 8.968 7.9337 9 8.5c.032.5663-.454 1.398-1.4595 1.398C6.6593 9.898 6 9 5.963 8.4851m-1.4748.5368c-.2635.5941-.8099.876-1.5443.876s-1.7073-.6248-1.7073-2.204v-.4603c0-1.0416.721-2.131 1.7073-2.131.9864 0 1.6425 1.031 1.5443 2.2492h-2.956"></path>
                        </g>
                    </svg>
                </kbd> to reset
            </div>
        </div>
    </nav>

    <div class="nav-footer">
        <svg class="sa-icon sa-thin">
            <use href="/icons/sprite.svg#wifi"></use>
        </svg>
    </div>
</aside>

<div class="backdrop" data-action="toggle-swap" data-toggleclass="app-mobile-menu-open"></div>


<!-- Sidebar Link Active Script -->
<script>
    const sideNavMenu = document.getElementById("js-nav-menu");
    const currentUrl = window.location.href.split(/[?#]/)[0];     // Match current URL
    const allLinks = sideNavMenu.querySelectorAll("a");
    const allCollapses = sideNavMenu.querySelectorAll("li [aria-expanded='true']");   // Ensure only one collapse is open at a time

    // Prevent default toggle behavior for toggle links
    sideNavMenu.querySelectorAll("li .has-ul").forEach((toggle) => {
        toggle.addEventListener("click", (e) => e.preventDefault());
    });

    allCollapses.forEach((collapse) => {

        console.log(collapse)
        collapse.addEventListener("show.bs.collapse", (event) => {
            const currentCollapse = event.target;

            // Get all ancestor collapses of the current item
            const ancestors = [];
            let el = currentCollapse.parentElement;
            while (el && el !== sideNavMenu) {
                if (el.classList.contains("collapse")) {
                    ancestors.push(el);
                }
                el = el.parentElement;
            }

            allCollapses.forEach((other) => {
                if (other !== currentCollapse && !ancestors.includes(other)) {
                    new bootstrap.Collapse(other, {toggle: false}).hide();
                }
            });
        });
    });

    allLinks.forEach((link) => {
        if (link.href === currentUrl) {

            link.classList.add("active");

            // Traverse up to activate all parents and show collapses
            let currentElement = link.closest("li");
            while (currentElement && currentElement !== sideNavMenu) {
                currentElement.classList.add("active");

                // Show parent collapses
                const parentCollapse = currentElement.closest("[role='menu']");
                if (parentCollapse) {
                    new bootstrap.Collapse(parentCollapse, {toggle: false}).show();

                    // Also mark the <li> that contains the collapse as active
                    const collapseParentLi = parentCollapse.closest("li");
                    if (collapseParentLi) {
                        collapseParentLi.classList.add("active");
                    }

                    currentElement = collapseParentLi;
                } else {
                    currentElement = currentElement.parentElement;
                }
            }
        }
    });
</script>
