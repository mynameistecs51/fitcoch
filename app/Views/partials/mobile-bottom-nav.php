<?php
/** @var string $currentNav */
?>
<nav
    class="md:hidden fixed bottom-0 inset-x-0 z-30 flex items-stretch justify-around border-t border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md pb-[env(safe-area-inset-bottom)]"
    aria-label="<?= escape(__('nav.mobile_shortcuts')) ?>"
>
    <a
        href="<?= escape(url('/dashboard')) ?>"
        class="flex flex-1 flex-col items-center justify-center gap-0.5 py-2.5 px-1 text-[10px] font-medium <?= $currentNav === 'dashboard' ? 'text-brand-500' : 'text-slate-600 dark:text-slate-300' ?>"
    >
        <i class="fa-solid fa-chart-line text-base"></i>
        <span class="truncate max-w-full"><?= escape(__('sidebar.dashboard')) ?></span>
    </a>
    <a
        href="<?= escape(url('/courses')) ?>"
        class="flex flex-1 flex-col items-center justify-center gap-0.5 py-2.5 px-1 text-[10px] font-medium <?= $currentNav === 'courses' ? 'text-brand-500' : 'text-slate-600 dark:text-slate-300' ?>"
    >
        <i class="fa-solid fa-circle-play text-base"></i>
        <span class="truncate max-w-full"><?= escape(__('sidebar.preclass')) ?></span>
    </a>
    <a
        href="<?= escape(url('/profile')) ?>"
        class="flex flex-1 flex-col items-center justify-center gap-0.5 py-2.5 px-1 text-[10px] font-medium <?= $currentNav === 'profile' ? 'text-brand-500' : 'text-slate-600 dark:text-slate-300' ?>"
    >
        <i class="fa-solid fa-user text-base"></i>
        <span class="truncate max-w-full"><?= escape(__('nav.profile')) ?></span>
    </a>
    <button
        type="button"
        id="mobile-menu-btn"
        class="flex flex-1 flex-col items-center justify-center gap-0.5 py-2.5 px-1 text-[10px] font-medium text-slate-600 dark:text-slate-300"
        aria-label="<?= escape(__('nav.open_menu')) ?>"
    >
        <i class="fa-solid fa-bars text-base"></i>
        <span class="truncate max-w-full"><?= escape(__('nav.menu')) ?></span>
    </button>
</nav>
