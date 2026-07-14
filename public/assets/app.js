(function () {
    const sidebar = document.getElementById('app-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const openBtn = document.getElementById('sidebar-open-btn');
    const closeBtn = document.getElementById('sidebar-close-btn');
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mdBreakpoint = 768;

    function isMobile() {
        return window.innerWidth < mdBreakpoint;
    }

    function openSidebar() {
        if (!sidebar || !isMobile()) {
            return;
        }
        sidebar.classList.remove('-translate-x-full');
        overlay?.classList.remove('hidden');
        document.body.classList.add('sidebar-open');
    }

    function closeSidebar() {
        if (!sidebar) {
            return;
        }
        if (isMobile()) {
            sidebar.classList.add('-translate-x-full');
        }
        overlay?.classList.add('hidden');
        document.body.classList.remove('sidebar-open');
    }

    window.toggleMobileSidebar = function () {
        if (!sidebar) {
            return;
        }
        if (sidebar.classList.contains('-translate-x-full')) {
            openSidebar();
        } else {
            closeSidebar();
        }
    };

    openBtn?.addEventListener('click', toggleMobileSidebar);
    closeBtn?.addEventListener('click', closeSidebar);
    mobileMenuBtn?.addEventListener('click', toggleMobileSidebar);
    overlay?.addEventListener('click', closeSidebar);

    sidebar?.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (isMobile()) {
                closeSidebar();
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    window.addEventListener('resize', function () {
        if (!isMobile()) {
            overlay?.classList.add('hidden');
            document.body.classList.remove('sidebar-open');
            sidebar?.classList.remove('-translate-x-full');
        } else {
            sidebar?.classList.add('-translate-x-full');
        }
    });
})();
