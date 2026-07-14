<?php
/** @var string $currentNav */
/** @var bool $isAdmin */
/** @var array<int, string> $roles */
?>
<div
    id="sidebar-overlay"
    class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm md:hidden hidden"
    aria-hidden="true"
></div>

<aside
    id="app-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex flex-col w-[min(88vw,18rem)] md:relative md:z-auto md:w-64 bg-white/95 dark:bg-slate-900/95 md:bg-white/90 dark:md:bg-slate-900/90 border-r border-slate-200 dark:border-slate-800 shrink-0 -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:transition-none py-4 md:py-6 shadow-2xl md:shadow-none"
    aria-label="<?= escape(__('nav.menu')) ?>"
>
    <div class="flex items-center justify-between px-4 pb-4 border-b border-slate-200 dark:border-slate-800 md:hidden shrink-0">
        <span class="text-sm font-bold"><?= escape(__('nav.menu')) ?></span>
        <button
            type="button"
            id="sidebar-close-btn"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800"
            aria-label="<?= escape(__('nav.close_menu')) ?>"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <?php require base_path('app/Views/partials/sidebar-nav.php'); ?>
</aside>
