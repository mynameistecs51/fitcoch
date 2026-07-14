<div class="flex items-center gap-2">
    <button
        type="button"
        onclick="toggleTheme()"
        class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs text-slate-600 dark:text-slate-300 hover:border-brand-500/50 transition duration-200"
        title="<?= escape(__('theme.toggle')) ?>"
        aria-label="<?= escape(__('theme.toggle')) ?>"
    >
        <i class="fa-solid fa-moon text-brand-500" data-theme-icon-dark></i>
        <i class="fa-solid fa-sun text-yellow-500 hidden" data-theme-icon-light></i>
        <span
            class="hidden sm:inline"
            data-theme-label
            data-label-dark="<?= escape(__('theme.dark')) ?>"
            data-label-light="<?= escape(__('theme.light')) ?>"
        ><?= escape(__('theme.dark')) ?></span>
    </button>

    <div class="flex items-center gap-1 rounded-xl border border-slate-200 dark:border-slate-700 p-1 bg-slate-50 dark:bg-slate-800">
        <a
            href="<?= escape(url('/lang/en')) ?>"
            class="px-2 py-1 rounded-lg text-xs <?= locale() === 'en' ? 'bg-brand-500 text-slate-950 font-semibold' : 'text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-accent' ?>"
        ><?= escape(__('lang.english')) ?></a>
        <a
            href="<?= escape(url('/lang/th')) ?>"
            class="px-2 py-1 rounded-lg text-xs <?= locale() === 'th' ? 'bg-brand-500 text-slate-950 font-semibold' : 'text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-accent' ?>"
        ><?= escape(__('lang.thai')) ?></a>
    </div>
</div>
