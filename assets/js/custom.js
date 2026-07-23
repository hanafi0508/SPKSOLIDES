// Project scripts
document.addEventListener('DOMContentLoaded', function () {
    const currentPath = window.location.pathname;
    const currentUrl = window.location.pathname + window.location.search;

    document.querySelectorAll('.app-sidebar .nav-link[href]').forEach(function (link) {
        const linkUrl = new URL(link.href, window.location.origin);
        const linkPath = linkUrl.pathname;
        const linkFullPath = linkUrl.pathname + linkUrl.search;

        if (currentUrl === linkFullPath || (linkUrl.search === '' && currentPath === linkPath)) {
            link.classList.add('active');
            const collapse = link.closest('.collapse');

            if (collapse) {
                collapse.classList.add('show');
                const trigger = document.querySelector('[href="#' + collapse.id + '"]');

                if (trigger) {
                    trigger.classList.add('active');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            }
        }
    });
});
