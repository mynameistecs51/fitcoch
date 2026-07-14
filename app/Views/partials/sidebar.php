<?php
/** @var string $currentNav */
/** @var bool $isAdmin */
?>
<aside class="hidden md:flex flex-col w-64 bg-white/90 dark:bg-slate-900/90 border-r border-slate-200 dark:border-slate-800 shrink-0 justify-between py-6">
    <div class="space-y-6 px-4">
        <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 tracking-widest uppercase px-3">
            <?= escape(__('sidebar.learning_process')) ?>
        </div>
        <nav class="space-y-1.5">
            <a
                href="<?= escape(url('/dashboard')) ?>"
                class="nav-item flex items-center w-full px-4 py-3 text-sm font-medium rounded-xl transition duration-200 <?= $currentNav === 'dashboard' ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white border-l-4 border-brand-500' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' ?>"
            >
                <i class="fa-solid fa-chart-line w-6 <?= $currentNav === 'dashboard' ? 'text-brand-500' : 'text-slate-400' ?>"></i>
                <span><?= escape(__('sidebar.dashboard')) ?></span>
            </a>

            <span class="nav-item flex items-center w-full px-4 py-3 text-sm font-medium rounded-xl text-slate-400 dark:text-slate-500 cursor-not-allowed opacity-60">
                <i class="fa-solid fa-circle-play w-6 text-slate-400"></i>
                <span class="flex-1 text-left">
                    <?= escape(__('sidebar.preclass')) ?>
                    <span class="text-[9px] block text-brand-600 dark:text-brand-accent"><?= escape(__('sidebar.preclass_sub')) ?></span>
                </span>
                <span class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-slate-200 dark:bg-slate-800 text-slate-500"><?= escape(__('sidebar.coming_soon')) ?></span>
            </span>

            <span class="nav-item flex items-center w-full px-4 py-3 text-sm font-medium rounded-xl text-slate-400 dark:text-slate-500 cursor-not-allowed opacity-60">
                <i class="fa-solid fa-person-running w-6 text-slate-400"></i>
                <span class="flex-1 text-left">
                    <?= escape(__('sidebar.inclass')) ?>
                    <span class="text-[9px] block text-cyan-600 dark:text-cyan-400"><?= escape(__('sidebar.inclass_sub')) ?></span>
                </span>
                <span class="px-1.5 py-0.5 text-[9px] font-bold rounded bg-slate-200 dark:bg-slate-800 text-slate-500"><?= escape(__('sidebar.coming_soon')) ?></span>
            </span>

            <span class="nav-item flex items-center w-full px-4 py-3 text-sm font-medium rounded-xl text-slate-400 dark:text-slate-500 cursor-not-allowed opacity-60">
                <i class="fa-solid fa-square-poll-horizontal w-6 text-slate-400"></i>
                <span class="flex-1 text-left">
                    <?= escape(__('sidebar.assessment')) ?>
                    <span class="text-[9px] block text-yellow-600 dark:text-yellow-400"><?= escape(__('sidebar.assessment_sub')) ?></span>
                </span>
            </span>

            <?php if (!empty($isAdmin)): ?>
                <div class="h-px bg-slate-200 dark:bg-slate-800 my-4"></div>
                <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 tracking-widest uppercase px-3">
                    <?= escape(__('sidebar.admin_section')) ?>
                </div>
                <a
                    href="<?= escape(url('/admin/users')) ?>"
                    class="nav-item flex items-center w-full px-4 py-3 text-sm font-medium rounded-xl transition duration-200 <?= $currentNav === 'admin' ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white border-l-4 border-brand-500' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' ?>"
                >
                    <i class="fa-solid fa-users-gear w-6 <?= $currentNav === 'admin' ? 'text-brand-500' : 'text-slate-400' ?>"></i>
                    <span><?= escape(__('admin.title')) ?></span>
                </a>
            <?php endif; ?>

            <div class="h-px bg-slate-200 dark:bg-slate-800 my-4"></div>

            <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 tracking-widest uppercase px-3">
                <?= escape(__('sidebar.research_panel')) ?>
            </div>
            <span class="nav-item flex items-center w-full px-4 py-3 text-sm font-medium rounded-xl text-slate-400 dark:text-slate-500 cursor-not-allowed opacity-60">
                <i class="fa-solid fa-graduation-cap w-6 text-brand-500"></i>
                <span class="flex-1 text-left font-semibold text-brand-600 dark:text-brand-500">
                    <?= escape(__('sidebar.research')) ?>
                    <span class="text-[9px] block text-slate-400 font-normal"><?= escape(__('sidebar.research_sub')) ?></span>
                </span>
            </span>

            <a
                href="<?= escape(url('/profile')) ?>"
                class="nav-item flex items-center w-full px-4 py-3 text-sm font-medium rounded-xl transition duration-200 <?= $currentNav === 'profile' ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white border-l-4 border-brand-500' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' ?>"
            >
                <i class="fa-solid fa-user w-6 <?= $currentNav === 'profile' ? 'text-brand-500' : 'text-slate-400' ?>"></i>
                <span><?= escape(__('nav.profile')) ?></span>
            </a>
        </nav>
    </div>

    <div class="px-6 py-4 mx-4 bg-slate-50 dark:bg-slate-950/80 rounded-2xl border border-slate-200 dark:border-slate-800/80">
        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider"><?= escape(__('sidebar.thesis_footer')) ?></p>
        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 mt-1"><?= escape(__('sidebar.researcher_name')) ?></p>
        <p class="text-[9px] text-slate-500 dark:text-slate-400"><?= escape(__('sidebar.researcher_degree')) ?></p>
    </div>
</aside>
