<div class="flex items-center gap-2">
    <button
        type="button"
        onclick="toggleTheme()"
        class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 bg-white/60 dark:bg-slate-800/60 text-xs text-slate-600 dark:text-slate-300 hover:border-brand-500/40 hover:bg-brand-500/5 transition duration-200"
        title="<?= escape(__('theme.toggle')) ?>"
        aria-label="<?= escape(__('theme.toggle')) ?>"
    >
        <i class="fa-solid fa-moon text-brand-500 hidden" data-theme-icon-dark></i>
        <i class="fa-solid fa-sun text-yellow-500" data-theme-icon-light></i>
        <span
            class="hidden sm:inline"
            data-theme-label
            data-label-dark="<?= escape(__('theme.dark')) ?>"
            data-label-light="<?= escape(__('theme.light')) ?>"
        ><?= escape(__('theme.light')) ?></span>
    </button>
</div>
