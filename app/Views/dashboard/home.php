<?php

ob_start();
?>
<section class="space-y-8">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-white via-slate-50 to-brand-50 dark:from-slate-900 dark:via-slate-900 dark:to-brand-dark/20 p-6 md:p-10 border border-slate-200 dark:border-slate-800">
        <div class="absolute -right-10 -bottom-10 opacity-10 text-9xl text-brand-accent pointer-events-none">
            <i class="fa-solid fa-medal"></i>
        </div>
        <div class="max-w-3xl space-y-4 relative">
            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-accent border border-brand-500/20">
                <?= escape(__('dashboard.badge')) ?>
            </span>
            <h1 class="text-2xl md:text-4xl font-extrabold text-slate-900 dark:text-white leading-tight">
                <?= escape(__('dashboard.hero_title')) ?>
            </h1>
            <p class="text-sm md:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                <?= escape(__('dashboard.hero_desc')) ?>
            </p>
            <p class="text-lg font-bold text-brand-600 dark:text-brand-500">
                <?= escape(__('dashboard.welcome', ['name' => $user->firstName])) ?>
            </p>
            <div class="flex flex-wrap gap-4 pt-2">
            <a href="<?= escape(url('/courses')) ?>" class="px-5 py-2.5 rounded-xl bg-brand-500 text-slate-950 font-bold text-sm shadow-lg shadow-brand-500/20 hover:bg-brand-accent transition duration-200">
                <?= escape(__('courses.title')) ?>
            </a>
        </div>
        </div>
    </div>

    <div>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center">
            <i class="fa-solid fa-arrows-spin mr-3 text-brand-500"></i>
            <?= escape(__('dashboard.pathway_title')) ?>
        </h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 relative hover:border-brand-500/40 transition">
                <span class="absolute -top-4 -left-2 w-10 h-10 rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-accent font-bold flex items-center justify-center border border-brand-500/30 text-lg">1</span>
                <div class="text-brand-500 text-3xl mb-4 mt-2"><i class="fa-solid fa-mobile-screen-button"></i></div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white"><?= escape(__('dashboard.step1_title')) ?></h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed"><?= escape(__('dashboard.step1_desc')) ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 relative hover:border-cyan-500/40 transition">
                <span class="absolute -top-4 -left-2 w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold flex items-center justify-center border border-cyan-500/30 text-lg">2</span>
                <div class="text-cyan-500 dark:text-cyan-400 text-3xl mb-4 mt-2"><i class="fa-solid fa-people-group"></i></div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white"><?= escape(__('dashboard.step2_title')) ?></h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed"><?= escape(__('dashboard.step2_desc')) ?></p>
            </div>
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 relative hover:border-yellow-500/40 transition">
                <span class="absolute -top-4 -left-2 w-10 h-10 rounded-xl bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 font-bold flex items-center justify-center border border-yellow-500/30 text-lg">3</span>
                <div class="text-yellow-500 dark:text-yellow-400 text-3xl mb-4 mt-2"><i class="fa-solid fa-clipboard-check"></i></div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white"><?= escape(__('dashboard.step3_title')) ?></h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed"><?= escape(__('dashboard.step3_desc')) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4"><?= escape(__('dashboard.account_info')) ?></h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                <p class="text-[10px] uppercase text-slate-500 dark:text-slate-400"><?= escape(__('dashboard.email')) ?></p>
                <p class="font-semibold mt-1 text-slate-900 dark:text-slate-200"><?= escape($user->email) ?></p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                <p class="text-[10px] uppercase text-slate-500 dark:text-slate-400"><?= escape(__('dashboard.timezone')) ?></p>
                <p class="font-semibold mt-1 text-slate-900 dark:text-slate-200"><?= escape($user->timezone) ?></p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                <p class="text-[10px] uppercase text-slate-500 dark:text-slate-400"><?= escape(__('dashboard.roles')) ?></p>
                <p class="font-semibold mt-1 text-brand-600 dark:text-brand-accent"><?= escape(translate_roles($roles)) ?></p>
            </div>
        </div>
    </div>

    <div class="p-6 rounded-3xl border border-brand-500/20 bg-brand-500/5 dark:bg-brand-dark/20 text-brand-700 dark:text-brand-accent">
        <p class="font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-info"></i>
            <?= escape(__('dashboard.sprint_title')) ?>
        </p>
        <p class="text-sm mt-1 text-slate-600 dark:text-slate-300"><?= escape(__('dashboard.sprint_message')) ?></p>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'dashboard';
require base_path('app/Views/layouts/app.php');
