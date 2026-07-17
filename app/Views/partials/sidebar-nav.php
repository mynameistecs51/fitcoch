<?php
/** @var string $currentNav */
/** @var bool $isAdmin */
/** @var array<int, string> $roles */
?>
<div class="space-y-6 px-4 flex-1 overflow-y-auto">
    <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 tracking-widest uppercase px-3">
        <?= escape(__('sidebar.learning_process')) ?>
    </div>
    <nav class="space-y-1">
        <a
            href="<?= escape(url('/dashboard')) ?>"
            class="ux-nav-link gap-3 <?= $currentNav === 'dashboard' ? 'is-active' : '' ?>"
        >
            <i class="fa-solid fa-chart-line ux-nav-icon"></i>
            <span><?= escape(__('sidebar.dashboard')) ?></span>
        </a>

        <a
            href="<?= escape(url('/courses')) ?>"
            class="ux-nav-link gap-3 <?= $currentNav === 'courses' ? 'is-active' : '' ?>"
        >
            <i class="fa-solid fa-circle-play ux-nav-icon"></i>
            <span class="flex-1 text-left min-w-0">
                <?= escape(__('sidebar.preclass')) ?>
                <span class="text-[9px] block text-brand-600 dark:text-brand-accent font-normal"><?= escape(__('sidebar.preclass_sub')) ?></span>
            </span>
        </a>

        <a
            href="<?= escape(url('/review/dashboard')) ?>"
            class="ux-nav-link gap-3 <?= ($currentNav ?? '') === 'reviews' ? 'is-active' : '' ?>"
        >
            <i class="fa-solid fa-brain ux-nav-icon"></i>
            <span class="flex-1 text-left min-w-0">
                <?= escape(__('sidebar.assessment')) ?>
                <span class="text-[9px] block text-yellow-600 dark:text-yellow-400 font-normal"><?= escape(__('sidebar.assessment_sub')) ?></span>
            </span>
        </a>

        <?php if (!empty($isAdmin) || (!empty($roles) && (in_array('instructor', $roles, true) || in_array('admin', $roles, true)))): ?>
            <a
                href="<?= escape(url('/instructor/courses')) ?>"
                class="ux-nav-link gap-3 <?= ($currentNav ?? '') === 'instructor' ? 'is-active' : '' ?>"
            >
                <i class="fa-solid fa-chalkboard-user ux-nav-icon"></i>
                <span><?= escape(__('courses.instructor.nav')) ?></span>
            </a>
        <?php endif; ?>

        <?php if (!empty($isAdmin)): ?>
            <div class="h-px bg-slate-200/80 dark:bg-slate-800/80 my-4"></div>
            <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 tracking-widest uppercase px-3">
                <?= escape(__('sidebar.admin_section')) ?>
            </div>
            <a
                href="<?= escape(url('/admin/users')) ?>"
                class="ux-nav-link gap-3 <?= $currentNav === 'admin' ? 'is-active' : '' ?>"
            >
                <i class="fa-solid fa-users-gear ux-nav-icon"></i>
                <span><?= escape(__('admin.title')) ?></span>
            </a>
        <?php endif; ?>

        <div class="h-px bg-slate-200/80 dark:bg-slate-800/80 my-4"></div>

        <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 tracking-widest uppercase px-3">
            <?= escape(__('sidebar.research_panel')) ?>
        </div>
        <span class="ux-nav-link gap-3 cursor-not-allowed opacity-50">
            <i class="fa-solid fa-graduation-cap ux-nav-icon text-brand-500"></i>
            <span class="flex-1 text-left font-semibold text-brand-600 dark:text-brand-500 min-w-0">
                <?= escape(__('sidebar.research')) ?>
                <span class="text-[9px] block text-slate-400 font-normal"><?= escape(__('sidebar.research_sub')) ?></span>
            </span>
        </span>

        <a
            href="<?= escape(url('/profile')) ?>"
            class="ux-nav-link gap-3 <?= $currentNav === 'profile' ? 'is-active' : '' ?>"
        >
            <i class="fa-solid fa-user ux-nav-icon"></i>
            <span><?= escape(__('nav.profile')) ?></span>
        </a>

        <form method="POST" action="<?= escape(url('/logout')) ?>" class="md:hidden pt-1">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <button type="submit" class="ux-nav-link gap-3 w-full text-red-600 dark:text-red-400 hover:bg-red-500/10">
                <i class="fa-solid fa-right-from-bracket ux-nav-icon text-red-500"></i>
                <span><?= escape(__('nav.sign_out')) ?></span>
            </button>
        </form>
    </nav>
</div>

<div class="mx-2 md:mx-4 mb-2 md:mb-0 px-4 py-4 ux-card shrink-0">
    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider"><?= escape(__('sidebar.thesis_footer')) ?></p>
    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 mt-1"><?= escape(__('sidebar.researcher_name')) ?></p>
    <p class="text-[9px] text-slate-500 dark:text-slate-400 mt-0.5"><?= escape(__('sidebar.researcher_degree')) ?></p>
</div>
