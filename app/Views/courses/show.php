<?php

ob_start();
$thClass = 'px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-4 py-3 text-sm text-slate-700 dark:text-slate-300';
?>
<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white"><?= escape($course->title) ?></h1>
            <?php if ($course->description): ?>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-3xl"><?= escape($course->description) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= escape(url('/courses')) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
            <?= escape(__('courses.back_to_list')) ?>
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 p-6 md:p-8 rounded-3xl border border-slate-200 dark:border-slate-800">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center">
            <i class="fa-solid fa-list-ol text-brand-500 mr-2"></i>
            <?= escape(__('courses.syllabus_title')) ?>
        </h2>

        <?php if ($modules === []): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400"><?= escape(__('courses.no_modules')) ?></p>
        <?php else: ?>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr>
                            <th class="<?= escape($thClass) ?> w-16"><?= escape(__('courses.table.order')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.module')) ?></th>
                            <th class="<?= escape($thClass) ?>"><?= escape(__('courses.table.content')) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        <?php foreach ($modules as $module): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="<?= escape($tdClass) ?> font-bold text-brand-600 dark:text-brand-accent">
                                    <?= escape((string) $module->sequenceOrder) ?>
                                </td>
                                <td class="<?= escape($tdClass) ?> font-semibold text-slate-900 dark:text-slate-200">
                                    <?= escape($module->title) ?>
                                </td>
                                <td class="<?= escape($tdClass) ?> text-slate-500 dark:text-slate-400 text-xs">
                                    <?= escape(__('courses.nuggets_coming_sprint4')) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = 'courses';
require base_path('app/Views/layouts/app.php');
