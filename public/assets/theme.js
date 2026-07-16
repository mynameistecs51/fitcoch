(function () {
    const STORAGE_KEY = 'fitcoch-theme';
    const root = document.documentElement;

    function getStoredTheme() {
        const stored = localStorage.getItem(STORAGE_KEY);
        return stored === 'light' || stored === 'dark' ? stored : 'light';
    }

    function applyTheme(theme) {
        const isDark = theme === 'dark';
        root.classList.toggle('dark', isDark);
        localStorage.setItem(STORAGE_KEY, theme);
        updateToggleUi(theme);
    }

    function updateToggleUi(theme) {
        const isDark = theme === 'dark';
        document.querySelectorAll('[data-theme-icon-dark]').forEach(function (el) {
            el.classList.toggle('hidden', !isDark);
        });
        document.querySelectorAll('[data-theme-icon-light]').forEach(function (el) {
            el.classList.toggle('hidden', isDark);
        });
        document.querySelectorAll('[data-theme-label]').forEach(function (el) {
            el.textContent = isDark
                ? el.getAttribute('data-label-dark') || 'Dark'
                : el.getAttribute('data-label-light') || 'Light';
        });
    }

    window.toggleTheme = function () {
        applyTheme(getStoredTheme() === 'dark' ? 'light' : 'dark');
    };

    applyTheme(getStoredTheme());
})();
