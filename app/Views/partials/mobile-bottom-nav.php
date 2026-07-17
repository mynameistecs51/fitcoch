<?php
/** @var string $currentNav */
?>
<nav
    class="md:hidden fixed bottom-0 inset-x-0 z-30 flex items-stretch justify-around border-t border-slate-200/80 dark:border-slate-800/80 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_24px_rgba(15,23,42,0.06)] dark:shadow-[0_-4px_24px_rgba(0,0,0,0.3)]"
    aria-label="<?= escape(__('nav.mobile_shortcuts')) ?>"
>
    <a
        href="<?= escape(url('/dashboard')) ?>"
        class="ux-mobile-nav-link <?= $currentNav === 'dashboard' ? 'is-active' : '' ?>"
    >
        <i class="fa-solid fa-chart-line"></i>
        <span class="truncate max-w-full"><?= escape(__('sidebar.dashboard')) ?></span>
    </a>
    <a
        href="<?= escape(url('/courses')) ?>"
        class="ux-mobile-nav-link <?= $currentNav === 'courses' ? 'is-active' : '' ?>"
    >
        <i class="fa-solid fa-circle-play"></i>
        <span class="truncate max-w-full"><?= escape(__('sidebar.preclass')) ?></span>
    </a>
    <a
        href="<?= escape(url('/profile')) ?>"
        class="ux-mobile-nav-link <?= $currentNav === 'profile' ? 'is-active' : '' ?>"
    >
        <i class="fa-solid fa-user"></i>
        <span class="truncate max-w-full"><?= escape(__('nav.profile')) ?></span>
    </a>
    <button
        type="button"
        id="mobile-menu-btn"
        class="ux-mobile-nav-link"
        aria-label="<?= escape(__('nav.open_menu')) ?>"
    >
        <i class="fa-solid fa-bars"></i>
        <span class="truncate max-w-full"><?= escape(__('nav.menu')) ?></span>
    </button>
</nav>
