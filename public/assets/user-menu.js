(function () {
    const menu = document.getElementById('header-user-menu');
    const button = document.getElementById('header-user-menu-btn');
    const dropdown = document.getElementById('header-user-menu-dropdown');

    if (!menu || !button || !dropdown) {
        return;
    }

    function isOpen() {
        return !dropdown.classList.contains('hidden');
    }

    function openMenu() {
        dropdown.classList.remove('hidden');
        button.setAttribute('aria-expanded', 'true');
    }

    function closeMenu() {
        dropdown.classList.add('hidden');
        button.setAttribute('aria-expanded', 'false');
    }

    function toggleMenu() {
        if (isOpen()) {
            closeMenu();
        } else {
            openMenu();
        }
    }

    button.addEventListener('click', function (event) {
        event.stopPropagation();
        toggleMenu();
    });

    document.addEventListener('click', function (event) {
        if (!menu.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
})();
